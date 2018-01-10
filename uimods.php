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
  } elseif ($objectName == 'Mailing') {
    CRM_Uimods_MailingPermission::check($op, $objectName, $id, $params);
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
 * Hook implementation: define permissions
 */
function uimods_civicrm_permission(&$permissions) {
  CRM_Uimods_MailingPermission::specify($permissions);
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
  switch ($formName) {
    case 'CRM_Contact_Form_Contact':
      CRM_Uimods_OrganisationName::buildFormHook($formName, $form);
      CRM_Uimods_MinorChanges::buildFormHook($formName, $form);
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
  if (class_exists('CRM_Gdprx_ConsentApiWrapper')) {
    if ($apiRequest['entity'] == 'RemoteRegistration' && $apiRequest['action'] == 'register') {
      $wrappers[] = new CRM_Gdprx_ConsentApiWrapper(
        'RemoteRegistration',
        'register',
        'Anmeldung Veranstaltung',
        'Double-opt-in',
        'request::consent_note');

    } elseif ($apiRequest['entity'] == 'RemoteGroup' && $apiRequest['action'] == 'subscribe') {
      $wrappers[] = new CRM_Gdprx_ConsentApiWrapper(
        'RemoteGroup',
        'subscribe',
        'Anmeldung Gruppe',
        'Double-opt-in',
        'request::consent_note',
        'now',
        'reply::id'
      );
    }
  }
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
 * Implements hook_civicrm_alterMailParams().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterMailParams/
 */
function uimods_civicrm_alterMailParams(&$params, $context) {
  if ($context == 'civimail') {
    // strip email 'toName', see HBS-6300
    if (isset($params['toName'])) {
      unset($params['toName']);
    }
  }
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
