<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 2/1/2020
 * Time: 7:09 PM
 */

include PURCHASE_HISTORY_OBJ;
include PURCHASE_MONTH_OBJ;

class MonthlyLayout {

    public $purchaseHistoryArray = array();
    public $monthInformationArray = array();

    /**
     * @param $db SQLite3
     */
    function draw( $db ) {
        $this->printToggleMonthJS();

        if( IsAdminLoggedIn() && isset($_GET['userid'] ) && isset($_GET['name'] )  ) {
            $userID = $_GET['userid'];
            $name = $_GET['name'];
        } else {
            $userID = $_SESSION['UserID'];
            $name = $_SESSION['FirstName'] . " " . $_SESSION['LastName'];
        }

        $month = date('m_Y' );

        if( isset( $_GET['month'] ) ) {
            $month = $_GET['month'];
        }

        $monthPieces = explode("_", $month );

        if( count( $monthPieces ) != 2 ) {
            echo "You are trying to do some weird stuff with this month you selected: <b>$month</b>";
            die();
        }

        $monthNumber = $monthPieces[0];
        $year = $monthPieces[1];

        echo "<div id= 'container'>";

        echo "<div style='height:55px; position: relative;' class='rounded_header'>";

        echo "<div style='position:absolute; top: 0; left: 0; padding: 5px 20px;' class='title'>";
        echo $this->getHeader( $name );
        echo "</div>";

        $this->drawRightHeader( $db, $userID );
        echo "</div>";

        echo "<div class='center_piece'>";

        $this->displayPaymentMethods();

        echo "<div class='rounded_table_no_border'>";

        $this->buildPurchaseInformation( $db, $userID );

        echo "<table>";
        echo "<thead>";

        $columnCount = 4;

        if( $this->IsVendor() ) {
            $columnCount = 6;
        }

        echo "<tr>";
        echo "<td style='text-align:center; background-color: #255420; color:#d8e41d; font-weight:bold; text-transform: uppercase;' colspan='$columnCount'>PAYMENT HISTORY</td>";
        echo "</tr>";

        echo "<tr class='table_header'>";
        echo "<th>Month</th>";

        if( $this->isVendor() ) {
            echo "<th style='text-align:center;' colspan='3'>Totals</th>";
        } else {
            echo "<th>Balance</th>";
        }
        echo "<th>Payments</th>";
        echo "<th>Money Owed</th>";

        echo "</tr>";

        if( $this->isVendor() ) {
            echo "<tr class='table_header'>";
            echo "<th style='border-top: 2px solid #255420;'>&nbsp;</th>";
            echo "<th style='border-top: 2px solid #255420;'>Total Income</th>";
            echo "<th style='border-top: 2px solid #255420;'>Matt Tax</th>";
            echo "<th style='border-top: 2px solid #255420;'>Paycheck Amount</th>";
            echo "<th style='border-top: 2px solid #255420;'>&nbsp;</th>";
            echo "<th style='border-top: 2px solid #255420;'>&nbsp;</th>";
            echo "</tr>";
        }

        echo "</thead>";

        foreach( $this->monthInformationArray as $monthLink => $information ) {
            $this->printBillingMonthRow( $db, $month, $monthLink, $userID, $information, $name );
        }

        echo "<tr>";
        echo "<td id='showHideMonths' colspan='$columnCount' style='text-align: center; cursor:pointer;' onclick='showHideMonths();' >Show all Months</td>";
        echo "</tr>";

        echo "</table>";

        echo "</div>";

        $monthHeader = date('F Y', mktime(0, 0, 0, $monthNumber, 1, $year));

        $this->printPurchaseHistory( $month, $monthHeader );

        echo "</div>";
        echo "</div>";
    }

