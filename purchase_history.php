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

</head>


<?php

	if( $isMobile ) {
		//Some magic that makes the top blue bar fill the width of the phone's screen
		echo "<body class='soda_body' style='display:inline-table;'>";
	} else {
		echo "<body class='soda_body'>";
	}
	
	include("login_bar.php");
	$itemType = $_GET['type'];
	
	if( !$loggedInAdmin ) {
		TrackVisit($db, "PurchaseHistory-" .  $itemType, $loggedIn);
	}
	
	
	
	
	if( $loggedInAdmin && isset($_GET['userid'] ) && isset($_GET['name'] )  ) {
		$userID = $_GET['userid'];
		$name = $_GET['name'];
	} else {
		$userID = $_SESSION['userID'];
		$name = $_SESSION['firstname'];
	}
	
	// ------------------------------------
	// PURCHASE HISTORY TABLE
	// ------------------------------------
	echo "<div class='soda_popout'  style='margin:10px; padding:5px;'><span style='font-size:26px;'>Purchase History for '$name'</span> <span style='font-size:0.8em;'></span></div>";
	echo "<div id='restock_all'>";
	echo "<table style='font-size:12; border-collapse:collapse; margin:10px; width:100%'>";
	echo "<thead><tr>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Cost</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date Purchased</th>";
	
	echo "</tr></thead>";
	
	$results = $db->query("SELECT i.Name, p.Cost, p.Date, p.UserID FROM Purchase_History p JOIN Item i on p.itemID = i.ID WHERE p.UserID = $userID AND i.Type='$itemType' ORDER BY p.Date DESC");
	while ($row = $results->fetchArray()) {
		echo "<tr>";
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Name'] . "</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$" . number_format($row['Cost'], 2).  "</td>";
		$date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $date_object->format('m/d/Y  [h:i:s A]') . "</td>";
		echo "</tr>";
	}
	
		echo "</table>";
	echo "</div>";
?>

</body>