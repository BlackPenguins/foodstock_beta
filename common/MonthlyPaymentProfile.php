<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 5/23/2020
 * Time: 6:21 PM
 */

class MonthlyPaymentProfile {
    private $months = array();

    public function storeMonthlyProfile( $month, $year, $monthlyProfile ) {
        $this->months[ $this->getMonthlyKey( $month, $year ) ] = $monthlyProfile;
	}

	public function getMonthlyProfile( $month, $year ) {
        return $this->months[ $this->getMonthlyKey( $month, $year ) ];
	}

	private function getMonthlyKey( $month, $year ) {
        return "$month-$year";
    }
}