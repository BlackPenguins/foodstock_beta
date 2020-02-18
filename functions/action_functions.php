<?php
include(AUDIT_OBJ);

/**
 * @param $db SQLite3
 * @param $name
 * @param $price
 * @param $itemType
 * @return string
 */
function addItem( $db, $name, $price, $discountPrice, $itemType ) {
    $name = trim($name);
    $date = date('Y-m-d H:i:s');
    error_log( "Price[$price] Discount[$discountPrice]" );
    $price = convertDecimalToWholeCents( trim( $price ) );
    $discountPrice = convertDecimalToWholeCents( trim( $discountPrice ) );
    $itemType = trim( $itemType );

    $itemCountStatement = $db->prepare("SELECT COUNT(*) as Count FROM Item WHERE Name = :name AND Type = :itemType");
    $itemCountStatement->bindValue( ":name", $name );
    $itemCountStatement->bindValue( ":itemType", $itemType );
    $itemCountResults = $itemCountStatement->execute();

    $itemCountRow = $itemCountResults->fetchArray();
    $numOfExistingItems = $itemCountRow['Count'];

    if( $numOfExistingItems > 0 ) {
        return "Item \"$name\" already exists.";
    } else {
        $statement = $db->prepare( "INSERT INTO Item (Name, Date, ChartColor, TotalCans, Price, DiscountPrice, TotalIncome, TotalExpenses, Type, RefillTrigger, RestockTrigger, IsBought, VendorID) VALUES " .
            "( :name, :date, :chartColor, 0, :price, :discountPrice, 0.00, 0.00, :itemType, 0, 0, 0, :vendorID)" );

        $statement->bindValue( ":name", $name );
        $statement->bindValue( ":date", $date );
        $statement->bindValue( ":chartColor", "ABCDEF" );
        $statement->bindValue( ":price", $price );
        $statement->bindValue( ":discountPrice", $discountPrice );
        $statement->bindValue( ":itemType", $itemType );

        if( IsVendor() ) {
            $statement->bindValue(":vendorID", $_SESSION['UserID'] );
        } else {
            $statement->bindValue(":vendorID", 0 );
        }

        $statement->execute();

        sendSlackMessageToMatt("*Item Name:* $name\n*Price:* $price\n*Discount Price:* $discountPrice\n*Type:* $itemType", ":heavy_plus_sign:", "NEW ITEM", "#b7ab1a");

        return "Item \"$name\" added successfully.";
    }
}

/**
 * @param $db SQLite3
 * @param $itemType
 * @param $itemID
 * @param $name
 * @param $price
 * @param $discountPrice
 */
function editItem( $db, $itemID, $name, $price, $discountPrice, $unitName, $unitNamePlural, $alias, $currentFlavor, $status, $expirationDate ) {

    $retired = $status == "active" ? 0 : 1;

    $updateImageURL = "";
    $updateThumbURL = "";
    $targetImageFileName = "";
    $targetThumbFileName = "";

    if ( is_uploaded_file($_FILES['uploadedImage']['tmp_name'] ) ) {
        log_debug( "FOUND TMP: [" .$_FILES['uploadedImage']['tmp_name'] . "]" );
        log_debug( "FOUND NAME: [" .$_FILES['uploadedImage']['name'] . "]" );
        $targetImageFileName = basename( $_FILES['uploadedImage']['name'] );
        log_debug( "FOUND TARGET: [" .$targetImageFileName . "]" );
        $target = IMAGES_NORMAL_PATH . $targetImageFileName;
        log_debug( "FOUND TARGET PATH: [" .$target . "]" );

        if( !move_uploaded_file( $_FILES['uploadedImage']['tmp_name'], $target ) ) {
            error_log(" THERE WAS AN ERROR UPLOADING THIS IMAGE: " . $_FILES['uploadedImage']['tmp_name'] );
        } else {
            $updateImageURL = ", ImageURL = :targetImageFileName";
            log_debug( "FOUND UPDATE: [" .$updateImageURL . "]" );
        }
    }

    if ( is_uploaded_file($_FILES['uploadedThumb']['tmp_name'] ) ) {
        $targetThumbFileName = basename( $_FILES['uploadedThumb']['name'] );
        $target = IMAGES_THUMBNAILS_PATH . $targetThumbFileName;
        if( !move_uploaded_file( $_FILES['uploadedThumb']['tmp_name'], $target ) ) {
            error_log(" THERE WAS AN ERROR UPLOADING THIS THUMBNAIL: " . $_FILES['uploadedThumb']['tmp_name'] );
        } else {
            $updateThumbURL = ", ThumbURL = :targetThumbFileName";
        }
    }

    $quantityStatement = $db->prepare( "SELECT Price, DiscountPrice FROM Item WHERE ID = :itemID" );
    $quantityStatement->bindValue( ":itemID", $itemID );
    $quantityResults = $quantityStatement->execute();

    $quantityRow = $quantityResults->fetchArray();
    $beforePrice = $quantityRow['Price'];
    $beforeDiscountPrice = $quantityRow['DiscountPrice'];

    if( $beforePrice != $price || $beforeDiscountPrice != $discountPrice ) {
        log_debug( "Price was changed. Original Price [$beforePrice] New Price [$price] Original Discount [$beforeDiscountPrice] New Discount [$discountPrice]" );
        $newItemDetailsIDs = array();

        $itemsInStockStatement = $db->prepare( "SELECT StockID, ItemDetailsID FROM Items_In_Stock WHERE ItemID = :itemID" );
        $itemsInStockStatement->bindValue( ":itemID", $itemID );
        $itemsInStockResults = $itemsInStockStatement->execute();

        log_debug("Checking every item in stock with item id [$itemID]" );
        while ($row = $itemsInStockResults->fetchArray()) {
            $originalItemDetailsID = $row['ItemDetailsID'];
            $stockID = $row['StockID'];

            log_debug("--- ItemDetailsID [$originalItemDetailsID], Stock ID [$stockID]" );

            if( array_key_exists( $originalItemDetailsID, $newItemDetailsIDs ) ) {
                $newItemDetailsID = $newItemDetailsIDs[$originalItemDetailsID];
                log_debug("--- Mapping already exists. New ItemDetailsID = $newItemDetailsID" );
            } else {
                $originalItemDetailsStatement = $db->prepare( "SELECT ItemID, RetailPrice, ExpDate FROM Item_Details WHERE ItemDetailsID= :itemDetailsID" );
                $originalItemDetailsStatement->bindValue( ":itemDetailsID", $originalItemDetailsID );
                $originalItemDetailsResults = $originalItemDetailsStatement->execute();

                $originalItemDetailsRow = $originalItemDetailsResults->fetchArray();
                $originalRetailPrice = $originalItemDetailsRow['RetailPrice'];
                $originalItemID = $originalItemDetailsRow['ItemID'];
                $originalExpDate = $originalItemDetailsRow['ExpDate'];

                log_debug("--- Creating new Item Details ID ItemID [$originalItemID] Price [$price] Discount [$discountPrice] Retail [$originalRetailPrice] ExpDate [$originalExpDate]" );

                $stmt=$db->prepare("INSERT INTO Item_Details (ItemID, Price, DiscountPrice, RetailPrice, ExpDate) VALUES " .
                    "(:itemID, :price, :discountPrice, :retailPrice, :expDate)");
                $stmt->bindValue(':itemID', $originalItemID );
                $stmt->bindValue(':price', $price );
                $stmt->bindValue(':discountPrice', $discountPrice );
                $stmt->bindValue(':retailPrice', $originalRetailPrice );
                $stmt->bindValue(':expDate', $originalExpDate );
                $stmt->execute();
                $newItemDetailsID = $db->lastInsertRowID();
                $newItemDetailsIDs[$originalItemDetailsID] = $newItemDetailsID;

                log_debug("--- Caching new Item Details ID [$newItemDetailsID]" );
            }

            $updateStockStatement = $db->prepare( "UPDATE Items_in_Stock SET ItemDetailsID=:itemDetailsID WHERE StockID = :stockID" );
            $updateStockStatement->bindValue( ":itemDetailsID", $newItemDetailsID, SQLITE3_TEXT );
            $updateStockStatement->bindValue( ":stockID", $stockID, SQLITE3_TEXT );
            $updateStockStatement->execute();

            log_debug("--- Updating Stock with new Item Details ID for Stock [$stockID]" );
        }

    }
    // TODO MTM: Remove Exp Date from Item, we have it in Restock
    $statement = $db->prepare( "UPDATE Item SET Name=:name, ChartColor=:chartColor, Price = :price, DiscountPrice = :discountPrice, Retired = :retired $updateImageURL $updateThumbURL, " .
        "UnitName = :unitName, UnitNamePlural = :unitNamePlural, Alias = :alias, CurrentFlavor = :currentFlavor, ExpirationDate = :expirationDate where ID = :itemID" );

    $statement->bindValue( ":name", $name );
    $statement->bindValue( ":chartColor", "ABCDEF" );
    $statement->bindValue( ":price", $price );
    $statement->bindValue( ":discountPrice", $discountPrice );
    $statement->bindValue( ":retired", $retired );
    $statement->bindValue( ":unitName", $unitName );
    $statement->bindValue( ":unitNamePlural", $unitNamePlural );
    $statement->bindValue( ":alias", $alias );
    $statement->bindValue( ":currentFlavor", $currentFlavor );
    $statement->bindValue( ":expirationDate", $expirationDate );
    $statement->bindValue( ":itemID", $itemID );
    $statement->bindValue( ":targetImageFileName", $targetImageFileName, SQLITE3_TEXT );
    $statement->bindValue( ":targetThumbFileName", $targetThumbFileName, SQLITE3_TEXT );
    $statement->execute();

    sendSlackMessageToMatt("*Item Name:* $name\n*Price:* $price\n*Discount Price:* $discountPrice\n*Flavor:* $currentFlavor\n*Alias:* $alias", ":pencil:", "EDIT ITEM", "#b7ab1a");

    return "Item \"$name\" edited successfully.";
}

