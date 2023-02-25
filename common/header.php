<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">

<?php
    include_once(HOLIDAY_FUNCTIONS_PATH);
    echo "<link rel='stylesheet' type='text/css' href='" . CSS_LINK . "'/>";
    echo "<link rel='stylesheet' type='text/css' href='" . MOBILE_CSS_LINK . "'/>";
    echo "<link rel='stylesheet' type='text/css' href='" . LIGHTS_CSS_LINK . "'/>";
    echo "<link rel='stylesheet' type='text/css' href='" . THEMES_CSS_LINK . "'/>";

    //-----------------------------------------------------
    // PAGE SPECIFIC INFORMATION
    //-----------------------------------------------------
    $titleName = "SodaStock";
    $isAdminPage = false;
    $isSharedAdminVendorPage = false;
    $bodyClass = getHolidayClass( "soda_body" );

    switch( $url ) {
        case ADMIN_AUDIT_REPORT_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = false;
            $titleName = "Admin - Audit Report";
            $trackingName = "Admin - Audit Report";
            break;
        case ADMIN_WEEKLY_AUDIT_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = false;
            $titleName = "Admin - Weekly Audit";
            $trackingName = "Admin - Weekly Audit";
            break;
        case ADMIN_TESTING_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = false;
            $titleName = "Admin - Automation Testing";
            $trackingName = "Admin - Automation Testing";
            break;
        case ADMIN_BOT_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = false;
            $titleName = "Admin - Bot";
            $trackingName = "Admin - Bot";
            break;
        case ADMIN_DEFECTIVES_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = false;
            $titleName = "Admin - Defectives";
            $trackingName = "Admin - Defectives";
            break;
        case ADMIN_INVENTORY_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = true;
            $titleName = "Admin - Inventory/Purchases";
            $trackingName = "Admin - Inventory/Purchases";
            break;
        case ADMIN_ITEMS_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = true;
            $titleName = "Admin - Items";
            $trackingName = "Admin - Items";
            break;
        case ADMIN_ITEMS_IN_STOCK_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = true;
            $titleName = "Admin - Items in Stock";
            $trackingName = "Admin - Items in Stock";
            break;
        case ADMIN_PAYMENTS_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = false;
            $titleName = "Admin - Payments";
            $trackingName = "Admin - Payments";
            break;
        case ADMIN_RESTOCK_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = true;
            $titleName = "Admin - Restock";
            $trackingName = "Admin - Restock";
            break;
        case ADMIN_SHOPPING_GUIDE_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = true;
            $titleName = "Admin - Shopping Guide";
            $trackingName = "Admin - Shopping Guide";
            break;
        case ADMIN_CHECKLIST_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = false;
            $titleName = "Admin - Checklist";
            $trackingName = "Admin - Checklist";
            break;
        case ADMIN_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = false;
            $titleName = "Admin - Home";
            $trackingName = "Admin - Home";
            break;
        case SODASTOCK_LINK:
            $isAdminPage = false;
            $titleName = "SodaStock";
            $trackingName = "SodaStock";
            break;
        case SNACKSTOCK_LINK:
            $isAdminPage = false;
            $titleName = "SnackStock";
            $trackingName = "SnackStock";
            $bodyClass = getHolidayClass( "snack_body" );
            break;
        case PURCHASE_HISTORY_LINK:
            $isAdminPage = false;
            $titleName = "Purchase History";
            break;
        case REGISTER_LINK:
            $isAdminPage = false;
            $titleName = "Register";
            $trackingName = "Register";
            break;
        case REQUESTS_LINK:
            $isAdminPage = false;
            $titleName = "Requests";
            $trackingName = "Requests";
            break;
        case PREFERENCES_LINK:
            $isAdminPage = false;
            $titleName = "Preferences";
            $trackingName = "Preferences";
            break;
        case HELP_LINK:
            $isAdminPage = false;
            $titleName = "Help";
            $trackingName = "Help";
            break;
        case STATS_LINK:
            $isAdminPage = false;
            $titleName = "Stats";
            break;
        case ITEM_STATS_LINK:
            $isAdminPage = false;
            $titleName = "Item Stats";
            break;
        case VENDOR_LINK:
            $isAdminPage = true;
            $isSharedAdminVendorPage = true;
            $titleName = "Vendor";
            $trackingName = "Vendor";
            break;
        default:
            $isAdminPage = false;
            $trackingName = "Unknown [$url]";
    }
    //-----------------------------------------------------

    include_once( SESSION_FUNCTIONS_PATH );
    include_once( UI_FUNCTIONS_PATH );
    include_once( QUANTITY_FUNCTIONS_PATH );
    include_once( SLACK_FUNCTIONS_PATH );

    $db = getDB();

    date_default_timezone_set('America/New_York');
       
    if( $isAdminPage && isset($_GET['Proxy_x25'])) {
        $proxyUsername = $_GET['Proxy_x25'];
        LoginWithProxy( $db, true, $proxyUsername, "DOES NOT MATTER" );
    } else {
        Login($db);
    }

    if( $isAdminPage && !IsAdminLoggedIn() && !$isSharedAdminVendorPage ) {
        SetUserErrorMessage( "You are not an admin! SHAME! Redirecting you home..." );
        header( "Location:" . SODASTOCK_LINK );
        die();
    }

    echo "<script src='" . JS_COLOR_LINK . "'></script>";
    echo "<script src='" . SETUP_MODALS_LINK . "'></script>";

    echo "<title>$titleName</title>";

    if( $isAdminPage ) {
        echo "<link rel='icon' type='image/png' href='" . IMAGES_LINK . "admin.png' />";
    } else {
        echo "<link rel='icon' type='image/png' href='" . IMAGES_LINK . "soda_can_icon.png' />";
    }
?>
</head>

<?php
    echo "<body class='$bodyClass'>";

    if( $isAdminPage && ( IsAdminLoggedIn() || IsVendor() ) ) {
        include(BUILD_ADMIN_FORMS_PATH);
    }

    include(LOGIN_BAR_PATH);
    
    TrackVisit($db, $trackingName);

    DisplayMessages();
    
    // For these pages display the Admin sidebar
    if ( IsAdminLoggedIn() || IsVendor() ) {
        $className = $isAdminPage ? "admin_side_nav" : "admin_side_nav_not_admin_page";
        echo "<span class='$className'>";
        include ADMIN_NAV_BAR_PATH;
        echo "</span>";
    }
?>