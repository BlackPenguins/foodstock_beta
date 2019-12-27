<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 8/13/2019
 * Time: 8:35 PM
 */

class ItemCostDetails extends TestingBase {

    public $itemIncome = 0;
	public $itemExpenses = 0;
	public $itemProfit = 0;
	public $siteIncome = 0;
	public $siteExpenses = 0;
	public $sitePayments = 0;
	public $siteProfit = 0;
	public $restockTrigger = 0;
	public $refillTrigger = 0;

	public $latestInventoryRows = array();

	public $itemID = 0;
	public $shelfQuantity = 0;
	public $backstockQuantity = 0;
	public $itemPrice = 0;
	public $itemDiscountPrice = 0;
	public $itemRetailPrice = 0;

	public $itemType = "";
	public $db = null;


	public function __construct( $db, $itemType, $itemNamePrefix, $itemPrice, $itemDiscountPrice ) {
		$this->db = $db;
		$this->itemType = $itemType;
		$this->itemName = $itemNamePrefix . rand(1, 6000);
		$this->itemPrice = $itemPrice * 100;
		$this->itemDiscountPrice = $itemDiscountPrice * 100;

		$randomMarginPerItem = rand( 5, 30 );
		// Candy Item Price = 0.20 each
        // Candy Discount Price = 0.17 each

        // Candy Item Retail Price = 0.10 each

        // Profit is 0.10 for non-site
        // Profit is 0.07 for site
		$this->itemRetailPrice = $this->itemDiscountPrice  - $randomMarginPerItem;

		include_once(HANDLE_FORMS_PATH);
	}

