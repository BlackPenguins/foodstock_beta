<?php

function sendSlackMessageToMatt( $slackMessage, $emoji, $botName ) {
	sendSlackMessagePOST( "@mmiles", $emoji, $botName, $slackMessage );
}

function sendSlackMessageToUser( $slackID, $slackMessage, $emoji, $botName ) {
	sendSlackMessagePOST( "@" . $slackID, $emoji, $botName, $slackMessage );
}

function sendSlackMessageToRandom( $slackMessage, $emoji, $botName ) {
	sendSlackMessagePOST( "#random", $emoji, $botName, $slackMessage );
}

function sendSlackMessagePOST( $slackID, $emoji, $botName, $slackMessage ) {
	error_log("Sending Slack Message:\nSlack ID: [" . $slackID . "]\nEmoji: [" . $emoji . "]\nBot Name: [" . $botName . "]\nMessage: [" . $slackMessage . "]" );
	$params = array( "channel" => $slackID, "icon_emoji" => $emoji , "username" => $botName, "text" => $slackMessage);

	$url = 'https://hooks.slack.com/services/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';
	
	$fields = array(
		'payload' => json_encode($params)
	);
	
	// build the urlencoded data
	$postvars = http_build_query($fields);
	
	// open connection
	$ch = curl_init();
	
	// set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	// execute post
	$result = curl_exec($ch);
	
	if( $result != "ok" ) {
		error_log("There was an error connecting to slack!! [" . $result . "]" );
	}
	// close connection
	curl_close($ch);
}
function GetNameByIP($ip)
{
    if($ip == "192.9.200.54") {
            return "<span style='color:red;'>Matt Miles</span>";
    } else if($ip == "192.9.200.208") {
           return "<span style='color:red;'>Matt Miles (VM)</span>";
    } else if($ip == "192.9.200.37") {
            return "WebCRD_Coop1 (DJ)";
    } else if($ip == "192.9.200.95") {
            return "WebCRD_Coop3 (Angel)";
    } else if($ip == "192.9.200.193") {
            return "QA_Coop4 (John)";
    } else if($ip == "192.9.200.185") {
            return "QA_Coop1 (Luke)";
    } else if($ip == "192.9.200.166") {
            return "Tom W";
    } else if($ip == "192.9.200.186") {
            return "Chris M";
    } else if($ip == "192.9.200.171") {
            return "Joe S";
    } else if($ip == "192.9.200.31") {
            return "QA_Coop2 (Nich)";
    } else if($ip == "192.9.200.49") {
            return "QA_Coop1";
    } else if($ip == "192.9.200.67") {
            return "Emuel";
    } else if($ip == "192.9.200.229") {
            return "Lindsay";     
    } else if($ip == "192.9.200.47") {
            return "Mike";     
    } else if($ip == "192.9.200.109") {
            return "Darrick";     
    } else if($ip == "192.9.200.198") {
            return "John T";  
    } else { 
    		return $ip; 
    }
}