    function printPurchaseHistory( $month, $monthHeader ) {
        $colSpan = 3;
        if( IsAdminLoggedIn() || IsVendor() ) {
            $colSpan += 2;
        }

        if( $this->isVendor() ) {
            $colSpan += 1;
        }

        echo "<div style='margin-top:50px;' class='rounded_table_no_border'>";
        echo "<table>";

        echo "<thead>";

        echo "<tr>";
        echo "<td style='text-align:center; background-color: #255420; color:#d8e41d; font-weight:bold; text-transform: uppercase;' colspan='$colSpan'>PURCHASE HISTORY - $monthHeader</td>";
        echo "</tr>";

        echo "<tr class='table_header'>";
        echo "<th width='20%'>Date Purchased</th>";
        if( $this->isVendor() ) {
            echo "<th>User</th>";
        }
        echo "<th>Item</th>";
        echo "<th>Cost</th>";
        if( IsAdminLoggedIn() || $this->isVendor() ) {
            echo "<th>Retail Cost</th>";
            echo "<th>Profit</th>";
        }
        echo "</tr>";

        echo "</thead>";

        if( array_key_exists( $month, $this->purchaseHistoryArray ) ) {
            $currentWeek = "";
            $purchaseHistoryRows = $this->purchaseHistoryArray[$month];

            usort( $purchaseHistoryRows, array("MonthlyLayout", "comparePurchaseHistory" ) );

            foreach ($purchaseHistoryRows as $purchaseHistoryRow) {
                $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $purchaseHistoryRow->date);

                $weekOfPurchase = $date_object->format('\W\e\e\k W - \Y\e\a\r Y');

                if ($currentWeek == "") {
                    $currentWeek = $weekOfPurchase;
                } else {
                    if ($currentWeek != $weekOfPurchase) {
                        // New week
                        echo "<tr>";
                        echo "<td class='section' colspan='$colSpan'>";
                        echo $weekOfPurchase;
                        echo "</td>";
                        echo "</tr>";

                        $currentWeek = $weekOfPurchase;
                    }
                }
                $isCancelled = $purchaseHistoryRow->isCancelled === 1;
                $itemName = $purchaseHistoryRow->itemName;

                $discountCost = $purchaseHistoryRow->discountCost;
                $fullCost = $purchaseHistoryRow->cost;

                $discountAmountDisplay = getPriceDisplayWithDollars($discountCost);
                $costAmountDisplay = getPriceDisplayWithDollars($fullCost);

                if ($isCancelled) {
                    $discountAmountDisplay .= " (REFUNDED)";
                    $costAmountDisplay .= " (REFUNDED)";
                }

                if ($discountCost != "" && $discountCost != 0) {
                    $costDisplay = "<span class='red_price'>" . getPriceDisplayWithDollars($fullCost) . "</span>" . $discountAmountDisplay;
                    $cost = $discountCost;
                } else {
                    $costDisplay = $costAmountDisplay;
                    $cost = $fullCost;
                }

                if ($purchaseHistoryRow->isCashOnly == 1) {
                    $costDisplay = $costDisplay . "<span style='float:right; font-weight: bold; color:#023e0c;'>(CASH - ONLY)</span>";
                }

                $creditsUsed = $purchaseHistoryRow->creditsUsed;
                if ($creditsUsed > 0) {
                    $partialCreditsLabel = "";

                    if (($discountCost != 0 && $creditsUsed < $discountCost)
                        || ($discountCost == 0 && $fullCost != 0 && $creditsUsed < $fullCost)) {
                        $partialCreditsLabel = " PARTIAL";
                    }

                    $costDisplay = $costDisplay . "<span style='float:right; font-weight: bold; color:#716e08;'>(WITH " . getPriceDisplayWithDollars($creditsUsed) . "$partialCreditsLabel CREDITS)</span>";
                }

                $rowClass = "";
                $itemType = $purchaseHistoryRow->type;

                if ($isCancelled) {
                    $rowClass = "class='refund_row'";
                } else if ($itemType == "Soda") {
                    $rowClass = "class='soda_row'";
                } else if ($itemType == "Snack") {
                    $rowClass = "class='snack_row'";
                } else {
                    $itemID = $purchaseHistoryRow->itemID;

                    if ($itemID == CREDIT_ID) {
                        $creditDisplay = getPriceDisplayWithDollars($purchaseHistoryRow->cost);

                        if ($purchaseHistoryRow->cost > 0) {
                            $rowClass = "class='add_credit_row'";
                            $itemName = "Added Credits&nbsp;&nbsp;($creditDisplay)";
                        } else {
                            $rowClass = "class='remove_credit_row'";
                            $itemName = "Returned Credits&nbsp;&nbsp;($creditDisplay)";
                        }

                        $costDisplay = "---";
                    }
                }

                echo "<tr $rowClass>";
                echo "<td>" . $date_object->format('l m/d/Y  [h:i A]') . "</td>";

                if( $this->isVendor() ) {
                    echo "<td>" . $purchaseHistoryRow->user . "</td>";
                }
                echo "<td>" . $itemName . "</td>";
                echo "<td>" . $costDisplay . "</td>";

                if ( IsAdminLoggedIn() || $this->isVendor() ) {
                    $retailPrice = $purchaseHistoryRow->retailCost;
                    $profit = $cost - $retailPrice;
                    echo "<td>" . getPriceDisplayWithDollars($retailPrice) . "</td>";
                    echo "<td>" . getPriceDisplayWithDollars($profit) . "</td>";
                }
                echo "</tr>";
            }
        }

