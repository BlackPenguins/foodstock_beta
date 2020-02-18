<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<link rel="stylesheet" type="text/css" href="style_7_2.css">

<?php
    session_start();

    $isAdmin = isset( $_SESSION['LoggedIn'] ) && $_SESSION['LoggedIn'] && isset( $_SESSION['IsAdmin'] ) && $_SESSION['IsAdmin'];

    if( $isAdmin ) {
        echo "You are admin.";

        $db = new SQLite3( "lunch_index.db" );
        if (!$db) die ( "Oops, something broke. Blame Matt." );

        if (isset($_POST['AddLocation'])) {
            addLocation( $db, $_POST["LocationName"], $_POST["Category"], $_POST["Distance"] );
        } else if (isset($_POST['EditLocation'])) {

            isset($_POST["EditHasVegan"]) ? $hasVegan = 1 : $hasVegan = 0;
            isset($_POST["EditHasVegetarian"]) ? $hasVegetarian = 1 : $hasVegetarian = 0;
            isset($_POST["EditHasNoGluten"]) ? $hasNoGluten = 1 : $hasNoGluten = 0;
            isset($_POST["EditHasNoLactose"]) ? $hasNoLactose = 1 : $hasNoLactose = 0;
            isset($_POST["EditHasTakeout"]) ? $hasTakeout = 1 : $hasTakeout = 0;

            editLocation( $db, $_POST["EditLocationID"], $_POST["EditCategory"], $_POST["EditDistance"], $_POST['EditName'], $_POST['EditAbbr'],
                $_POST['EditPunchline'], $_POST['EditDescription'], $_POST['EditLatitude'], $_POST['EditLongitude'], $hasVegan, $hasVegetarian, $hasNoGluten, $hasNoLactose, $hasTakeout );
        }

        // Build Item Dropdown
        $statement = $db->prepare("SELECT CategoryID, Name " .
            "FROM Category " .
            "ORDER BY Name ASC");
        $results = $statement->execute();

        $category_options = "";
        while ($row = $results->fetchArray()) {
            $categoryID = $row['CategoryID'];
            $categoryName = $row['Name'];
            $category_options = $category_options . "<option value='$categoryID'>$categoryName</option>";
        }

        // Build Item Dropdown
        $statement = $db->prepare("SELECT DistanceID, Name " .
            "FROM Distance " .
            "ORDER BY Name ASC");
        $results = $statement->execute();

        $distance_options = "";
        while ($row = $results->fetchArray()) {
            $distanceID = $row['DistanceID'];
            $distanceName = $row['Name'];
             $distance_options = $distance_options . "<option value='$distanceID'>$distanceName</option>";
        }

        // Build Item Dropdown
        $statement = $db->prepare("SELECT LocationID, Name, Abbreviation, CategoryID, Punchline, Description, DistanceID, Latitude, Longitude, MenuFileName, HasVegan, HasVegetarian, HasNoGluten, HasTakeout, HasNoLactose " .
            "FROM Location " .
            "ORDER BY Name ASC");
        $results = $statement->execute();

        $location_options = "";
        $location_info = "";
        while ($row = $results->fetchArray()) {
            $locationID = $row['LocationID'];
            $locationName = $row['Name'];
            $locationAbbr = $row['Abbreviation'];
            $categoryID = $row['CategoryID'];
            $locationPunchline = $row['Punchline'];
            $locationDescription = $row['Description'];
            $distanceID = $row['DistanceID'];
            $locationLatitude = $row['Latitude'];
            $locationLongitude = $row['Longitude'];
            $locationMenuFileName = $row['MenuFileName'];
            $locationHasVegan = $row['HasVegan'];
            $locationHasVegetarian= $row['HasVegetarian'];
            $locationHasNoGluten= $row['HasNoGluten'];
            $locationHasTakeout= $row['HasTakeout'];
            $locationHasNoLactose= $row['HasNoLactose'];

            $location_options = $location_options . "<option value='$locationID'>$locationName</option>";

            $location_info = $location_info . "<input type=\"hidden\" id=\"Location_Name_$locationID\" value=\"$locationName\"/>" .
            "<input type=\"hidden\" id=\"Location_Abbreviation_$locationID\" value=\"$locationAbbr\"/>" .
            "<input type=\"hidden\" id=\"Location_CategoryID_$locationID\" value=\"$categoryID\"/>" .
            "<input type=\"hidden\" id=\"Location_Punchline_$locationID\" value=\"$locationPunchline\"/>" .
            "<input type=\"hidden\" id=\"Location_Description_$locationID\" value=\"$locationDescription\"/>" .
            "<input type=\"hidden\" id=\"Location_DistanceID_$locationID\" value=\"$distanceID\"/>" .
            "<input type=\"hidden\" id=\"Location_Latitude_$locationID\" value=\"$locationLatitude\"/>" .
            "<input type=\"hidden\" id=\"Location_Longitude_$locationID\" value=\"$locationLongitude\"/>" .
            "<input type=\"hidden\" id=\"Location_MenuFileName_$locationID\" value=\"$locationMenuFileName\"/>" .
            "<input type=\"hidden\" id=\"Location_HasVegan_$locationID\" value=\"$locationHasVegan\"/>" .
            "<input type=\"hidden\" id=\"Location_HasVegetarian_$locationID\" value=\"$locationHasVegetarian\"/>" .
            "<input type=\"hidden\" id=\"Location_HasNoGluten_$locationID\" value=\"$locationHasNoGluten\"/>" .
            "<input type=\"hidden\" id=\"Location_HasTakeout_$locationID\" value=\"$locationHasTakeout\"/>" .
            "<input type=\"hidden\" id=\"Location_HasNoLactose_$locationID\" value=\"$locationHasNoLactose\"/>";
        }

        $add_category_dropdown = "<select name='Category' style='font-size:1em;'>$category_options</select>";
        $add_distance_dropdown = "<select name='Distance' style='font-size:1em;'>$distance_options</select>";

        $edit_location_dropdown = "<select id='EditLocation' name='EditLocationID' style='font-size:1em;'>$location_options</select>";
        $edit_category_dropdown = "<select id='EditCategory' name='EditCategory' style='font-size:1em;'>$category_options</select>";
        $edit_distance_dropdown = "<select id='EditDistance' name='EditDistance' style='font-size:1em;'>$distance_options</select>";

        buildAddLocation( $add_category_dropdown, $add_distance_dropdown );
        buildEditLocation( $edit_location_dropdown, $edit_category_dropdown, $edit_distance_dropdown, $location_info );


   } else {
       echo "You aren't admin! Get out of here Noah!";
   }

