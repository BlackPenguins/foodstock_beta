<?php
include(__DIR__ . "/../appendix.php" );

function DisplayAgoTime( $dateBefore, $dateNow ) {
    $date_object = new DateTime();

    if( $dateBefore != "") {
        $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $dateBefore);
    }

    $time_since = $dateNow->diff($date_object);
    
    $days_ago = $time_since->format('%a');
    $hours_ago = $time_since->format('%h');
    $minutes_ago = $time_since->format('%i');
    $seconds_ago = $time_since->format('%s');

    $ago_text = "UNKNOWN";

    if($days_ago >= 1) {
        $ago_text = $days_ago . " day". ( ( $days_ago == 1 )? (""):("s") ). "  ago";
    } else if($hours_ago >= 1) {
        $ago_text = $hours_ago . " hour". ( ( $hours_ago == 1 )? (""):("s") ). " ago";
    } else if($minutes_ago >= 1) {
        $ago_text = $minutes_ago . " minute". ( ( $minutes_ago == 1 )? (""):("s") ). "  ago";
    } else  if($seconds_ago >= 1) {
        $ago_text = $seconds_ago . " second". ( ( $seconds_ago == 1 )? (""):("s") ). "  ago";
    }

    return $ago_text;
}

function getPriceDisplayWithSymbol($priceInWholeCents) {
    return getPriceDisplayWithFormat( $priceInWholeCents, "symbol" );
}

function getPriceDisplayWithEnglish($priceInWholeCents) {
    return getPriceDisplayWithFormat( $priceInWholeCents, "english" );
}

function getPriceDisplayWithDollars($priceInWholeCents) {
    return getPriceDisplayWithFormat( $priceInWholeCents, "dollars" );
}

function getPriceDisplayWithDecimals($priceInWholeCents) {
    return getPriceDisplayWithFormat( $priceInWholeCents, "decimals" );
}

function getPriceDisplayWithFormat($priceInWholeCents, $format ) {

    if( $priceInWholeCents < 1 && $priceInWholeCents > 0 ) {
        // Since we migrated to whole cents that means 1 cent is now '1'
        // Anything less than 1 should not be possible
        // Something could also be 1.10 (which would also be migration fail) but that's harder to test for
        // Basically NOTHING should have decimals anymore
        return "Migration Failed!! - Contact Dev [$format] [$priceInWholeCents]";
    }
    $dollars = 0;
    $negativeSign = $priceInWholeCents < 0 ? "-" : "";

    $priceInWholeCents = abs( $priceInWholeCents );

    if( $priceInWholeCents >= 100 ) {
        $dollars = floor( $priceInWholeCents / 100 );
    }

    $cents = $priceInWholeCents - ( $dollars * 100 );

//    log_debug(" Whole Cents [$priceInWholeCents] Format [$format] Dollars [$dollars] Cents [$cents]");

    $price = "Unknown Price - Contact Dev [$format] [$priceInWholeCents]";

    if( $dollars >= 1 || $format == "dollars" || $format == "decimals" ) {
        $paddedCents = sprintf('%02d', $cents);
        $dollarSign =  $format == "decimals" ? "" : "$";
        $dollarsWithThousandsComma = number_format( $dollars, 0 );
        $price = $dollarSign . $negativeSign . $dollarsWithThousandsComma . "." . $paddedCents;
    } else {
        if( $format == "symbol" ) {
            $price = $negativeSign . $cents . "&cent;";
        } else if( $format == "english" ) {
            $price = $negativeSign . $cents . " cents";
        }
    }
    
    return $price;
}

