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
        $all_addresses = self::loadAddresses($contact_ids, $location_type_id);
        foreach ($contact_ids as $contact_id) {
          if (isset($all_addresses[$contact_id])) {
            $address = $all_addresses[$contact_id];
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


  protected static function loadAddresses($contact_ids, $location_type_id) {
    // TODO: cache?
    $query = civicrm_api3('Address', 'get', array(
      'contact_id'       => array('IN' => $contact_ids),
      'location_type_id' => $location_type_id,
      'return'           => 'street_address,supplemental_address_1,supplemental_address_2,postal_code,city,country_id,master_id,contact_id',
      'options'          => array('limit' => 0),
      ));

    // index by contact
    $contactId_2_address = array();
    foreach ($query['values'] as $address) {
      $contactId_2_address[$address['contact_id']] = $address;
    }
    return $contactId_2_address;
  }
}