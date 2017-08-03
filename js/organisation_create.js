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

// IDs - will be replaced upon injection
var line_1  = "input[id^=OGRNAME_ROW1_]";
var label_1 = "label[for^=OGRNAME_ROW1_]";
var line_2  = "input[id^=OGRNAME_ROW2_]";
var label_2 = "label[for^=OGRNAME_ROW2_]";

// move some stuff around
cj("#organization_name").after(cj(line_2));
cj("#organization_name").after("<br/>");
cj("#organization_name").after(cj(label_2));
cj("#organization_name").after("<br/>");

cj("#organization_name").after(cj(line_1));
cj("#organization_name").after("<br/>");
cj("#organization_name").after("<span class=\"crm-marker\" title=\"Dieses Feld ist ein Pflichtfeld.\"> *</span>");
cj("#organization_name").after(cj(label_1));
cj("#organization_name").after("<br/>");

cj("#legal_name").after(cj("#sic_code"))
cj("#legal_name").after("<br/>");
cj("#legal_name").after(cj("label[for=sic_code]"))
cj("#legal_name").after("<br/>");

cj("#legal_name").after(cj("#nick_name"))
cj("#legal_name").after("<br/>");
cj("#legal_name").after(cj("label[for=nick_name]"))
cj("#legal_name").after("<br/>");

// remove old block
cj("#organisation_name").remove();

// adjust size
cj(line_1).addClass("big");
cj(line_2).addClass("big");

// live update organization_name
function compile_organisation_name() {
  var organisation_name = cj(line_1).val() + " " + cj(line_2).val();
  organisation_name = organisation_name.trim();
  cj("#organization_name").val(organisation_name);
}

cj(line_1).addClass("required");
cj(line_1).change(compile_organisation_name);
cj(line_2).change(compile_organisation_name);

// move the Organization_name field after the input fields
cj("label[for='organization_name']").parent().find('br').first().remove();
cj("label[for='organization_name']").parent().find('br').first().remove();

cj("<br>").insertAfter(cj(line_2));
cj('#organization_name').insertAfter(cj(line_2));
cj("<br>").insertAfter(cj(line_2));
cj("label[for='organization_name']").insertAfter(cj(line_2));
cj("<br>").insertAfter(cj(line_2));