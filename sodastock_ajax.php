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
    	$userName = $_POST['userName'];
    	
    	$nameQuery = "";
    	
    	if( $itemSearch != "" ) {
    		$nameQuery = " AND Name Like '%" . $itemSearch . "%' ";
    	}
    	
    	$cardQuery = "SELECT ID, Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, DateModified, ModifyType, Retired, ImageURL, ThumbURL, UnitName, DiscountPrice, OutOfStock, OutOfStockReporter, OutOfStockDate FROM Item WHERE Type ='" . $itemType . "' " .$nameQuery . " AND Hidden != 1 ORDER BY Retired, BackstockQuantity DESC, ShelfQuantity DESC";
    	
    	if( $userID != "" ) {
    		$cardQuery = "SELECT ID, Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, DateModified, ModifyType, Retired, ImageURL, ThumbURL, UnitName, (SELECT count(*) FROM Purchase_History p WHERE p.UserID = " . $userID . " AND p.ItemID = i.ID) as Frequency, DiscountPrice, OutOfStock, OutOfStockReporter, OutOfStockDate FROM Item i WHERE Type ='" . $itemType . "' " .$nameQuery . " AND Hidden != 1 ORDER BY Frequency DESC, Retired, BackstockQuantity DESC, ShelfQuantity DESC"; 
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
			buildTopSection($row, $containerType, $location, $userName, $loggedIn, $isMobile);
			echo "</div>";
		
			echo "<div class='middle_section'>";
			buildMiddleSection($db, $row, $loggedInAdmin, $loggedIn, $isMobile);
			echo "</div>";
			
			if( !$isMobile) {
				echo "<div class='bottom_section'>";
				buildBottomSection($db, $row, $containerType, $isMobile);
				echo "</div>";
			}
		
			echo "</div>";
		
		}
    } 
    else if( $type == "ToggleRequestCompleted" ) {
    	$requestID = $_POST['id'];
    	$results = $db->query("SELECT Completed FROM Requests WHERE ID =" . $requestID );
    	$row = $results->fetchArray();
    	
    	if( $row['Completed'] == 1 ) {
    		$db->exec( "UPDATE Requests set Completed = 0 WHERE ID = " . $requestID );
    	} else {
    		$db->exec( "UPDATE Requests set Completed = 1 WHERE ID = " . $requestID );
    	}
    }
    else if( $type == "OutOfStockRequest" ) {
    	$itemID = $_POST['itemID'];
    	$reporter = $_POST['reporter'];
    	$date = date('Y-m-d H:i:s', time());
    	$db->exec( "UPDATE Item set OutOfStock = 1, OutOfStockDate = '$date', OutOfStockReporter = '$reporter' WHERE ID = $itemID" );
    }
    else if($type == "DrawCart" )
    {
    	$itemQuantities = array();
    	$itemPrices = array();
    	$itemDiscountPrices = array();
    	$itemNames = array();
    	
    	$itemsInCart = json_decode($_POST['items']);
    	$url = $_POST['url'];

    	foreach( $itemsInCart as $itemID ) {
    		if( array_key_exists( $itemID, $itemQuantities ) === false ) {
    			$results = $db->query("SELECT * FROM Item WHERE ID =" . $itemID );
    			$row = $results->fetchArray();
    			$itemName = $row['Name'];
    			$itemPrice = $row['Price'];
    			$itemDiscountPrice = $row['DiscountPrice'];
    			
    			$itemQuantities[$itemID] = 1;
    			$itemNames[$itemID] = $itemName;
    			$itemPrices[$itemID] = $itemPrice;
    			$itemDiscountPrices[$itemID] = $itemDiscountPrice;
    		} else {
    			$itemQuantities[$itemID] = $itemQuantities[$itemID] + 1;
    		}
    	}
    	
    	$totalPrice = 0.0;
    	$totalSavings = 0.0;
    	
    	echo "<table style='border-collapse:collapse;'>";
    	echo "<tr><th style='color:#FFFFFF;'>Item</th><th style='color:#FFFFFF;'>Price</th></tr>";
    	foreach( $itemQuantities as $itemID => $itemQuantity ) {
    		$itemName = $itemNames[$itemID];
    		$itemPrice = $itemPrices[$itemID];
    		$itemDiscountPrice = $itemDiscountPrices[$itemID];
    		$costDisplay = "";
    		
    		if( $itemDiscountPrice != "" ) {
    			$costDisplay = "<span class='red_price'>$" . number_format($itemPrice, 2) . "</span> $" . number_format($itemDiscountPrice,2);
    			$totalPriceForItem = ( $itemDiscountPrice * $itemQuantity);
    			$totalSavings += ( $itemPrice - $itemDiscountPrice ) * $itemQuantity;
    		} else {
    			$costDisplay = number_format($itemPrice, 2);
    			$totalPriceForItem = ( $itemPrice * $itemQuantity);
    		}
    		
    		$totalPrice += $totalPriceForItem;
    		
    		echo "<tr><td style='color:#FFFFFF; padding: 5px 0px;'>" . $itemName . ( $itemQuantity > 1 ? " (x" . $itemQuantity . ")" : "" ) . "</td><td style='color:#FFFFFF; padding-left:15px;'>" . $costDisplay . "</td></tr>";
    		//echo "<div style='padding:10px;'>" . $itemName . ( $itemQuantity > 1 ? " (x" . $itemQuantity . ")" : "" ) . " = " .  '$' . number_format($totalPriceForItem, 2) . "</div>";
    	}
    	
    	echo "<tr><td style='color:#FFFFFF; padding-top:15px; border-top: 1px solid #FFF;'>TOTAL PRICE:</td><td style='color:#FFFFFF; font-weight:bold; padding-left:15px; padding-top:15px; border-top: 1px solid #FFF;'>" . '$' . number_format($totalPrice, 2) ."</td>";
    	echo "<tr><td style='color:#49c533; padding-top:5px;'>TOTAL SAVINGS:</td><td style='color:#49c533; font-weight:bold; padding-left:15px; padding-top:5px;'>" . '$' . number_format($totalSavings, 2) ."</td>";
    	echo "</table>";
    	
    	echo "<form id='add_item_form' enctype='multipart/form-data' action='handle_forms.php' method='POST'>";
    	echo "<input type='hidden' name='items' value='" . json_encode($itemsInCart) . "'/><br>";
    	echo "<input type='hidden' name='Purchase' value='Purchase'/><br>";
    	echo "<input type='hidden' name='redirectURL' value='$url'/><br>";
    	echo "<button class='quantity_button quantity_button_purchase' title='Purchase'>PURCHASE FOR $" . number_format($totalPrice, 2) . "</button>";
    	echo "<br><br><input type='checkbox' name='CashOnly' value='CashOnly'/><label style='padding:5px 0px;' for='CashOnly'>Already purchased with cash - don't add this to my balance (DISCOUNT PRICES DO NOT APPLY)</label><br>";
    	echo "</form>";
    }
?>