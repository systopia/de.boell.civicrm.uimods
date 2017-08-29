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

class CRM_Uimods_UserClearanceApiWrapper implements API_Wrapper {

  /**
   * Interface for interpreting api input.
   *
   * @param array $apiRequest
   *
   * @return array
   *   modified $apiRequest
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * Interface for interpreting api output.
   *
   * @param array $apiRequest
   * @param array $result
   *
   * @return array
   *   modified $result
   */
  public function toApiOutput($apiRequest, $result) {
    if ($apiRequest['entity'] == 'RemoteRegistration' && $apiRequest['action'] == 'register') {
      if (empty($result['is_error']) && !empty($result['contact_id'])) {
        $this->createUserClearanceRecord(
          $result['contact_id'],
          'Anmeldung Veranstaltung',
          'Double-opt-in',
          "Veranstaltung {$result['event_id']}");
      }

    } else if ($apiRequest['entity'] == 'RemoteGroup' && $apiRequest['action'] == 'subscribe') {
      if (empty($result['is_error']) && !empty($result['id'])) {
        $this->createUserClearanceRecord(
          $result['id'],
          'Anmeldung Gruppe',
          'Double-opt-in',
          '');
      }
    }

    return $result;
  }

  /**
   * add a new user clearance (Nutzungsberechtigung) entry for the contact
   */
  protected function createUserClearanceRecord($contact_id, $category, $source, $note, $date = 'now') {
    $data = array(
      'nutzungsberechtigung.nutzungsberechtigung_datum'     => date('YmdHis', strtotime($date)),
      'nutzungsberechtigung.nutzungsberechtigung_kategorie' => CRM_Core_OptionGroup::getValue('nutzungsberechtigung_kategorie', $category, 'label'),
      'nutzungsberechtigung.nutzungsberechtigung_quelle'    => CRM_Core_OptionGroup::getValue('nutzungsberechtigung_quelle', $source, 'label'),
      'nutzungsberechtigung.nutzungsberechtigung_anmerkung' => $note,
    );

    // resolve custom fields
    CRM_Uimods_CustomData::resolveCustomFields($data, array('nutzungsberechtigung'));

    // since this is a multi-entry group, we need to clarify the index (-1 = new entry)
    $request = array('entity_id' => $contact_id);
    foreach ($data as $key => $value) {
      $request[$key . ':-1'] = $value;
    }

    civicrm_api3('CustomValue', 'create', $request);
  }

  /**
   * will be triggered by a civicrm_apiWrappers hook
   *  and register wrappers for related actions
   */
  public static function registerWrappers(&$wrappers, $apiRequest) {
    if (  ($apiRequest['entity'] == 'RemoteRegistration' && $apiRequest['action'] == 'register')
       || ($apiRequest['entity'] == 'RemoteGroup' && $apiRequest['action'] == 'subscribe')) {
      $wrappers[] = new CRM_Uimods_UserClearanceApiWrapper();
    }
  }
}