/**
 * @param $db SQLite3
 * @param $userID
 * @param $slackID
 * @param $anonName
 * @param $inactive
 * @param $resetPassword
 * @param $isCoop
 * @return string
 */
function editUser( $db, $userID, $slackID, $anonName, $inactive, $resetPassword, $isCoop, $isVendor ) {
    $uniqueID = uniqid();
    $userMessage = "";

    $resetPasswordQuery = "";
    $randomPassword = "";

    if( $resetPassword == true ) {
        log_debug("Resetting Password");
        $randomPassword = sha1($uniqueID);
        $resetPasswordQuery = " Password=:randomPassword,";
        $userMessage .= "Password for user was reset to \"$uniqueID\". ";
    }

    $statement = $db->prepare( "UPDATE User SET SlackID=:slackID, AnonName=:anonName, $resetPasswordQuery Inactive = :inactive, IsCoop = :isCoop, IsVendor = :isVendor where UserID = :userID" );

    $statement->bindValue( ":slackID", $slackID );
    $statement->bindValue( ":anonName", strip_tags( $anonName ) );
    $statement->bindValue( ":inactive", $inactive );
    $statement->bindValue( ":isCoop", $isCoop );
    $statement->bindValue( ":isVendor", $isVendor );
    $statement->bindValue( ":userID", $userID );
    $statement->bindValue( ":randomPassword", $randomPassword );
    $statement->execute();

    return $userMessage . "User edited successfully.";
}

/**
 * @param $db SQLite3
 * @param $vendorID
 * @param $paymentMonth
 * @param $sodaAmount
 * @param $snackAmount
 * @param $note
 * @param $method
 */
function makeCommission( $db, $vendorID, $paymentMonth, $sodaAmount, $snackAmount, $note, $method, $sodaCommission, $snackCommission ) {
    log_payment( "Incoming paycheck for User [$vendorID] Month [$paymentMonth] Snack[$snackAmount] Soda[$sodaAmount]" );

    if( $vendorID > 0 ) {
        log_payment( "User paycheck found for [$vendorID]" );
        $statement = $db->prepare("SELECT SlackID, UserName, FirstName, LastName From User where UserID = :userID");
        $statement->bindValue( ":userID", $vendorID );
        $results = $statement->execute();

        $row = $results->fetchArray();
        $slackID = $row['SlackID'];
        $name = $row['FirstName'] . " " . $row['LastName'];
        $username = $row['UserName'];

        $amount = $sodaAmount + $snackAmount;

        $paymentMessage = "Your paycheck of *" . getPriceDisplayWithDollars( $amount ) ."* was received through $method for $paymentMonth.";

        sendSlackMessageToUser( $slackID,  $paymentMessage, ":heavy_dollar_sign:", "PAYCHECK RECEIVED", "#127b3c", $username, true );

        $date = date('Y-m-d H:i:s');

        // DEV: Changing this code? Change the payment code for Auditing as well (above)
        $db->exec("BEGIN;");

        $stmt=$db->prepare("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment, VendorID) VALUES ".
            "(:userID, :method, :sodaAmount, :date, :note, 'Soda', :paymentMonth, :vendorID)");
        $stmt->bindValue(':userID', $vendorID );
        $stmt->bindValue(':method', $method );
        $stmt->bindValue(':sodaAmount', $sodaAmount );
        $stmt->bindValue(':date', $date );
        $stmt->bindValue(':note', $note );
        $stmt->bindValue(':paymentMonth', $paymentMonth );
        $stmt->bindValue(':vendorID', $vendorID );
        $stmt->execute();

        $stmt=$db->prepare("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment, VendorID) VALUES " .
            "(:userID, :method, :snackAmount, :date, :note, 'Snack', :paymentMonth, :vendorID)");
        $stmt->bindValue(':userID', $vendorID );
        $stmt->bindValue(':method', $method );
        $stmt->bindValue(':snackAmount', $snackAmount );
        $stmt->bindValue(':date', $date );
        $stmt->bindValue(':note', $note );
        $stmt->bindValue(':paymentMonth', $paymentMonth );
        $stmt->bindValue(':vendorID', $vendorID );
        $stmt->execute();

        $stmt=$db->prepare("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment, VendorID) VALUES ".
            "(0, :method, :sodaAmount, :date, :note, 'Soda', :paymentMonth, :vendorID)");
        $stmt->bindValue(':method', $method );
        $stmt->bindValue(':sodaAmount', $sodaCommission );
        $stmt->bindValue(':date', $date );
        $stmt->bindValue(':note', "Commission from $vendorID" );
        $stmt->bindValue(':paymentMonth', $paymentMonth );
        $stmt->bindValue(':vendorID', $vendorID );
        $stmt->execute();

        $stmt=$db->prepare("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment, VendorID) VALUES " .
            "(0, :method, :snackAmount, :date, :note, 'Snack', :paymentMonth, :vendorID)");
        $stmt->bindValue(':method', $method );
        $stmt->bindValue(':snackAmount', $snackCommission );
        $stmt->bindValue(':date', $date );
        $stmt->bindValue(':note', "Commission from $vendorID" );
        $stmt->bindValue(':paymentMonth', $paymentMonth );
        $stmt->bindValue(':vendorID', $vendorID );
        $stmt->execute();

        $stmt=$db->prepare("UPDATE Information SET SitePayments = SitePayments + :sodaAmount, SiteProfit = SiteProfit + :sodaAmount where ItemType = 'Soda'");
        $stmt->bindValue(':sodaAmount', $sodaCommission );
        $stmt->execute();

        $stmt=$db->prepare("UPDATE Information SET SitePayments = SitePayments + :snackAmount, SiteProfit = SiteProfit + :snackAmount where ItemType = 'Snack'");
        $stmt->bindValue(':snackAmount', $snackCommission );
        $stmt->execute();

        $db->exec("COMMIT;");

        return "Paycheck added successfully.";
    }
}
/**
 * @param $db SQLite3
 * @param $userID
 * @param $paymentMonth
 * @param $sodaAmount
 * @param $snackAmount
 * @param $note
 * @param $method
 */
