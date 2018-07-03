<?php
        if(!$isLoggedInAdmin) {
            return;
        }
        
        $hideForms = "style='display:none;'";
        
        if( !$isMobile ) {
                $hideForms = "";
        }

        $results = $db->query("SELECT FirstName, LastName, UserID, SlackID, Inactive From User Order By FirstName Asc");
        $user_options = "";
        $user_info = "";
        
        $user_options = "";
         
        while ($row = $results->fetchArray()) {
            $fullName = $row['FirstName'] . " " . $row['LastName'];
            $userID = $row['UserID'];
            $slackID = $row['SlackID'];
            $inactive = $row['Inactive'];
            $user_options = $user_options . "<option value='$userID'>$fullName</option>";
            
            $user_info = $user_info .
            "<input type='hidden' id='User_SlackID_$userID' value='$slackID'/>" . 
            "<input type='hidden' id='User_Inactive_$userID' value='$inactive'/>";
        }
         
        $user_dropdown = "<select id='UserDropdown' name='UserDropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'><option value='0'>(Manual Count)</option>$user_options</select>";
        $edit_user_dropdown = "<select id='EditUserDropdown' name='EditUserDropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$user_options</select>";
         
        $itemType_options = "";
        $itemType_options = $itemType_options . "<option value='Soda'>Soda</option>";
        $itemType_options = $itemType_options . "<option value='Snack'>Snack</option>";
        $itemType_dropdown = "<select id='ItemTypeDropdown' name='ItemTypeDropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$itemType_options</select>";
        
        $method_dropdown = "<select id='MethodDropdown' name='MethodDropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>" .
                "<option value='None'>None</option>" .
                "<option value='Venmo'>Venmo</option>" .
                "<option value='Square Cash'>Square Cash</option>" .
                "<option value='PayPal'>PayPal</option>" .
                "<option value='Cash'>Cash</option>" .
                "<option value='Refund'>Refund</option>" .
                "<option value='Other'>Other</option>" .
                "</select>";
        
        buildModalsForType($db, "Soda", $hideForms, $isMobile );
        buildModalsForType($db, "Snack", $hideForms, $isMobile );
        
        // ------------------------------------
        // PAYMENT MODAL
        // ------------------------------------
