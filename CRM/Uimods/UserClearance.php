<?php
/*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: B. Endres  (endres@systopia.de)                |
| Author: P. Batroff (batroff@systopia.de)               |
| Source: http://www.systopia.de/                        |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

class CRM_Uimods_UserClearance {

  private $formName       = NULL;
  private $form           = NULL;
  private $category2label = NULL;
  private $sources2label  = NULL;

  private static $cid     = NULL;

  /**
   * CRM_Uimods_UserClearance constructor.
   */
  public function __construct($formName = NULL, &$form = NULL, $cid = NULL) {
    $this->form = $form;
    $this->formName = $formName;
    if ($cid != NULL) {
      self::$cid = $cid;
    }

  }

  /**
   * handles the build form hook action
   */
  public function buildFormHook() {
    if (!empty(self::$cid)) {
      // we are in edit mode, nothing to do here!
      error_log("nothin to do here ... [buildFormHook]");
      return;
    }
    // get the user categories and sources from civi and save them locally
    $this->getUserOptionGroups();

    // add date, prefilled with current date
    $this->form->add(
      'datepicker',
      'user_clearance_date',
      'Nutzungsberechtigung Datum'
    );

    // add category dropdown from option group
    // ==> how do I get option group? CiviAPIS
    $this->form->add('select',
      "user_clearence_category",
      'Kategorie',
      $this->category2label,
      FALSE,
      array('class' => 'user-category')
    );

    // add source category
    $this->form->add('select',
      "user_clearence_source",
      'Quelle',
      $this->sources2label,
      FALSE,
      array('class' => 'user-source')
    );
    // remark (note)
    $this->form->add(
      'text',
      "user_clearance_note",
      'Nutzerberechtigung Anmerkung'
    );
    // add template path for these fields
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "CRM/Uimods/UserClearanceContactForm.tpl"
    ));
  }

  /**
   * handles the alter template hook action
   */
  public function alterTemplateHook() {

  }
  /**
   * handles the validate form hook action
   */
  public function validateFormHook() {
    if (!empty(self::$cid)) {
      // nothing to do here, we edited the contact
      error_log("nothin to do here ... [validateFormHook]");
      return;
  }
  }

  /**
   * handles the post process hook action
   */
  public function postProcessHook() {

    if (!empty(self::$cid)) {
      // nothing to do here, we edited the contact
      error_log("nothin to do here ... [post hook]");
      return;
    }

    CRM_Uimods_CustomData::resolveCustomFields($params);

  }

////////////////////////////////////////////////////////////////////////////////
/// internal helper functions
////////////////////////////////////////////////////////////////////////////////

  /**
   * get the option groups for sources and categories
   * and save them as a local attribute as id => label array
   */
  private function getUserOptionGroups() {
    //
    $user_categories = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'option_group_id' => "nutzungsberechtigung_kategorie",
      'options' => array(
        'limit' => 100
      ),
    ))['values'];
    foreach ($user_categories as $category) {
      $this->category2label[$category['id']] = $category['label'];
    }

    $user_sources = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'option_group_id' => "nutzungsberechtigung_quelle",
      'options' => array(
        'limit' => 1000
      ),
    ))['values'];
    foreach ($user_sources as $source) {
      $this->sources2label[$source['id']] = $source['label'];
    }
  }
}