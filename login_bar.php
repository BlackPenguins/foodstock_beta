<?php 
	echo "<div style='color:#FFF; background-color:#46465f;  border-bottom: 3px solid #000; width:100%;  padding: 5px 0px;'>";
	echo "<div id='container_top' style='margin:10px;'>";

	if( $isMobile ) {
		echo "<span style='float:right; padding:0px 5px; display:inline-block'>";
		DisplayLoggedInUser($loggedIn, $loggedInAdmin, $loginPassword, $url);
		echo "</span>";
		echo "<div style='clear: both;'></div>";
	} else {
		if(!$loggedIn) {
			echo "<span style='padding:5px;'><a class='register' href='register.php'>Register for a Discount!<a/></span>";
		} else {
			echo "<a style='text-decoration:none;' href='sodastock.php'><span class='nav_buttons nav_buttons_soda'>Soda Home</span></a>";
			echo "<a style='text-decoration:none;' href='snackstock.php'><span class='nav_buttons nav_buttons_snack'>Snack Home</span></a>";
			echo "<a style='text-decoration:none;' href='requests.php'><span class='nav_buttons nav_buttons_requests'>Requests</span></a>";
			
			if( $loggedInAdmin ) {
				echo "<a style='text-decoration:none;' href='admin_x25.php'><span class='nav_buttons nav_buttons_admin'>Administration</span></a>";
			}
			echo "<span style='margin-left:25px;'>";
			echo "<a href='purchase_history.php?type=Soda'><span class='nav_buttons nav_buttons_soda'>Soda Balance: $" .  number_format($_SESSION['SodaBalance'], 2) . "</span></a>";
			echo "&nbsp;";
			echo "<a href='purchase_history.php?type=Snack'><span class='nav_buttons nav_buttons_snack'>Snack Balance: $" .  number_format($_SESSION['SnackBalance'], 2) . "</span></a>";
			echo "</span>";
			
		}
		
		echo "<span style='float:right;'>";
		DisplayLoggedInUser($loggedIn, $loggedInAdmin, $loginPassword, $url);
		echo "</span>";
	}

	echo "</div>";
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
