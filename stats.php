<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Statistics</title>
<link rel='icon' type='image/png' href='soda_can_icon.png' />
<?php
    $db = new SQLite3("db/item.db");
    if (!$db) die ($error);
    
    $url = "sodastock.php";
    
    include("foodstock_functions.php");
    date_default_timezone_set('America/New_York');
        
    Login($db);
            
        
    $isLoggedIn = IsLoggedIn();
    $isLoggedInAdmin = IsAdminLoggedIn();
    $loginPassword = false;
    
    require_once 'Mobile_Detect.php';
 
    $detect = new Mobile_Detect;
    $device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
    $isMobile = $device_type == 'phone';

    if(isset($_GET['mobile'])) {
        $isMobile = true;
    }
?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>

<?php
    if( !$isMobile) {
        echo "<script src='js/load_modals.js'></script>";
    }
?>

<link rel="stylesheet" type="text/css" href="colorPicker.css"/>
<link rel="stylesheet" type="text/css" href="css/style.css"/>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">

<script type="text/javascript">
    $( document ).ready( function() {

        $('#start_date').datepicker({ dateFormat: 'yy-mm-dd' });
        $('#end_date').datepicker({ dateFormat: 'yy-mm-dd' });
        
        <?php 
            if(!$isMobile && $isLoggedIn) {
                echo "loadUserModals();\n";
            }
            
            if( !isset( $_POST['start_date'])) {
                echo "$('#start_date').datepicker('setDate', '-90' );";
            } else {
                echo "$('#start_date').datepicker('setDate', '" . $_POST['start_date'] . "' );";
            }
            
            if( !isset( $_POST['end_date'])) {
                echo "$('#end_date').datepicker('setDate', new Date() );";
            } else {
                echo "$('#end_date').datepicker('setDate', '" . $_POST['end_date'] . "' );";
            }
            
        ?>

        // top soda purchases
        // top snack purchases
        // top count purchase
        // your own ideas
        // sold per day 
    });

    function toggleCompleted( requestID ) {
        $.post("sodastock_ajax.php", { 
            type:'ToggleRequestCompleted',
            id:requestID,
        },function(data) {
        });
    }
</script>
</head>

<?php

    if( $isMobile ) {
        //Some magic that makes the top blue bar fill the width of the phone's screen
        echo "<body class='soda_body' style='display:inline-table;'>";
    } else {
        echo "<body class='soda_body'>";
    }
    
    include("login_bar.php");
    
    TrackVisit($db, 'Stats');
    
    echo "<div style='padding: 10px; background-color:#d07a30; border-bottom: 3px solid #000;'>";
   
    $startDate = "2018-09-01";
    $endDate = "2018-10-01";
    
    if( isset( $_POST['start_date'])) {
        $startDate = $_POST['start_date'];
    }
    
    if( isset( $_POST['end_date'])) {
        $endDate = $_POST['end_date'];
    }
    
    echo "<form enctype='multipart/form-data' action='stats.php' method='POST'>";
    echo "Start Date: <input autocomplete='off' type='text' name='start_date' id='start_date'>";
    echo "End Date: <input autocomplete='off' type='text' name='end_date' id='end_date'>";
    echo "<input type='submit' value='Give me Statistics!'/>";
    echo "</form>";
    
    echo "<h1>Have an idea for a chart/graph? Let me know, I'll make it.</h1>";
    ?>
    <script type="text/javascript" src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script> 
<script type="text/javascript"> 
window.onload = function() {

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
                    $results = $db->query( "select count(p.itemid) as 'count', sum(p.cost) as 'totalCost', p.itemid, i.name as 'name', i.type as 'type' from purchase_history p JOIN item i on p.itemid = i.id where p.Date between '$startDate' AND '$endDate' AND p.Cancelled is NULL group by p.itemid order by totalcost asc" );
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
                 $anonNames = ['Rabbit', 'Koala', 'Panda', 'Cat', 'Dog', 'Mouse', 'Porcupine', 'Monkey', 'Giraffe', 'Dolphin', 'Jaguar', 'Seal', 'Deer', 'Penguin', 'Lamb', 'Owl', 'Kangaroo', 'Fox', 'Hamster', 'Lion' ];
                 $anonCount = 0;
                 $results = $db->query( "select u.UserName, u.FirstName, u.LastName, sum(p.cost) as 'Total' from Purchase_History p LEFT JOIN User u ON p.UserID = u.UserID WHERE p.Date between '$startDate' AND '$endDate' group by p.UserID order by total" );
                 while ($row = $results->fetchArray()) {
                    $name = $row['FirstName'] . " " . $row['LastName'];
                    $total = $row['Total'];
                    $userName = $row['UserName'];
                     
                    if( $isLoggedIn && $userName == $_SESSION['UserName'] ) {
                        $name = "(YOU)";
                    } else if( !$isLoggedInAdmin ) {
                        $name = $anonNames[$anonCount++];
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
    echo "</div>";
?>

</body>