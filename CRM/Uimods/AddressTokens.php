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

class CRM_Uimods_AddressTokens {

  /**
   * Handles civicrm_tokens hook
   * @see https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_tokens
   */
  public static function addTokens(&$tokens) {
    $location_types = civicrm_api3('LocationType', 'get', array('is_active' => 1, 'return' => 'display_name,name'));
    $new_tokens = array();
    foreach ($location_types['values'] as $location_type) {
      $section_name = "Adresse_{$location_type['id']}";

      // address tokens
      $new_tokens["{$section_name}.{$location_type['id']}_street_address"]         = "Strassenname ({$location_type['display_name']})";
      $new_tokens["{$section_name}.{$location_type['id']}_supplemental_address_1"] = "Adresszusatz 1 ({$location_type['display_name']})";
      $new_tokens["{$section_name}.{$location_type['id']}_supplemental_address_2"] = "Adresszusatz 2 ({$location_type['display_name']})";
      $new_tokens["{$section_name}.{$location_type['id']}_postal_code"]            = "Postleitzahl ({$location_type['display_name']})";
      $new_tokens["{$section_name}.{$location_type['id']}_city"]                   = "Stadt ({$location_type['display_name']})";
      $new_tokens["{$section_name}.{$location_type['id']}_country"]                = "Land ({$location_type['display_name']})";

      // extra tokens
      $new_tokens["{$section_name}.{$location_type['id']}_master"]                 = "Name Master ({$location_type['display_name']})";
      $new_tokens["{$section_name}.{$location_type['id']}_master_1"]               = "Name Master (Zeile 1) ({$location_type['display_name']})";
      $new_tokens["{$section_name}.{$location_type['id']}_master_2"]               = "Name Master (Zeile 2) ({$location_type['display_name']})";

      // store results
      $tokens["{$section_name}"] = $new_tokens;
    }
  }

  /**
   * Handles civicrm_tokenValues hook
   * @param $values - array of values, keyed by contact id
   * @param $cids - array of contactIDs that the system needs values for.
   * @param $job - the job_id
   * @param $tokens - tokens used in the mailing - use this to check whether a token is being used and avoid fetching data for unneeded tokens
   * @param $context - the class name
   *
   * @see https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_tokenValues
   */
  public static function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    // extract contact_ids
    if (is_string($cids)) {
      $contact_ids = explode(',', $cids);
    } elseif (isset($cids['contact_id'])) {
      $contact_ids = array($cids['contact_id']);
    } elseif (is_array($cids)) {
      $contact_ids = $cids;
    } else {
      error_log("Cannot interpret cids: " . json_encode($cids));
      return;
    }

    foreach ($tokens as $token_class => $token_list) {
      if (preg_match('/^Adresse_\d+$/', $token_class)) {
        $location_type_id = substr($token_class, 8);
        $includes_master_tokens = self::includesMasterTokens($token_list);
        $location_type_addresses = self::loadAddresses($contact_ids, $location_type_id, $includes_master_tokens);
        foreach ($contact_ids as $contact_id) {
          if (isset($location_type_addresses[$contact_id])) {
            $address = $location_type_addresses[$contact_id];
            foreach ($token_list as $token) {
              $field = substr($token, strlen($location_type_id) + 1);
              $values[$contact_id]["{$token_class}.{$token}"] = $address[$field];
              // error_log("FIELD {$token_class}.{$token} to $field, value is " . $address[$field]);
            }
          } else {
            // this guy doesn't have this address
            foreach ($token_list as $token) {
              $field = substr($token, strlen($location_type_id) + 1);
              $values[$contact_id]["{$token_class}.{$token}"] = '';
              // error_log("FIELD {$token_class}.{$token}n set to empty string");
            }
          }
        }
      }
    }
  }

  /**
   * just check if the token list includes tokens that
   * require loading the master contact (address sharing)
   */
  protected static function includesMasterTokens($token_list) {
    foreach ($token_list as $token) {
      if (strstr($token, 'master')) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * loads all addresses with a given type for the contact list
   * If $load_master is true, the fields 'master', 'master_1' and 'master_2' will be popuplated
   */
  protected static function loadAddresses($contact_ids, $location_type_id, $load_master = FALSE) {
    // TODO: cache?
    $query = civicrm_api3('Address', 'get', array(
      'contact_id'       => array('IN' => $contact_ids),
      'location_type_id' => $location_type_id,
      'return'           => 'street_address,supplemental_address_1,supplemental_address_2,postal_code,city,country_id,master_id,contact_id',
      'options'          => array('limit' => 0),
      ));

    // index by contact
    $contactId_2_address = array();
    $contactId_2_masterAddressId  = array();
    foreach ($query['values'] as $address) {
      $contactId_2_address[$address['contact_id']] = $address;
      if (!empty($address['master_id'])) {
        $contactId_2_masterAddressId[$address['contact_id']] = $address['master_id'];
      }
    }

    // add master information if requested
    // TODO: speed up with SQL?
    if ($load_master && !empty($contactId_2_masterAddressId)) {
      // step 1: load all master addresses
      $master_query = civicrm_api3('Address', 'get', array(
        'id'      => array('IN' => array_values($contactId_2_masterAddressId)),
        'return'  => 'id,contact_id',
        'options' => array('limit' => 0),
        ));
      $contactId_2_masterContactId = array();
      $masterAddressId_2_contactId = array_flip($contactId_2_masterAddressId);
      foreach ($master_query['values'] as $master_address) {
        $contact_id = $masterAddressId_2_contactId[$master_address['id']];
        $contactId_2_masterContactId[$contact_id] = $master_address['contact_id'];
      }

      // step 2: load all master contacts and set values in $contactId_2_address
      $masterContactId_2_contactId = array_flip($contactId_2_masterContactId);
      $master_contactquery = civicrm_api3('Contact', 'get', array(
        'id'      => array('IN' => array_values($contactId_2_masterContactId)),
        'return'  => 'display_name,custom_3,custom_4',
        'options' => array('limit' => 0),
        ));
      foreach ($master_contactquery['values'] as $master_contact) {
        $contact_id = $masterContactId_2_contactId[$master_contact['id']];
        $contactId_2_address[$contact_id]['master'] = $master_contact['display_name'];
        $contactId_2_address[$contact_id]['master_1'] = $master_contact['custom_3'];
        $contactId_2_address[$contact_id]['master_2'] = $master_contact['custom_4'];
      }
    }

    return $contactId_2_address;
  }
}