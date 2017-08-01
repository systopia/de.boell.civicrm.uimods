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
 * Minor Changes to the UI:
 *  - hide default greetings (email/postal)
 *  - hide preferred_mail_format, communication_style_id, sic_code
 */
class CRM_Uimods_MinorChanges {

  public static function pageRunHook(&$page) {
    // add general UI mods
    $script2 = file_get_contents(__DIR__ . '/../../js/summary_view_mods.js');
    CRM_Core_Region::instance('page-footer')->add(array(
      'script' => $script2,
      ));
  }
}