function DisplayPaymentMethods() {
	echo "<div style='margin:10px; padding:5px;'>";
	echo "<span style='float:left; '><span style='vertical-align:top; font-weight:bold;'>Supported Payment Methods:</span> <img style='width:34px; margin-right:5px;' title='Square Cash App' src='square_cash.png'/><img style='width:35px; margin-right:5px;' title='Venmo App' src='venmo.png'/><img style='width:37px; margin-right:5px;' title=\"Seriously needed a hover-text for this?  It's PayPal.\" src='paypal.png'/><img style='width:30px; margin-right:5px;' title='Send through Facebook' src='facebook.png'/> <span style='font-size:0.8em; vertical-align:super;'>(or suggest something else)</span></span>";
	echo "</div>";
}
function Login($db) {
	session_start();
	
	if( isset( $_SESSION['signed_in'] ) && $_SESSION['signed_in'] == true ) {
		// Already logged in
		$results = $db->query("SELECT * FROM User WHERE UserName = '" . $_SESSION['username'] . "'");
		$row = $results->fetchArray();
		$_SESSION['SlackID'] = $row['SlackID'];
		$_SESSION['SodaBalance'] = $row['SodaBalance'];
		$_SESSION['SnackBalance'] = $row['SnackBalance'];
		error_log( "Recached SlackID [" . $_SESSION['SlackID'] . "] for [" . $_SESSION['username'] . "]" );
		return;
	}

	if( !isset( $_POST['login_username'] ) || !isset( $_POST['login_password'] ) ) {
		// Missing fields - reject login
		$_SESSION['signed_in'] = false;
		$_SESSION['username'] = null;
		return;
	}
	
	session_destroy();
	session_start();
	
	$username = $_POST['login_username'];
	$password_sha1 = sha1($_POST['login_password']);
	
	$results = $db->query("SELECT * FROM User WHERE UserName = '" . $username . "' AND Password  = '" . $password_sha1 . "'");
	
	$row = $results->fetchArray();
	$userExists = $row != false;
	
	if( $userExists ) {
		$firstName = $row['FirstName'];
		$userID = $row['UserID'];
		$sodaBalance = $row['SodaBalance'];
		$snackBalance = $row['SnackBalance'];
		$slackID = $row['SlackID'];
		error_log("Logging in with [$username] [$userID] [$sodaBalance][$snackBalance]");
		$_SESSION['signed_in'] = true;
		$_SESSION['username'] = $username;
		$_SESSION['firstname'] = $firstName;
		$_SESSION['userID'] = $userID;
		$_SESSION['SodaBalance'] = $sodaBalance;
		$_SESSION['SnackBalance'] = $snackBalance;
		$_SESSION['SlackID'] = $slackID;
	} else {
		$_SESSION['signed_in'] = false;
		$_SESSION['username'] = null;
		
		echo "<div style='padding:30px; font-weight:bold; font-size:1.3em;'>Incorrect password!</div>";
	}
}
function IsLoggedIn()
{        
   	return $_SESSION['signed_in'];
}

function IsAdminLoggedIn()
{
	return $_SESSION['signed_in'] && $_SESSION['username'] == 'mmiles';
}

function GetSessionKey()
{
        global $randomKey; 
        $retvar = md5($randomKey);
        $retvar = 'user_'.substr($retvar,0,10);
        return $retvar;
}

function SanitizeForSQL($str)
{
        if( function_exists( "mysql_real_escape_string" ) )
        {
                  $ret_str = mysql_real_escape_string( $str );
        }
        else
        {
                  $ret_str = addslashes( $str );
        }
        $ret_str = str_replace(";", "", $ret_str);
        return $ret_str;
}

function TrackVisit($db, $title, $loggedIn)
{
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        
        if( $loggedIn == true ) {
        	$ipAddress = $_SESSION['username'];
        }
        
        $date = date('Y-m-d H:i:s', time());
        $agent = "Not Found";
        
        if(isset($_SERVER['HTTP_USER_AGENT']) == true)
                $agent = $_SERVER['HTTP_USER_AGENT'];
        
        $db->exec("INSERT INTO Visits (IP, Date, Agent) VALUES( '$ipAddress', '$date', '$agent')");
		
		if( $ipAddress != "192.9.200.54" && $ipAddress  != "::1" && $ipAddress != "72.225.38.26" ) {
			sendSlackMessageToMatt($title . " visited by [" . $ipAddress . "] on [" . $agent . "]", ":earth_americas:", "SodaStock - VISIT NEW" );
		}
}

function BuildCans($cold_item, $warm_item)
{
        // Build the cans
        $rows_of_cans = ceil(($cold_item + $warm_item) / 3.0);
        $blank_cans = 3 - (($cold_item + $warm_item) % 3);
        $cold_cans_left = $cold_item;
        
        if($blank_cans == 3) { $blank_cans = 0; }
        
        
        //echo "Cans[$rows_of_cans] Blank[$blank_cans]";
        for($row = 0; $row < 3; $row++) 
        {
                        for($col = 0; $col < $rows_of_cans; $col++) 
                        {
                                        if($col < ($rows_of_cans - 1) || $blank_cans == 0)
                                        {
                                                        if($cold_cans_left > 0) 
                                                        {
                                                                        echo "<img src='item_top_cold_1.jpg'/>";
                                                                        $cold_cans_left--;
                                                        }
                                                        else 
                                                        {
                                                                        echo "<img src='item_top_warm_1.jpg'/>";
                                                        }
                                        }
                                        else
                                        {
                                                        $blank_cans--;
                                        }
                        }
                        echo "<br>";
        }
}

