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
                
        <?php 
            if(!$isMobile && $isLoggedIn) {
                echo "loadUserModals();\n";
            }
        ?>           
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
   
    echo "<h1>Top Bought Snacks of July 2018</h1>";
    echo "<table style='border-collapse: collapse;'>";
    echo "<tr><th style='padding:10px;'>Name</th><th style='padding:10px;'>Amount</th><th style='padding:10px;'>Total Income</th></tr>";
//     echo "<ul style='list-style-type: none;'>";
    $results = $db->query("select count(p.itemid) as 'count', sum(p.cost) as 'totalCost', p.itemid, i.name as 'name', i.type as 'type' from purchase_history p JOIN item i on p.itemid = i.id where p.Date between '2018-07-01' AND '2018-08-01' group by p.itemid order by count desc");
    while ($row = $results->fetchArray()) {
        $itemName = $row['name'];
        $count = $row['count'];
        $totalCost = $row['totalCost'];
        $itemType = $row['type'];
        
        $color = $itemType == "Snack" ? "nav_buttons_snack" : "nav_buttons_soda";
//         echo "<li style='border: 1px solid #000; padding:5px;' class='$color'>$itemName - $count times ($" . number_format( $totalCost, 2) . ")</li>";
        echo "<tr class='$color'>";
        echo "<td style='padding:5px; border:1px solid #000;'>$itemName</td>";
        echo "<td style='padding:5px; border:1px solid #000;'>$count</td>";
        echo "<td style='padding:5px; border:1px solid #000;'>$" . number_format( $totalCost, 2) . "</td>";
        echo "</tr>";
    }
//     echo "</ul>";
    echo "</table>";
    echo "</div>";
?>

</body>