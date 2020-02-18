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

            $finalCost = $cost;

            // Purchases that used credits might affect what was actually for balance
            if( $row['UseCredits'] > 0 ) {
                $finalCost -= $row["UseCredits"];
            }

            if( $row['Type'] == "Snack" ) {
                $currentMonthSnackTotal += $finalCost;
            } else if( $row['Type'] == "Soda" ) {
                $currentMonthSodaTotal += $finalCost;
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
?>