<?php
$db = new SQLite3("db/item.db");
if (!$db) die ($error);

include("foodstock_functions.php");
date_default_timezone_set('America/New_York');

Login($db);

$userMessage = "";

// ------------------------------------
// HANDLE USER QUERIES
// ------------------------------------
if(isset($_POST['Purchase'])) {
    if( $_SESSION['InactiveUser'] == true ) {
        $userMessage = "You cannot purchase items when you are inactive.";
    } else {
        $itemsInCart = json_decode($_POST['items']);
        $cashOnly = isset( $_POST['CashOnly'] );

        $totalPrice = 0.0;
        $totalSavings = 0.0;

        $errors = "";
        $purchaseMessage = "";
        $itemType = "UNKNOWN";

        foreach( $itemsInCart as $itemID ) {
            $results = $db->query("SELECT * FROM Item WHERE ID =" . $itemID );
            $row = $results->fetchArray();
            $itemPrice = $row['Price'];
            $originalItemPrice = $itemPrice;
            $discountItemPrice = $row['DiscountPrice'];

            // Apply the discount
            if( $discountItemPrice != "" && $discountItemPrice != 0 ) {
                $totalSavings += ( $itemPrice - $discountItemPrice );
                $itemPrice = $discountItemPrice;
            }

            if( $discountItemPrice == "" ) {
                $discountItemPrice = 0;
            }

            $itemName = $row['Name'];
            $shelfQuantity = $row['ShelfQuantity'];
            $backstockQuantity = $row['BackstockQuantity'];
            $itemType = $row['Type'];

            if( $shelfQuantity - 1 <= -1 ) {
                $errors .= "Not enough " . $itemName . " in stock. Purchase of THAT ITEM cancelled. Contact Matt.\\n";
            } else {
                
                if( $shelfQuantity - 1 == 0 ) {
                    sendSlackMessageToMatt( "*Item Name:* " . $itemName . "\n*Buyer:* " . $_SESSION['UserName'], ":negative_squared_cross_mark:", "OUT OF STOCK BY PURCHASE", "#791414" );
                }
                
                $date = date('Y-m-d H:i:s', time());
                $totalPrice += $itemPrice;

                $cashOnlyInteger = $cashOnly ? 1 : 0;
                
                $inventoryQuery = "INSERT INTO Daily_Amount (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Price, Restock, PurchaseID) VALUES($itemID, '$date', $backstockQuantity, $backstockQuantity, $shelfQuantity," . ($shelfQuantity - 1) . ", $itemPrice, 0, -2)";
                $db->exec( $inventoryQuery );
                $dailyAmountID = $db->lastInsertRowID();
                
                $purchaseHistoryQuery = "INSERT Into Purchase_History (UserID, ItemID, Cost, DiscountCost, Date, CashOnly, DailyAmountID) VALUES (" . $_SESSION['UserID'] . "," . $itemID . "," . $originalItemPrice . "," . $discountItemPrice . ",'" . $date . "'," . $cashOnlyInteger .  ", " . $dailyAmountID . ")";
                $itemQuery = "UPDATE Item SET TotalIncome = TotalIncome + $itemPrice, DateModified = '$date', ModifyType = 'Purchased by " . $_SESSION['UserID'] . "' where ID = $itemID";
                $itemCountQuery = "UPDATE Item SET ShelfQuantity = ShelfQuantity - 1 where ID = $itemID";
                $informationQuery = "UPDATE Information SET Income = Income + $itemPrice where ItemType = '$itemType'";
                

                $db->exec( $purchaseHistoryQuery );
                $db->exec( $itemCountQuery );
                $db->exec( $itemQuery );
                $db->exec( $informationQuery );


                $purchaseMessage = $purchaseMessage . "- " . $itemName . " ($" . number_format($itemPrice, 2) . ")\n";
            }
        }
        
        $totalPrice = round( $totalPrice, 2 );
        $totalSavings = round( $totalSavings, 2);

        if( !$cashOnly ) {
            $typeOfBalance = $itemType . "Balance";
            $typeOfSavings = $itemType . "Savings";

            $balanceUpdateQuery = "UPDATE User SET $typeOfBalance = $typeOfBalance + $totalPrice , $typeOfSavings = $typeOfSavings + $totalSavings where UserID = " . $_SESSION['UserID'];
            error_log("Balance Update [" . $balanceUpdateQuery . "]" );
            $db->exec( $balanceUpdateQuery );

            $_SESSION[$typeOfBalance] = $_SESSION[$typeOfBalance] + $totalPrice;
        }

        $purchaseMessage = $purchaseMessage . "*Total Price:* $" . number_format($totalPrice, 2) . "\n";

        if( !$cashOnly ) {
            $purchaseMessage = $purchaseMessage . "*Your " . $itemType . " Balance:* $" . number_format($_SESSION[$typeOfBalance],2) . "\n";
        } else {
            $purchaseMessage = $purchaseMessage . "*THIS PURCHASE WAS CASH-ONLY*\n";
        }

        if( $_SESSION["SlackID"] == "" ) {
            sendSlackMessageToMatt( "Failed to send notification for " . $_SESSION['UserName'] . ". Create a SlackID!", ":no_entry:", $itemType . "Stock - ERROR!!", "#bb3f3f" );
        } else {
            sendSlackMessageToUser( $_SESSION["SlackID"],  $purchaseMessage , ":shopping_trolley:" , $itemType . "Stock - RECEIPT", "#3f5abb" );
        }

        sendSlackMessageToMatt( "*(" . strtoupper($_SESSION['UserName']) . ")*\n" . $purchaseMessage, ":shopping_trolley:", $itemType . "Stock - RECEIPT", "#3f5abb" );

        if( $errors != "" ) {
            error_log( "ERROR: [" . $_SESSION['UserID'] . "]" . $errors );
            $userMessage = "Something went wrong - contact Matt!! " . $errors;
            sendSlackMessageToMatt( "Errors: " . $errors, ":no_entry:", $itemType . "Stock - ERROR!!", "#bb3f3f" );
        }
    }

} else if(isset($_POST['Request'])) {
    $itemType = trim($_POST["ItemTypeDropdown_Request"]);
    $date = date('Y-m-d H:i:s');
    $itemName = $db->escapeString(trim($_POST["ItemName_Request"]));
    $note = $db->escapeString(trim($_POST["Note_Request"]));
    $userID = $_SESSION['UserID'];
    $username = $_SESSION['UserName'];
    $slackID = $_SESSION['SlackID'];
        
    if( $slackID == "" ) {
        sendSlackMessageToMatt( "Failed to send notification for " . $username . ". Create a SlackID!", ":no_entry:", $itemType . "Stock - ERROR!!", "#bb3f3f"  );
    } else {
        sendSlackMessageToUser( $slackID,  "*Item Name:* " . $itemName . "\n*Notes:* " . $note, ":ballot_box_with_ballot:", "REQUEST RECEIVED", "#863fbb" );
    }

    sendSlackMessageToMatt( "*(" . strtoupper($username) . ")*\n*Item Name:* " . $itemName . "\n*Notes:* " . $note, ":ballot_box_with_ballot:", "REQUEST RECEIVED", "#863fbb" );

    $db->exec("INSERT INTO Requests (UserID, ItemName, Date, Note, ItemType) VALUES($userID, '$itemName', '$date', '$note', '$itemType')");

    $userMessage = "Request submitted successfully.";
} else if(isset($_POST['Shopping'])) {
        $itemID = trim($_POST["ItemDropdown"]);
        $store = trim($_POST["StoreDropdown"]);
        $date = date('Y-m-d H:i:s');
        $packQuantity = trim($_POST["PackQuantity"]);
        $price = trim($_POST["Price"]);
        $priceType = trim($_POST["PriceType"]);
        $submitter = trim($_POST["Submitter"]);
    
        $regularPrice = "null";
        $salePrice = "null";
    
    
        if( $priceType == "sale" ) {
            $salePrice = $price;
        } else {
            $regularPrice = $price;
        }
    
        if( $store == "BestProfits" ) {
            $store = "null";
            $regularPrice = "null";
            $salePrice = "null";
        } else {
            $store = "'$store'";
        }
    
    
        $shoppingQuery = "INSERT INTO Shopping_Guide (ItemID, PackQuantity, RegularPrice, SalePrice, Store, User, Date) VALUES($itemID, $packQuantity, $regularPrice, $salePrice, $store, '$submitter', '$date')";
        error_log("Shopping Query: [" . $shoppingQuery . "]" );
        $db->exec( $shoppingQuery );
} else {
    // ------------------------------------
    // HANDLE ADMIN QUERIES
    // ------------------------------------
    if( !IsAdminLoggedIn() ) {
        $userMessage = "YOU ARE NOT LOGGED IN AS ADMIN!";
    } else {
        if(isset($_POST['AddItem'])) {
            $name = trim($_POST["ItemName"]);
            $date = date('Y-m-d H:i:s');
            $chartColor = trim($_POST["ChartColor"]);
            $price = trim($_POST["CurrentPrice"]);
            $itemType = trim($_POST["ItemType"]);
    
            $addItemQuery = "INSERT INTO Item (Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, Type) VALUES( '$name', '$date', '$chartColor', 0, 0, 0, $price, 0.00, 0.00, '$itemType')";
            $db->exec( $addItemQuery );
    
            $userMessage = "Item \"$name\" added successfully.";
        } else if(isset($_POST['EditItem'])) {
            $itemType = trim($_POST["ItemType"]);
    
            $id = trim($_POST["Edit" . $itemType . "Dropdown"]);
            $name = trim($_POST["EditItemName" . $itemType]);
            $chartColor = trim($_POST["EditChartColor" . $itemType]);
            $price = trim($_POST["EditPrice" . $itemType]);
            $discountPrice = trim($_POST["EditDiscountPrice" . $itemType]);
            $imageURL = trim($_POST["EditImageURL" . $itemType]);
            $thumbURL = trim($_POST["EditThumbURL" . $itemType]);
            $unitName = trim($_POST["EditUnitName" . $itemType]);
            $unitNamePlural = trim($_POST["EditUnitNamePlural" . $itemType]);
            $alias = trim($_POST["EditAlias" . $itemType]);
            $status = trim($_POST["EditStatus" . $itemType]);
            $expirationDate = trim($_POST["EditExpirationDate" . $itemType]);
    
            error_log("Status: " . $status );
            $retired = $status == "active" ? 0 : 1;
    
            $editItemQuery = "UPDATE Item SET Name='$name', ChartColor='$chartColor', Price = $price, DiscountPrice = $discountPrice, Retired = $retired, ImageURL = '$imageURL', ThumbURL = '$thumbURL', UnitName = '$unitName', UnitNamePlural = '$unitNamePlural', Alias = '$alias', ExpirationDate = '$expirationDate' where ID = $id";
            error_log("Edit Item Query: [" . $editItemQuery . "]" );
            $db->exec( $editItemQuery );
    
            $userMessage = "Item \"$name\" edited successfully.";
        } else if(isset($_POST['EditUser'])) {
            $id = trim($_POST["EditUserDropdown"]);
            $slackID = trim($_POST["SlackID"]);
    
            $inactive = 0;
            $resetPassword = false;
    
            if( isset($_POST["Inactive"]) ) {
                $inactive = 1;
            }
    
            if( isset($_POST["ResetPassword"]) ) {
                $resetPassword = true;
            }
    
            $uniqueID = uniqid();
            $resetPasswordQuery = "";
    
            if( $resetPassword == true ) {
                error_log("restting");
                $resetPasswordQuery = " Password='" . sha1($uniqueID) . "',";
                $userMessage = $userMessage . "Password for user was reset to \"$uniqueID\". ";
            }
    
            $editItemQuery = "UPDATE User SET SlackID='$slackID', $resetPasswordQuery Inactive = $inactive where UserID = $id";
            error_log("Edit User Query: [" . $editItemQuery . "]" );
            $db->exec( $editItemQuery );
    
            $userMessage = $userMessage . "User edited successfully.";
        } else if(isset($_POST['Restock'])) {
            $id = trim($_POST["RestockDropdown"]);
            $itemType = trim($_POST["ItemType"]);
            $date = date('Y-m-d H:i:s');
            $numberOfCans = trim($_POST["NumberOfCans"]);
            $cost = trim($_POST["Cost"]);
            $multiplier = trim($_POST["Multiplier"]);
            
            if( $multiplier > 1 ) {
                $numberOfCans *= $multiplier;
                $cost *= $multiplier;
            }
    
            $db->exec("INSERT INTO Restock (ItemID, Date, NumberOfCans, Cost) VALUES($id, '$date', $numberOfCans, $cost)");
            $db->exec("UPDATE Item SET TotalExpenses = TotalExpenses + $cost, BackstockQuantity = BackstockQuantity + $numberOfCans, TotalCans = TotalCans + $numberOfCans where ID = $id");
            $db->exec("UPDATE Information SET Expenses = Expenses + $cost where ItemType = '$itemType'");
    
            $userMessage = "Restocked successfully.";
        }  else if(isset($_POST['Defective'])) {
            $id = trim($_POST["DefectiveDropdown"]);
            $itemType = trim($_POST["ItemType"]);
            $date = date('Y-m-d H:i:s');
            $numberOfCans = trim($_POST["NumberOfUnits"]);
            
            $results = $db->query("SELECT Price, BackstockQuantity, ShelfQuantity From Item where ID = $id");
            $row = $results->fetchArray();
            $price = round($row['Price'], 2);
            $shelfQuantity = $row['ShelfQuantity'];
            $backstockQuantity = $row['BackstockQuantity'];
            
            $totalQuantity = $shelfQuantity + $backstockQuantity;
            
            if( $numberOfCans > $totalQuantity ) {
                $userMessage = "Error: Trying to defect out more than you have.";
            } else {
                $shelfDecrement = 0;
                $backstockDecrement = 0;
                
                if( $numberOfCans > $shelfQuantity ) {
                    $backstockDecrement = $numberOfCans -  $shelfQuantity;
                    $shelfDecrement  = $shelfQuantity;
                } else {
                    $shelfDecrement = $numberOfCans;
                }
                
                $db->exec("INSERT INTO Defectives (ItemID, Date, Amount, Price) VALUES($id, '$date', $numberOfCans, $price)");
                $db->exec("UPDATE Item SET ShelfQuantity=ShelfQuantity-$shelfDecrement, BackstockQuantity=BackstockQuantity-$backstockDecrement WHERE ID = $id");
                $userMessage = "Defectives successfully.";
            }
        } else if(isset($_POST['Payment'])) {
            error_log( "Incoming payment." );
            $userID = trim($_POST["UserDropdown"]);
            $paymentMonth = trim($_POST["MonthDropdown"]);
            $date = date('Y-m-d H:i:s');
            $snackAmount = trim($_POST["SnackAmount"]);
            $sodaAmount = trim($_POST["SodaAmount"]);
            $note = trim($_POST["Note"]);
            $method = trim($_POST["MethodDropdown"]);
             
            $isUserPayment = $userID > 0;
            $isBalanceValid = true;
    
            if( $isUserPayment ) {
                error_log( "User payment found." );
                $results = $db->query("SELECT SodaBalance, SnackBalance, SlackID, UserName From User where UserID = $userID");
                $row = $results->fetchArray();
                $sodaBalance = round($row['SodaBalance'], 2);
                $snackBalance = round($row['SnackBalance'], 2);
                $slackID = $row['SlackID'];
                $username = $row['UserName'];
    
                if( $sodaAmount > $sodaBalance ) {
                    $isBalanceValid = false;
                    error_log( "Bad Soda balance. Amount: [" . $sodaAmount . "] Balance: [" . $sodaBalance . "]" );
                    $userMessage = "This payment [$" . number_format($sodaAmount, 2) . "] is larger than the user\"s Soda Balance of [$" . number_format($sodaBalance,2) . "]. Payment denied!";
                }

                if( $snackAmount > $snackBalance) {
                    $isBalanceValid = false;
                    error_log( "Bad Snack balance. Amount: [" . $snackAmount . "] Balance: [" . $snackBalance . "]" );
                    $userMessage = "This payment [$" . number_format($snackAmount, 2) . "] is larger than the user\"s Snack Balance of [$" . number_format($snackBalance,2) . "]. Payment denied!";
                }
                
                if( $isBalanceValid ) {
                    $newSodaBalance = $sodaBalance - $sodaAmount;
                    error_log( "Reduced Soda balance [" . $newSodaBalance . "] is [" . $sodaBalance . " - " . $sodaAmount . "]" );
                    
                    $newSnackBalance = $snackBalance - $snackAmount;
                    error_log( "Reduced Snack balance [" . $newSnackBalance . "] is [" . $snackBalance . " - " . $snackAmount . "]" );
                    
                    if( $slackID == "" ) {
                        sendSlackMessageToMatt( "Failed to send notification for " . $username . ". Create a SlackID!", ":no_entry:", "FoodStock - ERROR!!", "#bb3f3f");
                    } else {
                        sendSlackMessageToUser( $slackID,  "Soda Payment: *$" . number_format($sodaAmount,2) . "*\nYour Current Soda Balance: *$" . number_format($newSodaBalance,2) . "*\n\nSnack Payment: *$" . number_format($snackAmount,2) . "*\nYour Current Snack Balance: *$" . number_format($newSnackBalance,2) . "*", ":dollar:", "PAYMENT RECEIVED", "#127b3c" );
                    }
                    
                    sendSlackMessageToMatt(   "*(" . strtoupper($username) . ")*\nSoda Payment: *$" . number_format($sodaAmount,2) . "*\nTheir Current Soda Balance: *$" . number_format($newSodaBalance,2) . "*\n\nSnack Payment: *$" . number_format($snackAmount,2) . "*\nTheir Current Snack Balance: *$" . number_format($newSnackBalance,2) . "*", ":dollar:", "PAYMENT RECEIVED", "#127b3c" );
                }
            }
    
            if( $isBalanceValid ) {
                $db->exec("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment) VALUES($userID, '$method', $sodaAmount, '$date', '$note', 'Soda', '$paymentMonth')");
                $db->exec("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment) VALUES($userID, '$method', $snackAmount, '$date', '$note', 'Snack', '$paymentMonth')");
    
                if( $isUserPayment ) {
                    $db->exec("UPDATE User SET SodaBalance = SodaBalance - " . round($sodaAmount, 2) . " where UserID = $userID");
                    $db->exec("UPDATE User SET SnackBalance = SnackBalance - " . round($snackAmount, 2) . " where UserID = $userID");
                }
    
                $db->exec("UPDATE Information SET ProfitActual = ProfitActual + $sodaAmount where ItemType = 'Soda'");
                $db->exec("UPDATE Information SET ProfitActual = ProfitActual + $snackAmount where ItemType = 'Snack'");
    
                $userMessage = "Payment added successfully.";
            }
        } else if(isset($_POST['Inventory'])) {
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
    
    
                $results = $db->query("SELECT ID, BackstockQuantity, ShelfQuantity, Price, Name, Type, UnitName FROM Item WHERE ID = $id");
                while ($row = $results->fetchArray()) {
                    $backstockQuantityBefore = $row['BackstockQuantity'];
                    $shelfQuantityBefore = $row['ShelfQuantity'];
                    $priceBefore = $row['Price'];
                    $itemName = $row['Name'];
                    $itemType = $row['Type'];
                    $itemUnits = $row['UnitName'];
                }
    
                if( $price == "") {
                    $price = $priceBefore;
                }
    
                if( $shelfQuantity > $shelfQuantityBefore ) {
                    //New item was added to the fridge
                    $slackMessageItems = $slackMessageItems . "*" . $itemName . ":* " . $shelfQuantityBefore . " " . $itemUnits ."s --> *" . $shelfQuantity . " " . $itemUnits ."s*\n";
                }
                
                $totalCansBefore = $backstockQuantityBefore + $shelfQuantityBefore;
                $totalCans = $backstockQuantity + $shelfQuantity;
                
                $income = ($totalCansBefore - $totalCans) * $priceBefore;
    
                $dailyAmountQuery = "INSERT INTO Daily_Amount (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Price, Restock, PurchaseID) VALUES($id, '$date', $backstockQuantityBefore, $backstockQuantity, $shelfQuantityBefore, $shelfQuantity, $price, $restocked, -3)";
                error_log("DA: [" . $dailyAmountQuery . "]" );
                $db->exec( $dailyAmountQuery );
                $db->exec("UPDATE Item SET Price = $price, DateModified = '$date' where ID = $id");
                $db->exec("UPDATE Item SET TotalIncome = TotalIncome + $income, BackstockQuantity = $backstockQuantity, ShelfQuantity = $shelfQuantity, OutOfStock = '', DateModified = '$date', ModifyType = 'Counted' where ID = $id");
                $db->exec("UPDATE Information SET Income = Income + $income where ItemType = '$itemType'");
    
            }
    
            $userMessage = "Inventory was successful for " . count($id_all) . " items.";
    
            $emoji = ":soda:";
            $location = "fridge";
            $page = "sodastock.php";
    
            if( $itemType == "Snack" ) {
                $emoji = ":cookie:";
                $location = "cabinet";
                $page = "snackstock.php";
            }
            if( $slackMessageItems != "" && $sendToSlack == true) {
                $slackMessage = $slackMessageItems ."\n\nWant to see what is in the $location, the prices, what has been discontinued, the trends of different items being bought, or just general statistics? View the NEW <http://penguinore.net/$page>";
    
                sendSlackMessageToRandom($slackMessage, $emoji, $itemType. "Stock - REFILL" );
            }
        }
    }
}

if( isset( $_POST['redirectURL'] ) ) {
    
    if( $userMessage != "" ) {
        $_SESSION['UserMessage'] = $userMessage;
    }
    
    // Redirect to page
    header( "Location:" . $_POST['redirectURL'] );
}
?>