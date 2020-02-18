<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 2/1/2020
 * Time: 7:35 PM
 */

class VendorHistoryLayout extends MonthlyLayout {
    function getHeader( $name ) {
        echo "Vendor History for <b>$name</b>";
    }

    /**
     * @param $db SQLite3
     * @param $userID
     * @return string|void
     */
    function buildPurchaseInformation( $db, $userID ) {
        $statement = $db->prepare( "SELECT i.Name, p.ItemID, i.Type, d.Price, p.CashOnly, d.RetailPrice, d.DiscountPrice, d.ItemDetailsID, " .
            "p.Cost as LegacyPrice, p.DiscountCost as LegacyDiscountPrice, p.Date, p.UserID, p.UseCredits, p.Cancelled, u.FirstName, u.LastName " .
            "FROM Purchase_History p " .
            // LEFT JOIN Item because Credits are ID 4000, and there is no corresponding Item in the Item table with that ID
            "LEFT JOIN Item i on p.ItemID = i.ID " .
            "LEFT JOIN Item_Details d ON p.ItemDetailsID = d.ItemDetailsID " .
            "LEFT JOIN User u ON p.UserID = u.UserID " .
            "WHERE i.VendorID = :userID AND p.UserID != :userID " .
            "ORDER BY p.Date DESC, p.ID DESC" );

        $statement->bindValue(":userID", $userID );

        $results = $statement->execute();
        while ($row = $results->fetchArray()) {
            $this->storePurchaseHistory( $row['Date'], $row['ItemDetailsID'], $row['LegacyDiscountPrice'], $row['LegacyPrice'], $row['DiscountPrice'], $row['Price'],
                $row['UseCredits'], $row['CashOnly'], $row['Type'], $row['Name'], $row['Cancelled'], $row['RetailPrice'], $row['ItemID'], $row['FirstName'] . " " . $row['LastName'] );
        }

        $statement = $db->prepare( "SELECT i.Name, p.ItemID, i.Type, d.Price, d.RetailPrice, d.DiscountPrice, d.ItemDetailsID, " .
            "p.Price as LegacyPrice, p.Date, p.Cancelled " .
            "FROM Inventory_History p " .
            // LEFT JOIN Item because Credits are ID 4000, and there is no corresponding Item in the Item table with that ID
            "LEFT JOIN Item i on p.ItemID = i.ID " .
            "LEFT JOIN Item_Details d ON p.ItemDetailsID = d.ItemDetailsID " .
            "WHERE i.VendorID = :userID AND p.InventoryType = 'MANUAL PURCHASE' " .
            "ORDER BY p.Date DESC, p.ID DESC" );

        $statement->bindValue(":userID", $userID );

        $results = $statement->execute();
        while ($row = $results->fetchArray()) {
            $this->storePurchaseHistory( $row['Date'], $row['ItemDetailsID'], 0, $row['LegacyPrice'], 0, $row['Price'],
                0, 0, $row['Type'], $row['Name'], $row['Cancelled'], $row['RetailPrice'], $row['ItemID'], "(Inventory)" );
        }
    }

    function isVendor() {
        return true;
    }

    function getPaymentsQuery() {
        return "SELECT UserID, Amount, Date, ItemType, Method FROM Payments WHERE VendorID = :userID AND MonthForPayment = :currentMonthLabel AND Cancelled is NULL ORDER BY Date DESC, ItemType ASC";
    }
}