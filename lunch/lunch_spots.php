<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>

<?php
       // Return menus
       // Return images for each place (small icons)


	$db = new SQLite3( "lunch_index.db" );
    if (!$db) die ( "Oops, something broke. Blame Matt." );

    date_default_timezone_set('America/New_York');

    $statementCategory = $db->prepare("SELECT Name FROM Category ORDER BY Position" );
    $resultsCategory = $statementCategory->execute();

    $allCategories = array();
    $allCategoriesForDisplay = array();

    while ($rowCategory = $resultsCategory->fetchArray()) {
        $categoryName = $rowCategory['Name'];
        $allCategories[] = strtolower( $categoryName );
        $allCategoriesForDisplay[] = $categoryName;
    }

    // Change for testing
    $lineBreak = "\n";

	if( isset( $_GET["mode"] ) ) {
//		echo "Entering new mode code.$lineBreak";
        if( $_GET["mode"] == "random" ) {
            $originalPreferences = $_GET["preferences"];
//			echo "Returning random with preference [$preferences]$lineBreak";

			$preferences = strtolower( $originalPreferences );

			$statementDistance = $db->prepare("SELECT Name FROM Distance ORDER BY Position" );
            $resultsDistance = $statementDistance->execute();

            $allDistances = array();

            while ($rowDistance = $resultsDistance->fetchArray()) {
                $distanceName = strtolower( $rowDistance['Name'] );
                $allDistances[] = $distanceName;
            }

			if( $preferences == "looper" ) {
                echo "You should go to McCann's. Anyway, Mike (one of our co-ops) has a story:$lineBreak\"I had a code review a while back, towards the end of my co-op where it was Full timer Alex, Nick, Matt, and Myself reviewing some test I had written. At some point during the code review we came across a file where there was a for loop with the variable looper like so: `for(int looper = 0; looper < someThing; looper++){}` Of course this isn't a great variable name, but in its defense it was used like for like a one line for loop that didn't do anything too complicated.$lineBreak $lineBreak Anyhow, all of us, myself included, laughed at it and asked \"What idiot wrote this stupid variable name???\" We inspected the git history for the file. I was the idiot, several months earlier.$lineBreak $lineBreak I was laughed at for several minutes.";
                die();
            } else if( $preferences == "help" || $preferences == "options" || $preferences == "man" )  {
                echo "Usage:$lineBreak";
                echo "lunch random &lt;preferences&gt;$lineBreak";
                echo "lunch menu &lt;location&gt;$lineBreak";
                echo "The following are supported for &lt;preferences&gt; (you can combine them): $lineBreak* By Distance:* " . implode(", ", $allDistances) . "$lineBreak* By Category:* " . implode(", ", $allCategoriesForDisplay ) . "$lineBreak* By Options:* Gluten Free/No Gluten, Lactose Free/No Lactose, Vegetarian/Veggie, Vegan, Takeout";
                die();
            }

			// Find common terms that are two words and fill in spaces so they aren't split apart
            $preferences = str_replace( "fast food", "fast_food", $preferences );
            $preferences = str_replace( "sit down", "sit_down", $preferences );
            $preferences = str_replace( "very short", "very_short", $preferences );
            $preferences = str_replace( "very long", "very_long", $preferences );
            $preferences = str_replace( "gluten free", "gluten_free", $preferences );
            $preferences = str_replace( "no gluten", "no_gluten", $preferences );

            $preferenceDisplay = "You had no preferences. Here's a pick from the entire list.";


            $allPreferences = explode(" ", $preferences );

            $currentBindings = array();
            $currentWhereStatement = "";
            for( $paramNumber = 1; $paramNumber <= sizeof( $allPreferences ); $paramNumber++) {
                $preference = strtolower( $allPreferences[$paramNumber-1] );
                $preference = str_replace( "_", " ", $preference );

                $currentWhereSegment = "";

                if( in_array( $preference, $allCategories ) ) {
                    $currentWhereSegment = "lower(c.Name) = :category$paramNumber";
                    $currentBindings[":category$paramNumber"] = $preference;
                } else if( in_array( $preference, $allDistances ) ) {
                    $currentWhereSegment = "lower(d.Name) = :distance$paramNumber";
                    $currentBindings[":distance$paramNumber"] = $preference;
                } else if( $preference == "nogluten" || $preference == "glutenfree" ) {
                    $currentWhereSegment = "l.HasNoGluten = 1";
                } else if( $preference == "nolactose" || $preference == "lactosefree" ) {
                    $currentWhereSegment = "l.HasNoLactose = 1";
                } else if( $preference == "vegan" ) {
                    $currentWhereSegment = "l.HasVegan = 1";
                } else if( $preference == "vegetarian" || $preference == "veggie" ) {
                    $currentWhereSegment = "l.HasVegetarian = 1";
                } else if( $preference == "takeout" ) {
                    $currentWhereSegment = "l.HasTakeout = 1";
                }

                if( $currentWhereSegment == "" ) {
                    echo "I didn't know what to do with [$preference]. Ignoring...$lineBreak";
                } else {
                    $currentWhereStatement .= " AND $currentWhereSegment ";
                }
//                echo "Current so far: [$currentWhereStatement]$lineBreak";
            }

            if( $currentWhereStatement != "" ) {
                $preferenceDisplay = "You wanted [$originalPreferences] as a preference.";
            }

            $randomLocationSQL = "SELECT l.LocationID, l.Name, l.Abbreviation, l.Punchline, l.Description, d.Name as DistanceName, c.Name as CategoryName, Latitude, Longitude, MenuFileName " .
                "FROM Location l " .
                "JOIN Distance d ON l.DistanceID = d.DistanceID " .
                "JOIN Category c ON l.CategoryID = c.CategoryID " .
                "WHERE lower(c.Name) != 'permanently closed' $currentWhereStatement " .
                "ORDER BY RANDOM() LIMIT 1";

//            echo "$lineBreak SQL[$randomLocationSQL]$lineBreak";

            $statementRandomLocation = $db->prepare( $randomLocationSQL );

            foreach( $currentBindings as $binding => $value ) {
                $statementRandomLocation->bindValue($binding, $value );
            }

            $resultsRandomLocation = $statementRandomLocation->execute();
            $rowRandomLocation = $resultsRandomLocation->fetchArray();

            $locationName = $rowRandomLocation['Name'];
            $locationCategory = $rowRandomLocation['CategoryName'];
            $locationDistance = $rowRandomLocation['DistanceName'];
            $locationDescription = $rowRandomLocation['Punchline'];

            if( $locationName == "" ) {
                echo "I could not find anything for these preferences. Choose something less specific.$lineBreak$lineBreak" . $preferenceDisplay;
            } else {
                echo "You should go to *" . $locationName . "* (" . $locationCategory . " category) - " . $locationDistance . " distance $lineBreak $lineBreak _" . $locationDescription . "_$lineBreak$lineBreak" . $preferenceDisplay;
            }
            die();
        } else if( $_GET["mode"] == "menu" ) {
//			echo "Returning menu for place [" . $_GET["place"] . "]";

			$place = $_GET["place"];
			$place = strtolower( $place );

			$menuSQL = "SELECT l.Name, MenuFileName " .
                "FROM Location l " .
                "WHERE lower(l.Name) like :place ";

//            echo "$lineBreak SQL[$menuSQL]$lineBreak";

            $statementMenu = $db->prepare( $menuSQL );
            $statementMenu->bindValue(":place", "%$place%");

            $resultsMenu = $statementMenu->execute();
            $rowMenu = $resultsMenu->fetchArray();

            $menuName = $rowMenu['MenuFileName'];
            $placeName = $rowMenu['Name'];

            if( $menuName != "" ) {
                echo "I found this menu for *$placeName*: $lineBreak https://penguinore.net/lunch/menus/$menuName";
            } else {
                echo "I could not find a menu for *$place*.";
            }
		}
		die();
	}

