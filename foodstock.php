<head>
<meta name="viewport" content="width=device-width, initial-scale = 1.0,maximum-scale=1.0">

<?php
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
    
    function addItemToCart(itemID, isMobile) {
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
                isMobile:isMobile,
                url:'<?php  echo $url; ?>'
            },function(data) {
                $('#cart_area').html(data);
        });
    }

    function removeItemFromCart(itemID, isMobile) {
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
                isMobile:isMobile,
                url:'<?php  echo $url; ?>'
            },function(data) {
                $('#cart_area').html(data);
        });
    }

    function breakBulb(bulb) {
        $(bulb).addClass('dead');
    }

    function setVersionCookie( version ) {
        document.cookie = "version_viewed=" + version + "; expires=Fri, 1 Mar 2041 12:00:00 UTC";
    }

    var totalTimeLeft = 300;
    
    var warningTimer = null;

    function startReminderTimer() {
        warningTimer = setInterval(function() {
            totalTimeLeft--;

            var minutes = Math.floor( totalTimeLeft / 60 );
            var seconds = totalTimeLeft - (minutes * 60);

            if( minutes < 10 ) { minutes = "0" + minutes; }
            if( seconds < 10 ) { seconds = "0" + seconds; }

            $('#warning_time').html( minutes + ":" + seconds );

            if( totalTimeLeft == 0 ) {
                $("body").append("<div id='overlay' style='background-color:#000000; opacity:0.85; z-index:4000; width:100%; height: 100%; position:fixed; top:0; bottom:0; right:0; left:0;'>&nbsp;</div>");
                $("body").append("<div id='bringing_down_the_hammer' style='padding:20px; border:5px #666f18 solid; background-color:#d0c21d; z-index:6000; width:550px; height: 255px; margin-top:-93px; margin-left:-250px; position:fixed; top:50%; left:50%;'>" +
                    "<div style='text-align:center; font-size:3em; margin-bottom:15px; text-decoration:underline;'>Friendly Reminder</div>" +
                    "<div style='text-align:center; font-size:1.3em;'>" +
                    "This site has been open for 5 minutes without a purchase.<br><br>Did you forget to pay for something?" +
                    "</div>" +
                    "<div style='margin-top:10px; color: #931515; text-align:center; font-size: 0.7em'>I'm missing quite a bit of money for both soda and snacks.</u> So after 5 minutes of being idle the site will now ask if you forgot to pay for something. If you think you might have forgotten to pay between grabbing the item and making it back to your desk to pay you can now open the site before you leave and use this feature as a friendly reminder.</div>" +
                    "<div style='text-align:center;'><button style='font-size:1.7em; margin-top:20px;' onclick='$(\"#overlay\").hide(); $(\"#bringing_down_the_hammer\").hide();'>Close</button></div>" +
                    "</div>");
//             alert("Friendly Reminder: The site has been open for 5 minutes without a purchase. Did you forget to pay?");
                clearInterval(warningTimer);
            }
        }, 1000);
    }
</script>
<?php
// ------------------------------------
// FANCY ITEM TABLE
// ------------------------------------

//echo "<div style='margin-bottom:5px; margin-left:5px;'>";

if( !$isMobile ) {
    // echo "<span><b><a href='http://penguinore.net/sodastock.php'>Bookmark Us! Tell your friends!</a></b><br><span style='font-size:10px;'>Only the ones at RSA because I'm not selling this anywhere else.</span></span>";
}


echo "</div>";

$results = $db->query("SELECT Income, Expenses, ProfitExpected, ProfitActual, FirstDay FROM Information WHERE ItemType ='" . $itemType . "'");

