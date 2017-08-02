/*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| Author: P. Batroff (batroff@systopia.de)               |
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

// array with blacklisted tokens. Add tokens in here if more tokens need blacklisting
var blacklisted_tokens = [
    "{contact.postal_greeting}",
    "{contact.email_greeting}"
];

// array with blacklisted label patterns. Will be matched by startsWith
var blacklisted_texts = [
    "Protected "
];

var tokens = cj("input.crm-token-selector").closest("form").data("tokens");

var arrayLength = tokens.length;
// iterate over all objects
for (var i = 0; i < arrayLength; i++) {
    var field_length = tokens[i].children.length;
    // iterate over the children array with the values backwards
    // so the index wont be messed up
    for (j=field_length-1; j>=0; j--) {
        if (cj.inArray(tokens[i].children[j].id, blacklisted_tokens) != -1) {
            // remove values (via splice) from array
            tokens[i].children.splice(j, 1);
        } else {
            // check against blacklisted texts
            for (var n = 0; n < blacklisted_texts.length; n++) {
                if ((tokens[i].children[j].text).startsWith(blacklisted_texts[n])) {
                    tokens[i].children.splice(j, 1);
                }
            }
        }
    }
}