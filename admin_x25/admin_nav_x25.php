<?php 
    include(__DIR__ . "/../appendix.php" );

    $showSodaButtons = true;
    $showSnackButtons = true;

    $showSodaInStockButtons = true;
    $showSnackInStockButtons = true;

    if( IsVendor() ) {
        $statement = $db->prepare("SELECT COUNT(*) as Count " .
            "FROM Item " .
            "WHERE Type = :itemType AND Hidden != 1 AND VendorID = :vendorID");
        $statement->bindValue( ":itemType", "Soda" );
        $statement->bindValue( ":vendorID", $_SESSION['UserID'] );
        $results = $statement->execute();
        $numberOfActiveItems = $results->fetchArray()['Count'];
        $showSodaButtons = $numberOfActiveItems > 0;

        $statement = $db->prepare("SELECT COUNT(*) as Count " .
            "FROM Item " .
            "WHERE Type = :itemType AND Hidden != 1 AND VendorID = :vendorID");
        $statement->bindValue( ":itemType", "Snack" );
        $statement->bindValue( ":vendorID", $_SESSION['UserID'] );
        $results = $statement->execute();
        $numberOfActiveItems = $results->fetchArray()['Count'];
        $showSnackButtons = $numberOfActiveItems > 0;

        $statement = $db->prepare("SELECT COUNT(*) as Count, " . getQuantityQuery() . " " .
            "FROM Item i " .
            "WHERE Type = :itemType AND Hidden != 1 AND TotalAmount > 0 AND VendorID = :vendorID");
        $statement->bindValue( ":itemType", "Soda" );
        $statement->bindValue( ":vendorID", $_SESSION['UserID'] );
        $results = $statement->execute();
        $numberOfActiveItems = $results->fetchArray()['Count'];
        $showSodaInStockButtons = $numberOfActiveItems > 0;

        $statement = $db->prepare("SELECT COUNT(*) as Count, " . getQuantityQuery() . " " .
            "FROM Item i " .
            "WHERE Type = :itemType AND Hidden != 1 AND TotalAmount > 0 AND VendorID = :vendorID");
        $statement->bindValue( ":itemType", "Snack" );
        $statement->bindValue( ":vendorID", $_SESSION['UserID'] );
        $results = $statement->execute();
        $numberOfActiveItems = $results->fetchArray()['Count'];
        $showSnackInStockButtons = $numberOfActiveItems > 0;

    }

    // TODO Make sure on the processing side you cant edit an item you arent a vendor of, inc ase change the request of itemID
    //  Grey out the item buttons, dont hide them
    // Vendor will be able to see all of this unless admin only is specified

    echo "<nav id='nav_admin' role='navigation'>";
        echo "<ul>";
            echo "<li><div id='add_item_soda_button' class='hide_from_modal nav_buttons nav_buttons_soda'>Add Soda</div></li>";

            if( $showSodaButtons ) {
                echo "<li><div id='edit_item_soda_button' class='hide_from_modal nav_buttons nav_buttons_soda'>Edit Soda</div></li>";
                echo "<li><div id='restock_item_soda_button' class='hide_from_modal nav_buttons nav_buttons_soda'>Restock Soda</div></li>";
            }

            if( $showSodaInStockButtons ) {
                echo "<li><div id='refill_item_soda_button' class='nav_buttons nav_buttons_soda'>Refill Soda</div></li>";
                echo "<li><div id='inventory_item_soda_button' class='nav_buttons nav_buttons_soda'>Inventory Soda</div></li>";
            }

            if( IsAdminLoggedIn() ) {
                echo "<li><div id='defective_item_soda_button' class='hide_from_modal nav_buttons nav_buttons_soda'>Defect Soda</div></li>";
            }
        echo "</ul>";

        echo "<ul>";
            echo "<li><div id='add_item_snack_button' class='hide_from_modal nav_buttons nav_buttons_snack'>Add Snack</div></li>";

            if( $showSnackButtons ) {
                echo "<li><div id='edit_item_snack_button' class='hide_from_modal nav_buttons nav_buttons_snack'>Edit Snack</div></li>";
                echo "<li><div id='restock_item_snack_button' class='hide_from_modal nav_buttons nav_buttons_snack'>Restock Snack</div></li>";
            }

            if( $showSnackInStockButtons ) {
                echo "<li><div id='refill_item_snack_button' class='nav_buttons nav_buttons_snack'>Refill Snack</div></li>";
                echo "<li><div id='inventory_item_snack_button' class='nav_buttons nav_buttons_snack'>Inventory Snack</div></li>";
            }

            if (IsAdminLoggedIn()) {
                echo "<li><div id='defective_item_snack_button' class='hide_from_modal nav_buttons nav_buttons_snack'>Defect Snack</div></li>";
            }
        echo "</ul>";

        if( IsAdminLoggedIn() ) {
            echo "<ul>";
            echo "<li><div id='edit_user_button' class='hide_from_modal nav_buttons nav_buttons_admin'>Edit User</div></li>";
            echo "<li><div id='credit_user_button' class='hide_from_modal nav_buttons nav_buttons_admin'>Credit User</div></li>";
            echo "</ul>";
        }

        echo "<ul>";

            if( IsAdminLoggedIn() ) {
                echo "<li><a href='" . ADMIN_CHECKLIST_LINK . "'><div class='nav_buttons nav_buttons_audit'>Checklist</div></a></li>";
                echo "<li><a href='" . ADMIN_LINK . "'><div class='nav_buttons nav_buttons_audit'>Users</div></a></li>";
                echo "<li><a href='" . ADMIN_PAYMENTS_LINK . "'><div class='nav_buttons nav_buttons_audit'>Payments</div></a></li>";
            }

            echo "<li><a href='" . VENDOR_LINK . "'><div class='nav_buttons nav_buttons_audit'>Vendor</div></a></li>";
            echo "<li><a href='" . ADMIN_ITEMS_LINK . "'><div class='nav_buttons nav_buttons_audit'>Items</div></a></li>";
            echo "<li><a href='" . ADMIN_ITEMS_IN_STOCK_LINK . "'><div class='nav_buttons nav_buttons_audit'>Items in Stock</div></a></li>";
            echo "<li><a href='" . ADMIN_RESTOCK_LINK . "'><div class='nav_buttons nav_buttons_audit'>Restock</div></a></li>";
            echo "<li><a href='" . ADMIN_INVENTORY_LINK . "'><div class='nav_buttons nav_buttons_audit'>Inventory/Purchases</div></a></li>";

            if( IsAdminLoggedIn() ) {
                echo "<li><a href='" . ADMIN_WEEKLY_AUDIT_LINK . "'><div class='nav_buttons nav_buttons_audit'>Audit</div></a></li>";
            }

        echo "</ul>";

        if( IsAdminLoggedIn() ) {
            echo "<ul>";
            echo "<li><a href='" . ADMIN_SHOPPING_GUIDE_LINK . "'><div class='nav_buttons nav_buttons_audit'>Shopping Guide</div></a></li>";
            echo "<li><a href='" . ADMIN_DEFECTIVES_LINK . "'><div class='nav_buttons nav_buttons_audit'>Defectives</div></a></li>";
            echo "<li><a href='" . ADMIN_AUDIT_REPORT_LINK . "'><div class='nav_buttons nav_buttons_audit'>Audit Report</div></a></li>";
            echo "<li><a href='" . ADMIN_BOT_LINK . "'><div class='nav_buttons nav_buttons_audit'>Bot</div></a></li>";
            echo "<li><a href='" . ADMIN_MIGRATION_LINK . "'><div class='nav_buttons nav_buttons_audit'>Migration</div></a></li>";
            echo "<li><a href='" . ADMIN_TESTING_LINK . "'><div class='nav_buttons nav_buttons_audit'>Automation Testing</div></a></li>";
            echo "</ul>";
        }
    echo "</nav>";
?>

<script>
    setupModal( "add_item_soda" );
    setupModal( "edit_item_soda" );
    setupModal( "restock_item_soda" );
    setupModal( "refill_item_soda" );
    setupModal( "inventory_item_soda" );
    setupModal( "defective_item_soda" );

    setupModal( "add_item_snack" );
    setupModal( "edit_item_snack" );
    setupModal( "restock_item_snack" );
    setupModal( "refill_item_snack" );
    setupModal( "inventory_item_snack" );
    setupModal( "defective_item_snack" );

    setupModal( "edit_user" );
    setupModal( "credit_user" );
    setupModal( "payment" );
</script>
