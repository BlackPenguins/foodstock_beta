<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 9/15/2019
 * Time: 6:43 PM
 */

class Testing extends TestingBase {

    public function __construct() {
		include_once(HANDLE_FORMS_PATH);
	}

    function runTest( $db ) {

        TestingBase::$startTime = time();

        sendSlackMessageToMatt("*Automated Test Begin*", ":bar_chart:", "FOODSTOCK TESTING", "#b7ab1a");

        $this->addSection("Register User");

        $testUser = new User( $db, "jHalpert", "Jim", "Halpert", "123-555-8675" );
        $testUser->createUser();

        $sodaItemTest = new ItemCostDetails($db, "Soda", "Milk_", 4.89, 1.45);
        $sodaItemTest->testSetup( $testUser );

        $this->addDatapoint( "User soda balance is now [" . $testUser->getSodaBalance() . "]" );

        // We will do the purchasing from here using the get() values (getPrice()) from the POPOs
        // Maybe the User should have its own POPO? Savings, Balance
        // Or just store those values in this method - we are only testing one user?

        $snackItemTest = new ItemCostDetails($db, "Snack", "Cookies_", 12.03, 11.99);
        $snackItemTest->testSetup( $testUser );

        $this->addDatapoint( "User snack balance is now [" . $testUser->getSnackBalance() . "]" );

        $this->addSection( "Make Payments" );
        $sodaBalanceWholeCents = $testUser->getSodaBalance();
        $snackBalanceWholeCents = $testUser->getSnackBalance();

        $test2SodaPaymentWholeCents = 230;

        $test1SodaPaymentWholeCents = $sodaBalanceWholeCents - $test2SodaPaymentWholeCents;

        $test3SnackPaymentWholeCents = 679;

        $test2SnackPaymentWholeCents = $snackBalanceWholeCents - $test3SnackPaymentWholeCents;



        $this->addDatapoint( "Soda Payment Only" );
        $this->testPayment( $db, $testUser, $sodaItemTest, $snackItemTest, $test1SodaPaymentWholeCents, 0, "Test1", "PayPal" );

        $this->addDatapoint( "Rest of Soda Payment and Partial Snack Payment" );
        $this->testPayment( $db, $testUser, $sodaItemTest, $snackItemTest, $test2SodaPaymentWholeCents, $test2SnackPaymentWholeCents, "Test2", "Venmo" );

        $this->addDatapoint( "Rest of Snack Payment - Balance is Zero" );
        $this->testPayment( $db, $testUser, $sodaItemTest, $snackItemTest, 0, $test3SnackPaymentWholeCents, "Test3", "Cash" );

        $this->addDatapoint( "Cancel last 4 payments - Undo Profit Only." );
        $this->testCancelPayment( $db, $testUser, $sodaItemTest, $snackItemTest, 0, $test3SnackPaymentWholeCents, $test2SodaPaymentWholeCents, $test2SnackPaymentWholeCents );


        $percentagePassed = TestingBase::$totalPasses / TestingBase::$totalTests * 100;
        $percentagePassed = round( floor( $percentagePassed ), 0 );
        $totalTime = time() - TestingBase::$startTime;
        $testStatus = TestingBase::$totalPasses != TestingBase::$totalTests ? ":rage:" : ":tada: :tada: :tada: :tada: :tada: ";

        sendSlackMessageToMatt("$testStatus *Automated Test Complete*\n(" . TestingBase::$totalPasses . "/" . TestingBase::$totalTests . ") tests passed - *$percentagePassed%* [$totalTime seconds]", ":bar_chart:", "FOODSTOCK TESTING", "#b7ab1a");
    }

    /**
     * @param $db SQLite3
     * @param $user User
     * @param $soda ItemCostDetails
     * @param $snack ItemCostDetails
     * @param $sodaPayment
     * @param $snackPayment
     * @param $note
     * @param $method
     */
    function testPayment( $db, $user, $soda, $snack, $sodaPayment, $snackPayment, $note, $method ) {
        $todaysMonth = date('F Y');
        $sodaBalance = $user->getSodaBalance();
        $snackBalance = $user->getSnackBalance();

        $this->addDatapoint( "Adding Soda Payment[$sodaPayment] Snack Payment[$snackPayment] || Soda Balance [$sodaBalance] Snack Balance [$snackBalance]" );
        makePayment( $db, $user->getUserID(), $todaysMonth, $sodaPayment, $snackPayment, $note, $method );
        $user->increaseBalance( "Soda", -abs( $sodaPayment ) );
        $user->increaseBalance( "Snack", -abs( $snackPayment ) );
        $soda->sitePayments += $sodaPayment;
        $snack->sitePayments += $snackPayment;

        $user->assertUserBalance();
        $soda->assertSiteProfit();
        $snack->assertSiteProfit();
    }

    /**
     * @param $db SQLite3
     * @param $user User
     * @param $soda ItemCostDetails
     * @param $snack ItemCostDetails
     * @param $soda1ReturnPayment
     * @param $snack1ReturnPayment
     * @param $soda2ReturnPayment
     * @param $snack2ReturnPayment
     */
    function testCancelPayment( $db, $user, $soda, $snack, $soda1ReturnPayment, $snack1ReturnPayment, $soda2ReturnPayment, $snack2ReturnPayment ) {
        $lastPaymentID = $this->getValue( $db, "Select PaymentID from Payments ORDER BY PaymentID DESC LIMIT 1 " );
        $secondLastPaymentID = $lastPaymentID - 1;
        $thirdLastPaymentID = $lastPaymentID - 2;
        $fourthLastPaymentID = $lastPaymentID - 3;
        $this->addDatapoint( "Latest PaymentIDs: [$lastPaymentID, $secondLastPaymentID, $thirdLastPaymentID, $fourthLastPaymentID]" );

        cancelPayment( $db, $lastPaymentID );
        cancelPayment( $db, $secondLastPaymentID );
        cancelPayment( $db, $thirdLastPaymentID );
        cancelPayment( $db, $fourthLastPaymentID );

        $user->increaseBalance( "Soda", $soda1ReturnPayment );
        $user->increaseBalance( "Snack", $snack1ReturnPayment );
        $soda->sitePayments -= $soda1ReturnPayment;
        $snack->sitePayments -= $snack1ReturnPayment;

        $user->increaseBalance( "Soda", $soda2ReturnPayment );
        $user->increaseBalance( "Snack", $snack2ReturnPayment );
        $soda->sitePayments -= $soda2ReturnPayment;
        $snack->sitePayments -= $snack2ReturnPayment;

        $user->assertUserBalance();
        $soda->assertSiteProfit();
        $snack->assertSiteProfit();


    }
}