<?php 
    include(__DIR__ . "/../appendix.php" );
    echo "<div class='nav_box'>";
    
        echo "<div style='margin-bottom:20px;'>";
            echo "<div id='add_item_Soda_button' class='nav_buttons nav_buttons_soda'>Add Soda</div>";
            echo "<div id='edit_item_Soda_button' class='nav_buttons nav_buttons_soda'>Edit Soda</div>";
            echo "<div id='restock_item_Soda_button' class='nav_buttons nav_buttons_soda'>Restock Soda</div>";
            echo "<div id='inventory_Soda_button' class='nav_buttons nav_buttons_soda'>Inventory Soda</div>";
            echo "<div id='defective_item_Soda_button' class='nav_buttons nav_buttons_soda'>Defect Soda</div>";
        echo "</div>";
        
        echo "<div style='margin-bottom:20px;'>";
            echo "<div id='add_item_Snack_button' class='nav_buttons nav_buttons_snack'>Add Snack</div>";
            echo "<div id='edit_item_Snack_button' class='nav_buttons nav_buttons_snack'>Edit Snack</div>";
            echo "<div id='restock_item_Snack_button' class='nav_buttons nav_buttons_snack'>Restock Snack</div>";
            echo "<div id='inventory_Snack_button' class='nav_buttons nav_buttons_snack'>Inventory Snack</div>";
            echo "<div id='defective_item_Snack_button' class='nav_buttons nav_buttons_snack'>Defect Snack</div>";
        echo "</div>";
        
        echo "<div style='margin-bottom:20px;'>";
            echo "<div id='edit_user_button' class='nav_buttons nav_buttons_admin'>Edit User</div>";
            echo "<div id='credit_user_button' class='nav_buttons nav_buttons_admin'>Credit User</div>";
        echo "</div>";
        
        echo "<div style='margin-bottom:20px; padding-top:40px; margin-top:40px; border-top: 3px solid #000;'>";
            echo "<a href='" . ADMIN_CHECKLIST_LINK . "'><div class='nav_buttons nav_buttons_snack'>Checklist</div></a>";
            echo "<a href='" . ADMIN_LINK . "'><div class='nav_buttons nav_buttons_admin'>Users</div></a>";
            echo "<a href='" . ADMIN_PAYMENTS_LINK . "'><div class='nav_buttons nav_buttons_billing'>Payments</div></a>";
            echo "<a href='" . ADMIN_ITEMS_LINK . "'><div class='nav_buttons nav_buttons_snack'>Items</div></a>";
            echo "<a href='" . ADMIN_RESTOCK_LINK . "'><div class='nav_buttons nav_buttons_snack'>Restock</div></a>";
            echo "<a href='" . ADMIN_INVENTORY_LINK . "'><div class='nav_buttons nav_buttons_snack'>Inventory/Purchases</div></a>";
            echo "<a href='" . ADMIN_WEEKLY_AUDIT_LINK ."'><div class='nav_buttons nav_buttons_audit'>Audit</div></a>";
        echo "</div>";

        echo "<div style='margin-bottom:40px; margin-top:40px;'>";
            echo "<a href='" . ADMIN_SHOPPING_GUIDE_LINK . "'><div class='nav_buttons nav_buttons_snack'>Shopping Guide</div></a>";
            echo "<a href='" . ADMIN_DEFECTIVES_LINK . "'><div class='nav_buttons nav_buttons_defectives'>Defectives</div></a>";
            echo "<a href='" . ADMIN_AUDIT_REPORT_LINK ."'><div class='nav_buttons nav_buttons_audit'>Audit Report</div></a>";
            echo "<a href='" . ADMIN_BOT_LINK ."'><div class='nav_buttons nav_buttons_audit'>Bot</div></a>";
            echo "<a href='" . ADMIN_MIGRATION_LINK ."'><div class='nav_buttons nav_buttons_audit'>Migration</div></a>";
        echo "</div>";
        
    echo "</div>";
?>
