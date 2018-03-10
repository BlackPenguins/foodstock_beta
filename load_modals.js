function loadModals(isLoggedIn, type) {

	// Load the Coke Zero info on load
	/*
	var id = $("select#DailyAmountDropdown option:selected").attr('value');
	$.post("itemstock_ajax.php", {item_id:id},function(data) {
					var itemInfo = jQuery.parseJSON(data);
					$('#BackstockQuantity').val(itemInfo['Backstock']);
					$('#ShelfQuantity').val(itemInfo['Shelf']);
					originalShelf = itemInfo['Shelf'];
					$('#CurrentPrice').val(itemInfo['Price']);

					
	});
*/

	

	// Open modals with buttons
	$("#add_item_button").click( function() {
			$('#add_item').dialog('open');
			 return false;
	});
	
	$("#edit_item_button").click( function() {
			$('#edit_item').dialog('open');
			 return false;
	});
	
	$("#restock_item_button").click( function() {
			$('#restock_item').dialog('open');
			 return false;
	});
	
	$("#daily_amount_button").click( function() {
			$('#daily_amount').dialog('open');
			 return false;
	});

	$("#send_email_button").click( function() {
			$("#send_email_form").submit();
			 return false;
	});

	
				
	/*    
	// Change the item info on change
	$("select#DailyAmountDropdown").change(function () {
			var id = $("select#DailyAmountDropdown option:selected").attr('value');
			$.post("itemstock_ajax.php", {item_id:id},function(data) {
					var itemInfo = jQuery.parseJSON(data);
					$('#BackstockQuantity').val(itemInfo['Backstock']);
					$('#ShelfQuantity').val(itemInfo['Shelf']);
					originalShelf = itemInfo['Shelf'];
					$('#CurrentPrice').val(itemInfo['Price']);

					
			});
	});

	$("#ShelfQuantity").change(function () {
			var newValue = $("#ShelfQuantity").val();
			if(newValue > originalShelf) {
				var takenFromBackstock = (newValue - originalShelf);
				var backStockQuantity = $("#BackstockQuantity").val();
				var newBackstockQuantity = backStockQuantity - takenFromBackstock;

				if(newBackstockQuantity >= 0 && takenFromBackstock > 0) {
					$("#BackstockQuantity").val(newBackstockQuantity);
					$("#BackStockUpdate").html(" (Removed <b>" + takenFromBackstock + "</b> from Backstock)");
					$("#BackStockUpdate").css("color", "green");
					originalShelf = newValue;
				} else {
					$("#ShelfQuantity").val(originalShelf);
					$("#BackStockUpdate").html(" (Not enough backstock to remove " + takenFromBackstock + " cans!)");
					$("#BackStockUpdate").css("color", "red");
				}
			}
	});
	*/

	// Build forms
	$( "#add_item" ).dialog( {
			autoOpen: false,
			width: 500,
			modal: true,
			buttons: [
						{
							id: "Add_Item_Cancel",
							text: "Cancel",
							click: function() {
								$(this).dialog("close");
							} 
						},
						{
							id:"Add_Food_Submit",
							text: "Add " + type,
							click: function() { 
								if(isLoggedIn) {
									$("#add_item_form").submit();
								}
							}
						}
					]
	});    
	
	$( "#edit_item" ).dialog( {
			autoOpen: false,
			width: 500,
			modal: true,
			buttons: [
						{
							id: "Edit_Item_Cancel",
							text: "Cancel",
							click: function() {
								$(this).dialog("close");
							} 
						},
						{
							id:"Edit_Item_Submit",
							text: "Edit " + type,
							click: function() { 
								if(isLoggedIn) {
									$("#edit_item_form").submit();
								}
							}
						}
					]
	});    
	
	$( "#restock_item" ).dialog( {
			autoOpen: false, 
			width: 500,
			modal: true,
			buttons: [
						{
							id: "Restock_Item_Cancel",
							text: "Cancel",
							click: function() {
								$(this).dialog("close");
							} 
						},
						{
							id:"Restock_Item_Submit",
							text: "Restock " + type,
							click: function() { 
								if(isLoggedIn) {
									$("#restock_item_form").submit();
								}
							}
						}
					]
	});
	
	$( "#daily_amount" ).dialog( {
			autoOpen: false, 
			width: 800,
			modal: true,
			buttons: [
						{
							id: "Update_Item_Cancel",
							text: "Cancel",
							click: function() {
								$(this).dialog("close");
							} 
						},
						{
							id:"Update_Item_Submit",
							text: "Update " + type,
							click: function() { 
								if(isLoggedIn) {
									$("#daily_amount_form").submit();
								}
							}
						}
					]
	});
}

//Put the function at a global level so it can be accessed from diff files
window.loadModals = loadModals;