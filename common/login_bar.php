<?php 
    include(__DIR__ . "/../appendix.php" );
    DisplayNav($db, $isLoggedIn, $isLoggedInAdmin, $url);

    echo "</div>";

    function DisplayNav($db, $isLoggedIn, $isLoggedInAdmin, $url) {
        $noMobileSupport = "<span class='unsupported_mobile'>(no mobile support yet)</span>";
        echo "<div id='navigation_bar'>";

        DisplayLoggedIn("mobile", $isLoggedIn, $isLoggedInAdmin);

        echo "<span id='hamburger' onclick='openNav();'>";

        echo "<svg style='width:32px; height: 32px;' aria-hidden='true' focusable='false' role='presentation' viewBox='0 0 37 40'>";
        echo "<path style='fill:#FFFFFF;' d='M33.5 25h-30c-1.1 0-2-.9-2-2s.9-2 2-2h30c1.1 0 2 .9 2 2s-.9 2-2 2zm0-11.5h-30c-1.1 0-2-.9-2-2s.9-2 2-2h30c1.1 0 2 .9 2 2s-.9 2-2 2zm0 23h-30c-1.1 0-2-.9-2-2s.9-2 2-2h30c1.1 0 2 .9 2 2s-.9 2-2 2z'></path>";
        echo "</svg>";

        echo "</span>";

        echo "<span id='close' onclick='closeNav();'>";

        echo "<svg  style='width:32px; height: 32px;' viewPort='0 0 33 33' version='1.1' xmlns='http://www.w3.org/2000/svg'>";
        echo "<line x1='13' y1='27' x2='32' y2='10' stroke='white' stroke-width='3'/>";
        echo "<line x1='13' y1='10' x2='32' y2='27' stroke='white' stroke-width='3'/>";
        echo "</svg>";

        echo "</span>";

        echo "<nav id='nav' role='navigation'>";
        echo "<ul>";

        echo "<li><a style='text-decoration:none;' href='" . SODASTOCK_LINK . "'><span class='nav_buttons nav_buttons_soda'>Soda Home</span></a></li>";
        echo "<li><a style='text-decoration:none;' href='" . SNACKSTOCK_LINK . "'><span class='nav_buttons nav_buttons_snack'>Snack Home</span></a></li>";

        if(!$isLoggedIn) {
            $results = $db->query("SELECT Count(*) as Active, Sum(SnackSavings) as TotalSnackSavings, Sum(SodaSavings) as TotalSodaSavings FROM User WHERE SnackBalance != 0.0 OR SodaBalance != 0");
            $row = $results->fetchArray();
            $totalActiveUsers = $row['Active'];
            $totalSnackSavings = $row['TotalSnackSavings'];
            $totalSodaSavings = $row['TotalSodaSavings'];
            $totalSavings = $totalSnackSavings + $totalSodaSavings;

            $results = $db->query("SELECT Count(*) as Total FROM Purchase_History WHERE Cancelled IS NULL");
            $row = $results->fetchArray();
            $totalPurchases = $row['Total'];

            echo "<li><span id='register_box'>";
            echo "<a class='register' href='" . REGISTER_LINK . "'>Register for a Discount!<a/> (We have $totalActiveUsers active users with a total of " . getPriceDisplayWithDollars( $totalSavings ) . " in savings and " . $totalPurchases . " total purchases)";
            echo "</span></li>";

        } else {
            echo "<li><a style='text-decoration:none;' href='" . REQUESTS_LINK . "'><span class='nav_buttons nav_buttons_requests'>Requests</span></a></li>";

            if( $url == REQUESTS_LINK ) {
                echo "<li><a style='text-decoration:none;' href='#Requests'><span style='font-size:0.7em; background-color: #8e67a7;' class='nav_buttons nav_buttons_requests'>Requests</span></a><li>";
                echo "<li><a style='text-decoration:none;' href='#Feature Requests'><span style='font-size:0.7em; background-color: #8e67a7;' class='nav_buttons nav_buttons_requests'>Feature Requests</span></a><li>";
                echo "<li><a style='text-decoration:none;' href='#Bug Reports'><span style='font-size:0.7em; background-color: #8e67a7;' class='nav_buttons nav_buttons_requests'>Bug Reports</span></a><li>";
            }
            echo "<li><a style='text-decoration:none;' href='" . STATS_LINK . "'><span class='nav_buttons nav_buttons_stats'>Graphs $noMobileSupport</span></a><li>";

            if( $isLoggedInAdmin ) {
                echo "<li><a style='text-decoration:none;' href='" . ADMIN_LINK . "'><span class='nav_buttons nav_buttons_admin'>Administration $noMobileSupport</span></a><li>";
            }

            $totalBalance = $_SESSION['SodaBalance'] + $_SESSION['SnackBalance'];
            $credits = $_SESSION['Credits'];

            $creditsGreyedOut = "";

            if( $credits <= 0 ) {
                $creditsGreyedOut = "opacity: 0.45;";
            }

            echo "<li><a style='text-decoration:none; $creditsGreyedOut' href='" . PURCHASE_HISTORY_LINK . "'><span class='nav_buttons nav_buttons_admin'>Credits: " .  getPriceDisplayWithDollars( $credits ) . "</span></a><li>";
            echo "<li><a style='text-decoration:none;' href='" . PURCHASE_HISTORY_LINK . "'><span class='nav_buttons nav_buttons_billing'>Balance: " .  getPriceDisplayWithDollars( $totalBalance ) . "</span></a><li>";

            if( $isLoggedInAdmin ) {
                $refillCount = getRefillCount($db);
                $restockCount =  getRestockCount($db);
                $refillText = "";
                $restockText = "";

                if( $refillCount > 0 ) {
                    $refillText .= "<a style='text-decoration:none;' href='" . ADMIN_CHECKLIST_LINK . "'><span style='padding: 5px 10px; font-size: 0.8em; border: 1px dashed #000000; font-weight: bold; background-color: #f7ff03; color: #b30f0e; margin-left: 20px;'>";
                    $refillText .= "Desk Refill ($refillCount)";
                    $refillText .= "</span>";
                }

                if( $restockCount > 0 ) {
                    $restockText .= "<a style='text-decoration:none;' href='" . ADMIN_CHECKLIST_LINK . "'><span style='padding: 5px 10px; font-size: 0.8em; border: 1px dashed #000000; font-weight: bold; background-color: #953bce; color: #FFFFFF; margin-left: 20px;'>";
                    $restockText .= "Store Restock ($restockCount)";
                    $restockText .= "</span>";
                }

                if( $refillText != "" || $restockText != "" ) {
                    echo "<li><span style='margin-left: 105px;'>$refillText $restockText</span></a><li>";
                }
            }
        }
        echo "</ul>";

        DisplayLoggedInUser($isLoggedIn, $isLoggedInAdmin, $url);

        echo "</nav>";
    }

    function DisplayLoggedInUser($isLoggedIn, $isLoggedInAdmin, $url) {

        echo "<span id='credentials_box'>";
        DisplayLoggedIn("desktop", $isLoggedIn, $isLoggedInAdmin);
        DisplayLoginForm($isLoggedIn, $url);
        echo "</span>";
    }

    function DisplayLoggedIn($id, $isLoggedIn, $isLoggedInAdmin) {
        if($isLoggedIn)
        {
            echo "<span id='display_name_$id'>";
            echo "Logged in: <b><font color ='#FFFF00'>[" . $_SESSION['UserName'] . "]" . ( $isLoggedInAdmin ? " - Administrator" : "" ) . "</font></b>";
            echo "</span>";
            DisplayLoggedOutLink($id, $isLoggedIn);
        }
    }
    function DisplayLoginForm($isLoggedIn, $url) {
        if(!$isLoggedIn) {
            //PHP_SELF = this current php page name (take you back to the current page)
            echo "<form style='margin:0px 5px; padding:0px;' action='$url' method='post' accept-charset='UTF-8'>";

            echo "<span id='username_box'>";
            echo "Username: ";
            echo "<input id='username_input' type='text' name='login_username' id='username' maxlength='40' />";
            echo "</span>";

            echo "<span id='password_box'>";
            echo "Password: ";
            echo "<input id='password_input' type='password' name='login_password' id='password' maxlength='40' />";
            echo "</span>";

            echo "<input type='submit' id='login_button' name='Submit' value='Login' />";
            echo "</label>";
            echo "</form>";
        }
    }

    function DisplayLoggedOutLink($id, $isLoggedIn) {
        if($isLoggedIn) {
            echo "<span id='logout_link_$id' title='" . $_SESSION['SlackID'] . "' style='padding:0px 10px 0px 10px;'>";
            echo "<b><a style='color:white;' href='" . LOGOUT_LINK . "'>[Logout]</a></b>";
            echo "</span>";
        }
    }
?>

<script>
function openNav() {
    $("#nav").addClass("open-nav");
    $("#hamburger").hide();
    $("#close").show();
}

function closeNav() {
    $("#nav").removeClass("open-nav");
    $("#hamburger").show();
    $("#close").hide();
}
</script>
