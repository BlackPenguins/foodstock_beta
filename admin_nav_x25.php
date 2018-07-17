<?php 
    echo "<div class='nav_box'>";
    
        echo "<div style='margin-bottom:20px;'>";
            echo "<div id='add_item_Soda_button' class='nav_buttons nav_buttons_soda'>Add Soda</div>";
            echo "<div id='edit_item_Soda_button' class='nav_buttons nav_buttons_soda'>Edit Soda</div>";
            echo "<div id='restock_item_Soda_button' class='nav_buttons nav_buttons_soda'>Restock Soda</div>";
            echo "<div id='inventory_Soda_button' class='nav_buttons nav_buttons_soda'>Inventory Soda</div>";
        echo "</div>";
        
        echo "<div style='margin-bottom:20px;'>";
            echo "<div id='add_item_Snack_button' class='nav_buttons nav_buttons_snack'>Add Snack</div>";
            echo "<div id='edit_item_Snack_button' class='nav_buttons nav_buttons_snack'>Edit Snack</div>";
            echo "<div id='restock_item_Snack_button' class='nav_buttons nav_buttons_snack'>Restock Snack</div>";
            echo "<div id='inventory_Snack_button' class='nav_buttons nav_buttons_snack'>Inventory Snack</div>";
        echo "</div>";
        
        echo "<div style='margin-bottom:20px;'>";
            echo "<div id='payment_button' class='nav_buttons nav_buttons_billing'>Add Payment</div>";
            echo "<div id='edit_user_button' class='nav_buttons nav_buttons_admin'>Edit User</div>";
        echo "</div>";
        
        echo "<div style='margin-bottom:20px; padding-top:40px; margin-top:40px; border-top: 3px solid #000;'>";
            echo "<a href='admin_x25.php'><div class='nav_buttons nav_buttons_admin'>Users</div></a>";
            echo "<a href='admin_payments_x25.php'><div class='nav_buttons nav_buttons_billing'>Payments</div></a>";
            echo "<a href='admin_items_x25.php'><div class='nav_buttons nav_buttons_snack'>Items</div></a>";
            echo "<a href='admin_restock_x25.php'><div class='nav_buttons nav_buttons_snack'>Restock</div></a>";
            echo "<a href='admin_inventory_x25.php'><div class='nav_buttons nav_buttons_snack'>Inventory</div></a>";
        echo "</div>";
        
    echo "</div>";
?>
