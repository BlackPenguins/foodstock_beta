<?php
$trackingName =  "Purchase History - Soda and Snack";
    include( "appendix.php" );
    include(MONTHLY_LAYOUT_BASE_OBJ);
    include(PURCHASE_HISTORY_LAYOUT_BASE_OBJ);
    include(USER_PAYMENT_PROFILE);
    $url = PURCHASE_HISTORY_LINK;
    include( HEADER_PATH );
    $layout = new PurchaseHistoryLayout();
    benchmark_start( "Draw" );
    $layout->draw( $db );
    benchmark_stop( "Draw" );
?>