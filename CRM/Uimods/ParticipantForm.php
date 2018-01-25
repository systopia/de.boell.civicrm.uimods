<?php
/*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2017 SYSTOPIA                            |
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
 * Changes the custom data form elements, hides storno mail checkbox and
 * the custom message field
 */
class CRM_Uimods_ParticipantForm {

  public static function buildFormHook($formName, &$form) {

    $script = file_get_contents(__DIR__ . '/../../js/participant_form.js');
    CRM_Core_Region::instance('page-body')->add(array(
      'script' => $script
    ));
  }
}