function makePayment( $db, $userID, $paymentMonth, $sodaAmount, $snackAmount, $note, $method ) {
    log_payment( "Incoming payment for User [$userID] Month [$paymentMonth] Snack[$snackAmount] Soda[$sodaAmount]" );

    if( $userID > 0 ) {
        log_payment( "User payment found for [$userID]" );
        $statement = $db->prepare("SELECT SodaBalance, SnackBalance, SlackID, UserName, FirstName, LastName From User where UserID = :userID");
        $statement->bindValue( ":userID", $userID );
        $results = $statement->execute();

        $row = $results->fetchArray();
        $sodaBalance = $row['SodaBalance'];
        $snackBalance = $row['SnackBalance'];
        $slackID = $row['SlackID'];
        $name = $row['FirstName'] . " " . $row['LastName'];
        $username = $row['UserName'];

        if( $sodaAmount > $sodaBalance ) {
            error_log( "Bad Soda balance. Amount: [" . $sodaAmount . "] Balance: [" . $sodaBalance . "]" );
            return "This payment [" . getPriceDisplayWithDollars( $sodaAmount ) . "] is larger than the user\"s Soda Balance of [" . getPriceDisplayWithDollars( $sodaBalance ) . "]. Payment denied!";
        }

        if( $snackAmount > $snackBalance) {
            error_log( "Bad Snack balance. Amount: [" . $snackAmount . "] Balance: [" . $snackBalance . "]" );
            return "This payment [" . getPriceDisplayWithDollars( $snackAmount ) . "] is larger than the user\"s Snack Balance of [" . getPriceDisplayWithDollars( $snackBalance ) . "]. Payment denied!";
        }

        $newSodaBalance = $sodaBalance - $sodaAmount;
        log_payment( "Reduced Soda balance [" . $newSodaBalance . "] is [" . $sodaBalance . " Balance - " . $sodaAmount . " Amount]" );

        $newSnackBalance = $snackBalance - $snackAmount;
        log_payment( "Reduced Snack balance [" . $newSnackBalance . "] is [" . $snackBalance . " Balance - " . $snackAmount . " Amount]" );

        $newTotalBalance = $newSodaBalance + $newSnackBalance;
        $balance = $sodaBalance + $snackBalance;
        $amount = $sodaAmount + $snackAmount;

        $paymentMessage = "Your payment with $method was received for $paymentMonth.\n\n" .
        "Your Current Balance: *" . getPriceDisplayWithDollars( $newTotalBalance ) . "*       (*" . getPriceDisplayWithDollars( $balance ) . "* original balance  -  *" . getPriceDisplayWithDollars( $amount ) . "* payment)";

        sendSlackMessageToUser( $slackID,  $paymentMessage, ":dollar:", "PAYMENT RECEIVED", "#127b3c", $username, true );

        $date = date('Y-m-d H:i:s');

        // DEV: Changing this code? Change the payment code for Auditing as well (above)
        $db->exec("BEGIN;");

        $stmt=$db->prepare("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment) VALUES ".
            "(:userID, :method, :sodaAmount, :date, :note, 'Soda', :paymentMonth)");
        $stmt->bindValue(':userID', $userID );
        $stmt->bindValue(':method', $method );
        $stmt->bindValue(':sodaAmount', $sodaAmount );
        $stmt->bindValue(':date', $date );
        $stmt->bindValue(':note', $note );
        $stmt->bindValue(':paymentMonth', $paymentMonth );
        $stmt->execute();

        $stmt=$db->prepare("INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment) VALUES " .
            "(:userID, :method, :snackAmount, :date, :note, 'Snack', :paymentMonth)");
        $stmt->bindValue(':userID', $userID );
        $stmt->bindValue(':method', $method );
        $stmt->bindValue(':snackAmount', $snackAmount );
        $stmt->bindValue(':date', $date );
        $stmt->bindValue(':note', $note );
        $stmt->bindValue(':paymentMonth', $paymentMonth );
        $stmt->execute();

        $stmt=$db->prepare("UPDATE User SET SodaBalance = SodaBalance - :sodaAmount where UserID = :userID");
        $stmt->bindValue(':sodaAmount', $sodaAmount );
        $stmt->bindValue(':userID', $userID );
        $stmt->execute();

        $stmt=$db->prepare("UPDATE User SET SnackBalance = SnackBalance - :snackAmount where UserID = :userID");
        $stmt->bindValue(':snackAmount', $snackAmount );
        $stmt->bindValue(':userID', $userID );
        $stmt->execute();

        $stmt=$db->prepare("UPDATE Information SET SitePayments = SitePayments + :sodaAmount where ItemType = 'Soda'");
        $stmt->bindValue(':sodaAmount', $sodaAmount );
        $stmt->execute();

        $stmt=$db->prepare("UPDATE Information SET SitePayments = SitePayments + :snackAmount where ItemType = 'Snack'");
        $stmt->bindValue(':snackAmount', $snackAmount );
        $stmt->execute();

        $db->exec("COMMIT;");

        return "Payment added successfully.";
    }
}
/**
 * @param $db SQLite3
 * @param $quantity
 * @param $multiplier
 * @param $retailCost
 * @param $itemType
 * @return string
 */
function restockItem( $db, $itemID, $quantity, $multiplier, $retailCost, $itemType, $expDate ) {

    $retailCost = convertDecimalToWholeCents( trim( $retailCost ) );

    if( $multiplier > 1 ) {
        $quantity *= $multiplier;
        $retailCost *= $multiplier;
    }

    $restockTrigger = "";
    if( $quantity > 3 ) {
        $restockTrigger = " RestockTrigger = 0, IsBought = 0,";
    }

    $date = date('Y-m-d H:i:s', time());
    $costEach = round( $retailCost / $quantity );

    // Get the current prices from the item catalog
    $itemStatement = $db->prepare("SELECT Price, DiscountPrice From Item where ID = :itemID");
    $itemStatement->bindValue( ":itemID", $itemID );
    $itemResults = $itemStatement->execute();

    $itemRow = $itemResults->fetchArray();
    $price = $itemRow['Price'];
    $discountPrice = $itemRow['DiscountPrice'];

    $stmt=$db->prepare("INSERT INTO Item_Details (ItemID, Price, DiscountPrice, RetailPrice, ExpDate) VALUES " .
        "(:itemID, :price, :discountPrice, :costEach, :expDate)");
    $stmt->bindValue(':itemID', $itemID );
    $stmt->bindValue(':price', $price );
    $stmt->bindValue(':discountPrice', $discountPrice );
    $stmt->bindValue(':costEach', $costEach );
    $stmt->bindValue(':expDate', $expDate );
    $stmt->execute();
    $itemDetailsID = $db->lastInsertRowID();

    $db->exec("BEGIN;");

    $stmt=$db->prepare("INSERT INTO Restock (ItemID, Date, NumberOfCans, Cost) VALUES " .
        "(:itemID, :date, :quantity, :retailCost)");
    $stmt->bindValue(':itemID', $itemID );
    $stmt->bindValue(':date', $date );
    $stmt->bindValue(':quantity', $quantity );
    $stmt->bindValue(':retailCost', $retailCost );
    $stmt->execute();

    addToBackstockQuantity( $db, $quantity, $itemID, $itemDetailsID, "RESTOCK" );

    $stmt=$db->prepare("UPDATE Item SET ItemExpenses = ItemExpenses + :retailCost, $restockTrigger TotalCans = TotalCans + :quantity where ID = :itemID");
    $stmt->bindValue(':retailCost', $retailCost );
    $stmt->bindValue(':quantity', $quantity );
    $stmt->bindValue(':itemID', $itemID );
    $stmt->execute();

    $stmt=$db->prepare("UPDATE Information SET SiteExpenses = SiteExpenses + :retailCost where ItemType = :itemType");
    $stmt->bindValue(':retailCost', $retailCost );
    $stmt->bindValue(':itemType', $itemType );
    $stmt->execute();

    $db->exec("COMMIT;");

    sendSlackMessageToMatt("*Item ID:* $itemID\n*Quantity:* $quantity\n*Cost:* $retailCost\n*Multiplier:* $multiplier", ":truck:", "RESTOCK ITEM", "#b7ab1a");

    return "Restocked successfully.";
}

/**
 * @param $db SQLite3
 * @param $itemID_all
 * @param $addToShelf_all
 * @param $sendToSlack
 */
