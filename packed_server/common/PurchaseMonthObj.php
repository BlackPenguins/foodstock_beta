<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 2/7/2020
 * Time: 6:46 PM
 */

class PurchaseMonthObj
{
    public $sodaTotal = 0.0;
    public $snackTotal = 0.0;

    public $sodaCashOnlyTotal = 0.0;
    public $snackCashOnlyTotal = 0.0;

    public $sodaCreditTotal = 0.0;
    public $snackCreditTotal = 0.0;

    public $sodaPaid = 0.0;
    public $snackPaid = 0.0;

    public $sodaCount = 0;
    public $snackCount = 0;

    public $sodaCashOnlyCount = 0;
    public $snackCashOnlyCount = 0;

    public $monthLabel = "";
    public $month = 0;
    public $year = 0;

    public function __construct() {
        // Allocate stuff
	}

	public static function withPurchaseHistory( $monthLabel ) {
        $instance = new self();
        $instance->monthLabel = $monthLabel;
        return $instance;
    }
	public static function withPaymentHistory( $db, $userID, $month, $year ) {
        $monthDate = new DateTime();
        $monthDate->setDate($year, $month, 1 );

        $instance = new self();
        $instance->monthLabel = $monthDate->format( 'F Y' );
        $instance->month = $month;
        $instance->year = $year;
        $instance->createForUser( $db, $userID );
        return $instance;
    }