    /**
     * @param $user User
     */
	public function testSetup( $user )
    {
        // CREATE ITEMS
        $this->addSection("Adding " . $this->itemType . " Item");

        $createItemResponse = addItem($this->db, $this->itemName, "FF3300", $this->itemPrice / 100.00, $this->itemDiscountPrice / 100.00, $this->itemType);

        $this->assertText("$this->itemName Item Success", $createItemResponse, "Item \"$this->itemName\" added successfully.");

        $createItemResponse = addItem($this->db, $this->itemName, "FF3300", $this->itemPrice / 100.00, $this->itemDiscountPrice / 100.00, $this->itemType);

        $this->assertText("$this->itemType Item Already Exists", $createItemResponse, "Item \"$this->itemName\" already exists.");

        // Get IDs
        $this->itemID = $this->getValue($this->db, "SELECT ID From Item WHERE Name = '$this->itemName'");
        $this->siteIncome = $this->getValue($this->db, "SELECT SiteIncome From Information WHERE ItemType = '$this->itemType'");
        $this->siteExpenses = $this->getValue($this->db, "SELECT SiteExpenses From Information WHERE ItemType = '$this->itemType'");
        $this->sitePayments = $this->getValue($this->db, "SELECT SitePayments From Information WHERE ItemType = '$this->itemType'");
        $this->siteProfit = $this->getValue($this->db, "SELECT SiteProfit From Information WHERE ItemType = '$this->itemType'");

        $this->assertColumn($this->db, "Add $this->itemType - ID", "SELECT ID From Item WHERE Name = '$this->itemName'", "$this->itemID");
        $this->assertColumn($this->db, "Add $this->itemType - Price", "SELECT Price From Item WHERE Name = '$this->itemName'", $this->itemPrice);
        $this->assertColumn($this->db, "Add $this->itemType - DiscountPrice", "SELECT DiscountPrice From Item WHERE Name = '$this->itemName'", $this->itemDiscountPrice);
        $this->assertColumn($this->db, "Add $this->itemType - Name", "SELECT Name From Item WHERE Name = '$this->itemName'", "$this->itemName");
        $this->assertColumn($this->db, "Add $this->itemType - Type", "SELECT Type From Item WHERE Name = '$this->itemName'", "$this->itemType");

        $this->assertQuantities();
        $this->assertItemProfit();
        $this->assertSiteProfit();


        // RESTOCK THOSE ITEMS
        $this->addSection("Restock  " . $this->itemType . " Item");
        $firstRestockQuantity = rand( 1, 10 );
        $this->addDatapoint( "Restocking with zero backstock." );
        $this->testRestock( $firstRestockQuantity );

        // Again, with expenses and backstock no longer 0
        $secondRestockQuantity = rand( 1, 10 );
        $this->addDatapoint( "Restocking with non-zero backstock." );
        $this->testRestock( $secondRestockQuantity );

        // Again, this is the quantity that will be left as backstock
        $thirdRestockQuantity = rand( 4, 10 );
        $this->addDatapoint( "Restocking the amount that will be left as backstock as the refill." );
        $this->testRestock( $thirdRestockQuantity );

        $this->addSection("Refill " . $this->itemType . " Item");

        // Leave the $thirdRestockQuantity as backstock
        $refillQuantity = $firstRestockQuantity + $secondRestockQuantity;

        $this->addDatapoint( "Refilling with the first two restock quantities." );
        $this->testRefill( $refillQuantity );


        $this->addDatapoint( "Refilling with just enough to trigger a Restock Trigger." );
        $quantityToRestock = $this->backstockQuantity - 3;
        $this->restockTrigger = 1;
        $this->testRefill( $quantityToRestock );

        $this->addSection("Inventory " . $this->itemType . " Item");
        $this->testInventory( 5 );
        $this->refillTrigger = 1;
        $this->testInventory( 1 );

        $this->addSection("Purchase " . $this->itemType . " Item");
        $this->testPurchase( $user, 1, 0 );
        $this->testPurchase( $user, 1, 0 );

        $this->testRefill( $this->backstockQuantity );

        $this->addSection("Purchase " . $this->itemType . " Item - Out of Stock");
        $this->testPurchase( $user, 2, 0 );
        $this->testPurchase( $user, 2, 0 );

        $this->testRestock( 23 );
        $this->testRefill( 23 );
        $this->refillTrigger = 0;

        $this->addSection("Purchase " . $this->itemType . " Item - Cash Only");
        $this->testPurchase( $user, 3, 1 );

        $this->addSection("Purchase " . $this->itemType . " Item - Credits Only");
        $amountForCreditsExactlyWholeCents = 3 * $this->itemDiscountPrice;
        $amountForCreditsAndBalanceWholeCents = 5 * $this->itemDiscountPrice;
        $creditsToCoverBothTestsWholeCents = $amountForCreditsExactlyWholeCents + $amountForCreditsAndBalanceWholeCents;
        $creditsToCoverBothTests = ceil( ( $creditsToCoverBothTestsWholeCents ) / 100 );
        $creditsToCoverBothTestsWholeCents = $creditsToCoverBothTests * 100;

        $this->addDatapoint( "Buying [$$creditsToCoverBothTestsWholeCents&cent;] credits for the user." );
        creditUser( $this->db, $this->isHidingSlack(), $user->getUserID(), $creditsToCoverBothTests, false );
        $user->increaseCredits( $creditsToCoverBothTestsWholeCents );

        // This will be small enough to not use all credits
        $this->testPurchase( $user, 3, 0 );

        $this->addSection("Purchase " . $this->itemType . " Item - Credits and Balance");

        // This will use up all credits along with balance
        $this->testPurchase( $user, 8, 0 );

        $this->addSection("Purchase " . $this->itemType . " Item - Credits Only Exactly at Zero");

        $amountForCreditsExactlyWholeCents = 4 * $this->itemDiscountPrice;
        $amountForCreditsExactly = $amountForCreditsExactlyWholeCents / 100;

        $this->addDatapoint( "Buying [$$amountForCreditsExactlyWholeCents&cent;] credits for the user." );
        creditUser( $this->db, $this->isHidingSlack(), $user->getUserID(), $amountForCreditsExactly, false );
        $user->increaseCredits( round( $amountForCreditsExactlyWholeCents ) );

        // This will use up all credits to exactly 0
        $this->testPurchase( $user, 4, 0 );

        $this->addSection("Returning Credits");
        $creditsToBuyWholeCents = 2823;
        $creditsToBuy = 28.23;
        $creditsToSellWholeCents = 1719;
        $creditsToSell = 17.19;
        $this->addDatapoint( "Buying [$$creditsToBuyWholeCents&cent;] credits for the user." );
        creditUser( $this->db, $this->isHidingSlack(), $user->getUserID(), $creditsToBuy, false );
        $user->increaseCredits( $creditsToBuyWholeCents );

        $this->addDatapoint( "Returning [$$creditsToSellWholeCents&cent;] credits for the user." );
        creditUser( $this->db, $this->isHidingSlack(), $user->getUserID(), $creditsToSell, true );
        $user->increaseCredits( $creditsToSellWholeCents * -1 );

        $user->assertUserBalance();

        $this->addSection("Defect " . $this->itemType . " Item");
        $this->testDefective( 5 );


        $this->addSection("--- " .  $this->itemType . " Test Complete ---");
    }

