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

class CRM_Uimods_Config {

  private static $orgname_group_id = NULL;

  /**
   * get CustomGroup ID of the orgnisation_names
   */
  public static function getOrgnameGroupID() {
    if (self::$orgname_group_id == NULL) {
      $group = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'organisation_name'));
      self::$orgname_group_id = $group['id'];
    }
    return self::$orgname_group_id;
  }

}