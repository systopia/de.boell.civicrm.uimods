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

class CRM_Uimods_NameTokens {

  /**
   * Handles civicrm_tokens hook
   * @see https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_tokens
   */
  public static function addTokens(&$tokens) {
    $tokens['Contact']['Contact.organization_name_1'] = "Organisationsname Zeile 1";
    $tokens['Contact']['Contact.organization_name_2'] = "Organisationsname Zeile 2";
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

    // check if we need to spring into action...
    if (    in_array('organization_name_1', $tokens['Contact'])
         || in_array('organization_name_2', $tokens['Contact'])) {
      $field_name_1 = CRM_Uimods_Config::getOrgnameField(1);
      $field_name_2 = CRM_Uimods_Config::getOrgnameField(2);
      $data = civicrm_api3('Contact', 'get', array(
        'contact_id'   => array('IN' => $contact_ids),
        'contact_type' => 'Organization',
        'return' =>  "{$field_name_1},{$field_name_2}"
        ));

      foreach ($contact_ids as $contact_id) {
        if (isset($data['values'][$contact_id])) {
          $values[$contact_id]["Contact.organization_name_1"] = CRM_Utils_Array::value($field_name_1, $data['values'][$contact_id], '');
          $values[$contact_id]["Contact.organization_name_2"] = CRM_Utils_Array::value($field_name_2, $data['values'][$contact_id], '');
        } else {
          $values[$contact_id]["Contact.organization_name_1"] = '';
          $values[$contact_id]["Contact.organization_name_2"] = '';
        }
      }
    }
  }
}