function BuildCansVertical($cold_item, $warm_item)
{
        // Build the cans
        $warm_items_left = $warm_item;
        $cold_items_left = $cold_item;
        $current_column = 1;
        
        while( $warm_items_left + $cold_items_left > 0) {
                if($cold_items_left > 0) 
                {
                                echo "<img src='item_top_cold_orig.png'/>";
                                $cold_items_left--;
                }
                else 
                {
                                echo "<img src='item_top_warm_orig.png'/>";
                                $warm_items_left--;
                }
                
                if($current_column==6) {
                        echo "<br>";
                        $current_column = 1;
                } else {
                        $current_column++;
                }
                
        }
}

function DisplayAgoTime( $dateBefore, $dateNow ) {
        $date_object = new DateTime();

        if( $dateBefore != "") {
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $dateBefore);
        }

        $time_since = $dateNow->diff($date_object);
        
        $days_ago = $time_since->format('%a');
        $hours_ago = $time_since->format('%h');
        $minutes_ago = $time_since->format('%i');
        $seconds_ago = $time_since->format('%s');

        $ago_text = "UNKNOWN";

        if($days_ago >= 1) {
            $ago_text = $days_ago . " day". ( ( $days_ago == 1 )? (""):("s") ). "  ago...";
        } else if($hours_ago >= 1) {
            $ago_text = $hours_ago . " hour". ( ( $hours_ago == 1 )? (""):("s") ). " ago...";
        } else if($minutes_ago >= 1) {
            $ago_text = $minutes_ago . " minute". ( ( $minutes_ago == 1 )? (""):("s") ). "  ago...";
        } else  if($seconds_ago >= 1) {
            $ago_text = $seconds_ago . " second". ( ( $seconds_ago == 1 )? (""):("s") ). "  ago...";
        }

        return $ago_text;
}

function buildTopSection( $row, $containerType, $location, $loggedIn, $isMobile ) {
	$retired_label = "<span style='color:#FF6464; border: #9D3A3A 2px dashed; padding:10px; font-weight:bold;'>DISCONTINUED</span>";
	
	$item_id = $row[0];
	$item_name = $row[1];
	$price = $row[7];
	$originalPrice = $price;
	$discountPrice = $row['DiscountPrice'];
	$hasDiscount = false;
	
	if( $loggedIn && $discountPrice != "" ) {
		$price = $discountPrice;
		$hasDiscount = true;
	}

	$price_color = "#FFFFFF";
	$price_background_color = "#025F00";

	// On sale - YELLOW
	if($price < 0.50) {
		$price_color = "#000000";
		$price_background_color = "#FFD500";
	// Expensive - RED
	} else if( $price > 1.00) {
		$price_color = "#FFFFFF";
		$price_background_color = "#5f0000";
	}

	$retired_item = $row[12];
	$cold_item = $row[6];
	$warm_item = $row[5];

	$priceDisplay = "";
	
	if( $loggedIn && $hasDiscount == true ) {
		$priceDisplay = "<span style='font-size:19px; color:$price_color; padding:5px; font-weight:bold; background-color:$price_background_color; border: 2px solid #6b6b6b; float:right;'>".getPriceDisplay ( $discountPrice )."</span><span style='font-size:19px; color:#FFFFFF; padding:5px; font-weight:bold; background-color:#151515; text-decoration:line-through; margin-right:5px; border: 2px solid #6b6b6b; float:right;'>". getPriceDisplay( $originalPrice ) ."</span>";
	} else {
		$priceDisplay = "<span style='font-size:19px; color:$price_color; padding:5px; font-weight:bold; background-color:$price_background_color; border: 2px solid #6b6b6b; float:right;'>". getPriceDisplay( $price ) ."</span>";
	}
    echo "<div style='height:200px;'>";
    echo $priceDisplay;

    echo "<div style='width:40%; float:left;'>";
    DisplayCan($row[1], ($cold_item + $warm_item == 0), $row[13] );
    echo "</div>";
    echo "<div style='width:56%; float:right;'>";

	
	
	if( $retired_item == 1) {
		echo "<div class='circle'>$retired_label</div>"; 
	} else {
	

		if($cold_item == 0 && $warm_item == 0) {
			echo "<div class='no_item circle' style='padding:10px; color:#FF3838'><img width='15px' src='none.png' title='Item running low...'/>&nbsp;SOLD OUT</div>";
			echo "<div class='no_item circle' style='padding:10px; color:#FF3838'><img width='15px' src='none.png' title='Item running low...'/>&nbsp;SOLD OUT</div>"; 
		} else {
			if($cold_item == 0) 
			{ 
				echo "<div class='no_item circle' style='padding:10px;'><img width='15px' src='none.png' title='Item running low...'/>&nbsp;0 $containerType in $location</div>"; 
			} 
			else 
			{ 
				echo "<div title='Cold Cans in the Fridge' class='cold_item circle' style='padding:10px;'>".(($cold_item < 5)?("<img src='./warning.png' title='Item running low...'/>&nbsp;"):(""))."$cold_item $containerType" . ( $cold_item > 1 ? "s" : "" ) . " in $location</div>"; 
			}

			
			if($warm_item == 0) 
			{ 
				echo "<div class='no_item circle' style='padding:10px;'><img width='15px' src='none.png' title='Item running low...'/>&nbsp;0 $containerType at desk</div>"; 
			} 
			else 
			{ 
				echo "<div title='Warm Cans under my Desk' class='warm_item circle' style='padding:10px;'>".(($warm_item < 5)?("<img src='./warning.png' title='Item running low...'/>&nbsp;"):(""))."$warm_item $containerType" . ( $warm_item > 1 ? "s" : "" ) . " at desk</div>"; 
			}
		}
		
		echo "<input id='shelf_quantity_" . $item_id . "' type='hidden' value='" . $cold_item . "'/>";
	}

    echo "</div>";
    echo "</div>";
}

