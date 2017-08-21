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
  protected static $_currently_edited_address_relevant       = FALSE;
  protected static $_currently_editing_contact_id            = NULL;

  /**
   * Find out wheter this address change is relevant
   */
  public static function handleAddressPre($op, $id, $params) {
    $master_id_changed      = FALSE;
    $business_location_type = FALSE;

    if ($op == 'edit' || $op == 'create' || $op == 'delete') {
      $address = $params; // copy params
      // check if attributes are missing
      if ($id && (!isset($address['master_id']) || empty($address['contact_id'])  || empty($address['location_type_id']))) {
        // load address and fill attributes
        $address = civicrm_api3('Address', 'getsingle', array(
          'id'     => $id,
          'return' => 'contact_id,master_id,location_type_id'));
      }

      // store the contact_id
      if (!empty($address['contact_id'])) {
        self::$_currently_editing_contact_id = $address['contact_id'];
      } else {
        error_log("de.boell.civicrm.uimods: Unexpected preHook data! Contact author.");
        self::$_currently_editing_contact_id = NULL;
      }

      // this change is relevant, if master_id is changed
      if (!empty($address['master_id']) || !empty($params['master_id'])) {
        $master_id_changed = TRUE;
      } else {
        $master_id_changed = FALSE;
      }

      // store the address type
      if (!empty($address['location_type_id'])) {
        if (   $address['location_type_id'] == CRM_Uimods_Config::getBusinessLocationType()
            || $params['location_type_id'] == CRM_Uimods_Config::getBusinessLocationType()) {
          $business_location_type = TRUE;
        } else {
          $business_location_type = FALSE;
        }
      } else {
        error_log("de.boell.civicrm.uimods: Unexpected preHook data! Contact author.");
        self::$_currently_editing_address_location_type = 'UNKNOWN';
      }
    }

    // mark this edit as relevant if either is involved (just to be safe)
    self::$_currently_edited_address_relevant = $business_location_type || $master_id_changed;
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
  }

  /**
   * check if we maybe want to delete this realtionship
   * @see https://projekte.systopia.de/redmine/issues/5193
   */
  public static function handleRelationshipPre($op, $objectId, $params) {
    // nothing to do
  }

  /**
   * check if we maybe want to delete this realtionship
   * @see https://projekte.systopia.de/redmine/issues/5193
   */
  public static function handleRelationshipPost($op, $objectId, $objectRef) {
    if ( $op != 'delete' && $objectId && $objectRef->relationship_type_id == CRM_Uimods_Config::getLegacyEmployerRelationshipID()) {
      civicrm_api3('Relationship', 'delete', array('id' => $objectId));
    }
  }

  /**
   * Make sure the relations reflect the current address sharing status
   */
  public static function synchroniseEmployerRelationships($contact_id) {
    if (empty($contact_id)) {
      error_log("de.boell.civicrm.uimods: Couldn't access contact_id! Contact author.");
      return;
    }

    $relationships_changed = FALSE;

    // first: consolidate relationships (i.e. system has created multiple)
    // $relationships = self::consolidateRelationships($contact_id);

    // load relationships
    $relationships = civicrm_api3('Relationship', 'get', array(
      'contact_id_a'         => $contact_id,
      'relationship_type_id' => CRM_Uimods_Config::getEmployerRelationshipID(),
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

    // NOW: synchronise
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

      // find a matching active relationship
      foreach ($relationships as $relationship) {
        if (!$relationship['is_active']) continue;

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
        // we need to make sure there is an active one
        try {
          $today = date('Y-m-d');
          $relationships_changed = TRUE;

          // but first: check if there isn't an inactive one
          $existing = NULL;
          foreach ($relationships as $relationship) {
            if ($relationship['contact_id_b'] == $employer_id) {
              $start_date = empty($relationship['start_date']) ? '0000-00-00' : date('Y-m-d', strtotime($relationship['start_date']));
              $end_date   = empty($relationship['end_date'])   ? '9999-99-99' : date('Y-m-d', strtotime($relationship['end_date']));
              // error_log("[{$relationship['id']}]: $start_date - $end_date");
              if ($start_date <= $today && $end_date >= $today) {
                // we found a match
                // error_log("MATCH!");
                $existing = $relationship;
                break;
              }
            }
          }

          if (!empty($existing['id'])) {
            // there is one: simply set to active again
            // error_log("ACTIVATE RELATIONSHIP " . $existing['id']);
            civicrm_api3('Relationship', 'create', array(
              'id'        => $existing['id'],
              'is_active' => '1',
              'end_date'  => ''));
            $contact_employer_id = $employer_id;

          } else {
            $new_relationship_data = array(
              'contact_id_a'         => $contact_id,
              'contact_id_b'         => $employer_id,
              'relationship_type_id' => CRM_Uimods_Config::getEmployerRelationshipID(),
              'is_active'            => '1',
              'start_date'           => date('YmdHis'));
            // error_log("CREATE RELATIONSHIP " . json_encode($new_relationship_data));
            $contact_employer_id = $employer_id;
            civicrm_api3('Relationship', 'create', $new_relationship_data);
          }
        } catch (Exception $e) {
          error_log("de.boell.civicrm.uimods: Couldn't create/reactivate - " . $e->getMessage());
        }
      }
    } // END address loop

    // now, for all remaining active relationships we couldn't find a current address
    //  -> those need to be deactivated
    foreach ($relationships as $relationship) {
      if (!$relationship['is_active']) continue;

      // error_log("END RELATIONSHIP " . json_encode($relationship));
      try {
        $relationships_changed = TRUE;
        civicrm_api3('Relationship', 'create', array(
          'id'        => $relationship['id'],
          'is_active' => '0',
          'end_date'  => date('YmdHis')));
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
   * Generate the HTML content for the "employer" field
   */
  public static function getCurrentEmployerHTML($contact_id) {
    if (empty($contact_id)) return '';

    // load all active relationships
    $relationships = civicrm_api3('Relationship', 'get', array(
      'contact_id_a'         => $contact_id,
      'relationship_type_id' => CRM_Uimods_Config::getEmployerRelationshipID(),
      'return'               => 'contact_id_b',
      'is_active'            => 1,
      'sequential'           => 1,
      'option.limit'         => 0))['values'];

    // load related contacts' display name
    $contact_list = array();
    foreach ($relationships as $realtionship) {
      $contact_list[] = $realtionship['contact_id_b'];
    }
    if (!empty($contact_list)) {
      $contacts = civicrm_api3('Contact', 'get', array(
        'id'           => array('IN' => $contact_list),
        'return'       => 'display_name',
        'sequential'   => 0,
        'option.limit' => 0))['values'];

      $employers = array();
      foreach ($relationships as $relationship) {
        $link = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=19' . $relationship['contact_id_b'], true);
        $name = $contacts[$relationship['contact_id_b']]['display_name'];
        $employers[] = "<a href=\"{$link}\" title=\"Aktuelle/n Arbeitgeber/in anzeigen\">{$name}</a>";
      }

      return implode(', ', $employers);
    } else {
      return '';
    }
  }






  /**
   * make sure there are no duplicate relationships,
   *  e.g. created by CiviCRM's brilliant address sharing algorithm
   *
   * @return consolidated relationships
   * @deprecated
   */
  protected static function consolidateRelationships($contact_id) {
    // load all relationships
    $relationships = civicrm_api3('Relationship', 'get', array(
      'contact_id_a'         => $contact_id,
      'relationship_type_id' => CRM_Uimods_Config::getEmployerRelationshipID(),
      'return'               => 'contact_id_b,is_active,end_date,start_date',
      'sequential'           => 1,
      'option.limit'         => 0))['values'];

    // sort into categories
    $cat2relationships = array();
    foreach ($relationships as $relationship) {
      $category = "{$relationship['is_active']}-{$relationship['contact_id_b']}";
      $cat2relationships[$category][] = $relationship;
    }

    // consolidate multiple ones
    foreach (array_keys($cat2relationships) as $category) {
      $relationships = $cat2relationships[$category];
      if (count($relationships) > 1) {
        $cat2relationships[$category] = self::consolidateRelationshipTuple($relationships);
      }
    }

    // now: return in indexed set of relationships
    $all_relationships = array();
    foreach ($cat2relationships as $category => $relationships) {
      foreach ($relationships as $relationship) {
        if ($relationship['is_active']) {
          $all_relationships[$relationship['id']] = $relationship;
        }
      }
    }
    return $all_relationships;
  }

  /**
   * TODO
   * @deprecated
   */
  protected static function consolidateRelationshipTuple($relationships) {
    if (count($relationships) > 1) {
      error_log("MULTIPLE RELATIONSHIP DETECTED!");
      foreach ($relationships as $relationship) {
        error_log(json_encode($relationship));
      }
    }

    // TODO: implement?

    return $relationships;
  }
}