    function testRefill( $refillQuantity ) {
	    $itemIDs = array();
        $addToShelf = array();

        $itemIDs[] = $this->itemID;
        $addToShelf[]  = $refillQuantity;

	    $this->addDatapoint( "Refilling Item [$this->itemID] with [$refillQuantity] quantity" );
        refillItem( $this->db, $this->isHidingSlack(), $itemIDs, $addToShelf, true );
        $this->setInventoryHistory( $refillQuantity, "REFILL" );

        $this->shelfQuantity = $this->shelfQuantity + $refillQuantity;
        $this->backstockQuantity = $this->backstockQuantity - $refillQuantity;

        $this->addDatapoint( "After refill all profits should remain the same." );
        $this->assertInventoryHistory( $refillQuantity, "REFILL" );
        $this->assertQuantities();
        $this->assertItemProfit();
        $this->assertSiteProfit();
        $this->assertRestockTrigger();
    }

    function testInventory( $newShelfQuantity ) {
	    $itemIDs = array();
        $removeFromShelf = array();

        $itemIDs[] = $this->itemID;
        $removeFromShelf[] = $newShelfQuantity;
        $inventoryQuantity = $this->shelfQuantity - $newShelfQuantity;

	    $this->addDatapoint( "Inventory Item [$this->itemID] from [$this->shelfQuantity] to [$newShelfQuantity] quantity" );
        inventoryItem( $this->db, $itemIDs, $removeFromShelf, "", $this->itemType );
        $this->setInventoryHistory( $inventoryQuantity, "INVENTORY" );

        $this->shelfQuantity = $newShelfQuantity;

        // We round so it becomes an int, not float
        $itemIncomeForInventory = round($this->itemPrice * $inventoryQuantity );
        $itemRetailTotalPrice = round($this->itemRetailPrice * $inventoryQuantity);
        $this->addDatapoint( "[$this->itemPrice&cent;] price x [$inventoryQuantity] quantity = [$itemIncomeForInventory] income");

        $profitForItems = $itemIncomeForInventory - $itemRetailTotalPrice;
        $this->addDatapoint( "[$itemIncomeForInventory&cent;] income - [$itemRetailTotalPrice&cent;] retail = [$profitForItems&cent;] profit");

        $this->siteProfit += $profitForItems;
        $this->itemProfit += $profitForItems;

        $this->siteIncome += $itemIncomeForInventory;
        $this->itemIncome += $itemIncomeForInventory;

        $this->assertQuantities();
        $this->assertInventoryHistory( $inventoryQuantity, "INVENTORY" );
        $this->assertItemProfit();
        $this->assertSiteProfit();
        $this->assertRefillTrigger();
    }

    function testDefective( $quantity ) {
        $newShelfQuantity = $this->shelfQuantity - $quantity;

	    $this->addDatapoint( "Defecting Item [$this->itemID] from [$this->shelfQuantity] to [$newShelfQuantity] quantity" );
        defectItem( $this->db, $this->itemID, $quantity );

        $this->shelfQuantity = $newShelfQuantity;

        // Complete loss - no income

        $this->assertQuantities();
        $this->assertItemProfit();
        $this->assertSiteProfit();
        $this->assertRefillTrigger();
    }

