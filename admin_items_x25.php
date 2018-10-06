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
    
    TrackVisit($db, 'Admin - Items Listing');
    

    DisplayUserMessage();
    
    echo "<span style='width:11%; vertical-align:top; display:inline-block; padding: 10px; background-color:#4d544e; border: 0px solid #000;'>";
    
    if( $isLoggedInAdmin ) {
        include "admin_nav_x25.php";
    }
    
    echo "</span>";
    
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        // ------------------------------------
        // ITEM TABLE
        // ------------------------------------
        echo "<span class='soda_popout' style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>Item Inventory</span></span>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-bottom: 20px; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>ID</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Type</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Name</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Price per Unit</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Discount Price per Unit</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date Created</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date Modified</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Chart Color</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Shelf Quantity</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Backstock Quantity</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Total Units Bought</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Total Income</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Total Expenses</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Discontinued</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        
        $results = $db->query("SELECT ID, Type, Name, OutOfStock, Date, DateModified, ModifyType, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, DiscountPrice, TotalIncome, TotalExpenses, Retired, Hidden, (ShelfQuantity + BackstockQuantity) as Total FROM Item WHERE Hidden != 1 ORDER BY Retired, Type DESC, Total ASC");
        while ($row = $results->fetchArray()) {
            $isDiscontinued = $row['Retired'] == 1;
            
            if( $isDiscontinued ) {
                $rowClass = "discontinued_row";
            }
            
            $colorPrice = "";
            $colorDiscount = "";
            
            if( $row['DiscountPrice'] == 0 ) {
                $colorPrice = "background-color: #e7ea14;";
            } else {
                $colorDiscount = "background-color: #e7ea14;";
            }
            
            $colorSoldOut = "";
            if( ( $row['Total'] == 0 || $row['OutOfStock'] == 1 ) && !$isDiscontinued ) {
                $colorSoldOut = "background-color: #ea7714;";
            }

            echo "<tr class='$rowClass'>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['ID'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Type'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid; $colorSoldOut'>" . $row['Name'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid; $colorPrice'>$" . number_format( $row['Price'], 2) . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid; $colorDiscount'>$" . number_format( $row['DiscountPrice'], 2) . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Date'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['DateModified'] . " (" . $row['ModifyType'] . ")</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['ChartColor'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['ShelfQuantity'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['BackstockQuantity'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['TotalCans'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>$" . number_format( $row['TotalIncome'], 2) . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>$" . number_format( $row['TotalExpenses'], 2) . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>". ( $isDiscontinued ? ( "YES" ) : ( "NO" ) ) . "</td>";
            echo "</tr>";
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
        }
        
        echo "</table>";
    echo "</span>";
?>

</body>