function convertDecimalToWholeCents( $decimal ) {
    try {
        $valueInDollars = number_format($decimal, 2);
        $valueInDollars = str_replace(",", "", $valueInDollars);

        $isNegative = substr($valueInDollars, 0, 1) === "-";

        $moneyPieces = explode(".", $valueInDollars);
        $dollars = $moneyPieces[0];
        $cents = $moneyPieces[1];

        if ($isNegative) {
            $valueInWholeCents = ($dollars * 100) - $cents;
        } else {
            $valueInWholeCents = ($dollars * 100) + $cents;
        }
//    echo "Converted [$valueInDollars] --> [$valueInWholeCents]<br>";
        return $valueInWholeCents;
    } catch (Exception $e) {
        error_log( "Failed to convert decimal to whole cents. Decimal [$decimal] Message: [" .  $e->getMessage() . "]" );
    }
}

function DisplayPreview($item_name, $soldOut, $imageURL) {
    $opacity = "1.0";

    if( $soldOut == true ) {
        $opacity = "0.4";
    }
    
    if( $imageURL == "" ) {
        echo "<div class='vcenter' style='background-color:#212121; font-weight:bold;  word-spacing:200px; height:200px; color:#FFFFFF'><span>".$item_name."</span></div>";
    } else {
        echo "<img src='" . PREVIEW_IMAGES_NORMAL . $imageURL . "' style = 'height:200px; max-width:100%; opacity:$opacity' />";
    }
}

function DisplayShelfCan($itemID, $item_name, $thumbURL) {
    $clickAddToCart = "";

    if( IsLoggedIn() ) {
        $clickAddToCart = "onclick='addItemToCart(" . $itemID . ", \"\")'";
    }
    if( $thumbURL == "" ) {
        echo "<img title='$item_name' style='padding:5px;' src='" . PREVIEW_IMAGES_THUMBS . "not_found_sm.png' />";
    } else {
        echo "<img $clickAddToCart title='$item_name' style='padding:5px;' src='" . PREVIEW_IMAGES_THUMBS . $thumbURL . "' />";
    }
}

/**
 * @param $db SQLite3
 * @param $userID
 * @param $monthNumber
 * @param $year
 * @param $monthLabel
 * @return array
 */
