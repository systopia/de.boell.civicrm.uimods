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

  private $formName = NULL;
  private $form = NULL;

  /**
   * CRM_Uimods_UserClearance constructor.
   */
  public function __construct($formName = NULL, &$form = NULL) {
    $this->form = $form;
    $this->formName = $formName;
  }

  /**
   * handles the build form hook action
   */
  public function buildFormHook() {
    // TODO: add date, prefilled with current date

    // TODO: add category dropdown from option group
    // ==> how do I get option group? CiviAPIS

    // TODO: add source category

    // TODO: remark (note)
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

  }

  /**
   * handles the post process hook action
   */
  public function postProcessHook() {
    $result = civicrm_api3('Contact', 'get', array(
      'sequential' => 1,
      'first_name' => "CARLOS",
    ));
    error_log("pbaDebug: " . json_encode($result));
  }
}