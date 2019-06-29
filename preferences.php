<?php
    include( "appendix.php" );
    
    $url = PREFERENCES_LINK;
    include( HEADER_PATH );
//    include_once( LOG_FUNCTIONS_PATH );
?>

<?php
    $results = $db->query("SELECT AnonName, ShowDiscontinued, ShowCashOnly, ShowCredit, SubscribeRestocks, ShowItemStats, ShowShelf, ShowTrending FROM User where UserID = " . $_SESSION['UserID'] );
    $row = $results->fetchArray();
    $showDiscontinuedChecked = $row['ShowDiscontinued'] == 1 ? "checked" : "";
    $showCashOnlyChecked = $row['ShowCashOnly'] == 1 ? "checked" : "";
    $showCreditChecked = $row['ShowCredit'] == 1 ? "checked" : "";
    $showTrendingChecked = $row['ShowTrending'] == 1 ? "checked" : "";
    $subscribeRestocksChecked = $row['SubscribeRestocks'] == 1 ? "checked" : "";
    $showItemStatsChecked = $row['ShowItemStats'] == 1 ? "checked" : "";
    $showShelf = $row['ShowShelf'] == 1 ? "checked" : "";
    $anonAnimal = $row['AnonName'];

    echo "<div class='rounded_header'><span  class='title'>User Preferences</span></div>";
    echo "<form id='preferences_form' id='edit_user_form' class='fancy' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
    echo "<fieldset style='padding:0px 20px 20px 20px; font-size:1.2em; color:#000; border:3px solid #a0b113; background-color:#fffac0;'>";

    echo "<label for='SlackID'>Anonymous Animal</label>";
    echo "<input type='text' style='margin-bottom:5px;' value='$anonAnimal' id='Preferences_AnonAnimal' name='Preferences_AnonAnimal' class='text ui-widget-content ui-corner-all'/>";
    echo "<div style='font-size:0.9em; color:#3e3f3d; line-height:15px; margin-bottom:20px;'>This is the name you appear as to other people in the graphs to keep anonymity. Should I let you be able to change this? Will you abuse it? Nah...</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showItemStatsChecked type='checkbox' id='Preferences_ShowItemStats' name='Preferences_ShowItemStats'/>";
    echo "<label style='display:inline;' for='Preferences_ShowItemStats'>Show Item Statistics</label>";
    echo "<div style='font-size:0.9em; color:#3e3f3d; line-height:15px; margin-bottom:20px;'>Show the statistic boxes below each item on Soda Home and Snack Home pages - your purchase count, the average number of days for restock (popularity), and the total sold.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showDiscontinuedChecked type='checkbox' id='Preferences_ShowDiscontinued' name='Preferences_ShowDiscontinued'/>";
    echo "<label style='display:inline;' for='Preferences_ShowDiscontinued'>Show Discontinued Items</label>";
    echo "<div style='font-size:0.9em; color:#3e3f3d; line-height:15px; margin-bottom:20px;'>Show the discontinued items in the list on the Soda Home and Snack Home pages.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showCashOnlyChecked type='checkbox' id='Preferences_ShowCashOnly' name='Preferences_ShowCashOnly'/>";
    echo "<label style='display:inline;' for='Preferences_ShowCashOnly'>Show Cash-Only Option</label>";
    echo "<div style='font-size:0.9em; color:#3e3f3d; line-height:15px; margin-bottom:20px;'>Show the cash-only option in the cart.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showCreditChecked type='checkbox' id='Preferences_ShowCredit' name='Preferences_ShowCredit'/>";
    echo "<label style='display:inline;' for='Preferences_ShowCredit'>Show Credit</label>";
    echo "<div style='font-size:0.9em; color:#3e3f3d; line-height:15px; margin-bottom:20px;'>Show the yellow Credit box in the navigation bar. If you have credits this setting is ignored and it's shown anyway.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showShelf type='checkbox' id='Preferences_ShowShelf' name='Preferences_ShowShelf'/>";
    echo "<label style='display:inline;' for='Preferences_ShowShelf'>Show Shelf</label>";
    echo "<div style='font-size:0.9em; color:#3e3f3d; line-height:15px; margin-bottom:20px;'>Show the shelf (at the top of the Soda Home page) that displays a quick view of what is currently in the freezer. Each image can be clicked and add one to your cart.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $subscribeRestocksChecked type='checkbox' id='Preferences_SubscribeRestocks' name='Preferences_SubscribeRestocks'/>";
    echo "<label style='display:inline;' for='Preferences_SubscribeRestocks'>Subscribe to Restocks</label>";
    echo "<div style='font-size:0.9em; color:#3e3f3d; line-height:15px; margin-bottom:20px;'>Have restock information sent to you via FoodstockBot. The same message that would appear in the #random channel.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showTrendingChecked type='checkbox' id='Preferences_ShowTrending' name='Preferences_ShowTrending'/>";
    echo "<label style='display:inline;' for='Preferences_ShowTrending'>Show Trending</label>";
    echo "<div style='font-size:0.9em; color:#3e3f3d; line-height:15px; margin-bottom:20px;'>Show the green trending box at the top of the item list page. This box will tell you which items are selling the most for the current day.</div>";
    echo "</div>";

    echo "<input type='hidden' name='Preferences' value='Preferences'/><br>";
    echo "<input type='hidden' name='redirectURL' value='" . PREFERENCES_LINK . "'/><br>";


    echo "<input class='ui-button' style='padding:10px; text-align:center; width:100%;' type='submit' name='Save_Preferences' value='Save Preferences'/><br>";

    echo "</fieldset>";
    echo "</form>";
    echo "</div>";
?>

</body>