function getTotalsForUser( $db, $userID, $monthNumber, $year, $monthLabel ) {
    $startDate = $year . "-" . $monthNumber . "-01";
    
    if( $monthNumber == 12) {
        $monthNumber = 1;
        $year++;
    } else {
        $monthNumber++;
    }
    
    if( $monthNumber < 10 ) { $monthNumber = "0" . $monthNumber; }
    
    $endDate = $year . "-" . $monthNumber . "-01";
    
    $currentMonthSodaTotal = 0.0;
    $currentMonthSnackTotal = 0.0;

    $currentMonthSodaCreditTotal = 0.0;
    $currentMonthSnackCreditTotal = 0.0;

    $query = "SELECT i.Name, i.Type, p.Cost as LegacyPrice, p.CashOnly, p.UseCredits, p.DiscountCost as LegacyDiscountPrice, p.Date, p.UserID, p.ItemDetailsID, d.Price, d.DiscountPrice " .
        "FROM Purchase_History p " .
        "JOIN Item i on p.itemID = i.ID " .
        "LEFT JOIN Item_Details d on p.ItemDetailsID = d.ItemDetailsID " .
        "WHERE p.UserID = :userID AND p.Date >= :startDate AND p.Date < :endDate AND p.Cancelled IS NULL " .
        "ORDER BY p.Date DESC";

    log_sql( "Payment Month: [$query]" );
    $statement = $db->prepare( $query );
    $statement->bindValue( ":userID", $userID );
    $statement->bindValue( ":startDate", $startDate );
    $statement->bindValue( ":endDate", $endDate );
    $results = $statement->execute();

    while ($row = $results->fetchArray()) {

        $itemDetailsID =  $row['ItemDetailsID'];
        $itemType =  $row['Type'];

        $discountCost = $row['LegacyDiscountPrice'];
        $fullCost = $row['LegacyPrice'];

        if( $itemDetailsID != null ) {
            $discountCost = $row['DiscountPrice'];
            $fullCost = $row['Price'];
        }

        if( $discountCost != "" && $discountCost != 0 ) {
            $cost = $discountCost;
        } else {
            $cost = $fullCost;
        }
        
        // Only purchases that WERE NOT cash-only go towards the total - because they already paid in cash
        if( $row['CashOnly'] != 1 ) {

            $creditsUsed = $row["UseCredits"];
            $finalCostNotIncludingCredits = $cost;

            // Purchases that used credits might affect what was actually for balance
            if( $creditsUsed > 0 ) {
                $finalCostNotIncludingCredits -= $creditsUsed;

                if( $itemType == "Snack" ) {
                    $currentMonthSnackCreditTotal += $creditsUsed;
                } else if( $itemType == "Soda" ) {
                    $currentMonthSodaCreditTotal += $creditsUsed;
                }
            }

            if( $itemType == "Snack" ) {
                $currentMonthSnackTotal += $finalCostNotIncludingCredits;
            } else if($itemType == "Soda" ) {
                $currentMonthSodaTotal += $finalCostNotIncludingCredits;
            }
        }
    }
    
    $sodaQuery = "SELECT Sum(Amount) as 'TotalAmount' FROM Payments WHERE UserID = :userID AND MonthForPayment = :monthLabel AND ItemType= :itemType AND Cancelled IS NULL AND VendorID = 0";
    $sodaStatement = $db->prepare( $sodaQuery );
    $sodaStatement->bindValue( ":userID", $userID );
    $sodaStatement->bindValue( ":monthLabel", $monthLabel );
    $sodaStatement->bindValue( ":itemType", "Soda" );
    $sodaResults = $sodaStatement->execute();

    $sodaTotalPaid = $sodaResults->fetchArray()['TotalAmount'];
    
    $snackQuery = "SELECT Sum(Amount) as 'TotalAmount' FROM Payments WHERE UserID = :userID AND MonthForPayment = :monthLabel AND ItemType= :itemType AND Cancelled IS NULL AND VendorID = 0";
    $snackStatement = $db->prepare( $snackQuery );
    $snackStatement->bindValue( ":userID", $userID );
    $snackStatement->bindValue( ":monthLabel", $monthLabel );
    $snackStatement->bindValue( ":itemType", "Snack" );
    $snackResults = $snackStatement->execute();

    $snackTotalPaid = $snackResults->fetchArray()['TotalAmount'];
    
    $returnArray = array();
    $returnArray['SodaTotal'] = $currentMonthSodaTotal;
    $returnArray['SnackTotal'] = $currentMonthSnackTotal;
    $returnArray['SodaCreditTotal'] = $currentMonthSodaCreditTotal;
    $returnArray['SnackCreditTotal'] = $currentMonthSnackCreditTotal;
    $returnArray['SodaPaid'] = $sodaTotalPaid;
    $returnArray['SnackPaid'] = $snackTotalPaid;

    log_debug( "Month [$monthLabel] User [$userID] Soda Total [$currentMonthSodaTotal] Snack Total [$currentMonthSnackTotal] Soda Paid: [$sodaTotalPaid] Snack Paid[$snackTotalPaid]" );
    return $returnArray;
}

/**
 * @param $db SQLite3
 * @param $checklistType
 * @param $selectType
 * @return mixed
 */
function getChecklistResults( $db, $checklistType, $selectType ) {
    $specialWhere = "";
    $specialSelect = "ID, Type, Name, RefillTrigger, RestockTrigger," . getQuantityQuery() .
        ",Price, DiscountPrice, Retired, Hidden, IsBought";

    if( $selectType == "COUNT" ) {
        $specialSelect = "COUNT(*) as Count," . getQuantityQuery();
    }

    if( $checklistType == "RefillTrigger" ) {
        // We don't care about refilling items that we dont have at the desk
        $specialWhere = " AND BackstockAmount > 0";
    } else if( $checklistType == "RestockTrigger" ) {
        // We don't care about discontinued items for store restock
        $specialWhere = " AND Retired != 1 ";
    }

    if( $selectType == "COUNT" ) {
        $specialWhere .= " AND IsBought = 0 ";
    }

    $statement = $db->prepare( "SELECT $specialSelect FROM Item i WHERE Hidden != 1 AND $checklistType = 1 $specialWhere ORDER BY Type DESC, Retired, ShelfAmount DESC" );
    return $statement->execute();
}