function buildAddLocation( $category_dropdown, $distance_dropdown ) {
    echo "<div id='add_location_modal' class='neptuneModalNo'>";
    echo "<div class='neptuneModalContentNo'>";

    echo "<form class='neptuneFormNo' id='add_location_form' enctype='multipart/form-data' action='lunch_spots_admin.php' method='POST'>";
    echo "<ul>";

    echo "<li>";
    echo "<label for='LocationName'>Location Name</label>";
    echo "<input type='text' autocomplete='off' name='LocationName' maxlength='40'/>";
    echo "</li>";

    echo "<li>";
    echo "<label for='LocationName'>Category</label>";
    echo  $category_dropdown;
    echo "</li>";

    echo "<li>";
    echo "<label for='LocationName'>Distance</label>";
    echo  $distance_dropdown;
    echo "</li>";

    echo "<input type='hidden' name='AddLocation' value='AddLocation'/>";

    echo "<li class='buttons'>";
    echo "<input style='padding:10px;' type='submit' name='Add_Location_Submit' value='Add Location'/>";
    echo "</li>";

    echo "</ul>";
    echo "</form>";

    echo "</div>";
    echo "</div>";
}

function buildEditLocation( $location_dropdown, $category_dropdown, $distance_dropdown, $location_info ) {

    echo "<div id='edit_location_modal' class='neptuneModalNo'>";
    echo "<div class='neptuneModalContentNo'>";

    echo "<form class='neptuneFormNo' id='edit_location_form' enctype='multipart/form-data' action='lunch_spots_admin.php' method='POST'>";
    echo "<ul>";

    echo "<li>";
    echo "<label for='LocationName'>Location</label>";
    echo $location_dropdown;
    echo "</li>";

    echo "<li>";
    echo "<label>Location Name</label>";
    echo "<input type='text' autocomplete='off' id='EditName' name='EditName' maxlength='40'/>";
    echo "</li>";

    echo "<li>";
    echo "<label>Abbreviation</label>";
    echo "<input type='text' autocomplete='off' id='EditAbbr' name='EditAbbr' maxlength='40'/>";
    echo "</li>";

    echo "<li>";
    echo "<label>Punchline</label>";
    echo "<input type='text' autocomplete='off' id='EditPunchline' name='EditPunchline' size='200' maxlength='200'/>";
    echo "</li>";

    echo "<li>";
    echo "<label>Description</label>";
    echo "<input type='text' autocomplete='off' id='EditDescription' name='EditDescription' size='200' maxlength='200'/>";
    echo "</li>";

    echo "<li>";
    echo "<label>Latitude</label>";
    echo "<input type='text' autocomplete='off' id='EditLatitude' name='EditLatitude' maxlength='40'/>";
    echo "</li>";

    echo "<li>";
    echo "<label>Longitude</label>";
    echo "<input type='text' autocomplete='off' id='EditLongitude' name='EditLongitude' maxlength='40'/>";
    echo "</li>";

    echo "<li>";
    echo "<label for='LocationName'>Category</label>";
    echo  $category_dropdown;
    echo "</li>";

    echo "<li>";
    echo "<label for='LocationName'>Distance</label>";
    echo  $distance_dropdown;
    echo "</li>";

    echo "<li>";
    echo "<label for='uploadedMenu'>Menu File</label>";
    echo "<input name='uploadedMenu' type='file' />";
    echo "<div style='color:#a50e18; padding: 5px 0px; font-size: 0.9em;' id='EditMenuName'></div>";
    echo "</li>";

    echo "<div class='neptuneRow'>";
        echo "<label style='display:inline;' for='IsVendor'>Vegan:</label>";
        echo "<input style='display:inline;' type='checkbox' id='EditHasVegan' name='EditHasVegan'/>";
    echo "</div>";

    echo "<div class='neptuneRow'>";
        echo "<label style='display:inline;' for='IsVendor'>Vegetarian:</label>";
        echo "<input style='display:inline;' type='checkbox' id='EditHasVegetarian' name='EditHasVegetarian'/>";
    echo "</div>";

    echo "<div class='neptuneRow'>";
        echo "<label style='display:inline;' for='IsVendor'>No Gluten:</label>";
        echo "<input style='display:inline;' type='checkbox' id='EditHasNoGluten' name='EditHasNoGluten'/>";
    echo "</div>";

    echo "<div class='neptuneRow'>";
        echo "<label style='display:inline;' for='IsVendor'>No Lactose:</label>";
        echo "<input style='display:inline;' type='checkbox' id='EditHasNoLactose' name='EditHasNoLactose'/>";
    echo "</div>";

    echo "<div class='neptuneRow'>";
        echo "<label style='display:inline;' for='IsVendor'>Takeout:</label>";
        echo "<input style='display:inline;' type='checkbox' id='EditHasTakeout' name='EditHasTakeout'/>";
    echo "</div>";
    echo  $location_info;
    echo "<input type='hidden' name='EditLocation' value='EditLocation'/>";

    echo "<li class='buttons'>";
    echo "<input style='padding:10px;' type='submit' name='Edit_Location_Submit' value='Edit Location'/>";
    echo "</li>";

    echo "</ul>";
    echo "</form>";

    echo "</div>";
    echo "</div>";
}