function refillItem( $db, $isTest, $itemID_all, $addToShelf_all, $sendToSlack, $refiller ) {

    benchmark_start( "REFILL" );
    $date = date('Y-m-d H:i:s');

    $slackMessageItems = "";
    $itemType = "";
    for ($i = 0; $i < count($itemID_all); $i++) {
        $itemID = $itemID_all[$i];
        $addToShelfQuantity = $addToShelf_all[$i];

        benchmark_start("Refill Item $itemID");

        if( $addToShelfQuantity > 0 ) {
            error_log("Item ID [$itemID] Quantity [$addToShelfQuantity]");

            $backstockQuantityBefore = 0;
            $shelfQuantityBefore = 0;

            $itemName = "N/A";
            $price = "N/A";
            $itemUnits = "N/A";
            $itemUnitsPlural = "N/A";
            $currentFlavor = "N/A";

            benchmark_start("Refill - Get Quantity $itemID");
            $statement = $db->prepare("SELECT i.ID," . getQuantityQuery() . ",i.Price, i.Name, i.Type, i.UnitName, i.UnitNamePlural, i.CurrentFlavor FROM Item i WHERE i.ID = :itemID");
            $statement->bindValue( ":itemID", $itemID );
            $results = $statement->execute();

            while ($row = $results->fetchArray()) {
                $backstockQuantityBefore = $row['BackstockAmount'];
                $shelfQuantityBefore = $row['ShelfAmount'];
                $itemName = $row['Name'];
                $price = $row['Price'];
                $itemType = $row['Type'];
                $itemUnits = $row['UnitName'];
                $itemUnitsPlural = $row['UnitNamePlural'];
                $currentFlavor = $row['CurrentFlavor'];

                if ($currentFlavor != "") {
                    $currentFlavor = "[" . $currentFlavor . "] ";
                }
            }

            benchmark_stop("Refill - Get Quantity $itemID");

            benchmark_start("Refill - Phase 1 $itemID");

            $newShelfQuantity = $shelfQuantityBefore + $addToShelfQuantity;
            $newBackstockQuantity = $backstockQuantityBefore - $addToShelfQuantity;

            $refillTrigger = "";
            //New item was added to the fridge
            $priceDisplay = getPriceDisplayWithEnglish($price);

            $slackMessageItems = $slackMessageItems . "*" . $itemName . " $currentFlavor:* " . $shelfQuantityBefore . " " .
                ($shelfQuantityBefore == 1 ? $itemUnits : $itemUnitsPlural) .
                " --> *" . $newShelfQuantity . " " .
                ($newShelfQuantity == 1 ? $itemUnits : $itemUnitsPlural) . "*    ($priceDisplay)\n";

            // Only clear the trigger of the items that are refilled
            if ($newShelfQuantity > 3) {
                $refillTrigger = "RefillTrigger = 0, IsBought = 0,";
            }

            benchmark_stop("Refill - Phase 1 $itemID");

            benchmark_start("Refill - Move Quantity  $itemID");
            $itemDetailsArray = moveToShelfQuantity($db, $addToShelfQuantity, $itemID);
            benchmark_stop("Refill - Move Quantity  $itemID");

            benchmark_start("Refill - Add Inv  $itemID");

            $currentBackstockQuantityCounter = $backstockQuantityBefore;
            $currentShelfQuantityCounter = $shelfQuantityBefore;

            $db->exec("BEGIN;");
            foreach ($itemDetailsArray as $itemDetails) {
                $itemDetailsID = $itemDetails->getItemDetailsID();

                $statement = $db->prepare( "INSERT INTO Inventory_History (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, InventoryType, ItemDetailsID) VALUES " .
                    "(:itemID, :date, :backstockBefore, :backstockAfter, :shelfBefore, :shelfAfter, :inventoryType, :itemDetailsID)" );

                $statement->bindValue(":itemID", $itemID );
                $statement->bindValue(":date", $date );
                $statement->bindValue(":backstockBefore", $currentBackstockQuantityCounter );
                $statement->bindValue(":backstockAfter",  ($currentBackstockQuantityCounter - 1) );
                $statement->bindValue(":shelfBefore", $currentShelfQuantityCounter );
                $statement->bindValue(":shelfAfter", ($currentShelfQuantityCounter + 1) );
                $statement->bindValue(":inventoryType", 'REFILL' );
                $statement->bindValue(":itemDetailsID", $itemDetailsID );

                $statement->execute();

                $currentBackstockQuantityCounter--;
                $currentShelfQuantityCounter++;
            }
            $db->exec("COMMIT;");

            benchmark_stop("Refill - Add Inv  $itemID");

            $restockTrigger = "";
            if ($newBackstockQuantity <= 3) {
                $restockTrigger = " RestockTrigger = 1, ";
            }

            $statement = $db->prepare( "UPDATE Item SET $refillTrigger $restockTrigger DateModified = :date where ID = :itemID" );

            $statement->bindValue(":itemID", $itemID );
            $statement->bindValue(":date", $date );
            $statement->execute();
        }

        benchmark_stop( "Refill Item $itemID" );
    }

    $emoji = ":soda:";
    $page = SODASTOCK_LINK;

    if( $itemType == "Snack" ) {
        $emoji = ":cookie:";
        $page = SNACKSTOCK_LINK;
    }

    if( !$isTest && $slackMessageItems != "" && $sendToSlack == true) {
        $slackMessage = $slackMessageItems ."\n\nVisit <https://penguinore.net$page|Foodstock> to see the prices and inventory of all snacks & sodas.";

        if( $refiller != "Matt" ) {
            $refillLabel = "REFILLED BY " . strtoupper($refiller);
        } else {
            $refillLabel = "REFILL";
        }

        sendSlackMessageToRandom($slackMessage, $emoji, $itemType. "Stock - $refillLabel" );

        $subscribeStatement = $db->prepare("SELECT SlackID, UserName FROM User where SubscribeRestocks = 1" );
        $subscribeResults = $subscribeStatement->execute();

        while( $row = $subscribeResults->fetchArray() ) {
            $slackIDToNotify = $row['SlackID'];
            $username = $row['UserName'];
            sendSlackMessageToUser( $slackIDToNotify, $slackMessage, $emoji, $itemType. "Stock - $refillLabel", "#000000", $username, false );
        }
    }
    benchmark_stop( "REFILL" );

    return "Refill was successful for " . count($itemID_all) . " items.";
}

function cancelInventory( $dailyAmountID ) {
//            $dailyAmountID = trim($_POST["DailyAmountID"]);
//
//        $results = $db->query("SELECT i.Type, d.ItemID, d.BackstockQuantityBefore, d.BackstockQuantity, d.ShelfQuantityBefore, d.ShelfQuantity, d.Price, d.RetailCost, d.ItemDetailsID from Inventory_History d JOIN Item i on d.ItemID =  i.ID WHERE d.ID = $dailyAmountID");
//        $row = $results->fetchArray();
//
//        $date = date('Y-m-d H:i:s');
//        $itemID = $row['ItemID'];
//        $itemType = $row['Type'];
//        $backstockBefore = $row['BackstockQuantityBefore'];
//        $backstockAfter= $row['BackstockQuantity'];
//        $shelfBefore = $row['ShelfQuantityBefore'];
//        $shelfAfter= $row['ShelfQuantity'];
//        $price = $row['Price'];
//        $itemDetailsID = $row['ItemDetailsID'];
//
//        $quantityBefore = $backstockBefore + $shelfBefore;
//        $quantityAfter = $backstockAfter + $shelfAfter;
//
//        $backstockDelta = $backstockBefore - $backstockAfter;
//        $shelfDelta = $shelfBefore - $shelfAfter;
//
//        $income = ($quantityBefore - $quantityAfter) * $price;
//
//        $cancelSQL = "UPDATE Inventory_History SET Cancelled = 1 WHERE ID = $dailyAmountID";
//        log_sql("Cancel SQL: [" . $cancelSQL . "]" );
//        $db->exec( $cancelSQL );
//
//        $newIncome = addToValue( $db, "Item", "TotalIncome", $income, "where ID = $itemID", false );
//        $itemSQL = "UPDATE Item SET TotalIncome = $newIncome, ModifyType = 'Cancelled', DateModified = '$date' where ID = $itemID";
//        log_sql("Item SQL: [" . $itemSQL . "]" );
//        $db->exec( $itemSQL );
//
//        addToBackstockQuantity( $db, $backstockDelta, $itemID, $itemDetailsID, "CANCEL MANUAL PURCHASE" );
//        addToShelfQuantity( $db, $shelfDelta, $itemID, $itemDetailsID, "CANCEL MANUAL PURCHASE" );
//
//        $newIncome = addToValue( $db, "Information", "Income", $income, "where ItemType = '$itemType'", false );
//        $infoSQL = "UPDATE Information SET Income = $newIncome where ItemType = '$itemType'";
//
//        log_sql("Info SQL: [" . $infoSQL . "]" );
//        $db->exec( $infoSQL );
}
/**
 * @param $db SQLite3
 * @return string
 */
