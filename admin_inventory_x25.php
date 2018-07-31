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
        // INVENTORY TABLE
        // ------------------------------------
        echo "<span class='soda_popout' style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>Inventory/Purchases Schedule</span></span>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>User Name</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Shelf Quantity</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Backstock Quantity</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Price</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        $previousDate = "";
        
        $results = $db->query("SELECT i.Name, u.FirstName, u.LastName, r.Date, r.BackstockQuantityBefore, r.BackstockQuantity, r.ShelfQuantityBefore, r.ShelfQuantity, r.Price FROM Daily_Amount r JOIN Item i ON r.itemID = i.id LEFT JOIN Purchase_History p ON r.Date = p.Date LEFT JOIN User u on p.UserID = u.UserID WHERE r.Date >= date('now','-2 months') ORDER BY r.Date DESC");
        while ($row = $results->fetchArray()) {
            
            if( $previousDate != "" && $previousDate != $row['Date'] ) {
                if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
            }

            $name = $row['FirstName'] . " " . $row['LastName'];
            
            $backstockQuantityBefore = $row['BackstockQuantityBefore'];
            $backstockQuantityAfter = $row['BackstockQuantity'];
            
            $shelfQuantityBefore = $row['ShelfQuantityBefore'];
            $shelfQuantityAfter = $row['ShelfQuantity'];
            
            $shelfQuantityDelta = ( $shelfQuantityAfter - $shelfQuantityBefore );
            $backstockQuantityDelta = ( $backstockQuantityAfter - $backstockQuantityBefore );
            
            
            if( $shelfQuantityDelta != 0 || $backstockQuantityDelta != 0 ) {
                if( $shelfQuantityDelta == ($backstockQuantityDelta * -1 ) ) {
                    $rowClass = "restock_row";
                }
                
                echo "<tr class='$rowClass'>";
                echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Name'] . "</td>";
                echo "<td style='padding:5px; border:1px #000 solid;'>" . $name . "</td>";
                $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
                echo "<td style='padding:5px; border:1px #000 solid;'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
                
                if( $shelfQuantityDelta > 0) { $shelfQuantityDelta = "+" . $shelfQuantityDelta; }
                if( $backstockQuantityDelta > 0) { $backstockQuantityDelta = "+" . $backstockQuantityDelta; }
                
                if( $shelfQuantityDelta == 0) { $shelfQuantityDelta = ""; }
                if( $backstockQuantityDelta == 0) { $backstockQuantityDelta = ""; }
                
                $shelfQuantityDisplay = "$shelfQuantityBefore --> $shelfQuantityAfter <span style='float:right; font-size:1.5em;'>" . $shelfQuantityDelta . "</span>";
                $backstockQuantityDisplay = "$backstockQuantityBefore --> $backstockQuantityAfter <span style='float:right; font-size:1.5em;'>" . $backstockQuantityDelta . "</span>";
                
                echo "<td style='padding:5px; border:1px #000 solid;'>" . $shelfQuantityDisplay . "</td>";
                echo "<td style='padding:5px; border:1px #000 solid;'>" . $backstockQuantityDisplay . "</td>";
                echo "<td style='padding:5px; border:1px #000 solid;'>$" . number_format( $row['Price'], 2) . "</td>";
                echo "</tr>";
            }
            
            $previousDate = $row['Date'];
        }
        
        echo "</table>";
    echo "</span>";
?>

</body>