//---------------------------------------
// BUILD TOP SECTION STATS
//---------------------------------------
if(!$isMobile) {
    $version = "6.2";
    $versionString = "Version $version !!! (May 28th, 2019)";

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
    
    echo "<table style='margin:0px 20px'>";
    echo "<tr>";
    echo "<td rowspan='2'><img src='" . IMAGES_LINK . "logo.jpg'/></td>";

    $newIcon = "";
    $versionClass = "version_old";


    if ( !isset( $_COOKIE["version_viewed"] ) || isset( $_COOKIE["version_viewed"] ) && $_COOKIE["version_viewed"] != $version ) {
        $newIcon = "<img style='vertical-align:middle; padding: 0px 5px;' width='32px' src='" .  IMAGES_LINK . "new.png'/>";
        $versionClass = "version";
    }

    echo "<td class='$versionClass'>";
    echo "$newIcon <a onclick='setVersionCookie(\"" . $version . "\");' href='#change_log'>$versionString</a>$newIcon ";
    echo "</td>";

    if( $isLoggedInAdmin ) {
        echo "<td style='color:black; background-color:#FFFFFF; padding:5px 15px; border: #000 2px solid;'><b>Profit / Day:</b> " . getPriceDisplayWithDollars($profitPerDay) . "</td>";
    }

    echo "<td style='color:black; background-color:#B888FF; padding:5px 15px; border: #000 2px solid;'><b>Days Active: </b>". $days_ago ." days</td>";
    echo "<td style='color:black; background-color:#fffa5c; padding:5px 15px; border: #000 2px solid;' title='Purchase Reminder - After being idle for five minutes without a purchase it will ask if you forgot to pay for something.'>";
    echo "<img style='vertical-align:middle;' width='30px' src='" . IMAGES_LINK . "timer.png'/>&nbsp;<span style='font-weight:bold;' id='warning_time'>-</span>";
    echo "</td>";

    if( $isLoggedInAdmin ) {
        echo "<td>&nbsp;</td>";
        echo "<td style='text-align:right; font-weight:bold;'>Calculated:</td>";
        echo "<td style='color:black; background-color:#90EE90; padding:5px 15px; border: #000 2px solid;'><b>Income:</b> ". getPriceDisplayWithDollars( $total_income )."</td>";
        echo "<td style='color:black; background-color:#EBEB59; padding:5px 15px; border: #000 2px solid;'><b>Profit:</b> ". getPriceDisplayWithDollars( $total_profit )."</td>";
        echo "<td style='color:black; background-color:#EE4545; padding:5px 15px; border: #000 2px solid;'><b>Expenses:</b> ". getPriceDisplayWithDollars( $total_expense )."</td>";
    }
    echo "</tr>";
    
    if( $isLoggedInAdmin ) {
        echo "<tr>";
        echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
        echo "<td style='text-align:right; font-weight:bold;'>Payments:</td>";
        echo "<td style='color:black; background-color:#ebb159; padding:5px 15px; border: #000 2px solid;'><b>Income:</b> ". getPriceDisplayWithDollars( $total_income_actual )."</td>";
        $actualProfit = $total_income - $total_income_actual;
        $actualDebt = $total_income_actual - $total_expense;
        echo "<td style='color:black; background-color:#EBEB59; padding:5px 15px; border: #000 2px solid;'><b>Owed Money:</b> ". getPriceDisplayWithDollars( $actualProfit )."</td>";
        echo "<td style='color:black; background-color:#EE4545; padding:5px 15px; border: #000 2px solid;'><b>Actual Debt:</b> ". getPriceDisplayWithDollars( $actualDebt )."</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div></div>";
}

echo "<div id= 'container_squisher'>";

if( isset( $_SESSION['PurchaseCompleted'] ) ) {
    unset( $_SESSION['PurchaseCompleted'] );
} else if( IsLoggedIn() ) {
    echo "<script>startReminderTimer();</script>";
}

echo "<div id='cart_area' class='cart_area' style='position:relative; margin-bottom:5px; padding:10px; color:#000000; background-color:#FFFFFF; border: 3px #8e8b8b solid;'>";
echo "<div style='display:flex; align-items:center;'>";
echo "<img width='40px' src='" . IMAGES_LINK . "handle_with_care.png'/>&nbsp;Remember to pick up your product first and have it physically in your hand before you buy on the website to avoid buying something that was recently all bought out by someone else.";
echo "</div>";

echo "<div style='display:flex; align-items:center;'>";
echo "<img width='40px' src='" . IMAGES_LINK . "sale.png'/>&nbsp;Discounted prices are only available when you buy through the site.";
echo "</div>";
echo "</div>";

$showShelf = true;
if( isset( $_SESSION['ShowShelf'] ) && $_SESSION['ShowShelf'] == 0 ) {
    $showShelf = false;
}

if( !$isMobile && $itemType != "Snack" && $showShelf ) {
    $results = $db->query("SELECT ID, Name, ShelfQuantity, DateModified, ThumbURL, Hidden FROM Item WHERE Type ='" . $itemType . "' AND Hidden != 1 ORDER BY DateModified DESC");
    
    echo "<div style='margin:20px; padding:10px; background-color:#2f2f2f; border: 3px #8e8b8b solid;'>";
    echo "<div style='color:#8e8b8b; font-weight:bold; padding-bottom:10px;'>The Shelf <span style='font-size:0.7em;'>(currently in the $location, clicking these will also add one to your cart)</span></div>";
    $lastUpdated = "";
    while ($row = $results->fetchArray()) {
        $name = $row['Name'];
        $id = $row['ID'];
        $shelf = $row['ShelfQuantity'];
        $thumbURL = $row['ThumbURL'];
        
        if( $lastUpdated == "") {
            $lastUpdated = $row['DateModified'];
        }
        
        if( $shelf > 0 ) {
            for($i = 0; $i < $shelf; $i++) {
                DisplayShelfCan($id, $name, $thumbURL );
            }
        }
    }

    $current_date = new DateTime();

    $ago_text = DisplayAgoTime($lastUpdated, $current_date);
            
    echo "<div style='color:#8e8b8b; padding-top:10px;'><b>Last Updated:</b> $ago_text...</div>";
    echo "</div>";
}

echo "<input placeholder='Search Items' autofocus type='text' style='padding:5px; border-radius:20px; font-size:1.6em;' onChange=\"updateCardArea('$itemType', '$className', '$location', '$isMobile', this.value );\"/>";

echo "<div id='card_area'>";
echo "<script>updateCardArea('$itemType', '$className', '$location', '$isMobile', '' );</script>";
echo "</div>";

if( !$isMobile) {

    global $adminClass;
    global $requestClass;
    global $dbClass;
    global $bugClass;

    $requestClass = 'color:#6328bd; font-weight:bold;';
    $adminClass = 'color:#bd2828; font-weight:bold;';
    $dbClass = 'color:#0f6d28; font-weight:bold;';
    $bugClass = 'color:#ed561d; font-weight:bold;';

    function DisplayUpdate( $date, $itemType, $changes ) {
        $backgroundColor = "#c8e2ff";

        if( $itemType == "Snack" ) {
            $backgroundColor = "#ffdedc";
        }

        echo "<li style='margin: 20px 0px; background-color:$backgroundColor; padding:15px; border:1px solid #000;'><b>$date:</b>";
        echo "<ul style='margin-top:10px;'>";
        foreach( $changes as $change ) {
            echo "<li>$change</li>";
        }
        echo "</ul>";
        echo "</li>";
    }

    function DisplayItem($type, $message) {
        global $adminClass;
        global $requestClass;
        global $dbClass;
        global $bugClass;
        $style = "";

        if( $type == "admin" ) {
            $style = "style='$adminClass'";
        } else if( $type == "request" ) {
            $style = "style='$requestClass'";
        } else if( $type == "db" ) {
            $style = "style='$dbClass'";
        } else if( $type == "bug" ) {
            $style = "style='$bugClass'";
        }

        return "<span $style>$message</span>";
    }


    echo "<div style='clear:both;'></div>";

    $milestoneClass= "background-color: #f1ff1a; padding:5px; margin: 20px 0px; border:solid 3px #8c8e1d;";

    
    echo "<div id='change_log' class='rounded_header'><span class='title'>Change Log <span style='font-size: 0.7em; margin-left: 20px;'>(<span style='$requestClass'>Requests in Purple</span> | <span style='$adminClass'>Admin Changes in Red</span> | <span style='$dbClass'>Database and Server Changes in Green</span> | <span style='$bugClass'>Bug Fixes in Orange</span>)</span></span></div>";
    echo "<ul style='margin:0px 40px 0px 0px; list-style-type: none;'>";

    // CREDIT: https://icons8.com/icon/2270/crown - CDN77
    // https://i2kplay.com/icon-new/

    // <div>Icons made by <a href="https://www.flaticon.com/authors/roundicons" title="Roundicons">Roundicons</a> from <a href="https://www.flaticon.com/" 			    title="Flaticon">www.flaticon.com</a> is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" 			    title="Creative Commons BY 3.0" target="_blank">CC 3.0 BY</a></div>

    DisplayUpdate("May 28, 2019 (6.2)", $itemType, array(
        DisplayItem("request", "Added Help section for new users (ask by Mike P)."),
        DisplayItem("request", "Added Preferences page to make the UI simpler. By default for new users almost everything is off. For current users everything is left on except the shelf, cash-only, and subscriptions. You can now show/hide cash-only, discontinued items, credit, item stats, the shelf. You can configure your anon animal for graphs."),
        DisplayItem("none", "Buying and selling credits are now in a slack notification to the user (like a receipt)."),
        DisplayItem("none", "Five minute reminder will not appear if you have already purchased something - or if you are logged out."),
        DisplayItem("none", "Five minute warning is condensed to just a timer in the top (to save space). The explanation of the timer has been moved to the popup itself and it's a hover-text of the timer."),
        DisplayItem("none", "Profit/Day statistic is no longer shown. It's been incorrect."),
        DisplayItem("none", "Added the ability to subscribe to restock messages. The same ones that are sent to #random."),
        DisplayItem("bug", "Fixed bug where the discount prices were not used in graphs and cancelled purchases WERE being used."),
        DisplayItem("bug", "Credits are no longer counted towards the balance in Billing Page."),
        DisplayItem("bug", "Opening a side menu in mobile with another one already open will close the current one. Clicking the requests sub-pages will close the side menu in mobile."),
        DisplayItem("bug", "Checklist was affecting both lists when checking off an item."),
        DisplayItem("admin", "Checklist has ajax refresh for items being checked off."),
        DisplayItem("admin", "First and Last Names used in slack notifications, instead of username."),
        DisplayItem("admin", "Added Google Pay as payment method."),
        DisplayItem("admin", "Bot persists fields between page refreshes."),
    ) );

    DisplayUpdate("May 19, 2019 (6.1)", $itemType, array(
        DisplayItem("request", "Version box will display a NEW icon (Icon credit: https://i2kplay.com/icon-new/) with a bright blue background for updates you haven't seen. Once you click the link it will clear out the 'new' status via a cookie and use a dark blue background (inspired by Nick). Rainbow box is gone."),
        DisplayItem("none", "Added 'bug-fixes' as category to change log."),
        DisplayItem("request", "Choosing 'cash-only' in cart will hide the total and credit amount since it's not relevant (Nick ask)."),
        DisplayItem("none", "Credits used are now shown in receipt. Total is not shown unless it is above $0 (the case where you didn't have enough credits to cover the purchase)."),
        DisplayItem("none", "Payment method is now shown in the slack message."),
        DisplayItem("none", "Running low trigger for soda is now 1 can instead of 3 cans (since usually it's a max of only 3 cans). Snacks are still 3 items for a trigger."),
        DisplayItem("none", "Submitting a request now works in mobile."),
        DisplayItem("bug", "Fixed bug where cash-only purchases tried using credits when it should be using balance."),
        DisplayItem("bug", "Fixed bug where credits were contributing towards the balance on purchase history page. Credit totals are now shared with the cash only total."),
        DisplayItem("bug", "Fixed bug where viewing links on other people's purchase histories was broken."),
        DisplayItem("bug", "Fixed bug where stats page was still in whole cents (it said $1400 instead of $14)."),
        DisplayItem("admin", "Most of Admin has mobile support and working forms. Admin pages are in a left side hamburger button (Crown icon credit: https://icons8.com/icon/2270/crown - CDN77.)"),
        DisplayItem("admin", "Checklist has checkboxes that can be checked off and save until inventory is done."),
        DisplayItem("admin", "Users who have credits are in a yellow box."),
        DisplayItem("admin", "Admin pages has its own favicon."),


    ) );

    // Admin, DB, Request, None
    DisplayUpdate("May 5, 2019 (6.0 - THE BIG ONE)", $itemType, array(
        DisplayItem("none", "This obnoxious rainbow cycle animation for the version box."),
        DisplayItem("db", "Changed every single price in the DB (about 14,000 of them across 22 db columns - please...no bugs) from dollars to whole cents. Example: $1.58 --> 158 cents. This solves the problem of floating point mathematical bugs (those negative balances) because there are no more decimals. Instead everything is converted to a decimal string using several centralized money string format functions just before it is displayed in the UI."),
        DisplayItem("request", "Added Credits (new yellow box in the header): the ability to buy credits upfront and use those for purchases instead. The cart has been updated to mimic Amazon's handing of amazon points. Once you run out the rest of the cost will go towards your balance. There is no option to opt-out of using the credits for the cart if there are any left (unlike Amazon). This means less needing to pay a balance at the end of the month if you bought enough credits to handle the month. So you could technically buy $40 of credits and not need to make a payment for about 5 months. There is also the ability to refund credits as well for the co-ops who leave and want their money back. Future uses of this could be sending credits to each other. (John T and Nick ask)"),
        DisplayItem("none", "Combined Snack and Soda amounts into one in the header, admin pages, slack messages, and purchase history page. With the addition of credits having both balances would have been confusing. And besides me, do you really care where your purchases are going?"),
        DisplayItem("none", "RIP Billing page. It has been removed and condensed into the Purchase History page. The only place that it will tell you the soda/snack balance breakdown will be on this page."),
        DisplayItem("none", "Payment History - the improved page has the billing section at the top in a cleaner table. The page will only show the information for one month at a time. You can select a month to view by clicking a link from the Billing section at the top. The detailed information for a month will only display for the current month. All months that require payment will always show - the rest can be collapsed. Payment method is now shown for each payment."),
        DisplayItem("none", "Purchase History - the improved page has the same Purchase History at the bottom. The purchase and refund of credits are shown in the purchase history. The amount of credits used for each purchase (partial too) is shown on the right side of each item in the table. The week divider now includes the Year. The Date column has been moved to the first column to mimic the Chase Bank transaction page."),
        DisplayItem("none", "Wrote my own logging methods (because PHP's sucks) so the error_log only has errors and everything else goes into own category (payment, sql, debug, slack). Now if that error_log is full I'll know something is wrong. Mimicked the RSA logging discussion and have the logs roll based on a 5 MB limit wth the date placed in the rolled log's filename."),
        DisplayItem("bug", "BUG-FIX: SodaBot now marks something as 'running low' (yellow) instead of 'out of stock' (red)."),
        DisplayItem("request", "Clicking an item in the shelf adds one to your cart (Nick and Christian ask)"),
        DisplayItem("request", "Added Rick and Mike P. milestones below. (Nick ask)"),
        DisplayItem("request", "Added 'Quick' and 'In Progress' statuses to request page."),
        DisplayItem("none", "Using Nick's the_nerd_herd_test slack channel for testing instead of SlackBot."),
        DisplayItem("db", "Created upgrade process/zip script - no more manual moving of php files."),
        DisplayItem("admin", "Migration Page: Ability to upgrade a database (add columns, change values) without manual work."),
        DisplayItem("db", "Renamed the database to test_db for the testing server so that when I copy over files I cant accidentally overwrite the production database with the testing one."),
        DisplayItem("admin", "Simplified the payments page into just 'total balance' and 'money owed'. Removed old payment modal. Made most of new modal read-only using retrieved payment information from that month."),
        DisplayItem("admin", "Started created automated tests. The crushinator has a rival."),
        DisplayItem("admin", "Audit page has new label explaining missing money."),
        DisplayItem("admin", "Less intrusive 'Restock' notification badge."),

    ) );

    DisplayUpdate("Apr 7, 2019 (5.9)", $itemType, array(
        DisplayItem("none", "Cleaned up CSS with tables."),
        DisplayItem("admin", "New Page: Checklist. Notifications for refills and restocks. Automatic setting of triggers with purchases."),
        DisplayItem("admin", "New Page: Audit. Calculates the amount of money that should be in the mug for each refill and reports loss."),
        DisplayItem("admin", "Changed the rest of the tables to the new look."),
        DisplayItem("none", "Christian Easter Egg ;)"),
        DisplayItem("bug", "Fixed bug with priority order in Requests page."),
        DisplayItem("none", "Added 'Check All Items' to Stats page. Displays the date of last purchase for each item to determine what to discontinue."),
    ) );

    DisplayUpdate("Mar 24, 2019 (5.8)", $itemType, array(
            DisplayItem("none", "Greatly improved mobile support! New hamburger icon opens a navigation drawer. Many pages have been improved for mobile and replaced with CSS tags. Fixed the horizontal scroll glitch on mobile where everything didn't fit on the screen slightly."),
            DisplayItem("db", "Database: Current flavor: For items like poptarts and muffins it will now display which flavor(s) are being sold (in restock slack messages too)."),
            DisplayItem("admin", "Admin: Upload image and thumbnails through administration. No more hardcoded paths so images can easily being updated and there will be less missing images."),
            DisplayItem("none", "After some feedback, lowered the cart timer from 15 minutes to 5 minutes."),
            DisplayItem("none", "Incorrect password is now a user message."),
    ) );
    
    DisplayUpdate("Mar 4, 2019 (5.7)", $itemType, array(
            DisplayItem("admin", "Admin: Proxy as users for testing."),
            DisplayItem("none", "Enhanced the cart (just copied the look of Amazon.com)"),
            DisplayItem("none", "Added user message for completed purchase."),
            DisplayItem("none", "After 15 minutes there is a pop-up reminding you that you might have forgotten to pay for something."),
    ) );
    
    DisplayUpdate("Feb 25, 2019 (5.7)", $itemType, array(
            DisplayItem("none", "Larger labels in all modals and inputs."),
            DisplayItem("none", "Delayed 'out of stock' notification."),
            DisplayItem("none", "Added 'priority' and 'completion date' to Requests."),
            DisplayItem("none", "Unassigned default priority."),
            DisplayItem("none", "Color-coded priority."),
            DisplayItem("none", "Display price in restock slack message."),
            DisplayItem("none", "Password and password field on one line."),
            DisplayItem("none", "WebCRD-like information messages instead of alerts."),
            DisplayItem("none", "Stats default to previous year instead of random month last year, hiding newer users."),
            DisplayItem("none", "(YOU) now displays in line graph."),
    ) );
    
    DisplayUpdate("Jan 28, 2019 (5.6)", $itemType, array(
            DisplayItem("none", "New, easier to read, table styles on Purchase History and Request pages."),
            DisplayItem("none", "Darker background colors - less neon, amateur like."),
            DisplayItem("none", "Added 'priority' and 'completion date' to Requests."),
            DisplayItem("none", "Expanded Requests button into sub-buttons (Request, Features, Bugs)."),
    ) );
    
    
    DisplayUpdate("Jan 21, 2019 (5.5)", $itemType, array(
            DisplayItem("none", "New Statistic! Display the number of purchases of a certain item per month and on hover show who bought them that month. Finds trends in why items stop selling. Maybe a co-op left and they were the only ones buying it."),
            DisplayItem("none", "Improved the clarity of the 'Foodstock Collection Agency' message to only include money owed for that particular month."),
            DisplayItem("none", "Items about to be discontinued are now purple and have a quantity shown."),
            DisplayItem("bug", "BUG-FIX: Discontinued items were not all at the bottom of the page (because they were bought by the user at some point)."),
            DisplayItem("admin", "Admin: Improved the Payment page and added 'Notify' buttons for each month."),
            DisplayItem("admin", "Admin: Added IsCoop (to divide up the user list) and AnonName (to hide real names from the statistics) settings to each user. Each user will now have their own anonymous name (for the public) assigned to them instead of being randomized."),
    ) );
    
    DisplayUpdate("Jan 14, 2019 (5.4)", $itemType, array(
            DisplayItem("none", "Christmas theme removed."),
            DisplayItem("bug", "BUG FIX: Plural labels in Inventory slack announcement."),
            DisplayItem("none", "Fixed the floating point number arithmetic bug. A similar bug that actually <a href='https://en.wikipedia.org/wiki/MIM-104_Patriot#Failure_at_Dhahran'>killed 28 soldiers in 1991</a> I learned from Stack Overflow. My bug was...less tragic."),
    ) );
    
    DisplayUpdate("Nov 24, 2018 (5.3)", $itemType, array(
            DisplayItem("none", "Christmas theme added. Credit for Christmas Lights goes to <a href='https://codepen.io/tobyj/pen/QjvEex'>Toby</a> and wreath icon goes to <a href='https://www.freepik.com/' title='Freepik'>Freepik</a> from <a href='https://www.flaticon.com/' title='Flaticon'>www.flaticon.com</a> under the <a href='http://creativecommons.org/licenses/by/3.0/' title='Creative Commons BY 3.0' >CC 3.0 BY License</a>."),
            DisplayItem("none", "Clicking the bulbs breaks them."),
            DisplayItem("none", "Soda section goes green for the season."),
            DisplayItem("none", "Removed 'seconds' from purchase history dates."),
    ) );
    
    DisplayUpdate("Nov 17, 2018 (5.2)", $itemType, array(
            DisplayItem("none", "Improved the readability of this change log, added two milestones, color-coded changes."),
            DisplayItem("none", "Designed the \"Thirsty\" mascot and logo."),
            DisplayItem("admin", "Admin: Added Audit and Defectives pages."),
            DisplayItem("none", "Moved and reduced size of search box."),
            DisplayItem("request", "Request by Frank: Added 'Google Pay' as supported payment"),
            DisplayItem("db", "Directories: Refactored the entire code base - organized into directories, centralized the URLs (appendix), removed unused code, removed duplicate code by reusing a 'header' page, indentation, renamed page names.</span>")
    ) );
    
    DisplayUpdate("Oct 24, 2018 (5.1)", $itemType, array(
            DisplayItem("none", "Complete redesign of cards with a more modern look. Credit goes towards <a href='https://codepen.io/andytran/pen/BNjymy'>Andy Tran</a> and <a href='https://codepen.io/roydigerhund/pen/OMreoV'>Matthias Martin</a> for taking elements from both of their UI designs and tweaking them to work with my site."),
            DisplayItem("none", "Removed 'Search' label."),
    ) );
    
    DisplayUpdate("Oct 20, 2018", $itemType, array(
            DisplayItem("none", "Added graphs to stats page and ability to set date range."),
            DisplayItem("admin", "Admin: Ability to undo anything (refunds on purchases, payments, inventory, restock)."),
            DisplayItem("none", "Improved sorting on main page so discontinued and sold out snacks don't appear at the top."),
            DisplayItem("admin", "Admin: Inventory Form - Added incrementers and 'unit changed' colors, removed Price column."),
            DisplayItem("admin", "Admin: Restock Form - improved UI, multiplier."),
            DisplayItem("admin", "Admin: Shopping Guide - order by Cost Each."),
            DisplayItem("db", "DB: Added 'Expiration Date' column to items."),
            DisplayItem("bug", "Misc bug fixes."),
    ) );
    
    DisplayUpdate("Aug 5, 2018", $itemType, array(
            DisplayItem("none", "Added FoodStockBot."),
            DisplayItem("request", "Request by Ryan: Show cash-only totals in Billing."),
            DisplayItem("none", "Added 'Alias' for items (people couldn't find the Spicy Snacks)."),
            DisplayItem("admin", "Admin: Redesigned 'Methods of Payment' section with accounts."),
            DisplayItem("none", "Divided request modals into 3 separate modal/buttons."),
            DisplayItem("none", "Sort requests by completion."),
            DisplayItem("none", "Added the start of the stats page."),
            DisplayItem("request", "Request by Nick: Slack notifications when item inventory reaches zero."),
            DisplayItem("none", "Attempted to fix rounding issues with negative $0 balances."),
            DisplayItem("admin", "Admin: Sorted inventory by quantity, added bot automatically notifying all users of payment owed at first of month, formatted phone numbers."),
    ) );
    
    DisplayUpdate("Jul 29, 2018", $itemType, array(
            DisplayItem("db", "Directories: Reorganized directories for resources/images."),
            DisplayItem("admin", "Admin: Divided up Admin into separate pages."),
            DisplayItem("bug", "Fixed massive income bugs and miscountings.")
    ) );
    
    DisplayUpdate("Jul 2, 2018", $itemType, array(
            DisplayItem("none", "Fixed many security vulnerabilities (thanks to Joe Guest for finding those)."),
            DisplayItem("none", "Prevent inactive users from ordering in case they want to login after leaving RSA (looking at you Aaron)."),
            DisplayItem("none", "Added more slack notifications: new users and out of stock."),
            DisplayItem("db", "DB: Added plural unit name DB column (english language sucks).")
    ) );

    DisplayUpdate("Jul 1, 2018", $itemType, array(
            DisplayItem("none", "Redesigned the Billing page so it's easier to read and combined the soda and snack into one page."),
            DisplayItem("none", "Marked the quantity of item with warning icon if someone reported it as out of stock."),
            DisplayItem("none", "Added billing to top bar, removed it from Purchase History page."),
            DisplayItem("none", "Divided purchase history by weeks, added day of week to date, labeled 'Cash-Only' purchases."),
            DisplayItem("admin", "Admin: Created dropdown for 'Method' and added 'Payment Month' to payment form.")
    ) );
    
    DisplayUpdate("Jun 29, 2018", $itemType, array(
            DisplayItem("admin", "Admin: Message feedback, side bar for navigation."),
            DisplayItem("admin", "Admin: Added edit user: change slackID, set inactive, reset password."),
    ) );
    
    DisplayUpdate("Jun 28, 2018", $itemType, array(
            DisplayItem("request", "Request By Nick: Added 'Report Out of Stock' button."),
            DisplayItem("none", "Saved space on cards by making statistics into icons."),
    ) );
    
    DisplayUpdate("Apr 1, 2018", $itemType, array(
            DisplayItem("none", "Added Billing section (in Purchase History) for monthly statements and records of payments."),
            DisplayItem("none", "Added 'total purchases' statistic to the Register Link."),
            DisplayItem("none", "Clicking the Version at the top now jumps you to the change log."),
    ) );
    
    DisplayUpdate("Mar 28, 2018", $itemType, array(
            DisplayItem("none", "Added 'Feature' and 'Bug' request types."),
            DisplayItem("none", "Divided Feature, Bug, and Requests into different sections."),
            DisplayItem("none", "Ability to mark requests as completed."),
    ) );
    
    DisplayUpdate("Mar 22, 2018", $itemType, array(
            DisplayItem("none", "Added discount prices - shown in the page, the purchase history, and the cart."),
            DisplayItem("none", "Show total savings and spent in purchase history."),
            DisplayItem("none", "Show total savings across all users in register link."),
            DisplayItem("none", "Striped tables (might need better colors)."),
            DisplayItem("none", "Added password confirmation to register page."),
    ) );
    
    DisplayUpdate("Mar 11, 2018", $itemType, array(
            DisplayItem("none", "Built Admin, Requests, and Purchase History pages."),
            DisplayItem("none", "Added Payments."),
            DisplayItem("none", "Display the number of times you bought an item in card."),
            DisplayItem("request", "Request by Nick: Order cards by the most bought (Favorites)."),
            DisplayItem("none", "Added Nav Buttons to top bar: Soda Home, Snack Home, Requests, Purchase History, Admin."),
            DisplayItem("db", "DB: Sped up home page by removing forms and many unnecessary SQL queries."),
            DisplayItem("none", "Added slack notifications for payments, requests, receipts, restocks - with specific emojis and bot names."),
            DisplayItem("none", "Split balances into soda balance and snack balance."),
            DisplayItem("request", "Request by Nick: Cash only option in cart allows you to decrement the quantity without adding total to your balance because you paid in change/cash."),
            DisplayItem("none", "Added ability to submit requests and view others' requests."),
    ) );
    
    DisplayUpdate("Mar 3, 2018", $itemType, array(
            DisplayItem("db", "Server: Site was moved to Vultr."),
            DisplayItem("none", "Added missing snack and soda images."),
    ) );
    
    DisplayUpdate("Mar 2, 2018", $itemType, array(
            DisplayItem("none", "Tabs and balances are now online. Items can be purchased through the site."),
            DisplayItem("none", "Card UI was improved a little."),
    ) );
    
    echo "<div style='$milestoneClass'><b>MILESTONE:</b> Site is now usable by other people.</div>";
    
    DisplayUpdate("Feb 16, 2018", $itemType, array(
            DisplayItem("none", "Started selling snacks - created SnackStock&trade;."),
            DisplayItem("db", "DB: Storing images and unit names in DB."),
    ) );

    echo "<div style='$milestoneClass'><b>MILESTONE:</b> Mike P stops supplying snacks and hands the duty over to me. (Feb 2017)</div>";
    
    DisplayUpdate("Jan 22, 2017", $itemType, array(
            DisplayItem("db", "I have no Idea what this was: Bunch of changes. TBA."),
    ) );
    
    DisplayUpdate("Nov 10, 2016", $itemType, array(
            DisplayItem("none", "Lower opacity for sodas that are sold out."),
            DisplayItem("none", "Added red text that says sold out."),
            DisplayItem("none", "Added 'container type' labels (bottles/cans/packets)."),
    ) );
    
    DisplayUpdate("Jul 1, 2016", $itemType, array(
            DisplayItem("none", "Created the card layout."),
            DisplayItem("none", "Old table layout can be found <a href='sodastock_table.php'>here</a>."),
    ) );
    
    DisplayUpdate("Jun 7, 2016", $itemType, array(
            DisplayItem("none", "Added 'days active' statistic."),
    ) );
    
    DisplayUpdate("Jun 5, 2016", $itemType, array(
            DisplayItem("none", "Added 'Email' button to email inventory counts."),
    ) );
    
    DisplayUpdate("Oct 28, 2015", $itemType, array(
            DisplayItem("none", "Added 'profit per day' statistic."),
    ) );
    
    DisplayUpdate("Oct 2, 2015", $itemType, array(
            DisplayItem("none", "Added change to cursor when hovering over cells that has hover text."),
    ) );
    
    DisplayUpdate("Oct 1, 2015", $itemType, array(
            DisplayItem("none", "Hid sold-out soda in 'Daily Amount' modal."),
            DisplayItem("none", "Added show/hide toggle for restock/inventory sections on home page."),
    ) );
    
    DisplayUpdate("Aug 28, 2015", $itemType, array(
            DisplayItem("none", "Re-ordered sodas by stock quantity. Sold out sodas are at the end."),
    ) );
    
    DisplayUpdate("Jul 22, 2015", $itemType, array(
            DisplayItem("none", "Removed tiny warm/cold can icons."),
            DisplayItem("none", "Added 'Last Store Purchase' and 'Avg Store Purchase'."),
    ) );
    
    DisplayUpdate("Jul 10, 2015", $itemType, array(
            DisplayItem("none", "Added ability to discontinue sodas."),
    ) );
    
    DisplayUpdate("Feb 16, 2015", $itemType, array(
            DisplayItem("none", "SodaStock&trade; goes live. Legacy SodaStock is <a href='https://docs.google.com/spreadsheets/d/16BSupau6vEIfGY_-mgvz0_dzTeiJPysl3Kt-80fr8Hc/edit?usp=sharing'>here</a>."),
    ) );
    
    echo "<div style='$milestoneClass'><b>MILESTONE:</b> Google Sheets replaced with website and a database.</div>";
    
    DisplayUpdate("Nov 11, 2014", $itemType, array(
            DisplayItem("none", "Started selling soda at RSA."),
    ) );

    echo "<div style='$milestoneClass'><b>MILESTONE:</b> Rick Schreiber leaves RSA: the original supplier of Soda. (Jan 2014)</div>";

    echo "</ul>";
}
//include("sodastock_charts.php");
$db->close();
}
?>
</div>
</body>