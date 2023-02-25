<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/appendix.php" );
    
    $url = VENDOR_LINK;
    include( HEADER_PATH );

    if( ( IsLoggedIn() && IsVendor() ) || IsAdminLoggedIn() ) {
        include(MONTHLY_LAYOUT_BASE_OBJ);
        include(VENDOR_HISTORY_LAYOUT_BASE_OBJ);
        include(USER_PAYMENT_PROFILE);

        echo "<span class='admin_box'>";
        $layout = new VendorHistoryLayout();
        $layout->draw($db);
        echo "</span>";
    }

?>

</body>