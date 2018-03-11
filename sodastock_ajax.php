<?php
	include("foodstock_functions.php");
	date_default_timezone_set('America/New_York');

    $type = $_POST['type'];
        
    $db = new SQLite3('db/item.db');
    if (!$db) die ($error);

    if($type == "CardArea" ) 
    {
    	$itemType = $_POST['itemType'];
    	$className = $_POST['className'];
    	$location = $_POST['location'];
    	$isMobile = $_POST['isMobile'];
    	$loggedInAdmin = $_POST['loggedInAdmin'];
    	$loggedIn = $_POST['loggedIn'];
    	$itemSearch = $_POST['itemSearch'];
    	
    	$userID = $_POST['userID'];
    	
    	$nameQuery = "";
    	
    	if( $itemSearch != "" ) {
    		$nameQuery = " AND Name Like '%" . $itemSearch . "%' ";
    	}
    	
    	$cardQuery = "SELECT ID, Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, DateModified, ModifyType, Retired, ImageURL, ThumbURL, UnitName FROM Item WHERE Type ='" . $itemType . "' " .$nameQuery . " ORDER BY Retired, BackstockQuantity DESC, ShelfQuantity DESC";
    	
    	if( $userID != "" ) {
    		$cardQuery = "SELECT ID, Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, DateModified, ModifyType, Retired, ImageURL, ThumbURL, UnitName, (SELECT count(*) FROM Purchase_History p WHERE p.UserID = " . $userID . " AND p.ItemID = i.ID) as Frequency FROM Item i WHERE Type ='" . $itemType . "' " .$nameQuery . " ORDER BY Frequency DESC, Retired, BackstockQuantity DESC, ShelfQuantity DESC"; 
    	}
    	
		$results = $db->query($cardQuery);
		
		//---------------------------------------
		// BUILD ITEM CARDS
		//---------------------------------------
		$columnNumber = 1;
		while ($row = $results->fetchArray()) {
		
			$containerType = "[UNKNOWN]";
		
			if( $row[15] != "" ) {
				$containerType = $row[15];
			}
		
			if( $row[12] == 0 ) {
				// Active - blue cards
				echo "<div class='" . $className . "_card card'>";
			} else {
				// Retired - black cards
				echo "<div class='card' style='background-color:#131313;'>";
			}
		
			echo "<div class='top_section'>";
			buildTopSection($row, $containerType, $location, $isMobile);
			echo "</div>";
		
			echo "<div class='middle_section'>";
			buildMiddleSection($db, $row, $loggedInAdmin, $loggedIn, $isMobile);
			echo "</div>";
			
			if( !$isMobile) {
				echo "<div class='bottom_section'>";
				buildBottomSection($row, $containerType);
				echo "</div>";
			}
		
			echo "</div>";
		
		}
    } 
    else if($type == "DrawCart" )
    {
    	$itemQuantities = array();
    	$itemPrices = array();
    	$itemNames = array();
    	
    	$itemsInCart = json_decode($_POST['items']);
    	$url = $_POST['url'];

    	foreach( $itemsInCart as $itemID ) {
    		if( array_key_exists( $itemID, $itemQuantities ) === false ) {
    			$results = $db->query("SELECT * FROM Item WHERE ID =" . $itemID );
    			$row = $results->fetchArray();
    			$itemName = $row['Name'];
    			$itemPrice = $row['Price'];
    			
    			$itemQuantities[$itemID] = 1;
    			$itemNames[$itemID] = $itemName;
    			$itemPrices[$itemID] = $itemPrice;
    		} else {
    			$itemQuantities[$itemID] = $itemQuantities[$itemID] + 1;
    		}
    	}
    	
    	$totalPrice = 0.0;
    	
    	echo "<table style='border-collapse:collapse;'>";
    	echo "<tr><th style='color:#FFFFFF;'>Item</th><th style='color:#FFFFFF;'>Price</th></tr>";
    	foreach( $itemQuantities as $itemID => $itemQuantity ) {
    		$itemName = $itemNames[$itemID];
    		$itemPrice = $itemPrices[$itemID];
    		
    		$totalPriceForItem = ( $itemPrice * $itemQuantity);
    		$totalPrice += $totalPriceForItem;
    		
    		echo "<tr><td style='color:#FFFFFF; padding: 5px 0px;'>" . $itemName . ( $itemQuantity > 1 ? " (x" . $itemQuantity . ")" : "" ) . "</td><td style='color:#FFFFFF; padding-left:15px;'>$" . number_format($totalPriceForItem, 2) . "</td></tr>";
    		//echo "<div style='padding:10px;'>" . $itemName . ( $itemQuantity > 1 ? " (x" . $itemQuantity . ")" : "" ) . " = " .  '$' . number_format($totalPriceForItem, 2) . "</div>";
    	}
    	
    	echo "<tr><td style='color:#FFFFFF; padding-top:15px; border-top: 1px solid #FFF;'>TOTAL PRICE:</td><td style='color:#FFFFFF; font-weight:bold; padding-left:15px; padding-top:15px; border-top: 1px solid #FFF;'>" . '$' . number_format($totalPrice, 2) ."</td>";
    	echo "</table>";
    	
    	echo "<form id='add_item_form' enctype='multipart/form-data' action='$url' method='POST'>";
    	echo "<input type='hidden' name='items' value='" . json_encode($itemsInCart) . "'/><br>";
    	echo "<input type='hidden' name='Purchase' value='Purchase'/><br>";
    	echo "<button class='quantity_button quantity_button_purchase' title='Purchase'>PURCHASE FOR $" . number_format($totalPrice, 2) . "</button>";
    	echo "<br><br><input type='checkbox' name='CashOnly' value='CashOnly'/><label style='padding:5px 0px;' for='CashOnly'>Already purchased with cash - don't add this to my balance</label><br>";
    	echo "</form>";
    }
?>