function inventoryItem( $db, $itemID_all, $removeFromShelf_all, $auditAmount, $itemType ) {
    $date = date('Y-m-d H:i:s');

    $backstockQuantityBefore = 0;
    $shelfQuantityBefore = 0;
    $auditID = 0;
    $auditMessage = "";

    if( $auditAmount != "" ) {
        $auditAmount =  convertDecimalToWholeCents( $auditAmount );

        log_debug(" Audit found [$auditAmount] - [$itemType]" );

        $statement = $db->prepare( "INSERT INTO Audit (Date, MissingMoney, ItemType) VALUES(:date, 0, :itemType)" );

        $statement->bindValue(":date", $date );
        $statement->bindValue(":itemType", $itemType );

        $statement->execute();
        $auditID = $db->lastInsertRowID();

        $auditMessage = "(AUDIT #$auditID)";

        $firstOfMonth = mktime(0, 0, 0, date("m"), 1, date("Y") );
        $monthLabel = date('F Y', $firstOfMonth);

        $statement = $db->prepare( "INSERT INTO Payments (UserID, Method, Amount, Date, Note, ItemType, MonthForPayment, AuditID) VALUES " .
            "(0, '', :auditAmount, :date, 'Audited', :itemType, :monthLabel, :auditID)" );

        $statement->bindValue(":auditAmount", $auditAmount );
        $statement->bindValue(":date", $date );
        $statement->bindValue(":itemType", $itemType );
        $statement->bindValue(":monthLabel", $monthLabel );
        $statement->bindValue(":auditID", $auditID );

        $statement->execute();
    }

    $db->exec("BEGIN;");

    for ($i = 0; $i < count($itemID_all); $i++) {
        $itemID = $itemID_all[$i];
        $newShelfQuantity = $removeFromShelf_all[$i];

        $statement = $db->prepare("SELECT " . getQuantityQuery() . " FROM Item i WHERE i.ID = :itemID");
        $statement->bindValue( ":itemID", $itemID );
        $results = $statement->execute();

        while ($row = $results->fetchArray()) {
            $shelfQuantityBefore = $row['ShelfAmount'];
            $backstockQuantityBefore = $row['BackstockAmount'];
        }

        $shelfDelta = $shelfQuantityBefore - $newShelfQuantity;

        $itemDetailsArray = removeFromShelfQuantity( $db, $shelfDelta, $itemID );

        $totalIncome = 0;
        $totalProfit = 0;

        $currentShelfQuantityCounter = $shelfQuantityBefore;
        foreach( $itemDetailsArray as $itemDetailsObj ) {
            $totalIncome += $itemDetailsObj->getFullPrice();
            $totalProfit += ( $itemDetailsObj->getFullPrice() - $itemDetailsObj->getRetailPrice() );
            $itemDetailsID = $itemDetailsObj->getItemDetailsID();

            $statement = $db->prepare( "INSERT INTO Inventory_History (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Restock, InventoryType, ItemDetailsID, AuditID) VALUES " .
                "(:itemID, :date, :backstockQuantityBefore, :backstockQuantityAfter, :shelfQuantityBefore, :shelfQuantityAfter,  0, 'MANUAL PURCHASE', :itemDetailsID, :auditID)" );

            $statement->bindValue(":itemID", $itemID );
            $statement->bindValue(":date", $date );
            $statement->bindValue(":backstockQuantityBefore", $backstockQuantityBefore );
            $statement->bindValue(":backstockQuantityAfter", $backstockQuantityBefore );
            $statement->bindValue(":shelfQuantityBefore", $currentShelfQuantityCounter );
            $statement->bindValue(":shelfQuantityAfter", ($currentShelfQuantityCounter - 1) );
            $statement->bindValue(":itemDetailsID", $itemDetailsID );
            $statement->bindValue(":auditID", $auditID );

            $statement->execute();

            $currentShelfQuantityCounter--;
        }

        $refillTriggerAmount = $itemType == "Snack" ? 3 : 1;

        $refillTrigger = "";
        if( $newShelfQuantity <= $refillTriggerAmount ) {
            $date = date('Y-m-d H:i:s', time());
            $refillTrigger = " RefillTrigger = 1, ";
        }

        $statement = $db->prepare( "UPDATE Information SET SiteIncome = SiteIncome + :totalIncome, SiteProfit = SiteProfit + :totalProfit  where ItemType = :itemType" );

        $statement->bindValue(":totalIncome", $totalIncome );
        $statement->bindValue(":totalProfit", $totalProfit );
        $statement->bindValue(":itemType", $itemType );

        $statement->execute();


        $statement = $db->prepare( "UPDATE Item SET $refillTrigger ItemIncome = ItemIncome + :itemIncome, ItemProfit = ItemProfit + :itemProfit  where ID = :itemID" );

        $statement->bindValue(":itemIncome", $totalIncome );
        $statement->bindValue(":itemProfit", $totalProfit );
        $statement->bindValue(":itemID", $itemID );

        $statement->execute();
    }

    if( $auditAmount != "" ) {
        $auditDetails = getAuditDetails( $db, $auditID, $itemType );
        $totalIncomeForAudit = $auditDetails->getTotalIncomeForAudit();
        $missingMoney = $totalIncomeForAudit - $auditAmount;

        error_log("[$auditAmount] Audit Amount [$totalIncomeForAudit] Total Income [$missingMoney] Missing Money" );

        $statement = $db->prepare( "UPDATE Information SET SiteLoss = SiteLoss + :missingMoney where ItemType = :itemType" );

        $statement->bindValue(":missingMoney", $missingMoney );
        $statement->bindValue(":itemType", $itemType );

        $statement->execute();
    }

    $db->exec("COMMIT;");

    sendSlackMessageToMatt(count( $itemID_all ) . " items", ":card_file_box:", "INVENTORY ITEM", "#b7ab1a");

    return "Inventory was successful for " . count($itemID_all) . " items. $auditMessage";
}

/**
 * @param $db SQLite3
 * @param $userID
 * @param $amount
 * @param $returnCredits
 */
function creditUser( $db, $isTest, $userID, $creditAmountInDecimal, $returnCredits ) {
    $creditAmountWholeCents = convertDecimalToWholeCents( $creditAmountInDecimal );
    $userMessage = "";

    $validCredits = true;

    $creditStatement = $db->prepare("SELECT Credits, SlackID, FirstName, LastName, UserName From User where UserID = :userID");
    $creditStatement->bindValue( ":userID", $userID );
    $creditResults = $creditStatement->execute();

    $creditRow = $creditResults->fetchArray();
    $currentCredits = $creditRow['Credits'];
    $slackID = $creditRow['SlackID'];
    $username = $creditRow['UserName'];
    $name = $creditRow['FirstName'] . " " . $creditRow['LastName'];

    if( $returnCredits ) {
        if( $currentCredits - $creditAmountWholeCents < 0 ) {
            $validCredits = false;
            $userMessage = $userMessage . "Failure! User only has $currentCredits cents -  cannot subtract $creditAmountWholeCents cents!";
        }
        $creditAmountWholeCents = $creditAmountWholeCents * -1;

        $creditMessage = "*$" . $creditAmountInDecimal . "* credits have been deducted from your account.";
    } else {
        $creditMessage = "*$" . $creditAmountInDecimal . "* credits have been added to your account.";
    }

    if( $validCredits ) {
        $db->exec("BEGIN;" );

        $statement = $db->prepare( "UPDATE User SET Credits=Credits + :creditAmountWholeCents where UserID = :userID" );

        $statement->bindValue(":creditAmountWholeCents", $creditAmountWholeCents );
        $statement->bindValue(":userID", $userID );

        $statement->execute();

        $statement = $db->prepare( "INSERT Into Purchase_History (UserID, ItemID, Cost, Date ) VALUES (:userID, " . CREDIT_ID . ", :creditAmountWholeCents, :date)" );

        $statement->bindValue(":userID", $userID );
        $statement->bindValue(":creditAmountWholeCents", $creditAmountWholeCents );
        $statement->bindValue(":date", date('Y-m-d H:i:s', time()) );

        $statement->execute();

        $db->exec("COMMIT;" );

        $userMessage = $userMessage . "User credited successfully with " . getPriceDisplayWithDollars($creditAmountWholeCents);

        if( !$isTest ) {
            sendSlackMessageToUser($slackID, $creditMessage, ":label:", "FoodStock - CREDITS", "#3f5abb", $username, true);
        }
    }

    return $userMessage;
}
/**
 * @param $db SQLite3
 * @param $cashOnly
 * @param $itemsInCart
 * @param $userID
 * @param $creditsOfUser
 */
