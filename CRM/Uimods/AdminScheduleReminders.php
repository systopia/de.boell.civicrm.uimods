<?php
/*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2018 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| Author: P. Batroff (batroff@systopia.de)               |
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
 * Gives a warning message when saving a reminder message that this reminder will
 * be automatically sent
 * @see https://projekte.systopia.de/redmine/issues/6750
 */
class CRM_Uimods_AdminScheduleReminders{

  /**
   * Gives a warning message when saving a reminder message that this reminder will
   * be automatically sent
   * @see https://projekte.systopia.de/redmine/issues/6750
   */
  public static function createUserWarning($formName, &$form) {
    CRM_Core_Session::setStatus(
      "Wenn der Reminder für heute konfiguriert ist, dann wird er innerhalb der nächsten 10 Minuten automatisch an alle Teilnehmer verschickt. Bitte den Inhalt der Email gegenchecken.",
      ts("Event Reminder Warning"),
      "error",
      array('unique' => true, 'expires' => 0));
  }


  public static function validate_reminder(&$fields, &$files, &$form, &$errors) {
    if (empty($fields['absolute_date']) && empty($fields['start_action_offset'])) {
      $errors['absolute_date']       = "Bitte setzen sie ein gültiges Versand Datum für den Reminder</br></br> Wenn noch kein Versand-Termin feststeht kann man die Erinnerung auch bis auf weiteres deaktivieren.";
    }
  }
}