//		if( count( $thePreference > 1 ) ) {
//			if( $splitPreference[0] == "imp" ) {
//				error_log($splitPreference[1]);
//				echo $splitPreference[1];
//				die();
//			}
//
//		}

    echo "<head>";
    echo "<title>Lunch Spots around RSA</title>";
    echo "<link rel='icon' type='image/png'  href='hamburger.ico'>";
    echo "</head>";
    echo "<div style='display:inline-block; width:35%;'>";

    echo "<div style='color:#006400;'>* In walking distance (average of 5-10 minute walk)</div>";
    echo "<div style='color:blue;'>* In short driving distance (average of 5-10 minute drive)</div>";
    echo "<div style='color:maroon;'>* In long driving distance (average of 10-20 minute drive)</div>";
    echo "<!-- <div style='color:black;'>* In plane riding distance (average of 1 hour trip)</div> -->";

    echo "<input type='checkbox' id='VeganFilter'/> Vegan";
    echo "<input type='checkbox' id='VegetarianFilter'/> Vegetarian";
    echo "<input type='checkbox' id='NoGlutenFilter'/> No Gluten";
    echo "<input type='checkbox' id='NoLactoseFilter'/> No Lactose";
    echo "<input type='checkbox' id='TakeoutFilter'/> Takeout";

    echo "<br>";
	$benchMarkers = "";

    foreach( $allCategoriesForDisplay as $categoryName ) {

        // Open new list
        echo "<li>";
        echo "<b>$categoryName</b>";
        echo "<ul>";


        $statementLocation = $db->prepare("SELECT l.LocationID, l.Name, l.Abbreviation, l.Punchline, l.Description, d.Name as DistanceName, c.Name as CategoryName, Latitude, Longitude, MenuFileName, " .
            "HasVegan, HasVegetarian, HasNoGluten, HasNoLactose, HasTakeout " .
            "FROM Location l " .
            "JOIN Distance d ON l.DistanceID = d.DistanceID " .
            "JOIN Category c ON l.CategoryID = c.CategoryID " .
            "WHERE c.Name = :categoryName " .
            "ORDER BY d.Position ASC, l.Name ASC" );
        $statementLocation->bindValue( ":categoryName", $categoryName );
        $resultsLocation = $statementLocation->execute();

        while ( $rowLocation = $resultsLocation->fetchArray()) {
            $locationName = $rowLocation['Name'];
            $locationAbbreviation = $rowLocation['Abbreviation'];
            $locationPunchline = $rowLocation['Punchline'];
            $locationDescription = $rowLocation['Description'];
            $locationDistance = $rowLocation['DistanceName'];
            $locationLatitude = $rowLocation['Latitude'];
            $locationLongitude = $rowLocation['Longitude'];
            $locationMenuFileName = $rowLocation['MenuFileName'];
            $locationHasVegan = $rowLocation['HasVegan'];
            $locationHasVegetarian = $rowLocation['HasVegetarian'];
            $locationHasNoGluten= $rowLocation['HasNoGluten'];
            $locationHasNoLactose = $rowLocation['HasNoLactose'];
            $locationHasTakeout = $rowLocation['HasTakeout'];

            $color = "#000000";

            switch ( $locationDistance ) {
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

            if ( $categoryName == "Permanently Closed" ) {
                $strikeThrough = "text-decoration: line-through;";
            }

            $classes = "location";

            if( $locationHasVegan == 1 ) {
                $classes .= " vegan";
            }

            if( $locationHasVegetarian == 1 ) {
                $classes .= " vegetarian";
            }

            if( $locationHasNoGluten == 1 ) {
                $classes .= " nogluten";
            }

            if( $locationHasNoLactose == 1 ) {
                $classes .= " nolactose";
            }

            if( $locationHasTakeout == 1 ) {
                $classes .= " takeout";
            }

            echo "<li class='$classes' style='color:$color; padding: 2px 0px; $strikeThrough'>$locationName";

            if ($locationAbbreviation != null && $locationAbbreviation != "FOOD TRUCK") {
                echo " (" . $locationAbbreviation . ")";
            }

            if ( $locationDescription != null || $locationPunchline != null ) {
                echo "<span style='font-size: 0.8em; color:#4c1182;'> - ";

                if ( $locationDescription != null ) {
                    echo $locationDescription;
                } else if ( $locationPunchline != null ) {
                    echo $locationPunchline;
                }

                echo "</span>";
            }

            if ( $locationAbbreviation != null ) {
                $icon = "";

                if ( $locationAbbreviation == "FOOD TRUCK" ) {
                    $icon = ", icon: food_truck_logo";
                } else {
                    $icon = ", label: '$locationAbbreviation'";
                }

                $escapedName = str_replace("'", "\'", $locationName);

                if( $locationLatitude == null ) {
                    $benchMarkers .= "console.error( 'Latitude for $escapedName is missing!');";
                }

                if( $locationLongitude == null ) {
                    $benchMarkers .= "console.error( 'Longitude for $escapedName is missing!');";
                }

                if( $locationLongitude != null && $locationLatitude != null ) {
                    $benchMarkers .= "new google.maps.Marker({ position: {lat: $locationLatitude, lng: $locationLongitude}, map: map, title: '" . $escapedName . "' $icon});\n";
                }
            }
        }

        echo "</ul>";
        echo "</li>";
    }

	?>

    </div>

	<div style='display:inline-block; width:64%; vertical-align:top; border:1px solid #000;'>
	<div id="map" style="height:600px;"></div>
        <a href='lunch_spots_admin.php'>Admin</a>
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

            new google.maps.Marker({
                position: {lat: 43.155012, lng: -77.619447},
                map: map,
                icon: rsa_logo
            });

            new google.maps.Marker({
                position: {lat: 43.1600687, lng: -77.6171080},
                map: map,
                icon: shoretel_logo
            });

        }

        google.maps.event.addDomListener(window, 'load', initialize);

        $( document ).ready( function() {
            $('#VeganFilter').change(function () {
                applyFilters();
            });

            $('#VegetarianFilter').change(function () {
                applyFilters();
            });

            $('#NoGlutenFilter').change(function () {
                applyFilters();
            });

            $('#NoLactoseFilter').change(function () {
                applyFilters();
            });

            $('#TakeoutFilter').change(function () {
                applyFilters();
            });
        });

        function applyFilters() {
            var isVegan = $('#VeganFilter').prop("checked");
            var isVegetarian = $('#VegetarianFilter').prop("checked");
            var isNoGluten = $('#NoGlutenFilter').prop("checked");
            var isNoLactose = $('#NoLactoseFilter').prop("checked");
            var isTakeout = $('#TakeoutFilter').prop("checked");

            if(!isVegan && !isVegetarian && !isNoGluten && !isNoLactose && !isTakeout ) {
                $('.location').show();
            } else {
                $('.location').hide();

                if (isVegan) {
                    $('.vegan').show();
                }

                if (isVegetarian) {
                    $('.vegetarian').show();
                }

                if (isNoGluten) {
                    $('.nogluten').show();
                }

                if (isNoLactose) {
                    $('.nolactose').show();
                }

                if (isTakeout) {
                    $('.takeout').show();
                }
            }
        }
    </script>