<?php
    include(__DIR__ . "/../appendix.php" );
    if(!$isLoggedIn) {
        return;
    }
    // ------------------------------------
    // SHOPPING MODAL - We want Nick to access this form
    // ------------------------------------
    // Build Item Dropdown
    $results = $db->query("SELECT ID, Name, Type FROM Item WHERE Hidden != 1 AND Retired != 1 order by type desc, name asc");
    $item_options = "";
    $previousType = "";
    while ($row = $results->fetchArray()) {
        $item_id = $row['ID'];
        $item_name = $row['Name'];
        $item_type = $row['Type'];
        
        if( $item_type != $previousType ) {
            $previousType = $item_type;
            $item_options = $item_options . "<option disabled style='font-weight:bold; color: blue;' value='$previousType'>$previousType</option>";
        }
        
        $item_options = $item_options . "<option value='" . $row['ID'] . "'>$item_name</option>";
        
    }
    
    $results = $db->query("SELECT Store FROM Shopping_Guide Order by Date Desc LIMIT 1");
    $lastStore = $results->fetchArray()['Store'];
    
    if( $lastStore == "" ) { $lastStore = "BestProfits"; }
    
    $shopping_item_dropdown = "<select id='ItemDropdown' name='ItemDropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$item_options</select>";
    
    $stores = array( "Walmart", "Costco", "BJs", "Target", "Aldi", "Wegmans", "PriceRite", "Tops", "BestProfits" );
    $store_dropdown = "<select id='StoreDropdown' name='StoreDropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>";
    
    foreach( $stores as $store ) {
        $selected = "";
        if( $store == $lastStore ) {
            $selected = "selected";
        }
        
        
        if( $store == "BestProfits" ) {
            if( $isLoggedInAdmin ) {
                $store_dropdown = $store_dropdown . "<option $selected value='BestProfits'>( BEST PROFITS )</option>";
            }
        } else {
            $store_dropdown = $store_dropdown ."<option $selected value='$store'>$store</option>";
        }
    }
          
    $store_dropdown = $store_dropdown ."</select>";
    
    echo "<div id='shopping' title='Add Shopping'>";
    echo "<form id='shopping_form' class='fancy' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
    echo "<fieldset>";
    echo "<label for='ItemDropdown'>Item</label>";
    echo $shopping_item_dropdown;
    echo "<label for='StoreDropdown'>Store</label>";
    echo $store_dropdown;
    echo "<label for='PackQuantity'>Pack Quantity</label>";
    echo "<input style='font-size:1.8em;' type='tel' id='PackQuantity' name='PackQuantity' class='text ui-widget-content ui-corner-all'/>";
    echo "<label for='Price'>Price</label>";
    echo "<input style='font-size:1.8em;' type='tel' id='Price' name='Price' class='text ui-widget-content ui-corner-all'/>";
    
    echo "<div class='radio_status'>";
    echo "<input class='radio' type='radio' id='RegularPrice' name='PriceType' value='regular' checked />";
    echo "<label for='RegularPrice'>Regular Price</label>";
    echo "<input class='radio' type='radio' id='SalePrice' name='PriceType' value='sale' />";
    echo "<label for='SalePrice'>Sale Price</label>";
    echo "</div>";
    
    echo "<input type='hidden' name='Shopping' value='Shopping'/><br>";
    echo "<input type='hidden' name='Submitter' value='" . $_SESSION["UserName"] . "'/><br>";
    echo "<input type='hidden' name='redirectURL' value='" . ADMIN_SHOPPING_GUIDE_LINK . "'/><br>";
    
    echo "<input class='ui-button' style='padding:10px;' type='submit' name='Shopping_Submit' value='Add Shopping'/><br>";
    
    echo "</fieldset>";
    echo "</form>";
    echo "</div>";

    if(!$isLoggedInAdmin) {
        return;
    }
    
    // I want the modals back in the mobile sites
    
    if( $url == ADMIN_SHOPPING_GUIDE_LINK ) {
        $hideForms = "style='display:none;'";
    } else {
        $hideForms = "";
    }
        
