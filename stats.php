<?php
    include( "appendix.php" );
    include_once( LOG_FUNCTIONS_PATH );
    include_once( QUANTITY_FUNCTIONS_PATH );

    $url = STATS_LINK;
    
    // Start Date - 1 Year Ago
    $startDate =  date('Y-m-d', strtotime( '-1 year', time() ) );
    
    // End Date - Today
    $endDate = date('Y-m-d');
    
    if( isset( $_POST['start_date'])) {
        $startDate = $_POST['start_date'];
    }
    
    if( isset( $_POST['end_date'])) {
        $endDate = $_POST['end_date'];
    }
    
    $itemsToShow = [29,42,119];
    
    if(isset($_POST['submit_items'])){
        if(!empty($_POST['Item'])){
            // Loop to store and display values of individual checked checkbox.
            $itemsToShow = $_POST['Item'];
        }
    }

    $itemNamesToShow = "";
    $db = new SQLite3( getDB() );

    $statQuery = "SELECT Name FROM Item WHERE ID in " . getPrepareStatementForInClause( count( $itemsToShow ) ) . " Order BY Type DESC, NAME ASC";
    $statStatment = $db->prepare( $statQuery );
    bindStatementsForInClause( $statStatment, $itemsToShow );
    $results = $statStatment->execute();

    while ($row = $results->fetchArray()) {
        $itemNamesToShow .= $row['Name'] . ",";
    }
    
    $trackingName = "Stats - $startDate : $endDate [$itemNamesToShow]";

    include( HEADER_PATH );
?>
<script type="text/javascript">
    $( document ).ready( function() {

        $('#start_date').datepicker({ dateFormat: 'yy-mm-dd' });
        $('#end_date').datepicker({ dateFormat: 'yy-mm-dd' });
        
        <?php 
            echo "$('#start_date').datepicker('setDate', '$startDate' );";
            echo "$('#end_date').datepicker('setDate', '$endDate' );";
        ?>

        // top soda purchases
        // top snack purchases
        // top count purchase
        // your own ideas
        // sold per day 
    });
</script>
<script type="text/javascript" src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script> 
</head>

<?php
    echo "<div style='padding: 10px; background-color:#d07a30; border-bottom: 3px solid #000;'>";
    echo "<h1>Have an idea for a chart/graph? Let me know, I'll make it.</h1>";
    echo "<form enctype='multipart/form-data' action='" . STATS_LINK . "' method='POST' style='background-color:#b9b9b9; border:2px solid #000; padding:20px;' >";
    
    $statement = $db->prepare( "SELECT ID, Name, Retired FROM Item WHERE Hidden != :hidden Order BY Type DESC, NAME ASC" );
    $statement->bindValue( ":hidden", 1 );
    $results =$statement->execute();
    
    //--------------------------------------------------------
    //--------------------------------------------------LAURIE DOESNT USE THE SITE THERE IS MISSING INFORMATION!!
    
    echo "<table>";
    $itemCountInRow = 0;
    echo "<tr>";
    while ($row = $results->fetchArray()) {
        
        $itemName = $row['Name'];
        $itemID = $row['ID'];
        $isRetired = $row['Retired'] == 1;
        $fontStyle = $isRetired ? "style='font-weight:bold; color:#962222'" : "";
        echo "<td><input type='checkbox' class='item_checkbox' name='Item[]' value='$itemID'><span $fontStyle>$itemName</span></input></td>";
        
        $itemCountInRow++;
        if( $itemCountInRow == 7 ) {
            $itemCountInRow = 0;
            echo "</tr><tr>";
        }
    }
    echo "</table>";
    
    echo "<input type='submit' name='submit_items' value='Graph Items'/>";
    echo "<div style='margin-top: 10px;'>";
    echo "<input id='checkAll' type='checkbox' name='CheckAll'>Check All Items</input>";
    echo "</div>";
    echo "</form>";

    echo "<div id='purchasesPerMonth' style='margin-bottom:10px; padding:10px; width: 900px; height: 500px'></div>";
    
    log_debug("Stats - Start Date [$startDate] End Date [$endDate]");
    
    echo "<form enctype='multipart/form-data' action='" . STATS_LINK . "' method='POST' style='background-color:#b9b9b9; border:2px solid #000; padding:20px; margin-top:30px;'>";
    echo "Start Date: <input autocomplete='off' type='text' name='start_date' id='start_date'>";
    echo "End Date: <input autocomplete='off' type='text' name='end_date' id='end_date'>";
    echo "<input type='submit' value='Give me Statistics!'/>";
    echo "</form>";
    
    ?>
    