/**
 * @param $db SQLite3
 * @param $name
 * @param $price
 * @param $itemType
 * @return string
 */
function addLocation( $db, $name, $categoryID, $distanceID ) {
    $name = trim($name);

    $itemCountStatement = $db->prepare("SELECT COUNT(*) as Count FROM Location WHERE Name = :name");
    $itemCountStatement->bindValue( ":name", $name );
    $itemCountResults = $itemCountStatement->execute();

    $itemCountRow = $itemCountResults->fetchArray();
    $numOfExistingItems = $itemCountRow['Count'];

    if( $numOfExistingItems > 0 ) {
        return "Location \"$name\" already exists.";
    } else {
        $statement = $db->prepare( "INSERT INTO Location (Name, CategoryID, DistanceID) VALUES " .
            "( :name, :categoryID, :distanceID )" );

        $statement->bindValue( ":name", $name );
        $statement->bindValue( ":categoryID", $categoryID );
        $statement->bindValue( ":distanceID", $distanceID );

        $statement->execute();
    }
}

/**
 * @param $db SQLite3
 * @param $itemType
 * @param $itemID
 * @param $name
 * @param $price
 * @param $discountPrice
 */
function editLocation( $db, $locationID, $categoryID, $distanceID, $name, $abbr, $punchline, $description, $latitude, $longitude, $hasVegan, $hasVegetarian, $hasNoGluten, $hasNoLactose, $hasTakeout ) {

    $updateMenuName = "";
    $targetMenuFileName = "";

    if ( isset( $_FILES['uploadedMenu']['tmp_name'] ) && is_uploaded_file($_FILES['uploadedMenu']['tmp_name'] ) ) {
        $targetMenuFileName = "menu_" . $locationID. "_" . basename( $_FILES['uploadedMenu']['name'] );
        $target = "menus/" . $targetMenuFileName;
        if( !move_uploaded_file( $_FILES['uploadedMenu']['tmp_name'], $target ) ) {
            error_log(" THERE WAS AN ERROR UPLOADING THIS THUMBNAIL: " . $_FILES['uploadedMenu']['tmp_name'] );
        } else {
            $updateMenuName = ", MenuFileName = :menuFileName";
        }
    }

    $updateSQL = "UPDATE Location SET CategoryID=:categoryID $updateMenuName, " .
        "DistanceID = :distanceID, Name =:name, Abbreviation = :abbr, Punchline = :punchline, Description = :description, Latitude = :latitude, Longitude = :longitude, " .
        "HasVegan = :hasVegan, HasVegetarian = :hasVegetarian, HasNoGluten = :hasNoGluten, HasNoLactose = :hasNoLactose, HasTakeout = :hasTakeout where LocationID = :locationID";

    $statement = $db->prepare( $updateSQL );

    error_log("UPDATE[$updateSQL ($locationID $categoryID $distanceID)]" );
    $statement->bindValue( ":locationID", $locationID );
    $statement->bindValue( ":categoryID", $categoryID );
    $statement->bindValue( ":distanceID", $distanceID );
    $statement->bindValue( ":name", $name );
    $statement->bindValue( ":abbr", $abbr );
    $statement->bindValue( ":punchline", $punchline );
    $statement->bindValue( ":description", $description );
    $statement->bindValue( ":latitude", $latitude );
    $statement->bindValue( ":longitude", $longitude );
    $statement->bindValue( ":hasVegan", $hasVegan );
    $statement->bindValue( ":hasVegetarian", $hasVegetarian );
    $statement->bindValue( ":hasNoGluten", $hasNoGluten );
    $statement->bindValue( ":hasNoLactose", $hasNoLactose );
    $statement->bindValue( ":hasTakeout", $hasTakeout );
    $statement->bindValue( ":menuFileName", $targetMenuFileName, SQLITE3_TEXT );
    $statement->execute();
}
?>

