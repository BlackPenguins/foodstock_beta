<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    $db = new SQLite3("db/item.db");
    if (!$db) die ($error);
        
    include("foodstock_functions.php");
    date_default_timezone_set('America/New_York');
        
    Login($db);

    $isLoggedIn = IsLoggedIn();
    $isLoggedInAdmin = IsAdminLoggedIn();
    $loginPassword = false;
    
    $itemType = "Soda";
    $url = "sodastock.php";
        
    require_once 'Mobile_Detect.php';
 
    $detect = new Mobile_Detect;
    $device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
    $isMobile = $device_type == 'phone';

    if(isset($_GET['mobile'])) {
        $isMobile = true;
    }
        
    echo "<title>Admin - Foodstock</title>";
    echo "<link rel='icon' type='image/png' href='soda_can_icon.png' />";
?>




<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script src="js/jscolor.js"></script>

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
            if(!$isMobile && $isLoggedInAdmin) {
                echo "loadSingleModals();\n";
                echo "loadItemModals('Soda');\n";
                echo "loadItemModals('Snack');\n";
            }
        ?>           
    });
</script>
</head>

<?php

    if( $isMobile ) {
        //Some magic that makes the top blue bar fill the width of the phone's screen
        echo "<body class='soda_body' style='display:inline-table;'>";
    } else {
        echo "<body class='soda_body'>";
    }
    
    include("build_admin_forms.php");
    include("login_bar.php");
    
    TrackVisit($db, 'Admin');
    
    if( !$isLoggedInAdmin ) {
        // Only admin is allowed on this page
        die;
    }

    DisplayUserMessage();
    
    echo "<span style='width:11%; vertical-align:top; display:inline-block; padding: 10px; background-color:#4d544e; border: 0px solid #000;'>";
    
    if( $isLoggedInAdmin ) {
        include "admin_nav_x25.php";
    }
    
    echo "</span>";
    
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        // ------------------------------------
        // RESTOCK TABLE
        // ------------------------------------
        echo "<span class='soda_popout' style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>Restock Schedule</span></span>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Number of Units</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Total Cost</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Cost Each</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Current Price</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Discount Price</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        $previousItem = "";
        
        $results = $db->query("SELECT s.Name, r.ItemID, r.Date, r.NumberOfCans, r.Cost, (r.Cost/r.NumberOfCans) as 'CostEach', s.Price, s.DiscountPrice, s.Retired FROM Restock r JOIN Item s ON r.itemID = s.id  ORDER BY r.Date DESC");
        while ($row = $results->fetchArray()) {
            $maxCostEach = "";
            if( $previousItem != "" && $previousItem != $row['Name'] ) {
                if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
                $maxCostEach = "font-weight:bold; font-size:1.1em;";
            }
            
            if( $row['Retired'] == 1) {
                $rowClass = "discontinued_row";
            }
            
            echo "<tr class='$rowClass'>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Name'] . "</td>";
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
            echo "<td style='padding:5px; border:1px #000 solid;'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['NumberOfCans'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>$" . number_format( $row['Cost'], 2) . "</td>";
            $costEach = $row['CostEach'];
            
            
            echo "<td style='padding:5px; $maxCostEach border:1px #000 solid;'>$" . number_format( $costEach, 2 )  . "</td>";
            echo "<td style='padding:5px; $maxCostEach border:1px #000 solid;'>$" . number_format( $row['Price'], 2 )  . "</td>";
            echo "<td style='padding:5px; $maxCostEach border:1px #000 solid;'>$" . number_format( $row['DiscountPrice'], 2 )  . "</td>";
            echo "</tr>";
            
            $previousItem = $row['Name'];
        }
        
        echo "</table>";
    echo "</span>";
?>

</body>