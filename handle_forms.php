<?php
// ------------------------------------
// HANDLE QUERIES
// ------------------------------------
if(isset($_POST['AddItem'])) 
{
		$auth = trim($_POST["AuthPass517"]);
		if($auth == "2385") {
	        $name = trim($_POST["ItemName"]); 
	        $date = date('Y-m-d H:i:s');
	        $chartColor = trim($_POST["ChartColor"]); 
	        $price = trim($_POST["CurrentPrice"]); 
	        $itemType = trim($_POST["ItemType"]); 
	        
	        $addItemQuery = "INSERT INTO Item (Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, Type) VALUES( '$name', '$date', '$chartColor', 0, 0, 0, $price, 0.00, 0.00, '$itemType')";
	        $db->exec( $addItemQuery );

	        echo "Item added successfully.<br>";
    	} else  {
			echo "YOU ARE NOT LOGGED IN!<br>";
		}
}

else if(isset($_POST['EditItem'])) 
{
		$auth = trim($_POST["AuthPass517"]);
		if($auth == "2385") {
			$itemType = trim($_POST["ItemType"]);
			
			$id = trim($_POST["Edit" . $itemType . "Dropdown"]); 
	        $name = trim($_POST["EditItemName" . $itemType]);
	        $chartColor = trim($_POST["EditChartColor" . $itemType]); 
	        $price = trim($_POST["EditPrice" . $itemType]); 
	        $imageURL = trim($_POST["EditImageURL" . $itemType]); 
	        $thumbURL = trim($_POST["EditThumbURL" . $itemType]); 
	        $unitName = trim($_POST["EditUnitName" . $itemType]); 
	        $status = trim($_POST["EditStatus" . $itemType]); 
	        
	        error_log("Status: " . $status );
			$retired = $status == "active" ? 0 : 1;
			
			 error_log("StatusRR: " . $retired );
	        $db->exec("UPDATE Item SET Name='$name', ChartColor='$chartColor', Price = $price, Retired = $retired, ImageURL = '$imageURL', ThumbURL = '$thumbURL', UnitName = '$unitName'  where ID = $id");

	        echo "Item edited successfully.<br>";
    	} else  {
			echo "YOU ARE NOT LOGGED IN!<br>";
		}
}

