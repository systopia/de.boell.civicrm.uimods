// find our stuff
var orgname_group = cj("#custom-set-content-ORGNAME_GROUP_ID");
var orgname_block = orgname_group.parent().parent();

// move inner part to upper right
cj("div.contactTopBar > div.contactCardRight").prepend(orgname_block);
