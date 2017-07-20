{*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: B. Endres  (endres@systopia.de)                |
| Author: P. Batroff (batroff@systopia.de)               |
| Source: http://www.systopia.de/                        |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*}

{capture assign=date}user_clearance_date{/capture}
<div class="crm-section">
    <p>Date: </p>
    {$form.$date.html}
</div>

{capture assign=note}user_clearance_note{/capture}
<div class="crm-section">
    <p>Note: </p>
    {$form.$note.html}
</div>

{capture assign=source}user_clearence_source{/capture}
<div class="crm-section">
    <p>Source: </p>
    {$form.$source.html}
</div>

{capture assign=category}user_clearence_category{/capture}
<div class="crm-section">
    <p>Category: </p>
    {$form.$category.html}
</div>