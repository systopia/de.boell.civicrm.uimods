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

// hide email
cj("label[for='preferred_mail_format']").parent().parent().hide();
//hide SIC code
cj("label[for='sic_code']").parent().parent().hide();

// hide Pseudonym & 'gesetzlicher Name' [#5525]
cj("label[for='nick_name']").parent().parent().hide();
cj("label[for='legal_name']").parent().parent().hide();