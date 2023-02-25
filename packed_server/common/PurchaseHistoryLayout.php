<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 2/1/2020
 * Time: 7:27 PM
 */

class PurchaseHistoryLayout extends MonthlyLayout {

    function getHeader( $name ) {
        echo "Purchase/Payment History for <b>$name</b>";
    }

    /**
     * @param $db SQLite3
     */
    function drawRightHeader( $db, $userID ) {
        $totalSodaSavings = 0.0;
        $totalSodaBalance = 0.0;
        $totalSnackSavings = 0.0;
        $totalSnackBalance = 0.0;

        $statement = $db->prepare( "SELECT d.Price, i.Type, d.DiscountPrice, d.ItemDetailsID, p.Cost as LegacyPrice, p.DiscountCost as LegacyDiscountPrice FROM Purchase_History p JOIN Item i on p.itemID = i.ID LEFT JOIN Item_Details d ON p.ItemDetailsID = d.ItemDetailsID WHERE p.UserID = :userID AND Cancelled IS NULL" );

        $statement->bindValue(":userID", $userID );

        $results = $statement->execute();

        while ($row = $results->fetchArray()) {
            $itemType = $row['Type'];

            $itemDetailsID =  $row['ItemDetailsID'];

            $discountCost = $row['LegacyDiscountPrice'];
            $fullCost = $row['LegacyPrice'];

            if( $itemDetailsID != null ) {
                $discountCost = $row['DiscountPrice'];
                $fullCost = $row['Price'];
            }

            if( $discountCost!= "" ) {
                if( $itemType == "Soda" ) {
                    $totalSodaSavings += ($fullCost - $discountCost);
                    $totalSodaBalance += $discountCost;
                } else if( $itemType == "Snack" ) {
                    $totalSnackSavings += ($fullCost -$discountCost);
                    $totalSnackBalance += $discountCost;
                }
            } else {
                if( $itemType == "Soda" ) {
                    $totalSodaBalance += $fullCost;
                } else if( $itemType == "Snack" ) {
                    $totalSnackBalance += $fullCost;
                }
            }
        }

        echo  "<div style='position:absolute; top: 0; right: 0;' class='total_details_box'><b>Total Soda Spent:</b> ". getPriceDisplayWithDollars( $totalSodaBalance ) . "&nbsp;&nbsp;|&nbsp;&nbsp;<b>Total Soda Savings:</b> " . getPriceDisplayWithDollars( $totalSodaSavings ) . "</div>";
        echo "<div style='position:absolute; bottom: 0; right: 0;' class='total_details_box'><b>Total Snack Spent:</b> ". getPriceDisplayWithDollars( $totalSnackBalance ) . "&nbsp;&nbsp;|&nbsp;&nbsp;<b>Total Snack Savings:</b> " . getPriceDisplayWithDollars( $totalSnackSavings ) . "</div>";
        printPurchaseHistorySubtitle();
    }

    function DisplayPaymentMethods() {
        echo "<div style='margin:0px 15px; padding:5px;'>";
        echo "<div style='background-color: #bd7949; padding:5px; border-top: 3px solid #000; border-right: 3px solid #000; border-left: 3px solid #000; border-bottom: 2px solid #000; '>";
        echo "<span style='vertical-align:top; font-weight:bold;'>Supported Payment Methods:</span>";
        echo "</div>";

        echo "<div style='padding: 10px; display:flex; align-items:stretch; font-weight:bold; background-color: #d89465; border-right: 3px solid #000; border-left: 3px solid #000; border-bottom: 3px solid #000;'>";

        $flexCSS = "padding:5px; border: 2px dashed #c16a2c; display:flex; align-items:center; margin:0px 10px;";
        echo "<span style='$flexCSS'>";
        echo "<img style='width:34px; margin-right:5px;' title='Square Cash App' src='" . IMAGES_LINK . "square_cash.png'/> \$mtm4440";
        echo "</span>";

        echo "<span style='$flexCSS'>";
        echo "<img style='width:35px; margin-right:5px;' title='Venmo App' src='" . IMAGES_LINK . "venmo.png'/> @Matt-Miles-17";
        echo "</span>";

        echo "<span style='$flexCSS'>";
        echo "<img style='width:37px; margin-right:5px;' title=\"Seriously needed a hover-text for this?  It's PayPal.\" src='" . IMAGES_LINK . "paypal.png'/> lightwave365@yahoo.com";
        echo "</span>";

        echo "<span style='$flexCSS'>";
        echo "<img style='width:30px; margin-right:5px;' title='Send through Facebook' src='" . IMAGES_LINK . "facebook.png'/>  mattmiles17";
        echo "</span>";

        echo "<span style='$flexCSS'>";
        echo "<img style='width:30px; margin-right:5px;' title='Cash in Hand' src='" . IMAGES_LINK . "cash_in_hand.png'/> Location: My Cube";
        echo "</span>";

        echo "<span style='$flexCSS'>";
        echo "<img style='width:30px; margin-right:5px;' title='Google Pay' src='" . IMAGES_LINK . "google_pay.jpg'/>mtm4440@g.rit.edu";
        echo "</span>";

        echo "<span style='$flexCSS font-size:0.7em;'>";
        echo "Or you can suggest something else - be a trendsetter.";
        echo "</span>";

        echo "</div>";
        echo "</div>";
    }

    /**
     * @param $db SQLite3
     * @return string|void
     */
    function buildPurchaseInformation( $db, $userID ) {
        $statement = $db->prepare( "SELECT i.Name, p.ItemID, i.Type, d.Price, p.CashOnly, d.RetailPrice, d.DiscountPrice, d.ItemDetailsID, u.FirstName, " .
            "p.Cost as LegacyPrice, p.DiscountCost as LegacyDiscountPrice, p.Date, p.UserID, p.UseCredits, p.Cancelled " .
            "FROM Purchase_History p " .
            // LEFT JOIN Item because Credits are ID 4000, and there is no corresponding Item in the Item table with that ID
            "LEFT JOIN Item i on p.ItemID = i.ID " .
            "LEFT JOIN Item_Details d ON p.ItemDetailsID = d.ItemDetailsID " .
            "LEFT JOIN User u ON u.UserID = i.VendorID " .
            "WHERE p.UserID = :userID " .
            "ORDER BY p.Date DESC, p.ID DESC" );

        $statement->bindValue(":userID", $userID );

        $results = $statement->execute();
        while ($row = $results->fetchArray()) {
            $itemName = $row['Name'];
            $vendorName = $row['FirstName'];
            if( $vendorName != "" ) {
                $itemName .= " <i>(Sold by $vendorName)</i>";
            }

            $this->storePurchaseHistory( $row['Date'], $row['ItemDetailsID'], $row['LegacyDiscountPrice'], $row['LegacyPrice'], $row['DiscountPrice'], $row['Price'],
                $row['UseCredits'], $row['CashOnly'], $row['Type'], $itemName, $row['Cancelled'], $row['RetailPrice'], $row['ItemID'], "NONE" );
        }
    }

    function isVendor() {
        return false;
    }

    function getPaymentsQuery() {
        return "SELECT UserID, Amount, Date, ItemType, Method FROM Payments WHERE UserID = :userID AND MonthForPayment = :currentMonthLabel AND Cancelled is NULL AND VendorID = 0 ORDER BY Date DESC, ItemType ASC";
    }
}