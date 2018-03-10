<?php 
	echo "<div style='color:#FFF; background-color:#46465f;  border-bottom: 3px solid #000; width:100%; height:26px; padding: 5px 0px;'>";

	if( $isMobile ) {
		if( $loggedInAdmin ) {
			echo "<table style='width:97%;'>";
			echo "<tr>";
			echo "<td><button onclick='showAddItem();' style='padding:5px; margin:0px 5px; width:100%; height:4em;' id='add_item_button' class='item_button ui-button ui-widget-content ui-corner-all'>Add" . $itemType . "</button></td>";
			echo "<td><button onclick='showEditItem();' style='padding:5px; margin:0px 5px; width:100%; height:4em;' id='edit_item_button' class='item_button ui-button ui-widget-content ui-corner-all'>Edit " . $itemType . "</button></td>";
			echo "<td><button onclick='showRestockItem();' style='padding:5px; margin:0px 5px; width:100%; height:4em;' id='restock_item_button' class='item_button ui-button ui-widget-content ui-corner-all'>Restock " . $itemType . "</button></td>";
			echo "<td><button onclick='showDailyAmount();' style='padding:5px; margin:0px 5px; width:100%; height:4em;' id='daily_amount_button' class='item_button ui-button ui-widget-content ui-corner-all'>Enter Daily Amount</button></td>";
			echo "</tr>";
			echo "</table>";
		}
		echo "<span style='float:right; padding:0px 5px; display:inline-block'>";
		DisplayLoggedInUser($loggedIn, $loggedInAdmin, $loginPassword, $url);
		echo "</span>";
		echo "<div style='clear: both;'></div>";
	} else {
		if( $loggedInAdmin ) {
			echo "<button style='padding:5px; margin:0px 5px;' id='add_item_button' class='item_button ui-button ui-widget-content ui-corner-all'>Add " . $itemType . "</button>";
			echo "<button style='padding:5px; margin:0px 5px;' id='edit_item_button' class='item_button ui-button ui-widget-content ui-corner-all'>Edit " . $itemType . "</button>";
			echo "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
			echo "<button style='padding:5px; margin:0px 5px;' id='restock_item_button' class='item_button ui-button ui-widget-content ui-corner-all'>Restock " . $itemType . "</button>";
			echo "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
			echo "<button style='padding:5px; margin:0px 5px;' id='daily_amount_button' class='item_button ui-button ui-widget-content ui-corner-all'>Enter Daily Amount</button>";
		}
		
		if(!$loggedIn) {
			echo "<span style='height:100%; float:left; margin:5px 10px;'><a class='register' href='register.php'>Register for a Discount!<a/></span>";
		} else {
			echo "Balance: $" .  number_format($_SESSION['balance'], 2);
		}
		
		echo "<span style='float:right;'>";
		DisplayLoggedInUser($loggedIn, $loggedInAdmin, $loginPassword, $url);
		echo "</span>";
	}

	echo "</div>";

	function DisplayLoggedInUser($loggedIn, $loggedInAdmin, $loginPassword, $url) {
		if($loggedIn)
		{	
			echo "Logged in: <b><font color ='#FFFF00'>[" . $_SESSION['username'] . "]" . ( $loggedInAdmin ? " - Administrator" : "" ) . "</font></b>";
			echo "<span style='padding:0px 10px 0px 10px;'>";
			echo "<b><a style='color:white;' href='logout.php'>[Logout]</a></b>";
			echo "</span>";
			echo "</span>";
		}
		else
		{
			if( $loginPassword != false ) {
				echo "<span style='float:right; margin:0px 5px;'>";
				echo "<span>";
				echo "<b><font color ='#FF0000'>Incorrect Password: [".$loginPassword."]</font></b>";
				echo "</span>";
				echo "</span>";
			
			}
				
			//PHP_SELF = this current php page name (take you back to the current page)
			echo "<form style='margin:0px 5px; padding:0px;' action='$url' method='post' accept-charset='UTF-8'>"; 
			echo "Username: ";
			echo "<input type='text' name='login_username' id='username' size='15' maxlength='40' />";
			echo "&nbsp;&nbsp;&nbsp;Password: ";
			echo "<input type='password' name='login_password' id='password' size='15' maxlength='40' />";
			echo "<input style='margin: 0px 10px;' type='submit' id='submit2' name='Submit' value='Login' />";
			echo "</label>";
			echo "</form>";
		}
	}
?>

<script type="text/javascript">
    function showAddItem() {
		$('#add_item').show();
		$('#edit_item').hide();
		$('#restock_item').hide();
		$('#daily_amount').hide();
	}
	
	function showEditItem() {
		$('#add_item').hide();
		$('#edit_item').show();
		$('#restock_item').hide();
		$('#daily_amount').hide();
	}
	
	function showRestockItem() {
		$('#add_item').hide();
		$('#edit_item').hide();
		$('#restock_item').show();
		$('#daily_amount').hide();
	}
	
	function showDailyAmount() {
		$('#add_item').hide();
		$('#edit_item').hide();
		$('#restock_item').hide();
		$('#daily_amount').show();
	}
</script>