<script type='text/javascript'>
$( document ).ready( function() {
    $('#EditLocation').change(function () {
        setItemInfo();
    });

    setItemInfo();
});

function setItemInfo() {

    var locationID = parseInt($('#EditLocation').val());
    var categoryID = $('#Location_CategoryID_' + locationID).val();
    var distanceID = $('#Location_DistanceID_' + locationID).val();
    var name = $('#Location_Name_' + locationID).val();
    var abbr = $('#Location_Abbreviation_' + locationID).val();
    var punchline = $('#Location_Punchline_' + locationID).val();
    var description = $('#Location_Description_' + locationID).val();
    var latitude = $('#Location_Latitude_' + locationID).val();
    var longitude = $('#Location_Longitude_' + locationID).val();
    var menuName = $('#Location_MenuFileName_' + locationID).val();
    var hasVegan = $('#Location_HasVegan_' + locationID).val();
    var hasVegetarian  = $('#Location_HasVegetarian_' + locationID).val();
    var hasNoGluten = $('#Location_HasNoGluten_' + locationID).val();
    var hasNoLactose = $('#Location_HasNoLactose_' + locationID).val();
    var hasTakeout = $('#Location_HasTakeout_' + locationID).val();

    $("#EditCategory").val(categoryID);
    $("#EditDistance").val(distanceID);
    $("#EditName").val(name);
    $("#EditAbbr").val(abbr);
    $("#EditPunchline").val(punchline);
    $("#EditDescription").val(description);
    $("#EditLatitude").val(latitude);
    $("#EditLongitude").val(longitude);
    $("#EditMenuName").html(menuName);

    $("#EditHasVegan").prop("checked", hasVegan != 0 );
    $("#EditHasVegetarian").prop("checked", hasVegetarian != 0 );
    $("#EditHasNoGluten").prop("checked", hasNoGluten != 0 );
    $("#EditHasNoLactose").prop("checked", hasNoLactose != 0 );
    $("#EditHasTakeout").prop("checked", hasTakeout != 0 );
}
</script>
