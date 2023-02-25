<?php
    include( "appendix.php" );
    include_once( LOG_FUNCTIONS_PATH );
    include_once( QUANTITY_FUNCTIONS_PATH );

    $url = ITEM_STATS_LINK;

    // Start Date - 1 Year Ago
    $startDate =  date('Y-m-d', strtotime( '-3 month', time() ) );

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
    $db = getDB();

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
    echo "<div style='padding: 10px; background-color:#d0b530; border-bottom: 3px solid #000;'>";
    //------------------------------------------------------------
    // LAURIE DOESNT USE THE SITE THERE IS MISSING INFORMATION!!
    //------------------------------------------------------------
    echo "<div style='margin-bottom: 10px;'>";
    $startDateFormatted = DateTime::createFromFormat('Y-m-d', $startDate );
    $startDateFormatted = $startDateFormatted->format('M jS, Y');
    $endDateFormatted = DateTime::createFromFormat('Y-m-d', $endDate );
    $endDateFormatted = $endDateFormatted->format('M jS, Y');

    echo "<span style='font-size: 2em;'>Showing <b>$startDateFormatted</b> to <b>$endDateFormatted</b></span>";

    echo "</div>";

    $statement = $db->prepare( "SELECT Type, COUNT(ID) as 'Count' FROM Item WHERE Hidden != :hidden GROUP BY TYPE" );
    $statement->bindValue( ":hidden", 1 );
    $results =$statement->execute();

    $numberOfSodaItems = 0;
    $numberOfSnackItems = 0;
    while ($row = $results->fetchArray()) {
        $type =$row['Type'];

        if( $type == "Soda" ) {
            $numberOfSodaItems = $row['Count'];
        } else if( $type == "Snack" ) {
            $numberOfSnackItems = $row['Count'];
        }
    }

    $statement = $db->prepare( "SELECT ID, Name, Retired, Type FROM Item WHERE Hidden != :hidden Order BY Type DESC, Retired ASC, NAME ASC" );
    $statement->bindValue( ":hidden", 1 );
    $results =$statement->execute();
    //------------------------------------------------------------
    // LAURIE DOESNT USE THE SITE THERE IS MISSING INFORMATION!!
    //------------------------------------------------------------

    $rows['Soda'] = array();
    $rows['Soda'][0] = array();
    $rows['Soda'][1] = array();
    $rows['Soda'][2] = array();

    $rows['Snack'] = array();
    $rows['Snack'][0] = array();
    $rows['Snack'][1] = array();
    $rows['Snack'][2] = array();

    $currentRow = 0;
    $endingSodaRow = floor($numberOfSodaItems / 3);
    $endingSnackRow = floor($numberOfSnackItems / 3);

    $currentType = null;

    while ($row = $results->fetchArray()) {

        $itemName = $row['Name'];
        $itemID = $row['ID'];
        $type = $row['Type'];
        $isRetired = $row['Retired'] == 1;
        $fontStyle = $isRetired ? "style='font-weight:bold; color:#962222'" : "";

        if( $currentType == null ) {
            $currentType = $type;
        } else if ( $currentType != $type ) {
            // We're on a new type - reset everything
            $currentRow = 0;
            $currentType = $type;
        }

        if( $type == "Soda" ) {
            $endingRow = $endingSodaRow;
        } else if( $type == "Snack" ) {
            $endingRow = $endingSnackRow;
        }

        $rows[$type][$currentRow][] = "<input type='checkbox' class='item_checkbox' name='Item[]' value='$itemID'><span $fontStyle>$itemName</span></input>";

        if( $currentRow >= $endingRow ) {
            $currentRow = 0;
        } else {
            $currentRow++;
        }
    }

    echo "<form enctype='multipart/form-data' action='" . ITEM_STATS_LINK . "' method='POST' style='background-color:#b9b9b9; border:2px solid #000; padding:20px;' >";
    echo "<div style='overflow: hidden; width: 100%;'>";
    echo "<div style='float:left; width: 48%;  background-color: #b4b7ff; padding: 10px; border: 3px solid #5c5f97'>";
    echo "<table>";
    for($row = 0; $row < count( $rows['Soda'] ); $row++ ) {
        echo "<tr>";
        for($col = 0; $col < count( $rows['Soda'][$row] ); $col++ ) {
            echo "<td>" . $rows['Soda'][$row][$col] . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    echo "<div style='float:right; width: 48%;  background-color: #ffaeb5; padding: 10px; border: 3px solid #974949'>";
    echo "<table>";
    for($row = 0; $row < count( $rows['Snack'] ); $row++ ) {
        echo "<tr>";
        for($col = 0; $col < count( $rows['Snack'][$row] ); $col++ ) {
            echo "<td>" . $rows['Snack'][$row][$col] . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    echo "</div>";

    echo "<div>";
    echo "<table style='margin-top: 15px;'>";

    echo "<tr>";
    echo "<td style='text-align: right'><input id='checkAll' type='checkbox' name='CheckAll'></input></td>";
    echo "<td>Check All Items</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>Start Date:</td>";
    echo "<td><input autocomplete='off' type='text' name='start_date' id='start_date'></td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>End Date:</td>";
    echo "<td><input autocomplete='off' type='text' name='end_date' id='end_date'></td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td colspan='2'><input type='submit' name='submit_items' value='Graph Items'/></td>";
    echo "</tr>";

    echo "</table>";
    echo "</form>";
    echo "</div>";

    echo "<div class='graph_container' id='purchasesPerMonth'></div>";
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
        axisX:{
            interval: 1,
            intervalType: "month",
        },
        data: [
        <?php  getUnitsPerMonth( $db, $itemsToShow, $startDate, $endDate ); ?>
        ]
    });
}
</script>
<?php
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
    function getUnitsPerMonth($db, $itemsToShow, $startDate, $endDate ) {
        $query = "select COUNT(p.ID) as TotalItems, i.Name, p.ItemID, strftime(\"%m-%Y\", p.Date) as 'Time' " .
            "FROM Purchase_History p " .
            "JOIN Item i on p.ItemID =  i.ID " .
            "WHERE p.ItemID in " . getPrepareStatementForInClause( count( $itemsToShow ) ) .
            "AND p.Date BETWEEN :startDate AND :endDate " .
            "GROUP BY strftime(\"%m-%Y\", p.Date), p.ItemID " .
            "ORDER BY p.ItemID, p.Date ASC";
        $statement = $db->prepare( $query );
        bindStatementsForInClause( $statement, $itemsToShow );
        $statement->bindValue( ":startDate", $startDate );
        $statement->bindValue( ":endDate", $endDate );
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

                $hoverInfo = $hoverInfo . "<br>$fullName: $userCount units";
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