        echo "</table>";
        echo "</div>";
    }
    /**
     * @param $db SQLite3
     * @param $selectedMonthLink
     * @param $currentMonthLink
     * @param $userID
     * @param $currentMonthLabel
     * @param $currentMonthSodaTotal
     * @param $currentMonthSnackTotal
     * @param $currentMonthSodaCashOnlyTotal
     * @param $currentMonthSnackCashOnlyTotal
     * @param $currentMonthSodaCount
     * @param $currentMonthSnackCount
     * @param $currentMonthSodaCashOnlyCount
     * @param $currentMonthSnackCashOnlyCount
     */
    function printBillingMonthRow($db, $selectedMonthLink, $currentMonthLink, $userID, $information, $fullName ) {
        $currentMonthLabel = $information->monthLabel;
        $currentMonthSodaTotal = $information->currentMonthSodaTotal;
        $currentMonthSnackTotal = $information->currentMonthSnackTotal;
        $currentMonthSodaCashOnlyTotal = $information->currentMonthSodaCashOnlyTotal;
        $currentMonthSnackCashOnlyTotal = $information->currentMonthSnackCashOnlyTotal;
        $currentMonthSodaCount = $information->currentMonthSodaCount;
        $currentMonthSnackCount = $information->currentMonthSnackCount;
        $currentMonthSodaCashOnlyCount = $information->currentMonthSodaCashOnlyCount;
        $currentMonthSnackCashOnlyCount = $information->currentMonthSnackCashOnlyCount;

        $totalPurchased = $currentMonthSodaTotal + $currentMonthSnackTotal;

        $statement = $db->prepare( $this->getPaymentsQuery() );

        $statement->bindValue(":userID", $userID );
        $statement->bindValue(":currentMonthLabel", $currentMonthLabel );

        $results = $statement->execute();

        $totalPaidSoda = 0.0;
        $totalPaidSnack = 0.0;
        $totalPaidCommissionSoda = 0.0;
        $totalPaidCommissionSnack = 0.0;
        $paymentDetails = "";

        while ($row = $results->fetchArray()) {
            $paymentAmount = $row['Amount'];
            $paymentMethod = $row['Method'];
            $itemType = $row['ItemType'];
            $userIDForVendor = $row['UserID'];

            if( $paymentMethod != "None" && $paymentMethod != "" ) {
                $paymentMethod = "&nbsp;&nbsp; [$paymentMethod]";
            } else {
                $paymentMethod = "";
            }

            $paymentDate = DateTime::createFromFormat( 'Y-m-d H:i:s', $row['Date'] )->format('F j, Y');
            $currentPaymentMethod = $paymentMethod;


            if( $paymentMethod != "None" && $paymentMethod != "" ) {
                $currentPaymentMethod = $paymentMethod;
            }

            if( $itemType == "Snack" ) {
                if( $userIDForVendor == 0 ) {
                    $totalPaidCommissionSnack += $paymentAmount;
                } else {
                    $totalPaidSnack += $paymentAmount;
                }
            } else if( $itemType == "Soda" ) {
                if( $userIDForVendor == 0 ) {
                    $totalPaidCommissionSoda += $paymentAmount;
                } else {
                    $totalPaidSoda += $paymentAmount;
                }
            }

            if( $userIDForVendor != 0 && $paymentAmount > 0) {
                $paymentDetails .= "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>" . $paymentDate . " ($itemType):</b> " . getPriceDisplayWithDollars($paymentAmount) . "$currentPaymentMethod</div>";
            }
        }




        if( $paymentDetails == "" ) {
            $paymentDetails = "No Payments";
        }

        $commissionForMatt = 0;

        if( $this->IsVendor() ) {
            $commissionForMatt = $totalPurchased * COMMISSION_PERCENTAGE;
        }

        $totalOwed = $totalPurchased - $totalPaidSoda - $totalPaidSnack - $commissionForMatt;
        $totalOwedCommission = $commissionForMatt - $totalPaidCommissionSoda - $totalPaidCommissionSnack;

        $hideRowClass = "";
        $hideRowStyle = "";
        $selectedCellStyle = "";

        if( $currentMonthLink != $selectedMonthLink && $totalOwed == 0 ) {
            $hideRowClass = "not_this_payment_month";
            $hideRowStyle ="style='display:none;'";
        }

        if( $currentMonthLink == $selectedMonthLink) {
            $selectedCellStyle ="style='color:#000000; font-weight:bold; font-size: 1.1em; vertical-align:top;'";
        }

        $owedColor = "paid_row";
        $owedLabel = "<b>ALL PAID</b>";

        if( $totalOwed != 0 || $totalOwedCommission != 0 ) {
            $owedColor = "owed_row";
            $owedLabel = getPriceDisplayWithDollars( $totalOwed );

            if( $totalOwed < 0 ) {
                $owedLabel .= " (Oopsie, we got a bug.)";
            }

            if( $totalOwedCommission != 0 ) {
                $owedLabel .= "<br>Unpaid Commission: " . getPriceDisplayWithDollars( $totalOwedCommission );
            }
        }

        echo "<tr  class='$owedColor $hideRowClass' $hideRowStyle>";
        $adminParameters = "";

        if( isset( $_GET['name'] ) ) {
            $adminParameters .= "&name=" . $_GET['name'];
        }

        if( isset( $_GET['userid'] ) ) {
            $adminParameters .= "&userid=" . $_GET['userid'];
        }

        $rootLink = PURCHASE_HISTORY_LINK;

        if( $this->isVendor() ) {
            $rootLink = VENDOR_LINK;
        }
        echo "<td $selectedCellStyle><a href = '$rootLink?month=$currentMonthLink$adminParameters'>$currentMonthLabel</a></td>";


        echo "<td $selectedCellStyle>";
        echo getPriceDisplayWithDollars( $totalPurchased );

        if( $currentMonthLink == $selectedMonthLink) {
            echo "<hr>";
            echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>$currentMonthSodaCount Sodas:</b> " . getPriceDisplayWithDollars( $currentMonthSodaTotal ) . "</div>";
            echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>$currentMonthSnackCount Snacks:</b> " . getPriceDisplayWithDollars( $currentMonthSnackTotal ) . "</div>";

            if( $currentMonthSodaCashOnlyCount > 0 ) {
                echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>Cash-Only/Credited Soda ($currentMonthSodaCashOnlyCount items):</b> " . getPriceDisplayWithDollars( $currentMonthSodaCashOnlyTotal ) . " (already paid)</div>";
            }

            if( $currentMonthSnackCashOnlyCount > 0 ) {
                echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>Cash-Only/Credited Snacks ($currentMonthSnackCashOnlyCount items):</b> " . getPriceDisplayWithDollars( $currentMonthSnackCashOnlyTotal ) . " (already paid)</div>";
            }
        }

        echo "</td>";


        $sodaVendorLeft = 0;
        $snackVendorLeft = 0;

        $sodaCommissionLeft = 0;
        $snackCommissionLeft = 0;

        if( $this->isVendor() ) {
            $totalSodaCommission = $currentMonthSodaTotal * COMMISSION_PERCENTAGE;
            $totalSnackCommission = $currentMonthSnackTotal * COMMISSION_PERCENTAGE;

            $sodaVendorLeft = $currentMonthSodaTotal -  $totalPaidSoda - $totalSodaCommission;
            $snackVendorLeft = $currentMonthSnackTotal - $totalPaidSnack - $totalSnackCommission;

            $sodaCommissionLeft = $totalSodaCommission - $totalPaidCommissionSoda;
            $snackCommissionLeft = $totalSnackCommission - $totalPaidCommissionSnack;

//             echo "Total [$currentMonthSodaTotal] Payment [$totalPaidSoda] Commission of Total [$totalSodaCommission] = Left [$sodaVendorLeft]<br>";
//             echo "Total [$currentMonthSnackTotal] Payment [$totalPaidSnack] Commission of Total [$totalSnackCommission] = Left [$snackVendorLeft]<br>";

            echo "<td $selectedCellStyle>";
            echo getPriceDisplayWithDollars($commissionForMatt);

            if( $currentMonthLink == $selectedMonthLink) {
                echo "<hr>";
                echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'>" . getPriceDisplayWithDollars($totalSodaCommission) . " for Soda</div>";
                echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'>" . getPriceDisplayWithDollars($totalSnackCommission) . " for Snack</div>";
            }
            echo "</td>";

            echo "<td $selectedCellStyle>";

            $totalOwedVendor = $totalPurchased - $commissionForMatt;
            $totalOwedVendorSoda = $currentMonthSodaTotal - $totalSodaCommission;
            $totalOwedVendorSnack = $currentMonthSnackTotal - $totalSnackCommission;

            echo getPriceDisplayWithDollars($totalOwedVendor);

            if( $currentMonthLink == $selectedMonthLink) {
                echo "<hr>";
                echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'>" . getPriceDisplayWithDollars($totalOwedVendorSoda) . " for Soda</div>";
                echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'>" . getPriceDisplayWithDollars($totalOwedVendorSnack) . " for Snack</div>";
            }

            echo "</td>";
        }


        echo "<td $selectedCellStyle>";
        echo getPriceDisplayWithDollars( $totalPaidSoda + $totalPaidSnack );

        if( $currentMonthLink == $selectedMonthLink) {
            echo "<hr>";
            echo $paymentDetails;
        }

        echo "</td>";

        $payNowButton = "";

        if( $this->isVendor() && IsAdminLoggedIn() && $totalOwed > 0 ) {
            $payNowButton = "<span style='padding: 5px;background-color: #59d83e;border: 2px solid #000; margin-left: 10px; cursor:pointer; ' " .
            "onclick='openPaymentModal(\"$fullName\", \"$userID\", \"$currentMonthLabel\", \"None\", " . getPriceDisplayWithDecimals( $sodaVendorLeft ) . ", " . getPriceDisplayWithDecimals( $snackVendorLeft ) . ", " . $sodaCommissionLeft . ", " . $snackCommissionLeft . ");'>Pay Now</span>";
        }

        echo "<td $selectedCellStyle>" .  $owedLabel . "$payNowButton</td>";
        echo "</tr>";
    }

    function printToggleMonthJS() {
        echo <<<SCRIPT
        <script>
            function showHideMonths() {
                $('.not_this_payment_month').toggle();
            
                if( $('#showHideMonths').html() == 'Show all Months' ) {
                    $('#showHideMonths').html( 'Hide all Non-Owed Payment Months' );
                } else {
                    $('#showHideMonths').html( 'Hide all Non-Owed Payment Months' );
                }
            }
        </script>
SCRIPT;
    }

    function storePurchaseHistory( $date, $itemDetailsID, $legacyDiscountPrice, $legacyPrice, $discountPrice, $price, $creditsUsed, $isCashOnly, $type, $itemName, $isCancelled, $retailCost, $itemID, $user ) {
        $purchaseDateObject = DateTime::createFromFormat( 'Y-m-d H:i:s', $date );
        $purchaseMonthLabel = $purchaseDateObject->format('F Y');
        $purchaseMonthLink = $purchaseDateObject->format('m_Y');

        $discountCost = $legacyDiscountPrice;
        $fullCost = $legacyPrice;

        if( $itemDetailsID != null ) {
            $discountCost = $discountPrice;
            $fullCost = $price;
        }

        if( $discountCost != "" && $discountCost != 0 ) {
            $cost = $discountCost;
        } else {
            $cost = $fullCost;
        }

        if( $isCancelled != 1 ) {
            $creditOrCashOnlyCost = 0;
            $balanceCost = 0;

            // Credit and cash only dont count for vendors
            // Matt already got money through Credits or money in mug, but vendor still needs to be paid
            if( $this->IsVendor() ) {
                $creditsUsed = 0;
            }

            $balanceWhenPartialCreditsUsed = $cost - $creditsUsed;

            if ($isCashOnly == 1) {
                $creditOrCashOnlyCost = $cost;
            } else if ($balanceWhenPartialCreditsUsed > 0) {
                $balanceCost = $balanceWhenPartialCreditsUsed;
                $creditOrCashOnlyCost = $creditsUsed;
            } else if ($creditsUsed > 0) {
                $creditOrCashOnlyCost = $creditsUsed;
            }

            if (!array_key_exists($purchaseMonthLink, $this->monthInformationArray)) {
                $newMonthlyInformation = new PurchaseMonthObj($purchaseMonthLabel);
                $this->monthInformationArray[$purchaseMonthLink] = $newMonthlyInformation;
            } else {
                $newMonthlyInformation = $this->monthInformationArray[$purchaseMonthLink];
            }


            // Only purchases that WERE NOT cash-only go towards the total - because they already paid in cash
            if ($balanceCost > 0) {
                if ($type == "Snack") {
                    $newMonthlyInformation->currentMonthSnackTotal += $balanceCost;
                    $newMonthlyInformation->currentMonthSnackCount++;
                    log_debug("Purchase Counts: [Snack] Balance: [$balanceCost] Count[$newMonthlyInformation->currentMonthSnackCount] New Total: [$newMonthlyInformation->currentMonthSnackTotal] Name: [$itemName] Date: [$date]");
                } else if ($type == "Soda") {
                    $newMonthlyInformation->currentMonthSodaTotal += $balanceCost;
                    $newMonthlyInformation->currentMonthSodaCount++;
                    log_debug("Purchase Counts: [Soda] Balance: [$balanceCost] Count[$newMonthlyInformation->currentMonthSodaCount] New Total: [$newMonthlyInformation->currentMonthSodaTotal] Name: [$itemName] Date: [$date]");
                }
            }

            if ($creditOrCashOnlyCost > 0) {
                if ($type == "Snack") {
                    $newMonthlyInformation->currentMonthSnackCashOnlyTotal += $creditOrCashOnlyCost;
                    $newMonthlyInformation->currentMonthSnackCashOnlyCount++;
                    log_debug("Credit Counts: [Snack] Balance: [$creditOrCashOnlyCost] Count[$newMonthlyInformation->currentMonthSnackCashOnlyCount] New Total: [$newMonthlyInformation->currentMonthSnackCashOnlyTotal] Name: [$itemName] Date: [$date]");
                } else if ($type == "Soda") {
                    $newMonthlyInformation->currentMonthSodaCashOnlyTotal += $creditOrCashOnlyCost;
                    $newMonthlyInformation->currentMonthSodaCashOnlyCount++;
                    log_debug("Credit Counts: [Soda] Balance: [$creditOrCashOnlyCost] Count[$newMonthlyInformation->currentMonthSodaCashOnlyCount] New Total: [$newMonthlyInformation->currentMonthSodaCashOnlyTotal] Name: [$itemName] Date: [$date]");
                }
            }
        }

        if( !array_key_exists( $purchaseMonthLink, $this->purchaseHistoryArray ) ) {
            $this->purchaseHistoryArray[ $purchaseMonthLink ] = array();
        }

        $profit = $cost - $retailCost;

        $newPurchaseRow = new PurchaseHistoryObj( $date, $itemID, $itemName, $fullCost, $discountCost, $retailCost, $profit, $type, $isCashOnly, $creditsUsed, $isCancelled, $user );
        $this->purchaseHistoryArray[ $purchaseMonthLink ][] = $newPurchaseRow;
    }
    function getHeader( $name ) {
        echo "Unknown Header";
    }

    function drawRightHeader( $db, $userID ){}

    function displayPaymentMethods() {}

    function buildPurchaseInformation( $db, $userID ) {
        return "DEFINE THE QUERY!";
    }

    function isVendor() {
        return false;
    }

    function comparePurchaseHistory( $a, $b ) {
        return ( $a->date > $b->date ) ? -1 : 1;
    }

    function getPaymentsQuery() {
        return "DEFINE THE QUERY!";
    }
}