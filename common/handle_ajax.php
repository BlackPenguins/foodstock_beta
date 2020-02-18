<?php
    include(__DIR__ . "/../appendix.php" );
    include( SESSION_FUNCTIONS_PATH );
    include(UI_FUNCTIONS_PATH);
    include(QUANTITY_FUNCTIONS_PATH);
    include_once(ACTION_FUNCTIONS_PATH);
    include(SLACK_FUNCTIONS_PATH);
    include_once(LOG_FUNCTIONS_PATH);

    session_start();
    date_default_timezone_set('America/New_York');

    $type = $_POST['type'];
        
    $db = new SQLite3( getDB() );
    if (!$db) die ($error);

    if($type == "CardArea" ) 
    {
        $itemType = $_POST['itemType'];
        $className = $_POST['className'];
        $location = $_POST['location'];
        $itemSearch = $_POST['itemSearch'];
        
        $nameQuery = "";

        if( $itemSearch != "" ) {
            $nameQuery = " AND ( Name Like :nameSearch OR Alias Like :aliasSearch)";
        }
        
        $cardQuery = "SELECT ID, Name, Date, TotalCans, " . getQuantityQuery() .
        ",Price, ItemIncome, ItemExpenses, ItemProfit, DateModified, " .
        "Retired, ImageURL, ThumbURL, UnitName, UnitNamePlural, DiscountPrice, CurrentFlavor, RefillTrigger, OutOfStockReporter, OutOfStockDate, u.FirstName " .
        "FROM Item i " .
        "LEFT JOIN User u ON i.VendorID = u.UserID " .
        "WHERE Type = :itemType " .$nameQuery . " AND Hidden != 1 " .
        "ORDER BY Retired, BackstockAmount DESC, ShelfAmount DESC";

        
        if( IsLoggedIn() ) {
            // Sort by user preference
            // This sort pretty much breaks them into 3 groups (bought ones at #1, discontinued at #3, the rest at #2) and sorts those 3,
            // then inside those groups it sorts by frequency, then shelf, then backstock
            $cardQuery = "SELECT ID, VendorID, Name, Date, TotalCans, " . getQuantityQuery() .
            ",Price, ItemIncome, ItemExpenses, ItemProfit, DateModified, " .
            "Retired, ImageURL, ThumbURL, UnitName, UnitNamePlural, (SELECT count(*) FROM Purchase_History p WHERE p.UserID = " . $_SESSION["UserID"] .
            " AND p.ItemID = i.ID AND p.Cancelled IS NULL) as Frequency, DiscountPrice, CurrentFlavor, RefillTrigger, OutOfStockReporter, OutOfStockDate, u.FirstName " .
            "FROM Item i " .
            "LEFT JOIN User u ON i.VendorID = u.UserID " .
            "WHERE Type = :itemType " .$nameQuery . " AND Hidden != 1 " .
            "ORDER BY CASE WHEN Retired = 1 AND ShelfAmount = 0 THEN '3' WHEN Frequency > 0 AND Retired = 0 THEN '1'  ELSE '2' END ASC, Frequency DESC, ShelfAmount DESC, BackstockAmount DESC";
        }
        
        $statement = $db->prepare( $cardQuery );
        $statement->bindValue( ":nameSearch", "%" .$itemSearch . "%" );
        $statement->bindValue( ":aliasSearch", "%" .$itemSearch . "%" );
        $statement->bindValue( ":itemType", $itemType );
        $results = $statement->execute();

        //---------------------------------------
        // BUILD ITEM CARDS
        //---------------------------------------
        $columnNumber = 1;
        while ($row = $results->fetchArray()) {
            $item_id = $row['ID'];
            $retired_item = $row['Retired'];

            $shelfAmount = $row['ShelfAmount'];
            $backstockAmount = $row['BackstockAmount'];

            $hideDiscontinued = true;

            if( isset( $_SESSION['ShowDiscontinued'] ) && $_SESSION['ShowDiscontinued'] != 0 ) {
                $hideDiscontinued = false;
            }

            if( $retired_item == 1 && $hideDiscontinued && $shelfAmount == 0 ) {
                continue;
            }

            $outOfStock = $row['RefillTrigger'];
            $outOfStockReporter = $row['OutOfStockReporter'];

            $item_name = $row['Name'];
            $supplierName = $row['FirstName'];

            $price = $row['Price'];
            $originalPrice = $price;
            $discountPrice = $row['DiscountPrice'];
            $imageURL = $row['ImageURL'];
            $hasDiscount = false;
            
            if( IsLoggedIn() && $discountPrice != "" ) {
                $price = $discountPrice;
                $hasDiscount = true;
            }
            
            $cardClass = "soda";
            
            if( $itemType == "Snack" ) {
                $cardClass = "snack";
            }
            
            $price_color = "#FFFFFF";
            $price_background_color = "#025F00";
            
            // On sale - YELLOW
            if($price < 50) {
                $price_color = "#000000";
                $price_background_color = "#FFD500";
                // Expensive - RED
            } else if( $price > 100) {
                $price_color = "#FFFFFF";
                $price_background_color = "#5f0000";
            }

            $priceDisplay = "";
            
            if( IsLoggedIn() && $hasDiscount == true ) {
                $priceDisplay = getPriceDisplayWithSymbol ( $discountPrice );
            } else {
                $priceDisplay = getPriceDisplayWithSymbol( $price );
            }
            
            $unitName = "item";
            $unitNamePlural = "items";
            
            if( $row['UnitName'] != "" ) {
                $unitName = $row['UnitName'];
            }
            
            if( $row['UnitNamePlural'] != "" ) {
                $unitNamePlural = $row['UnitNamePlural'];
            }
            
            $unitNameFinal = $shelfAmount > 1 ? $unitNamePlural : $unitName;
            
            $amountLeft = "N/A";
            $amountClass = "";
            $statusClass = "";
            $thumbnailClass = $cardClass;
            $buttonClass = $cardClass;
            $lightRopeClass = "";
            
            if( $retired_item == 1) {
                if($shelfAmount == 0) {
                    $amountLeft = "Discontinued";
                    $amountClass = "discontinued";
                    $statusClass = "post-module-discontinued";
                    $buttonClass = "disabled";
                    $lightRopeClass = "class='dead'";
                } else {
                    $amountClass = "discontinued-soon";
                    $amountLeft = "<div><span>$shelfAmount</span> $unitNameFinal Left</div>" .
                    "<div style='font-size: 0.8em; font-weight:bold; margin-top:5px; color:#ffe000'>(discontinued soon)</div>";
                }
            } else {
                if($shelfAmount == 0) {
                    $amountLeft = "SOLD OUT";
                    $amountClass = "sold-out";
                    $thumbnailClass = "sold-out";
                    $buttonClass = "disabled";
                } else {
                    $amountLeft = "<span>$shelfAmount</span> $unitNameFinal Left";
                }
            }
            
            echo "<input id='shelf_quantity_" . $item_id . "' type='hidden' value='" . $shelfAmount . "'/>";
            
            $statementPopularity = $db->prepare('SELECT ItemID, Date FROM Restock where ItemID = :itemID ORDER BY Date DESC');
            $statementPopularity->bindValue( ":itemID", $row['ID'] );
            $resultsPopularity = $statementPopularity->execute();

            $firstDate = "";
            $lastDate = "";
            $totalPurchases = 0;
            while ($rowPopularity = $resultsPopularity->fetchArray()) {
                if( $firstDate == "") {
                    $firstDate = $rowPopularity[1];
                }
                $lastDate = $rowPopularity[1];
                $totalPurchases++;
            }
            
            $date_before = DateTime::createFromFormat('Y-m-d H:i:s', $firstDate);
            $date_after = DateTime::createFromFormat('Y-m-d H:i:s', $lastDate);
            
            $days_ago = 0;
            
            if( $firstDate != "" && $lastDate != "" ) {
                if( $firstDate == $lastDate) {
                    $date_after = new DateTime();
                }
            
                $time_since = $date_before->diff($date_after);
                $days_ago = $time_since->format('%a');
            }
            
            $frequencyBought = "0";
            $purchaseDayInterval = "N/A";
            
            if( isset( $row['Frequency'] ) ) {
                $frequencyBought = $row['Frequency'];
            }
            
            if( $totalPurchases > 0 ) {
                $purchaseDayInterval = round($days_ago / $totalPurchases);
            }
            
            $previewImage = "";

            $christianInTheHouse = isset( $_SESSION['UserName'] ) && $_SESSION['UserName'] == "cmartinez" && $item_id == 65;

            if( $christianInTheHouse ) {
                $previewImage = "<img class='preview_zoom' src='" . IMAGES_LINK . "babe_ruth.jpg' />";
            } else if( $imageURL != "" ) {
                $previewImage = "<img class='preview_zoom' src='" . PREVIEW_IMAGES_NORMAL . $imageURL . "' />";
            } else {
                $previewImage = "<img class='preview_zoom' style='width: 100px; height: 100px; padding-top:70px;' src='" . IMAGES_LINK . "no_image.png' />";
            }

            $total_can_sold = $row['TotalCans'] - ( $backstockAmount + $shelfAmount );
            
            $statementDefect = $db->prepare("SELECT Sum(Amount) as 'TotalDefect' From Defectives where ItemID = :itemID" );
            $statementDefect->bindValue( ":itemID",  $row['ID'] );
            $resultsDefect = $statementDefect->execute();

            $rowDefect = $resultsDefect->fetchArray();
            $totalDefects = $rowDefect['TotalDefect'];
            
            $total_can_sold = $total_can_sold - $totalDefects;
            
            
            $reportButton = "";
            if( IsLoggedIn() && !IsInactive() && $outOfStock != "1" ) {
                $userName = $_SESSION['FirstName'] . " " . $_SESSION['LastName'];
                $reportButton = "<div style='position: absolute; right: 10px; top:-42px; cursor:pointer;' onclick='reportItemOutOfStock(\"$userName\"," . $row['ID'] . ",\"" . $row['Name'] . "\")'><img src='" . IMAGES_LINK . "low.png' title='Report Item Out of Stock'/></div>";
            }
            
            $outOfStockLabel = "";
            if( $outOfStock == "1" ) {
                $reportType = "out of stock";
                $reportClass = "out_of_stock";
                if( $outOfStockReporter == "StockBot" ) {
                    if( $shelfAmount > 0 ) {
                        $reportType = "running low";
                        $reportClass = "running_low";
                    }
                }

                $outOfStockLabel = "<div class='report-label $reportClass'>Reported as $reportType by " . $outOfStockReporter . "!</div>";
            }
            
            // ------------------
            // BUILD THE CARD
            // ------------------
            echo "<span class='post-module $statusClass'>";
//                 echo "<div class='snow'>";
                echo "<div class='thumbnail thumbnail-$thumbnailClass'>";
//                     echo "<img style='position:absolute; top:14px; right:17px; z-index:200;' src='" . IMAGES_LINK . "wreath.png'/>";
                     $smallPriceFont = "";

                     if( substr( $priceDisplay, 0, 1 ) == "$" ) {
                         $smallPriceFont = "style='font-size: 0.9em; padding:17.5px 0;'";
                     }
                    echo "<div $smallPriceFont class='price'>";
                        echo $priceDisplay;
                    echo "</div>";
                    echo $previewImage;
                echo "</div>";
                echo "</div>";
                echo "<div class='post-content'>";
                    echo $reportButton;

                    if( $row['DateModified'] != null ) {
                        $lastRefilled = DateTime::createFromFormat('Y-m-d H:i:s', $row['DateModified']);
                        $now = new DateTime();

                        $timeSinceLastRefill = $now->diff($lastRefilled);

                        $minutesSinceLastRefill = ($timeSinceLastRefill->d * 24 * 60) + ($timeSinceLastRefill->h * 60) + $timeSinceLastRefill->i;

                        if ($minutesSinceLastRefill <= 120 && $itemType == "Soda") {
                            echo "<div style='position: absolute; right: 10px; top:-80px;'><img src='" . IMAGES_LINK . "thermometer.png' title='This item was added to the fridge $minutesSinceLastRefill minutes ago and might not be cold yet.'/></div>";
                        }
                    }

                    echo "$outOfStockLabel";
                    echo "<div class='category category-$cardClass $amountClass'>$amountLeft</div>";

                    if( $supplierName != "" ) {
                        echo "<div title='This item is being sold by $supplierName through FoodStock' class='supplier'>Sold by $supplierName</div>";
                    }

                    if( $christianInTheHouse ) {
                        echo "<h1 class='title'>Fun-Size Babe Ruths</h1>";
                    } else {
                        echo "<h1 class='title'>" . $row['Name'] . "</h1>";
                    }
                    
                    $currentFlavor = $row['CurrentFlavor'];
                    if( $currentFlavor != "" ) {
                        echo "<h1 class='sub_title'><u>Current Flavor:</u> <i>$currentFlavor</i></h1>";
                    }

                    $income = $row['ItemIncome'];
                    $expense = $row['ItemExpenses'];
                    $profit = $row['ItemProfit'];

                    $profitClass = $profit > 0 ? "income" : "expenses";

                    $actionsClass = "actions_no_stats";

                    $showItemStats = true;
                    if( isset( $_SESSION['ShowShelf'] ) && $_SESSION['ShowItemStats'] == 0 ) {
                        $showItemStats = false;
                    }

                    if( $showItemStats ) {
                        $actionsClass = "actions";

                        if (IsAdminLoggedIn()) {

                            echo "<div class='stats'>";
                            echo "<span class='box box-expenses' title='Total Expenses'>";
                            echo "<span class='value'>" . getPriceDisplayWithDollars($expense) . "</span>";
                            echo "<span class='parameter'>Expenses</span>";
                            echo "</span>";

                            echo "<span style='border: 2px solid #000;' class='box box-$profitClass' title='Total Profit'>";
                            echo "<span class='value'>" . getPriceDisplayWithDollars($profit) . "</span>";
                            echo "<span class='parameter'>Profit</span>";
                            echo "</span>";

                            echo "<span class='box box-income' title='Total Income'>";
                            echo "<span class='value'>" . getPriceDisplayWithDollars($income) . "</span>";
                            echo "<span class='parameter'>Income</span>";
                            echo "</span>";
                            echo "</div>";
                        }

                        echo "<div class='stats'>";
                        echo "<span class='box box-$profitClass' title='You have bought this x times.'>";
                        echo "<span class='value'>$frequencyBought</span>";
                        echo "<span class='parameter'>Purchases</span>";
                        echo "</span>";

                        echo "<span class='box box-$profitClass' title='Restocked every x days.'>";
                        echo "<span class='value'>$purchaseDayInterval</span>";
                        echo "<span class='parameter'>Days</span>";
                        echo "</span>";

                        echo "<span class='box box-$profitClass' title='Total of x units sold.'>";
                        echo "<span class='value'>$total_can_sold</span>";
                        echo "<span class='parameter'>Total Sold</span>";
                        echo "</span>";
                        echo "</div>";
                    }

                    if( IsLoggedIn() && !IsInactive() ) {
                        echo "<div class='$actionsClass'>";
                            echo "<button id='add_button_" .  $row['ID'] . "' onclick='addItemToCart(" . $row['ID'] . ")' style='float:right;' class='btn btn-$buttonClass' title='Add item(s)'>Add</button>";
                            echo "<span style='float:right;' class='quantity' id='quantity_holder_" . $row['ID'] . "'>0</span>";
                            echo "<button id='remove_button_" .  $row['ID'] . "' onclick='removeItemFromCart(" . $row['ID'] . ")' style='float:left;' class='btn btn-$buttonClass' title='Remove item(s)'>Remove</button>";
                        echo "</div>"; //actions
                    }
                echo "</div>"; //post-content

                echo "<div>Something</div>";

//                echo "<ul style='top: 2px;' class='lightrope'>" .
//                 "<li $lightRopeClass title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
//                 "<li $lightRopeClass title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
//                 "<li $lightRopeClass title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
//                 "<li $lightRopeClass title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
//                 "<li $lightRopeClass title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
//                 "<li $lightRopeClass title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
//                 "<li $lightRopeClass title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
//                 "<li $lightRopeClass title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
//                 "<li $lightRopeClass title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
//                 "</ul>";
                
        echo "</span>"; //post-module
        }
    } 
    else if( $type == "ToggleRequestCompleted" ) {
        $requestID = $_POST['id'];
        $statement = $db->prepare("SELECT Completed FROM Requests WHERE ID = :requestID" );
        $statement->bindValue( ":requestID", $requestID );
        $results = $statement->execute();

        $row = $results->fetchArray();
        
        if( $row['Completed'] == 1 ) {
            $statement = $db->prepare( "UPDATE Requests set Completed = 0 WHERE ID = :requestID" );
            $statement->bindValue( ":requestID", $requestID );
            $statement->execute();
        } else {
            $statement = $db->prepare( "UPDATE Requests set Completed = 1, DateCompleted = :dateCompleted WHERE ID = :requestID" );
            $statement->bindValue( ":requestID", $requestID );
            $statement->bindValue( ":dateCompleted", date('Y-m-d H:i:s') );
            $statement->execute();
        }
    }
    else if( $type == "ToggleRequestPriority" ) {
        $statement = $db->prepare( "UPDATE Requests set Priority = :priority WHERE ID = :requestID" );
        $statement->bindValue( ":requestID", $_POST['id'] );
        $statement->bindValue( ":priority", $_POST['priority'] );
        $statement->execute();
    }
    else if( $type == "CancelPurchase" ) {
        $inventoryHistoryID = trim($_POST["DailyAmountID"]);

        $statement = $db->prepare("SELECT u.FirstName, u.LastName, u.UserName, u.SlackID, p.ItemDetailsID, p.ItemID, p.UserID " .
        "FROM Inventory_History d " .
        "JOIN Purchase_History p on d.ID = p.DailyAmountID " .
        "JOIN User u on p.UserID = u.UserID " .
        "WHERE d.ID = :inventoryHistoryID" );
        $statement->bindValue( ":inventoryHistoryID", $inventoryHistoryID );
        $results = $statement->execute();

        $row = $results->fetchArray();

        $date = date('Y-m-d H:i:s');
        $userID = $row['UserID'];
        $username = $row['FirstName'] . " " . $row['LastName'];
        $slackID = $row['SlackID'];
        $username = $row['UserName'];

        $itemDetailsID = $row['ItemDetailsID'];
        $itemID = $row['ItemID'];

        $db->exec("BEGIN;");

        $itemDetailsObj = addToShelfQuantity( $db, 1, $itemID, $itemDetailsID, "CANCEL SITE PURCHASE" )[0];

        $actualPrice = $itemDetailsObj->getSitePurchasePrice();
        $price = $itemDetailsObj->getFullPrice();
        $discountPrice = $itemDetailsObj->getDiscountPrice();

        $itemType = $itemDetailsObj->getItemType();
        $itemName = $itemDetailsObj->getItemName();

        $savings = $price - $discountPrice;


        $statement = $db->prepare( "UPDATE Inventory_History SET Cancelled = 1 WHERE ID = :inventoryHistoryID" );
        $statement->bindValue( ":inventoryHistoryID", $inventoryHistoryID );
        $statement->execute();

        $statement = $db->prepare( "UPDATE Purchase_History SET Cancelled = 1 WHERE DailyAmountID = :inventoryHistoryID" );
        $statement->bindValue( ":inventoryHistoryID", $inventoryHistoryID );
        $statement->execute();

        $statement = $db->prepare( "UPDATE Item SET ItemIncome = ItemIncome - :actualPrice where ID = :itemID" );
        $statement->bindValue( ":itemID", $itemID );
        $statement->bindValue( ":actualPrice", $actualPrice );
        $statement->execute();

        $statement = $db->prepare( "UPDATE Information SET SiteIncome = SiteIncome - :actualPrice where ItemType = :itemType" );
        $statement->bindValue( ":itemType", $itemType );
        $statement->bindValue( ":actualPrice", $actualPrice );
        $statement->execute();

        $typeOfBalance = $itemType . "Balance";
        $typeOfSavings = $itemType . "Savings";

        $statement = $db->prepare( "UPDATE User SET $typeOfBalance = $typeOfBalance - :actualPrice, $typeOfSavings = $typeOfSavings - :savings where UserID = :userID" );
        $statement->bindValue( ":actualPrice", $actualPrice );
        $statement->bindValue( ":savings", $savings );
        $statement->bindValue( ":userID", $userID );
        $statement->execute();

        $db->exec("COMMIT;");

        $purchaseMessage = $itemName . " (+ " . getPriceDisplayWithDollars( $actualPrice ) . ")";

        sendSlackMessageToUser( $slackID,  $purchaseMessage , ":money_mouth_face:" , $itemType . "Stock - REFUND", "#3f5abb", $username, true );
        // TODO MTM: Get rid of DateModified and ModifyType - never use it. Removing a column is hard. Create new temp table.


    }
    else if( $type == "CancelInventory" ) {
            // TODO MTM: Fix Cancel Inventory
//        $dailyAmountID = trim($_POST["DailyAmountID"]);
//
//        $results = $db->query("SELECT i.Type, d.ItemID, d.BackstockQuantityBefore, d.BackstockQuantity, d.ShelfQuantityBefore, d.ShelfQuantity, d.Price, d.RetailCost, d.ItemDetailsID from Inventory_History d JOIN Item i on d.ItemID =  i.ID WHERE d.ID = $dailyAmountID");
//        $row = $results->fetchArray();
//
//        $date = date('Y-m-d H:i:s');
//        $itemID = $row['ItemID'];
//        $itemType = $row['Type'];
//        $backstockBefore = $row['BackstockQuantityBefore'];
//        $backstockAfter= $row['BackstockQuantity'];
//        $shelfBefore = $row['ShelfQuantityBefore'];
//        $shelfAfter= $row['ShelfQuantity'];
//        $price = $row['Price'];
//        $itemDetailsID = $row['ItemDetailsID'];
//
//        $quantityBefore = $backstockBefore + $shelfBefore;
//        $quantityAfter = $backstockAfter + $shelfAfter;
//
//        $backstockDelta = $backstockBefore - $backstockAfter;
//        $shelfDelta = $shelfBefore - $shelfAfter;
//
//        $income = ($quantityBefore - $quantityAfter) * $price;
//
//        $cancelSQL = "UPDATE Inventory_History SET Cancelled = 1 WHERE ID = $dailyAmountID";
//        log_sql("Cancel SQL: [" . $cancelSQL . "]" );
//        $db->exec( $cancelSQL );
//
//        $newIncome = addToValue( $db, "Item", "TotalIncome", $income, "where ID = $itemID", false );
//        $itemSQL = "UPDATE Item SET TotalIncome = $newIncome, ModifyType = 'Cancelled', DateModified = '$date' where ID = $itemID";
//        log_sql("Item SQL: [" . $itemSQL . "]" );
//        $db->exec( $itemSQL );
//
//        addToBackstockQuantity( $db, $backstockDelta, $itemID, $itemDetailsID, "CANCEL MANUAL PURCHASE" );
//        addToShelfQuantity( $db, $shelfDelta, $itemID, $itemDetailsID, "CANCEL MANUAL PURCHASE" );
//
//        $newIncome = addToValue( $db, "Information", "Income", $income, "where ItemType = '$itemType'", false );
//        $infoSQL = "UPDATE Information SET Income = $newIncome where ItemType = '$itemType'";
//
//        log_sql("Info SQL: [" . $infoSQL . "]" );
//        $db->exec( $infoSQL );
    }
    else if( $type == "CancelRestock" ) {
        $restockID = trim($_POST["RestockID"]);

        $statement = $db->prepare("SELECT r.Cost, r.ItemID, r.NumberOfCans, i.Type From Restock r JOIN Item i on r.ItemID = i.ID where RestockID = :restockID" );
        $statement->bindValue( ":restockID", $restockID );
        $results = $statement->execute();

        $row = $results->fetchArray();

        $cost = $row['Cost'];
        $numberOfCans = $row['NumberOfCans'];
        $itemType = $row['Type'];
        $itemID = $row['ItemID'];

        $db->exec( "BEGIN;" );

        $statement = $db->prepare( "UPDATE Restock SET Cancelled = 1 where RestockID = :restockID" );
        $statement->bindValue( ":restockID", $restockID );
        $statement->execute();

        removeFromBackstockQuantity( $db, $numberOfCans, $itemID, "CANCEL RESTOCK" );

        $statement = $db->prepare( "UPDATE Item SET ItemExpenses = ItemExpenses - :cost, TotalCans = TotalCans - :numberOfUnits where ID = :itemID" );
        $statement->bindValue( ":cost", $cost );
        $statement->bindValue( ":numberOfUnits", $numberOfCans );
        $statement->bindValue( ":itemID", $itemID );
        $statement->execute();

        $statement = $db->prepare( "UPDATE Information SET SiteExpenses = SiteExpenses - :cost where ItemType = :itemType" );
        $statement->bindValue( ":cost", $cost );
        $statement->bindValue( ":itemType", $itemType );
        $statement->execute();

        $db->exec( "COMMIT;" );
    }
    else if( $type == "CancelPayment" ) {
        $paymentID = trim($_POST["PaymentID"]);
        cancelPayment( $db, $paymentID );
    }
    else if( $type == "NotifyUserOfPayment" ) {
        $month = trim($_POST["month"]);
        $year = trim($_POST["year"]);
        $displayMonth = trim($_POST["displayMonth"]);
        
        $statement = $db->prepare("SELECT UserID, UserName, UserName, SlackID, SodaBalance, SnackBalance, FirstName, LastName FROM User" );
        $results = $statement->execute();
        log_debug( "Notifying Users of Payment..." );
        
        while ($row = $results->fetchArray()) {


            $userName = $row['UserName'];
            $slackID = $row['SlackID'];
            $userID = $row['UserID'];
            $username = $row['UserName'];
            $name = $row["FirstName"] . " " . $row['LastName'];

//            if( $userName != "mmiles" ) {
//                continue;
//            }

            $totalArray = getTotalsForUser( $db, $userID, $month, $year, $displayMonth );
            
            $sodaTotal = $totalArray['SodaTotal'];
            $snackTotal = $totalArray['SnackTotal'];
            $sodaPaid = $totalArray['SodaPaid'];
            $snackPaid = $totalArray['SnackPaid'];
            
            $sodaTotalUnpaid = $sodaTotal - $sodaPaid;
            $snackTotalUnpaid = $snackTotal - $snackPaid;
            
            log_debug("Notifying User[$userName] Month[$month] Year[$year] Soda Total[$sodaTotal] Snack Total[$snackTotal] Soda Paid[$sodaTotalUnpaid] SnackPaid[$snackTotalUnpaid]");
            
            
            $totalBalance = $sodaTotalUnpaid + $snackTotalUnpaid ;
            
            if( $totalBalance > 0 ) {
                $slackMessage = "Good morning, $name! It's a new month. Here is your FoodStock Balance for $displayMonth:\n" .
                "*_Soda Balance:_* " . getPriceDisplayWithDollars( $sodaTotalUnpaid ) . "\n" .
                "*_Snack Balance:_* " . getPriceDisplayWithDollars( $snackTotalUnpaid ) . "\n\n" .
                        "*Total Balance Owed:* " . getPriceDisplayWithDollars( $totalBalance ) . "\n\n" .
                        "You can view more details on the <https://penguinore.net/purchase_history.php|Purchase/Payment History Page>. Have a great day! :grin:";
                
                sendSlackMessageToUser( $slackID, $slackMessage, ":credit:", "FoodStock Collection Agency", "#ff7a7a", $username, false );
            }
        }
    }
    else if( $type == "OutOfStockRequest" ) {
        $itemName = $_POST['itemName'];
        $reporter =  $_POST['reporter'];

        $statement = $db->prepare( "UPDATE Item set RefillTrigger = 1, OutOfStockDate = :date, OutOfStockReporter = :reporter WHERE ID = :itemID" );
        $statement->bindValue( ":date", date('Y-m-d H:i:s', time()) );
        $statement->bindValue( ":reporter", $reporter );
        $statement->bindValue( ":itemID", $_POST['itemID'] );
        $statement->execute();

        sendSlackMessageToMatt( "*Item Name:* " . $itemName . "\n*Reporter:* " . $reporter, ":negative_squared_cross_mark:", "OUT OF STOCK REPORT", "#791414" );
    }
    else if( $type == "DrawCart" ) {
        $itemQuantities = array();
        $itemPrices = array();
        $itemDiscountPrices = array();
        $itemNames = array();
        $itemThumbs = array();

        $itemsInCart = json_decode($_POST['items']);
        $url = $_POST['url'];

        foreach ($itemsInCart as $itemID) {
            if (array_key_exists($itemID, $itemQuantities) === false) {
                $statement = $db->prepare("SELECT * FROM Item WHERE ID = :itemID");
                $statement->bindValue( ":itemID",  $itemID );
                $results = $statement->execute();

                $row = $results->fetchArray();
                $itemName = $row['Name'];
                $itemPrice = $row['Price'];
                $itemDiscountPrice = $row['DiscountPrice'];
                $itemImage = $row['ImageURL'];

                $itemQuantities[$itemID] = 1;
                $itemNames[$itemID] = $itemName;
                $itemPrices[$itemID] = $itemPrice;
                $itemThumbs[$itemID] = $itemImage;
                $itemDiscountPrices[$itemID] = $itemDiscountPrice;
            } else {
                $itemQuantities[$itemID] = $itemQuantities[$itemID] + 1;
            }
        }

        $totalPrice = 0.0;
        $totalSavings = 0.0;
        $totalQuantity = 0;

        echo "<h3>Shopping Cart</h3>";

        echo "<table style='border-collapse:collapse; width:100%;'>";

        echo "<tr style='border-bottom:1px solid #dddddd'>";
        echo "<th style='color:#9c9e9c; width:50%;'>&nbsp;</th>";
        echo "<th style='color:#9c9e9c; text-align:left;'>Price</th>";
        echo "<th style='color:#9c9e9c;'>Quantity</th>";
        echo "</tr>";

        $creditsOfUser = $_SESSION['Credits'];
        $creditsLeft = $creditsOfUser;
        $creditsUsed = 0;

        foreach ($itemQuantities as $itemID => $itemQuantity) {
            $itemName = $itemNames[$itemID];
            $itemPrice = $itemPrices[$itemID];
            $itemDiscountPrice = $itemDiscountPrices[$itemID];
            $itemImageURL = $itemThumbs[$itemID];
            $costDisplay = "";

            if ($itemDiscountPrice != "") {
                $costDisplay = "<span class='red_price'>" . getPriceDisplayWithDollars($itemPrice) . "</span> " . getPriceDisplayWithDollars($itemDiscountPrice);
                $totalPriceForItem = ($itemDiscountPrice * $itemQuantity);
                $totalSavings += ($itemPrice - $itemDiscountPrice) * $itemQuantity;
            } else {
                $costDisplay = getPriceDisplayWithDollars($itemPrice);
                $totalPriceForItem = ($itemPrice * $itemQuantity);
            }


            $totalPrice += $totalPriceForItem;
            $totalQuantity += $itemQuantity;

            if ($creditsLeft > 0) {
                $creditsLeft = $creditsLeft - $totalPrice;

                if ($creditsLeft < 0) {
                    // If the credits are in the negative, that means that they spent them all and the negative amount is now a balance
                    $totalPrice = abs($creditsLeft);
                    $creditsUsed = $creditsOfUser;
                } else {
                    // No total, everything is paid in credits
                    $creditsUsed += $totalPrice;
                    $totalPrice = 0;

                }
            }

            echo "<tr style='border-bottom:1px solid #dddddd; padding:20px 0px; font-weight:bold; min-height:100px;'>";

            echo "<td style='color:#0066c0; padding: 5px 0px; font-size:1.3em; width:100px;'>";
            echo "<div style='float:left;'>";

            if ($itemImageURL != "") {
                echo "<img style='width: 50px;' src='" . PREVIEW_IMAGES_NORMAL . $itemImageURL . "' />";
            } else {
                echo "<img style='width: 50px;' src='" . IMAGES_LINK . "no_image.png' />";
            }
            echo "</div>";
            echo "<div style='float:left; margin-left:25px;'>";
            echo $itemName;
            echo "</div>";
            echo "</td>";

            echo "<td style='color:#31961f;'>" . $costDisplay . "</td>";

            $quantityColor = $itemQuantity == 1 ? "#0066c0" : "#b50505";
            echo "<td style='color:$quantityColor; margin: 5px 10px; text-align:center;'>";
            echo $itemQuantity;
            echo "</td>";

            echo "</tr>";
        }
        echo "</table>";


        echo "<div style='color:#000000; font-weight:bold; font-size:1.5em; text-align:right; padding:25px 20px;'>";
        echo "<span>&bigstar; Savings: </span>";
        echo "<span style='color:#1aa7d4;'>" . getPriceDisplayWithDollars($totalSavings) . "</span>";
        echo "</div>";


        $totalStrikeout = "";

        if ($totalPrice == 0) {
            $totalStrikeout = "text-decoration: line-through; opacity: 0.40;";
        }

        echo "<div class='hide_with_cash_only' style='color:#000000; font-weight:bold; font-size:1.5em; text-align:right; padding:0px 20px; $totalStrikeout'>";
        echo "<span>Total ($totalQuantity items): </span>";
        echo "<span style='color:#31961f;'>" . getPriceDisplayWithDollars($totalPrice) . "</span>";
        echo "</div>";

        if ($creditsUsed > 0) {
            echo "<div class='hide_with_cash_only' style='color:#000000; font-weight:bold; font-size:1.5em; text-align:right; padding:0px 20px;'>";
            echo "<span>Credits Used: </span>";
            echo "<span style='color:#ceaf0d;'>" . getPriceDisplayWithDollars($creditsUsed) . "</span>";
            echo "</div>";
        }



        echo "<form style='margin-top:20px;' id='add_item_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        echo "<input type='hidden' name='items' value='" . json_encode($itemsInCart) . "'/>";
        echo "<input type='hidden' name='Purchase' value='Purchase'/>";
        echo "<input type='hidden' name='redirectURL' value='$url'/>";
        echo "<div>";

        echo "<div>";

        echo "<div style='text-align:right;'>";
        echo "<button class='quantity_button quantity_button_purchase' title='Purchase'>PURCHASE</button>";
        echo "</div>";

        if( $_SESSION['ShowCashOnly'] ==  1 ) {
            echo "<div style='margin-top:15px;'>";
            echo "<input onclick='checkCashOnly()' type='checkbox' id='CashOnly' name='CashOnly' value='CashOnly'/><label style='padding:5px 0px; font-weight:bold;' for='CashOnly'>I have already placed cash in the mug instead</label>";
            echo "<div style='font-size:0.8em; font-style:italic; color:#6765c7; margin-top:5px;'>";
            echo "This means I want to use the site to keep track of what I bought but I do not want this added to my balance. You can use the <span style='color:#b50505;'>discount prices</span> if you keep track through the site.</div>";
            echo "</div>";
            echo "</div>";
        }

        echo "</div>";
        echo "</form>";
    } else if($type == "UpdateChecklist" ) {
        $itemID = $_POST['id'];
        $checklistType = $_POST['checklistType'];
        $isBought = 0;

        $statement = $db->prepare("SELECT IsBought, Name, Type," . getQuantityQuery() . ",Retired FROM Item i WHERE ID = :itemID"  );
        $statement->bindValue( ":itemID", $itemID );
        $results = $statement->execute();

        $row = $results->fetchArray();

        if( $row['IsBought'] == 1 ) {
            $statement = $db->prepare( "UPDATE Item set IsBought = 0 WHERE ID = :itemID" );
            $statement->bindValue( ":itemID", $itemID );
            $statement->execute();
            $isBought = 0;
        } else {
            $statement = $db->prepare( "UPDATE Item set IsBought = 1 WHERE ID = :itemID" );
            $statement->bindValue( ":itemID", $itemID );
            $statement->execute();
            $isBought = 1;
        }

        drawCheckListRow( $isBought, $itemID, $row['Name'], $row['Type'], $row['ShelfAmount'], $row['BackstockAmount'], $row['Retired'], $checklistType, "" );
        die();
    }
?>

<script>
    function checkCashOnly() {
        if( $('#CashOnly').prop('checked') ) {
            $('.hide_with_cash_only').hide();
        } else {
            $('.hide_with_cash_only').show();
        }
    }
</script>