//     if( !$isMobile ) {
//             $hideForms = "";
//     }

    $results = $db->query("SELECT FirstName, LastName, UserID, SlackID, Inactive, IsCoop, SodaBalance, SnackBalance, AnonName From User Order By FirstName Asc");
    $user_info = "";
    
    $user_options = "";
    $edit_user_options = "";
     
    while ($row = $results->fetchArray()) {
        $fullName = $row['FirstName'] . " " . $row['LastName'];
        $userID = $row['UserID'];
        $slackID = $row['SlackID'];
        $inactive = $row['Inactive'];
        $isCoop = $row['IsCoop'];
        $anonName = $row['AnonName'];
        $sodaBalance = number_format( round( $row['SodaBalance'], 2), 2);
        $snackBalance = number_format( round( $row['SnackBalance'], 2), 2);
        $inactive_strikethrough = "";
        
        if( $inactive != "1" ) {
            $user_options = $user_options . "<option value='$userID'>$fullName</option>";
        } else {
            $inactive_strikethrough = " style='font-weight:bold; color:#9b0909'";
        }
        
        $edit_user_options = $edit_user_options . "<option $inactive_strikethrough value='$userID'>$fullName</option>";
        
        $user_info = $user_info .
        "<input type='hidden' id='User_SlackID_$userID' value='$slackID'/>" . 
        "<input type='hidden' id='User_AnonName_$userID' value='$anonName'/>" . 
        "<input type='hidden' id='User_Inactive_$userID' value='$inactive'/>" .
        "<input type='hidden' id='User_IsCoop_$userID' value='$isCoop'/>" .
        "<input type='hidden' id='User_SodaBalance_$userID' value='$sodaBalance'/>" .
        "<input type='hidden' id='User_SnackBalance_$userID' value='$snackBalance'/>";
    }
     
    $user_dropdown = "<select id='UserDropdown' name='UserDropdown' class='text ui-widget-content ui-corner-all'><option value='0'>(Manual Count)</option>$user_options</select>";
    $edit_user_dropdown = "<select id='EditUserDropdown' name='EditUserDropdown' class='text ui-widget-content ui-corner-all'>$edit_user_options</select>";
     
    $method_dropdown = "<select id='MethodDropdown' name='MethodDropdown' class='text ui-widget-content ui-corner-all'>" .
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
//     $dateObject = DateTime::createFromFormat( 'Y-m-d H:i:s', time() );
//     $monthLabel = $dateObject->format('F Y');
    
    
    $paymentMonth_options = "";
    $monthLabel = "";
    $monthsAgo = 0;
    
    while ($monthLabel != "March 2018" ) {
        $firstOfMonth = mktime(0, 0, 0, date("m") - $monthsAgo, 1, date("Y") );
        $monthLabel = date('F Y', $firstOfMonth);
        $paymentMonth_options = $paymentMonth_options . "<option value='$monthLabel'>$monthLabel</option>";
        $monthsAgo++;
    }
    
    $paymentMonth_dropdown = "<select id='MonthDropdown' name='MonthDropdown' class='text ui-widget-content ui-corner-all'>$paymentMonth_options</select>";
    
    echo "<div id='payment' title='Add Payment' $hideForms>";
    echo "<form id='payment_form' class='fancy' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
    echo "<fieldset>";
    echo "<label for='UserDropdown'>User</label>";
    echo $user_dropdown;
    echo "<label for='MonthDropdown'>Payment Month</label>";
    echo $paymentMonth_dropdown;
    echo "<label for='Method'>Method</label>";
    echo $method_dropdown;
    echo "<label for='SodaAmount'>Soda Amount</label>";
    echo "<input style='font-size:2em;' type='tel' id='SodaAmount' name='SodaAmount' class='text ui-widget-content ui-corner-all'/>";
    echo "<label for='SnackAmount'>Snack Amount</label>";
    echo "<input style='font-size:2em;' type='tel' id='SnackAmount' name='SnackAmount' class='text ui-widget-content ui-corner-all'/>";
