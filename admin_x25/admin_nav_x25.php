<?php 
    include(__DIR__ . "/../appendix.php" );

    echo "<nav id='nav_admin' role='navigation'>";
        echo "<ul>";
            echo "<li><div id='add_item_Soda_button' class='hide_from_modal nav_buttons nav_buttons_soda'>Add Soda</div></li>";
            echo "<li><div id='edit_item_Soda_button' class='hide_from_modal nav_buttons nav_buttons_soda'>Edit Soda</div></li>";
            echo "<li><div id='restock_item_Soda_button' class='hide_from_modal nav_buttons nav_buttons_soda'>Restock Soda</div></li>";
            echo "<li><div id='inventory_Soda_button' class='nav_buttons nav_buttons_soda'>Inventory Soda</div></li>";
            echo "<li><div id='defective_item_Soda_button' class='hide_from_modal nav_buttons nav_buttons_soda'>Defect Soda</div></li>";
        echo "</ul>";

        echo "<ul>";
            echo "<li><div id='add_item_Snack_button' class='hide_from_modal nav_buttons nav_buttons_snack'>Add Snack</div></li>";
            echo "<li><div id='edit_item_Snack_button' class='hide_from_modal nav_buttons nav_buttons_snack'>Edit Snack</div></li>";
            echo "<li><div id='restock_item_Snack_button' class='hide_from_modal nav_buttons nav_buttons_snack'>Restock Snack</div></li>";
            echo "<li><div id='inventory_Snack_button' class='nav_buttons nav_buttons_snack'>Inventory Snack</div></li>";
            echo "<li><div id='defective_item_Snack_button' class='hide_from_modal nav_buttons nav_buttons_snack'>Defect Snack</div></li>";
        echo "</ul>";

        echo "<ul>";
            echo "<li><div id='edit_user_button' class='hide_from_modal nav_buttons nav_buttons_admin'>Edit User</div></li>";
            echo "<li><div id='credit_user_button' class='hide_from_modal nav_buttons nav_buttons_admin'>Credit User</div></li>";
        echo "</ul>";

        echo "<ul>";
            echo "<li><a href='" . ADMIN_CHECKLIST_LINK . "'><div class='nav_buttons nav_buttons_audit'>Checklist</div></a></li>";
            echo "<li><a href='" . ADMIN_LINK . "'><div class='nav_buttons nav_buttons_audit'>Users</div></a></li>";
            echo "<li><a href='" . ADMIN_PAYMENTS_LINK . "'><div class='nav_buttons nav_buttons_audit'>Payments</div></a></li>";
            echo "<li><a href='" . ADMIN_ITEMS_LINK . "'><div class='nav_buttons nav_buttons_audit'>Items</div></a></li>";
            echo "<li><a href='" . ADMIN_RESTOCK_LINK . "'><div class='nav_buttons nav_buttons_audit'>Restock</div></a></li>";
            echo "<li><a href='" . ADMIN_INVENTORY_LINK . "'><div class='nav_buttons nav_buttons_audit'>Inventory/Purchases</div></a></li>";
            echo "<li><a href='" . ADMIN_WEEKLY_AUDIT_LINK ."'><div class='nav_buttons nav_buttons_audit'>Audit</div></a></li>";
        echo "</ul>";

        echo "<ul>";
            echo "<li><a href='" . ADMIN_SHOPPING_GUIDE_LINK . "'><div class='nav_buttons nav_buttons_audit'>Shopping Guide</div></a></li>";
            echo "<li><a href='" . ADMIN_DEFECTIVES_LINK . "'><div class='nav_buttons nav_buttons_audit'>Defectives</div></a></li>";
            echo "<li><a href='" . ADMIN_AUDIT_REPORT_LINK ."'><div class='nav_buttons nav_buttons_audit'>Audit Report</div></a></li>";
            echo "<li><a href='" . ADMIN_BOT_LINK ."'><div class='nav_buttons nav_buttons_audit'>Bot</div></a></li>";
            echo "<li><a href='" . ADMIN_MIGRATION_LINK ."'><div class='nav_buttons nav_buttons_audit'>Migration</div></a></li>";
        echo "</ul>";
    echo "</nav>";
?>
