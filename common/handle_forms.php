<?php
include(__DIR__ . "/../appendix.php" );
$db = getDB();



include( SESSION_FUNCTIONS_PATH );
include(UI_FUNCTIONS_PATH);
include(QUANTITY_FUNCTIONS_PATH);
include(SLACK_FUNCTIONS_PATH);
include_once(LOG_FUNCTIONS_PATH);
include_once(ACTION_FUNCTIONS_PATH);
date_default_timezone_set('America/New_York');

Login($db);

$userMessage = "";

$startTime = time();

// Determine pemissions
define( "USER_PERMISSION", "User" );
define( "VENDOR_PERMISSION", "Vendor" );
define( "ADMIN_PERMISSION", "Admin" );

$permissionsRequired = array();

if(isset($_POST['Purchase'])) {
    $permissionsRequired[] = USER_PERMISSION;
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if(isset($_POST['Preferences'])) {
    $permissionsRequired[] = USER_PERMISSION;
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if(isset($_POST['Request'])) {
    $permissionsRequired[] = USER_PERMISSION;
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if(isset($_POST['Shopping'])) {
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if(isset($_POST['RegisterUser'])) {
    $permissionsRequired[] = USER_PERMISSION;
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['AddItem'])) {
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['EditItem'])) {
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['Restock'])) {
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['Inventory'])) {
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['Refill'])) {
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['SendBot'])) {
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['EditUser'])) {
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['CreditUser'])) {
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['Defective'])) {
    $permissionsRequired[] = VENDOR_PERMISSION;
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['Payment'])) {
    $permissionsRequired[] = ADMIN_PERMISSION;
} else if (isset($_POST['KillSession'])) {
    $permissionsRequired[] = ADMIN_PERMISSION;
}



$hasPermission = false;

if( IsAdminLoggedIn() ) {
    if( in_array( ADMIN_PERMISSION, $permissionsRequired ) ){
        $hasPermission = true;
    }
} else if( IsVendor() ) {
    if( in_array( VENDOR_PERMISSION, $permissionsRequired ) ) {
        $hasPermission = true;
    }
} else if( in_array( USER_PERMISSION, $permissionsRequired ) ) {
    $hasPermission = true;
}

