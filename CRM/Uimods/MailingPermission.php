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

class CRM_Uimods_MailingPermission {

  /**
   * Specify the new setting
   */
  public static function specify(&$permissions) {
    $permissions['mailing approval'] = $prefix . ts('CiviMail (Massenmailings): Freigabe');
  }

  /**
   * check the permission
   */
  public static function check($op, $objectName, $id, $params) {
    if ($objectName == 'Mailing') {
      if (!empty($params['approval_status_id'])) {
        // somebody submitted the approval_status_id!
        // check if this would be a change:
        if ($id) {
          $mailing = civicrm_api3('Mailing', 'getsingle', array(
            'id'     => $id,
            'return' => 'approval_status_id'));
          if ($mailing['approval_status_id'] == $params['approval_status_id']) {
            // oops, not a change after all
            return;
          }
        }

        // if we get here, somebody really wants to set approval_status_id!
        if (!CRM_Core_Permission::check('mailing approval')) {
          // ...and they don't have the appropriate permission. ABORT!!
          throw new Exception("Sie haben nicht die Berechtigung Rundschreiben freizugeben. Speichern Sie das Rundschreiben als Entwurf und bitten Sie jemanden mit den enstsprechenden Berechtigungen, die Freischaltung vorzunehmen.", 1);
        }
      }
    }
  }
}