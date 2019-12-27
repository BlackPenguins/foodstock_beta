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

    $("#refill_" + type + "_button").click( function() {
            $('#refill_' + type).dialog('open');
             return false;
    });
    
    $("#defective_item_" + type + "_button").click( function() {
        $('#defective_item_' + type).dialog('open');
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

    $( "#refill_" + type ).dialog( {
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
                                $("#refill_" + type + "_form").submit();
                            }
                        }
                    ]
    });
    
    $( "#defective_item_" + type ).dialog( {
        autoOpen: false, 
        width: 500,
        modal: true,
        buttons: [
                    {
                        id: "Defective_Item_" + type + "_Cancel",
                        text: "Cancel",
                        click: function() {
                            $(this).dialog("close");
                        } 
                    },
                    {
                        id:"Defective_Item_" + type + "_Submit",
                        text: "Defect " + type,
                        click: function() { 
                            $("#defective_item_" + type + "_form").submit();
                        }
                    }
                ]
});
}

function loadShoppingModal() {
    $("#shopping_button").click( function() {
        $('#shopping').dialog('open');
         return false;
    });
    
    $( "#shopping" ).dialog( {
        autoOpen: false, 
        width: 500,
        modal: true,
        buttons: [
                    {
                        id: "Shopping_Cancel",
                        text: "Cancel",
                        click: function() {
                            $(this).dialog("close");
                        } 
                    },
                    {
                        id:"Shopping_Submit",
                        text: "Add Shopping",
                        click: function() { 
                            $("#shopping_form").submit();
                        }
                    }
                ]
    });
}

function loadSingleModals() {
    console.log("Loading Single Modals.");
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

    $("#credit_user_button").click( function() {
        $('#credit_user').dialog('open');
        return false;
    });

    $( "#credit_user" ).dialog( {
        autoOpen: false,
        width: 500,
        modal: true,
        buttons: [
            {
                id: "Credit_User_Cancel",
                text: "Cancel",
                click: function() {
                    $(this).dialog("close");
                }
            },
            {
                id:"Credit_User_Submit",
                text: "Save",
                click: function() {
                    $("#credit_user_form").submit();
                }
            }
        ]
    });
}

function loadUserModals() {
    $("#request_item_button").click( function() {
        $('#request_item').dialog('open');
         return false;
    });
    
    $("#request_feature_button").click( function() {
        $('#request_feature').dialog('open');
         return false;
    });
    
    $("#report_bug_button").click( function() {
        $('#report_bug').dialog('open');
         return false;
    });
    
    $( "#request_item" ).dialog( {
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
                            $("#request_item_form").submit();
                        }
                    }
                ]
    });
    
    $( "#request_feature" ).dialog( {
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
                            $("#request_feature_form").submit();
                        }
                    }
                ]
    });
    
    $( "#report_bug" ).dialog( {
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
                        text: "Submit Report",
                        click: function() { 
                            $("#report_bug_form").submit();
                        }
                    }
                ]
    });
}

//Put the function at a global level so it can be accessed from diff files
window.loadItemModals = loadItemModals;
window.loadSingleModals = loadSingleModals;
window.loadUserModals = loadUserModals;
window.loadShoppingModal = loadShoppingModal;