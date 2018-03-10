<script type="text/javascript">
function sendSlackMessageToRandom( slackMessage ) {
	console.log("slack message [" + slackMessage + "]");
	$.ajax({
		data: 'payload=' + JSON.stringify({ "channel":"#random", "icon_emoji":":soda:", "username":"SodaStock","text":slackMessage }),
		dataType: 'json',
		processData: false,
		type: 'POST',
		url: 'https://hooks.slack.com/services/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'
	});
}
</script>

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
	        
	        $db->exec("INSERT INTO Item (Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses) VALUES( '$name', '$date', '$chartColor', 0, 0, 0, $price, 0.00, 0.00)");

	        echo "Item added successfully.<br>";
    	} else  {
			echo "YOU ARE NOT LOGGED IN!<br>";
		}
}

else if(isset($_POST['EditItem'])) 
{
		$auth = trim($_POST["AuthPass517"]);
		if($auth == "2385") {
			$id = trim($_POST["EditDropdown"]); 
	        $name = trim($_POST["EditItemName"]);
	        $chartColor = trim($_POST["EditChartColor"]); 
	        $price = trim($_POST["EditPrice"]); 
	        $imageURL = trim($_POST["EditImageURL"]); 
	        $thumbURL = trim($_POST["EditThumbURL"]); 
	        $unitName = trim($_POST["EditUnitName"]); 
	        $status = trim($_POST["EditStatus"]); 
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

		$totalPrice = 0.0;
		
		$errors = "";
		
    	foreach( $itemsInCart as $itemID ) {
    		$results = $db->query("SELECT * FROM Item WHERE ID =" . $itemID );
    		$row = $results->fetchArray();
    		$itemPrice = $row['Price'];
    		$itemName = $row['Name'];
    		$shelfQuantity = $row['ShelfQuantity'];
    		$backstockQuantity = $row['BackstockQuantity'];
    		
    		if( $shelfQuantity - 1 <= -1 ) {
    			$errors .= "Not enough " . $itemName . " in stock. Purchase Cancelled.\\n";
    		} else {
    		
	    		$date = date('Y-m-d H:i:s', time());
	    		$totalPrice += $itemPrice;
	    		
	    		$purchaseHistoryQuery = "INSERT Into Purchase_History (UserID, ItemID, Cost, Date ) VALUES (" . $_SESSION['userID'] . "," . $itemID . "," . $itemPrice . ",'" . $date . "')";
	    		$itemQuery = "UPDATE Item SET TotalIncome = TotalIncome + $itemPrice, ShelfQuantity = ShelfQuantity - 1, DateModified = '$date', ModifyType = 'Purchased by " . $_SESSION['userID'] . "' where ID = $itemID";
	    		$dailyAmount = "INSERT INTO Daily_Amount (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Price, Restock) VALUES($itemID, '$date', $backstockQuantity, $backstockQuantity, $shelfQuantity," . ($shelfQuantity - 1) . ", $itemPrice, 0)";

	    		$db->exec( $purchaseHistoryQuery );
	    		$db->exec( $itemQuery );
	    		$db->exec( $dailyAmount );
	    		
    		}
    	}
    	
    	$db->exec("UPDATE User SET Balance = Balance + $totalPrice where UserID = " . $_SESSION['userID']);
    	
    	$_SESSION['balance'] = $_SESSION['balance'] + $totalPrice;
    	
    	if( $errors != "" ) {
    		error_log( "ERROR: [" . $_SESSION['userID'] . "]" . $errors );
			echo "<script>alert('Something went wrong - contact Matt!!\\n" . $errors . "'); console.log('" . $errors  . "');</script>";
    	}
}

else if(isset($_POST['Restock'])) 
{
        $auth = trim($_POST["AuthPass517"]);
        if($auth == "2385") {
	        $id = trim($_POST["RestockDropdown"]); 
	        $date = date('Y-m-d H:i:s'); 
	        $numberOfCans = trim($_POST["NumberOfCans"]); 
	        $cost = trim($_POST["Cost"]); 
	                
	        $db->exec("INSERT INTO Restock VALUES($id, '$date', $numberOfCans, $cost)");
	        $db->exec("UPDATE Item SET TotalExpenses = TotalExpenses + $cost, BackstockQuantity = BackstockQuantity + $numberOfCans, TotalCans = TotalCans + $numberOfCans where ID = $id");

	        echo "Restock added successfully.<br>";
	    } else  {
			echo "YOU ARE NOT LOGGED IN!<br>";
		}
}

else if(isset($_POST['SendEmail'])) 
{
    require("PHPMailer_5.2.0/class.phpmailer.php");
    require("PHPMailer_5.2.0/class.smtp.php");

    $mail = new PHPMailer();

    $mail->IsSMTP();                                      // set mailer to use SMTP
    $mail->Host = "smtp.gmail.com";  // specify main and backup server
    $mail->Port       = 587; 
    $mail->SMTPSecure = "tls";
    $mail->SMTPAuth = true;     // turn on SMTP authentication
    $mail->Username = "gamerkd16@gmail.com";  // SMTP username
    $mail->Password = "symbg2"; // SMTP password

    $mail->From = "item@penguins.com";
    $mail->FromName = "ItemStock";
    $mail->AddAddress("mtm4440@g.rit.edu", "Matt");          // name is optional

    $mail->WordWrap = 50;                                 // set word wrap to 50 characters
    $mail->IsHTML(true);                                  // set email format to HTML

    $mail->Subject = "Your ItemStock Inventory!";
    
    $emailMessage = "";

	$emailMessage = $emailMessage . "<b><u>SOLD OUT:</u></b><br><br>";
    $results = $db->query('SELECT Name, BackstockQuantity, ShelfQuantity, Price, ID FROM Item WHERE NOT Retired = 1 AND (ShelfQuantity  + BackstockQuantity = 0) ORDER BY BackstockQuantity ASC, ShelfQuantity ASC');
    while ($row = $results->fetchArray()) {
        $item_name = $row[0];
        $backstockquantity = $row[1];
        $shelfquantity = $row[2];
        $price = $row[3];
        $item_id = $row[4];
        $emailMessage = $emailMessage . "<b>$item_name:</b> " . $backstockquantity . " in cube, " . $shelfquantity . " in fridge<br>";
    }

    $emailMessage = $emailMessage . "<br><b><u>RUNNING LOW:</u></b><br><br>";
    $results = $db->query('SELECT Name, BackstockQuantity, ShelfQuantity, Price, ID FROM Item WHERE NOT Retired = 1 AND (ShelfQuantity + BackstockQuantity > 0) AND (ShelfQuantity + BackstockQuantity < 12 ) ORDER BY BackstockQuantity ASC, ShelfQuantity ASC');
    while ($row = $results->fetchArray()) {
        $item_name = $row[0];
        $backstockquantity = $row[1];
        $shelfquantity = $row[2];
        $price = $row[3];
        $item_id = $row[4];
        $emailMessage = $emailMessage . "<b>$item_name:</b> " . $backstockquantity . " in cube, " . $shelfquantity . " in fridge<br>";
    }

    $emailMessage = $emailMessage . "<br><b><u>IN STOCK:</u></b><br><br>";
    $results = $db->query('SELECT Name, BackstockQuantity, ShelfQuantity, Price, ID FROM Item WHERE NOT Retired = 1  AND (ShelfQuantity + BackstockQuantity > 12) ORDER BY BackstockQuantity ASC');
    while ($row = $results->fetchArray()) {
        $item_name = $row[0];
        $backstockquantity = $row[1];
        $shelfquantity = $row[2];
        $price = $row[3];
        $item_id = $row[4];
        $emailMessage = $emailMessage . "<b>$item_name:</b> " . $backstockquantity . " in cube, " . $shelfquantity . " in fridge<br>";
    }

    $mail->Body    = $emailMessage;
    $mail->AltBody = $emailMessage;

    $emailSent = false;

    $emailSent = $mail->Send();

    error_log("EMAIL SENT: " . $emailMessage);

    if( !$emailSent )
    {
       echo "Message could not be sent:<br><br> $emailMessage";
       echo "Mailer Error: " . $mail->ErrorInfo;
       exit;
    }

    

    echo "Inventory has been emailed! :D";
    //mail("mtm4440@rit.edu", "ItemStock Inventory", "Here is the inventory so far.<br>New Line.\nNew Line2 <b>bold</b>", "-fitemstock@penguinore.com" );
}
/*
if(isset($_POST['DailyAmount'])) 
{
        $id = trim($_POST["DailyAmountDropdown"]); 
        $date = date('Y-m-d H:i:s'); 
        $backstockQuantity = trim($_POST["BackstockQuantity"]); 
        $shelfQuantity = trim($_POST["ShelfQuantity"]); 
        $price = trim($_POST["CurrentPrice"]); 
        $restocked = 0;
                
        $backstockQuantityBefore = 0;
        $shelfQuantityBefore = 0;
        $priceBefore = 0;

        
        $results = $db->query("SELECT ID, BackStockQuantity, ShelfQuantity, Price FROM Item WHERE ID = $id");
        while ($row = $results->fetchArray()) {
                $backstockQuantityBefore = $row[1];
                $shelfQuantityBefore = $row[2];
                $priceBefore = $row[3];
                
        }
        $totalCansBefore = $backstockQuantityBefore + $shelfQuantityBefore;
        $totalCans = $backstockQuantity + $shelfQuantity;
        
        $income = ($totalCansBefore - $totalCans) * $priceBefore;
        
        $db->exec("INSERT INTO Daily_Amount VALUES($id, '$date', $backstockQuantity, $shelfQuantity, $backstockQuantityBefore, $shelfQuantityBefore, $price, $restocked)");
        $db->exec("UPDATE Item SET Price = $price, DateModified = $date where ID = $id");
        $db->exec("UPDATE Item SET TotalIncome = TotalIncome + $income, BackstockQuantity = $backstockQuantity, ShelfQuantity = $shelfQuantity, DateModified = '$date', ModifyType = 'Counted' where ID = $id");
        echo "Daily_Amount added successfully.<br>";
}
*/

else if(isset($_POST['DailyAmount'])) 
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
			
	        for ($i = 0; $i < count($id_all); $i++) {
			    $id = $id_all[$i];
			    $backstockQuantity = $backstockQuantity_all[$i];
			    $shelfQuantity = $shelfQuantity_all[$i];
			    $price = $price_all == 0 ? "" : $price_all[$i];
				$itemName = "N/A";

		        $results = $db->query("SELECT ID, BackStockQuantity, ShelfQuantity, Price, Name FROM Item WHERE ID = $id");
		        while ($row = $results->fetchArray()) {
		                $backstockQuantityBefore = $row[1];
		                $shelfQuantityBefore = $row[2];
		                $priceBefore = $row[3];
		                $itemName = $row[4];
		        }
				
				if( $price == "") {
					$price = $priceBefore;
				}
				
				if( $shelfQuantity > $shelfQuantityBefore ) {
					//New item was added to the fridge
					$slackMessageItems = $slackMessageItems . "*" . $itemName . ":* " . $shelfQuantityBefore . " cans --> *" . $shelfQuantity . " cans*\\n";
				}				
		        $totalCansBefore = $backstockQuantityBefore + $shelfQuantityBefore;
		        $totalCans = $backstockQuantity + $shelfQuantity;
		        
		        $income = ($totalCansBefore - $totalCans) * $priceBefore;

				error_log("SQ1:" . "INSERT INTO Daily_Amount (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Price, Restock) VALUES($id, '$date',  $backstockQuantityBefore, $backstockQuantity, $shelfQuantityBefore, $shelfQuantity, $price, $restocked)");
		        $db->exec("INSERT INTO Daily_Amount (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Price, Restock) VALUES($id, '$date', $backstockQuantityBefore, $backstockQuantity, $shelfQuantityBefore, $shelfQuantity, $price, $restocked)");
				error_log("SQ2:" . "UPDATE Item SET Price = $price, DateModified = '$date' where ID = $id" );
		        $db->exec("UPDATE Item SET Price = $price, DateModified = '$date' where ID = $id");
		        $db->exec("UPDATE Item SET TotalIncome = TotalIncome + $income, BackstockQuantity = $backstockQuantity, ShelfQuantity = $shelfQuantity, DateModified = '$date', ModifyType = 'Counted' where ID = $id");
		        echo "Daily_Amount added successfully for Item #$id.<br>";
		    }
			
			if( $slackMessageItems != "" && $sendToSlack == true) {
				$slackMessage = ":grinning:  SODA RESTOCKED! :grinning:\\n\\n" . $slackMessageItems ."\\n\\nWant to see what\'s in the $location, the prices, what has been discontinued, the trends of different items being bought, or just general statistics? View the <http://penguinore.net/sodastock_home/$url>";
				
				error_log("SLACK: [" . $slackMessage . "]" );
				echo "<script type='text/javascript'>sendSlackMessagetoRandom('$slackMessage');</script>";
			}
		
			
		} else  {
			echo "YOU ARE NOT LOGGED IN!<br>";
		}
}
?>