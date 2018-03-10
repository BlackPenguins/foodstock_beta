<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
function main( $url, $title, $favicon, $itemType, $className, $location ) {
		$db = new SQLite3("db/item.db");
		if (!$db) die ($error);
		
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
		
		
		
//         //Input variables
//         if(isset($_POST['login_password']) && !$loggedIn) 
//         {
//         	$loginPassword = trim($_POST["login_password"]);
//         	// Log them in?
//         	if($loginPassword == "2385")
//            	{
//        			$_SESSION[GetSessionKey()] = "admin";
//           		$loggedIn = true;
//       		} else {
//             	$incorrectPassword = true;
//          	}
//         }
        
  	echo "<title>" . $title . " " . date('Y') . "</title>";
  	echo "<link rel='icon' type='image/png' href='" . $favicon . "' />";
?>




<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script src="jscolor.js"></script>

<script>
	var itemsInCart = [];
	
	function updateCardArea(itemTypeValue, classNameValue, locationValue, isMobileValue, loggedInValue, loggedInAdminValue, itemSearchValue) {
		console.log("Updating Card Area with [" + itemSearchValue + "]...");
		$.post("sodastock_ajax.php", { 
				type:'CardArea',
				itemType:itemTypeValue,
				className:classNameValue,
				location:locationValue,
				isMobile:isMobileValue,
				loggedInAdmin:loggedInAdminValue,
				loggedIn:loggedInValue,
				itemSearch:itemSearchValue
			},function(data) {
				$('#card_area').html(data);
		});
	}

	function addItemToCart(itemID) {
		var quantityBefore = parseInt( $('#quantity_holder_' + itemID).html() );
		var maxQuantity = parseInt( $('#shelf_quantity_' + itemID).val() );

		if( quantityBefore == maxQuantity ) {
			// Prevent out of stock quantities
			//return;
		}
		
		itemsInCart.push(itemID);
		console.log("Items in Cart: [" + itemsInCart + "]" );

		
		var newQuantity = quantityBefore + 1;
		
		$('#quantity_holder_' + itemID).html( newQuantity );
		

		if( newQuantity == 1 ) {
			$('#remove_button_' + itemID).removeClass('quantity_button_remove_disabled');
			$('#remove_button_' + itemID).addClass('quantity_button_remove');
		}

		if( newQuantity == maxQuantity ) {
			$('#add_button_' + itemID).addClass('quantity_button_add_disabled');
			$('#add_button_' + itemID).removeClass('quantity_button_add');
		}
		
		$.post("sodastock_ajax.php", { 
				type:'DrawCart',
				items:JSON.stringify(itemsInCart),
				url:'<?php  echo $url; ?>'
			},function(data) {
				$('#cart_area').html(data);
		});
	}

	function removeItemFromCart(itemID) {
		var quantityBefore = parseInt( $('#quantity_holder_' + itemID).html() );

		if( quantityBefore == 0 ) {
			// Prevent negative quantities
			return;
		}
		var index = itemsInCart.indexOf(itemID);

		if (index > -1) {
			itemsInCart.splice(index, 1);
		}
		
		console.log("Items in Cart: [" + itemsInCart + "]" );

		var newQuantity = quantityBefore - 1;
		var maxQuantity = parseInt( $('#shelf_quantity_' + itemID).val() );
		
		$('#quantity_holder_' + itemID).html( newQuantity );

		if( newQuantity == 0 ) {
			$('#remove_button_' + itemID).addClass('quantity_button_remove_disabled');
			$('#remove_button_' + itemID).removeClass('quantity_button_remove');
		}

		if( newQuantity == maxQuantity - 1 ) {
			$('#add_button_' + itemID).removeClass('quantity_button_add_disabled');
			$('#add_button_' + itemID).addClass('quantity_button_add');
		}
		
		$.post("sodastock_ajax.php", { 
				type:'DrawCart',
				items:JSON.stringify(itemsInCart),
				url:'<?php  echo $url; ?>'
			},function(data) {
				$('#cart_area').html(data);
		});
	}
</script>

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
				echo "loadModals('" . $loggedInAdmin . "','" . $itemType . "');\n";
			}
		?>       	
	});
