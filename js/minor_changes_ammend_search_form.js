/*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: B. Endres  (endres@systopia.de)                |
|         P. Batroff (batroff@systopia.de)               |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+-------------------------------------------------------*/

/**
 * Ammend the menu value for sending emails to Participants
 * TODO: Obsolete in 5.4 (probably in 5.3 as well)
 */
cj('option[value=6]').each(function() {
    if (cj(this).text() == 'E-Mail - jetzt senden') {
        cj(this).text("E-Mail - jetzt senden (an 50 oder weniger)")
    }
});