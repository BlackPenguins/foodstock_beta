<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
include( CSS_PATH );
include( "appendix.php" );

function main( $url, $itemType, $className, $location ) {
    include( HEADER_PATH );
?>

<script>
    var itemsInCart = [];
    
    function updateCardArea(itemTypeValue, classNameValue, locationValue, isMobileValue, itemSearchValue) {
        console.log("Updating Card Area with [" + itemSearchValue + "]...");
        $.post("<?php echo AJAX_LINK; ?>", { 
                type:'CardArea',
                itemType:itemTypeValue,
                className:classNameValue,
                location:locationValue,
                isMobile:isMobileValue,
                itemSearch:itemSearchValue,
            },function(data) {
                $('#card_area').html(data);
        });
    }

    function reportItemOutOfStock(user, itemID, itemName) {
        $isOutOfStock = confirm('Are you sure that you, ' + user + ', want to report "' + itemName + '" as out of stock?');
        
        if ( $isOutOfStock ) {
            alert("Thank you. Matt Miles has been notified about " + itemName + ".");

            $.post("<?php echo AJAX_LINK; ?>", { 
                type:'OutOfStockRequest',
                reporter:user,
                itemID:itemID,
                itemName:itemName
            },function(data) {
                // Do nothing right now
            });
        }
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
            $('#remove_button_' + itemID).removeClass('btn-disabled');
            $('#remove_button_' + itemID).addClass('btn- <? echo $itemType; ?>');
        }

        if( newQuantity == maxQuantity ) {
            $('#add_button_' + itemID).addClass('btn-disabled');
            $('#add_button_' + itemID).removeClass('btn- <? echo $itemType; ?>');
        }
        
        $.post("<?php echo AJAX_LINK; ?>", { 
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
            $('#remove_button_' + itemID).addClass('btn-disabled');
            $('#remove_button_' + itemID).removeClass('btn- <? echo $itemType; ?>');
        }

        if( newQuantity == maxQuantity - 1 ) {
            $('#add_button_' + itemID).removeClass('btn-disabled');
            $('#add_button_' + itemID).addClass('btn- <? echo $itemType; ?>');
        }
        
        $.post("<?php echo AJAX_LINK; ?>", { 
                type:'DrawCart',
                items:JSON.stringify(itemsInCart),
                url:'<?php  echo $url; ?>'
            },function(data) {
                $('#cart_area').html(data);
        });
    }
</script>
<?php

// ------------------------------------
// FANCY ITEM TABLE
// ------------------------------------
echo "<div style='margin-bottom:20px; margin-left:5px;'>";

if( !$isMobile ) {
    echo "<span><b><a href='http://penguinore.net/sodastock.php'>Bookmark Us! Tell your friends!</a></b><br><span style='font-size:10px;'>Only the ones at RSA because I'm not selling this anywhere else.</span></span>";
}


echo "</div>";

$results = $db->query("SELECT Income, Expenses, ProfitExpected, ProfitActual, FirstDay FROM Information WHERE ItemType ='" . $itemType . "'");

//---------------------------------------
// BUILD TOP SECTION STATS
//---------------------------------------
if(!$isMobile) {
    $version = "Version 5.2 (November 17th, 2018)";

    $total_income = 0;
    $total_expense = 0;

    $row = $results->fetchArray();
    $total_income = $row['Income'];
    $total_expense = $row['Expenses'];
    $total_profit = $total_income - $total_expense;
    $total_income_actual = $row['ProfitActual']; // This is actually the INCOME - NOT PROFIT
    $dateNow = new DateTime();
    $firstDay = DateTime::createFromFormat('Y-m-d H:i:s', $row['FirstDay']);
    
    $time_since = $dateNow->diff($firstDay);
    $days_ago = $time_since->format('%a');

    $profitPerDay = $total_profit / $days_ago;

    echo "<div style='margin: auto;'>";
    
    echo "<table>";
    echo "<tr>";
    echo "<td style='color:#000000; font-weight:bold; background-color:#efa217; padding:5px; border: #000 2px solid;'><a href='#change_log'>$version</a></td>";
    echo "<td style='color:black; background-color:#FFFFFF; padding:5px 15px; border: #000 2px solid;'><b>Profit / Day:</b> $". number_format($profitPerDay, 2)."</td>";
    echo "<td style='color:black; background-color:#B888FF; padding:5px 15px; border: #000 2px solid;'><b>Days Active: </b>". $days_ago ." days</td>";
    
    if( $isLoggedInAdmin ) {
        echo "<td>&nbsp;</td>";
        echo "<td style='text-align:right; font-weight:bold;'>Calculated:</td>";
        echo "<td style='color:black; background-color:#90EE90; padding:5px 15px; border: #000 2px solid;'><b>Income:</b> $". number_format($total_income, 2)."</td>";
        echo "<td style='color:black; background-color:#EBEB59; padding:5px 15px; border: #000 2px solid;'><b>Profit:</b> $". number_format($total_profit, 2)."</td>";
        echo "<td rowspan='2' style='color:black; background-color:#EE4545; padding:5px 15px; border: #000 2px solid;'><b>Expenses:</b> $". number_format($total_expense, 2)."</td>";
    }
    echo "</tr>";
    
    if( $isLoggedInAdmin ) {
        echo "<tr>";
        echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
        echo "<td style='text-align:right; font-weight:bold;'>Payments:</td>";
        echo "<td style='color:black; background-color:#ebb159; padding:5px 15px; border: #000 2px solid;'><b>Income:</b> $". number_format($total_income_actual, 2)."</td>";
        $actualProfit = $total_income_actual - $total_expense;
        echo "<td style='color:black; background-color:#EBEB59; padding:5px 15px; border: #000 2px solid;'><b>Profit:</b> $". number_format($actualProfit, 2)."</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div></div>";
}

echo "<div id='cart_area' style='margin:20px; padding:10px; color:#FFFFFF; background-color:#2f2f2f; border: 3px #8e8b8b dashed;'>";
echo "<div style='display:flex; align-items:center;'>";
echo "<img width='40px' src='" . IMAGES_LINK . "handle_with_care.png'/>&nbsp;Remember to pick up your product first and have it physically in your hand before you buy on the website to avoid buying something that was recently all bought out by someone else.";
echo "</div>";

echo "<div style='display:flex; align-items:center;'>";
echo "<img width='40px' src='" . IMAGES_LINK . "sale.png'/>&nbsp;Discounted prices are only available when you buy through the site.";
echo "</div>";
echo "</div>";
    
if( !$isMobile && $itemType != "Snack" ) {
    $results = $db->query("SELECT ID, Name, ShelfQuantity, DateModified, ThumbURL, Hidden FROM Item WHERE Type ='" . $itemType . "' AND Hidden != 1 ORDER BY DateModified DESC");
    
    echo "<div style='margin:20px; padding:10px; background-color:#2f2f2f; border: 3px #8e8b8b dashed;'>";
    echo "<div style='color:#8e8b8b; font-weight:bold; padding-bottom:10px;'>The Shelf <span style='font-size:0.7em;'>(currently in the $location)</span></div>";
    $lastUpdated = "";
    while ($row = $results->fetchArray()) {
        $name = $row['Name'];
        $shelf = $row['ShelfQuantity'];
        
        if( $lastUpdated == "") {
            $lastUpdated = $row['DateModified'];
        }
        
        if( $shelf > 0 ) {
            for($i = 0; $i < $shelf; $i++) {
                DisplayShelfCan($name, $row['ThumbURL']);
            }
        }
    }


    $current_date = new DateTime();
    $ago_text = DisplayAgoTime($lastUpdated, $current_date);
            
    echo "<div style='color:#8e8b8b; padding-top:10px;'><b>Last Updated:</b> $ago_text</div>";
    echo "</div>";
}

echo "<div style='font-size:1.2em; font-weight:bold; margin-left:30px;'><input placeholder='Search Items' autofocus type='text' style='padding:5px; border-radius:20px; font-size:1.6em;' onChange=\"updateCardArea('$itemType', '$className', '$location', '$isMobile', this.value );\"/></div>";
echo "<div id='card_area'>";
echo "<script>updateCardArea('$itemType', '$className', '$location', '$isMobile', '' );</script>";
echo "</div>";

if( !$isMobile) {
    echo "<div style='clear:both;'></div>";

    echo "<div id='change_log' class='" . $className . "_popout' style='margin:10px; padding:5px;'><span style='font-size:26px;'>Change Log</span></div>";
    echo "<ul>";
    echo "<li><b>Nov 17, 2018:</b> Added Audit and Defectives admin pages. Moved and reduced size of search box. Added 'Google Pay' as supported payment. Refactored the entire code base - organized by directories, centralized the URLs (appendix), removed unused code, removed duplicate code by reusing a 'header' page, indentation, renamed page names.</li>";
    echo "<li><b>Oct 24, 2018:</b> Complete redesign of cards with a more modern look. Removed 'Search' label. Credit goes towards <a href='https://codepen.io/andytran/pen/BNjymy'>Andy Tran</a> and <a href='https://codepen.io/roydigerhund/pen/OMreoV'>Matthias Martin</a> for taking elements from both of their UI designs and tweaking them to work with my site.</li>";
    echo "<li><b>Oct 20, 2018:</b> Added graphs to stats page and ability to set date range. Ability to undo anything (refunds on purchases, payments, inventory, restock). Improved sorting on main page so discontinued and sold out snacks don't appear at the top. Inventory Form - Added incrementers and 'unit changed' colors, removed Price column. Restock Form - improved UI, multiplier. Shopping Guide - order by Cost Each. Added 'Expiration Date' column to items. Misc bug fixes.</li>";
    echo "<li><b>Aug 5, 2018:</b> Added FoodStockBot. Show cash-only totals in Billing (Ryan ask). Added 'Alias' for items (people couldn't find the Spicy Snacks). Redesigned 'Methods of Payment' section with accounts. Divided request modals into 3 separate modal/buttons. Sort requests by completion. Added the start of the stats page. Slack notifications when item inventory reaches zero (Nick ask). Attempted to fix rounding issues with negative $0 balances. ADMIN: Sorted inventory by quantity, added bot automatically notifying all users of payment owed at first of month, formatted phone numbers.</li>";
    echo "<li><b>Jul 29, 2018:</b> Reorganized directories and resources. Divided up Admin into separate pages. Fixed massive income bugs and miscountings.</li>";
    echo "<li><b>Jul 2, 2018:</b> Fixed many security vulnerabilities (thanks to Joe Guest for finding those). Prevent inactive users from ordering in case they want to login after leaving RSA (looking at you Aaron). Added more slack notifications: new users and out of stock. Added plural unit name DB column (english language sucks).</li>";
    echo "<li><b>Jul 1, 2018:</b> Redesigned the Billing page so it's easier to read and combined the soda and snack into one page. Marked the quantity of item with warning icon if someone reported it as out of stock. Added billing to top bar, removed it from Purchase History page. Divided purchase history by weeks, added day of week to date, labeled 'Cash-Only' purchases. Admin: Created dropdown for 'Method' and added 'Payment Month' to payment form.</li>";
    echo "<li><b>Jun 29, 2018:</b> Admin Changes: Message feedback, side bar for navigation. Added edit user: change slackID, set inactive, reset password.</li>";
    echo "<li><b>Jun 28, 2018:</b> Added 'Report Out of Stock' button (Nick C request). Saved space on cards by making statistics into icons.</li>";
    echo "<li><b>Apr 1, 2018:</b> Added Billing section (in Purchase History) for monthly statements and records of payments. Added 'total purchases' statistic to the Register Link. Clicking the Version at the top now jumps you to the change log.</li>";
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