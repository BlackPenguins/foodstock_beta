<?php 
    include(__DIR__ . "/../appendix.php" );
    DisplayNav($db, $url);

    echo "</div>";

    /**
     * @param $db SQLite3
     * @param $url
     */
    function DisplayNav($db, $url) {
        $noMobileSupport = "<span class='unsupported_mobile'>(no mobile support yet)</span>";
        echo "<div id='navigation_bar'>";

        if( IsAdminLoggedIn() ) {
            echo "<span id='hamburger_admin' onclick='openNavAdmin();'>";

            echo "<svg style='width:26px; height: 26px;' aria-hidden='true' focusable='false' role='presentation' viewBox='0 0 26 26'>";
            echo "<path style='fill:#90ff6c;' d='M 13 2 C 11.894531 2 11 2.894531 11 4 C 11 4.777344 11.445313 5.449219 12.09375 5.78125 L 8 14 L 3.53125 10.28125 C 3.820313 9.933594 4 9.488281 4 9 C 4 7.894531 3.105469 7 2 7 C 0.894531 7 0 7.894531 0 9 C 0 10.105469 0.894531 11 2 11 C 2.136719 11 2.277344 10.996094 2.40625 10.96875 L 4.09375 19 L 21.90625 19 L 23.59375 10.96875 C 23.722656 10.996094 23.863281 11 24 11 C 25.105469 11 26 10.105469 26 9 C 26 7.894531 25.105469 7 24 7 C 22.894531 7 22 7.894531 22 9 C 22 9.488281 22.179688 9.933594 22.46875 10.28125 L 18 14 L 13.90625 5.78125 C 14.554688 5.449219 15 4.777344 15 4 C 15 2.894531 14.105469 2 13 2 Z M 4 21 L 4 22.5 C 4 23.328125 4.671875 24 5.5 24 L 20.5 24 C 21.328125 24 22 23.328125 22 22.5 L 22 21 Z'></path>";
            echo "</svg>";

            echo "</span>";

            echo "<span id='close_admin' onclick='closeNavAdmin();'>";

            echo "<svg  style='width:32px; height: 32px;' viewPort='0 0 33 33' version='1.1' xmlns='http://www.w3.org/2000/svg'>";
            echo "<line x1='13' y1='27' x2='32' y2='10' stroke='white' stroke-width='3'/>";
            echo "<line x1='13' y1='10' x2='32' y2='27' stroke='white' stroke-width='3'/>";
            echo "</svg>";

            echo "</span>";
        }

        DisplayLoggedIn("mobile" );

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

        echo "<li><a style='text-decoration:none;' href='" . HELP_LINK . "'><span class='nav_buttons nav_buttons_help'><span id='help_text'>?</span><span id='help_text_mobile'>Help</span></span></a></li>";
        echo "<li><a style='text-decoration:none;' href='" . SODASTOCK_LINK . "'><span class='nav_buttons nav_buttons_soda'>Soda Home</span></a></li>";
        echo "<li><a style='text-decoration:none;' href='" . SNACKSTOCK_LINK . "'><span class='nav_buttons nav_buttons_snack'>Snack Home</span></a></li>";

        if(!IsLoggedIn()) {
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
                echo "<li><a style='text-decoration:none;' onclick='closeNav();' href='#Requests'><span style='font-size:0.7em; background-color: #8e67a7;' class='nav_buttons nav_buttons_requests'>Requests</span></a><li>";
                echo "<li><a style='text-decoration:none;' onclick='closeNav();' href='#Feature Requests'><span style='font-size:0.7em; background-color: #8e67a7;' class='nav_buttons nav_buttons_requests'>Feature Requests</span></a><li>";
                echo "<li><a style='text-decoration:none;' onclick='closeNav();' href='#Bug Reports'><span style='font-size:0.7em; background-color: #8e67a7;' class='nav_buttons nav_buttons_requests'>Bug Reports</span></a><li>";
            }
            echo "<li><a style='text-decoration:none;' href='" . STATS_LINK . "'><span class='nav_buttons nav_buttons_stats'>Graphs $noMobileSupport</span></a><li>";

            if( IsAdminLoggedIn() ) {
                echo "<li><a style='text-decoration:none;' href='" . ADMIN_LINK . "'><span class='nav_buttons nav_buttons_admin'>Administration</span></a><li>";
            }

            if( IsVendor() ) {
                echo "<li><a style='text-decoration:none;' href='" . VENDOR_LINK . "'><span class='nav_buttons nav_buttons_admin'>Vendor HQ</span></a><li>";
            }

            echo "<li><a style='text-decoration:none;' href='" . PREFERENCES_LINK . "'><span class='nav_buttons nav_buttons_preferences'>Preferences</span></a></li>";

            $totalBalance = $_SESSION['SodaBalance'] + $_SESSION['SnackBalance'];
            $credits = $_SESSION['Credits'];

            $creditsGreyedOut = "";

            if( $credits <= 0 ) {
                $creditsGreyedOut = "opacity: 0.45;";
            }

            if( $credits > 0 || $_SESSION['ShowCredit'] == 1 ) {
                echo "<li><a style='text-decoration:none; $creditsGreyedOut' href='" . PURCHASE_HISTORY_LINK . "'><span class='nav_buttons nav_buttons_admin'>Credits: " . getPriceDisplayWithDollars($credits) . "$noMobileSupport</span></a><li>";
            }

            echo "<li><a style='text-decoration:none;' href='" . PURCHASE_HISTORY_LINK . "'><span class='nav_buttons nav_buttons_billing'>Balance: " .  getPriceDisplayWithDollars( $totalBalance ) . "$noMobileSupport</span></a><li>";

            if( IsAdminLoggedIn() ) {
                $refillCount = getRefillCount($db);
                $restockCount =  getRestockCount($db);
                $refillText = "";
                $restockText = "";

                if( $refillCount > 0 ) {
                    $refillText .= "<a style='text-decoration:none;' href='" . ADMIN_CHECKLIST_LINK . "'><span class='nav_buttons nav_buttons_desk_refill'>";
                    $refillText .= "Desk Refill ($refillCount)";
                    $refillText .= "</span>";
                }

                if( $restockCount > 0 ) {
                    $restockText .= "<a style='text-decoration:none;' href='" . ADMIN_CHECKLIST_LINK . "'><span class='nav_buttons nav_buttons_store_restock'>";
                    $restockText .= "Store Restock ($restockCount)";
                    $restockText .= "</span>";
                }

                if( $refillText != "" || $restockText != "" ) {
                    echo "<li><span style='margin-left: 105px;'>$refillText $restockText</span></a><li>";
                }
            }
        }
        echo "</ul>";

        DisplayLoggedInUser($url);

        echo "</nav>";
    }

    function DisplayLoggedInUser($url) {

        echo "<span id='credentials_box'>";
        DisplayLoggedIn("desktop" );
        DisplayLoginForm($url);
        echo "</span>";
    }

    function DisplayLoggedIn($id) {
        if(IsLoggedIn()) {
            $inactiveUser = isset( $_SESSION['InactiveUser'] ) && $_SESSION['InactiveUser'] == 1;
            $userColor = $inactiveUser ? "#ee3636" : "#FFFF00";
            echo "<span id='display_name_$id'>";
            echo "Logged in: <b><span style='color:$userColor;'>[" . $_SESSION['UserName'] . "]";

            if( IsAdminLoggedIn() ) {
                echo " - Administrator";
            }

            if( $inactiveUser ) {
                echo " - Inactive";
            }

            echo "</span></b>";
            echo "</span>";
            DisplayLoggedOutLink($id);
        }
    }
    function DisplayLoginForm($url) {
        if(!IsLoggedIn()) {
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

    function DisplayLoggedOutLink($id) {
        if(IsLoggedIn()) {
            echo "<span id='logout_link_$id' title='" . $_SESSION['SlackID'] . "' style='padding:0px 10px 0px 10px;'>";
            echo "<b><a style='color:white;' href='" . LOGOUT_LINK . "'>[Logout]</a></b>";
            echo "</span>";
        }
    }
?>

<script>
function openNav() {
    closeNavAdmin();
    $("#nav").addClass("open-nav");
    $("#hamburger").hide();
    $("#close").show();
}

function closeNav() {
    $("#nav").removeClass("open-nav");
    $("#hamburger").show();
    $("#close").hide();
}

function openNavAdmin() {
    closeNav();
    $("#nav_admin").addClass("open-nav-admin");
    $("#hamburger_admin").hide();
    $("#close_admin").show();
}

function closeNavAdmin() {
    $("#nav_admin").removeClass("open-nav-admin");
    $("#hamburger_admin").show();
    $("#close_admin").hide();
}
</script>