function getRefillCount($db) {
    $row = getChecklistResults($db,  "RefillTrigger", "COUNT"  )->fetchArray();
    return $row["Count"];
}

function getRestockCount($db) {
    $row = getChecklistResults($db,  "RestockTrigger", "COUNT"  )->fetchArray();
    return $row["Count"];
}


function drawCheckListRow( $isBought, $itemID, $itemName, $itemType, $shelfQuantity, $backstockQuantity, $isDiscontinued, $checklistType, $extraInfo) {
    $completedMark = "&#9746;";
    $completedMarkColor = "#6b1010";

    error_log("Entering with [$isBought, $itemID, $itemName, $itemType, $shelfQuantity, $backstockQuantity, $isDiscontinued]" );
    if( $isBought == 1 ) {
        $completedMark = "&#9745;";
        $completedClass = "completed";
        $completedMarkColor = "#0b562d";
    }

    $typeColor = "#403ecc";

    if( $itemType == "Snack" ) {
        $typeColor = "#cc3e3e";
    }

    $onClick = " onclick='toggleCompleted( $itemID, \"$checklistType\" );'";

    echo "<td style='padding-left: 0px; font-size:1.6em; cursor:pointer; text-align:center; font-weight:bold; color: $completedMarkColor;'> <span$onClick>$completedMark </span></td>";
    echo "<td style='color:$typeColor'>";

    echo "$itemName";

    if( $extraInfo != "" ) {
        echo "<br>$extraInfo";
    }

    echo "</td>";
    echo "<td style='color:$typeColor'>$itemType</td>";
    echo "<td style='color:$typeColor'>$shelfQuantity</td>";
    echo "<td style='color:$typeColor'>$backstockQuantity</td>";

}

/**
 * @param $db SQLite3
 * @param $itemType
 * @param $itemSearch
 * @throws Exception
 */
