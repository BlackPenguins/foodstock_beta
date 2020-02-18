<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 2/7/2020
 * Time: 6:46 PM
 */

class PurchaseHistoryObj
{
    public $date = "";
    public $itemID = 0;
	public $itemName = "";
	public $cost = 0;
	public $discountCost = 0;
	public $retailCost = 0;
	public $profit = 0;
	public $type;
	public $isCashOnly;
	public $credtsUsed;
	public $isCancelled;
	public $user;

	public function __construct($date, $itemID, $itemName, $cost, $discountCost, $retailCost, $profit, $type, $isCashOnly, $creditsUsed, $isCancelled, $user ) {
		$this->date = $date;
		$this->itemName = $itemName;
		$this->itemID = $itemID;
		$this->cost = $cost;
		$this->discountCost = $discountCost;
		$this->retailCost = $retailCost;
		$this->profit = $profit;
		$this->type = $type;
		$this->isCashOnly = $isCashOnly;
		$this->creditsUsed = $creditsUsed;
		$this->isCancelled = $isCancelled;
		$this->user = $user;
	}
}