<script type="text/javascript">

$("#checkAll").change(function(){
    var status = $(this).is(":checked") ? true : false;
    console.log("Changing to [" + status  + "]" );
    $(".item_checkbox").prop("checked",status);
});

window.onload = function() {

    $("#purchasesPerMonth").CanvasJSChart({ 
        title: { 
            text: "Purchases per Month" 
        }, 
        axisY: { 
            title: "Total Units Purchased" 
        },
        data: [ 
        <?php  getData( $db, $itemsToShow ); ?>
        ] 
    });   
    
    $("#topSnacks").CanvasJSChart({ 
        title: { 
            text: "Top Snacks/Soda Purchased" 
        }, 
        axisY: { 
            title: "Total Cost Purchased" 
        }, 
        data: [ 
        { 
            type: "bar", 
            toolTipContent: "{label}: {y}",
            xValueFormatString: "$#,###.00",
            yValueFormatString: "$#,###.00",
            dataPoints: [ 

                <?php
                $totalPurchasesTotalQuery = "SELECT count(p.itemid) as 'count', " .
                    "sum(CASE " .
                    "WHEN d.DiscountPrice IS NOT NULL AND d.DiscountPrice > 0 THEN d.DiscountPrice ".
                    "WHEN d.Price IS NOT NULL THEN d.Price " .
                    "WHEN p.DiscountCost IS NOT NULL AND p.DiscountCost > 0 THEN p.DiscountCost " .
                    "WHEN p.Cost IS NOT NULL THEN p.Cost ".
                    "ELSE 99999 END) as 'totalCost', " . // Make a bug very obvious if its not these cases
                    "p.itemid, i.name as 'name', i.type as 'type' " .
                    "FROM Purchase_History p " .
                    "JOIN Item i on p.itemid = i.id  " .
                    "LEFT JOIN Item_Details d on p.ItemDetailsID = d.ItemDetailsID  " .
                    "WHERE p.Date BETWEEN :startDate AND :endDate AND p.Cancelled is NULL " .
                    "GROUP BY p.itemid " .
                    "ORDER BY totalcost asc";
                $statement = $db->prepare( $totalPurchasesTotalQuery );
                $statement->bindValue( ":startDate", $startDate );
                $statement->bindValue( ":endDate", $endDate );
                $results = $statement->execute();

                    while ($row = $results->fetchArray()) {
                        $itemName = $row['name'];
                        $count = $row['count'];
                        $totalCost = getPriceDisplayWithDecimals( $row['totalCost'] );
                        $itemType = $row['type'];
                          
                        echo "{ label: '$itemName ($count units)', y: $totalCost  }, ";
                    }
                ?>
            ] 
        } 
        ] 
    });   
    
    $("#topUsers").CanvasJSChart({ 
        title: { 
            text: "Top Purchases by User",
            fontSize: 24
        }, 
        axisY: { 
            title: "Products in $" 
        }, 
        legend :{ 
            verticalAlign: "center", 
            horizontalAlign: "right" 
        }, 
        data: [ 
        { 
            type: "pie", 
            showInLegend: true, 
            toolTipContent: "{label} (#percent%) <br/> {y}", 
            indexLabel: "{y}", 
            yValueFormatString: "$#,###.00",
            dataPoints: [ 

            <?php
//                  $anonNames = ['Rabbit', 'Koala', 'Panda', 'Cat', 'Dog', 'Mouse', 'Porcupine', 'Monkey', 'Giraffe', 'Dolphin', 'Jaguar', 'Seal', 'Deer', 'Penguin', 'Lamb', 'Owl', 'Kangaroo', 'Fox', 'Hamster', 'Lion' ];
                $anonCount = 0;
                $totalPurchasesByUserQuery = "SELECT u.UserName, u.AnonName, u.FirstName, u.LastName, " .
                "sum(CASE " .
                    "WHEN d.DiscountPrice IS NOT NULL AND d.DiscountPrice > 0 THEN d.DiscountPrice ".
                    "WHEN d.Price IS NOT NULL THEN d.Price " .
                    "WHEN p.DiscountCost IS NOT NULL AND p.DiscountCost > 0 THEN p.DiscountCost " .
                    "WHEN p.Cost IS NOT NULL THEN p.Cost ".
                    "ELSE 99999 END) as 'TheTotal', " . // Make a bug very obvious if its not these cases
                "p.userID " .
                "FROM Purchase_History p " .
                "JOIN User u on p.UserID = u.UserID " .
                "LEFT JOIN Item_Details d ON p.ItemDetailsID = d.ItemDetailsID  " .
                "WHERE p.cancelled is null and p.Date BETWEEN :startDate AND :endDate " .
                "GROUP BY u.UserID " .
                "ORDER BY TheTotal";
                $totalPurchasesByUserStatement = $db->prepare( $totalPurchasesByUserQuery );
                $totalPurchasesByUserStatement->bindValue( ":endDate", $endDate );
                $totalPurchasesByUserStatement->bindValue( ":startDate", $startDate );
                $results = $totalPurchasesByUserStatement->execute();

                log_sql( "Total Purchases SQL: [$totalPurchasesByUserQuery]" );
                 
                while ($row = $results->fetchArray()) {
                    $name = $row['FirstName'] . " " . $row['LastName'];
                    $total = getPriceDisplayWithDecimals( $row['TheTotal'] );
                    $userName = $row['UserName'];
                    $anonName = $row['AnonName'];
                     
                    if( IsLoggedIn() && $userName == $_SESSION['UserName'] ) {
                        $name = "(YOU)";
                    } else if( !IsAdminLoggedIn() ) {
                        $name = $anonName;
                    }
                    
                    echo "{ label: '$name',  y: $total, legendText: '$name'}, ";
                }
            ?>
            ] 
        } 
        ] 
    }); 
} 
</script> 
<?php
    echo "<div id='topSnacks' style='margin-bottom:10px; padding:10px; width: 900px; height: 500px'></div>";
    echo "<div id='topUsers' style='margin-bottom:10px; padding:10px; width: 900px; height: 500px'></div>";

    echo "<div class='center_piece'>";
    echo "This table shows when an item was last bought. Red rows are already discontinued. Used to determine which items to discontinue.";
    echo "<div class='rounded_table'>";
    echo "<table>";
    echo "<thead><tr class='table_header'>";
    echo "<th>Item</th>";
    echo "<th>Last Bought</th>";
    echo "</tr>";

    $current_date = new DateTime();
    $lastPurchaseByItemQuery = "select max(d.Date) as LastBought, i.Name as ItemName, i.Retired as Discontinued " .
     "FROM Inventory_History d " .
     "JOIN Item i on d.ItemID = i.ID ".
     "WHERE d.ItemID in " . getPrepareStatementForInClause( count( $itemsToShow ) ) . " and d.ShelfQuantityBefore > d.ShelfQuantity " .
     "GROUP BY d.ItemID " .
     "ORDER BY LastBought ASC";
    $lastPurchaseByItemStatement = $db->prepare( $lastPurchaseByItemQuery );
    bindStatementsForInClause( $lastPurchaseByItemStatement, $itemsToShow );
    $lastPurchaseByItemResults = $lastPurchaseByItemStatement->execute();

    while ($lastPurchaseByItemRow = $lastPurchaseByItemResults->fetchArray()) {
        $itemName = $lastPurchaseByItemRow['ItemName'];
        $lastBought = $lastPurchaseByItemRow['LastBought'];
        $isDiscontinued = $lastPurchaseByItemRow['Discontinued'] == 1;
        $rowStyle = $isDiscontinued ? "class='discontinued_row'" : "";
        echo "<tr $rowStyle>";
        echo "<td>$itemName</td>";
        echo "<td>$lastBought (" . DisplayAgoTime($lastBought, $current_date) . ")</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";


//     getData( $db, $itemsToShow, $isLoggedIn, $isLoggedInAdmin );
    echo "</div>";


/**
 * @param $db SQLite3
 * @param $itemsToShow
 */
    function getData( $db, $itemsToShow ) {
        $query = "select COUNT(p.ID) as TotalItems, i.Name, p.ItemID, strftime(\"%m-%Y\", p.Date) as 'Time' " .
            "FROM Purchase_History p " .
            "JOIN Item i on p.ItemID =  i.ID " .
            "WHERE p.ItemID in " . getPrepareStatementForInClause( count( $itemsToShow ) ) .
            "GROUP BY strftime(\"%m-%Y\", p.Date), p.ItemID " .
            "ORDER BY p.ItemID, p.Date ASC";
        $statement = $db->prepare( $query );
        bindStatementsForInClause( $statement, $itemsToShow );
        $results = $statement->execute();

        $currentItem = -1;
        while ($row = $results->fetchArray()) {
            $totalItems = $row['TotalItems'];
            $name = $row['Name'];
            $time = $row['Time'];
            $itemID = $row['ItemID'];
            
            $splitDate = explode('-', $time );
            $month = $splitDate[0];
            $year = $splitDate[1];
            
            $userQuery = "select u.FirstName, u.LastName, u.UserName, u.AnonName, count(u.UserName) as UserCount " .
                "FROM Purchase_History p " .
                "JOIN User u ON p.userID = u.UserID " .
                "WHERE p.ItemID = :itemID AND strftime(\"%m-%Y\", p.Date) = :monthYear GROUP BY u.UserName;";
            $userStatement = $db->prepare( $userQuery );
            $userStatement->bindValue( ":itemID", $itemID );
            $userStatement->bindValue( ":monthYear", $month . "-" . $year );
            $userResults = $userStatement->execute();

            $hoverInfo = "";
            while ($userRow = $userResults->fetchArray()) {
                $fullName = $userRow['FirstName'] . " " . $userRow['LastName'];
                $userCount = $userRow['UserCount'];
                $userName = $userRow['UserName'];
                $anonName = $userRow['AnonName'];
                
                if( IsLoggedIn() && $userName == $_SESSION['UserName'] ) {
                    $fullName = "(YOU)";
                } else if( !IsAdminLoggedIn() ) {
                    $fullName = $anonName;
                }
                
                $hoverInfo = $hoverInfo . "$fullName: $userCount units<br>";
            }

            if( $currentItem == -1 || $currentItem != $itemID ) {
                
                if( $currentItem != -1 ) {
                    // Close the previous one
                    echo "]},\n";
                }
                
                echo "{" .
                        "type: 'line'," .
                        "showInLegend: true," .
                        "name: '$name'," .
                        "dataPoints: [";
                
                $currentItem = $itemID;
            }
            
            
            echo " { x: new Date($year, ($month - 1), 1), y: $totalItems, toolTipContent: \"<u><b>{name}:</u> {y} total units</b><br>$hoverInfo\" }, \n";
        }
        
        // Close the final one
        echo "]},\n";
    }
?>

</body>