function buildCardArea( $db, $itemType, $itemSearch ) {
//    error_log("Entering with [$itemType][$itemSearch]" );
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

        if (isset($_SESSION['ShowDiscontinued']) && $_SESSION['ShowDiscontinued'] != 0) {
            $hideDiscontinued = false;
        }

        if ($retired_item == 1 && $hideDiscontinued && $shelfAmount == 0) {
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

        if (IsLoggedIn() && $discountPrice != "") {
            $price = $discountPrice;
            $hasDiscount = true;
        }

        $price_color = "#FFFFFF";
        $price_background_color = "#025F00";

        // On sale - YELLOW
        if ($price < 50) {
            $price_color = "#000000";
            $price_background_color = "#FFD500";
            // Expensive - RED
        } else if ($price > 100) {
            $price_color = "#FFFFFF";
            $price_background_color = "#5f0000";
        }

        $priceDisplay = "";

        if (IsLoggedIn() && $hasDiscount == true) {
            $priceDisplay = getPriceDisplayWithSymbol($discountPrice);
        } else {
            $priceDisplay = getPriceDisplayWithSymbol($price);
        }

        $unitName = "item";
        $unitNamePlural = "items";

        if ($row['UnitName'] != "") {
            $unitName = $row['UnitName'];
        }

        if ($row['UnitNamePlural'] != "") {
            $unitNamePlural = $row['UnitNamePlural'];
        }

        $unitNameFinal = $shelfAmount > 1 ? $unitNamePlural : $unitName;

        $amountLeft = "N/A";
        $quantityBoxClass = "";
        $statusClass = "";
        $thumbnailSoldOutClass = "";
        $buttonClass = "";
        $refilledClass = "";

        $justRefilled = false;

        if ($row['DateModified'] != null) {
            $lastRefilled = DateTime::createFromFormat('Y-m-d H:i:s', $row['DateModified']);
            $now = new DateTime();

            $timeSinceLastRefill = $now->diff($lastRefilled);

            $minutesSinceLastRefill = ($timeSinceLastRefill->d * 24 * 60) + ($timeSinceLastRefill->h * 60) + $timeSinceLastRefill->i;

            $justRefilled = $minutesSinceLastRefill <= 120 && $itemType == "Soda";
        }

        if ($justRefilled) {
            $refilledClass = "refilled";
        }

        if ($retired_item == 1) {
            if ($shelfAmount == 0) {
                $amountLeft = "Discontinued";
                $quantityBoxClass = "quantity_box_discontinued";
                $statusClass = "card_block_discontinued";
                $buttonClass = "quantity_button_disabled";
            } else {
                $quantityBoxClass = "quantity_box_discontinued_soon";
                $amountLeft = "<div><span>$shelfAmount</span> $unitNameFinal Left</div>" .
                    "<div style='font-size: 0.8em; font-weight:bold; margin-top:5px; color:#ffe000'>(discontinued soon)</div>";
            }
        } else {
            if ($shelfAmount == 0) {
                $amountLeft = "SOLD OUT";
                $quantityBoxClass = "quantity_box_sold_out";
                $thumbnailSoldOutClass = "thumbnail_sold_out";
                $buttonClass = "quantity_button_disabled";
            } else {
                $amountLeft = "<span>$shelfAmount</span> $unitNameFinal Left";
            }
        }

        echo "<input id='shelf_quantity_" . $item_id . "' type='hidden' value='" . $shelfAmount . "'/>";

        $statementPopularity = $db->prepare('SELECT ItemID, Date FROM Restock where ItemID = :itemID ORDER BY Date DESC');
        $statementPopularity->bindValue(":itemID", $row['ID']);
        $resultsPopularity = $statementPopularity->execute();

        $firstDate = "";
        $lastDate = "";
        $totalPurchases = 0;
        while ($rowPopularity = $resultsPopularity->fetchArray()) {
            if ($firstDate == "") {
                $firstDate = $rowPopularity[1];
            }
            $lastDate = $rowPopularity[1];
            $totalPurchases++;
        }

        $date_before = DateTime::createFromFormat('Y-m-d H:i:s', $firstDate);
        $date_after = DateTime::createFromFormat('Y-m-d H:i:s', $lastDate);

        $days_ago = 0;

        if ($firstDate != "" && $lastDate != "") {
            if ($firstDate == $lastDate) {
                $date_after = new DateTime();
            }

            $time_since = $date_before->diff($date_after);
            $days_ago = $time_since->format('%a');
        }

        $frequencyBought = "0";
        $purchaseDayInterval = "N/A";

        if (isset($row['Frequency'])) {
            $frequencyBought = $row['Frequency'];
        }

        if ($totalPurchases > 0) {
            $purchaseDayInterval = round($days_ago / $totalPurchases);
        }

        $previewImage = "";


        if ($imageURL != "") {
            $previewImage = "<img class='preview_zoom' src='" . PREVIEW_IMAGES_NORMAL . $imageURL . "' />";
        } else {
            $previewImage = "<img class='preview_zoom' style='width: 100px; height: 100px; padding-top:70px;' src='" . IMAGES_LINK . "no_image.png' />";
        }

        $total_can_sold = $row['TotalCans'] - ($backstockAmount + $shelfAmount);

        $statementDefect = $db->prepare("SELECT Sum(Amount) as 'TotalDefect' From Defectives where ItemID = :itemID");
        $statementDefect->bindValue(":itemID", $row['ID']);
        $resultsDefect = $statementDefect->execute();

        $rowDefect = $resultsDefect->fetchArray();
        $totalDefects = $rowDefect['TotalDefect'];

        $total_can_sold = $total_can_sold - $totalDefects;


        $reportButton = "";
        if (IsLoggedIn() && !IsInactive() && $outOfStock != "1") {
            $userName = $_SESSION['FirstName'] . " " . $_SESSION['LastName'];
            $reportButton = "<div style='position: absolute; right: 10px; top:-42px; cursor:pointer;' onclick='reportItemOutOfStock(\"$userName\"," . $row['ID'] . ",\"" . $row['Name'] . "\")'><img src='" . IMAGES_LINK . "low.png' title='Report Item Out of Stock'/></div>";
        }

        $outOfStockLabel = "";
        if ($outOfStock == "1") {
            $reportType = "out of stock";
            $reportClass = "out_of_stock";
            if ($outOfStockReporter == "StockBot") {
                if ($shelfAmount > 0) {
                    $reportType = "running low";
                    $reportClass = "running_low";
                }
            }

            $outOfStockLabel = "<div class='report-label $reportClass'>Reported as $reportType by " . $outOfStockReporter . "!</div>";
        }

        // ------------------
        // BUILD THE CARD
        // ------------------
        echo "<span class='card_block $statusClass $refilledClass'>";
//                 echo "<div class='snow'>";
        echo "<div class='thumbnail $thumbnailSoldOutClass'>";
        printHolidayPriceIcon($priceDisplay);
        echo $previewImage;
        echo "</div>";
        echo "<div class='post-content'>";
        echo $reportButton;

        if ($justRefilled) {
            echo "<div style='position: absolute; right: 10px; top:-80px;'><img src='" . IMAGES_LINK . "thermometer.png' title='This item was added to the fridge $minutesSinceLastRefill minutes ago and might not be cold yet.'/></div>";
        }

        echo "$outOfStockLabel";
        echo "<div class='quantity_box $quantityBoxClass'><span class='quantity_text'>$amountLeft</span></div>";

        if ($supplierName != "") {
            echo "<div title='This item is being sold by $supplierName through FoodStock' class='supplier'>Sold by $supplierName</div>";
        }

        echo "<h1 class='title'>" . getHolidayItemName($row['Name']) . "</h1>";

        $currentFlavor = $row['CurrentFlavor'];
        if ($currentFlavor != "") {
            echo "<h1 class='sub_title'><u>Current Flavor:</u> <i>$currentFlavor</i></h1>";
        }

        $income = $row['ItemIncome'];
        $expense = $row['ItemExpenses'];
        $profit = $row['ItemProfit'];

        $profitClass = $profit > 0 ? "income" : "expenses";

        $actionsClass = "actions_no_stats";

        $showItemStats = true;
        if (isset($_SESSION['ShowShelf']) && $_SESSION['ShowItemStats'] == 0) {
            $showItemStats = false;
        }

        if ($showItemStats) {
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

        if (IsLoggedIn() && !IsInactive()) {
            echo "<div class='$actionsClass'>";
            echo "<button id='add_button_" . $row['ID'] . "' onclick='addItemToCart(" . $row['ID'] . ")' style='float:right;' class='quantity_button $buttonClass' title='Add item(s)'>Add</button>";
            echo "<span style='float:right;' class='quantity' id='quantity_holder_" . $row['ID'] . "'>0</span>";
            echo "<button id='remove_button_" . $row['ID'] . "' onclick='removeItemFromCart(" . $row['ID'] . ")' style='float:left;' class='quantity_button_disabled quantity_button' title='Remove item(s)'>Remove</button>";
            echo "</div>"; //actions
        }
        echo "</div>"; //post-content

        echo "<div>Something</div>";

        printHolidayLights();

        echo "</span>"; //card_block
    }
}
?>