    /**
     * @param $user User
     * @param $quantity int
     */
    function testPurchase( $user, $quantity, $cashOnly ) {
	    $itemIDs = array();
        for( $i = 0; $i < $quantity; $i++ ) {
            $itemIDs[] = $this->itemID;
        }

	    $this->addDatapoint( "Purchase Item ID [$this->itemID] from [$this->shelfQuantity] on shelf with [$quantity] desired quantity" );

	    if( $this->shelfQuantity - $quantity >= 0 ) {

	        $this->setInventoryHistory($quantity, "INVENTORY");
	        $this->shelfQuantity -= $quantity;

	        $itemIncomeForInventory = round($this->itemDiscountPrice * $quantity);
	        $itemRetailTotalPrice = round($this->itemRetailPrice * $quantity);
            $this->addDatapoint("[$this->itemDiscountPrice&cent;] price x [$quantity] quantity = [$itemIncomeForInventory&cent;] income");

            $this->siteIncome += $itemIncomeForInventory;
            $this->itemIncome += $itemIncomeForInventory;

            $profitForItems = $itemIncomeForInventory - $itemRetailTotalPrice;
            $this->addDatapoint( "[$itemIncomeForInventory&cent;] income - [$itemRetailTotalPrice&cent;] retail = [$profitForItems&cent;] profit");

            $this->siteProfit += $profitForItems;
            $this->itemProfit += $profitForItems;

            if( $cashOnly == 1 ) {
                $this->addDatapoint( "Cash-Only. Only decrement the quantity. Increase profits." );
            } else {
                $creditsLeft = $user->getCreditsLeft();
                $this->addDatapoint( "Amount of credits left: [$creditsLeft]" );

                if( $creditsLeft >= 0 ) {
                    // 100 total - 40 credits = 60 left for the balance
                    // 100 total - 300 credits = -200 left for the credits
                    $amountAddToBalance = $itemIncomeForInventory - $creditsLeft;
                    $this->addDatapoint( "Credits left. Weird math going down. Total [$itemIncomeForInventory&cent;] Amount for Balance: [$amountAddToBalance&cent;]" );
                    if( $amountAddToBalance >= 0 ) {
                        // Part balance, part credits
                        $this->addDatapoint( "Part Balance, Part Credits. Balance Increase [$amountAddToBalance&cent;] All Credits Used [$creditsLeft&cent;]" );
                        $user->increaseBalance($this->itemType, $amountAddToBalance);
                        $user->decreaseCredits( $creditsLeft );
                    } else {
                        $this->addDatapoint( "Only credits used. Balance unaffected. Decrement credits: [$itemIncomeForInventory]" );
                        $user->decreaseCredits( $itemIncomeForInventory );
                    }
                } else {
                    $this->addDatapoint( "Increasing Balance by [$itemIncomeForInventory]" );
                    $user->increaseBalance($this->itemType, $itemIncomeForInventory);
                }
            }
        } else {
	        $this->addDatapoint( "There is zero left. Nothing should be changed." );
        }

        purchaseItems( $this->db, $this->isHidingSlack(), $user->getUserID(), $itemIDs, $cashOnly );

        $this->assertQuantities();
        $this->assertInventoryHistory( $quantity, "INVENTORY" );
        $this->assertItemProfit();
        $this->assertSiteProfit();
        $this->assertRefillTrigger();
        $user->assertUserBalance();
    }

    function testRestock( $randomItemRestockQuantity ) {

        $randomItemRestockCostInDB = ($this->itemRetailPrice * $randomItemRestockQuantity );
        $randomItemRestockCost = $randomItemRestockCostInDB / 100;

        $this->addDatapoint( "Restocking [$this->itemName] with [$randomItemRestockQuantity] qty at [$" . $randomItemRestockCost . "] Retail Cost. Item Price [" . $this->itemPrice . "] Discount Price: [$this->itemDiscountPrice]" );

        $this->siteExpenses += $randomItemRestockCostInDB;
        $this->itemExpenses += $randomItemRestockCostInDB;
        $this->backstockQuantity += $randomItemRestockQuantity;

        restockItem( $this->db, $this->itemID, $randomItemRestockQuantity, 1, $randomItemRestockCost, $this->itemType, "01/20/2029" );

        $this->assertQuantities();
        $this->assertItemProfit();
        $this->assertSiteProfit();
    }


    function assertQuantities() {
        // Get Quantities
        /* @var $db SQLite3 */
        $db = $this->db;
        $statement = $db->prepare("SELECT " . getQuantityQuery() . " FROM Item i WHERE ID = :itemID" );
        $statement->bindValue( ":itemID", $this->itemID );
        $results = $statement->execute();

        $itemQuantities = $results->fetchArray();

        $actualShelfQuantity = $itemQuantities['ShelfAmount'];
        $actualBackstockQuantity = $itemQuantities['BackstockAmount'];

        $this->assertText("Shelf Quantity", $actualShelfQuantity, $this->shelfQuantity );
        $this->assertText("Backstock Quantity", $actualBackstockQuantity, $this->backstockQuantity );
    }

    function assertItemProfit() {
        // Get and SET Quantities
        $actualItemIncome = $this->getValue($this->db, "SELECT ItemIncome From Item WHERE Name = '$this->itemName'");
        $actualItemExpenses = $this->getValue($this->db, "SELECT ItemExpenses From Item WHERE Name = '$this->itemName'");
        $actualItemProfit = $this->getValue($this->db, "SELECT ItemProfit From Item WHERE Name = '$this->itemName'");

        $this->assertText("$this->itemName - Income", $actualItemIncome, $this->itemIncome );
        $this->assertText("$this->itemName - Expenses", $actualItemExpenses, $this->itemExpenses );
        $this->assertText("$this->itemName - Profit", $actualItemProfit, $this->itemProfit );
    }

