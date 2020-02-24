<?php
    if(!function_exists('getDB')) {

        /**
         * @return SQLite3 $db
         */
        function getDB() {
            if (isTestServer()) {
                $dbPath = __DIR__ . getSlash() . "test_db" . getSlash() . "item.db";
            } else {
                $dbPath = __DIR__ . getSlash() . "db" . getSlash() . "item.db";
            }

            $db = new SQLite3( $dbPath );

            // Wait 15 seconds before showing database locked error
            $db->busyTimeout( 15000 );

            if (!$db) die ( "DB failed to load: " . $db->lastErrorMsg() );

            return $db;
        }
    }

    if(!function_exists('getTestDB')) {
        function getTestDB() {
            if (isTestServer()) {
                return __DIR__ . getSlash() . "test_db" . getSlash() . "item_unit_testing.db";
            }
        }
    }

    if(!function_exists('getSlash')) {
        function getSlash() {
            if (isTestServer()) {
                return "\\";
            } else {
                return "/";
            }
        }
    }

    if(!function_exists('isTestServer')) {
        function isTestServer() {
            return $_SERVER['SERVER_ADDR'] == "::1" || $_SERVER['SERVER_ADDR'] == "72.225.38.26" || $_SERVER['SERVER_ADDR'] == "192.168.86.20" || $_SERVER['SERVER_ADDR'] == "192.168.86.34";
        }
    }

    if( !defined("CREDIT_ID")) {
        define("CREDIT_ID", 4000);
    }

    if( !defined("COMMISSION_PERCENTAGE")) {
        define("COMMISSION_PERCENTAGE", 0.10);
    }

    date_default_timezone_set('America/New_York');
    $isTestServer = isTestServer();
    
    // LINKS ARE CLIENT SIDE - THEY USE THE URL (links, scripts, css)
    // PATHS ARE SERVER SIDE - THEY USE THE COMPUTER LOCATION (includes, db)
    $slash = getSlash();
    $subdomain = "";

    if( $isTestServer ) {
//         log_debug("TEST SERVER WAS FOUND - USING FOODSTOCK_BETA PATHS." );
        $subdomain = "/foodstock_beta";
    } else if( strpos( $_SERVER['PHP_SELF'], "staging_x27" ) !== false ) {
        error_log("STAGING SERVER WAS FOUND - USING STAGING_X27 PATHS." );
        $subdomain = "/staging_x27";
    }
    
    if( !defined("CSS_LINK")) {
        define( "CSS_LINK", "$subdomain/css/style_7_3.css" );
        define( "MOBILE_CSS_LINK", "$subdomain/css/mobile_7_3.css" );
        define( "LIGHTS_CSS_LINK", "$subdomain/css/lights.css" );
        define( "THEMES_CSS_LINK", "$subdomain/css/themes.css" );

        define( "JS_COLOR_LINK", "$subdomain/scripts/jscolor.js" );
        define( "SETUP_MODALS_LINK", "$subdomain/scripts/setup_modals.js" );
        
        define( "PREVIEW_IMAGES_NORMAL", "$subdomain/preview_images/normal/" );
        define( "PREVIEW_IMAGES_THUMBS", "$subdomain/preview_images/thumbnails/" );
        define( "IMAGES_LINK", "$subdomain/images/" );
        
        define( "ADMIN_WEEKLY_AUDIT_LINK", "$subdomain/admin_x25/admin_weekly_audit_x25.php" );
        define( "ADMIN_TESTING_LINK", "$subdomain/admin_x25/admin_testing_x25.php" );
        define( "ADMIN_AUDIT_REPORT_LINK", "$subdomain/admin_x25/admin_audit_x25.php" );
        define( "ADMIN_BOT_LINK", "$subdomain/admin_x25/admin_bot_x25.php" );
        define( "ADMIN_LINK", "$subdomain/admin_x25/admin_x25.php" );
        define( "ADMIN_DEFECTIVES_LINK", "$subdomain/admin_x25/admin_defectives_x25.php" );
        define( "ADMIN_INVENTORY_LINK", "$subdomain/admin_x25/admin_inventory_x25.php" );
        define( "ADMIN_ITEMS_LINK", "$subdomain/admin_x25/admin_items_x25.php" );
        define( "ADMIN_ITEMS_IN_STOCK_LINK", "$subdomain/admin_x25/admin_items_in_stock_x25.php" );
        define( "ADMIN_PAYMENTS_LINK", "$subdomain/admin_x25/admin_payments_x25.php" );
        define( "ADMIN_RESTOCK_LINK", "$subdomain/admin_x25/admin_restock_x25.php" );
        define( "ADMIN_SHOPPING_GUIDE_LINK", "$subdomain/admin_x25/admin_shopping_guide_x25.php" );
        define( "ADMIN_CHECKLIST_LINK", "$subdomain/admin_x25/admin_checklist_x25.php" );
        define( "ADMIN_MIGRATION_LINK", "$subdomain/admin_x25/admin_migration_x25.php" );

        define( "HANDLE_FORMS_LINK", "$subdomain/common/handle_forms.php" );
        define( "AJAX_LINK", "$subdomain/common/handle_ajax.php" );
        
        define( "LOGOUT_LINK", "$subdomain/logout.php" );
        define( "PURCHASE_HISTORY_LINK", "$subdomain/purchase_history.php" );
        define( "REGISTER_LINK", "$subdomain/register.php" );
        define( "REQUESTS_LINK", "$subdomain/requests.php" );
        define( "PREFERENCES_LINK", "$subdomain/preferences.php" );
        define( "HELP_LINK", "$subdomain/help.php" );
        define( "SNACKSTOCK_LINK", "$subdomain/snackstock.php" );
        define( "SODASTOCK_LINK", "$subdomain/sodastock.php" );
        define( "STATS_LINK", "$subdomain/stats.php" );

        define( "VENDOR_LINK", "$subdomain/vendor_x82.php" );
    }
    
    if( !defined("UI_FUNCTIONS_PATH")) {
        define( "UI_FUNCTIONS_PATH", __DIR__ . $slash . "functions" . $slash . "ui_functions.php" );
    }

    if( !defined("QUANTITY_FUNCTIONS_PATH")) {
        define( "QUANTITY_FUNCTIONS_PATH", __DIR__ . $slash . "functions" . $slash . "quantity_functions.php" );
    }

    if( !defined("ACTION_FUNCTIONS_PATH")) {
        define( "ACTION_FUNCTIONS_PATH", __DIR__ . $slash . "functions" . $slash . "action_functions.php" );
    }

    if( !defined("HOLIDAY_FUNCTIONS_PATH")) {
        define( "HOLIDAY_FUNCTIONS_PATH", __DIR__ . $slash . "functions" . $slash . "holiday_functions.php" );
    }

    if( !defined("ITEM_OBJ")) {
        define( "ITEM_OBJ", __DIR__ . $slash . "common" . $slash . "Item.php" );
    }

    if( !defined("AUDIT_OBJ")) {
        define( "AUDIT_OBJ", __DIR__ . $slash . "common" . $slash . "AuditDetails.php" );
    }

    if( !defined("ITEM_COST_DETAILS_OBJ")) {
        define( "ITEM_COST_DETAILS_OBJ", __DIR__ . $slash . "common" . $slash . "ItemCostDetails.php" );
    }

    if( !defined("USER_OBJ")) {
        define( "USER_OBJ", __DIR__ . $slash . "common" . $slash . "User.php" );
    }

    if( !defined("TESTING_OBJ")) {
        define( "TESTING_OBJ", __DIR__ . $slash . "common" . $slash . "Testing.php" );
    }

    if( !defined("TESTING_BASE_OBJ")) {
        define( "TESTING_BASE_OBJ", __DIR__ . $slash . "common" . $slash . "TestingBase.php" );
    }

    if( !defined("MONTHLY_LAYOUT_BASE_OBJ")) {
        define( "MONTHLY_LAYOUT_BASE_OBJ", __DIR__ . $slash . "common" . $slash . "MonthlyLayout.php" );
    }

    if( !defined("PURCHASE_HISTORY_OBJ")) {
        define( "PURCHASE_HISTORY_OBJ", __DIR__ . $slash . "common" . $slash . "PurchaseHistoryObj.php" );
    }

    if( !defined("PURCHASE_MONTH_OBJ")) {
        define( "PURCHASE_MONTH_OBJ", __DIR__ . $slash . "common" . $slash . "PurchaseMonthObj.php" );
    }

    if( !defined("PURCHASE_HISTORY_LAYOUT_BASE_OBJ")) {
        define( "PURCHASE_HISTORY_LAYOUT_BASE_OBJ", __DIR__ . $slash . "common" . $slash . "PurchaseHistoryLayout.php" );
    }

    if( !defined("VENDOR_HISTORY_LAYOUT_BASE_OBJ")) {
        define( "VENDOR_HISTORY_LAYOUT_BASE_OBJ", __DIR__ . $slash . "common" . $slash . "VendorHistoryLayout.php" );
    }
    
    if( !defined("SLACK_FUNCTIONS_PATH")) {
        define( "SLACK_FUNCTIONS_PATH", __DIR__ . $slash . "functions" . $slash . "slack_functions.php" );
    }

    if( !defined("LOG_FUNCTIONS_PATH")) {
        define( "LOG_FUNCTIONS_PATH", __DIR__ . $slash . "functions" . $slash . "log_functions.php" );
    }
    
    if( !defined("SESSION_FUNCTIONS_PATH")) {
        define( "SESSION_FUNCTIONS_PATH", __DIR__ .$slash . "functions" . $slash . "session_functions.php" );
    }

    if( !defined("HANDLE_FORMS_PATH")) {
        define( "HANDLE_FORMS_PATH", __DIR__ .$slash . "common" . $slash . "handle_forms.php" );
    }

    if( !defined("HANDLE_AJAX_PATH")) {
        define( "HANDLE_AJAX_PATH", __DIR__ .$slash . "common" . $slash . "handle_ajax.php" );
    }

    if( !defined("BUILD_ADMIN_FORMS_PATH")) {
        define( "BUILD_ADMIN_FORMS_PATH", __DIR__ . $slash . "admin_x25" . $slash . "build_admin_forms.php" );
    }
    
    if( !defined("LOGIN_BAR_PATH")) {
        define( "LOGIN_BAR_PATH", __DIR__ . $slash . "common" . $slash . "login_bar.php" );
    }
    
    if( !defined("HEADER_PATH")) {
        define( "HEADER_PATH", __DIR__ . $slash . "common" . $slash . "header.php" );
    }
    
    if( !defined("ADMIN_NAV_BAR_PATH")) {
        define( "ADMIN_NAV_BAR_PATH", __DIR__ . $slash . "admin_x25" . $slash . "admin_nav_x25.php" );
    }

    if( !defined("SQL_PATH")) {
        define( "SQL_PATH", __DIR__ . $slash);
    }
    
    if( !defined("FOODSTOCK_PATH")) {
        define( "FOODSTOCK_PATH",   __DIR__ . $slash . "foodstock.php" );
    }
    
    if( !defined("CSS_PATH")) {
        define( "CSS_PATH",   __DIR__ . $slash . "css" . $slash . "style_locator.php" );
    }

    if( !defined("IMAGES_NORMAL_PATH")) {
        define( "IMAGES_NORMAL_PATH",   __DIR__ . $slash . "preview_images" . $slash . "normal" . $slash );
    }

    if( !defined("IMAGES_THUMBNAILS_PATH")) {
        define( "IMAGES_THUMBNAILS_PATH",   __DIR__ . $slash . "preview_images" . $slash . "thumbnails" . $slash );
    }
?>