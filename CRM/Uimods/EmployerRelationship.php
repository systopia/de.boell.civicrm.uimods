<?php
/*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

/**
 * Implements special treatment of the employer relationship
 * in association with shared addresses
 */
class CRM_Uimods_EmployerRelationship {

  protected static $_currently_editing_contact_id            = NULL;
  protected static $_currently_edited_address_relevant       = NULL;
  protected static $_currently_editing_address_location_type = NULL;
  protected static $_currently_editing_relationship_new      = FALSE;
  protected static $_currently_editing_recursion_stop        = FALSE;
  protected static $_currently_editing_relationship_relevant = FALSE;

  /**
   * Store the information on which address type we're dealing with
   */
  public static function handleAddressPre($op, $id, $params) {
    if ($op == 'edit' || $op == 'create' || $op == 'delete') {
      $address = $params; // copy params
      // check if attributes are missing
      if ($id && (!isset($address['master_id']) || empty($address['contact_id'])  || empty($address['location_type_id']))) {
        // load address and fill attributes
        $address = civicrm_api3('Address', 'getsingle', array(
          'id'     => $id,
          'return' => 'contact_id,master_id,location_type_id'));
      }

      // this change is relevant, if master_id is changed
      if (!empty($address['master_id']) || !empty($params['master_id'])) {
        self::$_currently_edited_address_relevant = TRUE;
      } else {
        self::$_currently_edited_address_relevant = FALSE;
      }

      // store the contact_id
      if (!empty($address['contact_id'])) {
        self::$_currently_editing_contact_id = $address['contact_id'];
      } else {
        error_log("de.boell.civicrm.uimods: Unexpected preHook data! Contact author.");
        self::$_currently_editing_contact_id = NULL;
      }

      // store the address type
      if (!empty($address['location_type_id'])) {
        if (   $address['location_type_id'] == CRM_Uimods_Config::getBusinessLocationType()
            || $params['location_type_id'] == CRM_Uimods_Config::getBusinessLocationType()) {
          self::$_currently_editing_address_location_type = CRM_Uimods_Config::getBusinessLocationType();
        } else {
          self::$_currently_editing_address_location_type = $address['location_type_id'];
        }
      } else {
        error_log("de.boell.civicrm.uimods: Unexpected preHook data! Contact author.");
        self::$_currently_editing_address_location_type = 'UNKNOWN';
      }
    }
  }

  /**
   * Trigger relationship sync if this was a relevant change
   */
  public static function handleAddressPost($op, $objectId, $objectRef) {
    // if a relevant address has been manipulated, trigger sync!
    if (self::$_currently_edited_address_relevant) {
      self::$_currently_edited_address_relevant = FALSE;
      self::synchroniseEmployerRelationships(self::$_currently_editing_contact_id);
    }
    self::$_currently_edited_address_relevant = FALSE;
    self::$_currently_editing_address_location_type = NULL;
    self::$_currently_editing_contact_id = NULL;
  }

  /**
   * check if we maybe want to delete this realtionship
   * @see https://projekte.systopia.de/redmine/issues/5193
   */
  public static function handleRelationshipPre($op, $objectId, $params) {
    self::$_currently_editing_relationship_relevant = self::isRelationshipRelevant($params, $objectId);
    if (!self::$_currently_editing_relationship_relevant) {
      return;
    }

    // store the fact if this is new
    if (!self::$_currently_editing_recursion_stop) {
      self::$_currently_editing_relationship_new = ($objectId == NULL);
    }
  }

  /**
   * check if we maybe want to delete this realtionship
   * @see https://projekte.systopia.de/redmine/issues/5193
   */
  public static function handleRelationshipPost($op, $objectId, $objectRef) {
    // ignore irrelevant relationships
    if (!self::$_currently_editing_relationship_relevant) {
      return;
    }

    // PREVENT RECURSION
    if (self::$_currently_editing_recursion_stop) {
      return;
    } else {
      self::$_currently_editing_recursion_stop = TRUE;
    }

    if (self::$_currently_editing_address_location_type && $op == 'create') {
      // only keep automatically created relationships
      //  for location type "GESCHÄFTLICH":
      if (self::$_currently_editing_address_location_type == CRM_Uimods_Config::getBusinessLocationType()) {
        // avoid recursion:
        self::$_currently_editing_address_location_type = NULL;

        // set start date (which isn't set by the default function)
        if (self::$_currently_editing_relationship_new) {
          civicrm_api3('Relationship', 'create', array(
            'id'         => $objectId,
            'start_date' => date('Y-m-d')));
        }

      } else {
        // to avoid recur loop, set current location type to NULL:
        $cached_locationship_type = self::$_currently_editing_address_location_type;
        $cached_relevant          = self::$_currently_edited_address_relevant;
        self::$_currently_editing_address_location_type = NULL;
        self::$_currently_edited_address_relevant       = NULL;

        // get contact_id from the unwanted relationship
        $unwanted_relationship = civicrm_api3('Relationship', 'getsingle', array(
          'id'     => $objectId,
          'return' => 'contact_id_a'));

        // delete the unwanted relationship
        // error_log("DELETE automatically generated relationship");
        civicrm_api3('Relationship', 'delete', array('id' => $objectId));

        // call synchronisation
        if (!empty($unwanted_relationship['contact_id_a'])) {
          self::synchroniseEmployerRelationships($unwanted_relationship['contact_id_a']);
        }

        // restore current location type + update
        self::$_currently_editing_address_location_type = $cached_locationship_type;
        self::$_currently_edited_address_relevant       = $cached_relevant;
      }
    }

    self::$_currently_editing_recursion_stop = FALSE;
  }

