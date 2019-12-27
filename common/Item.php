<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 8/10/2019
 * Time: 4:47 PM
 */

include_once(ACTION_FUNCTIONS_PATH);

class Item {
	public $itemID = 0;
	public $price = 0;
	public $discountPrice = 0;
	public $retailPrice = 0;
	public $expDate = "";

	public $itemName = "";
	public $itemType = "";

	public $itemDetailsID = 0;


	public function __construct($itemID, $price, $discountPrice, $retailPrice, $expDate, $itemName, $itemType, $itemDetailsID) {
		$this->itemID = $itemID;
		$this->price = $price;
		$this->discountPrice = $discountPrice;
		$this->retailPrice = $retailPrice;
		$this->expDate = $expDate;
		$this->itemName = $itemName;
		$this->itemType = $itemType;
		$this->itemDetailsID = $itemDetailsID;
	}

	public function getFullPrice() {
		return $this->price;
	}

	public function getDiscountPrice() {
		return $this->discountPrice;
	}

	public function getRetailPrice() {
		return $this->retailPrice;
	}

	public function getSitePurchasePrice() {
	   return getSitePurchasePrice( $this->discountPrice, $this->price );
	}

	public function getManualPurchasePrice() {
	    return $this->price;
	}

	public function getItemName() {
	    return $this->itemName;
    }

    public function getItemType() {
	    return $this->itemType;
    }

    public function getItemDetailsID() {
	    return $this->itemDetailsID;
    }
}
?>