</script>
</head>


<?php

if( $isMobile ) {
	//Some magic that makes the top blue bar fill the width of the phone's screen
	echo "<body class='" . $className . "_body' style='display:inline-table;'>";
} else {
	echo "<body class='" . $className . "_body'>";
}

include("handle_forms.php");
include("login_bar.php");

date_default_timezone_set('America/New_York');

$statement = "";

/*
$statement = "UPDATE Item SET TotalExpenses = TotalExpenses - 3.49, BackstockQuantity = BackstockQuantity - 12, TotalCans = TotalCans - 12 where ID = 29";

$statement = "UPDATE ITEM SET Price = 0.40 WHERE name = 'Swiss Miss Dark Chocolate';";
$statement = "ALTER TABLE ITEM ADD COLUMN ImageURL TEXT;"
$statement = "ALTER TABLE ITEM ADD COLUMN ThumbURL TEXT;"
$statement = "ALTER TABLE ITEM ADD COLUMN UnitName TEXT;"
$statement = "UPDATE ITEM SET Retired = 1 WHERE name = 'Cherry Zero';";

$statement = "CREATE TABLE Item(id integer PRIMARY KEY AUTOINCREMENT, name text, date text, chartcolor text, totalcans integer, backstockquantity integer, shelfquantity integer, price real,  totalincome real, totalexpenses real )";
$statement = "CREATE TABLE Restock(itemid integer, date text, numberofcans integer, cost real )";
$statement = "CREATE TABLE Daily_Amount(itemid integer, date text, backstockquantitybefore integer, backstockquantity integer, shelfquantitybefore integer, shelfquantity integer, price real, restock integer )";
$statement = "DROP TABLE Item;";
*/

if( $statement != "" ) {
        echo "Executing......<br>";
        
        $db->exec($statement);
        
        echo "DONE!<br><br>";
}

if( !$loggedInAdmin ) {
	TrackVisit($db, $title, $loggedIn);
}

include("build_forms.php");

// ------------------------------------
// FANCY ITEM TABLE
// ------------------------------------
echo "<div style='margin-bottom:20px;'>";

if( !$isMobile ) {
	echo "<span><b><a href='http://penguinore.net/sodastock_home/" . $url . "'>Bookmark Us! Tell your friends!</a></b><br><span style='font-size:10px;'>Only the ones at RSA because I'm not selling this anywhere else.</span></span>";
}

$otherURL = "sodastock.php";
$otherTitle = "SodaStock";

if( $title == "SodaStock" ) {
	$otherURL = "snackstock.php";
	$otherTitle = "SnackStock";
}

echo "<span style='float:right; padding:10px;'><b><a href='$otherURL'>Go to $otherTitle >>></a></b></span>";
echo "</div>";

$user_name = "";
$user_visits = 0;
$user_ip = $_SERVER["REMOTE_ADDR"];

$results = $db->query('SELECT COUNT(*) FROM Visits WHERE IP = "'.$user_ip.'"');
while ($row = $results->fetchArray()) {
        $user_visits = $row[0];
}



$results = $db->query("SELECT ID, Name, Date, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, TotalIncome, TotalExpenses, DateModified, ModifyType, Retired FROM Item WHERE Type ='" . $itemType . "' ORDER BY Retired, BackstockQuantity DESC, ShelfQuantity DESC");

