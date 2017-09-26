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
 * Implement pre hook
 */
function uimods_civicrm_pre($op, $objectName, $id, &$params) {
  if ($objectName == 'Address') {
    CRM_Uimods_EmployerRelationship::handleAddressPre($op, $id, $params);
  } elseif ($objectName == 'Relationship') {
    CRM_Uimods_EmployerRelationship::handleRelationshipPre($op, $id, $params);
  }
}

/**
 * Implement post hook
 */
function uimods_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName == 'Address') {
    CRM_Uimods_EmployerRelationship::handleAddressPost($op, $objectId, $objectRef);
  } elseif ($objectName == 'Relationship') {
    CRM_Uimods_EmployerRelationship::handleRelationshipPost($op, $objectId, $objectRef);
  }
}

/**
 * Hook implementation: If custom organisation name is changed -> update organization_name
 */
function uimods_civicrm_custom($op, $groupID, $entityID, &$params) {
  if ($op == 'edit') {
    CRM_Uimods_OrganisationName::customHook($op, $groupID, $entityID, $params);
  }
}

/**
 * Hook implementation: Inject JS code adjusting summary view
 */
function uimods_civicrm_pageRun(&$page) {
  $page_name = $page->getVar('_name');
  switch ($page_name) {
    case 'CRM_Contact_Page_View_Summary':
      CRM_Uimods_OrganisationName::pageRunHook($page);
      CRM_Uimods_MinorChanges::pageRunHook($page);
      break;
    case 'Civi\\Angular\\Page\\Main':
      CRM_Uimods_MinorChanges::editTokens();
      break;
  }
}

/**
 * Implements hook_civicrm_buildForm()
 * @param $formName
 * @param $form
 */
function uimods_civicrm_buildForm($formName, &$form) {
  error_log("debug form: {$formName}");
  switch ($formName) {
    case 'CRM_Contact_Form_Contact':
      CRM_Uimods_OrganisationName::buildFormHook($formName, $form);
      CRM_Uimods_MinorChanges::buildFormHook($formName, $form);

      require_once 'CRM/Uimods/UserClearance.php';
      if (!empty($form->_contactId)) {
        $userClearance = new CRM_Uimods_UserClearance($formName, $form, $form->_contactId);
      } else {
        $userClearance = new CRM_Uimods_UserClearance($formName, $form);
      }
      $userClearance->buildFormHook();

      break;
    // TODO: more forms to come here, tokens are everywhere!
    case 'CRM_Contact_Form_Task_PDF':
    case 'CRM_Contact_Form_Task_Email':
      CRM_Uimods_MinorChanges::editTokens();
      break;
    case 'CRM_Contact_Form_Inline_ContactInfo':
    case 'CRM_Contact_Form_Inline_CommunicationPreferences':
      CRM_Uimods_MinorChanges::buildFormHook_InlineEdit();
      break;
    case 'CRM_Contact_Form_Task_AddToGroup':
      if (!CRM_Core_Permission::check('edit groups')) {
        CRM_Core_Region::instance('page-footer')->add(array(
          'script' => file_get_contents(__DIR__ . '/js/task_addtogroup_mods.js'),
          ));
      }
      break;
    // "Quick contact add Oraganisation (5680)
    case "CRM_Profile_Form_Edit":
      CRM_Uimods_OrganisationName::buildFormHook_quickOrganisationCreate($formName, $form);
      break;
    case "Civi\Angular\Page\Main":
      break;
    default:
      break;
  }
}

/**
 * Hook implementation: New Tokens
 */
function uimods_civicrm_tokens( &$tokens ) {
  CRM_Uimods_NameTokens::addTokens($tokens);
  CRM_Uimods_AddressTokens::addTokens($tokens);
  CRM_Uimods_OtherTokens::addTokens($tokens);
}

/**
 * Hook implementation: New Tokens
 */
function uimods_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
  CRM_Uimods_NameTokens::tokenValues($values, $cids, $job, $tokens, $context);
  CRM_Uimods_AddressTokens::tokenValues($values, $cids, $job, $tokens, $context);
  CRM_Uimods_OtherTokens::tokenValues($values, $cids, $job, $tokens, $context);
}

/**
 * Hook implementation: API Wrapper
 */
function uimods_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  CRM_Uimods_UserClearanceApiWrapper::registerWrappers($wrappers, $apiRequest);
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

  require_once 'CRM/Uimods/CustomData.php';
  $customData = new CRM_Uimods_CustomData('de.boell.civicrm.uimods');
  $customData->syncCustomGroup(__DIR__ . '/resources/custom_group_contact_extra.json');
  $customData->syncCustomGroup(__DIR__ . '/resources/custom_group_organisationsname.json');
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


/**
 * Implements hook_civicrm_searchTasks().
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_searchTasks
 */
function uimods_civicrm_searchTasks( $objectType, &$tasks ) {
  if (!CRM_Core_Permission::check('edit groups')) {
    foreach (array_keys($tasks) as $key) {
      if ($tasks[$key]['class'] == 'CRM_Contact_Form_Task_SaveSearch') {
        unset($tasks[$key]);
      }
    }
  }
}

/**
 * Implements hook_civicrm_validateForm()
 * @param $formName
 * @param $fields
 * @param $files
 * @param $form
 * @param $errors
 */
function uimods_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Contact_Form_Contact') {
    require_once 'CRM/Uimods/UserClearance.php';
    $userClearance = new CRM_Uimods_UserClearance($formName, $form);
    // TODO: do stuff here
    $userClearance->validateFormHook($fields, $files, $errors);

  }
}

/**
 * Implements hook_civicrm_postProcess()
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function uimods_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Contact') {
    require_once 'CRM/Uimods/UserClearance.php';
    $userClearance = new CRM_Uimods_UserClearance($formName, $form);
    // TODO: do stuff here
    $userClearance->postProcessHook();
  }
}