  /**
   * Make sure the relations reflect the current address sharing status
   */
  public static function synchroniseEmployerRelationships($contact_id) {
    if (empty($contact_id)) {
      error_log("de.boell.civicrm.uimods: Couldn't access contact_id! Contact author.");
      return;
    }

    // load all relationships
    $relationships = civicrm_api3('Relationship', 'get', array(
      'contact_id_a'         => $contact_id,
      'relationship_type_id' => CRM_Uimods_Config::getEmployerRelationshipID(),
      'is_active'            => 1,
      'return'               => 'contact_id_b,is_active,end_date,start_date',
      'sequential'           => 0,
      'option.limit'         => 0))['values'];

    // load all addresses
    $addresses = civicrm_api3('Address', 'get', array(
      'contact_id'       => $contact_id,
      'location_type_id' => CRM_Uimods_Config::getBusinessLocationType(),
      'master_id'        => array('IS NOT NULL' => 1),
      'return'           => 'master_id',
      'sequential'       => 0,
      'option.limit'     => 0))['values'];

    // load masters addresses
    $contact_employer_id = '';
    $master_id_to_contact_id = array();
    $master_address_ids = array();
    foreach ($addresses as $address) {
      if (!empty($address['master_id']))
      $master_address_ids[] = $address['master_id'];
    }
    if (!empty($master_address_ids)) {
      $master_addresses = civicrm_api3('Address', 'get', array(
        'id'           => array('IN' => $master_address_ids),
        'return'       => 'id,contact_id',
        'option.limit' => 0))['values'];
      foreach ($master_addresses as $master_address) {
        $master_id_to_contact_id[$master_address['id']] = $master_address['contact_id'];
      }
    }

    // error_log("LOADED ADDRESSES: " . json_encode($addresses));
    // error_log("LOADED RELATIONS: " . json_encode($relationships));
    // error_log("MASTER MAPPING  : " . json_encode($master_id_to_contact_id));

    // NOW: we've collected all the data -> synchronise
    foreach ($addresses as $address) {
      // first: find an existing relation
      $connected_relationship = NULL;
      $employer_id = $master_id_to_contact_id[$address['master_id']];
      if (!$employer_id) {
        error_log("de.boell.civicrm.uimods: LOOKUP ERROR! Contact author");
        continue;
      }
      // error_log("ADDRESS " . json_encode($address));
      // error_log("ORGANISATION $employer_id");
      foreach ($relationships as $relationship) {
        if ($relationship['contact_id_b'] == $employer_id) {
          $connected_relationship = $relationship;
          $contact_employer_id = $employer_id;
          break;
          // NEEDED FOR REACTIVATION: the active one has preference
          // if ($relationship['is_active'] || $connected_relationship == NULL) {
          //   $connected_relationship = $relationship;
          // }
        }
      }

      if ($connected_relationship) {
        // we found one...
        // error_log("ADDRESS MATCHED");
        unset($relationships[$connected_relationship['id']]);
      } else {
        // we should create a new one
        try {
          // but first: check if there isn't an inactive one
          $existing = civicrm_api3('Relationship', 'get', array(
            'contact_id_a'         => $contact_id,
            'contact_id_b'         => $employer_id,
            'relationship_type_id' => CRM_Uimods_Config::getEmployerRelationshipID(),
            'is_active'            => '0',
            'option.limit'         => '1',
            'start_date'           => date('Y-m-d')));
          if ($existing['id']) {
            // there is one: simply set to active again
            // error_log("ACTIVATE RELATIONSHIP " . $existing['id']);
            civicrm_api3('Relationship', 'create', array(
              'id'        => $existing['id'],
              'is_active' => '1',
              'end_date'  => ''));

          } else {
            $new_relationship_data = array(
              'contact_id_a' => $contact_id,
              'contact_id_b' => $employer_id,
              'relationship_type_id' => CRM_Uimods_Config::getEmployerRelationshipID(),
              'is_active'            => '1',
              'start_date'           => date('Y-m-d'));
            // error_log("CREATE RELATIONSHIP " . json_encode($new_relationship_data));
            $contact_employer_id = $employer_id;
            civicrm_api3('Relationship', 'create', $new_relationship_data);
          }
        } catch (Exception $e) {
          error_log("de.boell.civicrm.uimods: Couldn't create/reactivate - " . $e->getMessage());
        }
      }
    } // END address loop

    // now, for all remaining relationships we couldn't find a current address
    //  -> those need to be deactivated
    foreach ($relationships as $relationship) {
      // error_log("END RELATIONSHIP " . json_encode($relationship));
      try {
        civicrm_api3('Relationship', 'create', array(
          'id'        => $relationship['id'],
          'is_active' => '0',
          'end_date'  => date('Y-m-d')));
      } catch (Exception $e) {
        error_log("de.boell.civicrm.uimods: Couldn't end relationship - " . $e->getMessage());
      }
    }

    // finally: set/remove employer_id
    // error_log("SET $contact_id employer to $contact_employer_id");
    civicrm_api3('Contact', 'create', array(
      'id'          => $contact_id,
      'employer_id' => $contact_employer_id));
  }

  /**
   * Find out whether this relationship is relevant,
   * i.e. is it the employer relationship
   */
  protected static function isRelationshipRelevant($relationship_data, $relationship_id) {
    if (!empty($relationship_data['relationship_type_id'])) {
      return $relationship_data['relationship_type_id'] == CRM_Uimods_Config::getEmployerRelationshipID();
    } elseif (!empty($relationship_id)) {
      // load the relationship
      $relationship = civicrm_api3('Relationship', 'getsingle', array(
        'id'     => $relationship_id,
        'return' => 'relationship_type_id'));
      return $relationship['relationship_type_id'] == CRM_Uimods_Config::getEmployerRelationshipID();
    } else {
      return FALSE;
    }
  }
}
