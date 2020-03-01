<?php
    include(__DIR__ . "/../appendix.php" );
    if( !IsLoggedIn() ) {
        return;
    }

    // I want the modals back in the mobile sites
    $hideForms = "style='display:none;'";

    if( IsAdminLoggedIn() || IsVendor() ) {
        buildShoppingModal( $db, $hideForms );
        buildModalsForType($db, "Soda", $hideForms );
        buildModalsForType($db, "Snack", $hideForms );

        // ADMIN ONLY - NO VENDORS
        if( IsAdminLoggedIn() ) {
            $statement = $db->prepare("SELECT FirstName, LastName, UserID, SlackID, Inactive, IsCoop, SodaBalance, SnackBalance, AnonName, IsVendor From User Order By FirstName Asc");
            $results = $statement->execute();

            $user_info = "";

            $user_options = "";
            $edit_user_options = "";

            while ($row = $results->fetchArray()) {
                $fullName = $row['FirstName'] . " " . $row['LastName'];
                $userID = $row['UserID'];
                $slackID = $row['SlackID'];
                $inactive = $row['Inactive'];
                $isCoop = $row['IsCoop'];
                $isVendor = $row['IsVendor'];
                $anonName = $row['AnonName'];
                $inactive_strikethrough = "";

                if( $inactive == "1" ) {
                    $inactive_strikethrough = " style='font-weight:bold; color:#9b0909'";
                }

                $edit_user_options = $edit_user_options . "<option $inactive_strikethrough value='$userID'>$fullName</option>";

                $user_info = $user_info .
                "<input type='hidden' id='User_SlackID_$userID' value='$slackID'/>" .
                "<input type='hidden' id='User_AnonName_$userID' value='$anonName'/>" .
                "<input type='hidden' id='User_Inactive_$userID' value='$inactive'/>" .
                "<input type='hidden' id='User_IsCoop_$userID' value='$isCoop'/>" .
                "<input type='hidden' id='User_IsVendor_$userID' value='$isVendor'/>";
            }

            $edit_user_dropdown = "<select id='EditUserDropdown' name='EditUserDropdown' class='text ui-widget-content ui-corner-all'>$edit_user_options</select>";

            buildPaymentModal( $user_info, $hideForms );
            buildEditUserModal( $edit_user_dropdown, $hideForms );
            buildCreditUserModal( $edit_user_dropdown, $hideForms );
        }
    }

    /**
     * @param $db SQLite3
     * @param $hideForms
     */
    function buildShoppingModal( $db, $hideForms ) {
        // Build Item Dropdown
        $statement = $db->prepare("SELECT ID, Name, Type " .
            "FROM Item " .
            "WHERE Hidden != 1 AND Retired != 1 " .
            "ORDER BY type desc, name asc");
        $results = $statement->execute();

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

        $statement = $db->prepare("SELECT Store FROM Shopping_Guide Order by Date Desc LIMIT 1");
        $results = $statement->execute();

        $lastStore = $results->fetchArray()['Store'];

        if( $lastStore == "" ) { $lastStore = "BestProfits"; }

        $shopping_item_dropdown = "<select id='ItemDropdown' name='ItemDropdown'>$item_options</select>";

        $stores = array( "Walmart", "Costco", "BJs", "Target", "Aldi", "Wegmans", "PriceRite", "Tops", "BestProfits" );
        $store_dropdown = "<select id='StoreDropdown' name='StoreDropdown'>";

        foreach( $stores as $store ) {
            $selected = "";
            if( $store == $lastStore ) {
                $selected = "selected";
            }

            if( $store == "BestProfits" ) {
                if( IsAdminLoggedIn() ) {
                    $store_dropdown = $store_dropdown . "<option $selected value='BestProfits'>( BEST PROFITS )</option>";
                }
            } else {
                $store_dropdown = $store_dropdown ."<option $selected value='$store'>$store</option>";
            }
        }

        $store_dropdown = $store_dropdown ."</select>";

        echo "<div id='shopping_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar'>";
        echo "Add Shopping";
        echo "<span id='shopping_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='payment_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";

        echo "<ul>";

        echo "<li>";
        echo "<label for='ItemDropdown'>Item</label>";
        echo $shopping_item_dropdown;
        echo "</li>";

        echo "<li>";
        echo "<label for='StoreDropdown'>Store</label>";
        echo $store_dropdown;
        echo "</li>";

        echo "<li>";
        echo "<label for='PackQuantity'>Pack Quantity</label>";
        echo "<input type='tel' id='PackQuantity' name='PackQuantity'/>";
        echo "</li>";

        echo "<li class='PriceSection'>";
        echo "<label id='PriceLabel' for='Price'>Price</label>";
        echo "<input type='tel' id='Price' name='Price'/>";
        echo "</li>";

        echo "<li class='PriceSection'>";
        echo "<div id='PriceChoices' class='radio_status'>";
        echo "<input class='radio' type='radio' id='RegularPrice' name='PriceType' value='regular' checked />";
        echo "<label for='RegularPrice'>Regular Price</label>";
        echo "<input style='margin-left: 10px;' class='radio' type='radio' id='SalePrice' name='PriceType' value='sale' />";
        echo "<label for='SalePrice'>Sale Price</label>";
        echo "</div>";
        echo "</li>";

        echo "<li class='buttons'>";
        echo "<input style='padding:10px;' type='submit' name='Shopping_Submit' value='Add Shopping'/>";
        echo "</li>";

        echo "<input type='hidden' name='Shopping' value='Shopping'/>";
        echo "<input type='hidden' name='Submitter' value='" . $_SESSION["UserName"] . "'/>";
        echo "<input type='hidden' name='redirectURL' value='" . ADMIN_CHECKLIST_LINK . "'/>";

    //    echo "<input class='ui-button' style='padding:10px;' type='submit' name='Shopping_Submit' value='Add Shopping'/><br>";

        echo "</ul>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }

    function buildPaymentModal( $user_info, $hideForms ) {
        $method_dropdown = "<select id='MethodDropdown' name='MethodDropdown' class='text ui-widget-content ui-corner-all'>" .
            "<option value='None'>None</option>" .
            "<option value='Venmo'>Venmo</option>" .
            "<option value='Square Cash'>Square Cash</option>" .
            "<option value='PayPal'>PayPal</option>" .
            "<option value='Cash'>Cash</option>" .
            "<option value='Refund'>Refund</option>" .
            "<option value='Google Pay'>Google Pay</option>" .
            "<option value='Other'>Other</option>" .
            "</select>";

        echo "<div id='payment_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar'>";
        echo "Add Payment";
        echo "<span id='payment_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='payment_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";

        echo "<ul>";

        echo "<li>";
        echo "<label for='UserID'>User</label>";
        echo "<input type='hidden' id='UserID' name='UserID' value='-1'/>";
        echo "<div id='UserIDLabel'></div>";
        echo "</li>";

        echo "<ul>";

        echo "<li>";
        echo "<label for='Month'>Payment Month</label>";
        echo "<input type='hidden' id='Month' name='Month' value='-1'/>";
        echo "<div id='MonthLabel'></div>";
        echo "</li>";

        echo "<li>";
        echo "<label for='Method'>Method</label>";
        echo $method_dropdown;
        echo "</li>";

        echo "<li>";
        echo "<label for='TotalAmount'>Payment Amount</label>";
        echo "<input type='tel' id='TotalAmount' class='text ui-widget-content ui-corner-all'/>";
        echo "</li>";

        echo "<input type='hidden' id='SodaUnpaid' value='-1'/>";
        echo "<input type='hidden' id='SnackUnpaid' value='-1'/>";

        echo "<li>";
        echo "<label for='TotalAmount'>Payment for Soda</label>";
        echo "<input style='color:#225aa4; font-weight:bold;' readonly type='tel' id='SodaAmount' name='SodaAmount' class='text ui-widget-content ui-corner-all'/>";
        echo "</li>";

        echo "<li>";
        echo "<label for='TotalAmount'>Payment for Snack</label>";
        echo "<input style='color:#a42222; font-weight:bold;' readonly type='tel' id='SnackAmount' name='SnackAmount' class='text ui-widget-content ui-corner-all'/>";
        echo "</li>";

        echo "<li class='buttons'>";
        echo "<input style='padding:10px;' type='submit' name='Payment_Submit' value='Add Payment'/>";
        echo "</li>";

        echo $user_info;
        echo "<input type='hidden' name='SodaCommission' id='SodaCommission'/>";
        echo "<input type='hidden' name='SnackCommission' id='SnackCommission'/>";
        echo "<input type='hidden' name='Payment' value='Payment'/>";
        echo "<input type='hidden' name='redirectURL' value='" . ADMIN_PAYMENTS_LINK . "'/>";

        echo "</ul>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }

    function buildEditUserModal( $edit_user_dropdown, $hideForms ) {
        echo "<div id='edit_user_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar'>";
        echo "Edit User";
        echo "<span id='edit_user_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='edit_user_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";

        echo "<ul>";

        echo "<li>";
        echo "<label for='EditUserDropdown'>User</label>";
        echo $edit_user_dropdown;
        echo "</li>";

        echo "<li>";
        echo "<label for='SlackID'>Slack ID</label>";
        echo "<input type='text' id='SlackID' name='SlackID' class='text ui-widget-content ui-corner-all'/>";
         echo "</li>";

        echo "<li>";
        echo "<label for='AnonName'>Anon Name</label>";
        echo "<input type='text' id='AnonName' name='AnonName' class='text ui-widget-content ui-corner-all'/>";
        echo "</li>";

        echo "<div class='neptuneRow'>";
            echo "<label style='display:inline;' for='Inactive'>Inactive:</label>";
            echo "<input style='display:inline;' type='checkbox' id='Inactive' name='Inactive'/>";
        echo "</div>";

        echo "<div class='neptuneRow'>";
            echo "<label style='display:inline;' for='IsCoop'>Co-op:</label>";
            echo "<input style='display:inline;' type='checkbox' id='IsCoop' name='IsCoop'/>";
        echo "</div>";

        echo "<div class='neptuneRow'>";
            echo "<label style='display:inline;' for='IsVendor'>Vendor:</label>";
            echo "<input style='display:inline;' type='checkbox' id='IsVendor' name='IsVendor'/>";
        echo "</div>";

        echo "<div class='neptuneRow'>";
            echo "<label style='padding:5px 0px; display:inline;' for='ResetPassword'>Reset Password?</label>";
            echo "<input style='display:inline;' type='checkbox' name='ResetPassword'/>";
        echo "</div>";

        echo "<li class='buttons'>";
        echo "<input style='padding:10px;' type='submit' name='Edit_User_Submit' value='Save User'/>";
        echo "</li>";

        echo "<input type='hidden' name='EditUser' value='EditUser'/>";
        echo "<input type='hidden' name='redirectURL' value='" . ADMIN_LINK . "'/>";

        echo "</ul>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }

    function buildCreditUserModal( $edit_user_dropdown, $hideForms ) {

        echo "<div id='credit_user_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar'>";
        echo "Credit User";
        echo "<span id='credit_user_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='credit_user_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";

        echo "<ul>";

        echo "<li>";
        echo "<label for='EditUserDropdown'>User</label>";
        echo $edit_user_dropdown;
        echo "</li>";

        echo "<li>";
        echo "<label for='CreditAmount'>Credits</label>";
        echo "<input type='text' id='CreditAmount' name='CreditAmount' class='text ui-widget-content ui-corner-all'/>";
        echo "</li>";

        echo "<div class='neptuneRow'>";
            echo "<label style='display:inline;' for='ReturnCredits'>Credits being Returned</label>";
            echo "<input style='display:inline;' type='checkbox' id='ReturnCredits' name='ReturnCredits'/>";
        echo "</div>";

        echo "<li class='buttons'>";
        echo "<input style='padding:10px;' type='submit' name='Credit_User_Submit' value='Credit User'/>";
        echo "</li>";

        echo "<input type='hidden' name='CreditUser' value='EditUser'/>";
        echo "<input type='hidden' name='redirectURL' value='" . ADMIN_LINK . "'/>";

        echo "</ul>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }
    /**
     * @param $db SQLite3
     * @param $itemType
     * @param $hideForms
     */
    function buildModalsForType( $db, $itemType, $hideForms ) {

        $redirectURL = ADMIN_LINK;

        if( IsVendor() ) {
            $redirectURL = VENDOR_LINK;
        }

        $andVendorIDClause = "";

        if( IsVendor() ) {
            $andVendorIDClause = " AND VendorID = " .  $_SESSION['UserID'];
        }
        // Build Item Dropdown
        $statement = $db->prepare("SELECT ID, Name, Price, Retired, ImageURL, ThumbURL, UnitName, Tag, UnitNamePlural, DiscountPrice, Alias, CurrentFlavor, ExpirationDate " .
            "FROM Item " .
            "WHERE Type = :itemType AND Hidden != 1 $andVendorIDClause " .
            "ORDER BY retired asc, name asc");
        $statement->bindValue( ":itemType", $itemType );
        $results = $statement->execute();

        $item_options = "";
        $item_options_no_discontinued = "";
        $item_info = "";
        while ($row = $results->fetchArray()) {
            $item_id = $row['ID'];
            $item_name = $row['Name'];
            $item_price = $row['Price'];
            $item_discount_price = $row['DiscountPrice'];
            $item_retired = $row['Retired'];
            $item_imageURL = $row['ImageURL'];
            $item_thumbURL = $row['ThumbURL'];
            $item_unit_name = $row['UnitName'];
            $item_unit_name_plural = $row['UnitNamePlural'];
            $item_alias = $row['Alias'];
            $item_currentFlavor = $row['CurrentFlavor'];
            $item_expiration_date= $row['ExpirationDate'];
            $item_tag= $row['Tag'];
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
            "<input type='hidden' id='Item_" . $itemType . "_Price_$item_id' value='" . getPriceDisplayWithDecimals( $item_price ) . "'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_DiscountPrice_$item_id' value='" . getPriceDisplayWithDecimals( $item_discount_price ) . "'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_ImageURL_$item_id' value='$item_imageURL'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_ThumbURL_$item_id' value='$item_thumbURL'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_UnitName_$item_id' value='$item_unit_name'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_UnitNamePlural_$item_id' value='$item_unit_name_plural'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_Tag_$item_id' value='$item_tag'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_Alias_$item_id' value='$item_alias'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_CurrentFlavor_$item_id' value='$item_currentFlavor'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_ExpirationDate_$item_id' value='$item_expiration_date'/>" .
            "<input type='hidden' id='Item_" . $itemType . "_Retired_$item_id' value='$item_retired'/>";
        }

        $isNoItems = $item_options == "";
        $disabledNoItems = "";

        if( $isNoItems ) {
            $item_options = "<option>No Items Available</option>";
            $item_options_no_discontinued = "<option>No Items Available</option>";
            $disabledNoItems = "disabled";

            echo "<script>$(\"#Edit_Item_" . $itemType . "_Submit\").hide();</script>";
        }

        $edit_dropdown = "<select $disabledNoItems id='Edit" . $itemType . "Dropdown' name='Edit" . $itemType . "Dropdown' style='font-size:1em;'>$item_options</select>";
            
        $restock_dropdown = "<select $disabledNoItems id='RestockDropdown' name='RestockDropdown'>$item_options_no_discontinued</select>";


        buildAddItemModal( $itemType, $redirectURL );
        buildEditItemModal( $itemType, $edit_dropdown, $item_info, $redirectURL );
        buildRestockItemModal( $itemType, $restock_dropdown, $redirectURL );
        buildInventoryModal( $db, $itemType, $hideForms, $redirectURL );
        buildRefillModal( $db, $itemType, $hideForms, $redirectURL );

        if( IsAdminLoggedIn() ) {
            buildDefectiveModal( $itemType, $item_options, $hideForms );
        }
    }

    function buildRestockItemModal( $itemType, $restock_dropdown, $redirectURL ) {
        $itemTypeID = strtolower( $itemType );

        echo "<div id='restock_item_" . $itemTypeID . "_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar $itemTypeID'>";
        echo "Restock $itemType";
        echo "<span id='restock_item_" . $itemTypeID . "_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='restock_item_" . $itemTypeID . "_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        echo "<ul>";

        echo "<li>";
        echo "<label for='ItemNameDropdown'>$itemType</label>";
        echo $restock_dropdown;
        echo "</li>";

        echo "<li>";
        echo "<label for='NumberOfCans'>Number Of Units</label>";
        echo "<input type='tel' autocomplete='off' name='NumberOfCans'/>";
        echo "<span>Total number of items being restocked.</span>";
        echo "</li>";

        echo "<li>";
        echo "<label for='Cost'>Cost for Pack</label>";
        echo "<input type='tel' autocomplete='off' name='Cost'/>";
        echo "<span>Price of all items as a whole.</span>";
        echo "</li>";

        echo "<li>";
        echo "<label for='Multiplier'>Multiplier</label>";
        echo "<input type='tel' autocomplete='off' name='Multiplier'/>";
        echo "<span>Multiplies the price and the number of items.<br>For when you buy multiple packs of something for same price.</span>";
        echo "</li>";

        echo "<li>";
        echo "<label for='ExpDate'>Expiration Date</label>";
        echo "<input type='text' autocomplete='off' name='ExpDate'/>";
        echo "<span>The expiration date of the items.<br>Eventually will be used to determine when shelf items are going stale.</span>";
        echo "</li>";

        echo "<li class='buttons $itemTypeID'>";
        echo "<input style='padding:10px;' type='submit' name='Restock_Item_" . $itemType .  "_Submit' value='Restock $itemType'/>";
        echo "</li>";

        echo "<input type='hidden' name='ItemType' value='" . $itemType . "'/>";
        echo "<input type='hidden' name='Restock' value='Restock'/>";
        echo "<input type='hidden' name='redirectURL' value='$redirectURL'/>";

        echo "</ul>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }
    function buildEditItemModal( $itemType, $edit_dropdown, $item_info, $redirectURL ) {
        $itemTypeID = strtolower( $itemType );

        $editNameID = "EditItemName" . $itemType;
        $editPriceID = "EditPrice" . $itemType;
        $editDiscountPriceID = "EditDiscountPrice" . $itemType;
        $editImageURLID = "EditImageURL" . $itemType;
        $editThumbURLID = "EditThumbURL" . $itemType;
        $editUnitNameID = "EditUnitName" . $itemType;
        $editUnitNamePluralID = "EditUnitNamePlural" . $itemType;
        $editTag = "EditTag" . $itemType;
        $editAliasID = "EditAlias" . $itemType;
        $editCurrentFlavorID = "EditCurrentFlavor" . $itemType;
        $editExpirationDateID = "EditExpirationDate" . $itemType;
        $editActiveID = "EditStatusActive" . $itemType;
        $editDiscontinuedID = "EditStatusDiscontinued" . $itemType;
        $editStatusID = "EditStatus" . $itemType;

        $tagDropdown = "<select id='$editTag' name='$editTag' class='text ui-widget-content ui-corner-all'>" .
            "<option value=''>None</option>" .
            "<option value='New'>New</option>" .
            "<option value='LimitedTimeOnly'>Limited Time Only</option>" .
            "<option value='Clearance'>Clearance</option>" .
            "<option value='Seasonal'>Seasonal Item</option>" .
            "</select>";

        echo "<div id='edit_item_" . $itemTypeID . "_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar $itemTypeID'>";
        echo "Edit $itemType";
        echo "<span id='edit_item_" . $itemTypeID . "_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='edit_item_" . $itemTypeID . "_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        echo "<ul>";

        echo "<table>";
        echo "<tr>";

        echo "<td>";
        echo "<li>";
        echo "<label for='ItemNameDropdown'>" . $itemType . "</label>";
        echo $edit_dropdown;
        echo "</li>";
        echo "</td>";

        echo "</tr>";

        echo "<tr>";

        echo "<td>";
        echo "<li>";
        echo "<label for='ItemName'>Name</label>";
        echo "<input type='text' autocorrect='off' autocapitalize='off' maxlength='30' id='$editNameID' name='$editNameID'>";
        echo "<span>Name of the product</span>";
        echo "</li>";
        echo "</td>";

        echo "<td>";
        echo "<li>";
        echo "<label for='Alias'>Alias</label>";
        echo "<input type='text' id='$editAliasID' name='$editAliasID'>";
        echo "<span>Comma separated list of names that will also show in search.</span>";
        echo "</li>";
        echo "</td>";

        echo "</tr>";

        echo "<tr>";

        echo "<td>";
        echo "<li>";
        echo "<label for='CurrentPrice'>Price</label>";
        echo "<input type='tel' id='$editPriceID' name='$editPriceID'>";
        echo "<span>The full price if bought with money in the mugs</span>";
        echo "</li>";
        echo "</td>";

        echo "<td>";
        echo "<li>";
        echo "<label for='CurrentPrice'>Discount Price</label>";
        echo "<input type='tel' id='$editDiscountPriceID' name='$editDiscountPriceID'>";
        echo "<span>The discount price is bought through the site</span>";
        echo "</li>";
        echo "</td>";

        echo "</tr>";

        echo "<tr>";

        echo "<td>";
        echo "<li>";
        echo "<label for='UnitName'>Unit Name</label>";
        echo "<input type='text' id='$editUnitNameID' name='$editUnitNameID'>";
        echo "<span>The unit used for the quantity display.</span>";
        echo "</li>";
        echo "</td>";

        echo "<td>";
        echo "<li>";
        echo "<label for='UnitNamePlural'>Unit Name (plural)</label>";
        echo "<input type='text' id='$editUnitNamePluralID' name='$editUnitNamePluralID'>";
        echo "<span>Because the english language sucks and plural isn't predictable.</span>";
        echo "</li>";
        echo "</td>";

        echo "</tr>";

        echo "<tr>";

        echo "<td>";
        echo "<li>";
        echo "<label for='CurrentFlavor'>Current Flavor</label>";
        echo "<input type='text' id='$editCurrentFlavorID' name='$editCurrentFlavorID'>";
        echo "<span>The current flavor of the product.<br>So you don't need to create many items for the same item.</span>";
        echo "</li>";
        echo "</td>";

        echo "<td>";
        echo "<li>";
        echo "<label for='ExpirationDate'>Expiration Date</label>";
        echo "<input type='text' id='$editExpirationDateID' name='$editExpirationDateID'>";
        echo "<span>The date the item expires. (WIP)</span>";
        echo "</li>";
        echo "</td>";

        echo "</tr>";

        echo "<tr>";

        echo "<td>";
        echo "<li>";
        echo "<label for='ImageURL'>Image URL</label>";
        echo "<input name='uploadedImage' type='file' />";
        echo "<div style='color:#55a4ff; padding: 5px 0px; font-size: 0.9em;' id='$editImageURLID'></div>";
        echo "<span>The image used in the card.</span>";
        echo "</li>";
        echo "</td>";

        echo "<td>";
        echo "<li>";
        echo "<label for='ThumbURL'>Thumb URL</label>";
        echo "<input name='uploadedThumb' type='file' />";
        echo "<div style='color:#55a4ff; padding: 5px 0px; font-size: 0.9em;' id='$editThumbURLID'></div>";
        echo "<span>The thumbnail image used in the shelf.</span>";
        echo "</li>";
        echo "</td>";

        echo "</tr>";

        echo "<tr>";

        echo "<td colspan='1'>";
        echo "<li>";
        echo "<label style='display:inline-block' for='ThumbURL'>Status</label>";
        echo "<div class='radio_status'>";
        echo "<div style='display: inline-block;'>";
        echo "<input class='radio' type='radio' id='$editActiveID' name='$editStatusID' value='active' checked />";
        echo "<label for='$editActiveID'>Active</label>";
        echo "<input style='margin-left: 10px;' class='radio' type='radio' id='$editDiscontinuedID' name='$editStatusID' value='discontinued' />";
        echo "<label for='$editDiscontinuedID'>Discontinued</label>";
        echo "</div>";
        echo "</div>";
        echo "<span>Mark this discontinued when you won't be selling an item anymore.<br>A warning will appear in the card.<br>Once sold out the item won't show anywhere in the site anymore.</span>";

        echo "</li>";
        echo "</td>";

        echo "<td>";
        echo "<li>";
        echo "<label for='Tag'>Tag</label>";
        echo  $tagDropdown;
        echo "<span>The tag displayed in the top left of the card.</span>";
        echo "</li>";
        echo "</td>";

        echo "</tr>";

        echo "</table>";

        echo "<li class='buttons $itemTypeID'>";
        echo "<input style='padding:10px;' type='submit' name='Edit_Item_" . $itemType .  "_Submit' value='Edit $itemType'/>";
        echo "</li>";

        echo "</ul>";

        echo $item_info;
        echo "<input type='hidden' name='ItemType' value='$itemType'/>";
        echo "<input type='hidden' name='EditItem' value='EditItem'/>";
        echo "<input type='hidden' name='redirectURL' value='$redirectURL'/>";

        echo "</form>";
        echo "</div>";
        echo "</div>";
    }

    function buildAddItemModal( $itemType, $redirectURL ) {
        $itemTypeID = strtolower( $itemType );

        echo "<div id='add_item_" . $itemTypeID . "_modal' class='neptuneModal'>";
        echo "<div class='neptuneModalContent'>";


        echo "<div class='neptuneTitleBar $itemTypeID'>";
        echo "Add $itemType";
        echo "<span id='add_item_" . $itemTypeID . "_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='add_item_" . $itemTypeID . "_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        echo "<ul>";

        echo "<li>";
        echo "<label for='ItemName'>Name</label>";
        echo "<input type='text' autocomplete='off' name='ItemName' maxlength='40'/>";
        echo "<span>Name of the product</span>";
        echo "</li>";

        echo "<li>";
        echo "<label for='CurrentPrice'>Price</label>";
        echo "<input type='tel' autocomplete='off' name='CurrentPrice'/>";
        echo "<span>The full price if bought with money in the mugs</span>";
        echo "</li>";

        echo "<li>";
        echo "<label for='CurrentPrice'>Discount Price</label>";
        echo "<input type='tel' autocomplete='off' name='CurrentDiscountPrice'/>";
        echo "<span>The discount price is bought through the site</span>";
        echo "</li>";

        echo "<input type='hidden' name='ItemType' value='$itemType'/>";
        echo "<input type='hidden' name='AddItem' value='AddItem'/>";
        echo "<input type='hidden' name='redirectURL' value='$redirectURL'/>";

        echo "<li class='buttons $itemTypeID'>";
        echo "<input style='padding:10px;' type='submit' name='Add_Item_" . $itemType .  "_Submit' value='Add $itemType'/>";
        echo "</li>";

        echo "</ul>";
        echo "</form>";

        echo "</div>";
        echo "</div>";
    }

    function buildDefectiveModal( $itemType, $item_options_no_discontinued, $hideForms ) {
        $itemTypeID = strtolower( $itemType );

        $defective_dropdown = "<select id='DefectiveDropdown' name='DefectiveDropdown'>$item_options_no_discontinued</select>";


        echo "<div id='defective_item_" . $itemTypeID . "_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar $itemTypeID'>";
        echo "Defect $itemType";
        echo "<span id='defective_item_" . $itemTypeID . "_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='defective_item_" . $itemTypeID . "_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";

        echo "<ul>";

        echo "<li>";
        echo "<label for='ItemNameDropdown'>$itemType</label>";
        echo $defective_dropdown;
        echo "</li>";

        echo "<li>";
        echo "<label for='NumberOfUnits'>Number of Units</label>";
        echo "<input type='tel' name='NumberOfUnits' class='text ui-widget-content ui-corner-all'/>";
        echo "</li>";

        echo "<input type='hidden' name='ItemType' value='" . $itemType . "'/>";
        echo "<input type='hidden' name='Defective' value='Defective'/>";
        echo "<input type='hidden' name='redirectURL' value='" . ADMIN_DEFECTIVES_LINK . "'/>";

        echo "<li class='buttons $itemTypeID'>";
        echo "<input style='padding:10px;' type='submit' name='Defective_Item_" . $itemType . "_Submit' value='Defect $itemType'/>";
        echo "</li>";

        echo "</form>";
        echo "</div>";
        echo "</div>";
    }
    /**
     * @param $db SQLite3
     * @param $itemType
     * @param $hideForms
     */
    function buildRefillModal( $db, $itemType, $hideForms, $redirectURL ) {
        $itemTypeID = strtolower( $itemType );

        $prefix = "refill";
        $label = "Refill";

        echo "<div id='$prefix" . "_item_" . $itemTypeID . "_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar $itemTypeID'>";
        echo "Refill $itemType";
        echo "<span id='refill_item_" . $itemTypeID . "_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='refill_item_" . $itemTypeID . "_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";

        echo "<table style='border-spacing: 10px;'>";
        echo "<tr>";
        echo "<th class='admin_header_column'>" . $itemType . "</th>";
        echo "<th class='admin_header_column'>Add to Shelf</th>";
        echo "<th class='admin_header_column'>&nbsp;</th>";
        echo "<th class='admin_header_column'>Shelf Quantity</th>";
        echo "<th class='admin_header_column'>Backstock Quantity</th></tr>";

        $andVendorIDClause = "";

        if( IsVendor() ) {
            $andVendorIDClause = " AND VendorID = " .  $_SESSION['UserID'];
        }

        $statement = $db->prepare("SELECT Name," . getQuantityQuery() . ",ID FROM Item i " .
            "WHERE Hidden != 1 AND Type = :itemType AND TotalAmount > 0 $andVendorIDClause " .
            "ORDER BY ShelfAmount ASC, Name asc, Retired");
        $statement->bindValue( ":itemType", $itemType );
        $results = $statement->execute();

        $tabIndex = 1;
        while ($row = $results->fetchArray()) {
            $item_name = $row['Name'];
            $backstockquantity = $row['BackstockAmount'];
            $shelfquantity = $row['ShelfAmount'];
            $item_id = $row['ID'];
            echo "<tr>";
            echo "<input type='hidden' id='item_$item_id' name='RefillItemID[]' value='$item_id'/>";
            echo "<td class='neptuneRowLabel'>$item_name</td>";
            echo "<td class='neptuneRowInput'><input autocomplete='off' type='tel' onClick='this.select();' tabindex=$tabIndex id='RefillAddToShelf_$item_id' value='0' name='RefillAddToShelf[]'/></td>";
            echo "<td style='width: 20px;'>&nbsp;</td>";
            echo "<td><input type='tel' readonly onClick='this.select();' tabindex=0 id='RefillShelfQuantity_$item_id' value='$shelfquantity' name='RefillShelfQuantity[]' class='fakeInput text ui-corner-all'/></td>";
            echo "<td><input type='tel' readonly tabindex=0 id='RefillBackstockQuantity_$item_id' value='$backstockquantity' name='RefillBackstockQuantity[]' class='fakeInput text ui-corner-all'/></td>";
            echo "</tr>";

            $tabIndex++;

            // On change, update the backstock quantity if you are increasing the shelf quantity
            echo "<script type='text/javascript'>";
            echo "$( document ).ready( function() {";

            echo "$('#RefillAddToShelf_$item_id').change(function () {";
                echo "var incrementAmount = parseInt($('#RefillAddToShelf_$item_id').val());";
                echo "console.log('New Increment: [' + incrementAmount + ']');";
                echo "if(incrementAmount > 0) {";

                    echo "var backStockQuantity = parseInt($('#RefillBackstockQuantity_$item_id').val());";
                    echo "var shelfQuantity = parseInt($('#RefillShelfQuantity_$item_id').val());";
                    echo "var newBackstockQuantity = backStockQuantity - incrementAmount;";
                    echo "var newShelfQuantity = shelfQuantity + incrementAmount;";

                    echo "if(newBackstockQuantity >= 0) {";
                        echo "$('#RefillBackstockQuantity_$item_id').val(newBackstockQuantity);";
                        echo "$('#RefillShelfQuantity_$item_id').val(newShelfQuantity);";

                        echo "if(newBackstockQuantity == 0) {";
                            echo "$('#RefillBackstockQuantity_$item_id').css('background-color', '#3d3d3d');";
                            echo "$('#RefillBackstockQuantity_$item_id').css('color', '#ffffff');";
                            echo "$('#RefillBackstockQuantity_$item_id').css('border', '#9a2929 solid 2px');";
                        echo "} else {";
                            echo "$('#RefillBackstockQuantity_$item_id').css('background-color', '#ff9b9b');";
                        echo "}";

                        echo "$('#RefillShelfQuantity_$item_id').css('background-color', '#a6ff9b');";

                        // Dont wan't to think changing from 6 to 7 will increase by 1, it will increase 7 again
                        echo "$('#RefillAddToShelf_$item_id').attr('placeholder', '+' + incrementAmount + ' units');";
//                        echo "$('#RefillAddToShelf_$item_id').val('');";
                    echo "} else {";
                        echo "alert('There is not enough backstock available for [' + incrementAmount + '] units.');";
                    echo "}";
               echo "} else {";
                    echo "alert('Cannot have a negative refill.');";
                echo "}";
            echo "});"; // On Change
            echo "});"; // Document Ready
            echo "</script>";
        }
        echo "</table>";

        echo "<div class='neptuneRow'>";
        echo "<input style='display:inline-block;' type='checkbox' id='SendToSlack' name='SendToSlack'/> Notify #random Channel";
        echo "</div>";

        echo "<ul>";
        echo "<li class='buttons $itemTypeID'>";
        echo "<input style='padding:10px;' type='submit' name='Refill_Item_" . $itemType .  "_Submit' value='Refill $itemType'/>";
        echo "</li>";
        echo "</ul>";

        echo "<input type='hidden' name='Refiller' value='" . $_SESSION['FirstName'] ."'/>";
        echo "<input type='hidden' name='$label' value='Refill'/>";
        echo "<input type='hidden' name='ItemType' value='$itemType'/>";
        echo "<input type='hidden' name='redirectURL' value='$redirectURL'/>";

        echo "</form>";
        echo "</div>";
        echo "</div>";
    }

    /**
     * @param $db SQLite3
     * @param $itemType
     * @param $hideForms
     */
    function buildInventoryModal( $db, $itemType, $hideForms, $redirectURL ) {
        $itemTypeID = strtolower( $itemType );

        $prefix = "inventory";
        $label = "Inventory";

        echo "<div id='$prefix" . "_item_" . $itemTypeID . "_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar $itemTypeID'>";
        echo "Inventory $itemType";
        echo "<span id='inventory_item_" . $itemTypeID . "_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='" . $prefix . "_" . $itemTypeID . "_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";

        echo "<table style='border-spacing: 10px;'>";
        echo "<tr>";
        echo "<th class='admin_header_column'>" . $itemType . "</th>";
        echo "<th class='admin_header_column'>Shelf Quantity</th>";
        echo "<th class='admin_header_column'>Backstock Quantity</th></tr>";

        $andVendorIDClause = "";

        if( IsVendor() ) {
            $andVendorIDClause = " AND VendorID = " .  $_SESSION['UserID'];
        }

        $statement = $db->prepare("SELECT Name," . getQuantityQuery() . ",ID FROM Item i " .
            "WHERE Hidden != 1 AND Type = :itemType AND TotalAmount > 0 $andVendorIDClause " .
            "ORDER BY Name asc, Retired");
        $statement->bindValue( ":itemType", $itemType );
        $results = $statement->execute();

        $tabIndex = 1;
        while ($row = $results->fetchArray()) {
            $item_name = $row['Name'];
            $backstockquantity = $row['BackstockAmount'];
            $shelfquantity = $row['ShelfAmount'];
            $item_id = $row['ID'];
            echo "<tr>";
            echo "<input type='hidden' id='item_$item_id' name='ItemID[]' value='$item_id'/>";
            echo "<input type='hidden' id='ShelfQuantityOriginal_$item_id' value='$shelfquantity'/>";
            echo "<td class='neptuneRowLabel'>$item_name</td>";
            echo "<td class='neptuneRowInput'><input autocomplete='off' type='tel' onClick='this.select();' tabindex=0 id='ShelfQuantity_$item_id' value='$shelfquantity' name='ShelfQuantity[]' class='text ui-corner-all'/>";
            echo "<td><input type='tel' disabled tabindex=0 id='BackstockQuantity_$item_id' value='$backstockquantity' name='BackstockQuantity[]' class='fakeInput text ui-corner-all'/></td>";
            echo "</tr>";

            $tabIndex++;

            // On change, update the backstock quantity if you are increasing the shelf quantity
            echo "<script type='text/javascript'>";
            echo "$( document ).ready( function() {";

            echo "$('#ShelfQuantity_$item_id').change(function () {";
                echo "var originalAmount = parseInt($('#ShelfQuantityOriginal_$item_id').val());";
                echo "var newAmount = parseInt($('#ShelfQuantity_$item_id').val());";

                echo "if( newAmount < originalAmount ) {";
                    echo "$('#ShelfQuantity_$item_id').css('background-color', '#ff9b9b');";
                echo "} else {";
                    echo "alert('You cannot increase shelf amount. Use Refill to do that.');";
                    echo "$('#ShelfQuantity_$item_id').val(originalAmount);";
                echo "}";
            echo "});"; // On Change
            echo "});"; // Document Ready
            echo "</script>";
        }
        echo "</table>";

        if( !IsVendor() ) {
            echo "<li>";
            echo "<label for='AuditAmount'>Audit Amount</label>";
            echo "<input type='tel' autocomplete='off' name='AuditAmount'/>";
            echo "<span>The amount of money in the mug.</span>";
            echo "</li>";
        }

        echo "<input type='hidden' name='$label' value='$label'/>";
        echo "<input type='hidden' name='ItemType' value='$itemType'/>";
        echo "<input type='hidden' name='redirectURL' value='$redirectURL'/>";

        echo "<ul>";
        echo "<li class='buttons $itemTypeID'>";
        echo "<input style='padding:10px;' type='submit' name='Inventory_Item_" . $itemType .  "_Submit' value='Inventory $itemType'/>";
        echo "</li>";
        echo "</ul>";

        echo "</form>";
        echo "</div>";
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

    $('#EditExpirationDateSnack').datepicker();

    setItemInfo('Soda');
    setItemInfo('Snack');
    setUserInfo();
    updateStoreOptions();
});

function setItemInfo( type ) {
    var itemID = parseInt($('#Edit' + type + 'Dropdown').val());
    var itemName = $('#Item_' + type + '_Name_' + itemID).val();
    var itemPrice = $('#Item_' + type + '_Price_' + itemID).val();
    var itemDiscountPrice = $('#Item_' + type + '_DiscountPrice_' + itemID).val();
    var itemImageURL = $('#Item_' + type + '_ImageURL_' + itemID).val();
    var itemUnitName = $('#Item_' + type + '_UnitName_' + itemID).val();
    var itemUnitNamePlural = $('#Item_' + type + '_UnitNamePlural_' + itemID).val();
    var itemTag = $('#Item_' + type + '_Tag_' + itemID).val();
    var itemAlias = $('#Item_' + type + '_Alias_' + itemID).val();
    var itemCurrentFlavor = $('#Item_' + type + '_CurrentFlavor_' + itemID).val();
    var itemExpirationDate = $('#Item_' + type + '_ExpirationDate_' + itemID).val();
    var itemThumbURL = $('#Item_' + type + '_ThumbURL_' + itemID).val();
    var itemRetired = $('#Item_' + type + '_Retired_' + itemID).val();
    console.log("Item ID: " +  itemID + " " + itemName + " " + itemPrice+ " " + itemRetired);
    
    $("#EditItemName" + type).val(itemName);
    $("#EditPrice" + type).val(itemPrice);
    $("#EditDiscountPrice" + type).val(itemDiscountPrice);
    $("#EditImageURL" + type).html("(" + itemImageURL + ")");
    $("#EditThumbURL" + type).html("(" + itemThumbURL + ")");
    $("#EditUnitName" + type).val(itemUnitName);
    $("#EditUnitNamePlural" + type).val(itemUnitNamePlural);
    $("#EditTag" + type).val(itemTag);
    $("#EditAlias" + type).val(itemAlias);
    $("#EditCurrentFlavor" + type).val(itemCurrentFlavor);
    $("#EditExpirationDate" + type).val(itemExpirationDate);

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
    var isVendor = $('#User_IsVendor_' + userID).val();

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

    if( isVendor == 0 ) {
        $("#IsVendor").prop("checked", false);
    } else {
        $("#IsVendor").prop("checked", true);
    }
}

$('#TotalAmount').change(function() {
    var sodaAmount = 0.0;
    var snackAmount = 0.0;

    var amount = $('#TotalAmount').val();
    var sodaBalance =  parseFloat( $('#SodaUnpaid').val() );
    var snackBalance =  parseFloat( $('#SnackUnpaid').val() );
    var totalBalance = (sodaBalance + snackBalance );
    if( amount  > totalBalance ) {
        sodaAmount = parseFloat(sodaBalance);
        snackAmount = parseFloat(snackBalance);
        $('#TotalAmount').val( totalBalance );
    } else if( amount > sodaBalance ) {
        // Zero out the balance, take remainder from Snack
        sodaAmount = parseFloat(sodaBalance);
        snackAmount = parseFloat(amount - sodaAmount);
    } else {
        // Take it all from soda, leave snack unchanged
        sodaAmount = parseFloat(amount);
        snackAmount = parseFloat(0.0);
    }

    console.log("Calc Payments: Sod [" + sodaAmount + "] Sna [" + snackAmount + "]" );

    sodaAmount = sodaAmount.toFixed( 2 );
    snackAmount = snackAmount.toFixed( 2 );

    $('#SodaAmount').val( sodaAmount );
    $('#SnackAmount').val( snackAmount );
} );

$('#StoreDropdown').change(function() {
    updateStoreOptions();
} );

function updateStoreOptions() {
    var store = $('#StoreDropdown').val();

    if( store == "BestProfits" ) {
        $(".PriceSection").hide();
    } else {
        $(".PriceSection").show();
    }
    console.log("Store [" + store + "]" );
}

</script>