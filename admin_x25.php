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
<script src="jscolor.js"></script>

<?php
    if( !$isMobile) {
        echo "<script src='load_modals.js'></script>";
    }
?>

<link rel="stylesheet" type="text/css" href="colorPicker.css"/>
<link rel="stylesheet" type="text/css" href="style.css"/>
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
        echo "<div style='margin-bottom:20px;'>";
        
            echo "<div id='add_item_Soda_button' class='nav_buttons nav_buttons_soda'>Add Soda</div>";
            echo "<div id='edit_item_Soda_button' class='nav_buttons nav_buttons_soda'>Edit Soda</div>";
            echo "<div id='restock_item_Soda_button' class='nav_buttons nav_buttons_soda'>Restock Soda</div>";
            echo "<div id='inventory_Soda_button' class='nav_buttons nav_buttons_soda'>Inventory Soda</div>";
            
        echo "</div>";
        
        echo "<div style='margin-bottom:20px;'>";
        
            echo "<div id='add_item_Snack_button' class='nav_buttons nav_buttons_snack'>Add Snack</div>";
            echo "<div id='edit_item_Snack_button' class='nav_buttons nav_buttons_snack'>Edit Snack</div>";
            echo "<div id='restock_item_Snack_button' class='nav_buttons nav_buttons_snack'>Restock Snack</div>";
            echo "<div id='inventory_Snack_button' class='nav_buttons nav_buttons_snack'>Inventory Snack</div>";
            
        echo "</div>";
        
        echo "<div style='margin-bottom:20px;'>";
        
            echo "<div id='payment_button' class='nav_buttons nav_buttons_billing'>Add Payment</div>";
            echo "<div id='edit_user_button' class='nav_buttons nav_buttons_admin'>Edit User</div>";
        
        echo "</div>";
        
    }
    
    echo "</span>";
    
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        // ------------------------------------
        // USER TABLE
        // ------------------------------------
        echo "<span class='soda_popout' style='display:inline-block; width:100%; margin-left: 10px; padding:5px;'><span style='font-size:26px;'>Users</span> <span style='font-size:0.8em;'></span></span>";
        echo "<span id='users'>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Name</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>UserName</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Slack ID</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Phone Number</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date Created</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Soda Balance</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Snack Balance</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>TOTAL</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        
        $results = $db->query('SELECT u.UserID, u.UserName, u.SlackID, u.FirstName, u.LastName, u.PhoneNumber, u.SodaBalance, u.SnackBalance, u.DateCreated, u.InActive FROM User u ORDER BY u.Inactive asc, lower(u.FirstName) ASC');
        while ($row = $results->fetchArray()) {
            if( $row['Inactive'] == 1 ) {
                $rowClass = "discontinued_row";
            }
            
            echo "<tr class='$rowClass'>";
            $fullName = $row['FirstName'] . " " . $row['LastName'];
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $fullName . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['UserName'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['SlackID'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['PhoneNumber'] . "</td>";
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['DateCreated']);
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $date_object->format('m/d/Y  [h:i:s A]') . "</td>";
            $sodaBalance = number_format($row['SodaBalance'], 2);
            $snackBalance = number_format($row['SnackBalance'], 2);
            $totalBalance =  $sodaBalance + $snackBalance;
            
            $purchaseHistorySodaURL = "<a href='purchase_history.php?type=Soda&name=" . $fullName . "&userid=" . $row['UserID'] . "'>$" . $sodaBalance . "</a>";
            $purchaseHistorySnackURL = "<a href='purchase_history.php?type=Snack&name=" . $fullName . "&userid=" . $row['UserID'] . "'>$" . $snackBalance . "</a>";
            $billingSodaURL = "<a href='billing.php?type=Soda&name=" . $fullName . "&userid=" . $row['UserID'] . "'>Billing</a>";
            $billingSnackURL = "<a href='billing.php?type=Snack&name=" . $fullName . "&userid=" . $row['UserID'] . "'>Billing</a>";
            $sodaBalanceColor = "";
            $snackBalanceColor = "";
            $totalBalanceColor = "";
            
            if( $snackBalance > 0 ) {
                $snackBalanceColor = "background-color:#fdff7a;";
                $totalBalanceColor = "background-color:#fdff7a;";
            }
            
            if( $sodaBalance > 0 ) {
                $sodaBalanceColor = "background-color:#fdff7a;";
                $totalBalanceColor = "background-color:#fdff7a;";
            }
            
            echo "<td style='padding:5px; $sodaBalanceColor border:1px #000 solid;'>" . $purchaseHistorySodaURL . " (" . $billingSodaURL . ")</td>";
            echo "<td style='padding:5px; $snackBalanceColor border:1px #000 solid;'>" . $purchaseHistorySnackURL . " (" . $billingSnackURL . ")</td>";
            echo "<td style='padding:5px; $totalBalanceColor border:1px #000 solid;'>$" . number_format($totalBalance,2) . "</td>";
            echo "</tr>";
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
        }
        
            echo "</table>";
        echo "</span>";
        
        // ------------------------------------
        // ITEM TABLE
        // ------------------------------------
        echo "<span class='soda_popout' onclick='$(\"#item_all\").toggle();' style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>Item Inventory</span> <span style='font-size:0.8em;'>(show/hide)</span></span>";
        echo "<span id='item_all' style='display:none;'>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>ID</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Name</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date Modified</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Chart Color</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Total Cans</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Backstock Quantity</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Shelf Quantity</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Price per Can</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Total Income</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Total Expenses</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Discontinued</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        
        $results = $db->query("SELECT ID, Name, Date, DateModified, ModifyType, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, Retired, Hidden FROM Item WHERE Hidden != 1 ORDER BY Retired, Type DESC, ID DESC");
        while ($row = $results->fetchArray()) {
            $isDiscontinued = $row['Retired'] == 1;
            
            if( $isDiscontinued ) {
                $rowClass = "discontinued_row";
            }
            
            echo "<tr class='$rowClass'>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['ID'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Name'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Date'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['DateModified'] . " (" . $row['ModifyType'] . ")</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['ChartColor'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['TotalCans'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['BackstockQuantity'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['ShelfQuantity'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Price'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['TotalIncome'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['TotalExpenses'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>". ( $isDiscontinued ? ( "YES" ) : ( "NO" ) ) . "</td>";
            echo "</tr>";
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
        }
        
        echo "</table>";
        echo "</span>";
        
        // ------------------------------------
        // RESTOCK TABLE
        // ------------------------------------
        echo "<span class='soda_popout' onclick='$(\"#restock_all\").toggle();' style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>Restock Schedule</span> <span style='font-size:0.8em;'>(show/hide)</span></span>";
        echo "<span id='restock_all' style='display:none;'>";
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
        
        $results = $db->query("SELECT s.Name, r.ItemID, r.Date, r.NumberOfCans, r.Cost, (r.Cost/r.NumberOfCans) as 'CostEach', s.Price, s.DiscountPrice, s.Retired FROM Restock r JOIN Item s ON r.itemID = s.id  ORDER BY s.Type DESC, s.Retired ASC, s.Name, CostEach DESC, r.Date DESC");
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
        
    echo "</span>";
?>

</body>