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
<div id="user-clearance-date">
    {$form.$date.label}
    {$form.$date.html}
</div>

{capture assign=note}user_clearance_note{/capture}
<div id="user-clearance-note">
    {$form.$note.label}
    {$form.$note.html}
</div>

{capture assign=source}user_clearence_source{/capture}
<div id="user-clearance-source">
    {$form.$source.label}
    {$form.$source.html}
</div>

{capture assign=category}user_clearence_category{/capture}
<div id="user-clearance-category">
    {$form.$category.label}
    {$form.$category.html}
</div>


<script type="text/javascript">
// get variables

{literal}
// do js magics
    console.log("foooooooooooo");
    cj('#user-clearance-note').prependTo('#contactDetails');
    cj('#user-clearance-category').prependTo('#contactDetails');
    cj('#user-clearance-source').prependTo('#contactDetails');
    cj('#user-clearance-date').prependTo('#contactDetails');
</script>
{/literal}