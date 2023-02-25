<?php
    include( "appendix.php" );
    include_once( LOG_FUNCTIONS_PATH );
    include_once( QUANTITY_FUNCTIONS_PATH );

    $url = STATS_LINK;
    
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
    
    $db = getDB();

    $trackingName = "Stats (Date) - $startDate : $endDate";

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
    });
</script>

<script type="text/javascript" src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script> 
</head>

<?php
    echo "<div style='padding: 10px; background-color:#d0b530; border-bottom: 3px solid #000;'>";
    echo "<div style='height: 90px;'>";
    $startDateFormatted = DateTime::createFromFormat('Y-m-d', $startDate );
    $startDateFormatted = $startDateFormatted->format('M jS, Y');
    $endDateFormatted = DateTime::createFromFormat('Y-m-d', $endDate );
    $endDateFormatted = $endDateFormatted->format('M jS, Y');

    echo "<span style='font-size: 2em;'>Showing <b>$startDateFormatted</b> to <b>$endDateFormatted</b></span>";
    echo "<form enctype='multipart/form-data' action='" . STATS_LINK . "' method='POST' style='background-color:#ffe566; border:2px solid #000; padding:5px; display:inline; float:right;'>";

    echo "<table>";

    echo "<tr>";
    echo "<td>Start Date:</td>";
    echo "<td><input autocomplete='off' type='text' name='start_date' id='start_date'></td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>End Date:</td>";
    echo "<td><input autocomplete='off' type='text' name='end_date' id='end_date'></td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td colspan='2' style='text-align: center; padding: 5px;'><input type='submit' value='Give me Statistics!'/></td>";
    echo "</tr>";

    echo "</table>";

    echo "</form>";
    echo "</div>";

    echo "<div class='graph_container' id='totalPurchasesPerDay'></div>";
    echo "<div class='graph_container' id='totalIncomePerDay'></div>";
    echo "<div class='graph_container' id='topSnacks'></div>";
    echo "<div class='graph_container' id='topUsers'></div>";
    echo "</div>";

    ?>
    
<script type="text/javascript">
window.onload = function() {

    $("#totalPurchasesPerDay").CanvasJSChart({
        title: {
            text: "Total Purchases per Day"
        },
        axisY: {
            title: "Units Purchased"
        },
        axisX:{
            interval: 5,
            intervalType: "day",
        },
        data: [
            {
                type: 'line',
                yValueFormatString: '$#,###.00',
                dataPoints: [
                    <?php  getTotalUnitsPerDay( $db, $startDate, $endDate, "UNITS" ); ?>
                ]
            }
        ]
    });

    $("#totalIncomePerDay").CanvasJSChart({
        title: {
            text: "Total Income per Day"
        },
        axisY: {
            title: "Income"
        },
        axisX:{
            interval: 5,
            intervalType: "day",
        },
        data: [
            {
                type: 'line',
                yValueFormatString: '$#,###.00',
                dataPoints: [
                    <?php  getTotalUnitsPerDay($db, $startDate, $endDate, "INCOME"); ?>
                ]
            }
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
                type: 'bar',
                toolTipContent: '{label}: {y}',
                xValueFormatString: '$#,###.00',
                yValueFormatString: '$#,###.00',
                dataPoints: [
                    <?php  getTotalUnitsPurchased($db, $startDate, $endDate); ?>
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
                    <?php getPurchasesByUser( $db, $startDate, $endDate ); ?>
                ]
            }
        ] 
    }); 
} 
</script>
</body>

<?php
    /**
     * @param $db SQLite3
     * @param $startDate
     * @param $endDate
     */
    function getTotalUnitsPurchased( $db, $startDate, $endDate ) {
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

            echo "{ label: '$itemName ($count units)', y: $totalCost  }, ";
        }
    }
    /**
     * @param $db SQLite3
     * @param $itemsToShow
     */
    function getTotalUnitsPerDay($db, $startDate, $endDate, $type ) {
        $query = "select COUNT(p.ID) as TotalItems, SUM(i.discountprice) as TotalIncome, strftime(\"%m-%d-%Y\", p.Date) as 'Time' " .
            "FROM Purchase_History p " .
            "JOIN Item_Details i ON p.itemDetailsID = i.itemDetailsID " .
            "WHERE p.Date BETWEEN :startDate AND :endDate AND p.Cancelled is NULL " .
            "GROUP BY strftime(\"%m-%d-%Y\", p.Date) " .
            "ORDER BY p.Date ASC";
        $statement = $db->prepare( $query );
        $statement->bindValue( ":startDate", $startDate );
        $statement->bindValue( ":endDate", $endDate );
        $results = $statement->execute();


        $totalItemsPerDay = array();

        while ($row = $results->fetchArray()) {
            $totalItems = $row['TotalItems'];
            $totalIncome = $row['TotalIncome'];
            $time = $row['Time'];

            $splitDate = explode('-', $time );
            $month = $splitDate[0];
            $day = $splitDate[1];
            $year = $splitDate[2];

            $dayKey = "$year, $month, $day";
            if( $type == "UNITS" ) {
                $totalItemsPerDay[$dayKey] = $totalItems;
                error_log("Add Units [$dayKey] [$totalItems]");
            } else if( $type == "INCOME" ) {
                $totalIncome = getPriceDisplayWithDecimals( $totalIncome );
                $totalItemsPerDay[$dayKey] = $totalIncome;
                error_log("Add Income [$dayKey] [$totalIncome]");
            }
        }

        $begin = new DateTime( $startDate );
        $end = new DateTime( $endDate );

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        foreach ($period as $dt) {
            $year = $dt->format("Y");
            $month = $dt->format("m");
            $day = $dt->format("d");
            $dayOfWeek = $dt->format("D");
            $tooltipFormat = $dt->format("m/d/Y");

            $dayKey = "$year, $month, $day";

            $totalItems = 0;
            if( array_key_exists( $dayKey, $totalItemsPerDay ) ) {
                $totalItems = $totalItemsPerDay[$dayKey];
            }

            if( ( $dayOfWeek == "Sun" || $dayOfWeek == "Sat" ) && $totalItems == 0 ) {
                continue;
            }

            $month = $month - 1;

            if( $month <= 9 ) {
                $month = "0$month";
            }

            $dayKey = "$year, $month, $day";
            $tooltipValue = "";

            if( $type == "INCOME" ) {
                $tooltipValue = "$" . $totalItems;
            } else if( $type == "UNITS" ) {
                $tooltipValue =  $totalItems . " purchases";
            }

            echo " { x: new Date($dayKey), y: $totalItems, toolTipContent:'$tooltipFormat: <b>$tooltipValue</b>' }, \n";
        }
    }

    /**
     * @param $db SQLite3
     * @param $startDate
     * @param $endDate
     */
    function getPurchasesByUser($db, $startDate, $endDate ) {
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
    }
?>