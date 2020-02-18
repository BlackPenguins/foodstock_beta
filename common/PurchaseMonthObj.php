<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 2/7/2020
 * Time: 6:46 PM
 */

class PurchaseMonthObj
{
    public $currentMonthSodaTotal = 0.0;
    public $currentMonthSnackTotal = 0.0;
    public $currentMonthSodaCashOnlyTotal = 0.0;
    public $currentMonthSnackCashOnlyTotal = 0.0;

    public $currentMonthSodaCount = 0;
    public $currentMonthSnackCount = 0;
    public $currentMonthSodaCashOnlyCount = 0;
    public $currentMonthSnackCashOnlyCount = 0;

    public $monthLabel = "";

	public function __construct( $monthLabel ) {
		$this->monthLabel = $monthLabel;
	}
}