    function assertSiteProfit() {
        // Get Quantities
        $actualSiteIncome = $this->getValue($this->db, "SELECT SiteIncome From Information WHERE ItemType = '$this->itemType'");
        $actualSiteExpenses = $this->getValue($this->db, "SELECT SiteExpenses From Information WHERE ItemType = '$this->itemType'");
        $actualSitePayments = $this->getValue($this->db, "SELECT SitePayments From Information WHERE ItemType = '$this->itemType'");
        $actualSiteProfit = $this->getValue($this->db, "SELECT SiteProfit From Information WHERE ItemType = '$this->itemType'");

        $this->assertText("$this->itemType - Site Income", $actualSiteIncome, $this->siteIncome );
        $this->assertText("$this->itemType - Site Expenses", $actualSiteExpenses, $this->siteExpenses );
        $this->assertText("$this->itemType - Site Payments", $actualSitePayments, $this->sitePayments );
        $this->assertText("$this->itemType - Site Profit", $actualSiteProfit, $this->siteProfit );
    }

    function assertRestockTrigger() {
        $actualRestockTrigger = $this->getValue($this->db, "SELECT RestockTrigger From Item WHERE Name = '$this->itemName'");

        $this->assertText("Restock Trigger $this->itemType", $actualRestockTrigger, $this->restockTrigger );
    }

    function assertRefillTrigger() {
        $actualRefillTrigger = $this->getValue($this->db, "SELECT RefillTrigger From Item WHERE Name = '$this->itemName'");

        $this->assertText("Refill Trigger $this->itemType ", $actualRefillTrigger, $this->refillTrigger );
    }

    function setInventoryHistory( $numberOfRows, $type ) {
        $currentShelf = $this->shelfQuantity;
        $currentBackstock = $this->backstockQuantity;

        // Clear array for new rows
        $this->latestInventoryRows = array();

        for( $row = 1; $row <= $numberOfRows; $row++ ) {
            $expectedBackstockBefore = 0;
            $expectedBackstock = 0;
            $expectedShelfBefore = 0;
            $expectedShelf = 0;

            if( $type == "REFILL" ) {
                $expectedBackstockBefore = $currentBackstock;
                $expectedBackstock = $currentBackstock - 1;
                $expectedShelfBefore = $currentShelf;
                $expectedShelf = $currentShelf + 1;

                $currentShelf++;
                $currentBackstock--;
            } else if( $type == "INVENTORY" ) {
                $expectedBackstockBefore = $currentBackstock;
                $expectedBackstock = $currentBackstock;
                $expectedShelfBefore = $currentShelf;
                $expectedShelf = $currentShelf - 1;

                $currentShelf--;
            }

            $this->latestInventoryRows[] = "BackBefore[$expectedBackstockBefore] Back[$expectedBackstock] ShelfBefore[$expectedShelfBefore] Shelf[$expectedShelf] Item[$this->itemID]";
        }
    }
    function assertInventoryHistory( $numberOfRows, $type ) {
        /* @var $db SQLite3 */
        $db = $this->db;
        $statementPurchases = $db->prepare("SELECT * from Inventory_History ORDER BY ID DESC LIMIT $numberOfRows");
        $resultsPurchases = $statementPurchases->execute();

        $currentRow = $numberOfRows - 1;
        while ($rowPurchases = $resultsPurchases->fetchArray()) {
            $backstockBefore = $rowPurchases['BackstockQuantityBefore'];
            $backstock = $rowPurchases['BackstockQuantity'];
            $shelfBefore = $rowPurchases['ShelfQuantityBefore'];
            $shelf = $rowPurchases['ShelfQuantity'];
            $itemID = $rowPurchases['ItemID'];

            $expectedString = "Row [$currentRow] is less than zero. More actual rows than expected ones!";

            if( $currentRow >= 0 ) {
                $expectedString =  $this->latestInventoryRows[$currentRow];
            }

            $actualString = "BackBefore[$backstockBefore] Back[$backstock] ShelfBefore[$shelfBefore] Shelf[$shelf] Item[$itemID]";

            $this->assertText( "Inventory History $type $numberOfRows Rows", $actualString, $expectedString );

            $currentRow--;
        }
    }

}