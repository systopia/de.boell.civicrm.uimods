<?php
/*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2020 SYSTOPIA                            |
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

class CRM_Uimods_RegexTokens {

  /**
   * Generates an 'unregister' link of the form:
   * https://www.boell.de/node/{NODEID}/civi_unregister/{PARTICIPANT}/{PARTICIPANT_HASH}/{EMAIL}/{EMAIL_HASH}
   * for a token of the form:
   * ABMELDELINK-{event.event_id}-{contact.contact_id}
   * which will have been parsed as
   * /ABMELDELINK-(?P<event_id>[0-9]+)-(?P<contact_id>[0-9]+)/
   *
   * Otherwise a fallback is used
   *
   * @param $params array preg_match match result
   * @return string link string
   *
   * @see https://projekte.systopia.de/issues/9521
   */
  public static function eventUnregisterLink($params) {
    $FALLBACK =  'https://www.boell.de/unregister';

    try {
      // load event
      $drupal_node_field = CRM_Uimods_CustomData::getCustomFieldKey('remote_event_connection', 'external_identifier');
      static $event_cache = [];
      if (!isset($event_cache[$params['event_id']])) {
        $event_cache[$params['event_id']] = civicrm_api3('Event', 'getsingle', [
            'id'     => $params['event_id'],
            'return' => "id,{$drupal_node_field}"]);
      }
      // get drupal_node_id
      if (empty($event_cache[$params['event_id']][$drupal_node_field])) {
        return $FALLBACK;
      }
      $drupal_node_id = $event_cache[$params['event_id']][$drupal_node_field];

      // find participant
      $participants = civicrm_api3('Participant', 'get', [
          'event_id'   => $params['event_id'],
          'contact_id' => $params['contact_id'],
          'return'     => 'id,status_id,contact_id'
      ]);
      if ($participants['count'] <> 1) {
        return $FALLBACK;
      }
      $participant = reset($participants['values']);

      // load contact
      $contact = civicrm_api3('Contact', 'getsingle', [
          'id'     => $participant['contact_id'],
          'return' => 'email,hash'
      ]);
      if (empty($contact['email'])) {
        return $FALLBACK;
      }

      // generate email_hash
      $email_hash = crypt($contact['email']);

      // that's it:
      return "https://calendar.boell.de/node/{$drupal_node_id}/civi_unregister/{$participant['id']}/{$contact['hash']}/{$contact['email']}/{$email_hash}";

    } catch(Exception $ex) {
      Civi::log()->debug('unregisterLink generation failed: ' . $ex->getMessage());
    }

    return $FALLBACK;
  }
}