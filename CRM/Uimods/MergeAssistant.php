<?php
/*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2020 SYSTOPIA                            |
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
 * Custom code for contact merges
 */
class CRM_Uimods_MergeAssistant
{

    /**
     * Checks the current tuple for merge warnings and injects them as a popup
     */
    public static function injectMergeWarnings($form)
    {
      $warnings = CRM_Uimods_MergeAssistant::getMergeWarnings($form->_cid, $form->_oid);
      foreach ($warnings as $warning) {
        CRM_Core_Session::setStatus($warning, "Achtung!", 'warn');
      }
    }

    /**
     * Calculate a set of merge warnings for the given contact pair
     *
     * @param integer $main_contact_id
     *    The main contact ID (i.e. the one prevailing)
     * @param integer $other_contact_id
     *    The other contact ID (i.e. the one to be deleted)
     *
     * @return array
     *    a list of merge warnings
     */
    public static function getMergeWarnings($main_contact_id, $other_contact_id)
    {
        $merge_warnings = [];

        // make sure this IDs are valid
        $main_contact_id  = (int) $main_contact_id;
        $other_contact_id = (int) $other_contact_id;
        if (empty($main_contact_id) || empty($other_contact_id)) {
            return $merge_warnings;
        }

        // check for shared address with the same type, see HBS-6456:
        //  step 1: get all addresses that have the type
        $conflicting_address_ids = [];
        $conflicting_address_query = CRM_Core_DAO::executeQuery("
          SELECT 
           main_address.id  AS main_address_id,
           other_address.id AS other_address_id
          FROM civicrm_address main_address
          LEFT JOIN civicrm_address other_address ON other_address.contact_id = {$other_contact_id}
                                                  AND other_address.location_type_id = main_address.location_type_id
          WHERE main_address.contact_id = {$main_contact_id}
            AND other_address.id IS NOT NULL;");
        while ($conflicting_address_query->fetch()) {
            $conflicting_address_ids[] = (int) $conflicting_address_query->main_address_id;
            $conflicting_address_ids[] = (int) $conflicting_address_query->other_address_id;
        }

        //  step 2: check if one of them is shared
        if (!empty($conflicting_address_ids)) {
            $conflicting_address_ids_string = implode(',', $conflicting_address_ids);
            $conflicting_addresses_shared = CRM_Core_DAO::singleValueQuery("
              SELECT COUNT(id) 
              FROM civicrm_address 
              WHERE master_id IN ({$conflicting_address_ids_string})");
            if ($conflicting_addresses_shared) {
                $merge_warnings[] = "Bei diesen beiden Kontakten gibt es widersprüchliche geteilte Adressen. Diese werden beim Zusammenführen beschädigt und müssen im Nachgang korrigiert werden. Siehe hier: TODO";
            }
        }

        return $merge_warnings;
    }
}