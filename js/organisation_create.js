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

cj(line_1).change(compile_organisation_name);
cj(line_2).change(compile_organisation_name);
