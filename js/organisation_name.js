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

// find our stuff
var orgname_group = cj("#custom-set-content-ORGNAME_GROUP_ID");
var orgname_block = orgname_group.parent().parent();

// move inner part to upper right
cj("div.contactTopBar > div.contactCardLeft").prepend(orgname_block);

// update contact name when changed
orgname_group.attr('data-dependent-fields', '["#crm-contactname-content"]');
