<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/appendix.php" );
    
    $url = VENDOR_LINK;
    include( HEADER_PATH );

    if( ( IsLoggedIn() && IsVendor() ) || IsAdminLoggedIn() ) {
        include_once(MONTHLY_LAYOUT_BASE_OBJ);
        include_once(VENDOR_HISTORY_LAYOUT_BASE_OBJ);

        echo "<span class='admin_box'>";
        $layout = new VendorHistoryLayout();
        $layout->draw($db);
        echo "</span>";
    }

?>

</body>