<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>

<link rel="stylesheet" type="text/css" href="colorPicker.css"/>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">

<?php
    //-----------------------------------------------------
    // PAGE SPECIFIC INFORMATION
    //-----------------------------------------------------
    $titleName = "SodaStock";
    $isAdminPage = false;
    $bodyClass = "soda_body";
    
    switch( $url ) {
        case ADMIN_AUDIT_LINK:
            $isAdminPage = true;
            $titleName = "Admin - Audit";
            $trackingName = "Admin - Audit";
            break;
        case ADMIN_BOT_LINK:
            $isAdminPage = true;
            $titleName = "Admin - Bot";
            $trackingName = "Admin - Bot";
            break;
        case ADMIN_DEFECTIVES_LINK:
            $isAdminPage = true;
            $titleName = "Admin - Defectives";
            $trackingName = "Admin - Defectives";
            break;
        case ADMIN_INVENTORY_LINK:
            $isAdminPage = true;
            $titleName = "Admin - Inventory/Purchases";
            $trackingName = "Admin - Inventory/Purchases";
            break;
        case ADMIN_ITEMS_LINK:
            $isAdminPage = true;
            $titleName = "Admin - Items";
            $trackingName = "Admin - Items";
            break;
        case ADMIN_PAYMENTS_LINK:
            $isAdminPage = true;
            $titleName = "Admin - Payments";
            $trackingName = "Admin - Payments";
            break;
        case ADMIN_RESTOCK_LINK:
            $isAdminPage = true;
            $titleName = "Admin - Restock";
            $trackingName = "Admin - Restock";
            break;
        case ADMIN_SHOPPING_GUIDE_LINK:
            $isAdminPage = true;
            $titleName = "Admin - Shopping Guide";
            $trackingName = "Admin - Shopping Guide";
            break;
        case ADMIN_LINK:
            $isAdminPage = true;
            $titleName = "Admin - Home";
            $trackingName = "Admin - Home";
            break;
        case BILLING_LINK:
            $isAdminPage = false;
            $titleName = "Billing";
            $trackingName = "Billing";
            break;
        case SODASTOCK_LINK:
            $isAdminPage = false;
            $titleName = "SodaStock - " . date('Y');
            $trackingName = "SodaStock";
            break;
        case SNACKSTOCK_LINK:
            $isAdminPage = false;
            $titleName = "SnackStock - " . date('Y');
            $trackingName = "SnackStock";
            $bodyClass = "snack_body";
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
        case STATS_LINK:
            $isAdminPage = false;
            $titleName = "Stats";
            break;
        default:
            $isAdminPage = false;
            $trackingName = "Unknown [$url]";
    }
    //-----------------------------------------------------
    
    
    include( CSS_PATH );

    include( SESSION_FUNCTIONS_PATH );
    include( UI_FUNCTIONS_PATH );
    include( SLACK_FUNCTIONS_PATH );
    require_once( MOBILE_DETECTION_PATH );
    
    $db = new SQLite3(DB_PATH);
    if (!$db) die ($error);
        
    date_default_timezone_set('America/New_York');
        
    Login($db);

    $isLoggedIn = IsLoggedIn();
    $isLoggedInAdmin = IsAdminLoggedIn();
    
    if( $isAdminPage && !$isLoggedInAdmin && $url != ADMIN_SHOPPING_GUIDE_LINK ) {
        echo "You are not an admin. Shame!";
        die();
    }

    $detect = new Mobile_Detect;
    $device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
    $isMobile = $device_type == 'phone';

    if(isset($_GET['mobile'])) {
        $isMobile = true;
    }
    
    echo "<script src='" . JS_COLOR_LINK . "'></script>";
    
    if( !$isMobile) {
        echo "<script src='" . LOAD_MODALS_LINK . "'></script>";
    }
    
    echo "<title>$titleName</title>";
    echo "<link rel='icon' type='image/png' href='soda_can_icon.png' />";
    
    // LOAD THE ADMIN MODALS
    if(!$isMobile ) {
        echo "<script type='text/javascript'>";
        echo "$( document ).ready( function() {";
        
            if( $isAdminPage ) {
                echo "loadSingleModals();\n";
                echo "loadItemModals('Soda');\n";
                echo "loadItemModals('Snack');\n";
            }
            
            if( $url == ADMIN_SHOPPING_GUIDE_LINK ) {
                echo "loadShoppingModal();\n";
            }
            
            if( $url == REQUESTS_LINK ) {
                echo "loadUserModals();\n";
            }
        echo "});";
        echo "</script>";
    }
?>
</head>

<?php
    if( $isMobile ) {
        //Some magic that makes the top blue bar fill the width of the phone's screen
        echo "<body class='$bodyClass' style='display:inline-table;'>";
    } else {
        echo "<body class='$bodyClass'>";
    }
    
    if( $isAdminPage && $isLoggedInAdmin ) {
        include(BUILD_ADMIN_FORMS_PATH);
    }
    
    include(LOGIN_BAR_PATH);
    
    TrackVisit($db, $trackingName);
    
    DisplayUserMessage();
    
    // For these pages display the Admin sidebar
    if ( $isLoggedInAdmin && $isAdminPage ) {
        echo "<span style='width:11%; vertical-align:top; display:inline-block; padding: 10px; background-color:#4d544e; border: 0px solid #000;'>";
        include ADMIN_NAV_BAR_PATH;
        echo "</span>";
    }
?>