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

define('LOCATION_TYPE_GESCHAEFTLICH', '8');

/**
 * Implements special treatment of the employer relationship
 * in association with shared addresses
 */
class CRM_Uimods_EmployerRelationship {

  protected static $_currently_editing_address_location_type = NULL;

  /**
   * Check if the address is relevant and then mark it as such
   */
  public static function setAddressEditStart($op, $id, $params) {
    if ($op == 'edit' || $op == 'create') {
      if (!empty($params['location_type_id'])) {
        self::$_currently_editing_address_location_type = $params['location_type_id'];
      } else {
        error_log("OOPS");
        self::$_currently_editing_address_location_type = 'UNKNOWN';
      }
    }
  }

  /**
   * After the address edit we're not responsible any more
   */
  public static function setAddressEditFinish($op, $objectId, $objectRef) {
    self::$_currently_editing_address_location_type = NULL;
  }

  /**
   * check if we maybe want to delete this realtionship
   * @see https://projekte.systopia.de/redmine/issues/5193
   */
  public static function postProcessRelationshipCreate($op, $objectId, $objectRef) {
    if (self::$_currently_editing_address_location_type) {
      // only keep relationship for location type "GESCHÄFTLICH"
      if (self::$_currently_editing_address_location_type != LOCATION_TYPE_GESCHAEFTLICH) {
        self::$_currently_editing_address_location_type = NULL;
        // otherwise: delete
        civicrm_api3('Relationship', 'delete', array('id' => $objectId));
      }
    }
  }
}