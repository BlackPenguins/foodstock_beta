<?php
    include(__DIR__ . "/../appendix.php" );
    include( SESSION_FUNCTIONS_PATH );
    include(UI_FUNCTIONS_PATH);
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
        $isMobile = $_POST['isMobile'];
        $itemSearch = $_POST['itemSearch'];
        
        $nameQuery = "";

        if( $itemSearch != "" ) {
            $nameQuery = " AND ( Name Like '%" . $itemSearch . "%' OR Alias Like '%" . $itemSearch . "%')";
        }
        
        $cardQuery = "SELECT ID, Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses," .
        "DateModified, ModifyType, Retired, ImageURL, ThumbURL, UnitName, UnitNamePlural, DiscountPrice, CurrentFlavor, RefillTrigger, OutOfStockReporter, OutOfStockDate " .
        "FROM Item WHERE Type ='" . $itemType . "' " .$nameQuery . " AND Hidden != 1 ORDER BY Retired, BackstockQuantity DESC, ShelfQuantity DESC";
        
        if( IsLoggedIn() ) {
            // Sort by user preference
            // This sort pretty much breaks them into 3 groups (bought ones at #1, discontinued at #3, the rest at #2) and sorts those 3,
            // then inside those groups it sorts by frequency, then shelf, then backstock
            $cardQuery = "SELECT ID, Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, DateModified, " .
            "ModifyType, Retired, ImageURL, ThumbURL, UnitName, UnitNamePlural, (SELECT count(*) FROM Purchase_History p WHERE p.UserID = " . $_SESSION["UserID"] .
            " AND p.ItemID = i.ID AND p.Cancelled IS NULL) as Frequency, DiscountPrice, CurrentFlavor, RefillTrigger, OutOfStockReporter, OutOfStockDate FROM Item i " .
            "WHERE Type ='" . $itemType . "' " .$nameQuery . " AND Hidden != 1 " . 
            "ORDER BY CASE WHEN Retired = 1 AND ShelfQuantity = 0 THEN '3' WHEN Frequency > 0 AND Retired = 0 THEN '1'  ELSE '2' END ASC, Frequency DESC, ShelfQuantity DESC, BackstockQuantity DESC"; 
        }
        
        $results = $db->query($cardQuery);
        
        //---------------------------------------
        // BUILD ITEM CARDS
        //---------------------------------------
        $columnNumber = 1;
        while ($row = $results->fetchArray()) {
            
            $isLoggedIn = IsLoggedIn();
            
            $outOfStock = $row['RefillTrigger'];
            $outOfStockReporter = $row['OutOfStockReporter'];
            $item_id = $row['ID'];
            $item_name = $row['Name'];
            $price = $row['Price'];
            $originalPrice = $price;
            $discountPrice = $row['DiscountPrice'];
            $imageURL = $row['ImageURL'];
            $hasDiscount = false;
            
            if( $isLoggedIn && $discountPrice != "" ) {
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
            
            $retired_item = $row['Retired'];
            $cold_item = $row['ShelfQuantity'];
            $warm_item = $row['BackstockQuantity'];
            
            $priceDisplay = "";
            
            if( $isLoggedIn && $hasDiscount == true ) {
                $priceDisplay = getPriceDisplayWithSymbol ( $discountPrice );
            } else {
                $priceDisplay = getPriceDisplayWithSymbol( $price );
            }
            
            $unitName = "[UNKNOWN]";
            $unitNamePlural = "[UNKNOWN]";
            
            if( $row['UnitName'] != "" ) {
                $unitName = $row['UnitName'];
            }
            
            if( $row['UnitNamePlural'] != "" ) {
                $unitNamePlural = $row['UnitNamePlural'];
            }
            
            $unitNameFinal = $cold_item > 1 ? $unitNamePlural : $unitName;
            
            $amountLeft = "N/A";
            $amountClass = "";
            $statusClass = "";
            $thumbnailClass = $cardClass;
            $buttonClass = $cardClass;
            $lightRopeClass = "";
            
            if( $retired_item == 1) {
                if($cold_item == 0) {
                    $amountLeft = "Discontinued";
                    $amountClass = "discontinued";
                    $statusClass = "post-module-discontinued";
                    $buttonClass = "disabled";
                    $lightRopeClass = "class='dead'";
                } else {
                    $amountClass = "discontinued-soon";
                    $amountLeft = "<div><span>$cold_item</span> $unitNameFinal Left</div>" . 
                    "<div style='font-size: 0.8em; font-weight:bold; margin-top:5px; color:#ffe000'>(discontinued soon)</div>";
                }
            } else {
                if($cold_item == 0) {
                    $amountLeft = "SOLD OUT";
                    $amountClass = "sold-out";
                    $thumbnailClass = "sold-out";
                    $buttonClass = "disabled";
                } else {
                    $amountLeft = "<span>$cold_item</span> $unitNameFinal Left";
                }
            }
            
            echo "<input id='shelf_quantity_" . $item_id . "' type='hidden' value='" . $cold_item . "'/>";
            
            $resultsPopularity = $db->query('SELECT ItemID, Date FROM Restock where ItemID = ' . $row['ID'] . ' ORDER BY Date DESC');
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

            $total_can_sold = $row['TotalCans'] - ( $row['BackstockQuantity'] + $row['ShelfQuantity'] );
            
            $resultsDefect = $db->query("SELECT Sum(Amount) as 'TotalDefect' From Defectives where ItemID = ". $row['ID']);
            $rowDefect = $resultsDefect->fetchArray();
            $totalDefects = $rowDefect['TotalDefect'];
            
            $total_can_sold = $total_can_sold - $totalDefects;
            
            
            $reportButton = "";
            if( $isLoggedIn && $outOfStock != "1" ) {
                $userName = $_SESSION['FirstName'] . " " . $_SESSION['LastName'];
                $reportButton = "<div style='position: absolute; right: 10px; top:-42px; cursor:pointer;' onclick='reportItemOutOfStock(\"$userName\"," . $row['ID'] . ",\"" . $row['Name'] . "\")'><img src='" . IMAGES_LINK . "flag.png' title='Report Item Out of Stock'/></div>";
            }
            
            $outOfStockLabel = "";
            if( $outOfStock == "1" ) {
                $reportType = "out of stock";
                $reportClass = "out_of_stock";
                if( $outOfStockReporter == "SodaBot" ) {
                    if( $cold_item > 0 ) {
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
                    echo "<div class='price'>";
                    
                        echo $priceDisplay;
                    echo "</div>";
                    echo $previewImage;
                echo "</div>";
                echo "</div>";
                echo "<div class='post-content'>";
                    echo $reportButton;
                    echo "$outOfStockLabel";
                    echo "<div class='category category-$cardClass $amountClass'>$amountLeft</div>";

                    if( $christianInTheHouse ) {
                        echo "<h1 class='title'>Fun-Size Babe Ruths</h1>";
                    } else {
                        echo "<h1 class='title'>" . $row['Name'] . "</h1>";
                    }
                    
                    $currentFlavor = $row['CurrentFlavor'];
                    if( $currentFlavor != "" ) {
                        echo "<h1 class='sub_title'><u>Current Flavor:</u> <i>$currentFlavor</i></h1>";
                    }

                    $income = $row['TotalIncome'];
                    $expense = $row['TotalExpenses'];
                    
                    $profit = $income - $expense;
                    $profitClass = $profit > 0 ? "income" : "expenses";
                    
                    if( IsAdminLoggedIn() ) {

                        echo "<div class='stats'>";
                            echo "<span class='box box-expenses' title='Total Expenses'>";
                                echo "<span class='value'>" . getPriceDisplayWithDollars( $expense ) . "</span>";
                                echo "<span class='parameter'>Expenses</span>";
                            echo "</span>";
                            
                            echo "<span style='border: 2px solid #000;' class='box box-$profitClass' title='Total Profit'>";
                                echo "<span class='value'>" . getPriceDisplayWithDollars( $profit ) . "</span>";
                                echo "<span class='parameter'>Profit</span>";
                            echo "</span>";
                            
                            echo "<span class='box box-income' title='Total Income'>";
                                echo "<span class='value'>" . getPriceDisplayWithDollars ($income ) . "</span>";
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

                    if( IsLoggedIn() ) {
                        echo "<div class='actions'>";
                            echo "<button id='add_button_" .  $row['ID'] . "' onclick='addItemToCart(" . $row['ID'] . ", \"$isMobile\")' style='float:right;' class='btn btn-$buttonClass' title='Add item(s)'>Add</button>";
                            echo "<span style='float:right;' class='quantity' id='quantity_holder_" . $row['ID'] . "'>0</span>";
                            echo "<button id='remove_button_" .  $row['ID'] . "' onclick='removeItemFromCart(" . $row['ID'] . ", \"$isMobile\")' style='float:left;' class='btn btn-$buttonClass' title='Remove item(s)'>Remove</button>";
                        echo "</div>"; //actions
                    }
                echo "</div>"; //post-content
                
//                 echo "<ul style='top: 2px;' class='lightrope'>" .
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
        $results = $db->query("SELECT Completed FROM Requests WHERE ID =" . $requestID );
        $row = $results->fetchArray();
        
        if( $row['Completed'] == 1 ) {
            $db->exec( "UPDATE Requests set Completed = 0 WHERE ID = " . $requestID );
        } else {
            $date = date('Y-m-d H:i:s');
            $db->exec( "UPDATE Requests set Completed = 1, DateCompleted = '$date' WHERE ID = " . $requestID );
        }
    }
    else if( $type == "ToggleRequestPriority" ) {
        $requestID = $_POST['id'];
        $priority = $_POST['priority'];
        $db->exec( "UPDATE Requests set Priority = '$priority' WHERE ID = " . $requestID );
    }
    else if( $type == "CancelPurchase" ) {
        $dailyAmountID = trim($_POST["DailyAmountID"]);
        
        $results = $db->query("SELECT u.UserName, u.SlackID, p.Cost, p.DiscountCost, p.UserID, i.Type, i.Name, d.ItemID, d.BackstockQuantityBefore, d.BackstockQuantity, d.ShelfQuantityBefore, d.ShelfQuantity, d.Price from Daily_Amount d JOIN Item i on d.ItemID =  i.ID JOIN Purchase_History p on d.ID = p.DailyAmountID JOIN User u on p.UserID = u.UserID WHERE d.ID = $dailyAmountID");
        $row = $results->fetchArray();
        
        $date = date('Y-m-d H:i:s');
        $itemID = $row['ItemID'];
        $itemName = $row['Name'];
        $userID = $row['UserID'];
        $username = $row['UserName'];
        $slackID = $row['SlackID'];
        $itemType = $row['Type'];
        $backstockBefore = $row['BackstockQuantityBefore'];
        $backstockAfter= $row['BackstockQuantity'];
        $shelfBefore = $row['ShelfQuantityBefore'];
        $shelfAfter= $row['ShelfQuantity'];
        $price= $row['Cost'];
        $discountPrice= $row['DiscountCost'];
        
        $actualPrice = $discountPrice == 0.0 ? $price : $discountPrice;
        
        $savings = $price - $discountPrice;
        
        $cancelDailySQL = "UPDATE Daily_Amount SET Cancelled = 1 WHERE ID = $dailyAmountID";
        log_sql("Cancel Daily Amount SQL: [" . $cancelDailySQL . "]" );
        $db->exec( $cancelDailySQL );
        
        $cancelPurchaseSQL = "UPDATE Purchase_History SET Cancelled = 1 WHERE DailyAmountID = $dailyAmountID";
        log_sql("Cancel SQL: [" . $cancelPurchaseSQL . "]" );
        $db->exec( $cancelPurchaseSQL );
        
        $newIncome = addToValue( $db, "Item", "TotalIncome", $actualPrice, "where ID = $itemID", false );
        $itemQuery = "UPDATE Item SET ShelfQuantity = ShelfQuantity + 1, TotalIncome = $newIncome, DateModified = '$date', ModifyType = 'Cancelled' where ID = $itemID";
        log_sql("Item SQL: [" . $itemQuery . "]" );
        $db->exec( $itemQuery );
        
        $newIncome = addToValue( $db, "Information", "Income", $actualPrice, "where ItemType = '$itemType'", false );
        $infoQuery = "UPDATE Information SET Income = $newIncome where ItemType = '$itemType'";
        log_sql("Info SQL: [" . $infoQuery . "]" );
        $db->exec( $infoQuery );
        
        $typeOfBalance = $itemType . "Balance";
        $typeOfSavings = $itemType . "Savings";
        
        $newBalance = addToValue( $db, "User", $typeOfBalance, $actualPrice, "where UserID = $userID", false );
        $newSavings = addToValue( $db, "User", $typeOfSavings, $savings, "where UserID = $userID", false );
        $balanceUpdateQuery = "UPDATE User SET $typeOfBalance = $newBalance, $typeOfSavings = $newSavings where UserID = $userID";

        log_sql("Balance Update SQL [" . $balanceUpdateQuery . "]" );
        $db->exec( $balanceUpdateQuery );
        
        $purchaseMessage = $itemName . " (+ " . getPriceDisplayWithDollars( $actualPrice ) . ")";
        
        if( $slackID == "" ) {
            sendSlackMessageToMatt( "Failed to send notification for " . $username . ". Create a SlackID!", ":no_entry:", $itemType . "Stock - ERROR!!", "#bb3f3f" );
        } else {
            sendSlackMessageToUser($slackID,  $purchaseMessage , ":money_mouth_face:" , $itemType . "Stock - REFUND", "#3f5abb" );
        }
        
        sendSlackMessageToMatt( "*(" . $username . ")*\n" . $purchaseMessage, ":money_mouth_face:", $itemType . "Stock - REFUND", "#3f5abb" );
    }
    else if( $type == "CancelInventory" ) {
        $dailyAmountID = trim($_POST["DailyAmountID"]);
        
        $results = $db->query("SELECT i.Type, d.ItemID, d.BackstockQuantityBefore, d.BackstockQuantity, d.ShelfQuantityBefore, d.ShelfQuantity, d.Price from Daily_Amount d JOIN Item i on d.ItemID =  i.ID WHERE d.ID = $dailyAmountID");
        $row = $results->fetchArray();
        
        $date = date('Y-m-d H:i:s');
        $itemID = $row['ItemID'];
        $itemType = $row['Type'];
        $backstockBefore = $row['BackstockQuantityBefore'];
        $backstockAfter= $row['BackstockQuantity'];
        $shelfBefore = $row['ShelfQuantityBefore'];
        $shelfAfter= $row['ShelfQuantity'];
        $price= $row['Price'];
        
        $quantityBefore = $backstockBefore + $shelfBefore;
        $quantityAfter = $backstockAfter + $shelfAfter;
        
        $backstockDelta = $backstockBefore - $backstockAfter;
        $shelfDelta = $shelfBefore - $shelfAfter;
        
        $income = ($quantityBefore - $quantityAfter) * $price;
        
        $cancelSQL = "UPDATE Daily_Amount SET Cancelled = 1 WHERE ID = $dailyAmountID";
        log_sql("Cancel SQL: [" . $cancelSQL . "]" );
        $db->exec( $cancelSQL );
        
        $newIncome = addToValue( $db, "Item", "TotalIncome", $income, "where ID = $itemID", false );
        $itemSQL = "UPDATE Item SET TotalIncome = $newIncome, BackstockQuantity = BackstockQuantity + $backstockDelta, ShelfQuantity = ShelfQuantity + $shelfDelta, ModifyType = 'Cancelled', DateModified = '$date' where ID = $itemID";
        log_sql("Item SQL: [" . $itemSQL . "]" );
        $db->exec( $itemSQL );
        
        $newIncome = addToValue( $db, "Information", "Income", $income, "where ItemType = '$itemType'", false );
        $infoSQL = "UPDATE Information SET Income = $newIncome where ItemType = '$itemType'";

        log_sql("Info SQL: [" . $infoSQL . "]" );
        $db->exec( $infoSQL );
    }
    else if( $type == "CancelRestock" ) {
        $restockID = trim($_POST["RestockID"]);
        
        $results = $db->query("SELECT r.Cost, r.ItemID, r.NumberOfCans, i.Type From Restock r JOIN Item i on r.ItemID = i.ID where RestockID = $restockID");
        $row = $results->fetchArray();
        
        $cost = $row['Cost'];
        $numberOfCans = $row['NumberOfCans'];
        $itemType = $row['Type'];
        $itemID = $row['ItemID'];

        $cancelSQL = "UPDATE Restock SET Cancelled = 1 where RestockID = $restockID";
        log_sql( "Cancel SQL [$cancelSQL]" );
        $db->exec( $cancelSQL );
        
        $newTotalExpenses = addToValue( $db, "Item", "TotalExpenses", $cost, "where ID = $itemID", false );
        $itemSQL = "UPDATE Item SET TotalExpenses = $newTotalExpenses, BackstockQuantity = BackstockQuantity - $numberOfCans, TotalCans = TotalCans - $numberOfCans where ID = $itemID";
        log_sql( "Item SQL [$itemSQL]" );
        $db->exec( $itemSQL );
        
        $newExpenses = addToValue( $db, "Information", "Expenses", $cost, "where ItemType = '$itemType'", false );
        $infoSQL = "UPDATE Information SET Expenses = $newExpenses where ItemType = '$itemType'";
        log_sql( "Info SQL [$infoSQL]" );
        $db->exec( $infoSQL );
    }
    else if( $type == "CancelPayment" ) {
        
        $paymentID = trim($_POST["PaymentID"]);
        
        $results = $db->query("SELECT UserID, Amount, ItemType From Payments where PaymentID = $paymentID");
        $row = $results->fetchArray();
        
        $userID = $row['UserID'];
        $amount = $row['Amount'];
        $itemType = $row['ItemType'];
        
        $isUserPayment = $userID > 0;
        
        $cancelSQL = "Update Payments SET Cancelled = 1 WHERE PaymentID = $paymentID";
        log_sql("Cancel [$cancelSQL]");
        
        $db->exec( $cancelSQL );
    
        if( $isUserPayment ) {
            $balanceType = $itemType . "Balance";
            
            $newBalance = addToValue( $db, "User", $balanceType, $amount, "where UserID = $userID", true );
            $balanceSQL = "UPDATE User SET $balanceType = " . $newBalance . " where UserID = $userID";
            log_sql("UserBalance [$balanceSQL]");
            $db->exec( $balanceSQL );
        }
    
        $newProfit = addToValue( $db, "Information", "ProfitActual", $amount, "where ItemType = '$itemType'", false );
        $infoSQL = "UPDATE Information SET ProfitActual = $newProfit where ItemType = '$itemType'";

        log_sql("Info [$infoSQL]");
        $db->exec( $infoSQL );
        
        $db->exec();
    }
    else if( $type == "NotifyUserOfPayment" ) {
        $month = trim($_POST["month"]);
        $year = trim($_POST["year"]);
        $displayMonth = trim($_POST["displayMonth"]);
        
        $results = $db->query("SELECT UserID, UserName, SlackID, SodaBalance, SnackBalance, FirstName, LastName FROM User" );
        log_debug( "Notifying Users..." );
        
        while ($row = $results->fetchArray()) {


            $userName = $row['UserName'];
            $slackID = $row['SlackID'];
            $userID = $row['UserID'];
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
            
            log_debug("User[$userName]Month[$month]Year[$year]Soda Total[$sodaTotal]Snack Total[$snackTotal][Soda Paid[$sodaTotalUnpaid]SnackPaid[$snackTotalUnpaid]");
            
            
            $totalBalance = $sodaTotalUnpaid + $snackTotalUnpaid ;
            
            if( $totalBalance > 0 ) {
                $slackMessage = "Good morning, $name! It's a new month. Here is your FoodStock Balance for $displayMonth:\n" .
                "*_Soda Balance:_* " . getPriceDisplayWithDollars( $sodaTotalUnpaid ) . "\n" .
                "*_Snack Balance:_* " . getPriceDisplayWithDollars( $snackTotalUnpaid ) . "\n\n" .
                        "*Total Balance Owed:* " . getPriceDisplayWithDollars( $totalBalance ) . "\n\n" .
                        "You can view more details on the <http://penguinore.net/purchase_history.php|Purchase/Payment History Page>. Have a great day! :grin:";
                
                sendSlackMessageToUser( $slackID, $slackMessage, ":credit:", "FoodStock Collection Agency", "#ff7a7a" );
            }
        }
    }
    else if( $type == "OutOfStockRequest" ) {
        $itemID = $_POST['itemID'];
        $itemName = $_POST['itemName'];
        $reporter = $_POST['reporter'];
        $date = date('Y-m-d H:i:s', time());
        $db->exec( "UPDATE Item set RefillTrigger = 1, OutOfStockDate = '$date', OutOfStockReporter = '$reporter' WHERE ID = $itemID" );
        sendSlackMessageToMatt( "*Item Name:* " . $itemName . "\n*Reporter:* " . $reporter, ":negative_squared_cross_mark:", "OUT OF STOCK REPORT", "#791414" );
    }
    else if($type == "DrawCart" )
    {
        $itemQuantities = array();
        $itemPrices = array();
        $itemDiscountPrices = array();
        $itemNames = array();
        $itemThumbs= array();
        
        $itemsInCart = json_decode($_POST['items']);
        $url = $_POST['url'];
        $isMobile = $_POST['isMobile'];

        foreach( $itemsInCart as $itemID ) {
            if( array_key_exists( $itemID, $itemQuantities ) === false ) {
                $results = $db->query("SELECT * FROM Item WHERE ID =" . $itemID );
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

        foreach( $itemQuantities as $itemID => $itemQuantity ) {
            $itemName = $itemNames[$itemID];
            $itemPrice = $itemPrices[$itemID];
            $itemDiscountPrice = $itemDiscountPrices[$itemID];
            $itemImageURL = $itemThumbs[$itemID];
            $costDisplay = "";
            
            if( $itemDiscountPrice != "" ) {
                $costDisplay = "<span class='red_price'>" . getPriceDisplayWithDollars( $itemPrice ) . "</span> " . getPriceDisplayWithDollars( $itemDiscountPrice );
                $totalPriceForItem = ( $itemDiscountPrice * $itemQuantity);
                $totalSavings += ( $itemPrice - $itemDiscountPrice ) * $itemQuantity;
            } else {
                $costDisplay = getPriceDisplayWithDollars( $itemPrice );
                $totalPriceForItem = ( $itemPrice * $itemQuantity);
            }


            $totalPrice += $totalPriceForItem;
            $totalQuantity += $itemQuantity;

            if( $creditsLeft > 0 ) {
                $creditsLeft = $creditsLeft - $totalPrice;

                if( $creditsLeft < 0 ) {
                    // If the credits are in the negative, that means that they spent them all and the negative amount is now a balance
                    $totalPrice = abs( $creditsLeft );
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
            
            if( $itemImageURL != "" ) {
                echo  "<img style='width: 50px;' src='" . PREVIEW_IMAGES_NORMAL . $itemImageURL . "' />";
            } else {
                echo  "<img style='width: 50px;' src='" . IMAGES_LINK . "no_image.png' />";
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
        
        $firstColSpan = "<td colspan='2'>&nbsp;</td> ";
        $secondColSpan = "";
        
        if( $isMobile ) {
            $firstColSpan = "";
            $secondColSpan = "colspan='3'";
        }
        
        echo "<tr>";
        echo $firstColSpan;
        echo "<td $secondColSpan style='color:#000000; font-weight:bold; font-size:1.5em; text-align:right; padding:25px 20px;'>";
        echo "<span>&bigstar; Savings: </span>";
        echo "<span style='color:#1aa7d4;'>" . getPriceDisplayWithDollars( $totalSavings ) . "</span>";
        echo "</td>";
        echo "</tr>";

        $totalStrikeout = "";

        if( $totalPrice == 0 ) {
            $totalStrikeout = "text-decoration: line-through; opacity: 0.40;";
        }

        echo "<tr>";
        echo $firstColSpan;
        echo "<td $secondColSpan style='color:#000000; font-weight:bold; font-size:1.5em; text-align:right; padding:0px 20px; $totalStrikeout'>";
        echo "<span>Total ($totalQuantity items): </span>";
        echo "<span style='color:#31961f;'>" . getPriceDisplayWithDollars( $totalPrice ) . "</span>";
        echo "</td>";
        echo "</tr>";

        if( $creditsUsed > 0 ) {
            echo "<tr>";
            echo $firstColSpan;
            echo "<td $secondColSpan style='color:#000000; font-weight:bold; font-size:1.5em; text-align:right; padding:0px 20px;'>";
            echo "<span>Credits Used: </span>";
            echo "<span style='color:#ceaf0d;'>" . getPriceDisplayWithDollars($creditsUsed) . "</span>";
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
        
        echo "<form style='margin-top:20px;' id='add_item_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        echo "<input type='hidden' name='items' value='" . json_encode($itemsInCart) . "'/>";
        echo "<input type='hidden' name='Purchase' value='Purchase'/>";
        echo "<input type='hidden' name='redirectURL' value='$url'/>";
        echo "<div>";
        
        $rightSide = $isMobile ? "" : "style='margin-left:75%;'";
        echo "<div $rightSide>";
        
        echo "<div style='text-align:right;'>";
        echo "<button class='quantity_button quantity_button_purchase' title='Purchase'>PURCHASE</button>";
        echo "</div>";
        
        echo "<div style='margin-top:15px;'>";
        echo "<input type='checkbox' name='CashOnly' value='CashOnly'/><label style='padding:5px 0px; font-weight:bold;' for='CashOnly'>I have already placed cash in the mug instead</label>";
        echo "<div style='font-size:0.8em; font-style:italic; color:#6765c7; margin-top:5px;'>";
        echo "This means I want to use the site to keep track of what I bought but I do not want this added to my balance. You can use the <span style='color:#b50505;'>discount prices</span> if you keep track through the site.</div>";
        echo "</div>";
        echo "</div>";
        
        echo "</div>";
        echo "</form>";
    }
?>