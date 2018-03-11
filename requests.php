<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    $db = new SQLite3("db/item.db");
    if (!$db) die ($error);
    
    $url = "sodastock.php";
    
    include("foodstock_functions.php");
    date_default_timezone_set('America/New_York');
        
    Login($db);
            
        
    $loggedIn = IsLoggedIn();
    $loggedInAdmin = IsAdminLoggedIn();
    $loginPassword = false;
    
    require_once 'Mobile_Detect.php';
 
    $detect = new Mobile_Detect;
    $device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
    $isMobile = $device_type == 'phone';

    if(isset($_GET['mobile'])) {
        $isMobile = true;
    }
        
	echo "<title>Requests</title>";
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
				echo "loadUserModals('" . $loggedInAdmin . "');\n";
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
	include("login_bar.php");
	
	if( !$loggedInAdmin ) {
		TrackVisit($db, 'Requests', $loggedIn);
	}
	
		echo "<div style='padding: 10px; background-color:#d03030; border-bottom: 3px solid #000;'>";
		echo "<button style='padding:5px; margin:0px 5px;' id='request_button' class='item_button ui-button ui-widget-content ui-corner-all'>Request Snack/Soda</button>";
		echo "</div>";
		
		// ------------------------------------
		// REQUEST MODAL
		// ------------------------------------
		$itemType_options = "";
		$itemType_options = $itemType_options . "<option value='Soda'>Soda</option>";
		$itemType_options = $itemType_options . "<option value='Snack'>Snack</option>";
		$itemType_dropdown = "<select id='ItemTypeDropdown_Request' name='ItemTypeDropdown_Request' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$itemType_options</select>";
			
		echo "<div id='request' title='Request' style='display:none;'>";
		echo "<form id='request_form' class='fancy' enctype='multipart/form-data' action='requests.php' method='POST'>";
		echo "<fieldset>";
		echo "<label style='padding:5px 0px;' for='ItemTypeDropdown_Request'>Type</label>";
        echo $itemType_dropdown;
        echo "<label style='padding:5px 0px;' for='ItemName_Request'>Item</label>";
        echo "<input type='text' name='ItemName_Request' class='text ui-widget-content ui-corner-all'/>";
        echo "<label style='padding:5px 0px;' for='Note'>Note</label>";
        echo "<input type='text' name='Note_Request' class='text ui-widget-content ui-corner-all'/>";
	
     	echo "<input type='hidden' name='AuthPass517' value='2385'/><br>";
        echo "<input type='hidden' name='Request' value='Request'/><br>";
	
        echo "</fieldset>";
		echo "</form>";
		echo "</div>";
	
	// ------------------------------------
	// REQUESTS TABLE
	// ------------------------------------
	echo "<div class='soda_popout'  style='margin:10px; padding:5px;'><span style='font-size:26px;'>Requests</span> <span style='font-size:0.8em;'></span></div>";
	echo "<div id='users'>";
	echo "<table style='font-size:12; border-collapse:collapse; margin:10px; width:100%'>";
	echo "<thead><tr>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item Name</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item Type</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Requested By</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Note</th>";
	
	echo "</tr></thead>";
	
	$results = $db->query('SELECT u.FirstName, u.LastName, r.ItemName, r.ItemType, r.Date, r.Note  FROM REQUESTS r JOIN User u ON r.UserID = u.UserID ORDER BY r.Date DESC');
	while ($row = $results->fetchArray()) {
		echo "<tr>";
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['ItemName'] . "</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['ItemType'] . "</td>";
		$fullName = $row['FirstName'] . " " . $row['LastName'];
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $fullName . "</td>";
		$date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $date_object->format('m/d/Y  [h:i:s A]') . "</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Note'] . "</td>";
		echo "</tr>";
	}
	
		echo "</table>";
	echo "</div>";
?>

</body>