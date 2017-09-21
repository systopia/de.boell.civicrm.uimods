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

// field names
var line_1  = "input[id^=OGRNAME_ROW1]";
var line_2  = "input[id^=OGRNAME_ROW2]";

// callback to joined organization name
function compile_organisation_name() {
    var organisation_name = cj(line_1).val() + " " + cj(line_2).val();
    organisation_name = organisation_name.trim();
    cj("#organization_name").val(organisation_name);
}

// listen for changes
cj(line_1).change(compile_organisation_name);
cj(line_2).change(compile_organisation_name);