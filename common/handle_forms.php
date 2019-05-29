<?php
include(__DIR__ . "/../appendix.php" );
$db = new SQLite3( getDB() );
if (!$db) die ($error);


include( SESSION_FUNCTIONS_PATH );
include(UI_FUNCTIONS_PATH);
include(SLACK_FUNCTIONS_PATH);
include_once(LOG_FUNCTIONS_PATH);
date_default_timezone_set('America/New_York');

Login($db);

$userMessage = "";

$startTime = time();
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
        $totalCredits = 0.0;
        $totalSavings = 0.0;

        $errors = "";
        $purchaseMessage = "";
        $itemType = "UNKNOWN";
        $itemsOutOfStock = array();

        $creditsOfUser = $_SESSION['Credits'];
        $creditsLeftOfUser = $creditsOfUser;

        foreach( $itemsInCart as $itemID ) {
            $startTimeItem = time();
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

                $refillTriggerAmount = $itemType == "Snack" ? 3 : 1;
                $refillTrigger = "";
                if( $shelfQuantity - 1 <= $refillTriggerAmount ) {

                    if( $shelfQuantity - 1 <= 0  ) {
                        $itemsOutOfStock[] = $itemName;
                    }
                    $date = date('Y-m-d H:i:s', time());
                    $refillTrigger = " ,RefillTrigger = 1, OutOfStockDate = '$date', OutOfStockReporter='SodaBot'";
                }

                $date = date('Y-m-d H:i:s', time());
                $useCredits = 0;

                if( $creditsLeftOfUser > 0 && !$cashOnly ) {
                    $useCredits = $creditsLeftOfUser;
                    $creditsLeftOfUser -= $itemPrice;

                    if( $creditsLeftOfUser < 0 ) {
                        // If we went over their credit limit, we need to add it to their balance now
                        $totalPrice += abs( $creditsLeftOfUser );
                        $totalCredits += $useCredits;
                        $creditsLeftOfUser = 0;
                    } else {
                        $useCredits = $itemPrice;
                        $totalCredits += $itemPrice;
                    }
                } else {
                    $totalPrice += $itemPrice;
                }

                $cashOnlyInteger = $cashOnly ? 1 : 0;

                $inventoryQuery = "INSERT INTO Daily_Amount (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Price, Restock, PurchaseID) VALUES($itemID, '$date', $backstockQuantity, $backstockQuantity, $shelfQuantity," . ($shelfQuantity - 1) . ", $itemPrice, 0, -2)";
                $db->exec( $inventoryQuery );
                $dailyAmountID = $db->lastInsertRowID();

                $purchaseHistoryQuery = "INSERT Into Purchase_History (UserID, ItemID, Cost, DiscountCost, Date, CashOnly, DailyAmountID, UseCredits) VALUES (" . $_SESSION['UserID'] . "," . $itemID . "," . $originalItemPrice . "," . $discountItemPrice . ",'" . $date . "'," . $cashOnlyInteger .  ", " . $dailyAmountID . ",$useCredits)";

                $newTotalIncome = addToValue( $db, "Item", "TotalIncome", $itemPrice, "where ID = $itemID", true );
                $itemQuery = "UPDATE Item SET TotalIncome = $newTotalIncome, DateModified = '$date', ModifyType = 'Purchased by " . $_SESSION['UserID'] . "' where ID = $itemID";

                $itemCountQuery = "UPDATE Item SET ShelfQuantity = ShelfQuantity - 1 $refillTrigger where ID = $itemID";

                $newIncome = addToValue( $db, "Information", "Income", $itemPrice, "where ItemType = '$itemType'", true );
                $informationQuery = "UPDATE Information SET Income = $newIncome where ItemType = '$itemType'";


                $db->exec( $purchaseHistoryQuery );
                $db->exec( $itemCountQuery );
                $db->exec( $itemQuery );
                $db->exec( $informationQuery );


                $purchaseMessage = $purchaseMessage . "- " . $itemName . " (" . getPriceDisplayWithDollars( $itemPrice ) . ")\n";
            }
            $stopTimeItem = time();
            $totalTimeItem = $stopTimeItem - $startTimeItem;

            log_benchmark( "Time to complete purchase for [$itemName]: $totalTimeItem seconds" );
        }

        if( !$cashOnly ) {
            $typeOfBalance = $itemType . "Balance";
            $typeOfSavings = $itemType . "Savings";

            $newBalance = addToValue( $db, "User", $typeOfBalance, $totalPrice, "where UserID = " . $_SESSION['UserID'], true );
            $newSavings = addToValue( $db, "User", $typeOfSavings, $totalSavings, "where UserID = " . $_SESSION['UserID'], true );
            $balanceUpdateQuery = "UPDATE User SET $typeOfBalance = $newBalance , $typeOfSavings = $newSavings, Credits = $creditsLeftOfUser where UserID = " . $_SESSION['UserID'];
            log_sql("Balance Update [" . $balanceUpdateQuery . "]" );
            $db->exec( $balanceUpdateQuery );

            $_SESSION[$typeOfBalance] = $_SESSION[$typeOfBalance] + $totalPrice;
        }

        if( $totalCredits > 0 ) {
            $purchaseMessage = $purchaseMessage . "*Total Credits:* " . getPriceDisplayWithDollars( $totalCredits ) . "\n";
        }

        if( $totalPrice > 0 ) {
            $purchaseMessage = $purchaseMessage . "*Total Price:* " . getPriceDisplayWithDollars($totalPrice) . "\n";
        }

        if( !$cashOnly ) {
            $totalBalance = $_SESSION['SodaBalance'] + $_SESSION['SnackBalance'];
            $purchaseMessage = $purchaseMessage . "*Your Balance:* " . getPriceDisplayWithDollars( $totalBalance ) . "\n";
        } else {
            $purchaseMessage = $purchaseMessage . "*THIS PURCHASE WAS CASH-ONLY*\n";
        }

        if( $_SESSION["SlackID"] == "" ) {
            sendSlackMessageToMatt( "Failed to send notification for " . $_SESSION['UserName'] . ". Create a SlackID!", ":no_entry:", $itemType . "Stock - ERROR!!", "#bb3f3f" );
        } else {
            sendSlackMessageToUser( $_SESSION["SlackID"],  $purchaseMessage , ":shopping_trolley:" , $itemType . "Stock - RECEIPT", "#3f5abb" );
        }

        sendSlackMessageToMatt( "*(" . strtoupper($_SESSION['FirstName'] . " " . $_SESSION['LastName']) . ")*\n" . $purchaseMessage, ":shopping_trolley:", $itemType . "Stock - RECEIPT", "#3f5abb" );
        $userMessage = "Purchase Completed";
        $_SESSION['PurchaseCompleted'] = 1;

        if( $errors != "" ) {
            error_log( "ERROR: [" . $_SESSION['UserID'] . "]" . $errors );
            $userMessage = "Something went wrong - contact Matt!! " . $errors;
            sendSlackMessageToMatt( "Errors: " . $errors, ":no_entry:", $itemType . "Stock - ERROR!!", "#bb3f3f" );
        }

        if( count( $itemsOutOfStock) > 0 ) {
            foreach($itemsOutOfStock  as $item ) {
                sleep( 1 );
                sendSlackMessageToMatt( "*Item Name:* " . $item . "\n*Buyer:* " . $_SESSION['FirstName'] . " " . $_SESSION['LastName'], ":negative_squared_cross_mark:", "OUT OF STOCK BY PURCHASE", "#791414" );
            }
        }
    }

} else if(isset($_POST['Preferences'])) {
    $userID = $_SESSION['UserID'];
    $anonAnimal = trim( $_POST['Preferences_AnonAnimal'] );
    $showDiscontinued = 0;
    $showCashOnly = 0;
    $showCredit = 0;
    $showShelf = 0;
    $showItemStats = 0;
    $subscribeRestocks = 0;

    if( isset($_POST["Preferences_ShowDiscontinued"]) ) {
        $showDiscontinued = 1;
    }

    if( isset($_POST["Preferences_ShowCashOnly"]) ) {
        $showCashOnly = 1;
    }

    if( isset($_POST["Preferences_ShowCredit"]) ) {
        $showCredit = 1;
    }

    if( isset($_POST["Preferences_ShowShelf"]) ) {
        $showShelf = 1;
    }

    if( isset($_POST["Preferences_ShowItemStats"]) ) {
        $showItemStats = 1;
    }

    if( isset($_POST["Preferences_SubscribeRestocks"]) ) {
        $subscribeRestocks = 1;
    }

    $results = $db->query("SELECT count(*) as Total FROM User WHERE AnonName = '$anonAnimal' AND UserID != $userID" );
    $row = $results->fetchArray();
    $total = $row['Total'];

    $anonNameUpdate = "";
    if( $total > 0 ) {
        $userMessage = "The Anonymous Animal '$anonAnimal' is already being used by another user.";
    } else {
        $anonNameUpdate = ", AnonName = '$anonAnimal' ";
        $_SESSION['AnonName'] = $anonAnimal;
        $userMessage = "User Preferences saved.";
    }

    $editUserQuery = "UPDATE User SET ShowDiscontinued=$showDiscontinued, ShowCashOnly=$showCashOnly, ShowCredit=$showCredit, ShowItemStats=$showItemStats, ShowShelf=$showShelf, SubscribeRestocks=$subscribeRestocks $anonNameUpdate where UserID = $userID";
    log_sql("Edit User Query: [" . $editUserQuery . "]" );
    $db->exec( $editUserQuery );

    $_SESSION['ShowDiscontinued'] = $showDiscontinued;
    $_SESSION['ShowCashOnly'] = $showCashOnly;
    $_SESSION['ShowCredit'] = $showCredit;
    $_SESSION['ShowItemStats'] = $showItemStats;
    $_SESSION['ShowShelf'] = $showShelf;
    $_SESSION['SubscribeRestocks'] = $subscribeRestocks;

} else if(isset($_POST['Request'])) {
    $itemType = trim($_POST["ItemTypeDropdown_Request"]);
    $date = date('Y-m-d H:i:s');
    $itemName = $db->escapeString(trim($_POST["ItemName_Request"]));
    $note = $db->escapeString(trim($_POST["Note_Request"]));
    $userID = $_SESSION['UserID'];
    $username = $_SESSION['FirstName'] . " " . $_SESSION['LastName'];
    $slackID = $_SESSION['SlackID'];

    if( $slackID == "" ) {
        sendSlackMessageToMatt( "Failed to send notification for " . $username . ". Create a SlackID!", ":no_entry:", $itemType . "Stock - ERROR!!", "#bb3f3f"  );
    } else {
        sendSlackMessageToUser( $slackID,  "*Item Name:* " . $itemName . "\n*Notes:* " . $note, ":ballot_box_with_ballot:", "REQUEST RECEIVED", "#863fbb" );
    }

    sendSlackMessageToMatt( "*(" . strtoupper($username) . ")*\n*Item Name:* " . $itemName . "\n*Notes:* " . $note, ":ballot_box_with_ballot:", "REQUEST RECEIVED", "#863fbb" );

    $db->exec("INSERT INTO Requests (UserID, ItemName, Date, Note, ItemType,Priority) VALUES($userID, '$itemName', '$date', '$note', '$itemType', '')");

    $userMessage = "Request submitted successfully.";
} else if(isset($_POST['Shopping'])) {
        $itemID = trim($_POST["ItemDropdown"]);
        $store = trim($_POST["StoreDropdown"]);
        $date = date('Y-m-d H:i:s');
        $packQuantity = trim($_POST["PackQuantity"]);
        $price = convertDecimalToWholeCents( trim($_POST["Price"]) );
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
        log_sql("Shopping Query: [" . $shoppingQuery . "]" );
        $db->exec( $shoppingQuery );
} else {
    // ------------------------------------
    // HANDLE ADMIN QUERIES
    // ------------------------------------
    if( !IsAdminLoggedIn() ) {
        $userMessage = "YOU ARE NOT LOGGED IN AS ADMIN!";
    } else {
        if(isset($_POST['AddItem'])) {
            $userMessage = addItem( $db, $_POST["ItemName"], $_POST["ChartColor"], $_POST["CurrentPrice"], $_POST["ItemType"] );
        } else if(isset($_POST['EditItem'])) {
            $itemType = trim($_POST["ItemType"]);

            $id = trim($_POST["Edit" . $itemType . "Dropdown"]);
            $name = trim($_POST["EditItemName" . $itemType]);
            $chartColor = trim($_POST["EditChartColor" . $itemType]);
            $price = convertDecimalToWholeCents( trim($_POST["EditPrice" . $itemType]) );
            $discountPrice = convertDecimalToWholeCents( trim($_POST["EditDiscountPrice" . $itemType]) );
            $imageURL = trim($_POST["EditImageURL" . $itemType]);
            $thumbURL = trim($_POST["EditThumbURL" . $itemType]);
            $unitName = trim($_POST["EditUnitName" . $itemType]);
            $unitNamePlural = trim($_POST["EditUnitNamePlural" . $itemType]);
            $alias = trim($_POST["EditAlias" . $itemType]);
            $currentFlavor = trim($_POST["EditCurrentFlavor" . $itemType]);
            $status = trim($_POST["EditStatus" . $itemType]);
            $expirationDate = trim($_POST["EditExpirationDate" . $itemType]);

            $retired = $status == "active" ? 0 : 1;

            $updateImageURL = "";
            $updateThumbURL = "";

            if ( is_uploaded_file($_FILES['uploadedImage']['tmp_name'] ) ) {
                log_debug( "FOUND TMP: [" .$_FILES['uploadedImage']['tmp_name'] . "]" );
                log_debug( "FOUND NAME: [" .$_FILES['uploadedImage']['name'] . "]" );
                $targetFileName = basename( $_FILES['uploadedImage']['name'] );
                log_debug( "FOUND TARGET: [" .$targetFileName . "]" );
                $target = IMAGES_NORMAL_PATH . $targetFileName;
                log_debug( "FOUND TARGET PATH: [" .$target . "]" );

                if( !move_uploaded_file( $_FILES['uploadedImage']['tmp_name'], $target ) ) {
                    error_log(" THERE WAS AN ERROR UPLOADING THIS IMAGE: " . $_FILES['uploadedImage']['tmp_name'] );
                } else {
                    $updateImageURL = ", ImageURL = '$targetFileName'";
                    log_debug( "FOUND UPDATE: [" .$updateImageURL . "]" );
                }
            }

            if ( is_uploaded_file($_FILES['uploadedThumb']['tmp_name'] ) ) {
                $targetFileName = basename( $_FILES['uploadedThumb']['name'] );
                $target = IMAGES_THUMBNAILS_PATH . $targetFileName;
                if( !move_uploaded_file( $_FILES['uploadedThumb']['tmp_name'], $target ) ) {
                    error_log(" THERE WAS AN ERROR UPLOADING THIS THUMBNAIL: " . $_FILES['uploadedThumb']['tmp_name'] );
                } else {
                    $updateThumbURL = ", ThumbURL = '$targetFileName'";
                }
            }

            $editUserQuery = "UPDATE Item SET Name='$name', ChartColor='$chartColor', Price = $price, DiscountPrice = $discountPrice, Retired = $retired $updateImageURL $updateThumbURL, UnitName = '$unitName', UnitNamePlural = '$unitNamePlural', Alias = '$alias', CurrentFlavor = '$currentFlavor', ExpirationDate = '$expirationDate' where ID = $id";
            log_sql("Edit Item Query: [" . $editUserQuery . "]" );
            $db->exec( $editUserQuery );

            $userMessage = "Item \"$name\" edited successfully.";
        } else if(isset($_POST['SendBot'])) {
            $botMessage = trim($_POST["BotMessage"]);
            $emoji = trim($_POST["Emoji"]);
            $botName = trim($_POST["BotName"]);
            $emoji = str_replace(":", "", $emoji );

            $_SESSION['BotName'] = $botName;
            $_SESSION['Emoji'] = $emoji;
            sendSlackMessageToNerdHerd($botMessage, ":$emoji:", $botName );
        } else if(isset($_POST['EditUser'])) {
            $id = trim($_POST["EditUserDropdown"]);
            $slackID = trim($_POST["SlackID"]);
            $anonName = trim($_POST["AnonName"]);

            $inactive = 0;
            $resetPassword = false;
            $isCoop = 0;

            if( isset($_POST["Inactive"]) ) {
                $inactive = 1;
            }

            if( isset($_POST["IsCoop"]) ) {
                $isCoop = 1;
            }

            if( isset($_POST["ResetPassword"]) ) {
                $resetPassword = true;
            }

            $uniqueID = uniqid();
            $resetPasswordQuery = "";

            if( $resetPassword == true ) {
                log_debug("Resetting Password");
                $resetPasswordQuery = " Password='" . sha1($uniqueID) . "',";
                $userMessage = $userMessage . "Password for user was reset to \"$uniqueID\". ";
            }

            $editUserQuery = "UPDATE User SET SlackID='$slackID', AnonName='$anonName', $resetPasswordQuery Inactive = $inactive, IsCoop = $isCoop where UserID = $id";
            log_sql("Edit User Query: [" . $editUserQuery . "]" );
            $db->exec( $editUserQuery );

            $userMessage = $userMessage . "User edited successfully.";
        } else if(isset($_POST['CreditUser'])) {
            $id = trim($_POST["EditUserDropdown"]);
            $creditAmountInDecimal = trim($_POST["CreditAmount"]);
            $creditAmountWholeCents = convertDecimalToWholeCents( $creditAmountInDecimal );

            $returnCredits = false;
            $validCredits = true;

            $creditResults = $db->query("SELECT Credits, SlackID, FirstName, LastName, UserName From User where UserID = $id");
            $creditRow = $creditResults->fetchArray();
            $currentCredits = $creditRow['Credits'];
            $slackID = $creditRow['SlackID'];
            $username = $creditRow['UserName'];
            $name = $creditRow['FirstName'] . " " . $creditRow['LastName'];

            $creditMessage = "Welp, I got nothing. Talk to Matt.";

            if( isset($_POST["ReturnCredits"]) ) {
                if( $currentCredits - $creditAmountWholeCents < 0 ) {
                    $validCredits = false;
                    $userMessage = $userMessage . "Failure! User only has $currentCredits cents -  cannot subtract $creditAmountWholeCents cents!";
                }
                $creditAmountWholeCents = $creditAmountWholeCents * -1;

                $creditMessage = "*$" . $creditAmountInDecimal . "* credits have been deducted from your account.";
            } else {
                $creditMessage = "*$" . $creditAmountInDecimal . "* credits have been added to your account.";
            }

            if( $validCredits ) {
                $editUserQuery = "UPDATE User SET Credits=Credits + $creditAmountWholeCents where UserID = $id";
                log_sql("Edit User Query: [" . $editUserQuery . "]");
                $db->exec($editUserQuery);

                $date = date('Y-m-d H:i:s', time());

                $purchaseHistoryQuery = "INSERT Into Purchase_History (UserID, ItemID, Cost, Date ) VALUES ($id, " . CREDIT_ID . ", $creditAmountWholeCents,'" . $date . "')";
                log_sql("Update Purchase_History Query: [" . $purchaseHistoryQuery . "]");
                $db->exec($purchaseHistoryQuery);

                $userMessage = $userMessage . "User credited successfully with " . getPriceDisplayWithDollars($creditAmountWholeCents);

                if( $slackID == "" ) {
                    sendSlackMessageToMatt( "Failed to send notification for " . $username . ". Create a SlackID!", ":no_entry:", "FoodStock - ERROR!!", "#bb3f3f" );
                } else {
                    sendSlackMessageToUser($slackID,  $creditMessage , ":label:" , $itemType . "Stock - CREDITS", "#3f5abb" );
                    sendSlackMessageToMatt( "*(" . strtoupper($name ) . ")*\n" . $creditMessage, ":label:", "FoodStock - CREDITS", "#3f5abb" );

                }
            }
        } else if(isset($_POST['Restock'])) {
            $id = trim($_POST["RestockDropdown"]);
            $itemType = trim($_POST["ItemType"]);
            $date = date('Y-m-d H:i:s');
            $numberOfCans = trim($_POST["NumberOfCans"]);
            $cost = convertDecimalToWholeCents(  trim($_POST["Cost"] ) );
            $multiplier = trim($_POST["Multiplier"]);

            if( $multiplier > 1 ) {
                $numberOfCans *= $multiplier;
                $cost *= $multiplier;
            }

            $restockTrigger = "";
            if( $numberOfCans > 3 ) {
                $date = date('Y-m-d H:i:s', time());
                $restockTrigger = " RestockTrigger = 0, IsBought = 0,";
            }

            $db->exec("INSERT INTO Restock (ItemID, Date, NumberOfCans, Cost) VALUES($id, '$date', $numberOfCans, $cost)");

            $newTotalExpenses = addToValue( $db, "Item", "TotalExpenses", $cost, "where ID = $id", true );
            $db->exec("UPDATE Item SET TotalExpenses = $newTotalExpenses, $restockTrigger BackstockQuantity = BackstockQuantity + $numberOfCans, TotalCans = TotalCans + $numberOfCans where ID = $id");

            $newExpenses = addToValue( $db, "Information", "Expenses", $cost, "where ItemType = '$itemType'", true );
            $db->exec("UPDATE Information SET Expenses = $newExpenses where ItemType = '$itemType'");

            $userMessage = "Restocked successfully.";
        }  else if(isset($_POST['Defective'])) {
            $id = trim($_POST["DefectiveDropdown"]);
            $itemType = trim($_POST["ItemType"]);
            $date = date('Y-m-d H:i:s');
            $numberOfCans = trim($_POST["NumberOfUnits"]);

            $results = $db->query("SELECT Price, BackstockQuantity, ShelfQuantity From Item where ID = $id");
            $row = $results->fetchArray();
            $price = $row['Price'];
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
            log_payment( "Incoming payment." );
            $userID = trim($_POST["UserID"]);
            $paymentMonth = trim($_POST["Month"]);
            $date = date('Y-m-d H:i:s');
            $snackAmount = convertDecimalToWholeCents( trim($_POST["SnackAmount"]) );
            $sodaAmount = convertDecimalToWholeCents( trim($_POST["SodaAmount"]) );
            $note = trim($_POST["Note"]);
            $method = trim($_POST["MethodDropdown"]);

            if( $userID > 0 ) {
                log_payment( "User payment found." );
                $results = $db->query("SELECT SodaBalance, SnackBalance, SlackID, UserName, FirstName, LastName From User where UserID = $userID");
                $row = $results->fetchArray();
                $sodaBalance = $row['SodaBalance'];
                $snackBalance = $row['SnackBalance'];
                $slackID = $row['SlackID'];
                $username = $row['FirstName'] . " " . $row['LastName'];

                $isBalanceValid = true;

                if( $sodaAmount > $sodaBalance ) {
                    $isBalanceValid = false;
                    error_log( "Bad Soda balance. Amount: [" . $sodaAmount . "] Balance: [" . $sodaBalance . "]" );
                    $userMessage = "This payment [" . getPriceDisplayWithDollars( $sodaAmount ) . "] is larger than the user\"s Soda Balance of [" . getPriceDisplayWithDollars( $sodaBalance ) . "]. Payment denied!";
                }

                if( $snackAmount > $snackBalance) {
                    $isBalanceValid = false;
                    error_log( "Bad Snack balance. Amount: [" . $snackAmount . "] Balance: [" . $snackBalance . "]" );
                    $userMessage = "This payment [" . getPriceDisplayWithDollars( $snackAmount ) . "] is larger than the user\"s Snack Balance of [" . getPriceDisplayWithDollars( $snackBalance ) . "]. Payment denied!";
                }

                if( $isBalanceValid ) {
                    $newSodaBalance = $sodaBalance - $sodaAmount;
                    log_payment( "Reduced Soda balance [" . $newSodaBalance . "] is [" . $sodaBalance . " - " . $sodaAmount . "]" );

                    $newSnackBalance = $snackBalance - $snackAmount;
                    log_payment( "Reduced Snack balance [" . $newSnackBalance . "] is [" . $snackBalance . " - " . $snackAmount . "]" );

                    $newTotalBalance = $newSodaBalance + $newSnackBalance;
                    $balance = $sodaBalance + $snackBalance;
                    $amount = $sodaAmount + $snackAmount;

                    $paymentMessage = "Your payment with $method was received for $paymentMonth.\n\n" .
                    "Your Current Balance: *" . getPriceDisplayWithDollars( $newTotalBalance ) . "*       (*" . getPriceDisplayWithDollars( $balance ) . "* original balance  -  *" . getPriceDisplayWithDollars( $amount ) . "* payment)";

                    if( $slackID == "" ) {
                        sendSlackMessageToMatt( "Failed to send notification for " . $username . ". Create a SlackID!", ":no_entry:", "FoodStock - ERROR!!", "#bb3f3f");
                    } else {
                        sendSlackMessageToUser( $slackID,  $paymentMessage, ":dollar:", "PAYMENT RECEIVED", "#127b3c" );
                    }

                    sendSlackMessageToMatt( "*(" . strtoupper($username) . ")*\n$paymentMessage", ":dollar:", "PAYMENT RECEIVED", "#127b3c" );

                    // DEV: Changing this code? Change the payment code for Auditing as well (above)
                    $db->exec("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment) VALUES($userID, '$method', $sodaAmount, '$date', '$note', 'Soda', '$paymentMonth')");
                    $db->exec("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment) VALUES($userID, '$method', $snackAmount, '$date', '$note', 'Snack', '$paymentMonth')");

                    $newSodaBalance = addToValue( $db, "User", "SodaBalance", $sodaAmount, "where UserID = $userID", false );
                    $newSnackBalance = addToValue( $db, "User", "SnackBalance", $snackAmount,  "where UserID = $userID", false );

                    $db->exec("UPDATE User SET SodaBalance = $newSodaBalance where UserID = $userID");
                    $db->exec("UPDATE User SET SnackBalance = $newSnackBalance where UserID = $userID");

                    $newProfitSoda = addToValue( $db, "Information", "ProfitActual", $sodaAmount, "where ItemType = 'Soda'", true );
                    $newProfitSnack = addToValue( $db, "Information", "ProfitActual", $snackAmount, "where ItemType = 'Snack'", true );

                    $db->exec("UPDATE Information SET ProfitActual = $newProfitSoda where ItemType = 'Soda'");
                    $db->exec("UPDATE Information SET ProfitActual = $newProfitSnack where ItemType = 'Snack'");

                    $userMessage = "Payment added successfully.";
                }
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
            $auditAmount = convertDecimalToWholeCents( $_POST["AuditAmount"] );
            $itemType = $_POST["ItemType"];
            $price_all = 0;

            if( isset($_POST['CurrentPrice']) ) {
                $price_all = $_POST["CurrentPrice"];
            }
            $restocked = 0;

            $backstockQuantityBefore = 0;
            $shelfQuantityBefore = 0;
            $priceBefore = 0;
            $auditID = 0;
            $auditMessage = "";

            if( $auditAmount != "" ) {
                log_debug(" Audit found [$auditAmount] - [$itemType]" );
                $auditQuery = "INSERT INTO Audit (Date, MissingMoney, ItemType) VALUES('$date', 0, '$itemType')";
                log_sql(" Audit Query [$auditQuery]" );
                $db->exec( $auditQuery );
                $auditID = $db->lastInsertRowID();
                $auditMessage = "(AUDIT #$auditID)";

                $firstOfMonth = mktime(0, 0, 0, date("m"), 1, date("Y") );
                $monthLabel = date('F Y', $firstOfMonth);
                $paymentQuery = "INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment, AuditID) VALUES(0, '', $auditAmount, '$date', 'Audited', '$itemType', '$monthLabel', $auditID)";
                log_sql(" Payment Query [$paymentQuery]" );
                $db->exec( $paymentQuery);
                $newProfit = addToValue( $db, "Information", "ProfitActual", $auditAmount, "where ItemType = '$itemType'", true );
                $db->exec("UPDATE Information SET ProfitActual = $newProfit where ItemType = '$itemType'");
            }

            $slackMessageItems = "";
            $itemType = "";

            for ($i = 0; $i < count($id_all); $i++) {
                $startTimeItem = time();
                $id = $id_all[$i];
                $backstockQuantity = $backstockQuantity_all[$i];
                $shelfQuantity = $shelfQuantity_all[$i];
                $price = $price_all == 0 ? "" : convertDecimalToWholeCents( $price_all[$i] );
                $itemName = "N/A";


                $results = $db->query("SELECT ID, BackstockQuantity, ShelfQuantity, Price, Name, Type, UnitName, UnitNamePlural, CurrentFlavor FROM Item WHERE ID = $id");
                while ($row = $results->fetchArray()) {
                    $backstockQuantityBefore = $row['BackstockQuantity'];
                    $shelfQuantityBefore = $row['ShelfQuantity'];
                    $priceBefore = $row['Price'];
                    $itemName = $row['Name'];
                    $itemType = $row['Type'];
                    $itemUnits = $row['UnitName'];
                    $itemUnitsPlural = $row['UnitNamePlural'];
                    $currentFlavor = $row['CurrentFlavor'];

                    if( $currentFlavor != "" ) {
                        $currentFlavor = "[" . $currentFlavor . "] ";
                    }
                }

                if( $price == "") {
                    $price = $priceBefore;
                }

                $refillTrigger = "";
                if( $shelfQuantity > $shelfQuantityBefore ) {
                    //New item was added to the fridge
                    $priceDisplay = getPriceDisplayWithEnglish( $priceBefore );

                    $slackMessageItems = $slackMessageItems . "*" . $itemName . " $currentFlavor:* " . $shelfQuantityBefore . " " .
                            ( $shelfQuantityBefore == 1 ? $itemUnits : $itemUnitsPlural ) .
                            " --> *" . $shelfQuantity . " " .
                            ( $shelfQuantity == 1 ? $itemUnits : $itemUnitsPlural ) . "*    ($priceDisplay)\n";

                    // Only clear the trigger of the items that are refilled
                    if( $shelfQuantity > 3 ) {
                        $refillTrigger = "RefillTrigger = 0, IsBought = 0,";
                    }
                }

                $totalCansBefore = $backstockQuantityBefore + $shelfQuantityBefore;
                $totalCans = $backstockQuantity + $shelfQuantity;

                $income = ($totalCansBefore - $totalCans) * $priceBefore;

                $restockTrigger = "";
                if( $backstockQuantity <= 3 ) {
                    $date = date('Y-m-d H:i:s', time());
                    $restockTrigger = " RestockTrigger = 1, ";
                }

                $refillTriggerAmount = $itemType == "Snack" ? 3 : 1;

                if( $shelfQuantity <= $refillTriggerAmount ) {
                    $date = date('Y-m-d H:i:s', time());
                    $refillTrigger = " RefillTrigger = 1, ";
                }

                $dailyAmountQuery = "INSERT INTO Daily_Amount (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Price, Restock, PurchaseID, AuditID) VALUES($id, '$date', $backstockQuantityBefore, $backstockQuantity, $shelfQuantityBefore, $shelfQuantity, $price, $restocked, -3, $auditID)";
                log_sql("DA Query: [" . $dailyAmountQuery . "]" );
                $db->exec( $dailyAmountQuery );
                $db->exec("UPDATE Item SET Price = $price, DateModified = '$date' where ID = $id");

                $newTotalIncome = addToValue( $db, "Item", "TotalIncome", $income, "where ID = $id", true );
                $db->exec("UPDATE Item SET TotalIncome = $newTotalIncome, BackstockQuantity = $backstockQuantity, ShelfQuantity = $shelfQuantity, $refillTrigger $restockTrigger DateModified = '$date', ModifyType = 'Counted' where ID = $id");

                $newIncome = addToValue( $db, "Information", "Income", $income, "where ItemType = '$itemType'", true );
                $db->exec("UPDATE Information SET Income = $newIncome where ItemType = '$itemType'");

                $stopTimeItem = time();
                $totalTimeItem = $stopTimeItem - $startTimeItem;

                log_benchmark( "Time to complete purchase for [$itemName]: $totalTimeItem seconds" );
            }


            $userMessage = "Inventory was successful for " . count($id_all) . " items. $auditMessage";

            $emoji = ":soda:";
            $location = "fridge";
            $page = SODASTOCK_LINK;

            if( $itemType == "Snack" ) {
                $emoji = ":cookie:";
                $location = "cabinet";
                $page = SNACKSTOCK_LINK;
            }
            if( $slackMessageItems != "" && $sendToSlack == true) {
                $slackMessage = $slackMessageItems ."\n\nVisit <http://penguinore.net$page|Foodstock> to see the prices and inventory of all snacks & sodas.";

                sendSlackMessageToRandom($slackMessage, $emoji, $itemType. "Stock - REFILL" );

                $subscribeResults = $db->query("SELECT SlackID FROM User where SubscribeRestocks = 1" );
                while( $row = $subscribeResults->fetchArray() ) {
                    $slackIDToNotify = $row['SlackID'];
                    sendSlackMessageToUser( $slackIDToNotify, $slackMessage, $emoji, $itemType. "Stock - REFILL", "#000000" );
                }
            }
        }
    }
}

$stopTime = time();
$totalTime = $stopTime - $startTime;
log_benchmark( "Time to complete handle_forms: $totalTime seconds" );

if( isset( $_POST['redirectURL'] ) ) {

    if( $userMessage != "" ) {
        $_SESSION['UserMessage'] = $userMessage;
    }

    // Redirect to page
    header( "Location:" . $_POST['redirectURL'] );
}

function addItem( $db, $name, $chartColor, $price, $itemType ) {
    $name = trim($name);
    $date = date('Y-m-d H:i:s');
    $chartColor = trim($chartColor);
    $price = convertDecimalToWholeCents( trim( $price  ) );
    $itemType = trim( $itemType );

    $addItemQuery = "INSERT INTO Item (Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, Type, RefillTrigger, RestockTrigger, IsBought) VALUES( '$name', '$date', '$chartColor', 0, 0, 0, $price, 0.00, 0.00, '$itemType', 0, 0, 0)";
    $db->exec( $addItemQuery );

    return "Item \"$name\" added successfully.";
}
?>