function purchaseItems( $db, $isTest, $userID, $itemsInCart, $cashOnly )
{
    if( count ( $itemsInCart ) == 0 ) {
        error_log( "Something is wrong. Zero items were purchased?" );
        return;
    }

    if( $userID == "" ) {
        error_log( "Something is wrong. User tried purchasing with a null user ID." );
        return "User ID is null. Contact Matt Miles!";
    }

    $totalPrice = 0.0;
    $totalCredits = 0.0;
    $totalSavings = 0.0;

    $errors = "";
    $purchaseMessage = "";
    $itemsOutOfStock = array();

    $userQuery = "SELECT SlackID, UserName, FirstName, LastName, SnackBalance, SodaBalance, Inactive, Credits FROM User WHERE UserID = :userID";
    $userStatement = $db->prepare( $userQuery );
    $userStatement->bindValue( ":userID", $userID );
    $userResults = $userStatement->execute();

    $userRow = $userResults->fetchArray();

    $creditsLeftOfUser = $userRow['Credits'];
    $firstName = $userRow['FirstName'];
    $lastName = $userRow['LastName'];
    $slackID = $userRow['SlackID'];
    $userName = $userRow['UserName'];
    $currentSodaBalance = $userRow['SodaBalance'];
    $currentSnackBalance = $userRow['SnackBalance'];
    $inactiveUser = $userRow['Inactive'];
    error_log("Inactive bought [$userID] $inactiveUser]" );

    if( $inactiveUser == 1 ) {
        return "You cannot purchase items while you are inactive!";
    }

    $itemQuantityValidationArray = array();
    $itemQuantityValidationPassed = true;
    $itemType = "UNKNOWN";
    foreach ($itemsInCart as $itemID) {

        if( array_key_exists( $itemID, $itemQuantityValidationArray ) === false ) {
            $itemValidationStatement = $db->prepare( "SELECT i.Type, " . getQuantityQuery() . " FROM Item i WHERE ID = :itemID" );
            $itemValidationStatement->bindValue( ":itemID", $itemID );
            $itemValidationResults = $itemValidationStatement->execute();

            $itemValidationRow = $itemValidationResults->fetchArray();
            $itemValidationShelfQuantity = $itemValidationRow['ShelfAmount'];
            $itemType = $itemValidationRow['Type'];
            $itemQuantityValidationArray[$itemID] = $itemValidationShelfQuantity;
        }

        $itemQuantityValidationArray[$itemID]--;

        if( $itemQuantityValidationArray[$itemID] < 0 ) {
            $itemQuantityValidationPassed = false;
            $errors .= "Validation failed for Item ID [$itemID]. Purchasing more than what is available.\n";
        }
    }

    if( !$itemQuantityValidationPassed ) {
        $errors .= "The following items were purchased by *$firstName $lastName*: [" . implode(",", $itemsInCart ) . "] :angrymob: GET THEM! :angrymob: ";
    }

    log_payment( "=======================================================================" );
    log_payment("START ItemType: [$itemType] Count: [" . count( $itemsInCart ) . "] Credits: [$creditsLeftOfUser]" );

    $vendorsToNotify = array();

    // TODO MTM: This is a bit slow with 25 purchased items
    if( $itemQuantityValidationPassed ) {
        foreach ($itemsInCart as $itemID) {
            $startTimeItem = time();
            $itemQuery = "SELECT u.UserID, u.SlackID, i.Name," . getQuantityQuery() . " FROM Item i LEFT JOIN User u ON i.VendorID = u.UserID WHERE ID = :itemID";
            log_sql("ITEM QUERY [$itemQuery]");
            $statement = $db->prepare($itemQuery);
            $statement->bindValue( ":itemID", $itemID );
            $results = $statement->execute();

            $row = $results->fetchArray();

            $itemProfit = 0.0;
            $itemName = $row['Name'];
            $shelfQuantity = $row['ShelfAmount'];
            $backstockQuantity = $row['BackstockAmount'];
            $vendorSlackID = $row['SlackID'];
            $vendorID = $row['UserID'];

            if ($shelfQuantity - 1 <= -1) {
                $errors .= "Welp, this should never happen. Validation should have caught this. Item Name: " . $itemName;
                error_log("FAIL [$itemType] [$itemName]");
            } else {
                $db->exec( "BEGIN;" );
                $itemDetailsObj = removeFromShelfQuantity($db, 1, $itemID )[0];
                $db->exec( "COMMIT;" );

                $itemType = $itemDetailsObj->getItemType();
                log_payment("GOOD [$itemType] [$itemName]");
                $itemPrice = $itemDetailsObj->getSitePurchasePrice();
                $originalItemPrice = $itemDetailsObj->getFullPrice();
                $itemDetailsID = $itemDetailsObj->itemDetailsID;
                $retailCostPerItem = $itemDetailsObj->getRetailPrice();

                $refillTriggerAmount = $itemType == "Snack" ? 3 : 1;
                $refillTrigger = "";
                if ($shelfQuantity - 1 <= $refillTriggerAmount) {

                    if ($shelfQuantity - 1 <= 0) {
                        $itemsOutOfStock[] = $itemName;
                    }
                    $date = date('Y-m-d H:i:s', time());
                    $refillTrigger = " ,RefillTrigger = 1, OutOfStockDate = '$date', OutOfStockReporter='StockBot'";
                }

                $date = date('Y-m-d H:i:s', time());
                $useCredits = 0;

                log_payment( "Purchase. ItemPrice [$itemPrice] FullPrice [$originalItemPrice] CreditsLeft[$creditsLeftOfUser] Type[$itemType]" );
                if ($creditsLeftOfUser > 0 && !$cashOnly) {
                    $useCredits = $creditsLeftOfUser;
                    $creditsLeftOfUser -= $itemPrice;

                    log_payment("Use Credits: [$useCredits] Credits Left: [$creditsLeftOfUser]" );

                    if ($creditsLeftOfUser < 0) {
                        // If we went over their credit limit, we need to add it to their balance now
                        $totalPrice += abs($creditsLeftOfUser);
                        $totalCredits += $useCredits;
                        $creditsLeftOfUser = 0;

                        log_payment("Part Credits, Part Balance. Total Balance: [$totalPrice]" );
                    } else {
                        $useCredits = $itemPrice;
                        $totalCredits += $itemPrice;

                        log_payment("All Credits. Credits Left: [$totalPrice]" );
                    }
                } else {
                    // Dont charge vendor for their own products
                    if( $vendorID != $userID ) {
                        $totalPrice += $itemPrice;
                    }
                }

                $itemProfit += ($itemPrice - $retailCostPerItem);

                log_payment("Profit [$itemProfit] = Item Price [$itemPrice] - RetailCost [$retailCostPerItem] - [UPDATE Item SET ItemProfit = ItemProfit + $itemProfit where ID = $itemID]" );

                $cashOnlyInteger = $cashOnly ? 1 : 0;

                $statement = $db->prepare( "INSERT INTO Inventory_History (ItemID, Date, BackstockQuantityBefore, BackstockQuantity, ShelfQuantityBefore, ShelfQuantity, Restock, InventoryType, ItemDetailsID ) VALUES " .
                    "(:itemID, :date, :backstockQuantityBefore, :backstockQuantityAfter, :shelfQuantityBefore, :shelfQuantityAfter, 0, 'SITE PURCHASE', :itemDetailsID )" );

                $statement->bindValue(":itemID", $itemID );
                $statement->bindValue(":date", $date );
                $statement->bindValue(":backstockQuantityBefore", $backstockQuantity );
                $statement->bindValue(":backstockQuantityAfter", $backstockQuantity );
                $statement->bindValue(":shelfQuantityBefore", $shelfQuantity );
                $statement->bindValue(":shelfQuantityAfter",  ($shelfQuantity - 1) );
                $statement->bindValue(":itemDetailsID",  $itemDetailsID );

                $statement->execute();
                $inventoryHistoryID = $db->lastInsertRowID();

                $db->exec( "BEGIN;" );

                $statement = $db->prepare("UPDATE Item SET ItemIncome = ItemIncome + :itemPrice, ItemProfit = ItemProfit + :itemProfit where ID = :itemID");

                $statement->bindValue(":itemPrice", $itemPrice);
                $statement->bindValue(":itemProfit", $itemProfit);
                $statement->bindValue(":itemID", $itemID);

                $statement->execute();

                if( $vendorSlackID == "" ) {
                    // Only update site income for non-vendor items
                    $statement = $db->prepare("UPDATE Information SET SiteIncome = SiteIncome + :itemPrice, SiteProfit = SiteProfit + :itemProfit where ItemType = :itemType");

                    $statement->bindValue(":itemPrice", $itemPrice);
                    $statement->bindValue(":itemProfit", $itemProfit);
                    $statement->bindValue(":itemType", $itemType);

                    $statement->execute();
                } else {
                    // Add vendor to notify list
                    if( !in_array( $vendorSlackID, $vendorsToNotify ) ) {
                        $vendorsToNotify[] = $vendorSlackID;
                    }
                }


                $statement = $db->prepare( "INSERT Into Purchase_History (UserID, ItemID, Date, CashOnly, DailyAmountID, UseCredits, ItemDetailsID) VALUES " .
                    "(:userID, :itemID, :date, :cashOnlyInteger, :inventoryHistoryID, :useCredits, :itemDetailsID )" );

                $statement->bindValue(":userID", $userID );
                $statement->bindValue(":itemID", $itemID );
                $statement->bindValue(":date", $date );
                $statement->bindValue(":cashOnlyInteger", $cashOnlyInteger );
                $statement->bindValue(":inventoryHistoryID", $inventoryHistoryID );
                $statement->bindValue(":useCredits", $useCredits );
                $statement->bindValue(":itemDetailsID", $itemDetailsID );

                $statement->execute();

                $db->exec( "COMMIT;" );

                $purchaseMessage = $purchaseMessage . "- " . $itemName . " (" . getPriceDisplayWithDollars($itemPrice) . ")\n";
            }
            $stopTimeItem = time();
            $totalTimeItem = $stopTimeItem - $startTimeItem;

            log_benchmark("Time to complete purchase for [$itemName]: $totalTimeItem seconds");
        }

        log_payment("FINAL [$itemType]");
    }

    if( $errors == "" ) {
        if (!$cashOnly) {

            if( $itemType == "Snack" ) {
                $typeOfBalance = "SnackBalance";
                $typeOfSavings = "SnackSavings";

                $currentSnackBalance += $totalPrice;
            } else if( $itemType == "Soda" ) {
                $typeOfBalance = "SodaBalance";
                $typeOfSavings = "SodaSavings";

                $currentSodaBalance += $totalPrice;
            }

            $statement = $db->prepare( "UPDATE User SET $typeOfBalance = $typeOfBalance + :totalPrice , $typeOfSavings = $typeOfSavings + :totalSavings, Credits = :creditsLeftOfUser where UserID = :userID" );

            $statement->bindValue(":totalPrice", $totalPrice );
            $statement->bindValue(":totalSavings", $totalSavings );
            $statement->bindValue(":creditsLeftOfUser", $creditsLeftOfUser );
            $statement->bindValue(":userID", $userID );

            $statement->execute();
        }

        if ($totalCredits > 0) {
            $purchaseMessage = $purchaseMessage . "*Total Credits:* " . getPriceDisplayWithDollars($totalCredits) . "\n";
        }

        if ($totalPrice > 0) {
            $purchaseMessage = $purchaseMessage . "*Total Price:* " . getPriceDisplayWithDollars($totalPrice) . "\n";
        }

        if (!$cashOnly) {
            $totalBalance = $currentSodaBalance + $currentSnackBalance;
            $purchaseMessage = $purchaseMessage . "*Your Balance:* " . getPriceDisplayWithDollars($totalBalance) . "\n";
        } else {
            $purchaseMessage = $purchaseMessage . "*THIS PURCHASE WAS CASH-ONLY*\n";
        }

        // Notify admin about purchase
        if( !in_array( "U1FEGH4U9", $vendorsToNotify ) ) {
            $vendorsToNotify[] = "U1FEGH4U9";
        }

        if( !$isTest ) {
            // Send receipts to the admin and vendors
            foreach( $vendorsToNotify as $vendorSlackID ) {
                sendSlackMessageToUser($vendorSlackID, "*(" . strtoupper("$firstName $lastName") . ")*\n" . $purchaseMessage, ":shopping_trolley:", $itemType . "Stock - RECEIPT", "#3f5abb", $vendorSlackID );
            }

            sendSlackMessageToUser($slackID, $purchaseMessage, ":shopping_trolley:", $itemType . "Stock - RECEIPT", "#3f5abb", $userName );

            $_SESSION["SodaBalance"] = $currentSodaBalance;
            $_SESSION["SnackBalance"] = $currentSnackBalance;
            $_SESSION['PurchaseCompleted'] = 1;

            if (count($itemsOutOfStock) > 0) {
                foreach ($itemsOutOfStock as $item) {
                    sleep(1);
                    sendSlackMessageToMatt("*Item Name:* " . $item . "\n*Buyer:* " . "$firstName $lastName", ":negative_squared_cross_mark:", "OUT OF STOCK BY PURCHASE", "#791414");
                }
            }
        }

        return "Purchase Completed";
    } else {
        error_log( "ERROR: [$userID]" . $errors );
        sendSlackMessageToMatt( "Errors: " . $errors, ":no_entry:", $itemType . "Stock - ERROR!!", "#bb3f3f" );
        return "Something went wrong - contact Matt!! " . $errors;
    }
}

