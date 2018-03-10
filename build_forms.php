<?php
		if(!$loggedInAdmin) {
			return;
		}
		
		$hideForms = "style='display:none;'";
		
		if( !$isMobile ) {
				$hideForms = "";
		}
		
		// Build Item Dropdown
        $results = $db->query("SELECT ID, Name, Price, Retired, ChartColor, ImageURL, ThumbURL, UnitName FROM Item WHERE Type ='" . $itemType . "' order by name asc");
		$item_options = "";
		$item_options_no_discontinued = "";
		$item_info = "";
		while ($row = $results->fetchArray()) {
			$item_id = $row[0];
			$item_name = $row[1];
			$item_price = $row[2];
			$item_retired = $row[3];
			$item_chart_color = $row[4];
			$item_imageURL = $row[5];
			$item_thumbURL = $row[6];
			$item_unit_name = $row[7];
			if(strlen($item_name) > 30)
			{
					$item_name = substr($item_name, 0, 30)."...";
			}
			$strikethrough = ( $item_retired == "0" ? "" : " style='font-weight:bold; color:#9b0909'");
			$item_options = $item_options . "<option $strikethrough value='$row[0]'>$item_name</option>";
			
			if($item_retired == 0) {
				$item_options_no_discontinued = $item_options_no_discontinued . "<option $strikethrough value='$row[0]'>$item_name</option>";
			}
			
			$item_info = $item_info . "<input type='hidden' id='Item_Name_$item_id' value='$item_name'/>" .
			"<input type='hidden' id='Item_Price_$item_id' value='$item_price'/>" .
			"<input type='hidden' id='Item_ImageURL_$item_id' value='$item_imageURL'/>" .
			"<input type='hidden' id='Item_ThumbURL_$item_id' value='$item_thumbURL'/>" .
			"<input type='hidden' id='Item_UnitName_$item_id' value='$item_unit_name'/>" .
			"<input type='hidden' id='Item_Retired_$item_id' value='$item_retired'/>" .
			"<input type='hidden' id='Item_ChartColor_$item_id' value='$item_chart_color'/>";
        }
    
		$edit_dropdown = "<select id='EditDropdown' name='EditDropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$item_options</select>";
		
		$restock_dropdown = "<select id='RestockDropdown' name='RestockDropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$item_options_no_discontinued</select>";
				
        
        // ------------------------------------
        // ADD ITEM MODAL
        // ------------------------------------
        echo "<div id='add_item' class='fancy' title='Add " . $itemType . "' $hideForms>";
        echo "<form id='add_item_form' enctype='multipart/form-data' action='$url' method='POST'>";
        echo "<fieldset>";
        echo "<label style='padding:5px 0px;' for='ItemName'>Name</label>";
        echo "<input type='text' autocorrect='off' autocapitalize='off' maxlength='40'; name='ItemName' class='text ui-widget-content ui-corner-all'>";
        echo "<label style='padding:5px 0px;' for='ChartColor'>Color</label>";
        echo "<input name='ChartColor' class='color text ui-widget-content ui-corner-all'>";
        echo "<label style='padding:5px 0px;' for='CurrentPrice'>Price of Can</label>";
        echo "<input type='tel' name='CurrentPrice' value='0.50' class='text ui-widget-content ui-corner-all'/>";
		
		echo "<input type='hidden' name='AuthPass517' value='2385'/><br>";
		echo "<input type='hidden' name='AddItem' value='AddItem'/><br>";
		
		if( $isMobile) {
			echo "<input class='ui-button' style='padding:10px;' type='submit' name='Add_Food_Submit' value='Add " . $itemType . "'/><br>";
		}
        echo "</fieldset>";
        echo "</form>";
        echo "</div>";
		
		// ------------------------------------
        // EDIT ITEM MODAL
        // ------------------------------------
        echo "<div id='edit_item' class='fancy' title='Edit " . $itemType . "' $hideForms>";
        echo "<form id='edit_item_form' enctype='multipart/form-data' action='$url' method='POST'>";
        echo "<fieldset>";
        echo "<label style='padding:5px 0px;' for='ItemNameDropdown'>" . $itemType . "</label>";
        echo $edit_dropdown;
		echo "<label style='padding:5px 0px;' for='ItemName'>Name</label>";
        echo "<input type='text' autocorrect='off' autocapitalize='off' maxlength='30'; id='EditItemName' name='EditItemName' class='text ui-widget-content ui-corner-all'>";
        echo "<label style='padding:5px 0px;' for='ChartColor'>Color</label>";
        echo "<input id='EditChartColor' name='EditChartColor' class='color text ui-widget-content ui-corner-all'>";
        echo "<label style='padding:5px 0px;' for='CurrentPrice'>Price of Can</label>";
        echo "<input type='tel' id='EditPrice' name='EditPrice' class='text ui-widget-content ui-corner-all'/>";
        echo "<label style='padding:5px 0px;' for='ImageURL'>Image URL</label>";
        echo "<input id='EditImageURL' name='EditImageURL' class='text ui-widget-content ui-corner-all'>";
        echo "<label style='padding:5px 0px;' for='ThumbURL'>Thumb URL</label>";
        echo "<input id='EditThumbURL' name='EditThumbURL' class='text ui-widget-content ui-corner-all'>";
        echo "<label style='padding:5px 0px;' for='UnitName'>Unit Name</label>";
        echo "<input id='EditUnitName' name='EditUnitName' class='text ui-widget-content ui-corner-all'>";
		echo "<div class='radio_status'>";
		echo "<input class='radio' type='radio' id='EditStatusActive' name='EditStatus' value='active' checked />";
		echo "<label for='EditStatusActive'>Active</label>";
		echo "<input class='radio' type='radio' id='EditStatusDiscontinued' name='EditStatus' value='discontinued' />";
		echo "<label for='EditStatusDiscontinued'>Discontinued</label>";
		echo "</div>";
		
		echo $item_info;
		echo "<input type='hidden' name='AuthPass517' value='2385'/><br>";
		echo "<input type='hidden' name='EditItem' value='EditItem'/><br>";
		
		if( $isMobile) {
			echo "<input class='ui-button' style='padding:10px;' type='submit' name='Edit_Item_Submit' value='Edit " . $itemType . "'/><br>";
		}
        echo "</fieldset>";
        echo "</form>";
        echo "</div>";
        
        // ------------------------------------
        // RESTOCK ITEM MODAL
        // ------------------------------------
        echo "<div id='restock_item' title='Restock " . $itemType . "' $hideForms>";
        echo "<form id='restock_item_form' class='fancy' enctype='multipart/form-data' action='$url' method='POST'>";
        echo "<fieldset>";
        echo "<label style='padding:5px 0px;' for='ItemNameDropdown'>" . $itemType . "</label>";
        echo $restock_dropdown;
        echo "<label style='padding:5px 0px;' for='NumberOfCans'>Number Of Cans</label>";
        echo "<input type='tel' name='NumberOfCans' class='text ui-widget-content ui-corner-all'/>";
        echo "<label style='padding:5px 0px;' for='Cost'>Cost for Pack</label>";
        echo "<input type='tel' name='Cost' class='text ui-widget-content ui-corner-all'/>";

        echo "<input type='hidden' name='AuthPass517' value='2385'/><br>";
        echo "<input type='hidden' name='Restock' value='Restock'/><br>";
		
		if( $isMobile) {
			echo "<input style='padding:10px;' type='submit' name='Restock_Item_Submit' value='Restock " . $itemType . "'/><br>";
		}
        echo "</fieldset>";
        echo "</form>";
        echo "</div>";

        // ------------------------------------
        // SEND EMAIL FORM
        // ------------------------------------
        echo "<form id='send_email_form' class='fancy' enctype='multipart/form-data' action='$url' method='POST'>";

        echo "<input type='hidden' name='SendEmail' value='SendEmail'/><br>";
        echo "</form>";
        
        // ------------------------------------
        // DAILY AMOUNT MODAL - ALL ITEMS
        // ------------------------------------
        echo "<div id='daily_amount' title='Enter Daily Amounts' $hideForms>";
        echo "<form id='daily_amount_form' class='fancy' enctype='multipart/form-data' action='$url' method='POST'>";

        echo "<table>";
        echo "<tr><th>" . $itemType . "</th><th>Shelf Quantity</th><th>Backstock Quantity</th>";
		
		if(!$isMobile) {
			echo "<th>Price</th>";
		}
		
		echo "</tr>";
		
        $results = $db->query("SELECT Name, BackstockQuantity, ShelfQuantity, Price, ID FROM Item WHERE NOT Retired = 1 AND Type ='" . $itemType . "' AND (BackstockQuantity + ShelfQuantity) > 0 ORDER BY Name asc, Retired");
		$tabIndex = 1;
		while ($row = $results->fetchArray()) {
			$item_name = $row[0];
	        $backstockquantity = $row[1];
	        $shelfquantity = $row[2];
	        $price = $row[3];
	        $item_id = $row[4];
	        echo "<tr>";
	        echo "<td><b>$item_name</b></td>";
	        echo "<input type='hidden' id='item_$item_id' name='ItemID[]' value='$item_id'/>";
	        echo "<td><input type='tel' onClick='this.select();' tabindex=$tabIndex id='ShelfQuantity_$item_id' value='$shelfquantity' name='ShelfQuantity[]' class='text ui-corner-all'/></td>";
	        echo "<td><input type='tel' tabindex=0 id='BackstockQuantity_$item_id' value='$backstockquantity' name='BackstockQuantity[]' class='text  ui-corner-all'/></td>";
			
			if( !$isMobile ) {
				echo "<td><input tabindex=0 id='CurrentPrice_$item_id' value='$price' name='CurrentPrice[]' class='text ui-corner-all'/></td>";
			}
	        echo "</tr>";

			$tabIndex++;
	        // On change, update the backstock quantity if you are increasing the shelf quantity
	        echo "<script type='text/javascript'>";
    			echo "$( document ).ready( function() {";

    				echo "var originalShelf_$item_id = parseInt($('#ShelfQuantity_$item_id').val());";

			        echo "$('#ShelfQuantity_$item_id').change(function () {";
		                echo "var newValue = parseInt($('#ShelfQuantity_$item_id').val());";
		                echo "console.log('Original: [' + originalShelf_$item_id + ']');";
		                echo "console.log('New: [' + newValue + ']');";
		                echo "if(newValue > originalShelf_$item_id) {";
		                echo "var takenFromBackstock = (newValue - originalShelf_$item_id);";
		                echo "console.log('Taken:' + takenFromBackstock);";
		                echo "var backStockQuantity = $('#BackstockQuantity_$item_id').val();";
		                echo "var newBackstockQuantity = backStockQuantity - takenFromBackstock;";

		                echo "if(newBackstockQuantity >= 0 && takenFromBackstock > 0) {";
			            echo "$('#BackstockQuantity_$item_id').val(newBackstockQuantity);";
			            //echo "$('#BackStockUpdate_$item_id').html(' (Removed <b>' + takenFromBackstock + '</b> from Backstock)');";
			            //echo "$('#BackStockUpdate_$item_id').css('color', 'green');";
			            echo "originalShelf_$item_id = newValue;";
			            echo "} else {";
			            echo "$('#ShelfQuantity_$item_id').val(originalShelf_$item_id);";
			            //echo "$('#BackStockUpdate_$item_id').html(' (Not enough backstock to remove ' + takenFromBackstock + ' cans!)');";
			            //echo "$('#BackStockUpdate_$item_id').css('color', 'red');";
			            echo "}";
		                echo "}";
            		echo "});";
				echo "});";
			echo "</script>";
		}
		echo "</table>";
       	echo "<input type='checkbox' id='SendToSlack' checked name='SendToSlack'/> Send to Slack";

       	echo "<input type='hidden' name='AuthPass517' value='2385'/><br>";
       	echo "<input type='hidden' name='DailyAmount' value='DailyAmount'/><br>";
        
        
		if( $isMobile) {
			echo "<input style='padding:10px;' type='submit' name='Update_Item_Submit' value='Add Daily Amount'/><br>";
		}
        echo "</form>";
        echo "</div>";
