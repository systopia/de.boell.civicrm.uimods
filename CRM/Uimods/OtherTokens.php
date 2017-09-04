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

class CRM_Uimods_OtherTokens {

  /**
   * Handles civicrm_tokens hook
   * @see https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_tokens
   */
  public static function addTokens(&$tokens) {
    $tokens['datum'] = array(
      'datum.kurz' => 'aktuelles Datum (kurz)',
      'datum.lang' => 'aktuelles Datum (lang)',
    );
  }

  /**
   * Handles civicrm_tokenValues hook
   * @param $values - array of values, keyed by contact id
   * @param $cids - array of contactIDs that the system needs values for.
   * @param $job - the job_id
   * @param $tokens - tokens used in the mailing - use this to check whether a token is being used and avoid fetching data for unneeded tokens
   * @param $context - the class name
   *
   * @see https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_tokenValues
   */
  public static function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    if (!empty($tokens['datum'])) {
      $oldlocale = setlocale(LC_ALL, 0);
      setlocale(LC_ALL, 'de_DE');
      $datum = array(
        'datum.kurz' => strftime("%d.%m.%Y"),
        'datum.lang' => strftime("%A, der %d. %B %Y"),
      );
      setlocale(LC_ALL, $oldlocale);
      foreach ($cids as $cid) {
        $values[$cid] = empty($values[$cid]) ? $datum : $values[$cid] + $datum;
      }
    }
  }
}