else if(isset($_POST['Purchase']))
{
		$itemsInCart = json_decode($_POST['items']);
		$cashOnly = isset( $_POST['CashOnly'] );
		
		$totalPrice = 0.0;
		
		$errors = "";
		$purchaseMessage = "";
		$itemType = "UNKNOWN";
		
    	foreach( $itemsInCart as $itemID ) {
    		$results = $db->query("SELECT * FROM Item WHERE ID =" . $itemID );
    		$row = $results->fetchArray();
    		$itemPrice = $row['Price'];
    		$itemName = $row['Name'];
    		$shelfQuantity = $row['ShelfQuantity'];
    		$backstockQuantity = $row['BackstockQuantity'];
    		$itemType = $row['Type'];
    		
    		if( $shelfQuantity - 1 <= -1 ) {
    			$errors .= "Not enough " . $itemName . " in stock. Purchase Cancelled.\\n";
    		} else {
	    		$date = date('Y-m-d H:i:s', time());
	    		$totalPrice += $itemPrice;
	    		
	    		$purchaseHistoryQuery = "INSERT Into Purchase_History (UserID, ItemID, Cost, Date ) VALUES (" . $_SESSION['userID'] . "," . $itemID . "," . $itemPrice . ",'" . $date . "')";
	    		$itemQuery = "UPDATE Item SET TotalIncome = TotalIncome + $itemPrice, DateModified = '$date', ModifyType = 'Purchased by " . $_SESSION['userID'] . "' where ID = $itemID";
	    		$itemCountQuery = "UPDATE Item SET ShelfQuantity = ShelfQuantity - 1 where ID = $itemID";
	    		$informationQuery = "UPDATE Information SET Income = Income + $itemPrice where ItemType = '$itemType'";
	    		$inventoryQuery = "INSERT INTO Daily_Amount (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Price, Restock) VALUES($itemID, '$date', $backstockQuantity, $backstockQuantity, $shelfQuantity," . ($shelfQuantity - 1) . ", $itemPrice, 0)";

	    		$db->exec( $purchaseHistoryQuery );
	    		$db->exec( $inventoryQuery );
	    		$db->exec( $itemCountQuery );
	    		
    			$db->exec( $itemQuery );
    			$db->exec( $informationQuery );
	    		
	    		
	    		$purchaseMessage = $purchaseMessage . "- " . $itemName . " ($" . number_format($itemPrice, 2) . ")\\n";
    		}
    	}
    	
    	if( !$cashOnly ) {
	    	$typeOfBalance = $itemType . "Balance";
	    	
	    	$balanceUpdateQuery = "UPDATE User SET " . $typeOfBalance . " = " . $typeOfBalance . " + $totalPrice where UserID = " . $_SESSION['userID'];
	    	$db->exec( $balanceUpdateQuery );
	    	
	    	$_SESSION[$typeOfBalance] = $_SESSION[$typeOfBalance] + $totalPrice;
    	}
    	
    	$purchaseMessage = $purchaseMessage . "*Total Price:* $" . number_format($totalPrice, 2) . "\\n";
    	
    	if( !$cashOnly ) {
    		$purchaseMessage = $purchaseMessage . "*Your " . $itemType . " Balance:* $" . number_format($_SESSION[$typeOfBalance],2) . "\\n";
    	} else {
    		$purchaseMessage = $purchaseMessage . "*THIS PURCHASE WAS CASH-ONLY*\\n";
    	}
    	
    	if( $_SESSION["SlackID"] == "" ) {
    		echo "<script>sendSlackMessageToMatt( 'Failed to send notification for " . $_SESSION['username'] . ". Create a SlackID!', ':no_entry:', '" . $itemType . "Stock - ERROR!!' );</script>";
    	} else {
    		echo "<script>sendSlackMessageToUser( '" . $_SESSION["SlackID"] . "',  '$purchaseMessage',':shopping_trolley:', '" . $itemType . "Stock - RECEIPT' );</script>";
    	}
    	
    	echo "<script>sendSlackMessageToMatt( '*(" . strtoupper($_SESSION['username']) . ")*\\n$purchaseMessage', ':shopping_trolley:', '" . $itemType . "Stock - RECEIPT' );</script>";
    	
    	if( $errors != "" ) {
    		error_log( "ERROR: [" . $_SESSION['userID'] . "]" . $errors );
			echo "<script>alert('Something went wrong - contact Matt!!\\n" . $errors . "'); console.log('" . $errors  . "'); sendSlackMessageToMatt( 'Errors: $errors', ':no_entry:', '" . $itemType . "Stock - ERROR!!' );</script>";
    	}
}