?>

<script type='text/javascript'>
$( document ).ready( function() {
	$('#EditDropdown').change(function () {
		setItemInfo();
	});
	
	setItemInfo();
});

function setItemInfo() {
	var itemID = parseInt($('#EditDropdown').val());
	var itemName = $('#Item_Name_' + itemID).val();
	var itemPrice = $('#Item_Price_' + itemID).val();
	var itemImageURL = $('#Item_ImageURL_' + itemID).val();
	var itemUnitName = $('#Item_UnitName_' + itemID).val();
	var itemThumbURL = $('#Item_ThumbURL_' + itemID).val();
	var itemChartColor = $('#Item_ChartColor_' + itemID).val();
	var itemRetired = $('#Item_Retired_' + itemID).val();
	console.log("Item ID: " +  itemID + " " + itemName + " " + itemPrice+ " " + itemChartColor + " " + itemRetired);
	
	$("#EditItemName").val(itemName);
	$("#EditPrice").val(itemPrice);
	$("#EditImageURL").val(itemImageURL);
	$("#EditThumbURL").val(itemThumbURL);
	$("#EditUnitName").val(itemUnitName);
	$("#EditChartColor").val(itemChartColor);
	
	if( itemRetired == 0 ) {
		$("#EditStatusActive").prop("checked", true);
		$("#EditStatusDiscontinued").prop("checked", false);
	} else {
		$("#EditStatusActive").prop("checked", false);
		$("#EditStatusDiscontinued").prop("checked", true);
	}
}
</script>