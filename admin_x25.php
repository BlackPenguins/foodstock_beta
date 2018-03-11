<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    $db = new SQLite3("db/item.db");
    if (!$db) die ($error);
        
    include("foodstock_functions.php");
    date_default_timezone_set('America/New_York');
        
    Login($db);
            
        
    $loggedIn = IsLoggedIn();
    $loggedInAdmin = IsAdminLoggedIn();
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
			if(!$isMobile) {
				echo "loadSingleModals('" . $loggedInAdmin . "');\n";
				echo "loadItemModals('" . $loggedInAdmin . "','Soda');\n";
				echo "loadItemModals('" . $loggedInAdmin . "','Snack');\n";
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
	
	include("handle_forms.php");
	include("build_forms.php");
	include("login_bar.php");
	
	if( !$loggedInAdmin ) {
		TrackVisit($db, 'Admin', $loggedIn);
		die;
	}
	
	if( $loggedInAdmin ) {
		echo "<div style='padding: 10px; background-color:#d03030; border-bottom: 3px solid #000;'>";
		
		echo "<table>";
		echo "<tr>";
		
		echo "<td style='width:33%;'>";
		echo "<button style='padding:5px; margin:0px 5px;' id='add_item_Soda_button' class='item_button ui-button ui-widget-content ui-corner-all'>Add Soda</button>";
		echo "<button style='padding:5px; margin:0px 5px;' id='edit_item_Soda_button' class='item_button ui-button ui-widget-content ui-corner-all'>Edit Soda</button>";
		echo "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
		echo "<button style='padding:5px; margin:0px 5px;' id='restock_item_Soda_button' class='item_button ui-button ui-widget-content ui-corner-all'>Restock Soda</button>";
		echo "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
		echo "<button style='padding:5px; margin:0px 5px;' id='inventory_Soda_button' class='item_button ui-button ui-widget-content ui-corner-all'>Enter Inventory Soda</button>";
		echo "</td>";
		
		echo "<td style='width:33%; text-align:center;'>";
		echo "<button style='padding:5px; margin:0px 5px;' id='payment_button' class='item_button ui-button ui-widget-content ui-corner-all'>Add Payment</button>";
		echo "</td>";
		
		echo "<td style='width:33%; text-align:right;'>";
		echo "<button style='padding:5px; margin:0px 5px;' id='add_item_Snack_button' class='item_button ui-button ui-widget-content ui-corner-all'>Add Snack</button>";
		echo "<button style='padding:5px; margin:0px 5px;' id='edit_item_Snack_button' class='item_button ui-button ui-widget-content ui-corner-all'>Edit Snack</button>";
		echo "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
		echo "<button style='padding:5px; margin:0px 5px;' id='restock_item_Snack_button' class='item_button ui-button ui-widget-content ui-corner-all'>Restock Snack</button>";
		echo "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
		echo "<button style='padding:5px; margin:0px 5px;' id='inventory_Snack_button' class='item_button ui-button ui-widget-content ui-corner-all'>Enter Inventory Snack</button>";
		echo "</td>";
		
		echo "</tr>";
		echo "</table>";
		echo "</div>";
	}
	
	// ------------------------------------
	// USER TABLE
	// ------------------------------------
	echo "<div class='soda_popout'  style='margin:10px; padding:5px;'><span style='font-size:26px;'>Users</span> <span style='font-size:0.8em;'></span></div>";
	echo "<div id='users'>";
	echo "<table style='font-size:12; border-collapse:collapse; margin:10px; width:100%'>";
	echo "<thead><tr>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>UserName</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Name</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Phone Number</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date Created</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Soda Balance</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Snack Balance</th>";
	
	echo "</tr></thead>";
	
	$results = $db->query('SELECT u.UserID, u.UserName, u.FirstName, u.LastName, u.PhoneNumber, u.SodaBalance, u.SnackBalance, u.DateCreated FROM User u ORDER BY u.DateCreated DESC');
	while ($row = $results->fetchArray()) {
		echo "<tr>";
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['UserName'] . "</td>";
		$fullName = $row['FirstName'] . " " . $row['LastName'];
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $fullName . "</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['PhoneNumber'] . "</td>";
		$date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['DateCreated']);
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $date_object->format('m/d/Y  [h:i:s A]') . "</td>";
		$purchaseHistorySodaURL = "<a href='purchase_history.php?type=Soda&name=" . $fullName . "&userid=" . $row['UserID'] . "'>$" . number_format($row['SodaBalance'], 2) . "</a>";
		$purchaseHistorySnackURL = "<a href='purchase_history.php?type=Snack&name=" . $fullName . "&userid=" . $row['UserID'] . "'>$" . number_format($row['SnackBalance'], 2) . "</a>";
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $purchaseHistorySodaURL .  "</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $purchaseHistorySnackURL .  "</td>";
		echo "</tr>";
	}
	
		echo "</table>";
	echo "</div>";
	
	// ------------------------------------
	// ITEM TABLE
	// ------------------------------------
	echo "<div class='soda_popout' onclick='$(\"#item_all\").toggle();' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Item Inventory</span> <span style='font-size:0.8em;'>(show/hide)</span></div>";
	echo "<div id='item_all' style='display:none;'>";
	echo "<table style='font-size:12; border-collapse:collapse; margin:10px; width:100%'>";
	echo "<thead><tr>";
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
	
	$results = $db->query("SELECT ID, Name, Date, DateModified, ModifyType, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, Retired FROM Item WHERE Type ='" . $itemType . "' ORDER BY Retired, ID DESC");
	while ($row = $results->fetchArray()) {
		echo "<tr>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[0]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[1]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[2]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[3] ($row[4])</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[5]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[6]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[7]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[8]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[9]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[10]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[11]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>".(($row[12]==1)?("YES"):("NO"))."</td>";
		echo "</tr>";
	}
	
	echo "</table>";
	echo "</div>";
	
	// ------------------------------------
	// RESTOCK TABLE
	// ------------------------------------
	echo "<div class='soda_popout' onclick='$(\"#restock_all\").toggle();' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Restock Schedule</span> <span style='font-size:0.8em;'>(show/hide)</span></div>";
	echo "<div id='restock_all' style='display:none;'>";
	echo "<table style='font-size:12; border-collapse:collapse; margin:10px; width:100%'>";
	echo "<thead><tr>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Number of Units</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Cost</th>";
	
	echo "</tr></thead>";
	
	$results = $db->query('SELECT s.Name, r.ItemID, r.Date, r.NumberOfCans, r.Cost FROM Restock r JOIN Item s ON r.itemID = s.id  ORDER BY r.Date DESC');
	while ($row = $results->fetchArray()) {
		echo "<tr>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[0]</td>";
		$date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row[2]);
		echo "<td style='padding:5px; border:1px #000 solid;'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[3]</td>";
			echo "<td style='padding:5px; border:1px #000 solid;'>$$row[4]</td>";
			echo "</tr>";
	}
	
	echo "</table>";
	echo "</div>";
	
	
	// ------------------------------------
	// INVENTORY TABLE
	// ------------------------------------
	echo "<div class='soda_popout' onclick='$(\"#daily_count_all\").toggle();' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Daily Count</span> <span style='font-size:0.8em;'>(show/hide)</span></div>";
	echo "<div id='daily_count_all' style='display:none;'>";
	echo "<table style='font-size:12; border-collapse:collapse; margin:10px; width:100%'>";
	echo "<thead><tr>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Shelf Quantity</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Backstock Quantity</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Price</th>";
	
	echo "</tr></thead>";
	
	$dailyData = array();
	
	$results = $db->query('SELECT s.Name, r.Date, r.BackstockQuantityBefore, r.BackstockQuantity, r.Price FROM Daily_Amount r JOIN Item s ON r.itemID = s.id  ORDER BY r.Date DESC');
	while ($row = $results->fetchArray()) {
		echo "<tr>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[0]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[1]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[3]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[2]</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$row[4]</td>";
		echo "</tr>";
	}
	
	echo "</table>";
	echo "</div>";
	
	// ------------------------------------
		// VISITS TABLE
	// ------------------------------------
	echo "<div class='soda_popout' onclick='$(\"#visits_all\").toggle();' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Page Visits</span> <span style='font-size:0.8em;'>(show/hide)</span></div>";
		echo "<div id='visits_all' style='display:none;'>";
			echo "<table style='font-size:12; border-collapse:collapse; margin:10px; width:100%'>";
		echo "<thead><tr>";
		echo "<th style='padding:5px; border:1px #000 solid;' align='left'>IP</th>";
		echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
		echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Agent</th>";
		echo "</tr></thead>";
	
		$self_count = 0;
	$self_date = "";
			$self_agent = "";

			$results = $db->query('SELECT IP, Date, Agent FROM Visits ORDER BY Date DESC');
		while ($row = $results->fetchArray()) {
		
			$ip = $row[0];
			if( strpos($row[0], '|') !== false ) {
			$ip_pieces = explode("|", $ip);
			$ip = trim($ip_pieces[0]);
			}


			$ip = GetNameByIP($ip);

			if($self_count != 0 && $ip != "<span style='color:red;'>Matt Miles</span>") {
			echo "<tr>";
			echo "<td style='padding:5px; border:1px #000 solid; font-weight:bold;'><span style='color:red;'>Matt Miles</span> (" . $self_count . " times)</td>";
					$date_object = DateTime::createFromFormat('Y-m-d H:i:s', $self_date);
			echo "<td style='padding:5px; border:1px #000 solid;'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";

					echo "<td style='padding:5px; border:1px #000 solid;'>$self_agent</td>";
			echo "</tr>";
			$self_count = 0;
			}

			if($ip == "<span style='color:red;'>Matt Miles</span>") {
			$self_count++;
			$self_date = $row[1];
					$self_agent = $row[2];
				} else {


					echo "<tr>";
					echo "<td style='padding:5px; border:1px #000 solid; font-weight:bold;'>$ip</td>";
							$date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row[1]);
							echo "<td style='padding:5px; border:1px #000 solid;'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";

					echo "<td style='padding:5px; border:1px #000 solid;'>$row[2]</td>";
								echo "</tr>";
					}
						

						
					}
						
					echo "</table>";
					echo "</div>";
?>

</body>