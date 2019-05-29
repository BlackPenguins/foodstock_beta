<?php
    if(!function_exists('getDB')) {
        function getDB() {
            if (isTestServer()) {
                return __DIR__ . getSlash() . "test_db" . getSlash() . "item.db";
            } else {
                return __DIR__ . getSlash() . "db" . getSlash() . "item.db";
            }
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
            return $_SERVER['SERVER_ADDR'] == "::1" || $_SERVER['SERVER_ADDR'] == "72.225.38.26" || $_SERVER['SERVER_ADDR'] == "192.168.86.234" || $_SERVER['SERVER_ADDR'] == "192.168.86.34";
        }
    }

    if( !defined("CREDIT_ID")) {
        define("CREDIT_ID", 4000);
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
        define( "CSS_LINK", "$subdomain/css/style_6_2.css" );
        define( "CSS_LIGHTS_LINK", "$subdomain/css/lights.css" );
        
        define( "JS_COLOR_LINK", "$subdomain/scripts/jscolor.js" );
        define( "LOAD_MODALS_LINK", "$subdomain/scripts/load_modals.js" );
        
        define( "PREVIEW_IMAGES_NORMAL", "$subdomain/preview_images/normal/" );
        define( "PREVIEW_IMAGES_THUMBS", "$subdomain/preview_images/thumbnails/" );
        define( "IMAGES_LINK", "$subdomain/images/" );
        
        define( "ADMIN_WEEKLY_AUDIT_LINK", "$subdomain/admin_x25/admin_weekly_audit_x25.php" );
        define( "ADMIN_AUDIT_REPORT_LINK", "$subdomain/admin_x25/admin_audit_x25.php" );
        define( "ADMIN_BOT_LINK", "$subdomain/admin_x25/admin_bot_x25.php" );
        define( "ADMIN_LINK", "$subdomain/admin_x25/admin_x25.php" );
        define( "ADMIN_DEFECTIVES_LINK", "$subdomain/admin_x25/admin_defectives_x25.php" );
        define( "ADMIN_INVENTORY_LINK", "$subdomain/admin_x25/admin_inventory_x25.php" );
        define( "ADMIN_ITEMS_LINK", "$subdomain/admin_x25/admin_items_x25.php" );
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
    }
    
    if( !defined("UI_FUNCTIONS_PATH")) {
        define( "UI_FUNCTIONS_PATH", __DIR__ . $slash . "functions" . $slash . "ui_functions.php" );
    }
    
    if( !defined("SLACK_FUNCTIONS_PATH")) {
        define( "SLACK_FUNCTIONS_PATH", __DIR__ . $slash . "functions" . $slash . "slack_functions.php" );
    }

    if( !defined("LOG_FUNCTIONS_PATH")) {
        define( "LOG_FUNCTIONS_PATH", __DIR__ . $slash . "functions" . $slash . "log_functions.php" );
    }
    
    if( !defined("MOBILE_DETECTION_PATH")) {
        define( "MOBILE_DETECTION_PATH", __DIR__ . $slash . "functions" . $slash . "mobile_detection.php" );
    }
    
    if( !defined("SESSION_FUNCTIONS_PATH")) {
        define( "SESSION_FUNCTIONS_PATH", __DIR__ .$slash . "functions" . $slash . "session_functions.php" );
    }

    if( !defined("HANDLE_FORMS_PATH")) {
        define( "HANDLE_FORMS_PATH", __DIR__ .$slash . "common" . $slash . "handle_forms.php" );
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