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
 * Basic configuration values
 */
class CRM_Uimods_Config {

  private static $orgname_group_id = NULL;
  private static $orgname_fields   = NULL;

  /**
   * get the "geschäftlich" location type id
   */
  public static function getBusinessLocationType() {
    return 8;
  }

  /**
   * get the emplyer relationship type id
   */
  public static function getEmployerRelationshipID() {
    return 5;
  }

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

  /**
   * return the field name of the first field as
   * API parameter, i.e. "custom_xx"
   *
   * @param $number  only supposed to be 1 or 2
   */
  public static function getOrgnameField($number) {
    $fields = self::getOrgnameFields();
    foreach ($fields as $field) {
      if ($field['name'] == "organisation_name_{$number}") {
        return "custom_{$field['id']}";
      }
    }
    return NULL;
  }

  /**
   * get all the fields from the orgname field group
   */
  public static function getOrgnameFields() {
    if (self::$orgname_fields == NULL) {
      $query = civicrm_api3('CustomField', 'get', array(
        'custom_group_id' => self::getOrgnameGroupID(),
        'options'         => array('limit' => 0),
        ));
      self::$orgname_fields = $query['values'];
    }
    return self::$orgname_fields;
  }

}