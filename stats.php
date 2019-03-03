<?php
    include( "appendix.php" );
    
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
    
    $itemsToShow = "29,42,119";
    
    if(isset($_POST['submit_items'])){
        if(!empty($_POST['Item'])){
            // Loop to store and display values of individual checked checkbox.
            $itemsToShow = implode(",", $_POST['Item'] );
        }
    }
    
    $trackingName = "Stats - $startDate : $endDate [$itemsToShow]";
    
    include( HEADER_PATH );
?>
<script type="text/javascript">
    $( document ).ready( function() {

        $('#start_date').datepicker({ dateFormat: 'yy-mm-dd' });
        $('#end_date').datepicker({ dateFormat: 'yy-mm-dd' });
        
        <?php 
            if(!$isMobile && $isLoggedIn) {
                echo "loadUserModals();\n";
            }
            
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
    
    $query = "SELECT ID, Name, Retired FROM Item WHERE Hidden != 1 Order BY Type DESC, NAME ASC";
    $results = $db->query( $query );
    
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
        echo "<td><input type='checkbox' name='Item[]' value='$itemID'><span $fontStyle>$itemName</span></input></td>";
        
        $itemCountInRow++;
        if( $itemCountInRow == 7 ) {
            $itemCountInRow = 0;
            echo "</tr><tr>";
        }
    }
    echo "</table>";
    
    echo "<input type='submit' name='submit_items' value='Graph Items'/>";
    echo "</form>";

    echo "<div id='purchasesPerMonth' style='margin-bottom:10px; padding:10px; width: 900px; height: 500px'></div>";
    
    error_log("Start Date [$startDate] End Date [$endDate]");
    
    echo "<form enctype='multipart/form-data' action='" . STATS_LINK . "' method='POST' style='background-color:#b9b9b9; border:2px solid #000; padding:20px; margin-top:30px;'>";
    echo "Start Date: <input autocomplete='off' type='text' name='start_date' id='start_date'>";
    echo "End Date: <input autocomplete='off' type='text' name='end_date' id='end_date'>";
    echo "<input type='submit' value='Give me Statistics!'/>";
    echo "</form>";
    
    ?>
    
<script type="text/javascript"> 
window.onload = function() {

    $("#purchasesPerMonth").CanvasJSChart({ 
        title: { 
            text: "Purchases per Month" 
        }, 
        axisY: { 
            title: "Total Units Purchased" 
        },
        data: [ 
        <?php  getData( $db, $itemsToShow, $isLoggedIn, $isLoggedInAdmin ); ?>
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
                $totalPurchasesTotalQuery = "select count(p.itemid) as 'count', sum(p.cost) as 'totalCost', p.itemid, i.name as 'name', i.type as 'type' from purchase_history p JOIN item i on p.itemid = i.id where p.Date between '$startDate' AND '$endDate' AND p.Cancelled is NULL group by p.itemid order by totalcost asc";
                $results = $db->query( $totalPurchasesTotalQuery );
                    while ($row = $results->fetchArray()) {
                        $itemName = $row['name'];
                        $count = $row['count'];
                        $totalCost = $row['totalCost'];
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
                 $totalPurchasesByUserQuery = "select u.UserName, u.AnonName, u.FirstName, u.LastName, sum(p.cost) as 'Total' from Purchase_History p LEFT JOIN User u ON p.UserID = u.UserID WHERE p.Date between '$startDate' AND '$endDate' group by p.UserID order by total";
                 $results = $db->query( $totalPurchasesByUserQuery );
                 error_log( $totalPurchasesByUserQuery );
                 
                 while ($row = $results->fetchArray()) {
                    $name = $row['FirstName'] . " " . $row['LastName'];
                    $total = $row['Total'];
                    $userName = $row['UserName'];
                    $anonName = $row['AnonName'];
                     
                    if( $isLoggedIn && $userName == $_SESSION['UserName'] ) {
                        $name = "(YOU)";
                    } else if( !$isLoggedInAdmin ) {
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
    
//     getData( $db, $itemsToShow, $isLoggedIn, $isLoggedInAdmin );
    echo "</div>";
    
    
    
    function getData( $db, $itemsToShow, $isLoggedIn, $isLoggedInAdmin ) {
        $query = "select COUNT(p.ID) as TotalItems, i.Name, p.ItemID,
           strftime(\"%m-%Y\", p.Date) as 'Time'
           from Purchase_History p JOIN Item i on p.ItemID =  i.ID WHERE p.ItemID in ($itemsToShow) group by strftime(\"%m-%Y\", p.Date), p.ItemID ORDER BY p.ItemID, p.Date ASC";
        $results = $db->query( $query );
        
        //echo "QUERY [ $query ]<br><br>";
        
        $currentItem = -1;
        while ($row = $results->fetchArray()) {
            $totalItems = $row['TotalItems'];
            $name = $row['Name'];
            $time = $row['Time'];
            $itemID = $row['ItemID'];
            
            $splitDate = explode('-', $time );
            $month = $splitDate[0];
            $year = $splitDate[1];
            
            $userQuery = "select u.FirstName, u.LastName, u.UserName, u.AnonName, count(u.UserName) as UserCount from Purchase_History p JOIN User u ON p.userID = u.UserID WHERE p.ItemID = $itemID AND strftime(\"%m-%Y\", p.Date) = \"" . $month . "-" . $year . "\" GROUP BY u.UserName;";
            $userResults = $db->query( $userQuery );
            
            $hoverInfo = "";
            while ($userRow = $userResults->fetchArray()) {
                $fullName = $userRow['FirstName'] . " " . $userRow['LastName'];
                $userCount = $userRow['UserCount'];
                $userName = $userRow['UserName'];
                $anonName = $userRow['AnonName'];
                
                if( $isLoggedIn && $userName == $_SESSION['UserName'] ) {
                    $fullName = "(YOU)";
                } else if( !$isLoggedInAdmin ) {
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