/**
 * @param $db SQLite3
 * @param $paymentID
 */
function cancelPayment( $db, $paymentID ) {
    $statement = $db->prepare("SELECT UserID, Amount, ItemType From Payments where PaymentID = :paymentID");
    $statement->bindValue( ":paymentID", $paymentID );
    $results = $statement->execute();

    $row = $results->fetchArray();

    $userID = $row['UserID'];
    $amount = $row['Amount'];
    $itemType = $row['ItemType'];

    $isUserPayment = $userID > 0;

    $db->exec( "BEGIN;" );

    $statement = $db->prepare( "UPDATE Payments SET Cancelled = 1 WHERE PaymentID = :paymentID" );
    $statement->bindValue(":paymentID", $paymentID );
    $statement->execute();

    if( $isUserPayment ) {
        $balanceType = $itemType . "Balance";

        $statement = $db->prepare( "UPDATE User SET $balanceType = $balanceType + :amount WHERE UserID = :userID" );
        $statement->bindValue(":amount", $amount );
        $statement->bindValue(":userID", $userID );
        $statement->execute();
    }

    $statement = $db->prepare( "UPDATE Information SET SitePayments = SitePayments - :amount WHERE ItemType = :itemType" );
    $statement->bindValue(":amount", $amount );
    $statement->bindValue(":itemType", $itemType );
    $statement->execute();

    $db->exec( "COMMIT;" );
}
/**
 * @param $db SQLite3
 * @param $itemID
 * @param $itemType
 * @param $quantity
 */
function defectItem($db, $itemID, $quantity ) {
    $statement = $db->prepare("SELECT " . getQuantityQuery() . ", Type From Item i where ID = :itemID" );
    $statement->bindValue( ":itemID", $itemID );
    $results = $statement->execute();

    $row = $results->fetchArray();
    $shelfQuantity = $row['ShelfAmount'];
    $itemType = $row['Type'];

    error_log("DEFECTIVE [$itemID] [$quantity] [$shelfQuantity]" );

    if( $quantity > $shelfQuantity ) {
        return "Error: Trying to defect out more than you have on the shelf of $shelfQuantity units.";
    } else {

        $db->exec( "BEGIN;" );

        $itemDetailsArray = removeFromShelfQuantity( $db, $quantity, $itemID );

        error_log("DEFECTIVE REMOVED [$itemID] [$quantity] [$shelfQuantity]" );

        foreach( $itemDetailsArray as $itemDetails ) {
            $statement = $db->prepare( "INSERT INTO Defectives (ItemID, Date, Amount, Price) VALUES(:itemID, :date, 1, :fullPrice)" );
            $statement->bindValue(":itemID", $itemID );
            $statement->bindValue(":date", date('Y-m-d H:i:s') );
            $statement->bindValue(":fullPrice", $itemDetails->getFullPrice() );
            $statement->execute();

            $statement = $db->prepare( "UPDATE Information SET SiteLoss = SiteLoss + :missingMoney where ItemType = :itemType" );

            $statement->bindValue(":missingMoney", $itemDetails->getRetailPrice() );
            $statement->bindValue(":itemType", $itemType );

            $statement->execute();
        }

        $db->exec( "COMMIT;" );

        return "Defectives successfully.";
    }
}
/**
 * @param $db SQLite3
 * @param $username
 * @param $password
 * @param $passwordAgain
 * @param $firstName
 * @param $lastName
 * @param $phoneNumber
 * @return string
 */
