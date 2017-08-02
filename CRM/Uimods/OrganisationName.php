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
 * UI Modifications related to showing the
 * two-line organisation name
 */
class CRM_Uimods_OrganisationName {

  /**
   * Adjust Contact create/edit form
   */
  public static function buildFormHook($formName, &$form) {
    $contact_type = CRM_Utils_Array::value('ct', $_REQUEST);
    $contact_id   = CRM_Utils_Array::value('cid', $_REQUEST);
    if ($contact_id) {
      // this is an update -> look up contact type
      $contact_type = civicrm_api3('Contact', 'getvalue', array(
        'id' => $contact_id,
        'return' => 'contact_type'));
    }

    if ($contact_type == 'Organization' || $form->_contactType == 'Organization') {
      $script = file_get_contents(__DIR__ . '/../../js/organisation_create.js');
      $script = str_replace('OGRNAME_ROW1', CRM_Uimods_Config::getOrgnameField(1), $script);
      $script = str_replace('OGRNAME_ROW2', CRM_Uimods_Config::getOrgnameField(2), $script);
      CRM_Core_Region::instance('page-footer')->add(array(
        'script' => $script,
        ));
    }
  }

  /**
   * Adjust Contact Summary View
   */
  public static function pageRunHook($page) {
    $orgname_group_id = CRM_Uimods_Config::getOrgnameGroupID();
    $script = file_get_contents(__DIR__ . '/../../js/organisation_name.js');
    $script = str_replace('ORGNAME_GROUP_ID', $orgname_group_id, $script);
    CRM_Core_Region::instance('page-footer')->add(array(
      'script' => $script,
      ));
  }

  /**
   * Recalculate organisation_name when either of the two-line name is edited
   */
  public static function customHook($op, $groupID, $entityID, &$params) {
    if ($groupID == CRM_Uimods_Config::getOrgnameGroupID()) {
      // re-calculate organisation name
      $field_1_id = CRM_Core_BAO_CustomField::getCustomFieldID('organisation_name_1', 'organisation_name');
      $field_2_id = CRM_Core_BAO_CustomField::getCustomFieldID('organisation_name_2', 'organisation_name');
      $values = civicrm_api3('Contact', 'getsingle', array(
          'id'     => $entityID,
          'return' => "custom_{$field_1_id},custom_{$field_2_id},organization_name"));

      // render new name
      $new_name = trim(trim($values["custom_{$field_1_id}"]) . ' ' . trim($values["custom_{$field_2_id}"]));
      if ($new_name != $values['organization_name']) {
        // store new name
        civicrm_api3('Contact', 'create', array(
          'id' => $entityID,
          'organization_name' => $new_name));
      }
    }
  }
}