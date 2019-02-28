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
 * Implements overrides to the default page/form permissions
 */
class CRM_Uimods_AdjustPagePermissions {

  /**
   * Handle the civicrm_alterMenu hook
   */
  public static function handleAlterMenu(&$items) {
    /* Mailing stats, disabled with HBS-5925
    /                re-enabled with HBS-8087
    if (isset($items['civicrm/mailing/report'])) {
      $items['civicrm/mailing/report']['access_arguments'] =
        array(array('access Report Criteria', 'administer CiviCRM'), "or");
    }
    // Mailing stats, see HBS-5925
    if (isset($items['civicrm/mailing/report/event'])) {
      $items['civicrm/mailing/report/event']['access_arguments'] =
        array(array('access Report Criteria', 'administer CiviCRM'), "or");
    }*/
  }

}
