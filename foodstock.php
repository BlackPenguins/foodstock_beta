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
  	echo "<title>" . $title . " " . date('Y') . "</title>";
  	echo "<link rel='icon' type='image/png' href='" . $favicon . "' />";
?>




<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script src="jscolor.js"></script>

<script>
	var itemsInCart = [];
	
	function updateCardArea(itemTypeValue, classNameValue, locationValue, isMobileValue, loggedInValue, loggedInAdminValue, itemSearchValue, userIDValue) {
		console.log("Updating Card Area with [" + itemSearchValue + "]...");
		$.post("sodastock_ajax.php", { 
				type:'CardArea',
				itemType:itemTypeValue,
				className:classNameValue,
				location:locationValue,
				isMobile:isMobileValue,
				loggedInAdmin:loggedInAdminValue,
				loggedIn:loggedInValue,
				itemSearch:itemSearchValue,
				userID:userIDValue
			},function(data) {
				$('#card_area').html(data);
		});
	}

	function addItemToCart(itemID) {
		var quantityBefore = parseInt( $('#quantity_holder_' + itemID).html() );
		var maxQuantity = parseInt( $('#shelf_quantity_' + itemID).val() );

		if( quantityBefore == maxQuantity ) {
			// Prevent out of stock quantities
			return;
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

    $( document ).ready( function() {
		<?php 
			echo "loadUserModals('" . $loggedInAdmin . "');\n";
		?>       	
	});
</script>

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
	echo "<body class='" . $className . "_body' style='display:inline-table;'>";
} else {
	echo "<body class='" . $className . "_body'>";
}

include("login_bar.php");

date_default_timezone_set('America/New_York');



if( !$loggedInAdmin ) {
	TrackVisit($db, $title, $loggedIn);
}


include("exec_sql.php");


// ------------------------------------
// FANCY ITEM TABLE
// ------------------------------------
echo "<div style='margin-bottom:20px;'>";

if( !$isMobile ) {
	echo "<span><b><a href='http://penguinore.net/sodastock.php'>Bookmark Us! Tell your friends!</a></b><br><span style='font-size:10px;'>Only the ones at RSA because I'm not selling this anywhere else.</span></span>";
}


echo "</div>";

$user_name = "";
$user_visits = 0;
$user_ip = $_SERVER["REMOTE_ADDR"];

$results = $db->query('SELECT COUNT(*) FROM Visits WHERE IP = "'.$user_ip.'"');
while ($row = $results->fetchArray()) {
        $user_visits = $row[0];
}



$results = $db->query("SELECT Income, Expenses, ProfitExpected, ProfitActual, FirstDay FROM Information WHERE ItemType ='" . $itemType . "'");

//---------------------------------------
// BUILD TOP SECTION STATS
//---------------------------------------
if(!$isMobile) {
	$version = "Version 4.2 (March 28th, 2018)";

	$total_income = 0;
	$total_expense = 0;

	$row = $results->fetchArray();
	$total_income = $row['Income'];
	$total_expense = $row['Expenses'];
	$total_profit = $row['ProfitExpected'];
	$total_income_actual = $row['ProfitActual'];
	$firstDay = $row['FirstDay'];
	

	echo "<div style='margin: auto;'>";
	echo "<div>";
	echo "<span style='color:white; background-color:#00881d; padding:5px; border: #000 2px dashed; margin-right:5px; width:245px; display:inline-block;'>$version</span>";
	if( $loggedInAdmin ) {
		echo "<span style='color:black; background-color:#90EE90; margin-left:5px; padding:5px 15px; border: #000 2px dashed;'><b>Total Income (Expected):</b> $". number_format($total_income, 2)."</span>";
		echo "<span style='color:black; background-color:#EE4545; padding:5px 15px; border: #000 2px dashed;'><b>Total Expenses:</b> $". number_format($total_expense, 2)."</span>";
		echo "<span style='color:black; background-color:#EBEB59; padding:5px 15px; border: #000 2px dashed;'><b>Total Profit (Expected):</b> $". number_format($total_profit, 2)."</span>";
		
	}
	
	$dateNow = new DateTime();
	$firstDay = DateTime::createFromFormat('Y-m-d H:i:s', $row['FirstDay']);
	
	$time_since = $dateNow->diff($firstDay);
	$days_ago = $time_since->format('%a');

	$profitPerDay = $total_profit / $days_ago;
	echo "<span style='color:black; background-color:#FFF; padding:5px 15px; border: #000 2px dashed;'><b>Total Profit / Day:</b> $". number_format($profitPerDay, 2)."</span>";
	echo "<span style='color:black; background-color:#B888FF; padding:5px 15px; border: #000 2px dashed;'><b>Days Active: </b>". $days_ago ." days</span>";
	echo "</div>";
	
	echo "<div style='margin-left:269px; margin-top:12px;'>";
	
	if( $loggedInAdmin ) {
		echo "<span style='color:black; background-color:#ebb159; padding:5px 15px; border: #000 2px dashed;'><b>Total Income (Actual):</b> $". number_format($total_income_actual, 2)."</span>";
		$actualProfit = $total_income_actual - $total_expense;
		echo "<span style='color:black; background-color:#EBEB59; padding:5px 15px; border: #000 2px dashed;'><b>Total Profit (Actual):</b> $". number_format($actualProfit, 2)."</span>";
	}
	echo "</div>";
	
	echo "<div></div>";
}

	echo "<div id='cart_area' style='margin:20px; padding:10px; color:#FFFFFF; background-color:#2f2f2f; border: 3px #8e8b8b dashed;'>";
	echo "Tabs and balances are now online! Remember to pick up your product first and have it physically in your hand before you buy on the website to avoid 'concurrency issues'. Discounted prices are also now in place. Order through the site to get them.";
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

$userID = "";

if( isset( $_SESSION['userID'] ) ) {
	$userID = $_SESSION['userID'];
}

echo "<div style='font-size:1.6em; font-weight:bold; margin:3px;'>Search: <input autofocus type='text' style='font-size:1.6em;' onChange=\"updateCardArea('$itemType', '$className', '$location', '$isMobile', '$loggedIn', '$loggedInAdmin', this.value, '$userID' );\"/></div>";
echo "<div id='card_area'>";
echo "<script>updateCardArea('$itemType', '$className', '$location', '$isMobile', '$loggedIn', '$loggedInAdmin', '', '$userID');</script>";
echo "</div>";

if( !$isMobile) {
	echo "<div style='clear:both;'></div>";


	echo "<div class='" . $className . "_popout' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Change Log</span></div>";
	echo "<ul>";
	echo "<li><b>Mar 28, 2018:</b> Added 'Feature' and 'Bug' request types. Divided Feature, Bug, and Requests into different sections. Ability to mark requests as completed.</li>";
	echo "<li><b>Mar 22, 2018:</b> Added discount prices - shown in the page, the purchase history, and the cart. Show total savings and spent in purchase history. Show total savings across all users in register link. Striped tables (might need better colors). Added password confirmation to register page.</li>";
	echo "<li><b>Mar 11, 2018:</b> Built Admin, Requests, and Purchase History pages. Added Payments. Display the number of times you bought an item in card. Order cards by the most bought (Favorites - Nick Ask). Added Nav Buttons to top bar: Soda Home, Snack Home, Requests, Purchase History, Admin. Sped up home page by removing forms and many unnecessary SQL queries. Added slack notifications for payments, requests, receipts, restocks - with specific emojis and bot names. Split balances into soda balance and snack balance. Cash only option in cart allows you to decrement the quantity without adding total to your balance because you paid in change/cash (Nick Ask). Added ability to submit requests and view others' requests.</li>";
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
}

//include("sodastock_charts.php");
$db->close();
}
?>
</body>