else if(isset($_POST['Restock'])) 
{
        $auth = trim($_POST["AuthPass517"]);
        if($auth == "2385") {
	        $id = trim($_POST["RestockDropdown"]);
	        $itemType = trim($_POST["ItemType"]);
	        $date = date('Y-m-d H:i:s'); 
	        $numberOfCans = trim($_POST["NumberOfCans"]); 
	        $cost = trim($_POST["Cost"]); 
	                
	        $db->exec("INSERT INTO Restock VALUES($id, '$date', $numberOfCans, $cost)");
	        $db->exec("UPDATE Item SET TotalExpenses = TotalExpenses + $cost, BackstockQuantity = BackstockQuantity + $numberOfCans, TotalCans = TotalCans + $numberOfCans where ID = $id");
	        $db->exec("UPDATE Information SET Expenses = Expenses + $cost where ItemType = '$itemType'");

	        echo "Restock added successfully.<br>";
	    } else  {
			echo "YOU ARE NOT LOGGED IN!<br>";
		}
}
else if(isset($_POST['Payment']))
{
	$auth = trim($_POST["AuthPass517"]);
	if($auth == "2385") {
		$userID = trim($_POST["UserDropdown"]);
		$itemType = trim($_POST["ItemTypeDropdown"]);
		$date = date('Y-m-d H:i:s');
		$amount = trim($_POST["Amount"]);
		$note = trim($_POST["Note"]);
		$method = trim($_POST["Method"]);
		 
		$isUserPayment = $userID > 0;
		$isBalanceValid = true;
		
		if( $isUserPayment ) {
			$typeOfBalance = $itemType . "Balance";
			$results = $db->query("SELECT $typeOfBalance, SlackID, UserName From User where UserID = $userID");
			$row = $results->fetchArray();
			$balance = $row[$typeOfBalance];
			$slackID = $row['SlackID'];
			$username = $row['UserName'];
			
			if( $amount > $balance ) {
				$isBalanceValid = false;
				echo "This payment [$" . number_format($amount, 2) . "] is larger than the user's $typeOfBalance of [$" . number_format($balance,2) . "]. Payment denied!<br>";
			} else {
				$newBalance = $balance - $amount;
				if( $slackID == "" ) {
					echo "<script>sendSlackMessageToMatt( 'Failed to send notification for " . $username . ". Create a SlackID!', ':no_entry:', '" . $itemType . "Stock - ERROR!!'  );</script>";
				} else {
					echo "<script>sendSlackMessageToUser( '" . $slackID . "',  'Payment: *$" . number_format($amount,2) . "*\\nYour Current " . $itemType ." Balance: *$" . number_format($newBalance,2) . "*', ':dollar:', '" . $itemType . "Stock - PAYMENT RECEIVED' );</script>";
				}
				
				echo "<script>sendSlackMessageToMatt( '*(" . strtoupper($username) . ")*\\n Payment: *$" . number_format($amount,2) . "*\\nTheir Current " . $itemType ." Balance: *$" . number_format($newBalance,2) . "*', ':dollar:', '" . $itemType . "Stock - PAYMENT RECEIVED' );</script>";
			}
		}
		
		if( $isBalanceValid ) {
			$db->exec("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType) VALUES($userID, '$method', $amount, '$date', '$note', '$itemType')");

			if( $isUserPayment ) {
				$db->exec("UPDATE User SET $typeOfBalance = $typeOfBalance - $amount where UserID = $userID");
			}
			
			$db->exec("UPDATE Information SET ProfitActual = ProfitActual + $amount where ItemType = '$itemType'");
			
			echo "Payment added successfully.<br>";
		}
	} else  {
		echo "YOU ARE NOT LOGGED IN!<br>";
	}
}
else if(isset($_POST['Request']))
{
	$itemType = trim($_POST["ItemTypeDropdown_Request"]);
	$date = date('Y-m-d H:i:s');
	$itemName = trim($_POST["ItemName_Request"]);
	$note = trim($_POST["Note_Request"]);
	$userID = $_SESSION['userID'];
	$username = $_SESSION['username'];
	$slackID = $_SESSION['SlackID'];
		
	if( $slackID == "" ) {
		echo "<script>sendSlackMessageToMatt( 'Failed to send notification for " . $username . ". Create a SlackID!', ':no_entry:', '" . $itemType . "Stock - ERROR!!'  );</script>";
	} else {
		echo "<script>sendSlackMessageToUser( '" . $slackID . "',  '*Item Name:* " . $itemName . "\\n*Notes:* " . $note ."', ':ballot_box_with_ballot:', 'REQUEST RECEIVED' );</script>";
	}

	echo "<script>sendSlackMessageToMatt( '*(" . strtoupper($username) . ")*\\n*Item Name:* " . $itemName . "\\n*Notes:* " . $note ."', ':ballot_box_with_ballot:', 'REQUEST RECEIVED' );</script>";

	$db->exec("INSERT INTO Requests (UserID, ItemName, Date, Note, ItemType) VALUES($userID, '$itemName', '$date', '$note', '$itemType')");

	echo "Request submitted successfully.<br>";
}
else if(isset($_POST['Inventory'])) 
{
		$auth = trim($_POST["AuthPass517"]);
		if($auth == "2385") {
	        $id_all = $_POST["ItemID"];
	        $sendToSlack = false;
	        
	        if( isset($_POST['SendToSlack']) && $_POST['SendToSlack'] == 'on') {
	        	$sendToSlack = true;
	        }
	        
	        $date = date('Y-m-d H:i:s'); 
	        $backstockQuantity_all = $_POST["BackstockQuantity"]; 
	        $shelfQuantity_all = $_POST["ShelfQuantity"]; 
	        $price_all = 0;
			
			if( isset($_POST['CurrentPrice']) ) {
				$price_all = $_POST["CurrentPrice"];
			}
	        $restocked = 0;
	                
	        $backstockQuantityBefore = 0;
	        $shelfQuantityBefore = 0;
	        $priceBefore = 0;
			

			$slackMessageItems = "";
			$itemType = "";
			
	        for ($i = 0; $i < count($id_all); $i++) {
			    $id = $id_all[$i];
			    $backstockQuantity = $backstockQuantity_all[$i];
			    $shelfQuantity = $shelfQuantity_all[$i];
			    $price = $price_all == 0 ? "" : $price_all[$i];
				$itemName = "N/A";
				

		        $results = $db->query("SELECT ID, BackStockQuantity, ShelfQuantity, Price, Name, Type, UnitName FROM Item WHERE ID = $id");
		        while ($row = $results->fetchArray()) {
		                $backstockQuantityBefore = $row[1];
		                $shelfQuantityBefore = $row[2];
		                $priceBefore = $row[3];
		                $itemName = $row[4];
		                $itemType = $row[5];
		                $itemUnits = $row[6];
		        }
				
				if( $price == "") {
					$price = $priceBefore;
				}
				
				if( $shelfQuantity > $shelfQuantityBefore ) {
					//New item was added to the fridge
					$slackMessageItems = $slackMessageItems . "*" . $itemName . ":* " . $shelfQuantityBefore . " " . $itemUnits ."s --> *" . $shelfQuantity . " " . $itemUnits ."s*\\n";
				}				
		        $totalCansBefore = $backstockQuantityBefore + $shelfQuantityBefore;
		        $totalCans = $backstockQuantity + $shelfQuantity;
		        
		        $income = ($totalCansBefore - $totalCans) * $priceBefore;

				error_log("SQ1:" . "INSERT INTO Daily_Amount (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Price, Restock) VALUES($id, '$date',  $backstockQuantityBefore, $backstockQuantity, $shelfQuantityBefore, $shelfQuantity, $price, $restocked)");
		        $db->exec("INSERT INTO Daily_Amount (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Price, Restock) VALUES($id, '$date', $backstockQuantityBefore, $backstockQuantity, $shelfQuantityBefore, $shelfQuantity, $price, $restocked)");
				error_log("SQ2:" . "UPDATE Item SET Price = $price, DateModified = '$date' where ID = $id" );
		        $db->exec("UPDATE Item SET Price = $price, DateModified = '$date' where ID = $id");
		        $db->exec("UPDATE Item SET TotalIncome = TotalIncome + $income, BackstockQuantity = $backstockQuantity, ShelfQuantity = $shelfQuantity, DateModified = '$date', ModifyType = 'Counted' where ID = $id");
		        $db->exec("UPDATE Information SET Income = Income + $income where ItemType = '$itemType'");
		        echo "Daily_Amount added successfully for Item #$id.<br>";
		    }
			
		    $emoji = ":soda:";
		    $location = "fridge";
		    
		    if( $itemType == "Snack" ) {
		    	$emoji = ":cookie:";
		    	$location = "cabinet";
		    }
			if( $slackMessageItems != "" && $sendToSlack == true) {
				$slackMessage = $slackMessageItems ."\\n\\nWant to see what\'s in the $location, the prices, what has been discontinued, the trends of different items being bought, or just general statistics? View the NEW <http://penguinore.net/$url>";
				
				echo "<script type='text/javascript'>sendSlackMessageToRandom('$slackMessage', '$emoji', '" . $itemType. "Stock - REFILL');</script>";
			}
		
			
		} else  {
			echo "YOU ARE NOT LOGGED IN!<br>";
		}
}
?>