function registerUser( $db, $isTest, $username, $password, $passwordAgain, $firstName, $lastName, $phoneNumber ) {
    if( $username == "" || $password == "" || $passwordAgain == "" || $firstName == "" || $lastName == "" ) {
        return "You must provide User Name, Password, First Name, and Last Name.";
    } else if( $password != $passwordAgain ) {
        return "Passwords did not match.";
    } else {
        $passwordSHA1 = sha1( $password );
        $date = date('Y-m-d H:i:s');

        $statement = $db->prepare("SELECT * FROM User WHERE UserName = :username");
        $statement->bindValue( ":username", $username );
        $results = $statement->execute();

        $userExists = $results->fetchArray() != false;

        if( $userExists ) {
            return "User <b>$username</b> already exists!";
        } else {
            $statement = $db->prepare( "INSERT INTO User (UserName, Password, FirstName, LastName, PhoneNumber, DateCreated, SodaBalance, SnackBalance, SodaSavings, " .
                "SnackSavings, AnonName, IsCoop, Inactive, Credits, ShowDiscontinued, ShowCashOnly, ShowCredit, ShowItemStats, ShowShelf, SubscribeRestocks, ShowTrending) VALUES " .
                "( :username, :passwordSHA1, :firstName, :lastName, :phoneNumber, :date, 0.00, 0.00, 0.00, 0.00, 'Penguin', 1, 0, 0.00, 0, 0, 0, 1, 0, 0, 1)" );
            $statement->bindValue(":username", $username );
            $statement->bindValue(":passwordSHA1", $passwordSHA1 );
            $statement->bindValue(":firstName", $firstName );
            $statement->bindValue(":lastName", $lastName );
            $statement->bindValue(":phoneNumber", $phoneNumber );
            $statement->bindValue(":date", $date );
            $statement->execute();

            if( !$isTest ) {
                sendSlackMessageToMatt("*User Name:* " . $username . "\n*Name:* " . $firstName . " " . $lastName, ":busts_in_silhouette:", "NEW USER", "#b7ab1a");
            }
            return "Registration complete! User <b>$username</b> has been created.";
        }
    }
}

function getSitePurchasePrice( $discountPrice, $price ) {
    if( $discountPrice == 0 || $discountPrice == "" ) {
        return $price;
    } else {
        return $discountPrice;
    }
}

/**
 * @param $db SQLite3
 * @param $currentAuditID
 * @return AuditItem
 */
function getAuditDetails( $db, $currentAuditID, $itemType ) {
    $statementPreviousAudit = $db->prepare("SELECT AuditID, Date from Audit WHERE AuditID < :currentAuditID and ItemType =:itemType ORDER BY AuditID DESC" );
    $statementPreviousAudit->bindValue( ":currentAuditID", $currentAuditID );
    $statementPreviousAudit->bindValue( ":itemType", $itemType );
    $resultsPreviousAudit = $statementPreviousAudit->execute();

    $previousAuditArray = $resultsPreviousAudit->fetchArray();
    $previousAuditID = $previousAuditArray['AuditID'];
    $previousAuditDate = $previousAuditArray['Date'];

    error_log("Previous Audit[$previousAuditID] [$previousAuditDate]" );
    if( $previousAuditID == "" ) {
        // No previous audit to compare to
        return null;
    }
    $breakdownTable = "";
    $totalIncomeForAudit = 0.0;
    $totalProfitForAudit = 0.0;

//    $shelfBeforeSQL = "SELECT before.ShelfQuantity as BeforeQuantity, before.Date, beforeDetails.Price as BeforePrice, " .
//        "after.ShelfQuantity as AfterQuantity, after.Date, after.AuditID, " .
//        "item.DiscountPrice, item.Price, item.id as ItemID, item.price as AfterPrice, item.Name, " .
//        "(select count(*) from Purchase_History p where p.Date > before.Date and p.ItemID = item.ID AND p.Date < after.Date) SitePurchases, " .
//        "(SELECT sum(d.ShelfQuantity-d.ShelfQuantityBefore) from Inventory_History d WHERE d.ShelfQuantity > d.ShelfQuantityBefore AND d.ItemID = item.ID AND d.Date > before.Date AND d.Date < after.Date) Refills " .
//        "FROM Inventory_History before " .
//        "JOIN Item_Details beforeDetails on before.ItemDetailsID = beforeDetails.ItemDetailsID " .
//        "JOIN Item_Details afterDetails on after.ItemDetailsID = afterDetails.ItemDetailsID " .
//        "JOIN Inventory_History after ON before.ItemID = after.ItemID AND after.AuditID = $currentAuditID " .
//        "JOIN Item item on before.ItemID = item.ID " .
//        "WHERE before.AuditID = $previousAuditID ORDER BY before.ItemID DESC";

     $shelfBeforeSQL = "SELECT inv.ItemID, item.Name, details.Price, details.RetailPrice, COUNT(inv.ItemID) as Count, SUM(details.Price) as 'Total_Price', SUM(details.RetailPrice) as 'Total_Retail' " .
         "FROM Inventory_History inv " .
         "INNER JOIN Item_Details details ON inv.ItemDetailsID = details.ItemDetailsID " .
         "INNER JOIN Item item ON inv.ItemID = item.ID " .
         "WHERE inv.ID > (SELECT MAX(ID) FROM Inventory_History WHERE AuditID = :previousAuditID) " .
         "AND inv.ID <= (SELECT MAX(ID) FROM Inventory_History WHERE AuditID = :currentAuditID) " .
         "AND inv.InventoryType = 'MANUAL PURCHASE' ".
         "AND inv.ItemDetailsID IS NOT NULL " .
         "GROUP BY inv.ItemID, details.Price, details.RetailPrice " .
         "ORDER BY inv.ItemID;";

    $statementShelfBefore = $db->prepare( $shelfBeforeSQL );
    $statementShelfBefore->bindValue( ":previousAuditID", $previousAuditID );
    $statementShelfBefore->bindValue( ":currentAuditID", $currentAuditID );
    $resultsShelfBefore = $statementShelfBefore->execute();

    $breakdownTable .= "<div class='rounded_inner_table'>";
//    $breakdownTable .= "[$shelfBeforeSQL]";
    $breakdownTable .= "<table>";
    $breakdownTable .= "<thead><tr>";
    $breakdownTable .= "<th>Item ID</th><th>Item Name</th><th>Total Site Purchases</th><th>Full Price</th><th>Retail Price</th><th>Total Income</th><th>Total Retail Cost</th><th>Profit</th>";
    $breakdownTable .= "</tr>";

    while ($rowShelfBefore = $resultsShelfBefore->fetchArray()) {
        $totalIncome = $rowShelfBefore['Total_Price'];
        $totalRetail = $rowShelfBefore['Total_Retail'];
        $totalCount = $rowShelfBefore['Count'];
        $itemName = $rowShelfBefore['Name'];
        $itemID = $rowShelfBefore['ItemID'];
        $itemPrice = $rowShelfBefore['Price'];
        $itemRetailPrice = $rowShelfBefore['RetailPrice'];

        $totalProfit = $totalIncome - $totalRetail;

        $rowStyle = "style='background-color: #ffb2b2; color:#000000;'";

        if( $totalProfit > 0 ) {
            $rowStyle = "style='background-color: #b2ffdb; color:#000000;'";
        }
        $breakdownTable .= "<tr $rowStyle'>";
        $breakdownTable .= "<td>$itemID</td>";
        $breakdownTable .= "<td><b>$itemName</b></td>";
        $breakdownTable .= "<td>$totalCount</td>";
        $breakdownTable .= "<td>" . getPriceDisplayWithDollars($itemPrice) . "</td>";
        $breakdownTable .= "<td>" . getPriceDisplayWithDollars($itemRetailPrice) . "</td>";
        $breakdownTable .= "<td>" . getPriceDisplayWithDollars($totalIncome) . "</td>";
        $breakdownTable .= "<td>" . getPriceDisplayWithDollars($totalRetail) . "</td>";
        $breakdownTable .= "<td>" . getPriceDisplayWithDollars($totalProfit) . "</td>";
        $breakdownTable .= "</tr>";

        $totalIncomeForAudit += $totalIncome;
        $totalProfitForAudit += $totalProfit;
    }
    $breakdownTable .= "</table>";

    return new AuditItem( $breakdownTable, $totalIncomeForAudit, $totalProfitForAudit, $previousAuditID, $previousAuditDate );
}
?>