<?php
$trackingName =  "Purchase History - Soda and Snack";
    include( "appendix.php" );
    include_once(MONTHLY_LAYOUT_BASE_OBJ);
    include_once(PURCHASE_HISTORY_LAYOUT_BASE_OBJ);
    $url = PURCHASE_HISTORY_LINK;
    include( HEADER_PATH );
    $layout = new PurchaseHistoryLayout();
    $layout->draw( $db );
?>