function getPriceDisplay($price) {
	if( $price >= 1.00 ) {
		$price = "$" . number_format($price,2);
	} else {
		$price = $price * 100;
		$price = $price . "&cent;";
	}
	
	return $price;
}
function buildMiddleSection($db, $row, $loggedInAdmin, $loggedIn, $isMobile) {
	
	$cold_item = $row[6];
	
	if( !$isMobile ) {
		$income = $row['TotalIncome'];
		$expense = $row['TotalExpenses'];
		
		
		$profit = number_format(($income-$expense), 2);
		$border = "3px solid #8c8c31";
		$backgroundColor = "#EBEB59";
	
		if($profit < 0) {
				$profit = "<div style='font-weight:bold;'>Debt" . ( $loggedInAdmin ? ":" : "" ) . "</div>" . ( $loggedInAdmin ? "<div>$" .  number_format( abs($profit), 2 ) . "</div>" : "" );
				$border = "3px solid #C60000";
				$backgroundColor = "#E25353";
		} else {
				$profit = "<div style='font-weight:bold;'>Profit" . ( $loggedInAdmin ? ":" : "" ) . "</div>". ($loggedInAdmin ? "<div>$" .  number_format($profit, 2) . "</div>" : "" );
		}
	
	    $total_can = $row[4];
	
		//<img width='20px' src='profit_icon.png'/>
		
	    $center = "";
	    
	    if( !$loggedInAdmin ) {
	    	$center = "style='text-align:center;'";
	    }
		// For the JUSTIFY TO EVENLY SPACE THE ELEMENTS THERE MUST BE SPACES BETWEEN THEM (&nbsp;) much like how for words to separated using justify there must be spaces to break on
	    
	    
	    
	    
	    if( $loggedInAdmin ) {
	    	echo "<div $center id='money_container'>";
	    	echo "<div class='money' style='background-color:$backgroundColor; border: $border' >" . $profit . "</div>&nbsp;";
	    	echo "<div class='money' style='background-color:#90EE90;'><div style='font-weight:bold;'>Income:</div><div>$" . number_format($income, 2) . "</div></div>&nbsp;";
	    	echo "<div class='money' style='background-color:#EE4545;'><div style='font-weight:bold;'>Expense:</div><div>$" . number_format($expense, 2) . "</div></div>&nbsp;";
	    	echo "</div>";
	    }
	
	    echo "<div style='clear:both;'></div>";
		
		$resultsPopularity = $db->query('SELECT ItemID, Date FROM Restock where ItemID = ' . $row[0] . ' ORDER BY Date DESC');
	    $firstDate = "";
	    $lastDate = "";
	    $totalPurchases = 0;
	    while ($rowPopularity = $resultsPopularity->fetchArray()) {
	        if( $firstDate == "") {
	            $firstDate = $rowPopularity[1];
	        }
	        $lastDate = $rowPopularity[1];
	        $totalPurchases++;
	    }
	
	    $date_before = DateTime::createFromFormat('Y-m-d H:i:s', $firstDate);
	    $date_after = DateTime::createFromFormat('Y-m-d H:i:s', $lastDate);
	
		$days_ago = 0;
		
		if( $firstDate != "" && $lastDate != "" ) {
			if( $firstDate == $lastDate) {
				$date_after = new DateTime();
			}
	
			$time_since = $date_before->diff($date_after);
			
			$days_ago = $time_since->format('%a');  
		}
	}
	
	if( $loggedIn ) {
		echo "<div style='padding-bottom:10px;'>";
		echo "<button id='remove_button_" .  $row[0] . "' class='quantity_button quantity_button_remove_disabled' onclick='removeItemFromCart(" . $row[0] . ")' title='Remove item(s)'>REMOVE</button>";
		echo "<span style='font-weight:bold; color:#FFF; padding:5px 10px; border: dashed 2px #000;' id='quantity_holder_" . $row[0] . "'>0</span>";
		
		if( $cold_item == 0 ) {
			echo "<button id='add_button_" .  $row[0] . "' class='quantity_button quantity_button_add_disabled' onclick='addItemToCart(" . $row[0] . ")' title='Add item(s)'>ADD</button>";
		} else {
			echo "<button id='add_button_" .  $row[0] . "' class='quantity_button quantity_button_add' onclick='addItemToCart(" . $row[0] . ")' title='Add item(s)'>ADD</button>";
		}
		
		echo "</div>";
	}
	
	
	if( !$isMobile ) {
		echo "<div>";
		
		if( !$loggedInAdmin ) {
			echo "<span class='money' style='display:inline-block; background-color:$backgroundColor; border: $border' >" . $profit . "</span>";
		}
		
		if( $totalPurchases > 0 ) {
	    	echo "<span style='padding:20px; color:#f9ff00; font-weight:bold;' >Bought every ".round($days_ago / $totalPurchases)." days...</span>";
		}
		
		echo "</div>";
		
		if( isset( $row['Frequency'] ) ) {
			echo "<div style='padding:20px 0px 0px 0px; color:#00ff39; font-weight:bold;' >You have bought this ". $row['Frequency'] ." times.</div>";
		}
	}
}

function buildBottomSection($row, $containerType) {

	$total_can_sold = $row[4] - ($row[5] + $row[6]);

    echo "<div style='padding:5px;'>" . $total_can_sold . " TOTAL " . strtoupper( $containerType ) . ( $total_can_sold > 1 ? "S" : "" ) . " SOLD</div>";
}
function DisplayCan($item_name, $soldOut, $imageURL)
{
		$opacity = "1.0";

		if( $soldOut == true ) {
			$opacity = "0.4";
		}
		
		if( $imageURL == "" ) {
			echo "<div class='vcenter' style='background-color:#212121; font-weight:bold;  word-spacing:200px; height:100%; color:#FFFFFF'><span>".$item_name."</span></div>";
		} else {
			echo "<img src='images/$imageURL' style = 'height:100%; max-width:100%; opacity:$opacity' />";
		}
}

function DisplayShelfCan($item_name, $thumbURL)
{
		if( $thumbURL == "" ) {
			echo "<img title='$item_name' style='padding:5px;' src='thumbs/not_found_sm.png' />";
		} else {
			echo "<img title='$item_name' style='padding:5px;' src='thumbs/$thumbURL' />";
		}
}
?>