//---------------------------------------
// BUILD TOP SECTION STATS
//---------------------------------------
if(!$isMobile) {
	$version = "Version 3.1 (Mar 2nd, 2018)";

	$total_income = 0;
	$total_expense = 0;

	while ($row = $results->fetchArray()) {
		$total_income = $total_income +(($row[4] - ($row[5] + $row[6]) ) * $row[7] );
		$total_expense = $total_expense + $row[9];
	}

	// For the lost $3 on the Diet Mountain Dew
// 	$total_expense = $total_expense + 3;

	$total_profit = $total_income - $total_expense;

	echo "<div style='margin: auto;'>";
	echo "<span style='color:white; background-color:#800; padding:5px; border: #000 2px dashed; margin-right:5px;'>$version</span>";
	if( $loggedInAdmin ) {
		echo "<span style='color:black; background-color:#90EE90; margin-left:5px; padding:5px 15px; border: #000 2px dashed;'><b>Total Income:</b> $". number_format($total_income, 2)."</span>";
		echo "<span style='color:black; background-color:#EE4545; padding:5px 15px; border: #000 2px dashed;'><b>Total Expenses:</b> $". number_format($total_expense, 2)."</span>";
		echo "<span style='color:black; background-color:#EBEB59; padding:5px 15px; border: #000 2px dashed;'><b>Total Profit:</b> $". number_format($total_profit, 2)."</span>";
	}
	$dateNow = new DateTime();
	$firstDay = DateTime::createFromFormat('Y-m-d H:i:s', "1989-07-09 00:00:00");
	
	if( $title == "SodaStock" ) {
		$firstDay = DateTime::createFromFormat('Y-m-d H:i:s', "2014-11-11 00:00:00");
	} else if( $title == "SnackStock" ) {
		$firstDay = DateTime::createFromFormat('Y-m-d H:i:s', "2018-02-19 00:00:00");
	}
	
	$time_since = $dateNow->diff($firstDay);
	$days_ago = $time_since->format('%a');

	$profitPerDay = $total_profit / $days_ago;
	echo "<span style='color:black; background-color:#FFF; padding:5px 15px; border: #000 2px dashed;'><b>Total Profit / Day:</b> $". number_format($profitPerDay, 2)."</span>";
	echo "<span style='color:black; background-color:#B888FF; padding:5px 15px; border: #000 2px dashed;'><b>Days Active: </b>". $days_ago ." days</span>";
	echo "<div></div>";
}

	echo "<div id='cart_area' style='margin:20px; padding:10px; color:#FFFFFF; background-color:#2f2f2f; border: 3px #8e8b8b dashed;'>";
	echo "Tabs and balances are now online! Remember to pick up your product first and have it physically in your hand before you buy on the website to avoid 'concurrency issues'. There will soon be discounted prices for registered users once I figure out what prices I can set.";
	echo "</div>";
	
if( !$isMobile && $itemType != "Snack" ) {
	$results = $db->query("SELECT ID, Name, ShelfQuantity, DateModified, ThumbURL FROM Item WHERE Type ='" . $itemType . "' ORDER BY DateModified DESC");
	
	echo "<div style='margin:20px; padding:10px; background-color:#2f2f2f; border: 3px #8e8b8b dashed;'>";
	echo "<div style='color:#8e8b8b; font-weight:bold; padding-bottom:10px;'>The Shelf <span style='font-size:0.7em;'>(currently in the $location)</span></div>";
	$lastUpdated = "";
	while ($row = $results->fetchArray()) {
		$name = $row[1];
		$shelf = $row[2];
		
		if( $lastUpdated == "") {
			$lastUpdated = $row[3];
		}
		
		if( $shelf > 0 ) {
			for($i = 0; $i < $shelf; $i++) {
				DisplayShelfCan($name, $row[4]);
			}
		}
	}


	$current_date = new DateTime();
	$ago_text = DisplayAgoTime($lastUpdated, $current_date);
			
	echo "<div style='color:#8e8b8b; padding-top:10px;'><b>Last Updated:</b> $ago_text</div>";
	echo "</div>";
}

echo "Item Search: <input type='text' style='font-size:2em;' onChange=\"updateCardArea('$itemType', '$className', '$location', '$isMobile', '$loggedIn', '$loggedInAdmin', this.value );\"/>";
echo "<div id='card_area'>";
echo "<script>updateCardArea('$itemType', '$className', '$location', '$isMobile', '$loggedIn', '$loggedInAdmin', '');</script>";
echo "</div>";

