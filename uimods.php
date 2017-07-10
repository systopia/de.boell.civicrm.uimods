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

require_once 'uimods.civix.php';

/**
 * Hook implementation: If custom organisation name is changed -> update organization_name
 */
function uimods_civicrm_custom($op, $groupID, $entityID, &$params) {
  if ($op == 'edit') {
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

/**
 * Hook implementation: Inject JS code adjusting summary view
 */
function uimods_civicrm_pageRun(&$page) {
  if ($page->getVar('_name') == 'CRM_Contact_Page_View_Summary') {
    // this is the right view -> inject JS
    $orgname_group_id = CRM_Uimods_Config::getOrgnameGroupID();
    $script = file_get_contents(__DIR__ . '/js/organisation_name.js');
    $script = str_replace('ORGNAME_GROUP_ID', $orgname_group_id, $script);
    CRM_Core_Region::instance('page-footer')->add(array(
      'script' => $script,
      ));
    $script2 = file_get_contents(__DIR__ . '/js/summary_view_mods.js');
    CRM_Core_Region::instance('page-footer')->add(array(
      'script' => $script2,
      ));

  }
}

/**
 * Hook implementation: Inject JS code into create/edit form
 */
function uimods_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Contact') {
    $contact_type = CRM_Utils_Array::value('ct', $_REQUEST);
    $contact_id   = CRM_Utils_Array::value('cid', $_REQUEST);
    if ($contact_id) {
      // this is an update -> look up contact type
      $contact_type = civicrm_api3('Contact', 'getvalue', array(
        'id' => $contact_id,
        'return' => 'contact_type'));
    }

    if ($contact_type == 'Organization') {
      $script = file_get_contents(__DIR__ . '/js/organisation_create.js');
      $script = str_replace('OGRNAME_ROW1', CRM_Uimods_Config::getOrgnameField(1), $script);
      $script = str_replace('OGRNAME_ROW2', CRM_Uimods_Config::getOrgnameField(2), $script);
      CRM_Core_Region::instance('page-footer')->add(array(
        'script' => $script,
        ));
    }
  }
}

/**
 * Hook implementation: New Tokens
 */
function uimods_civicrm_tokens( &$tokens ) {
  CRM_Uimods_NameTokens::addTokens($tokens);
  CRM_Uimods_AddressTokens::addTokens($tokens);
}

/**
 * Hook implementation: New Tokens
 */
function uimods_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
  CRM_Uimods_NameTokens::tokenValues($values, $cids, $job, $tokens, $context);
  CRM_Uimods_AddressTokens::tokenValues($values, $cids, $job, $tokens, $context);
}


/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function uimods_civicrm_config(&$config) {
  _uimods_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function uimods_civicrm_xmlMenu(&$files) {
  _uimods_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function uimods_civicrm_install() {
  _uimods_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function uimods_civicrm_postInstall() {
  _uimods_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function uimods_civicrm_uninstall() {
  _uimods_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function uimods_civicrm_enable() {
  _uimods_civix_civicrm_enable();

  require_once 'CRM/Utils/CustomData.php';
  $customData = new CRM_Utils_CustomData('de.boell.civicrm.uimods');
  $customData->syncCustomGroup(__DIR__ . '/resources/custom_group.json');
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function uimods_civicrm_disable() {
  _uimods_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function uimods_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _uimods_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function uimods_civicrm_managed(&$entities) {
  _uimods_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function uimods_civicrm_caseTypes(&$caseTypes) {
  _uimods_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function uimods_civicrm_angularModules(&$angularModules) {
  _uimods_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function uimods_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _uimods_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

