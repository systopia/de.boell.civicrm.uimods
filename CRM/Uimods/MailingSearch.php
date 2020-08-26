<?php
/*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: P.Batroff (batroff@systopia.de)                |
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
 *  - Remove is_archive search attribute
 * #11961
 */
class CRM_Uimods_MailingSearch {

  /**
   * Inject Javascript to remove is archive option
   * in civicrm/mailing/browse/archived
   * see #11961
   */
  public static function removeIsArchiveSearchOption() {
    $script = file_get_contents(__DIR__ . '/../../js/is_archive_mods.js');
    CRM_Core_Region::instance('page-footer')->add(array(
      'script' => $script,
    ));
  }
}