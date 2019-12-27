<?php

    include(ITEM_OBJ);

    function getPrepareStatementForInClause( $numberOfItemsInList ) {
        return "(" . trim( str_repeat("?,", $numberOfItemsInList ), "," ) . ")";
    }

    /**
     * @param $statement PDOStatement
     * @param $array
     */
    function bindStatementsForInClause( $statement, $array ) {
        for( $i = 1; $i <= count( $array ); $i++ ) {
            $statement->bindValue( $i, $array[$i - 1] );
        }
    }

    function getQuantityQuery() {
        return "(select count(ItemID) FROM Items_In_Stock WHERE IsBackstock IS NULL OR IsBackstock = 0 AND ItemID = i.ID ) as ShelfAmount, " .
               "(select count(ItemID) FROM Items_In_Stock WHERE IsBackstock = 1 AND ItemID = i.ID ) as BackstockAmount," .
               "(select count(ItemID) FROM Items_In_Stock WHERE ItemID = i.ID ) as TotalAmount";
    }

    // REFILL (FLIP THE BIT)
    /**
     * @param $db SQLite3
     * @param $amount
     * @param $itemID
     * @return Item[]
     */
    function moveToShelfQuantity( $db, $amount, $itemID ) {
        $itemDetailsArray = array();

        benchmark_start("Move Quantity Query  $itemID");
        $statement = $db->prepare( "SELECT s.StockID, s.ItemDetailsID FROM Items_In_Stock s " .
            "WHERE s.StockID IN (SELECT ss.StockID FROM Items_In_Stock ss WHERE ss.ItemID = :itemID AND ss.IsBackstock = 1 ORDER BY ss.Date ASC LIMIT $amount)" );
        $statement->bindValue( ":itemID", $itemID );
        $results = $statement->execute();

        benchmark_stop("Move Quantity Query  $itemID");

        benchmark_start("Move Quantity Loop  $itemID");
        $stockIDs = array();

        while ($row = $results->fetchArray()) {
            $stockID = $row['StockID'];
            $itemDetailsID = $row['ItemDetailsID'];

            benchmark_start("Move Quantity Obj  $itemID");
            $itemDetailsObj = createItemObj( $db, $itemDetailsID );

            $itemDetailsArray[] = $itemDetailsObj;
            $stockIDs[] = $stockID;
            benchmark_stop("Move Quantity Obj  $itemID");
        }
        benchmark_stop("Move Quantity Loop  $itemID");

        benchmark_start("Move Quantity Update  $itemID");
        $stockIDJoin = implode( ",", $stockIDs );

        $statement = $db->prepare( "UPDATE Items_In_Stock SET IsBackstock = 0 WHERE StockID in ($stockIDJoin)" );
        $statement->bindValue(":stockIDJoin", $stockIDJoin );
        $statement->execute();

        benchmark_stop("Move Quantity Update  $itemID");

        return $itemDetailsArray;
    }

    // CANCEL SITE AND MANUAL PURCHASES
    /**
     * @var $db SQLite3
     * @return Item[]
     */
    function addToShelfQuantity( $db, $amount, $itemID, $itemDetailsID, $type ) {
        $itemDetailsArray = array();

        $itemDetailsObj = createItemObj( $db, $itemDetailsID );

        if( $amount > 0 ) {
            $date = date('Y-m-d H:i:s');

            for ($shelf = 0; $shelf < $amount; $shelf++) {

                $statement = $db->prepare( "INSERT INTO Items_In_Stock (ItemID, IsBackstock, Date, ItemDetailsID) VALUES (:itemID, 0, :date, :itemDetailsID)" );
                $statement->bindValue(":itemID", $itemID );
                $statement->bindValue(":date", $date );
                $statement->bindValue(":itemDetailsID", $itemDetailsID );
                $statement->execute();

                // All identical items are being added, so use same object
                $itemDetailsArray[] = $itemDetailsObj;
            }
        }

        return $itemDetailsArray;
    }

    // RESTOCK
    /**
     * @var $db SQLite3
     */
    function addToBackstockQuantity( $db, $amount, $itemID, $itemDetailsID, $type ) {
        // We're just adding an ItemDetails, don't need to return an Item array
        // because we aren't doing anything with it after
        if( $amount > 0 ) {
            $date = date('Y-m-d H:i:s');

            for ($back = 0; $back < $amount; $back++) {
                $stmt=$db->prepare("INSERT INTO Items_In_Stock (ItemID, IsBackstock, Date, ItemDetailsID) VALUES " .
                    "(:itemID, 1, :date, :itemDetailsID)");
                $stmt->bindValue(':itemID', $itemID );
                $stmt->bindValue(':date', $date );
                $stmt->bindValue(':itemDetailsID', $itemDetailsID );
                $stmt->execute();
            }
        }
    }

    // SITE PURCHASE
    // MANUAL PURCHASE
    // DEFECTIVE
    /**
     * @var $db SQLite3
     * @return Item[]
     */
    function removeFromShelfQuantity($db, $amount, $itemID ) {
        $itemDetailsArray = array();

        if( $amount > 0 ) {
            $statement = $db->prepare( "SELECT s.StockID, s.ItemDetailsID " .
                "FROM Items_In_Stock s " .
                "WHERE s.ItemID = :itemID AND ( s.IsBackstock IS NULL OR s.IsBackstock = 0 ) ORDER BY s.Date ASC LIMIT $amount" );
            $statement->bindValue( ":itemID", $itemID );
            $results = $statement->execute();

            while ($row = $results->fetchArray()) {
                $stockID = $row['StockID'];
                $itemDetailsID = $row['ItemDetailsID'];

                $itemDetailsObj = createItemObj( $db, $itemDetailsID );
                $itemDetailsArray[] = $itemDetailsObj;

                $statement = $db->prepare( "DELETE FROM Items_In_Stock WHERE StockID = $stockID" );
                $statement->bindValue(":stockID", $stockID );
                $statement->execute();
            }
        }

        return $itemDetailsArray;
    }

    // CANCEL RESTOCK
    /**
     * @var $db SQLite3
     * @return Item[]
     */
    function removeFromBackstockQuantity($db, $amount, $itemID, $type ) {
        if( $amount > 0 ) {
            $statement = $db->prepare( "DELETE FROM Items_In_Stock WHERE StockID IN (SELECT StockID FROM Items_In_Stock WHERE ItemID = :itemID AND IsBackstock = 1 ORDER BY Date ASC LIMIT :amount)" );
            $statement->bindValue( ":itemID", $itemID );
            $statement->bindValue( ":amount", $amount );
            $statement->execute();
        }
    }

    /**
     * @param $db SQLite3
     * @param $itemDetailsID
     * @return Item
     */
    function createItemObj( $db, $itemDetailsID ) {
        $itemQuery = "SELECT d.ItemID, d.Price, d.DiscountPrice, d.RetailPrice, d.ExpDate, i.Type, i.Name FROM Item_Details d JOIN ITEM i ON d.ItemID = i.ID WHERE d.ItemDetailsID = :itemDetailsID";
        $statement = $db->prepare( $itemQuery );
        $statement->bindValue( ":itemDetailsID", $itemDetailsID );
        $results = $statement->execute();

        $row = $results->fetchArray();

        $itemID = $row['ItemID'];
        $price = $row['Price'];
        $discountPrice = $row['DiscountPrice'];
        $retailPrice = $row['RetailPrice'];
        $expDate = $row['ExpDate'];

        if( $discountPrice == 0 ) {
            // This means its the same price as the full price
            $discountPrice = $price;
        }

        $itemName = $row['Name'];
        $itemType = $row['Type'];

        return new Item( $itemID, $price, $discountPrice, $retailPrice, $expDate, $itemName, $itemType, $itemDetailsID );
    }
?>