<?php 
    // LINKS ARE CLIENT SIDE - THEY USE THE URL (links, scripts, css)
    // PATHS ARE SERVER SIDE - THEY USE THE COMPUTER LOCATION (includes, db)
    
    if( $_SERVER['SERVER_ADDR'] == "::1" || $_SERVER['SERVER_ADDR'] == "72.225.38.26" ) {
        if( !defined("CSS_LINK")) {
            define( "CSS_LINK", "/foodstock_beta/css/style_2.css" );
            
            define( "JS_COLOR_LINK", "/foodstock_beta/scripts/jscolor.js" );
            define( "LOAD_MODALS_LINK", "/foodstock_beta/scripts/load_modals.js" );
            
            define( "PREVIEW_IMAGES_NORMAL", "/foodstock_beta/preview_images/normal/" );
            define( "PREVIEW_IMAGES_THUMBS", "/foodstock_beta/preview_images/thumbnails/" );
            define( "IMAGES_LINK", "/foodstock_beta/images/" );
            
            define( "ADMIN_AUDIT_LINK", "/foodstock_beta/admin_x25/admin_audit_x25.php" );
            define( "ADMIN_LINK", "/foodstock_beta/admin_x25/admin_x25.php" );
            define( "ADMIN_DEFECTIVES_LINK", "/foodstock_beta/admin_x25/admin_defectives_x25.php" );
            define( "ADMIN_INVENTORY_LINK", "/foodstock_beta/admin_x25/admin_inventory_x25.php" );
            define( "ADMIN_ITEMS_LINK", "/foodstock_beta/admin_x25/admin_items_x25.php" );
            define( "ADMIN_PAYMENTS_LINK", "/foodstock_beta/admin_x25/admin_payments_x25.php" );
            define( "ADMIN_RESTOCK_LINK", "/foodstock_beta/admin_x25/admin_restock_x25.php" );
            define( "ADMIN_SHOPPING_GUIDE_LINK", "/foodstock_beta/admin_x25/admin_shopping_guide_x25.php" );
            
            define( "HANDLE_FORMS_LINK", "/foodstock_beta/common/handle_forms.php" );
            define( "AJAX_LINK", "/foodstock_beta/common/handle_ajax.php" );
            
            define( "BILLING_LINK", "/foodstock_beta/billing.php" );
            define( "LOGOUT_LINK", "/foodstock_beta/logout.php" );
            define( "PURCHASE_HISTORY_LINK", "/foodstock_beta/purchase_history.php" );
            define( "REGISTER_LINK", "/foodstock_beta/register.php" );
            define( "REQUESTS_LINK", "/foodstock_beta/requests.php" );
            define( "SNACKSTOCK_LINK", "/foodstock_beta/snackstock.php" );
            define( "SODASTOCK_LINK", "/foodstock_beta/sodastock.php" );
            define( "STATS_LINK", "/foodstock_beta/stats.php" );
        }
    } else {
        if( !defined("CSS_LINK")) {
            define( "CSS_LINK", "/css/style_2.css" );
            
            define( "JS_COLOR_LINK", "/scripts/jscolor.js" );
            define( "LOAD_MODALS_LINK", "/scripts/load_modals.js" );
            
            define( "PREVIEW_IMAGES_NORMAL", "/preview_images/normal/" );
            define( "PREVIEW_IMAGES_THUMBS", "/preview_images/thumbnails/" );
            define( "IMAGES_LINK", "/images/" );
            
            define( "ADMIN_AUDIT_LINK", "/admin_x25/admin_audit_x25.php" );
            define( "ADMIN_LINK", "/admin_x25/admin_x25.php" );
            define( "ADMIN_DEFECTIVES_LINK", "/admin_x25/admin_defectives_x25.php" );
            define( "ADMIN_ITEMS_LINK", "/admin_x25/admin_items_x25.php" );
            define( "ADMIN_INVENTORY_LINK", "/admin_x25/admin_inventory_x25.php" );
            define( "ADMIN_PAYMENTS_LINK", "/admin_x25/admin_payments_x25.php" );
            define( "ADMIN_RESTOCK_LINK", "/admin_x25/admin_restock_x25.php" );
            define( "ADMIN_SHOPPING_GUIDE_LINK", "/admin_x25/admin_shopping_guide_x25.php" );
            
            define( "HANDLE_FORMS_LINK", "/common/handle_forms.php" );
            define( "AJAX_LINK", "/common/handle_ajax.php" );
            
            define( "BILLING_LINK", "/billing.php" );
            define( "LOGOUT_LINK", "/logout.php" );
            define( "PURCHASE_HISTORY_LINK", "/purchase_history.php" );
            define( "REGISTER_LINK", "/register.php" );
            define( "REQUESTS_LINK", "/requests.php" );
            define( "SNACKSTOCK_LINK", "/snackstock.php" );
            define( "SODASTOCK_LINK", "/sodastock.php" );
            define( "STATS_LINK", "/stats.php" );
        }
    }

    date_default_timezone_set('America/New_York');
    
    if( !defined("UI_FUNCTIONS_PATH")) {
        define( "UI_FUNCTIONS_PATH", __DIR__ . "\\functions\\ui_functions.php" );
    }
    
    if( !defined("SLACK_FUNCTIONS_PATH")) {
        define( "SLACK_FUNCTIONS_PATH", __DIR__ . "\\functions\\slack_functions.php" );
    }
    
    if( !defined("DB_PATH")) {
        define( "DB_PATH", __DIR__ . "\\db\\item.db" );
    }
    
    if( !defined("MOBILE_DETECTION_PATH")) {
        define( "MOBILE_DETECTION_PATH", __DIR__ . "\\functions\\mobile_detection.php" );
    }
    
    if( !defined("SESSION_FUNCTIONS_PATH")) {
        define( "SESSION_FUNCTIONS_PATH", __DIR__ . "\\functions\\session_functions.php" );
    }
    
    if( !defined("BUILD_ADMIN_FORMS_PATH")) {
        define( "BUILD_ADMIN_FORMS_PATH", __DIR__ . "\\admin_x25\\build_admin_forms.php" );
    }
    
    if( !defined("LOGIN_BAR_PATH")) {
        define( "LOGIN_BAR_PATH", __DIR__ . "\\common\\login_bar.php" );
    }
    
    if( !defined("HEADER_PATH")) {
        define( "HEADER_PATH", __DIR__ . "\\common\\header.php" );
    }
    
    if( !defined("ADMIN_NAV_BAR_PATH")) {
        define( "ADMIN_NAV_BAR_PATH", __DIR__ . "\\admin_x25\\admin_nav_x25.php" );
    }
    
    if( !defined("CSS_PATH")) {
        define( "CSS_PATH",   __DIR__ . "\\css\\style_locator.php" );
    }
    
    if( !defined("SQL_PATH")) {
        define( "SQL_PATH",   __DIR__ . "\\functions\\exec_sql.php" );
    }
    
    if( !defined("FOODSTOCK_PATH")) {
        define( "FOODSTOCK_PATH",   __DIR__ . "\\foodstock.php" );
    }
?>