    public function addItemToToal( $itemName, $type, $balanceCost, $creditOrCashOnlyCost, $date ) {
//        benchmark_start( "StoreItem[$itemName]" );
        // Only purchases that WERE NOT cash-only go towards the total - because they already paid in cash
        if ($balanceCost > 0) {
            if ($type == "Snack") {
                $this->snackTotal += $balanceCost;
                $this->snackCount++;
//                log_debug("Purchase Counts: [Snack] Balance: [$balanceCost] Count[$this->snackCount] New Total: [$this->snackTotal] Name: [$itemName] Date: [$date]");
            } else if ($type == "Soda") {
                $this->sodaTotal += $balanceCost;
                $this->sodaCount++;
//                log_debug("Purchase Counts: [Soda] Balance: [$balanceCost] Count[$this->sodaCount] New Total: [$this->sodaTotal] Name: [$itemName] Date: [$date]");
            }
        }

        if ($creditOrCashOnlyCost > 0) {
            if ($type == "Snack") {
                $this->snackCashOnlyTotal += $creditOrCashOnlyCost;
                $this->snackCashOnlyCount++;
//                log_debug("Credit Counts: [Snack] Balance: [$creditOrCashOnlyCost] Count[$this->snackCashOnlyCount] New Total: [$this->snackCashOnlyTotal] Name: [$itemName] Date: [$date]");
            } else if ($type == "Soda") {
                $this->sodaCashOnlyTotal += $creditOrCashOnlyCost;
                $this->sodaCashOnlyCount++;
//                log_debug("Credit Counts: [Soda] Balance: [$creditOrCashOnlyCost] Count[$this->sodaCashOnlyCount] New Total: [$this->sodaCashOnlyTotal] Name: [$itemName] Date: [$date]");
            }
        }
//        benchmark_stop( "StoreItem[$itemName]" );
    }
    /**
     * @param $db SQLite3
     * @param $userID
     * @return array
     */
	private function createForUser( $db, $userID ) {
        $startDate = $this->year . "-" . $this->month . "-01";

        $endMonth = $this->month;
        $endYear = $this->year;

        if( $endMonth == 12) {
            $endMonth = 1;
            $endYear++;
        } else {
            $endMonth++;
        }

        if( $endMonth < 10 ) { $endMonth = "0" . $endMonth; }

        $endDate = $endYear . "-" . $endMonth . "-01";

        $currentMonthSodaTotal = 0.0;
        $currentMonthSnackTotal = 0.0;

        $currentMonthSodaCreditTotal = 0.0;
        $currentMonthSnackCreditTotal = 0.0;

        $query = "SELECT i.Name, i.Type, p.Cost as LegacyPrice, p.CashOnly, p.UseCredits, p.DiscountCost as LegacyDiscountPrice, p.Date, p.UserID, p.ItemDetailsID, d.Price, d.DiscountPrice " .
            "FROM Purchase_History p " .
            "JOIN Item i on p.itemID = i.ID " .
            "LEFT JOIN Item_Details d on p.ItemDetailsID = d.ItemDetailsID " .
            "WHERE p.UserID = :userID AND p.Date >= :startDate AND p.Date < :endDate AND p.Cancelled IS NULL " .
            "ORDER BY p.Date DESC";

//        log_sql( "Payment Month: [$query]" );
        $statement = $db->prepare( $query );
        $statement->bindValue( ":userID", $userID );
        $statement->bindValue( ":startDate", $startDate );
        $statement->bindValue( ":endDate", $endDate );
        $results = $statement->execute();

        while ($row = $results->fetchArray()) {

            $itemDetailsID =  $row['ItemDetailsID'];
            $itemType =  $row['Type'];

            $discountCost = $row['LegacyDiscountPrice'];
            $fullCost = $row['LegacyPrice'];

            if( $itemDetailsID != null ) {
                $discountCost = $row['DiscountPrice'];
                $fullCost = $row['Price'];
            }

            if( $discountCost != "" && $discountCost != 0 ) {
                $cost = $discountCost;
            } else {
                $cost = $fullCost;
            }

            // Only purchases that WERE NOT cash-only go towards the total - because they already paid in cash
            if( $row['CashOnly'] != 1 ) {

                $creditsUsed = $row["UseCredits"];
                $finalCostNotIncludingCredits = $cost;

                // Purchases that used credits might affect what was actually for balance
                if( $creditsUsed > 0 ) {
                    $finalCostNotIncludingCredits -= $creditsUsed;

                    if( $itemType == "Snack" ) {
                        $currentMonthSnackCreditTotal += $creditsUsed;
                    } else if( $itemType == "Soda" ) {
                        $currentMonthSodaCreditTotal += $creditsUsed;
                    }
                }

                if( $itemType == "Snack" ) {
                    $currentMonthSnackTotal += $finalCostNotIncludingCredits;
                } else if($itemType == "Soda" ) {
                    $currentMonthSodaTotal += $finalCostNotIncludingCredits;
                }
            }
        }

        $sodaQuery = "SELECT Sum(Amount) as 'TotalAmount' FROM Payments WHERE UserID = :userID AND MonthForPayment = :monthLabel AND ItemType= :itemType AND Cancelled IS NULL AND VendorID = 0";
        $sodaStatement = $db->prepare( $sodaQuery );
        $sodaStatement->bindValue( ":userID", $userID );
        $sodaStatement->bindValue( ":monthLabel", $this->monthLabel );
        $sodaStatement->bindValue( ":itemType", "Soda" );
        $sodaResults = $sodaStatement->execute();

        $sodaTotalPaid = $sodaResults->fetchArray()['TotalAmount'];

        $snackQuery = "SELECT Sum(Amount) as 'TotalAmount' FROM Payments WHERE UserID = :userID AND MonthForPayment = :monthLabel AND ItemType= :itemType AND Cancelled IS NULL AND VendorID = 0";
        $snackStatement = $db->prepare( $snackQuery );
        $snackStatement->bindValue( ":userID", $userID );
        $snackStatement->bindValue( ":monthLabel", $this->monthLabel );
        $snackStatement->bindValue( ":itemType", "Snack" );
        $snackResults = $snackStatement->execute();

        $snackTotalPaid = $snackResults->fetchArray()['TotalAmount'];

        $this->sodaTotal = $currentMonthSodaTotal;
        $this->snackTotal = $currentMonthSnackTotal;
        $this->sodaCreditTotal = $currentMonthSodaCreditTotal;
        $this->snackCreditTotal = $currentMonthSnackCreditTotal;
        $this->sodaPaid = $sodaTotalPaid;
        $this->snackPaid = $snackTotalPaid;

//        log_debug( "Month [$this->monthLabel] User [$userID] Soda Total [$currentMonthSodaTotal] Snack Total [$currentMonthSnackTotal] Soda Paid: [$sodaTotalPaid] Snack Paid[$snackTotalPaid]" );
    }

    public function getTotal() {
	    return $this->sodaTotal + $this->snackTotal;
    }

    public function getTotalPaid() {
	    return $this->sodaPaid + $this->snackPaid;
    }

    public function getSodaUnpaid() {
	    return $this->sodaTotal - $this->sodaPaid;
    }

    public function getSnackUnpaid() {
	    return $this->snackTotal - $this->snackPaid;
    }

    public function getTotalUnpaid() {
	    return $this->getSodaUnpaid() + $this->getSnackUnpaid();
    }

    public function getTotalCredit() {
	    return $this->sodaCreditTotal + $this->snackCreditTotal;
    }
}