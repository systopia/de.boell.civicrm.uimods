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


/**
 * adjustments for the contact summary view
 */
function uimods_adjustSummaryView() {
  // hide some fields (see #5283)
  cj("div.crm-contact-email_greeting_display").parent().hide();
  cj("div.crm-contact-postal_greeting_display").parent().hide();
  cj("div.crm-contact-preferred_mail_format").parent().hide();
  cj("input[name=communication_style_id]").parent().hide();
  cj("div.crm-contact_external_identifier_label").parent().hide();
  cj("div.crm-contact-sic_code").parent().hide();

  // hide 'Kontaktherkunft' edit options
  cj("#records-__CUSTOM-GROUP-ID__ tbody tr").each(function() {cj(this).find("td:eq(4)").remove();});
}

cj(document).ready(function () {
  // call adjustment once
  uimods_adjustSummaryView();

  // inject data dependency
  cj(document).bind("ajaxComplete", uimods_adjustSummaryView);
});