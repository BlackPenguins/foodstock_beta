<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );

    $url = ADMIN_CHECKLIST_LINK;
    include( HEADER_PATH );
    
    echo "<span class='admin_box'>";
    DrawChecklistTable( $db, "RefillTrigger", "Needs Refill from Desk" );
    DrawChecklistTable( $db, "RestockTrigger", "Needs Restock from Store" );
    echo "</span>";

    /**
     * @param $db SQLite3
     * @param $checklistType
     * @param $title
     */
    function DrawChecklistTable( $db, $checklistType, $title ) {
        $storeColors = array(
            "Walmart" => "#4274f4",
            "Costco" => "#41f473",
            "BJs" => "#f4bb41",
            "Target" => "#f44242",
            "Aldi" => "#b7b416",
            "Wegmans" => "#b75616",
            "PriceRite" => "#8e16b7",
            "Tops" => "#167cb7"
        );

        echo "<div class='page_header'><span class='title'>$title</span></div>";

        if( $checklistType == "RestockTrigger" ) {
            echo "<span id='shopping_button' class='nav_buttons nav_buttons_admin'>Add Shopping Guide</span>";
        }

        echo "<div class='center_piece'>";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr>";
        echo "<th class='admin_header_column' align='left'>&nbsp;</th>";
        echo "<th class='admin_header_column' align='left'>Name</th>";
        echo "<th class='admin_header_column' align='left'>Type</th>";
        echo "<th class='admin_header_column' align='left'>Shelf Quantity</th>";
        echo "<th class='admin_header_column' align='left'>Backstock Quantity</th>";

        echo "</tr></thead>";


        $results = getChecklistResults($db, $checklistType, "LIST" );
        while ($row = $results->fetchArray()) {
            $isDiscontinued = $row['Retired'] == 1;
            $isBought = $row['IsBought'] == 1;
            $itemID = $row['ID'];

            $price = $row['Price'];
            $discountPrice = $row['DiscountPrice'];

            if( $discountPrice != "" ) {
                $price = $discountPrice;
            }

            if( $isBought == 1 ) {
                $rowClass = "class='completed'";
            } else if( $isDiscontinued ) {
                $rowClass = "class='discontinued_row'";
            } else {
                $rowClass = "";
            }

            $extraInfo = "";

            if( $checklistType == "RestockTrigger" ) {
                $shoppingStatement = $db->prepare("SELECT ItemID, CASE WHEN SalePrice IS NULL OR SalePrice = 0 THEN RegularPrice/PackQuantity ELSE SalePrice/PackQuantity END CostEach, " .
                    "PackQuantity, Store, RegularPrice, SalePrice from Shopping_Guide " .
                    "WHERE ItemID = :itemID AND Store is NOT NULL ORDER BY CostEach");
                $shoppingStatement->bindValue( ":itemID", $itemID );
                $shoppingResults = $shoppingStatement->execute();

                while ($shoppingRow = $shoppingResults->fetchArray()) {
                    $packQuantity = $shoppingRow['PackQuantity'];
                    $store = $shoppingRow['Store'];
                    $regularPrice = $shoppingRow['RegularPrice'];
                    $salePrice = $shoppingRow['SalePrice'];
                    $costEach = $shoppingRow['CostEach'];

                    $details = "$packQuantity items for " . getPriceDisplayWithDollars($regularPrice) . "/" . getPriceDisplayWithDollars( $salePrice );

                    if ( $costEach <= $price && $store != "Costco" && $store != "BJs") {
                        $profitMargin =  $price - $costEach;

                        $storeColor = $storeColors[$store];

                        $profitMarginFormatted = getPriceDisplayWithDollars( $profitMargin );

                        if(  strpos( $profitMarginFormatted, "Migration" ) !== false ) {
                            $profitMarginFormatted = "$0.00";
                        }

                        $extraInfo .= "<span title='$details' style='display:inline-block; background-color: $storeColor; color:#000000; margin: 5px 10px 0px 0px; padding:5px;'>$store: " . $profitMarginFormatted . "</span>";
                    }
                }

                $shoppingStatement= $db->prepare("SELECT ItemID, PackQuantity from Shopping_Guide " .
                    "WHERE ItemID = :itemID AND Store is NULL");
                $shoppingStatement->bindValue( ":itemID", $itemID );
                $shoppingResults = $shoppingStatement->execute();

                $displayedBreak = false;

                while ($shoppingRow = $shoppingResults->fetchArray()) {
                    if( !$displayedBreak ) {
                        $displayedBreak = true;
                        $extraInfo .= "<br>";
                    }

                    $packQuantity = $shoppingRow['PackQuantity'];
                    $totalPrice = $price * $packQuantity;
                    $totalPriceFormatted = getPriceDisplayWithDollars( $totalPrice );
                    $extraInfo .= "<span style='display:inline-block; background-color: #000000; color:#bfbfbf; margin: 5px 10px 0px 0px; padding:5px;'>$packQuantity items&nbsp;&nbsp;:&nbsp;&nbsp;Less than $totalPriceFormatted</span>";
                }
            }


            echo "<tr $rowClass id='checklist_" . $checklistType . "_row_$itemID'>";
            drawCheckListRow( $isBought, $itemID, $row['Name'], $row['Type'], $row['ShelfAmount'], $row['BackstockAmount'], $isDiscontinued, $checklistType, $extraInfo );
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
        echo "</div>";
    }
?>

<script type="text/javascript">

    function toggleCompleted( itemID, checklistType ) {
        $.post("<?php echo AJAX_LINK; ?>", {
            type:'UpdateChecklist',
            id:itemID,
            checklistType:checklistType,
        },function(data) {
            var rowID = '#checklist_' + checklistType + '_row_' + itemID;
            console.log("Updating row [" + rowID + "] with [" + data + "]" );
            var checklistRow = $(rowID);
            checklistRow.html(data);

           if( checklistRow.hasClass( "completed" ) ) {
               checklistRow.removeClass( "completed" );
           } else {
               checklistRow.addClass( "completed" );
               if( checklistRow.hasClass( "discontinued_row" ) ) {
                   checklistRow.removeClass( "discontinued_row" );
               }
           }
        });
    }

    setupModal( "shopping" );
</script>

</body>