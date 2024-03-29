<?php
    include( "appendix.php" );
    
    $url = PREFERENCES_LINK;
    include( HEADER_PATH );
//    include_once( LOG_FUNCTIONS_PATH );
?>

<?php
    $statement = $db->prepare("SELECT AnonName, ShowDiscontinued, ShowCashOnly, ShowCredit, SubscribeRestocks, ShowItemStats, ShowShelf, ShowTrending, HideCompletedRequests FROM User where UserID = :userID"  );
    $statement->bindValue( ":userID", $_SESSION['UserID'] );
    $results = $statement->execute();

    $row = $results->fetchArray();
    $showDiscontinuedChecked = $row['ShowDiscontinued'] == 1 ? "checked" : "";
    $showCashOnlyChecked = $row['ShowCashOnly'] == 1 ? "checked" : "";
    $showCreditChecked = $row['ShowCredit'] == 1 ? "checked" : "";
    $showTrendingChecked = $row['ShowTrending'] == 1 ? "checked" : "";
    $hideCompletedRequests = $row['HideCompletedRequests'] == 1 ? "checked" : "";
    $subscribeRestocksChecked = $row['SubscribeRestocks'] == 1 ? "checked" : "";
    $showItemStatsChecked = $row['ShowItemStats'] == 1 ? "checked" : "";
    $showShelf = $row['ShowShelf'] == 1 ? "checked" : "";
    $anonAnimal = $row['AnonName'];

    echo "<div class='page_header'><span  class='title'>User Preferences</span></div>";
    echo "<form id='preferences_form' id='edit_user_form' class='inline_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
    echo "<fieldset>";

    echo "<label for='SlackID'>Anonymous Animal</label>";
    echo "<input type='text' style='margin-bottom:5px;' value='$anonAnimal' id='Preferences_AnonAnimal' name='Preferences_AnonAnimal' class='text ui-widget-content ui-corner-all'/>";
    echo "<div class='helptext'>This is the name you appear as to other people in the graphs to keep anonymity. Should I let you be able to change this? Will you abuse it? Nah...</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showItemStatsChecked type='checkbox' id='Preferences_ShowItemStats' name='Preferences_ShowItemStats'/>";
    echo "<label style='display:inline;' for='Preferences_ShowItemStats'>Show Item Statistics</label>";
    echo "<div class='helptext'>Show the statistic boxes below each item on Soda Home and Snack Home pages - your purchase count, the average number of days for restock (popularity), and the total sold.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showDiscontinuedChecked type='checkbox' id='Preferences_ShowDiscontinued' name='Preferences_ShowDiscontinued'/>";
    echo "<label style='display:inline;' for='Preferences_ShowDiscontinued'>Show Discontinued Items</label>";
    echo "<div class='helptext'>Show the discontinued items in the list on the Soda Home and Snack Home pages.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showCashOnlyChecked type='checkbox' id='Preferences_ShowCashOnly' name='Preferences_ShowCashOnly'/>";
    echo "<label style='display:inline;' for='Preferences_ShowCashOnly'>Show Cash-Only Option</label>";
    echo "<div class='helptext'>Show the cash-only option in the cart.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showCreditChecked type='checkbox' id='Preferences_ShowCredit' name='Preferences_ShowCredit'/>";
    echo "<label style='display:inline;' for='Preferences_ShowCredit'>Show Credit</label>";
    echo "<div class='helptext'>Show the yellow Credit box in the navigation bar. If you have credits this setting is ignored and it's shown anyway.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showShelf type='checkbox' id='Preferences_ShowShelf' name='Preferences_ShowShelf'/>";
    echo "<label style='display:inline;' for='Preferences_ShowShelf'>Show Shelf</label>";
    echo "<div class='helptext'>Show the shelf (at the top of the Soda Home page) that displays a quick view of what is currently in the freezer. Each image can be clicked and add one to your cart.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $subscribeRestocksChecked type='checkbox' id='Preferences_SubscribeRestocks' name='Preferences_SubscribeRestocks'/>";
    echo "<label style='display:inline;' for='Preferences_SubscribeRestocks'>Subscribe to Restocks</label>";
    echo "<div class='helptext'>Have restock information sent to you via FoodstockBot. The same message that would appear in the #random channel.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $showTrendingChecked type='checkbox' id='Preferences_ShowTrending' name='Preferences_ShowTrending'/>";
    echo "<label style='display:inline;' for='Preferences_ShowTrending'>Show Trending</label>";
    echo "<div class='helptext'>Show the green trending box at the top of the item list page. This box will tell you which items are selling the most for the current day.</div>";
    echo "</div>";

    echo "<div style='padding:5px 0px;'>";
    echo "<input style='display:inline;' $hideCompletedRequests type='checkbox' id='Preferences_HideCompletedRequests' name='Preferences_HideCompletedRequests'/>";
    echo "<label style='display:inline;' for='Preferences_HideCompletedRequests'>Hide Completed Requests</label>";
    echo "<div class='helptext'>Hide the green completed requests on the Requests page.</div>";
    echo "</div>";

    echo "<input type='hidden' name='Preferences' value='Preferences'/><br>";
    echo "<input type='hidden' name='redirectURL' value='" . PREFERENCES_LINK . "'/><br>";


    $disabledButton = IsInactive() ? " disabled " : "";
    echo "<input class='button' $disabledButton type='submit' name='Save_Preferences' value='Save Preferences'/><br>";

    echo "</fieldset>";
    echo "</form>";
    echo "</div>";
?>

</body>