//         $dateObject = DateTime::createFromFormat( 'Y-m-d H:i:s', time() );
//         $monthLabel = $dateObject->format('F Y');
        
        
        $paymentMonth_options = "";
        $monthLabel = "";
        $monthsAgo = 0;
        
        while ($monthLabel != "March 2018" ) {
            $firstOfMonth = mktime(0, 0, 0, date("m") - $monthsAgo, 1, date("Y") );
            $monthLabel = date('F Y', $firstOfMonth);
            $paymentMonth_options = $paymentMonth_options . "<option value='$monthLabel'>$monthLabel</option>";
            $monthsAgo++;
        }
        
        $paymentMonth_dropdown = "<select id='MonthDropdown' name='MonthDropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$paymentMonth_options</select>";
        
        echo "<div id='payment' title='Add Payment' $hideForms>";
        echo "<form id='payment_form' class='fancy' enctype='multipart/form-data' action='handle_forms.php' method='POST'>";
        echo "<fieldset>";
        echo "<label style='padding:5px 0px;' for='ItemTypeDropdown'>Type</label>";
        echo $itemType_dropdown;
        echo "<label style='padding:5px 0px;' for='UserDropdown'>User</label>";
        echo $user_dropdown;
        echo "<label style='padding:5px 0px;' for='MonthDropdown'>Payment Month</label>";
        echo $paymentMonth_dropdown;
        echo "<label style='padding:5px 0px;' for='Method'>Method</label>";
        echo $method_dropdown;
        echo "<label style='padding:5px 0px;' for='Amount'>Amount</label>";
        echo "<input type='tel' name='Amount' class='text ui-widget-content ui-corner-all'/>";
        echo "<label style='padding:5px 0px;' for='Note'>Note</label>";
        echo "<input type='text' name='Note' class='text ui-widget-content ui-corner-all'/>";
        
        echo $user_info;
        echo "<input type='hidden' name='Payment' value='Payment'/><br>";
        echo "<input type='hidden' name='redirectURL' value='admin_x25.php'/><br>";
        
        echo "</fieldset>";
        echo "</form>";
        echo "</div>";
        
        // ------------------------------------
        // EDIT USER MODAL
        // ------------------------------------
        echo "<div id='edit_user' title='Edit User' $hideForms>";
        echo "<form id='edit_user_form' class='fancy' enctype='multipart/form-data' action='handle_forms.php' method='POST'>";
        echo "<label style='padding:5px 0px;' for='EditUserDropdown'>User</label>";
        echo $edit_user_dropdown;
        echo "<label style='padding:5px 0px;' for='SlackID'>Slack ID</label>";
        echo "<input type='text' id='SlackID' name='SlackID' class='text ui-widget-content ui-corner-all'/>";
        
        echo "<div style='padding:5px 0px;'>";
            echo "<label style='display:inline;' for='Inactive'>Inactive:</label>";
            echo "<input style='display:inline;' type='checkbox' id='Inactive' name='Inactive'/>";
        echo "</div>";
        
        echo "<div style='padding:5px 0px;'>";
            echo "<label style='padding:5px 0px; display:inline;' for='ResetPassword'>Reset Password?</label>";
            echo "<input style='display:inline;' type='checkbox' name='ResetPassword'/>";
        echo "</div>";
        
        echo "<input type='hidden' name='EditUser' value='EditUser'/><br>";
        echo "<input type='hidden' name='redirectURL' value='admin_x25.php'/><br>";
        
        echo "</fieldset>";
        echo "</form>";
        echo "</div>";

        
        
        function buildModalsForType( $db, $itemType, $hideForms, $isMobile ) {
            // Build Item Dropdown
            $results = $db->query("SELECT ID, Name, Price, Retired, ChartColor, ImageURL, ThumbURL, UnitName, DiscountPrice FROM Item WHERE Type ='" . $itemType . "' AND Hidden != 1 order by name asc");
            $item_options = "";
            $item_options_no_discontinued = "";
            $item_info = "";
            while ($row = $results->fetchArray()) {
                $item_id = $row['ID'];
                $item_name = $row['Name'];
                $item_price = $row['Price'];
                $item_discount_price = $row['DiscountPrice'];
                $item_retired = $row['Retired'];
                $item_chart_color = $row['ChartColor'];
                $item_imageURL = $row['ImageURL'];
                $item_thumbURL = $row['ThumbURL'];
                $item_unit_name = $row['UnitName'];
                if(strlen($item_name) > 30) {
                    $item_name = substr($item_name, 0, 30)."...";
                }
                
                if( $item_discount_price == "" ) {
                    $item_discount_price = 0.0;
                }
                
                $strikethrough = ( $item_retired == "0" ? "" : " style='font-weight:bold; color:#9b0909'");
                $item_options = $item_options . "<option $strikethrough value='" . $row['ID'] . "'>$item_name</option>";
            
                if($item_retired == 0) {
                    $item_options_no_discontinued = $item_options_no_discontinued . "<option $strikethrough value='" . $row['ID'] . "'>$item_name</option>";
                }
            
                $item_info = $item_info . "<input type='hidden' id='Item_" . $itemType . "_Name_$item_id' value='$item_name'/>" .
                "<input type='hidden' id='Item_" . $itemType . "_Price_$item_id' value='$item_price'/>" .
                "<input type='hidden' id='Item_" . $itemType . "_DiscountPrice_$item_id' value='$item_discount_price'/>" .
                "<input type='hidden' id='Item_" . $itemType . "_ImageURL_$item_id' value='$item_imageURL'/>" .
                "<input type='hidden' id='Item_" . $itemType . "_ThumbURL_$item_id' value='$item_thumbURL'/>" .
                "<input type='hidden' id='Item_" . $itemType . "_UnitName_$item_id' value='$item_unit_name'/>" .
                "<input type='hidden' id='Item_" . $itemType . "_Retired_$item_id' value='$item_retired'/>" .
                "<input type='hidden' id='Item_" . $itemType . "_ChartColor_$item_id' value='$item_chart_color'/>";
            }
                
            $edit_dropdown = "<select id='Edit" . $itemType . "Dropdown' name='Edit" . $itemType . "Dropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$item_options</select>";
                
            $restock_dropdown = "<select id='RestockDropdown' name='RestockDropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$item_options_no_discontinued</select>";
                
            
            // ------------------------------------
            // ADD ITEM MODAL
            // ------------------------------------
            echo "<div id='add_item_" . $itemType . "' class='fancy' title='Add " . $itemType . "' $hideForms>";
            echo "<form id='add_item_" . $itemType . "_form' enctype='multipart/form-data' action='handle_forms.php' method='POST'>";
            echo "<fieldset>";
            echo "<label style='padding:5px 0px;' for='ItemName'>Name</label>";
            echo "<input type='text' autocorrect='off' autocapitalize='off' maxlength='40'; name='ItemName' class='text ui-widget-content ui-corner-all'>";
            echo "<label style='padding:5px 0px;' for='ChartColor'>Color</label>";
            echo "<input name='ChartColor' class='color text ui-widget-content ui-corner-all'>";
            echo "<label style='padding:5px 0px;' for='CurrentPrice'>Price of Can</label>";
            echo "<input type='tel' name='CurrentPrice' value='0.50' class='text ui-widget-content ui-corner-all'/>";
            
            echo "<input type='hidden' name='ItemType' value='$itemType'/><br>";
            echo "<input type='hidden' name='AddItem' value='AddItem'/><br>";
            echo "<input type='hidden' name='redirectURL' value='admin_x25.php'/><br>";
            
            if( $isMobile) {
                echo "<input class='ui-button' style='padding:10px;' type='submit' name='Add_Food_" . $itemType .  "_Submit' value='Add " . $itemType . "'/><br>";
            }
            echo "</fieldset>";
            echo "</form>";
            echo "</div>";
            
            // ------------------------------------
            // EDIT ITEM MODAL
            // ------------------------------------
            $editNameID = "EditItemName" . $itemType;
            $editChartColorID = "EditChartColor" . $itemType;
            $editPriceID = "EditPrice" . $itemType;
            $editDiscountPriceID = "EditDiscountPrice" . $itemType;
            $editImageURLID = "EditImageURL" . $itemType;
            $editThumbURLID = "EditThumbURL" . $itemType;
            $editUnitNameID = "EditUnitName" . $itemType;
            $editActiveID = "EditStatusActive" . $itemType;
            $editDiscontinuedID = "EditStatusDiscontinued" . $itemType;
            $editStatusID = "EditStatus" . $itemType;
            
            echo "<div id='edit_item_" . $itemType . "' class='fancy' title='Edit " . $itemType . "' $hideForms>";
            echo "<form id='edit_item_" . $itemType . "_form' enctype='multipart/form-data' action='handle_forms.php' method='POST'>";
            echo "<fieldset>";
            echo "<label style='padding:5px 0px;' for='ItemNameDropdown'>" . $itemType . "</label>";
            echo $edit_dropdown;
            echo "<label style='padding:5px 0px;' for='ItemName'>Name</label>";
            echo "<input type='text' autocorrect='off' autocapitalize='off' maxlength='30'; id='$editNameID' name='$editNameID' class='text ui-widget-content ui-corner-all'>";
            echo "<label style='padding:5px 0px;' for='ChartColor'>Color</label>";
            echo "<input id='$editChartColorID' name='$editChartColorID' class='color text ui-widget-content ui-corner-all'>";
            echo "<label style='padding:5px 0px;' for='CurrentPrice'>Price</label>";
            echo "<input type='tel' id='$editPriceID' name='$editPriceID' class='text ui-widget-content ui-corner-all'/>";
            echo "<label style='padding:5px 0px;' for='CurrentPrice'>Discount Price</label>";
            echo "<input type='tel' id='$editDiscountPriceID' name='$editDiscountPriceID' class='text ui-widget-content ui-corner-all'/>";
            echo "<label style='padding:5px 0px;' for='ImageURL'>Image URL</label>";
            echo "<input id='$editImageURLID' name='$editImageURLID' class='text ui-widget-content ui-corner-all'>";
            echo "<label style='padding:5px 0px;' for='ThumbURL'>Thumb URL</label>";
            echo "<input id='$editThumbURLID' name='$editThumbURLID' class='text ui-widget-content ui-corner-all'>";
            echo "<label style='padding:5px 0px;' for='UnitName'>Unit Name</label>";
            echo "<input id='$editUnitNameID' name='$editUnitNameID' class='text ui-widget-content ui-corner-all'>";
            echo "<div class='radio_status'>";
            echo "<input class='radio' type='radio' id='$editActiveID' name='$editStatusID' value='active' checked />";
            echo "<label for='$editActiveID'>Active</label>";
            echo "<input class='radio' type='radio' id='$editDiscontinuedID' name='$editStatusID' value='discontinued' />";
            echo "<label for='$editDiscontinuedID'>Discontinued</label>";
            echo "</div>";
            
            echo $item_info;
            echo "<input type='hidden' name='ItemType' value='$itemType'/><br>";
            echo "<input type='hidden' name='EditItem' value='EditItem'/><br>";
            echo "<input type='hidden' name='redirectURL' value='admin_x25.php'/><br>";
            
            if( $isMobile) {
                echo "<input class='ui-button' style='padding:10px;' type='submit' name='Edit_Item_" . $itemType .  "_Submit' value='Edit " . $itemType . "'/><br>";
            }
            echo "</fieldset>";
            echo "</form>";
            echo "</div>";
            
            // ------------------------------------
            // RESTOCK ITEM MODAL
            // ------------------------------------
            echo "<div id='restock_item_" . $itemType . "' title='Restock " . $itemType . "' $hideForms>";
            echo "<form id='restock_item_" . $itemType . "_form' class='fancy' enctype='multipart/form-data' action='handle_forms.php' method='POST'>";
            echo "<fieldset>";
            echo "<label style='padding:5px 0px;' for='ItemNameDropdown'>" . $itemType . "</label>";
            echo $restock_dropdown;
            echo "<label style='padding:5px 0px;' for='NumberOfCans'>Number Of Units</label>";
            echo "<input type='tel' name='NumberOfCans' class='text ui-widget-content ui-corner-all'/>";
            echo "<label style='padding:5px 0px;' for='Cost'>Cost for Pack</label>";
            echo "<input type='tel' name='Cost' class='text ui-widget-content ui-corner-all'/>";
            
            echo "<input type='hidden' name='ItemType' value='" . $itemType . "'/><br>";
            echo "<input type='hidden' name='Restock' value='Restock'/><br>";
            echo "<input type='hidden' name='redirectURL' value='admin_x25.php'/><br>";
            
            if( $isMobile) {
                echo "<input style='padding:10px;' type='submit' name='Restock_Item_" . $itemType .  "_Submit' value='Restock " . $itemType . "'/><br>";
            }
            echo "</fieldset>";
            echo "</form>";
            echo "</div>";
            
            // ------------------------------------
            // INVENTORY MODAL - ALL ITEMS
            // ------------------------------------
            echo "<div id='inventory_" . $itemType . "' title='Enter Inventory' $hideForms>";
            echo "<form id='inventory_" . $itemType . "_form' class='fancy' enctype='multipart/form-data' action='handle_forms.php' method='POST'>";
            
            echo "<table>";
                echo "<tr><th>" . $itemType . "</th><th>Shelf Quantity</th><th>Backstock Quantity</th>";
                
            if(!$isMobile) {
                echo "<th>Price</th>";
                }
                
            echo "</tr>";
                
                $results = $db->query("SELECT Name, BackstockQuantity, ShelfQuantity, Price, ID FROM Item WHERE NOT Retired = 1 AND Hidden != 1 AND Type ='" . $itemType . "' AND (BackstockQuantity + ShelfQuantity) > 0 ORDER BY Name asc, Retired");
            $tabIndex = 1;
            while ($row = $results->fetchArray()) {
                        $item_name = $row['Name'];
                        $backstockquantity = $row['BackstockQuantity'];
                        $shelfquantity = $row['ShelfQuantity'];
                        $price = $row['Price'];
                        $item_id = $row['ID'];
                        echo "<tr>";
                        echo "<td><b>$item_name</b></td>";
                        echo "<input type='hidden' id='item_$item_id' name='ItemID[]' value='$item_id'/>";
                        echo "<td><input type='tel' onClick='this.select();' tabindex=$tabIndex id='ShelfQuantity_$item_id' value='$shelfquantity' name='ShelfQuantity[]' class='text ui-corner-all'/></td>";
                        echo "<td><input type='tel' tabindex=0 id='BackstockQuantity_$item_id' value='$backstockquantity' name='BackstockQuantity[]' class='text  ui-corner-all'/></td>";
                            
                        if( !$isMobile ) {
                        echo "<td><input tabindex=0 id='CurrentPrice_$item_id' value='$price' name='CurrentPrice[]' class='text ui-corner-all'/></td>";
                        }
                        echo "</tr>";
                
                        $tabIndex++;
                        // On change, update the backstock quantity if you are increasing the shelf quantity
                                echo "<script type='text/javascript'>";
                                echo "$( document ).ready( function() {";
                
                                echo "var originalShelf_$item_id = parseInt($('#ShelfQuantity_$item_id').val());";
                
                        echo "$('#ShelfQuantity_$item_id').change(function () {";
                                    echo "var newValue = parseInt($('#ShelfQuantity_$item_id').val());";
                                    echo "console.log('Original: [' + originalShelf_$item_id + ']');";
                                    echo "console.log('New: [' + newValue + ']');";
                                echo "if(newValue > originalShelf_$item_id) {";
                                echo "var takenFromBackstock = (newValue - originalShelf_$item_id);";
                            echo "console.log('Taken:' + takenFromBackstock);";
                                        echo "var backStockQuantity = $('#BackstockQuantity_$item_id').val();";
                                        echo "var newBackstockQuantity = backStockQuantity - takenFromBackstock;";
                
                                        echo "if(newBackstockQuantity >= 0 && takenFromBackstock > 0) {";
                                        echo "$('#BackstockQuantity_$item_id').val(newBackstockQuantity);";
                                                //echo "$('#BackStockUpdate_$item_id').html(' (Removed <b>' + takenFromBackstock + '</b> from Backstock)');";
                                                //echo "$('#BackStockUpdate_$item_id').css('color', 'green');";
                                                echo "originalShelf_$item_id = newValue;";
                                                echo "} else {";
                                                echo "$('#ShelfQuantity_$item_id').val(originalShelf_$item_id);";
                                                //echo "$('#BackStockUpdate_$item_id').html(' (Not enough backstock to remove ' + takenFromBackstock + ' cans!)');";
                                                //echo "$('#BackStockUpdate_$item_id').css('color', 'red');";
                                                echo "}";
                                                echo "}";
                                                echo "});";
                                                echo "});";
                                                echo "</script>";
                }
                echo "</table>";
                echo "<input type='checkbox' id='SendToSlack' checked name='SendToSlack'/> Send to Slack";
                
               echo "<input type='hidden' name='Inventory' value='Inventory'/><br>";
               echo "<input type='hidden' name='redirectURL' value='admin_x25.php'/><br>";
                
                
            if( $isMobile) {
                                   echo "<input style='padding:10px;' type='submit' name='Update_Item_" . $itemType .  "_Submit' value='Add Inventory'/><br>";
                }
                echo "</form>";
            echo "</div>";
        }
