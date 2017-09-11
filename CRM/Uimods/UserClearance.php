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
      return;
    }
    // get the user categories and sources from civi and save them locally
    $this->getUserOptionGroups();

    // add date, prefilled with current date
    $this->form->add(
      'datepicker',
      'user_clearance_date',
      'Kontakherkunft Datum',
      array('class' => 'some-css-class'),
      TRUE,
      array('time' => FALSE)  // date picker options, remove the time
    );
    // make form element mandatory
    $this->form->addRule(
      'user_clearance_date',
      ts('This field is required.'),
      'required'
    );

    // add category dropdown from option group
    // ==> how do I get option group? CiviAPIS
    $this->form->add('select',
      "user_clearance_category",
      'Kategorie',
      $this->category2label,
      FALSE,
      array('class' => 'user-category')
    );
    // make form element mandatory
    $this->form->addRule(
      'user_clearance_category',
      ts('This field is required.'),
      'required'
    );

    // add source category
    $this->form->add('select',
      "user_clearance_source",
      'Quelle',
      $this->sources2label,
      FALSE,
      array('class' => 'user-source')
    );
    // make form element mandatory
    $this->form->addRule(
      'user_clearance_source',
      ts('This field is required.'),
      'required'
    );
    // remark (note)
    $this->form->add(
      'text',
      "user_clearance_note",
      'Kontakherkunft Anmerkung'
    );

    // set default values
    $this->setDefaultDropDownValues();

    // add template path for these fields
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "CRM/Uimods/UserClearanceContactForm.tpl"
    ));
  }

  /**
   * handles the validate form hook action
   */
  public function validateFormHook(&$fields, &$files, &$errors) {
    if (!empty(self::$cid)) {
      // nothing to do here, we edited the contact
      return;
    }
    $category = CRM_Utils_Array::value( 'user_clearance_category', $fields );
    if (!$category || $category == '0') {
      $errors['user_clearance_category'] = 'Kategorie ist ein Pflichtfeld';
    }
    $source = CRM_Utils_Array::value( 'user_clearance_source', $fields );
    if (!$source || $source == '0') {
      $errors['user_clearance_source'] = ts( 'Quelle ist ein Pflichtfeld' );
    }
    $contact_origin = CRM_Utils_Array::value( 'user_clearance_note', $fields );
    if (strlen($contact_origin) > 60) {
      $errors['user_clearance_note'] = ts( 'Die Kontakt Herkunft darf nur 60 Zeichen lang sein.' );
    }
  }

  /**
   * handles the post process hook action
   */
  public function postProcessHook() {

    if (!empty(self::$cid)) {
      // nothing to do here, we edited the contact
      return;
    }

    $values = $this->form->exportValues();

    // create parameter array
    $params = array(
      'id'                                                    => $this->form->_contactId,
      'nutzungsberechtigung.nutzungsberechtigung_datum'       => $values['user_clearance_date'],
      'nutzungsberechtigung.nutzungsberechtigung_quelle'      => $this->getUserClearanceValueOption($values['user_clearance_source']),
      'nutzungsberechtigung.nutzungsberechtigung_kategorie'   => $this->getUserClearanceValueOption($values['user_clearance_category']),
      'nutzungsberechtigung.nutzungsberechtigung_anmerkung'   => $values['user_clearance_note'],
    );
    // resolve option IDs to the corrosponding custom_xx names
    CRM_Uimods_CustomData::resolveCustomFields($params);

    // commit to DB
    civicrm_api3('Contact', 'create', $params);
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
    // this shall be the default value
    $this->category2label['0'] = "";

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
    // this shall be the default value and be empty
    $this->sources2label['0'] = "";
  }

  /**
   * sets the default values in the form dropdown elements
   */
  private function setDefaultDropDownValues() {
    $defaults = array(
      'user_clearance_category'   => '0',
      'user_clearance_source'     => '0',
      'user_clearance_date'       => date("Y-m-d"),
    );
    $this->form->setDefaults($defaults);
  }

  /**
   * Gets the value for the optionValueId for the optionGroup
   *    --> needed for setting the optionvalue in a contact
   * @param $optionValueId
   *
   * @return the value of the option Id
   */
  private function getUserClearanceValueOption($optionValueId) {
    $result = civicrm_api3('OptionValue', 'getsingle', array(
      'id' => $optionValueId,
    ));
    return $result['value'];
  }
}