//     echo "<label for='Note'>Note</label>";
//     echo "<input type='text' name='Note' class='text ui-widget-content ui-corner-all'/>";
    
    echo $user_info;
    echo "<input type='hidden' name='Payment' value='Payment'/><br>";
    echo "<input type='hidden' name='redirectURL' value='" . ADMIN_PAYMENTS_LINK . "'/><br>";
    
    echo "</fieldset>";
    echo "</form>";
    echo "</div>";

    // ------------------------------------
    // EDIT USER MODAL
    // ------------------------------------
    echo "<div id='edit_user' title='Edit User' $hideForms>";
    echo "<form id='edit_user_form' class='fancy' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
    echo "<label for='EditUserDropdown'>User</label>";
    echo $edit_user_dropdown;
    echo "<label for='SlackID'>Slack ID</label>";
    echo "<input type='text' id='SlackID' name='SlackID' class='text ui-widget-content ui-corner-all'/>";
    
    echo "<label for='AnonName'>Anon Name</label>";
    echo "<input type='text' id='AnonName' name='AnonName' class='text ui-widget-content ui-corner-all'/>";
    
    echo "<div style='padding:5px 0px;'>";
        echo "<label style='display:inline;' for='Inactive'>Inactive:</label>";
        echo "<input style='display:inline;' type='checkbox' id='Inactive' name='Inactive'/>";
    echo "</div>";
    
    echo "<div style='padding:5px 0px;'>";
    echo "<label style='display:inline;' for='IsCoop'>Co-op:</label>";
    echo "<input style='display:inline;' type='checkbox' id='IsCoop' name='IsCoop'/>";
    echo "</div>";
    
    echo "<div style='padding:5px 0px;'>";
        echo "<label style='padding:5px 0px; display:inline;' for='ResetPassword'>Reset Password?</label>";
        echo "<input style='display:inline;' type='checkbox' name='ResetPassword'/>";
    echo "</div>";
    
    echo "<input type='hidden' name='EditUser' value='EditUser'/><br>";
    echo "<input type='hidden' name='redirectURL' value='" . ADMIN_LINK . "'/><br>";
    
    echo "</fieldset>";
    echo "</form>";
    echo "</div>";

    
    
    function buildModalsForType( $db, $itemType, $hideForms, $isMobile ) {
        // Build Item Dropdown
        $results = $db->query("SELECT ID, Name, Price, Retired, ChartColor, ImageURL, ThumbURL, UnitName, UnitNamePlural, DiscountPrice, Alias, CurrentFlavor, ExpirationDate FROM Item WHERE Type ='" . $itemType . "' AND Hidden != 1 order by retired asc, name asc");
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
            $item_unit_name_plural = $row['UnitNamePlural'];
            $item_alias = $row['Alias'];
            $item_currentFlavor = $row['CurrentFlavor'];
            $item_expiration_date= $row['ExpirationDate'];
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
            "<input type='hidden' id='Item_" . $itemType . "_UnitNamePlural_$item_id' value='$item_unit_name_plural'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_Alias_$item_id' value='$item_alias'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_CurrentFlavor_$item_id' value='$item_currentFlavor'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_ExpirationDate_$item_id' value='$item_expiration_date'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_Retired_$item_id' value='$item_retired'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_ChartColor_$item_id' value='$item_chart_color'/>";
        }
            
        $edit_dropdown = "<select id='Edit" . $itemType . "Dropdown' name='Edit" . $itemType . "Dropdown' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$item_options</select>";
            
        $restock_dropdown = "<select id='RestockDropdown' name='RestockDropdown' style='padding:5px; width:100%; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$item_options_no_discontinued</select>";
        
        $defective_dropdown = "<select id='DefectiveDropdown' name='DefectiveDropdown' style='padding:5px; width:100%; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$item_options_no_discontinued</select>";
            
        
        // ------------------------------------
        // ADD ITEM MODAL
        // ------------------------------------
        echo "<div id='add_item_" . $itemType . "' class='fancy' title='Add " . $itemType . "' $hideForms>";
        echo "<form id='add_item_" . $itemType . "_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        echo "<fieldset>";
        echo "<label for='ItemName'>Name</label>";
        echo "<input type='text' autocorrect='off' autocapitalize='off' maxlength='40'; name='ItemName' class='text ui-widget-content ui-corner-all'>";
        echo "<label for='ChartColor'>Color</label>";
        echo "<input name='ChartColor' class='color text ui-widget-content ui-corner-all'>";
        echo "<label for='CurrentPrice'>Price of Can</label>";
        echo "<input type='tel' name='CurrentPrice' value='0.50' class='text ui-widget-content ui-corner-all'/>";
        
        echo "<input type='hidden' name='ItemType' value='$itemType'/><br>";
        echo "<input type='hidden' name='AddItem' value='AddItem'/><br>";
        echo "<input type='hidden' name='redirectURL' value='" . ADMIN_LINK . "'/><br>";
        
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
        $editUnitNamePluralID = "EditUnitNamePlural" . $itemType;
        $editAliasID = "EditAlias" . $itemType;
        $editCurrentFlavorID = "EditCurrentFlavor" . $itemType;
        $editExpirationDateID = "EditExpirationDate" . $itemType;
        $editActiveID = "EditStatusActive" . $itemType;
        $editDiscontinuedID = "EditStatusDiscontinued" . $itemType;
        $editStatusID = "EditStatus" . $itemType;
        
        echo "<div id='edit_item_" . $itemType . "' class='fancy' title='Edit " . $itemType . "' $hideForms>";
        echo "<form id='edit_item_" . $itemType . "_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        echo "<fieldset>";
        echo "<label for='ItemNameDropdown'>" . $itemType . "</label>";
        echo $edit_dropdown;
        echo "<label for='ItemName'>Name</label>";
        echo "<input type='text' autocorrect='off' autocapitalize='off' maxlength='30'; id='$editNameID' name='$editNameID' class='text ui-widget-content ui-corner-all'>";
        echo "<label for='ChartColor'>Color</label>";
        echo "<input id='$editChartColorID' name='$editChartColorID' class='color text ui-widget-content ui-corner-all'>";
        echo "<label for='CurrentPrice'>Price</label>";
        echo "<input type='tel' id='$editPriceID' name='$editPriceID' class='text ui-widget-content ui-corner-all'/>";
        echo "<label for='CurrentPrice'>Discount Price</label>";
        echo "<input type='tel' id='$editDiscountPriceID' name='$editDiscountPriceID' class='text ui-widget-content ui-corner-all'/>";

        echo "<label style='display:inline-block' for='ImageURL'>Image URL</label> <span style='color:#55a4ff' id='$editImageURLID'></span>";
        echo "<input style='padding-bottom:20px;' name='uploadedImage' type='file' />";

        echo "<label style='display:inline-block' for='ThumbURL'>Thumb URL</label> <span style='color:#55a4ff' id='$editThumbURLID'></span>";
        echo "<input style='padding-bottom:20px;' name='uploadedThumb' type='file' />";

        echo "<label for='UnitName'>Unit Name</label>";
        echo "<input id='$editUnitNameID' name='$editUnitNameID' class='text ui-widget-content ui-corner-all'>";
        echo "<label for='UnitNamePlural'>Unit Name (plural)</label>";
        echo "<input id='$editUnitNamePluralID' name='$editUnitNamePluralID' class='text ui-widget-content ui-corner-all'>";
        echo "<label for='Alias'>Alias</label>";
        echo "<input id='$editAliasID' name='$editAliasID' class='text ui-widget-content ui-corner-all'>";
        echo "<label for='ExpirationDate'>Expiration Date</label>";
        echo "<input id='$editExpirationDateID' name='$editExpirationDateID' class='text ui-widget-content ui-corner-all'>";
        echo "<div class='radio_status'>";
        echo "<label for='CurrentFlavor'>Current Flavor</label>";
        echo "<input id='$editCurrentFlavorID' name='$editCurrentFlavorID' class='text ui-widget-content ui-corner-all'>";
        echo "<input class='radio' type='radio' id='$editActiveID' name='$editStatusID' value='active' checked />";
        echo "<label for='$editActiveID'>Active</label>";
        echo "<input class='radio' type='radio' id='$editDiscontinuedID' name='$editStatusID' value='discontinued' />";
        echo "<label for='$editDiscontinuedID'>Discontinued</label>";
        echo "</div>";
        
        echo $item_info;
        echo "<input type='hidden' name='ItemType' value='$itemType'/><br>";
        echo "<input type='hidden' name='EditItem' value='EditItem'/><br>";
        echo "<input type='hidden' name='redirectURL' value='" . ADMIN_LINK . "'/><br>";
        
        if( $isMobile) {
            echo "<input class='ui-button' style='padding:10px;' type='submit' name='Edit_Item_" . $itemType .  "_Submit' value='Edit " . $itemType . "'/><br>";
        }
        echo "</fieldset>";
        echo "</form>";
        echo "</div>";
        
        // ------------------------------------
        // RESTOCK ITEM MODAL
        // ------------------------------------
        echo "<div style='width:775px;' id='restock_item_" . $itemType . "' title='Restock " . $itemType . "' $hideForms>";
        echo "<form id='restock_item_" . $itemType . "_form' class='fancy' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        echo "<label for='ItemNameDropdown'>" . $itemType . "</label>";
        echo $restock_dropdown;
        echo "<table>";
        
        echo "<tr>";
        echo "<td>";
        echo "<label for='NumberOfCans'>Number Of Units</label>";
        echo "</td>";
        echo "<td>";
        echo "<label for='Cost'>Cost for Pack</label>";
        echo "</td>";
        echo "<td>";
        echo "<label for='Multiplier'>Multiplier</label>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td>";
        echo "<input type='tel' style='font-size: 2em;' name='NumberOfCans' class='text ui-widget-content ui-corner-all'/>";
        echo "</td>";
        echo "<td>";
        echo "<input type='tel' style='font-size: 2em; 'name='Cost' class='text ui-widget-content ui-corner-all'/>";
        echo "</td>";
        echo "<td>";
        echo "<input type='tel' style='font-size: 2em; 'name='Multiplier' value='1' class='text ui-widget-content ui-corner-all'/>";
        echo "</td>";
        echo "</tr>";
        
        echo "</table>";
        
        $results = $db->query("SELECT ID, Name, Price, Retired, ChartColor, ImageURL, ThumbURL, UnitName, UnitNamePlural, DiscountPrice, Alias, CurrentFlavor, ExpirationDate FROM Item WHERE Type ='" . $itemType . "' AND Hidden != 1 order by name asc");
        $item_options = "";
        $item_options_no_discontinued = "";
        $item_info = "";
        while ($row = $results->fetchArray()) {
            $item_id = $row['ID'];
            $item_name = $row['Name'];
        }
        
        echo "<input type='hidden' name='ItemType' value='" . $itemType . "'/><br>";
        echo "<input type='hidden' name='Restock' value='Restock'/><br>";
        echo "<input type='hidden' name='redirectURL' value='" . ADMIN_LINK . "'/><br>";
        
        if( $isMobile) {
            echo "<input style='padding:10px;' type='submit' name='Restock_Item_" . $itemType .  "_Submit' value='Restock " . $itemType . "'/><br>";
        }
        echo "</form>";
        echo "</div>";
        
        // ------------------------------------
        // DEFECTIVES ITEM MODAL
        // ------------------------------------
        echo "<div id='defective_item_" . $itemType . "' title='Defective " . $itemType . "' $hideForms>";
        echo "<form id='defective_item_" . $itemType . "_form' class='fancy' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        echo "<label for='ItemNameDropdown'>" . $itemType . "</label>";
        echo $defective_dropdown;
        
        echo "<label for='NumberOfUnits'>Number Of Units</label>";
        echo "<input type='tel' style='font-size: 2em;' name='NumberOfUnits' class='text ui-widget-content ui-corner-all'/>";

        echo "<input type='hidden' name='ItemType' value='" . $itemType . "'/><br>";
        echo "<input type='hidden' name='Defective' value='Defective'/><br>";
        echo "<input type='hidden' name='redirectURL' value='" . ADMIN_DEFECTIVES_LINK . "'/><br>";
        
        if( $isMobile) {
            echo "<input style='padding:10px;' type='submit' name='Defective_Item_" . $itemType .  "_Submit' value='Defective " . $itemType . "'/><br>";
        }
        echo "</form>";
        echo "</div>";
        
        // ------------------------------------
        // INVENTORY MODAL - ALL ITEMS
        // ------------------------------------
        echo "<div id='inventory_" . $itemType . "' title='Enter Inventory' $hideForms>";
        echo "<form id='inventory_" . $itemType . "_form' class='fancy' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        
        echo "<table>";
        echo "<tr><th>" . $itemType . "</th><th>Add to Shelf</th><th>Shelf Quantity</th><th>Backstock Quantity</th></tr>";
            
        $results = $db->query("SELECT Name, BackstockQuantity, ShelfQuantity, ID FROM Item WHERE Hidden != 1 AND Type ='" . $itemType . "' AND (BackstockQuantity + ShelfQuantity) > 0 ORDER BY ShelfQuantity DESC, Name asc, Retired");
        $tabIndex = 1;
        while ($row = $results->fetchArray()) {
            $item_name = $row['Name'];
            $backstockquantity = $row['BackstockQuantity'];
            $shelfquantity = $row['ShelfQuantity'];
            $item_id = $row['ID'];
            echo "<tr>";
            echo "<td><b>$item_name</b></td>";
            echo "<input type='hidden' id='item_$item_id' name='ItemID[]' value='$item_id'/>";
            echo "<td><input type='tel' onClick='this.select();' tabindex=$tabIndex id='AddToShelf_$item_id' value='0' name='AddToShelf[]' class='text ui-corner-all'/></td>";
            echo "<td><input type='tel' onClick='this.select();' tabindex=0 id='ShelfQuantity_$item_id' value='$shelfquantity' name='ShelfQuantity[]' class='text ui-corner-all'/></td>";
            echo "<td><input type='tel' tabindex=0 id='BackstockQuantity_$item_id' value='$backstockquantity' name='BackstockQuantity[]' class='text  ui-corner-all'/></td>";
            echo "</tr>";
    
            $tabIndex++;
            
            // On change, update the backstock quantity if you are increasing the shelf quantity
            echo "<script type='text/javascript'>";
            echo "$( document ).ready( function() {";

            echo "$('#AddToShelf_$item_id').change(function () {";
                echo "var incrementAmount = parseInt($('#AddToShelf_$item_id').val());";
                echo "console.log('New Increment: [' + incrementAmount + ']');";
                echo "if(incrementAmount > 0) {";
                    
                    echo "var backStockQuantity = parseInt($('#BackstockQuantity_$item_id').val());";
                    echo "var shelfQuantity = parseInt($('#ShelfQuantity_$item_id').val());";
                    echo "var newBackstockQuantity = backStockQuantity - incrementAmount;";
                    echo "var newShelfQuantity = shelfQuantity + incrementAmount;";
        
                    echo "if(newBackstockQuantity >= 0) {";
                        echo "$('#BackstockQuantity_$item_id').val(newBackstockQuantity);";
                        echo "$('#ShelfQuantity_$item_id').val(newShelfQuantity);";
                        
                        echo "$('#BackstockQuantity_$item_id').css('background-color', '#a6ff9b');";
                        echo "$('#ShelfQuantity_$item_id').css('background-color', '#ff9b9b');";
                        
                        // Dont wan't to think changing from 6 to 7 will increase by 1, it will increase 7 again
                        echo "$('#AddToShelf_$item_id').attr('placeholder', '+' + incrementAmount + ' units');";
                        echo "$('#AddToShelf_$item_id').val('');";
                    echo "} else {";
                        echo "alert('There is not enough backstock available for [' + incrementAmount + '] units.');";
                    echo "}";
                echo "}";
            echo "});"; // On Change
            echo "});"; // Document Ready
            echo "</script>";
        }
        echo "</table>";
        echo "<input type='checkbox' id='SendToSlack' checked name='SendToSlack'/> Send to Slack";
            
        echo "<input type='hidden' name='Inventory' value='Inventory'/><br>";
        echo "<input type='hidden' name='redirectURL' value='" . ADMIN_LINK . "'/><br>";
            
            
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

    $('#UserDropdown').change(function () {
        setUserPaymentInfo();
    });

    $('#EditExpirationDateSnack').datepicker();

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
    var itemUnitNamePlural = $('#Item_' + type + '_UnitNamePlural_' + itemID).val();
    var itemAlias = $('#Item_' + type + '_Alias_' + itemID).val();
    var itemCurrentFlavor = $('#Item_' + type + '_CurrentFlavor_' + itemID).val();
    var itemExpirationDate = $('#Item_' + type + '_ExpirationDate_' + itemID).val();
    var itemThumbURL = $('#Item_' + type + '_ThumbURL_' + itemID).val();
    var itemChartColor = $('#Item_' + type + '_ChartColor_' + itemID).val();
    var itemRetired = $('#Item_' + type + '_Retired_' + itemID).val();
    console.log("Item ID: " +  itemID + " " + itemName + " " + itemPrice+ " " + itemChartColor + " " + itemRetired);
    
    $("#EditItemName" + type).val(itemName);
    $("#EditPrice" + type).val(itemPrice);
    $("#EditDiscountPrice" + type).val(itemDiscountPrice);
    $("#EditImageURL" + type).html("(" + itemImageURL + ")");
    $("#EditThumbURL" + type).html("(" + itemThumbURL + ")");
    $("#EditUnitName" + type).val(itemUnitName);
    $("#EditUnitNamePlural" + type).val(itemUnitNamePlural);
    $("#EditAlias" + type).val(itemAlias);
    $("#EditCurrentFlavor" + type).val(itemCurrentFlavor);
    $("#EditExpirationDate" + type).val(itemExpirationDate);
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
    var anonName = $('#User_AnonName_' + userID).val();
    var inactive = $('#User_Inactive_' + userID).val();
    var isCoop = $('#User_IsCoop_' + userID).val();
    
    $("#SlackID").val(slackID);
    $("#AnonName").val(anonName);
    
    if( inactive == 0 ) {
        $("#Inactive").prop("checked", false);
    } else {
        $("#Inactive").prop("checked", true);
    }

    if( isCoop == 0 ) {
        $("#IsCoop").prop("checked", false);
    } else {
        $("#IsCoop").prop("checked", true);
    }
}

function setUserPaymentInfo() {
    var userID = parseInt($('#UserDropdown').val());
    var sodaBalance = $('#User_SodaBalance_' + userID).val();
    var snackBalance = $('#User_SnackBalance_' + userID).val();
    
    $("#SodaAmount").val(sodaBalance);
    $("#SnackAmount").val(snackBalance);
}
</script>