if( !$isMobile) {
	echo "<div style='clear:both;'></div>";


	echo "<div class='" . $className . "_popout' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Change Log</span></div>";
	echo "<ul>";
	echo "<li><b>Mar 3, 2018:</b> Site was moved to Vultr. Added missing snack and soda images.</li>";
	echo "<li><b>Mar 2, 2018:</b> Tabs and balances are now online. Items can be purchased through the site. Card UI was improved a little.</li>";
	echo "<li><b>Feb 16, 2018:</b> Created SnackStock. Storing images and unit names in DB.</li>";
	echo "<li><b>Jan 22, 2017:</b> Bunch of changes. TBA.</li>";
	echo "<li><b>Nov 10, 2016:</b> Lower opacity for sodas that are sold out. Added red text that says sold out. Added 'container type' labels (bottles/cans/packets).</li>";
	echo "<li><b>Jul 1, 2016:</b> Created the card layout. Old table layout can be found <a href='sodastock_table.php'>here</a>.</li>";
	echo "<li><b>Jun 7, 2016:</b> Added 'days active' statistic.</li>";
	echo "<li><b>Jun 5, 2016:</b> Added 'Email' button to email inventory counts.</li>";
	echo "<li><b>Oct 28, 2015:</b> Added 'profit per day' statistic.</li>";
	echo "<li><b>Oct 2, 2015:</b> Added change to cursor when hovering over cells that has hover text.</li>";
	echo "<li><b>Oct 1, 2015:</b> Hid sold-out soda in 'Daily Amount' modal. Added show/hide toggle sections.</li>";
	echo "<li><b>Aug 28, 2015:</b> Re-ordered sodas by stock quantity. Sold out sodas at the end.</li>";
	echo "<li><b>Jul 22, 2015:</b> Removed tiny warm/cold can icons. Added 'Last Store Purchase' & 'Avg Store Purchase'.</li>";
	echo "<li><b>Jul 10, 2015:</b> Added discontinued sodas.</li>";
	echo "<li><b>Feb 16, 2015:</b> SodaStock&trade; goes live. Legacy SodaStock is <a href='https://docs.google.com/spreadsheets/d/16BSupau6vEIfGY_-mgvz0_dzTeiJPysl3Kt-80fr8Hc/edit?usp=sharing'>here</a>.</li>";
	echo "<li><b>Nov 11, 2014:</b> Started selling soda at RSA.</li>";
	echo "</ul>";

	// ------------------------------------
	// ITEM TABLE
	// ------------------------------------
	echo "<div class='" . $className . "_popout' onclick='$(\"#item_all\").toggle();' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Item Inventory</span> <span style='font-size:0.8em;'>(show/hide)</span></div>";
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
	echo "<div class='" . $className . "_popout' onclick='$(\"#restock_all\").toggle();' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Restock Schedule</span> <span style='font-size:0.8em;'>(show/hide)</span></div>";
	echo "<div id='restock_all' style='display:none;'>";
	echo "<table style='font-size:12; border-collapse:collapse; margin:10px; width:100%'>";
	echo "<thead><tr>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
	echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Number of Cans</th>";
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
	// DAILY AMOUNT TABLE
	// ------------------------------------
	echo "<div class='" . $className . "_popout' onclick='$(\"#daily_count_all\").toggle();' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Daily Count</span> <span style='font-size:0.8em;'>(show/hide)</span></div>";
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

		//if($dailyData[$row[1]]) {}
	}

	echo "</table>";
	echo "</div>";

	if( $loggedInAdmin ) {
			// ------------------------------------
			// VISITS TABLE
			// ------------------------------------
			echo "<div class='" . $className . "_popout' onclick='$(\"#visits_all\").toggle();' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Page Visits</span> <span style='font-size:0.8em;'>(show/hide)</span></div>";
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
	}
}

//include("sodastock_charts.php");
$db->close();
}
?>
</body>