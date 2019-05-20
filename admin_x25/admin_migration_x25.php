<?php

/*
 * DB UPDATES
 * Apr 7, 2019
 * Audit Table
 * AuditID - Payments, Daily_Amount
 * Rename OutOfStock to RefillTrigger - Item
 * Mar 17, 2019
 * Item - Add CurrentFlavor TEXT
 * Feb 24, 2019
 * Visits - Add Page TEXT
 * Jan 27, 2019
 * Requests - Add Priority TEXT
 * Requests - Add DateCompleted TEXT
 * 
 * Jan 19, 2019
 * User - Add IsCoop INTEGER
 * User - Add AnonName TEXT
 */
    include(__DIR__ . "/../appendix.php");
    include(UI_FUNCTIONS_PATH);

    $db = new SQLite3( getDB() );
    if (!$db) die ($error);

    $testMigration = false;


    if( isset( $_GET['version'] ) ) {
        $version = $_GET['version'];

        echo "<h1>Version $version Migration</h1>";

        executeStatement( $db, "CREATE TABLE IF NOT EXISTS Migration (ID INTEGER PRIMARY KEY AUTOINCREMENT, MigrationMark TEXT, Date TEXT )" );

        $migrationResults = queryStatement($db, "SELECT MigrationMark from Migration where MigrationMark = '$version';" );
        $migrationFound = $migrationResults->fetchArray()['MigrationMark'];

        if( $testMigration && $migrationFound != "" ) {
            echo "Migration Mark [$version] already exists!";
        } else {
            ini_set('max_execution_time', 3600);
            $start = time();

            switch ($version) {
                case "6.0":
                    v6_0($db);
                    break;
                case "6.1":
                    v6_1($db);
                    break;
                default:
                    echo "There is no migration for version [$version]!";
                    break;
            }

            $end = time();
            $totalTime = $end - $start;

            echo "<div style='background-color:#ff4c81; padding: 15px; margin: 30px 0px; border: #ff171d solid 4px;'>";
            echo "<div style='padding: 10px; color: #ffcdd7;'>TOTAL TIME ($totalTime seconds)</div>";
            echo "</div>";

            $date = date('Y-m-d H:i:s', time());
            executeStatement( $db, "INSERT INTO Migration ('MigrationMark', 'Date') VALUES ('$version', '$date')" );
        }

    } else {
        echo " Welcome to migration page. You need to select a version!";
    }

    function v6_1( $db )
    {
        executeStatement($db, "ALTER TABLE ITEM ADD COLUMN IsBought INTEGER;");
        executeStatement($db, "UPDATE ITEM Set RestockTrigger = 1 WHERE BackstockQuantity < 3;");
    }

    function v6_0( $db ) {
        executeStatement( $db, "ALTER TABLE USER ADD COLUMN Credits REAL;" );
        executeStatement( $db, "ALTER TABLE PURCHASE_HISTORY ADD COLUMN UseCredits INTEGER;" );
        executeStatement( $db, "UPDATE USER SET Credits = 0.0;" );
//
        convertDollarsToWholeCents( $db, "Item", "ID", "Price", "TotalIncome", "TotalExpenses", "DiscountPrice" );
        convertDollarsToWholeCents( $db, "Defectives", "Date", "Price" );
        convertDollarsToWholeCents( $db, "Payments", "PaymentID", "Amount" );
        convertDollarsToWholeCents( $db, "Shopping_Guide", "Date", "RegularPrice", "SalePrice" );
        convertDollarsToWholeCents( $db, "Information", "ItemType", "ProfitExpected", "ProfitActual", "Income" , "Expenses", "MoneyLost" );
        convertDollarsToWholeCents( $db, "Purchase_History", "ID", "Cost", "DiscountCost" );
        convertDollarsToWholeCents( $db, "User", "UserID", "SodaBalance", "SnackBalance", "SodaSavings", "SnackSavings" );
        convertDollarsToWholeCents( $db, "Daily_Amount", "ID", "Price" );
        convertDollarsToWholeCents( $db, "Restock", "RestockID", "Cost" );
        convertDollarsToWholeCents( $db, "Audit", "AuditID", "MissingMoney" );
    }

    function executeStatement( $db, $statement ) {
        executeStatementWithPrint( $db, $statement, true );
    }

    function executeStatementWithPrint( $db, $statement, $printNow ) {
        if( $printNow ) {
            echo "<div style='background-color:#55a4ff; padding: 15px; margin: 30px 0px; border: #424fff solid 4px;'>";
            echo "<div style='padding: 10px; color: #c2f9ff;'>Executing [$statement]</div>";
            $start = time();
        }

        $db->exec( $statement );

        if( $printNow ) {
            $end = time();
            $totalTime = $end - $start;
            echo "<div style='padding: 10px; color: #c2f9ff;'>Done! ($totalTime seconds)</div>";
            echo "</div>";
        }
    }

    function printBox( $contents ) {
        echo "<div style='background-color:#55a4ff; padding: 15px; margin: 30px 0px; border: #424fff solid 4px;'>";
        echo $contents;
        echo "</div>";
    }

    function queryStatement( $db, $statement ) {
        $start = time();
        $results = $db->query( $statement );
        $end = time();
        $totalTime = $end - $start;
        printBox( "<div style='padding: 10px; color: #c2f9ff;'>Querying [$statement]</div> <div style='padding: 10px; color: #c2f9ff;'>Done! ($totalTime seconds)</div>" );
        return $results;
    }

    function convertDollarsToWholeCents( $db, $tableName, $primaryID, ...$columnNames ) {
        $columnList = join(",", $columnNames );
        $tableResults = queryStatement($db, "SELECT $primaryID, $columnList from $tableName" );

        $totalExecutesForTable = "";
        while ($tableRow = $tableResults->fetchArray()) {
            $id = $tableRow[$primaryID];

            $finalUpdate = "";
            foreach($columnNames as $columnName ) {
                $valueInWholeCents = convertDecimalToWholeCents( $tableRow[$columnName] );

                $finalUpdate .= "$columnName = $valueInWholeCents,";
            }

            $finalUpdate = substr($finalUpdate, 0, -1);

            $updateTableRowSQL = "UPDATE $tableName Set $finalUpdate WHERE $primaryID = '$id'";
//            echo "[$updateTableRowSQL]<br>";

            executeStatement( $db, $updateTableRowSQL );
//            executeStatementWithPrint( $db, $updateTableRowSQL, false );

//            $totalExecutesForTable .= $updateTableRowSQL . "<br>";
        }

//        printBox( $totalExecutesForTable );
    }

    function operationCleanSlate($db) {
        $totalFutureIncomeSoda = fixItemIncomes($db, "Soda");
        echo "TOTAL FUTURE INCOMES - SODA:[$totalFutureIncomeSoda]<br>";
        $totalFutureIncomeSnack = fixItemIncomes($db, "Snack");
        echo "TOTAL FUTURE INCOMES - SNACK:[$totalFutureIncomeSnack]<br>";
        
        $sodaExpenses = 0;
        $snackExpenses = 0;
        
        $resultsInfo = $db->query("SELECT * from Information");
        while ($rowInfo = $resultsInfo->fetchArray()) {
            if( $rowInfo['ItemType'] == "Snack" ) {
                $snackExpenses = $rowInfo['Expenses'];
            } else {
                $sodaExpenses = $rowInfo['Expenses'];
            }
        }
        $newTotalIncomeSoda = $sodaExpenses - $totalFutureIncomeSoda;
        $newTotalIncomeSnack = $snackExpenses - $totalFutureIncomeSnack;
        
        echo "<pre>Soda Expenses [$sodaExpenses]\t\t\t\tPotential Soda Income [$totalFutureIncomeSoda]\t\t\t\tPotential Soda Income [$newTotalIncomeSoda]</pre>";
        echo "<pre>Snack Expenses [$snackExpenses]\t\t\t\tPotential Snack Income [$totalFutureIncomeSnack]\t\t\t\tPotential Snack Income [$newTotalIncomeSnack]</pre>";
        
        $totalSodaBalance = 0;
        $totalSnackBalance = 0;
        
        $resultsUser = $db->query('SELECT u.UserID, u.UserName, u.AnonName, u.SlackID, u.FirstName, u.LastName, u.PhoneNumber, u.SodaBalance, u.SnackBalance, u.DateCreated, u.InActive, u.IsCoop FROM User u ORDER BY u.Inactive asc, u.IsCoop, lower(u.FirstName) ASC');
        while ($rowUser = $resultsUser->fetchArray()) {
            $userName = $rowUser['UserName'];
            $sodaBalance = $rowUser['SodaBalance'];
            $snackBalance = $rowUser['SnackBalance'];
         
            $totalSodaBalance += $sodaBalance;
            $totalSnackBalance += $snackBalance;
            
            echo "<pre>User [$userName]\t\t\t\tSoda: [$sodaBalance]\t\t\t\tSnack: [$snackBalance]</pre>";
        }

        $newPaymentSoda = $newTotalIncomeSoda - $totalSodaBalance;
        $newPaymentSnack = $newTotalIncomeSnack - $totalSnackBalance;
        
        echo "<pre>New Income Soda [$newTotalIncomeSoda]\t\t\t\tPending Soda[$totalSodaBalance]\t\t\t\tNew Payment [$newPaymentSoda]</pre>";
        echo "<pre>New Income Snack [$newTotalIncomeSnack]\t\t\t\tPending Snack[$totalSnackBalance]\t\t\t\tNew Payment [$newPaymentSnack]</pre>";
            
        $db->exec("UPDATE INFORMATION Set Income = $newTotalIncomeSoda WHERE ItemType = 'Soda'");
        $db->exec("UPDATE INFORMATION Set Income = $newTotalIncomeSnack WHERE ItemType = 'Snack'");
        
        $db->exec("UPDATE INFORMATION Set ProfitActual = $newPaymentSoda WHERE ItemType = 'Soda'");
        $db->exec("UPDATE INFORMATION Set ProfitActual = $newPaymentSnack WHERE ItemType = 'Snack'");
        // Chicken Noodle, Cereal, Chewy = 0;
        // Expenses - Marshmellow - 3.20
        
    }
    
    function fixItemIncomes($db, $type) {
        $totalFutureIncome = 0;
        $resultsItem = $db->query("SELECT * from Item WHERE Type = '$type'");
        while ($rowItem = $resultsItem->fetchArray()) {
            $id = $rowItem['ID'];
            $totalExpenses = $rowItem['TotalExpenses'];
            $shelfQuantity = $rowItem['ShelfQuantity'];
            $backstockQuantity = $rowItem['BackstockQuantity'];
            $itemName = $rowItem['Name'];
            $price = $rowItem['Price'];
            $totalItemsUnsold = $shelfQuantity + $backstockQuantity;
            
            $potentalIncome = $totalItemsUnsold * $price;
            $newIncome = $totalExpenses - $potentalIncome;
            
            $totalFutureIncome += $potentalIncome;
            
            $db->exec("UPDATE ITEM Set TotalIncome = $newIncome WHERE ID = $id");
            
            echo "<pre>Item [$itemName]\t\t\t\tExpenses: [$totalExpenses]\t\t\t\tTotal Unsold [$totalItemsUnsold]\t\t\t\tPrice [$price]\t\t\t\tPotential Income [$potentalIncome]\t\t\t\tNew Income: [$newIncome]</pre><br>";
        }
        
        return $totalFutureIncome;
    }
    
    function fixPurchaseHistoryLink($db) {
        $results = $db->query("SELECT * from Purchase_history WHERE DailyAmountID IS NULL ORDER BY Date DESC");
        while ($row = $results->fetchArray()) {
            $date = $row['Date'];
            $itemID = $row['ItemID'];
            $purchaseID = $row['ID'];
    
            $resultsInner = $db->query("SELECT * from Daily_Amount WHERE Date = '$date' AND ItemID = $itemID AND PurchaseID = -1 ORDER BY Date DESC LIMIT 1");
            $rowInner = $resultsInner->fetchArray();
            $dailyAmountID = $rowInner['ID'];
    
            echo "SELECT * from Daily_Amount WHERE Date = '$date' AND ItemID = $itemID AND PurchaseID = -1 ORDER BY Date DESC LIMIT 1<br>";
    
            echo "UPDATE Daily_Amount SET PurchaseID = $purchaseID WHERE ID = $dailyAmountID<br>";
            echo "UPDATE Purchase_History SET DailyAmountID = $dailyAmountID WHERE ID = $purchaseID<br>";
    
            $db->exec( "UPDATE Daily_Amount SET PurchaseID = $purchaseID WHERE ID = $dailyAmountID" );
            $db->exec( "UPDATE Purchase_History SET DailyAmountID = $dailyAmountID WHERE ID = $purchaseID" );
        }
    }
    function fixIncomes($db) {
        
        echo "FIX INCOMES<br><br>";
        
        $dailyAmountArray = array();
        $dailyAmountArray[]= array(100, '2018-07-05 10:37:30',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-05 10:37:30',5, 0, 0, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-05 10:37:30',0, 4, 4, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-05 10:37:30',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-05 10:37:30',0, 12, 12, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-05 10:37:30',0, 12, 12, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-05 10:37:30',0, 14, 14, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-05 10:37:30',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-05 10:37:30',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-05 10:37:30',0, 15, 15, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-05 10:37:30',0, 16, 16, 0.25, 0);
        $dailyAmountArray[]= array(131, '2018-07-05 10:37:30',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-05 10:37:30',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(67, '2018-07-05 10:37:30',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-05 10:37:30',0, 6, 6, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-05 10:37:30',0, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-05 10:37:30',0, 16, 16, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-05 10:37:30',0, 12, 12, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-05 10:37:30',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-05 10:37:30',0, 10, 10, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-05 10:37:30',14, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-05 10:37:30',12, 0, 6, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-05 10:37:30',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-05 10:37:30',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-05 10:37:30',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-05 10:37:30',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-05 10:37:30',0, 3, 3, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-05 10:37:30',0, 45, 45, 0.2, 0);
        $dailyAmountArray[]= array(119, '2018-07-05 10:37:30',64, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(113, '2018-07-05 10:37:30',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(115, '2018-07-05 10:37:30',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-05 10:37:30',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-05 10:37:30',0, 7, 7, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-05 10:37:30',10, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(100, '2018-07-09 16:42:55',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-09 16:42:55',0, 0, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-09 16:42:55',0, 3, 3, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-09 16:42:55',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-09 16:42:55',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-09 16:42:55',16, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-09 16:42:55',0, 14, 14, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-09 16:42:55',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-09 16:42:55',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-09 16:42:55',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-09 16:42:55',0, 15, 15, 0.25, 0);
        $dailyAmountArray[]= array(131, '2018-07-09 16:42:55',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-09 16:42:55',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(67, '2018-07-09 16:42:55',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-09 16:42:55',0, 6, 6, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-09 16:42:55',0, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-09 16:42:55',10, 16, 16, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-09 16:42:55',0, 11, 11, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-09 16:42:55',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-09 16:42:55',0, 9, 9, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-09 16:42:55',14, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-09 16:42:55',12, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-09 16:42:55',18, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-09 16:42:55',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-09 16:42:55',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-09 16:42:55',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-09 16:42:55',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-09 16:42:55',0, 3, 3, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-09 16:42:55',0, 42, 42, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-09 16:42:55',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-09 16:42:55',64, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(113, '2018-07-09 16:42:55',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(115, '2018-07-09 16:42:55',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-09 16:42:55',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-09 16:42:55',0, 7, 7, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-09 16:42:55',10, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-09 16:42:55',15, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-16 11:03:46',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-16 11:03:46',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-16 11:03:46',30, 2, 1, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-16 11:03:46',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-16 11:03:46',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-16 11:03:46',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-16 11:03:46',20, 14, 0, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-16 11:03:46',25, 0, 0, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-16 11:03:46',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-16 11:03:46',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-16 11:03:46',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-16 11:03:46',0, 15, 15, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-16 11:03:46',106, 0, 0, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-16 11:03:46',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-16 11:03:46',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-16 11:03:46',15, 0, 0, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-16 11:03:46',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-16 11:03:46',0, 6, 6, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-16 11:03:46',12, 11, 0, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-16 11:03:46',30, 16, 0, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-16 11:03:46',0, 9, 9, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-16 11:03:46',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-16 11:03:46',12, 7, 0, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-16 11:03:46',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-16 11:03:46',12, 4, 0, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-16 11:03:46',18, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-16 11:03:46',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-16 11:03:46',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-16 11:03:46',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-16 11:03:46',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-16 11:03:46',12, 3, 0, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-16 11:03:46',0, 41, 41, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-16 11:03:46',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-16 11:03:46',64, 13, 13, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-16 11:03:46',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-16 11:03:46',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-16 11:03:46',0, 1, 1, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-16 11:03:46',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-16 11:03:46',15, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-16 11:07:16',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-16 11:07:16',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-16 11:07:16',0, 1, 31, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-16 11:07:16',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-16 11:07:16',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-16 11:07:16',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-16 11:07:16',0, 0, 20, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-16 11:07:16',0, 0, 25, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-16 11:07:16',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-16 11:07:16',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-16 11:07:16',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-16 11:07:16',0, 15, 15, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-16 11:07:16',0, 0, 106, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-16 11:07:16',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-16 11:07:16',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-16 11:07:16',0, 0, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-16 11:07:16',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-16 11:07:16',0, 6, 6, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-16 11:07:16',0, 0, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-16 11:07:16',0, 0, 40, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-16 11:07:16',0, 9, 9, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-16 11:07:16',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-16 11:07:16',0, 0, 12, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-16 11:07:16',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-16 11:07:16',6, 0, 6, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-16 11:07:16',12, 0, 6, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-16 11:07:16',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-16 11:07:16',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-16 11:07:16',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-16 11:07:16',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-16 11:07:16',0, 0, 12, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-16 11:07:16',0, 41, 41, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-16 11:07:16',0, 0, 20, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-16 11:07:16',64, 13, 13, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-16 11:07:16',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-16 11:07:16',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-16 11:07:16',0, 1, 0, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-16 11:07:16',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-16 11:07:16',5, 0, 10, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-16 11:11:11',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-16 11:11:11',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-16 11:11:11',0, 31, 31, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-16 11:11:11',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-16 11:11:11',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-16 11:11:11',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-16 11:11:11',0, 20, 20, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-16 11:11:11',0, 25, 25, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-16 11:11:11',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-16 11:11:11',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-16 11:11:11',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-16 11:11:11',0, 15, 15, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-16 11:11:11',0, 106, 106, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-16 11:11:11',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-16 11:11:11',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-16 11:11:11',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-16 11:11:11',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-16 11:11:11',0, 6, 6, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-16 11:11:11',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-16 11:11:11',0, 40, 40, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-16 11:11:11',0, 9, 9, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-16 11:11:11',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-16 11:11:11',0, 12, 11, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-16 11:11:11',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-16 11:11:11',6, 6, 6, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-16 11:11:11',12, 6, 6, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-16 11:11:11',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-16 11:11:11',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-16 11:11:11',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-16 11:11:11',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-16 11:11:11',0, 12, 12, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-16 11:11:11',0, 41, 41, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-16 11:11:11',0, 20, 20, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-16 11:11:11',64, 13, 13, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-16 11:11:11',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-16 11:11:11',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(85, '2018-07-16 11:11:11',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-16 11:11:11',5, 10, 10, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-18 15:11:31',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-18 15:11:31',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-18 15:11:31',0, 29, 29, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-18 15:11:31',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-18 15:11:31',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-18 15:11:31',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-18 15:11:31',0, 19, 19, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-18 15:11:31',0, 23, 23, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-18 15:11:31',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-18 15:11:31',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-18 15:11:31',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-18 15:11:31',0, 14, 14, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-18 15:11:31',0, 101, 101, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-18 15:11:31',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-18 15:11:31',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-18 15:11:31',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-18 15:11:31',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-18 15:11:31',0, 4, 4, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-18 15:11:31',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-18 15:11:31',0, 40, 40, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-18 15:11:31',0, 8, 8, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-18 15:11:31',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-18 15:11:31',0, 11, 11, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-18 15:11:31',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-18 15:11:31',6, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-18 15:11:31',12, 6, 6, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-18 15:11:31',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-18 15:11:31',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-18 15:11:31',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-18 15:11:31',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-18 15:11:31',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-18 15:11:31',0, 41, 41, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-18 15:11:31',0, 19, 19, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-18 15:11:31',64, 12, 0, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-18 15:11:31',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-18 15:11:31',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(85, '2018-07-18 15:11:31',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-18 15:11:31',5, 9, 9, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-18 15:11:51',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-18 15:11:51',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-18 15:11:51',0, 29, 29, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-18 15:11:51',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-18 15:11:51',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-18 15:11:51',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-18 15:11:51',0, 19, 19, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-18 15:11:51',0, 23, 23, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-18 15:11:51',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-18 15:11:51',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-18 15:11:51',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-18 15:11:51',0, 14, 14, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-18 15:11:51',0, 101, 101, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-18 15:11:51',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-18 15:11:51',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-18 15:11:51',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-18 15:11:51',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-18 15:11:51',0, 4, 4, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-18 15:11:51',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-18 15:11:51',0, 40, 40, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-18 15:11:51',0, 8, 8, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-18 15:11:51',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-18 15:11:51',0, 11, 11, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-18 15:11:51',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-18 15:11:51',6, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-18 15:11:51',12, 6, 6, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-18 15:11:51',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-18 15:11:51',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-18 15:11:51',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-18 15:11:51',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-18 15:11:51',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-18 15:11:51',0, 41, 41, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-18 15:11:51',0, 19, 19, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-18 15:11:51',32, 0, 32, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-18 15:11:51',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-18 15:11:51',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(85, '2018-07-18 15:11:51',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-18 15:11:51',5, 9, 9, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-23 11:11:08',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-23 11:11:08',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-23 11:11:08',0, 28, 28, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-23 11:11:08',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-23 11:11:08',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-23 11:11:08',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-23 11:11:08',0, 19, 19, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-23 11:11:08',0, 23, 23, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-23 11:11:08',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-23 11:11:08',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-23 11:11:08',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-23 11:11:08',0, 14, 14, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-23 11:11:08',0, 95, 95, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-23 11:11:08',0, 6, 6, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-23 11:11:08',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-23 11:11:08',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-23 11:11:08',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-23 11:11:08',0, 4, 4, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-23 11:11:08',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-23 11:11:08',0, 37, 37, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-23 11:11:08',0, 7, 7, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-23 11:11:08',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-23 11:11:08',0, 10, 10, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-23 11:11:08',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-23 11:11:08',6, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-23 11:11:08',12, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-23 11:11:08',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-23 11:11:08',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-23 11:11:08',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-23 11:11:08',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-23 11:11:08',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-23 11:11:08',0, 39, 39, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-23 11:11:08',0, 19, 19, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-23 11:11:08',32, 28, 28, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-23 11:11:08',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-23 11:11:08',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-23 11:11:08',18, 0, 18, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-23 11:11:08',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-23 11:11:08',5, 9, 9, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-26 09:23:27',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-26 09:23:27',0, 4, 4, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-26 09:23:27',0, 27, 27, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-26 09:23:27',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-26 09:23:27',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-26 09:23:27',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-26 09:23:27',0, 19, 19, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-26 09:23:27',0, 22, 22, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-26 09:23:27',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-26 09:23:27',0, 7, 7, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-26 09:23:27',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-26 09:23:27',0, 14, 14, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-26 09:23:27',0, 89, 89, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-26 09:23:27',0, 6, 6, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-26 09:23:27',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-26 09:23:27',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-26 09:23:27',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-26 09:23:27',0, 4, 4, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-26 09:23:27',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-26 09:23:27',0, 37, 37, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-26 09:23:27',0, 4, 4, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-26 09:23:27',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-26 09:23:27',0, 10, 10, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-26 09:23:27',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-26 09:23:27',6, 4, 0, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-26 09:23:27',12, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-26 09:23:27',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-26 09:23:27',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-26 09:23:27',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-26 09:23:27',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-26 09:23:27',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-26 09:23:27',0, 37, 37, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-26 09:23:27',0, 18, 18, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-26 09:23:27',32, 28, 28, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-26 09:23:27',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-26 09:23:27',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-26 09:23:27',18, 17, 17, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-26 09:23:27',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-26 09:23:27',5, 9, 9, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-26 09:23:40',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-26 09:23:40',0, 4, 4, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-26 09:23:40',0, 27, 27, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-26 09:23:40',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-26 09:23:40',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-26 09:23:40',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-26 09:23:40',0, 19, 19, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-26 09:23:40',0, 22, 22, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-26 09:23:40',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-26 09:23:40',0, 7, 7, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-26 09:23:40',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-26 09:23:40',0, 14, 14, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-26 09:23:40',0, 89, 89, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-26 09:23:40',0, 6, 6, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-26 09:23:40',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-26 09:23:40',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-26 09:23:40',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-26 09:23:40',0, 4, 4, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-26 09:23:40',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-26 09:23:40',0, 37, 37, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-26 09:23:40',0, 4, 4, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-26 09:23:40',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-26 09:23:40',0, 10, 10, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-26 09:23:40',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-26 09:23:40',0, 0, 6, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-26 09:23:40',12, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-26 09:23:40',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-26 09:23:40',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-26 09:23:40',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-26 09:23:40',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-26 09:23:40',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-26 09:23:40',0, 37, 37, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-26 09:23:40',0, 18, 18, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-26 09:23:40',32, 28, 28, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-26 09:23:40',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-26 09:23:40',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-26 09:23:40',18, 17, 17, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-26 09:23:40',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-26 09:23:40',5, 9, 9, 0.5, 0);
        
        $incomePerItem = array();
        $incomeCorrectPerItem = array();
        
        foreach( $dailyAmountArray as $day ) {
            $itemID = $day[0];
            $backstockAfter = $day[2];
            $shelfQuantityBefore = $day[3];
            $shelfQuantityAfter = $day[4];
            $price = $day[5];
            
            echo "Item $itemID at " . $day[1] . "<br>";
            
            $totalCansBefore = 0 + $shelfQuantityBefore;
            $totalCans = $backstockAfter + $shelfQuantityAfter;
            $income = (($totalCansBefore - $totalCans) * $price ) * -1;
            
            $incomeCorrect = 0;
            
            if( $shelfQuantityBefore > $shelfQuantityAfter ) {
                $incomeCorrect = ($shelfQuantityBefore - $shelfQuantityAfter) * $price;
            }
            
            echo "Income: $income<br><br>";
            
            if( array_key_exists( $itemID, $incomePerItem ) == false ) {
                $incomePerItem[$itemID] = 0.0;
            }
            
            if( array_key_exists( $itemID, $incomeCorrectPerItem ) == false ) {
                $incomeCorrectPerItem[$itemID] = 0.0;
            }
            
            $incomePerItem[$itemID] += $income;
            $incomeCorrectPerItem[$itemID] += $incomeCorrect;
        }
        
        $totalCorrections = 0.0;
        foreach( $incomePerItem as $itemID => $value ) {
            
            $resultsInner = $db->query("SELECT * from Item WHERE ID = $itemID");
            $rowInner = $resultsInner->fetchArray();
            
            $totalCans = $rowInner['TotalCans'];
            $totalIncome = $rowInner['TotalIncome'];
            $backstock = $rowInner['BackstockQuantity'];
            $shelf = $rowInner['ShelfQuantity'];
            $name = $rowInner['Name'];
            $priceSnack = $rowInner['Price'];
            $priceSnackDisc = $rowInner['DiscountPrice'];
            
            $totalUnits = $totalCans - ($shelf + $backstock);
            $newIncome = ($totalIncome + $value); // Remove the bad income
            $newIncome = $newIncome += $incomeCorrectPerItem[$itemID]; // Add the right income
            $expectedIncomeHigher = $totalUnits * $priceSnack;
            $expectedIncomeLower = $totalUnits * $priceSnackDisc;
            
            $correction = $value + $incomeCorrectPerItem[$itemID];
            
            $totalCorrections += $correction;
            
            $correct = "";
            
            if( $newIncome > $expectedIncomeLower && $newIncome < $expectedIncomeHigher ) {
                $correct = "CORRECT!!!";
            }
            if( $value != 0 ) {
                echo "Item $name ($itemID)---> FIX INCOME ($value) + ACTUAL INCOME ($totalIncome) Units: $totalUnits Price: $priceSnack Corrected:" . $incomeCorrectPerItem[$itemID] . "=========== PROJECTED NEW INCOME ($newIncome) &nbsp;&nbsp;&nbsp;&nbsp;LOW ($expectedIncomeLower) &nbsp;&nbsp;&nbsp;&nbsp;HIGH ($expectedIncomeHigher) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$correct <br><br>";
            }
            
            $db->exec("Update Item set TotalIncome = TotalIncome + $correction WHERE ID = $itemID");
            echo "Fixed $name with +$correction<br>";
        }
        
        $db->exec("Update Information set Income = Income + $totalCorrections WHERE ItemType ='Snack'");
        echo "Fixed Income with +$totalCorrections<br>";
        echo "CIMEOM FIX $totalCorrections";
    }
?>