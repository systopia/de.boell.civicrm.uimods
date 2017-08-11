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


// hide Anredestil in form
cj("input[name=communication_style_id]").parent().parent().hide();

// hide help icon
cj("label[for='email_greeting_id']").closest("tr").find("td:last-child").hide()
// hide Grussformeln
cj("label[for='email_greeting_id']").parent().hide();
cj("label[for='postal_greeting_id']").parent().hide();

// input fields
cj("#email_greeting").parent().hide();
cj("#postal_greeting").parent().hide();