if( !$hasPermission ) {
    $userMessage = "You do have permission to do this! Shame on you!";
} else {

    // ------------------------------------
    // HANDLE USER QUERIES
    // ------------------------------------
    if(isset($_POST['Purchase'])) {
        $itemsInCart = json_decode($_POST['items']);
    $cashOnly = isset( $_POST['CashOnly'] );
    $userMessage = purchaseItems( $db, false, $_SESSION['UserID'], $itemsInCart, $cashOnly );

    } else if(isset($_POST['Preferences'])) {
        $userID = $_SESSION['UserID'];
        $anonAnimal = strip_tags( trim( $_POST['Preferences_AnonAnimal'] ) );
        $showDiscontinued = 0;
        $showCashOnly = 0;
        $showCredit = 0;
        $showShelf = 0;
        $showItemStats = 0;
        $subscribeRestocks = 0;
        $showTrending = 0;

        if( isset($_POST["Preferences_ShowDiscontinued"]) ) {
            $showDiscontinued = 1;
        }

        if( isset($_POST["Preferences_ShowCashOnly"]) ) {
            $showCashOnly = 1;
        }

        if( isset($_POST["Preferences_ShowCredit"]) ) {
            $showCredit = 1;
        }

        if( isset($_POST["Preferences_ShowShelf"]) ) {
            $showShelf = 1;
        }

        if( isset($_POST["Preferences_ShowItemStats"]) ) {
            $showItemStats = 1;
        }

        if( isset($_POST["Preferences_SubscribeRestocks"]) ) {
            $subscribeRestocks = 1;
        }

        if( isset($_POST["Preferences_ShowTrending"]) ) {
            $showTrending = 1;
        }

        $statement = $db->prepare("SELECT count(*) as Total FROM User WHERE AnonName = :anonAnimal AND UserID != :userID" );
        $statement->bindValue( ":anonAnimal", $anonAnimal );
        $statement->bindValue( ":userID", $userID );
        $results = $statement->execute();

        $row = $results->fetchArray();
        $total = $row['Total'];

        $anonNameUpdate = "";
        if( $total > 0 ) {
            $userMessage = "The Anonymous Animal '$anonAnimal' is already being used by another user.";
        } else {
            $anonNameUpdate = ", AnonName = :anonAnimal ";
            $_SESSION['AnonName'] = $anonAnimal;
            $userMessage = "User Preferences saved.";
        }

        $statement = $db->prepare( "UPDATE User SET ShowDiscontinued=:showDiscontinued, ShowCashOnly=:showCashOnly, ShowCredit=:showCredit, ShowItemStats=:showItemStats, " .
            "ShowShelf=:showShelf, SubscribeRestocks=:subscribeRestocks, ShowTrending=:showTrending $anonNameUpdate where UserID = :userID" );
        $statement->bindValue( ":showDiscontinued", $showDiscontinued );
        $statement->bindValue( ":showCashOnly", $showCashOnly );
        $statement->bindValue( ":showCredit", $showCredit );
        $statement->bindValue( ":showItemStats", $showItemStats );
        $statement->bindValue( ":showShelf", $showShelf );
        $statement->bindValue( ":subscribeRestocks", $subscribeRestocks );
        $statement->bindValue( ":showTrending", $showTrending );
        $statement->bindValue( ":anonAnimal", $anonAnimal );
        $statement->bindValue( ":userID", $userID );
        $statement->execute();

        $_SESSION['ShowDiscontinued'] = $showDiscontinued;
        $_SESSION['ShowCashOnly'] = $showCashOnly;
        $_SESSION['ShowCredit'] = $showCredit;
        $_SESSION['ShowItemStats'] = $showItemStats;
        $_SESSION['ShowShelf'] = $showShelf;
        $_SESSION['SubscribeRestocks'] = $subscribeRestocks;
        $_SESSION['ShowTrending'] = $showTrending;

    } else if(isset($_POST['Request'])) {
        $itemType = trim($_POST["ItemTypeDropdown_Request"]);
        $date = date('Y-m-d H:i:s');
        $itemName = $db->escapeString(trim($_POST["ItemName_Request"]));
        $note = "";

        if( $itemName == "" ) {
            $userMessage = "Your request cannot be blank.";
        } else {

            if (isset($_POST["Note_Request"])) {
                $note = $db->escapeString(trim($_POST["Note_Request"]));
            }

            $userID = $_SESSION['UserID'];
            $username = $_SESSION['FirstName'] . " " . $_SESSION['LastName'];
            $slackID = $_SESSION['SlackID'];

            sendSlackMessageToUser($slackID, "*Item Name:* " . $itemName . "\n*Notes:* " . $note, ":ballot_box_with_ballot:", "REQUEST RECEIVED", "#863fbb", $_SESSION['UserName'], true);

            $statement = $db->prepare("INSERT INTO Requests (UserID, ItemName, Date, Note, ItemType, Priority) VALUES(:userID, :itemName, :date, :note, :itemType, '')");
            $statement->bindValue(":userID", $userID);
            $statement->bindValue(":itemName", $itemName);
            $statement->bindValue(":date", $date);
            $statement->bindValue(":note", $note);
            $statement->bindValue(":itemType", $itemType);
            $statement->execute();

            $userMessage = "Request submitted successfully.";
        }
    } else if(isset($_POST['Shopping'])) {
        $itemID = trim($_POST["ItemDropdown"]);
        $store = trim($_POST["StoreDropdown"]);
        $date = date('Y-m-d H:i:s');
        $packQuantity = trim($_POST["PackQuantity"]);
        $price = convertDecimalToWholeCents( trim($_POST["Price"]) );
        $priceType = trim($_POST["PriceType"]);
        $submitter = trim($_POST["Submitter"]);

        $regularPrice = "null";
        $salePrice = "null";


        if( $priceType == "sale" ) {
            $salePrice = $price;
        } else {
            $regularPrice = $price;
        }

        if( $store == "BestProfits" ) {
            $store = null;
            $regularPrice = "null";
            $salePrice = "null";
        }

        $statement = $db->prepare( "INSERT INTO Shopping_Guide (ItemID, PackQuantity, RegularPrice, SalePrice, Store, User, Date) VALUES " .
            "(:itemID, :packQuantity, :regularPrice, :salePrice, :store, :submitter, :date)" );

        $statement->bindValue( ":itemID", $itemID );
        $statement->bindValue( ":packQuantity", $packQuantity );
        $statement->bindValue( ":regularPrice", $regularPrice );
        $statement->bindValue( ":salePrice", $salePrice );
        $statement->bindValue( ":store", $store );
        $statement->bindValue( ":submitter", $submitter );
        $statement->bindValue( ":date", $date );
        $statement->execute();

    } else if(isset($_POST['RegisterUser'])) {
        $username = $db->escapeString(trim($_POST["UserName"]));
        $password = $_POST["Password"];
        $passwordAgain = $_POST["PasswordAgain"];
        $firstName = $db->escapeString($_POST["FirstName"]);
        $lastName = $db->escapeString($_POST["LastName"]);
        $phoneNumber = "";

        if (isset($_POST['PhoneNumber'])) {
            $phoneNumber = $db->escapeString($_POST["PhoneNumber"]);
        }

        $userMessage = registerUser($db, false, $username, $password, $passwordAgain, $firstName, $lastName, $phoneNumber);


        // ------------------------------------
        // HANDLE VENDOR/ADMIN QUERIES
        // ------------------------------------
    } else if(isset($_POST['KillSession'])) {
        $sessionID = $_POST['SessionID'];

        $sessionLocation = session_save_path();

        if( $sessionLocation == "" ) {
            $sessionLocation = sys_get_temp_dir();
        }

        $filepath = $sessionLocation . "/"  . $sessionID;
        unlink( $filepath );
        $userMessage = "Killed session at $filepath.";
    } else if (isset($_POST['AddItem'])) {
        $userMessage = addItem($db, $_POST["ItemName"], $_POST["CurrentPrice"], $_POST["CurrentDiscountPrice"], $_POST["ItemType"]);
    } else if (isset($_POST['EditItem'])) {
        $itemType = trim($_POST["ItemType"]);

        $itemID = trim($_POST["Edit" . $itemType . "Dropdown"]);
        $name = trim($_POST["EditItemName" . $itemType]);
        $price = convertDecimalToWholeCents(trim($_POST["EditPrice" . $itemType]));
        $discountPrice = convertDecimalToWholeCents(trim($_POST["EditDiscountPrice" . $itemType]));
        $unitName = trim($_POST["EditUnitName" . $itemType]);
        $unitNamePlural = trim($_POST["EditUnitNamePlural" . $itemType]);
        $tag = trim($_POST["EditTag" . $itemType]);
        $alias = trim($_POST["EditAlias" . $itemType]);
        $currentFlavor = trim($_POST["EditCurrentFlavor" . $itemType]);
        $status = trim($_POST["EditStatus" . $itemType]);
        $expirationDate = trim($_POST["EditExpirationDate" . $itemType]);

        $userMessage = editItem($db, $itemID, $name, $price, $discountPrice, $unitName, $unitNamePlural, $alias, $currentFlavor, $status, $expirationDate, $tag);
    } else if (isset($_POST['Restock'])) {
        $itemID = trim($_POST["RestockDropdown"]);
        $itemType = trim($_POST["ItemType"]);
        $quantity = trim($_POST["NumberOfCans"]);
        $expDate = trim($_POST["ExpDate"]);
        $retailCost = $_POST["Cost"];
        $multiplier = trim($_POST["Multiplier"]);

        $userMessage = restockItem($db, $itemID, $quantity, $multiplier, $retailCost, $itemType, $expDate);
    } else if (isset($_POST['Inventory'])) {
        $itemID_all = $_POST["ItemID"];
        $removeFromShelf_all = $_POST["ShelfQuantity"];
        $auditAmount = $_POST["AuditAmount"];
        $itemType = $_POST["ItemType"];

        $userMessage = inventoryItem($db, $itemID_all, $removeFromShelf_all, $auditAmount, $itemType);

    } else if (isset($_POST['Refill'])) {
        $itemID_all = $_POST["RefillItemID"];
        $refiller = $_POST["Refiller"];
        $sendToSlack = false;

        if (isset($_POST['SendToSlack']) && $_POST['SendToSlack'] == 'on') {
            $sendToSlack = true;
        }

        $addToShelf_all = $_POST["RefillAddToShelf"];
        $itemType = $_POST["ItemType"];

        $userMessage = refillItem($db, false, $itemID_all, $addToShelf_all, $sendToSlack, $refiller);
    } else if (isset($_POST['SendBot'])) {
        $botMessage = trim($_POST["BotMessage"]);
        $emoji = trim($_POST["Emoji"]);
        $botName = trim($_POST["BotName"]);
        $emoji = str_replace(":", "", $emoji);

        $_SESSION['BotName'] = $botName;
        $_SESSION['Emoji'] = $emoji;
        sendSlackMessageToNerdHerd($botMessage, ":$emoji:", $botName);
    } else if (isset($_POST['EditUser'])) {
        $userID = trim($_POST["EditUserDropdown"]);
        $slackID = trim($_POST["SlackID"]);
        $anonName = trim($_POST["AnonName"]);

        $inactive = 0;
        $resetPassword = false;
        $isCoop = 0;
        $isVendor = 0;

        if (isset($_POST["Inactive"])) {
            $inactive = 1;
        }

        if (isset($_POST["IsCoop"])) {
            $isCoop = 1;
        }

        if (isset($_POST["IsVendor"])) {
            $isVendor = 1;
        }

        if (isset($_POST["ResetPassword"])) {
            $resetPassword = true;
        }

        $userMessage = editUser($db, $userID, $slackID, $anonName, $inactive, $resetPassword, $isCoop, $isVendor);
    } else if (isset($_POST['CreditUser'])) {
        $userID = trim($_POST["EditUserDropdown"]);
        $creditAmountInDecimal = trim($_POST["CreditAmount"]);
        $returnCredits = isset($_POST["ReturnCredits"]);

        $userMessage = creditUser($db, false, $userID, $creditAmountInDecimal, $returnCredits);
    } else if (isset($_POST['Defective'])) {
        $itemID = trim($_POST["DefectiveDropdown"]);
        $itemType = trim($_POST["ItemType"]);
        $quantity = trim($_POST["NumberOfUnits"]);
        $userMessage = defectItem($db, $itemID, $quantity);

    } else if (isset($_POST['Payment'])) {
        $userID = trim($_POST["UserID"]);
        $paymentMonth = trim($_POST["Month"]);
        $snackAmount = convertDecimalToWholeCents(trim($_POST["SnackAmount"]));
        $sodaAmount = convertDecimalToWholeCents(trim($_POST["SodaAmount"]));
        $note = trim($_POST["Note"]);
        $method = trim($_POST["MethodDropdown"]);
        $sodaCommission = trim($_POST["SodaCommission"]);
        $snackCommission = trim($_POST["SnackCommission"]);

        if( $sodaCommission > 0 || $snackCommission > 0 ) {
            $userMessage = makeCommission($db, $userID, $paymentMonth, $sodaAmount, $snackAmount, $note, $method, $sodaCommission, $snackCommission);
        } else {
            $userMessage = makePayment($db, $userID, $paymentMonth, $sodaAmount, $snackAmount, $note, $method );
        }


        // WHEN DOING FULL ADUIT THE PROCESS SHOULD BE...
        // COUNT BACKSTOCK, REFILL WHATEVER IS MISSING
        // COUNT SHELF, INVENTORY WHAT IS MISSING
        // SHELF AND BACKSTOCK SHOULD BE READONLY

        // INVENTORY MEANS WE ARE SUBTRACTING STUFF - REMOVING FROM ITEMS_IN_STOCK
        // TURNS INTO A COUNT IN THE INVENTORY_HISTORY - STORE THE LARGEST RETAIL COST SINCE WE'RE GROUPING
        // IF WE HAVE TO UNDO WE'LL LOSE THOSE CHEAPER RETAIL COSTS WHEN WE PUT BACK INTO ITEMS_IN_STOCK BUT THATS YOUR PROBLEM

        // REFILL MEANS WERE FLIPPING THE BACKSTOCK BIT ON X NUMBER OF ITEMS - NOTHING ELSE, NO SUBTRACTION OR ADDING

        // ADDING IS FOR RESTOCK
    }
}

$stopTime = time();
$totalTime = $stopTime - $startTime;
log_benchmark( "Time to complete handle_forms: $totalTime seconds" );

if( isset( $_POST['redirectURL'] ) ) {

    if( $userMessage != "" ) {
        $_SESSION['UserMessage'] = $userMessage;
    }

    // Redirect to page
    header( "Location:" . $_POST['redirectURL'] );
}
?>