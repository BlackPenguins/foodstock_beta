function loadItemModals(type) {
    // Open modals with buttons
    $("#add_item_" + type + "_button").click( function() {
            $('#add_item_' + type).dialog('open');
             return false;
    });
    
    $("#edit_item_" + type + "_button").click( function() {
            $('#edit_item_' + type).dialog('open');
             return false;
    });
    
    $("#restock_item_" + type + "_button").click( function() {
            $('#restock_item_' + type).dialog('open');
             return false;
    });
    
    
    
    $("#inventory_" + type + "_button").click( function() {
            $('#inventory_' + type).dialog('open');
             return false;
    });

    // Build forms
    $( "#add_item_" + type ).dialog( {
            autoOpen: false,
            width: 500,
            modal: true,
            buttons: [
                        {
                            id: "Add_Item_" + type + "_Cancel",
                            text: "Cancel",
                            click: function() {
                                $(this).dialog("close");
                            } 
                        },
                        {
                            id:"Add_Food_" + type + "_Submit",
                            text: "Add " + type,
                            click: function() { 
                                $("#add_item_" + type + "_form").submit();
                            }
                        }
                    ]
    });    
    
    $( "#edit_item_" + type ).dialog( {
            autoOpen: false,
            width: 500,
            modal: true,
            buttons: [
                        {
                        	id: "Edit_Item_" + type + "_Cancel",
                            text: "Cancel",
                            click: function() {
                                $(this).dialog("close");
                            } 
                        },
                        {
                            id:"Edit_Item_" + type + "_Submit",
                            text: "Edit " + type,
                            click: function() { 
                                $("#edit_item_" + type + "_form").submit();
                            }
                        }
                    ]
    });    
    
    $( "#restock_item_" + type ).dialog( {
            autoOpen: false, 
            width: 500,
            modal: true,
            buttons: [
                        {
                            id: "Restock_Item_" + type + "_Cancel",
                            text: "Cancel",
                            click: function() {
                                $(this).dialog("close");
                            } 
                        },
                        {
                            id:"Restock_Item_" + type + "_Submit",
                            text: "Restock " + type,
                            click: function() { 
                                $("#restock_item_" + type + "_form").submit();
                            }
                        }
                    ]
    });
    
    
    
    $( "#inventory_" + type ).dialog( {
            autoOpen: false, 
            width: 800,
            modal: true,
            buttons: [
                        {
                            id: "Update_Item_" + type + "_Cancel",
                            text: "Cancel",
                            click: function() {
                                $(this).dialog("close");
                            } 
                        },
                        {
                            id:"Update_Item_" + type + "_Submit",
                            text: "Update " + type,
                            click: function() { 
                                $("#inventory_" + type + "_form").submit();
                            }
                        }
                    ]
    });
}

function loadSingleModals() {
    $("#payment_button").click( function() {
        $('#payment').dialog('open');
         return false;
    });
    
    $( "#payment" ).dialog( {
        autoOpen: false, 
        width: 500,
        modal: true,
        buttons: [
                    {
                        id: "Payment_Cancel",
                        text: "Cancel",
                        click: function() {
                            $(this).dialog("close");
                        } 
                    },
                    {
                        id:"Payment_Submit",
                        text: "Add Payment",
                        click: function() { 
                            $("#payment_form").submit();
                        }
                    }
                ]
    });
    
    $("#edit_user_button").click( function() {
        $('#edit_user').dialog('open');
         return false;
    });
    
    $( "#edit_user" ).dialog( {
        autoOpen: false, 
        width: 500,
        modal: true,
        buttons: [
                    {
                        id: "Edit_User_Cancel",
                        text: "Cancel",
                        click: function() {
                            $(this).dialog("close");
                        } 
                    },
                    {
                        id:"Edit_User_Submit",
                        text: "Save",
                        click: function() { 
                            $("#edit_user_form").submit();
                        }
                    }
                ]
    });
}

function loadUserModals() {
    $("#request_button").click( function() {
        $('#request').dialog('open');
         return false;
    });
    
    $( "#request" ).dialog( {
        autoOpen: false, 
        width: 500,
        modal: true,
        buttons: [
                    {
                        id: "Request_Cancel",
                        text: "Cancel",
                        click: function() {
                            $(this).dialog("close");
                        } 
                    },
                    {
                        id:"Request_Submit",
                        text: "Submit Request",
                        click: function() { 
                            $("#request_form").submit();
                        }
                    }
                ]
    });
}

//Put the function at a global level so it can be accessed from diff files
window.loadItemModals = loadItemModals;
window.loadSingleModals = loadSingleModals;
window.loadUserModals = loadUserModals;