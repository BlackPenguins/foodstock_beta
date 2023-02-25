<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 5/23/2020
 * Time: 6:10 PM
 */

class UserPaymentProfile {
    private $months = array();

    public function __construct() {
        // Allocate stuff
	}

	public function storeItem( $itemName, $type, $balanceCost, $creditOrCashOnlyCost, $date ) {
        $purchaseDateObject = DateTime::createFromFormat( 'Y-m-d H:i:s', $date );
        $purchaseMonthLabel = $purchaseDateObject->format('F Y');

        $monthKey = $this->getMonthlyKeyByDate( $date );
        if ( !array_key_exists($monthKey, $this->months ) ) {
            $monthlyInformation = PurchaseMonthObj::withPurchaseHistory( $purchaseMonthLabel );
            $this->months[$monthKey] = $monthlyInformation;
        } else {
            $monthlyInformation = $this->months[$monthKey];
        }

        $monthlyInformation->addItemToToal( $itemName, $type, $balanceCost, $creditOrCashOnlyCost, $date );
    }

    public function storePastYearPayments( $db, $userID, $monthsMaxAgo) {
        for( $monthsAgo = 0; $monthsAgo <= $monthsMaxAgo; $monthsAgo++ ) {
            $month = date('m', UserPaymentProfile::getDateOfPreviousMonth( $monthsAgo ) );
            $year = date('Y', UserPaymentProfile::getDateOfPreviousMonth( $monthsAgo ) );

            $purchaseMonth = PurchaseMonthObj::withPaymentHistory( $db, $userID, $month, $year );

            $this->months[ $this->getMonthlyKeyByNumber( $month, $year ) ] = $purchaseMonth;
        }
    }
	public function getMonthlyProfile( $month, $year ) {
        return $this->months[ $this->getMonthlyKeyByNumber( $month, $year ) ];
	}

	public function getMonthlyKeyByNumber( $month, $year ) {
        return $month . "_" . $year;
    }

    /**
     * @param $date DateTime
     * @return mixed
     */
    public function getMonthlyKeyByDate( $date ) {
        $date = DateTime::createFromFormat( 'Y-m-d H:i:s', $date );
        return $date->format('m_Y');
    }

    public function getMonths() {
        return $this->months;
    }

    public static function getDateOfPreviousMonth( $xMonthsAgo ) {
        $today = date('F Y');
        return strtotime( '-' . $xMonthsAgo . ' months', strtotime( $today ) );
    }
}