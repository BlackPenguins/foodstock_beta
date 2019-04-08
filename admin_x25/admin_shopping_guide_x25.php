<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_SHOPPING_GUIDE_LINK;
    include( HEADER_PATH );
    
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
    
        $shoppingTitle = "Shopping Guide";
        
        if( isset( $_GET['store'] ) ) {
           $shoppingTitle .= " - Only showing the items that make a profit at <b>" . $_GET['store'] . "</b>";
        }
        // ------------------------------------
        // ITEM TABLE
        // ------------------------------------

        echo "<span class='soda_popout' style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>$shoppingTitle</span></span>";
        
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
        
        echo "<div style='margin:20px;'>";
        echo "<span style='float:right;' id='shopping_button' class='nav_buttons nav_buttons_admin'>Add Shopping Guide</span>";
        echo "<a href='" . ADMIN_SHOPPING_GUIDE_LINK . "'><span style='color:#000000; cursor:pointer; border:2px solid #000; margin-right:5px; background-color:#ffffff; padding: 5px;'>All</span></a>";
        foreach( $storeColors as $store => $color ) {
            echo "<a href='" . ADMIN_SHOPPING_GUIDE_LINK . "?store=$store'><span style='color:#000000; cursor:pointer; border:2px solid #000; margin-right:5px; background-color:$color; padding: 5px;'>$store</span></a>";
        }
        echo "</div>";
        
        $rowClass = "odd";
        $number = 1;
        $results = $db->query("SELECT ID, Type, Name, RefillTrigger, OutOfStockReporter, ImageURL, UnitName, UnitNamePlural, Date, DateModified, ModifyType, ChartColor, TotalCans, (BackstockQuantity + ShelfQuantity) as 'Total', Price, DiscountPrice, TotalIncome, TotalExpenses, Retired, Hidden, (ShelfQuantity + BackstockQuantity) as Total FROM Item WHERE Retired = 0 AND Hidden != 1 ORDER BY Type DESC, RefillTrigger ASC, Total ASC");
        while ($row = $results->fetchArray()) {

            $outOfStock = $row['RefillTrigger'];
            $item_id = $row['ID'];
            $item_name = $row['Name'];
            $price = $row['Price'];
            $originalPrice = $price;
            $discountPrice = $row['DiscountPrice'];
            $hasDiscount = false;
            
            $price_color = "#FFFFFF";
            $price_background_color = "#025F00";
            
            if( $discountPrice != "" ) {
                $price = $discountPrice;
                $hasDiscount = true;
                $price_color = "#000000";
                $price_background_color = "#FFD500";
            }
            
            $STORE_PRICES_TABLE = "";
            $profitAtThisStore = false;
            
            $resultsQuantity = $db->query("SELECT ItemID, CASE WHEN SalePrice IS NULL THEN RegularPrice/PackQuantity ELSE SalePrice/PackQuantity END CostEach, PackQuantity, Store, RegularPrice, SalePrice from Shopping_Guide WHERE ItemID = $item_id AND Store is NOT NULL ORDER BY CostEach");
            $rowQuantity = $resultsQuantity->fetchArray();
            
            if( $rowQuantity !== false ) {
                $STORE_PRICES_TABLE .= "<div title='Quantity in Stock' class='shopping_guide_container' style='font-size:0.9em; padding:10px; margin:10px;'>";
                $STORE_PRICES_TABLE .= "<b>Store Prices</b>";
            
                $STORE_PRICES_TABLE .= "<table class='bordered_table' style=font-size:0.7em;'>";
                $STORE_PRICES_TABLE .= "<tr><th>Store</th><th>Pack Quantity</th><th>Regular Price</th><th>Sale Price</th><th>Cost Each</th></tr>";
            
                do {
                    $packQuantity = $rowQuantity['PackQuantity'];
                    $store = $rowQuantity['Store'];
                    $regularPrice = $rowQuantity['RegularPrice'];
                    $salePrice = $rowQuantity['SalePrice'];
                    $costEach = $rowQuantity['CostEach'];

                    if( isset($_GET['store']) && $store == $_GET['store'] && $costEach <= $price ) {
                        $profitAtThisStore = true;
                    }
                    
                    $storeColor = $storeColors[$store];
                    
                    $rowStyle = "style='background-color: $storeColor'";
                    
                    if( $costEach > $price ) {
                        $rowStyle = " style='color:white; background-color:#232323;' ";
                    }
            
                    $STORE_PRICES_TABLE .= "<tr><td $rowStyle>$store</td><td $rowStyle>$packQuantity</td><td $rowStyle>$" . number_format( $regularPrice, 2 ) . "</td><td $rowStyle>$" . number_format( $salePrice, 2 ) . "</td><td $rowStyle>$" . number_format( $costEach, 2 ) . "</td></tr>";
                } while ($rowQuantity = $resultsQuantity->fetchArray() );
            
                $STORE_PRICES_TABLE .= "</table>";
            
                $STORE_PRICES_TABLE .= "</div>";
            }
            
            
            // Skip drawing the cards that dont make a profit at this store
            if( isset( $_GET['store'] ) ) {
                if( !$profitAtThisStore ) {
                    continue;
                }
            }
            
            echo "<div class='" . $row['Type'] . "_card card'>";
            echo "<div class='top_section'>";
            
            $totalQuantity = $row['Total'];
            
            echo "<div style='height:220px;'>";
            
            echo "<span style='height: 8%; font-size:1em; color:$price_color; padding:5px; font-weight:bold; background-color:$price_background_color; border: 2px solid #6b6b6b; float:right;'>". getPriceDisplay( $price ) ."</span>";

            if( $outOfStock == 1 ) {
                $reporter = $row['OutOfStockReporter'];
                echo "<span style='font-size:0.7em; margin-right: 10px; color:#000; padding:5px; background-color:#ff8100; border: 2px solid #000; float:right;'>Sold Out Reported by:<br><b>$reporter</b></span>";
            }
            
            echo "<div style='width:40%; float:left;'>";
            echo "<span style='font-size:0.7em; padding:3px; color:#000000; margin-right: 3px; font-weight:bold; background-color:#00e7ff; border: 3px solid rgba(0, 0, 0, 0.4); float: left;'>". $number ."</span>";
            echo "<span style='float:left;'>";
            DisplayPreview($row['Name'], $totalQuantity == 0, $row['ImageURL'] );
            echo "</span>";
            echo "</div>";
            
            echo "<div style='width:56%; float:right;'>";
            
            $unitName = "[UNKNOWN]";
            $unitNamePlural = "[UNKNOWN]";
            
            if( $row['UnitName'] != "" ) {
                $unitName = $row['UnitName'];
            }
            
            if( $row['UnitNamePlural'] != "" ) {
                $unitNamePlural = $row['UnitNamePlural'];
            }
            
            if($totalQuantity == 0) {
                echo "<div class='no_item circle' style='padding:10px; color:#FF3838'><img width='15px' src='" . IMAGES_LINK . "none.png' title='Item sold out!'/>&nbsp;SOLD OUT</div>";
            } else {
                $unitNameFinal = $totalQuantity > 1 ? $unitNamePlural : $unitName;
                echo "<div title='Quantity in Stock' class='cold_item' style='padding:10px; margin:5px 0px;'>$totalQuantity $unitNameFinal</div>";
            }
           
            
            
            $resultsQuantity = $db->query("SELECT ItemID, PackQuantity from Shopping_Guide WHERE ItemID = $item_id AND Store is NULL");
            $rowQuantity = $resultsQuantity->fetchArray();
            
            if( $rowQuantity !== false ) {
                
                echo "<div title='Quantity in Stock' class='shopping_guide_container' style='font-size:0.8em; padding:10px;'>";
                echo "<b>Best Profits</b>";
                
                echo "<table class='bordered_table' style=font-size:0.7em;'>";
                echo "<tr><th>Pack Quantity</th><th>Max Cost for Pack</th></tr>";

                do {
                    $packQuantity = $rowQuantity['PackQuantity'];
                    $totalPrice = $price * $packQuantity;
                    echo "<tr><td>$packQuantity</td><td>$" . number_format( $totalPrice, 2 ) . "</td></tr>"; 
                } while ($rowQuantity = $resultsQuantity->fetchArray() );
                
                echo "</table>";
                echo "</div>";
            }
            
           
            echo "</div>";
            echo "</div>";
            echo "</div>"; //Top Section
           
            echo $STORE_PRICES_TABLE;
            
            
            $number++;
            echo "</div>"; //Card
        }
    echo "</span>";
?>

</body>