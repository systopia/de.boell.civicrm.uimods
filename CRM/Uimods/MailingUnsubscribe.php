<?php
/*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2018 SYSTOPIA                            |
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
 * Handles a custom unsubscribing procedure
 * @see https://projekte.systopia.de/redmine/issues/6323
 */
class CRM_Uimods_MailingUnsubscribe {

  /**
   * Handles a custom unsubscribing procedure
   * @see https://projekte.systopia.de/redmine/issues/6323
   */
  public static function handleUnsubscribeGroups($mailingId, $contactId, &$groups, &$baseGroups) {
    // only unsubscribe from the groups that the contact is really a member of
    $groups_copy = $groups;
    foreach ($groups_copy as $group_id) {
      if (!CRM_Contact_BAO_GroupContact::isContactInGroup($contactId, $group_id)) {
        // not in the group -> remove from unsubscribe list
        $key = array_search($group_id, $groups);
        if ($key !== false) {
          unset($groups[$key]);
        }
      }
    }
  }
}