?>

<script type='text/javascript'>
$( document ).ready( function() {
    $('#EditSnackDropdown').change(function () {
        setItemInfo('Snack');
    });

    $('#EditSodaDropdown').change(function () {
        setItemInfo('Soda');
    });

    $('#EditUserDropdown').change(function () {
        setUserInfo();
    });
    
    setItemInfo('Soda');
    setItemInfo('Snack');
    setUserInfo();
});

function setItemInfo( type ) {
    var itemID = parseInt($('#Edit' + type + 'Dropdown').val());
    var itemName = $('#Item_' + type + '_Name_' + itemID).val();
    var itemPrice = $('#Item_' + type + '_Price_' + itemID).val();
    var itemDiscountPrice = $('#Item_' + type + '_DiscountPrice_' + itemID).val();
    var itemImageURL = $('#Item_' + type + '_ImageURL_' + itemID).val();
    var itemUnitName = $('#Item_' + type + '_UnitName_' + itemID).val();
    var itemThumbURL = $('#Item_' + type + '_ThumbURL_' + itemID).val();
    var itemChartColor = $('#Item_' + type + '_ChartColor_' + itemID).val();
    var itemRetired = $('#Item_' + type + '_Retired_' + itemID).val();
    console.log("Item ID: " +  itemID + " " + itemName + " " + itemPrice+ " " + itemChartColor + " " + itemRetired);
    
    $("#EditItemName" + type).val(itemName);
    $("#EditPrice" + type).val(itemPrice);
    $("#EditDiscountPrice" + type).val(itemDiscountPrice);
    $("#EditImageURL" + type).val(itemImageURL);
    $("#EditThumbURL" + type).val(itemThumbURL);
    $("#EditUnitName" + type).val(itemUnitName);
    $("#EditChartColor + type").val(itemChartColor);
    
    if( itemRetired == 0 ) {
        $("#EditStatusActive" + type).prop("checked", true);
        $("#EditStatusDiscontinued" + type).prop("checked", false);
    } else {
        $("#EditStatusActive" + type).prop("checked", false);
        $("#EditStatusDiscontinued" + type).prop("checked", true);
    }
}

function setUserInfo() {
    var userID = parseInt($('#EditUserDropdown').val());
    var slackID = $('#User_SlackID_' + userID).val();
    var inactive = $('#User_Inactive_' + userID).val();
    
    $("#SlackID").val(slackID);
    
    if( inactive == 0 ) {
        $("#Inactive").prop("checked", false);
    } else {
        $("#Inactive").prop("checked", true);
    }
}
</script>