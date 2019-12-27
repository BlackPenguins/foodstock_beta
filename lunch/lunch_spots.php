<head>
<title>Lunch Spots around RSA</title>
<link rel="icon" type="image/png"  href="hamburger.ico">
</head>
<div style='display:inline-block; width:35%;'>
	<div style='color:#006400;'>* In walking distance (average of 5-10 minute walk)</div>
	<div style='color:blue;'>* In short driving distance (average of 5-10 minute drive)</div>
	<div style='color:maroon;'>* In long driving distance (average of 10-20 minute drive)</div>
	<!-- <div style='color:black;'>* In plane riding distance (average of 1 hour trip)</div> -->
	<br>

<?php
       // Remove duplicate code, have the Location objects build EVERYTHING - the bot, the list, the map
       // Return menus
       // Return images for each place (small icons)


	include "lunch_location.php";
	include "lunch_index.php";

	$lunchSpotsArray = getLocations();
	usort($lunchSpotsArray, 'cmp' );

	$thePreference = "";

	if( isset( $_GET["mode"] ) ) {
		echo "Entering new mode code.\n";
        if( $_GET["mode"] == "random" ) {
			echo "Returning random with preference [" . $_GET["preferences"] . "]";
        } else if( $_GET["mode"] == "menu" ) {
			echo "Returning menu for place [" . $_GET["place"] . "]";
		}
		die();
	}

    if( isset( $_GET["preferences"] ) ) {

		$thePreference = trim( $_GET["preferences"] );
		$splitPreference = explode( "|", $thePreference );

		if( count( $thePreference > 1 ) ) {
			if( $splitPreference[0] == "imp" ) {
				error_log($splitPreference[1]);
				echo $splitPreference[1];
				die();
			}

		}

		$thePreference = strtolower( $thePreference );

		error_log( "Preferences:[$thePreference]");

		$thePreference = preg_replace( '/\s+/', " ", $thePreference );
		error_log("PREFERENCES: [$thePreference]");
	}

	if( $thePreference == "looper" ) {
		echo "You should go to McCann's. Anyway, Mike (one of our co-ops) has a story:\n\"I had a code review a while back, towards the end of my co-op where it was Full timer Alex, Nick, Matt, and Myself reviewing some test I had written. At some point during the code review we came across a file where there was a for loop with the variable looper like so: `for(int looper = 0; looper < someThing; looper++){}` Of course this isn't a great variable name, but in its defense it was used like for like a one line for loop that didn't do anything too complicated.\n\nAnyhow, all of us, myself included, laughed at it and asked \"What idiot wrote this stupid variable name???\" We inspected the git history for the file. I was the idiot, several months earlier.\n\nI was laughed at for several minutes.";
		die();
	}


	if( $thePreference == "help" || $thePreference == "options" || $thePreference == "man" )  {
		echo "The following are supported for \"preferences\".\n* •By Distance:* Walking, Short, Long\n* •By Category:* Classics, Mexican, Pizza, Subs, Diners, Fast Food, Random, Sit Down";
		die();
	}




	$preferenceDisplay = "You had no preferences. Here's a pick from the entire list.";
	$hasPreference = false;

	if( $thePreference != "" ) {
		$preferenceDisplay = "You wanted [$thePreference] as a preference.";
		$hasPreference = true;
	}

	if( isset( $_GET["random"] ) ) {
		$foundPlace = false;

		while( $foundPlace == false && count($lunchSpotsArray) > 0 ) {
			$randomIndex = rand(0, count($lunchSpotsArray) - 1);
			$locationObj = $lunchSpotsArray[$randomIndex];
			$locationName = $locationObj->getName();
			$locationCategory = $locationObj->getCategory();
			$locationDistance = $locationObj->getDistanceType();
			$locationDescription = $locationObj->getDescription();

			if( $hasPreference == true && strtolower( $locationDistance ) != $thePreference && strtolower( $locationCategory ) != $thePreference ) {
				// Preference didn't match - remove from array
				array_splice($lunchSpotsArray, $randomIndex, 1 );
				continue;
			} else {
				echo "You should go to *" . $locationName . "* (" . $locationCategory . " category) - " . $locationDistance . " distance\n\n_" . $locationDescription . "_\n\n" . $preferenceDisplay;
				$foundPlace = true;
			}
		}

		if( $foundPlace == false ) {
			echo "[$thePreference] is not a valid preference. Get out of here.";
		}
	}

	$currentCategory = "";

	$benchMarkers = "";

	foreach( $lunchSpotsArray as $lunchSpot ) {
	    if( $currentCategory == "" || $lunchSpot->getCategory() != $currentCategory ) {

	        if( $currentCategory != "" ) {
	            // Close previous list
	            echo "</ul>";
	            echo "</li>";
            }

	        // Open new list
	        echo "<li>";
		    echo "<b>" . $lunchSpot->getCategory() . "</b>";
		    echo "<ul>";

		    $currentCategory = $lunchSpot->getCategory();
        }

	    $color = "#000000";

	    switch( $lunchSpot->getDistanceType() ) {
	        case "Very Short";
            case "Walking":
                $color = "#006400";
                break;
            case "Short":
                $color = "blue";
                break;
            case "Long":
                $color = "maroon";
                break;
            case "Very Long":
                $color = "#bebebe";
                break;
        }

        $strikeThrough = "";

	    if( $lunchSpot->getCategory() == "Permanently Closed" ) {
	        $strikeThrough = "text-decoration: line-through;";
        }

	    echo "<li style='color:$color; padding: 2px 0px; $strikeThrough'>" . $lunchSpot->getName();

	    if( $lunchSpot->getAbbreviation() != null && $lunchSpot->getAbbreviation() != "FOOD TRUCK" ) {
            echo " (" . $lunchSpot->getAbbreviation() . ")";
        }

	    if( $lunchSpot->getDescription() != null || $lunchSpot->getPunchline() != null ) {
            echo "<span style='font-size: 0.8em; color:#4c1182;'> - ";

            if( $lunchSpot->getDescription() != null ) {
                echo $lunchSpot->getDescription();
            } else if( $lunchSpot->getPunchline() != null ) {
                echo $lunchSpot->getPunchline();
            }

            echo  "</span>";
        }

	    if( $lunchSpot->getAbbreviation() != null ) {

	        $icon = "";

	        if( $lunchSpot->getAbbreviation() == "FOOD TRUCK" ) {
	            $icon = ", icon: food_truck_logo";
            } else {
	            $icon = ", label: '" . $lunchSpot->getAbbreviation() . "'";
            }

	        $escapedName = str_replace( "'", "\'", $lunchSpot->getName() );
	        $benchMarkers .= "var beachMarker = new google.maps.Marker({ position: {lat: " . $lunchSpot->getLatitude() . ", lng: " . $lunchSpot->getLongitude() . "}, map: map, title: '" . $escapedName . "' $icon});";
        }
    }

	?>

    </div>

	<div style='display:inline-block; width:64%; vertical-align:top; border:1px solid #000;'>
	<div id="map" style="height:600px;"></div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA1bot3pg5kYd2M8I9FmcK29kb7YsF5iBA"></script>

    <script>
        function initialize() {
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 17,
                center: { lat: 43.155814, lng: -77.615362 }
            });

            var rsa_logo = 'rsa_logo.png';
            var shoretel_logo = 'shoretel_logo.png';
            var food_truck_logo = 'yellow_truck.png';

            <?php echo $benchMarkers; ?>

            var beachMarker = new google.maps.Marker({
                position: {lat: 43.155012, lng: -77.619447},
                map: map,
                icon: rsa_logo
            });

            var beachMarker = new google.maps.Marker({
                position: {lat: 43.1600687, lng: -77.6171080},
                map: map,
                icon: shoretel_logo
            });

        }

        google.maps.event.addDomListener(window, 'load', initialize);
    </script>