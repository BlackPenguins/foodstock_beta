<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 9/28/2019
 * Time: 3:59 PM
 */

class User extends TestingBase
{
    public $userID = 0;
    public $username = "";
    public $firstName = "";
    public $lastName = "";
    public $phoneNumber = "";

    public $sodaBalance = 0;
    public $snackBalance = 0;
    public $sodaSavings = 0;
    public $snackSavings = 0;
    public $creditsLeft = 0;

    public $db = null;


    public function __construct($db, $username, $firstName, $lastName, $phoneNumber)
    {
        $randomID = rand(1, 6000);
        $this->db = $db;
        $this->username = $username . "_" . $randomID;
        $this->firstName = $firstName;
        $this->lastName = $lastName . "_" . $randomID;
        $this->phoneNumber = $phoneNumber;


        include_once(HANDLE_FORMS_PATH);
    }

    public function createUser()
    {
        registerUser($this->db, $this->isHidingSlack(), $this->username, $this->username, $this->username, $this->firstName, $this->lastName, $this->phoneNumber, "RSA");

        $this->userID = $this->getValue($this->db, "Select UserID from User WHERE UserName = '" . $this->username . "'");
        $this->addDatapoint("Created [$this->username] User with ID of [$this->userID]");
    }

    public function increaseBalance( $itemType, $balance ) {
        if( $itemType == "Soda" ) {
            $this->sodaBalance += $balance;
        } else if( $itemType == "Snack" ) {
            $this->snackBalance += $balance;
        }
    }

    public function increaseCredits( $creditsLeft) {
        $this->creditsLeft += $creditsLeft;
    }

    public function decreaseCredits( $creditsLeft) {
        $this->addDatapoint( "Decrement credits [$creditsLeft] from User" );
        $this->creditsLeft -= $creditsLeft;
    }

    public function getCreditsLeft() {
        return $this->creditsLeft;
    }

    /**
     * @return int
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * @param int $userID
     */
    public function setUserID($userID)
    {
        $this->userID = $userID;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return int
     */
    public function getSodaBalance()
    {
        return $this->sodaBalance;
    }

    /**
     * @param int $sodaBalance
     */
    public function setSodaBalance($sodaBalance)
    {
        $this->sodaBalance = $sodaBalance;
    }

    /**
     * @return int
     */
    public function getSnackBalance()
    {
        return $this->snackBalance;
    }

    /**
     * @param int $snackBalance
     */
    public function setSnackBalance($snackBalance)
    {
        $this->snackBalance = $snackBalance;
    }

    /**
     * @return int
     */
    public function getSodaSavings()
    {
        return $this->sodaSavings;
    }

    /**
     * @param int $sodaSavings
     */
    public function setSodaSavings($sodaSavings)
    {
        $this->sodaSavings = $sodaSavings;
    }

    /**
     * @return int
     */
    public function getSnackSavings()
    {
        return $this->snackSavings;
    }

    /**
     * @param int $snackSavings
     */
    public function setSnackSavings($snackSavings)
    {
        $this->snackSavings = $snackSavings;
    }

    function assertUserBalance() {
        $actualSnackBalance = $this->getValue($this->db, "SELECT SnackBalance From User WHERE UserID = " . $this->userID );
        $actualSodaBalance = $this->getValue($this->db, "SELECT SodaBalance From User WHERE UserID = " . $this->userID );
        $actualSnackSavings= $this->getValue($this->db, "SELECT SnackSavings From User WHERE UserID = " . $this->userID );
        $actualSodaSavings = $this->getValue($this->db, "SELECT SodaSavings From User WHERE UserID = " . $this->userID );
        $actualCredits = $this->getValue($this->db, "SELECT Credits From User WHERE UserID = " . $this->userID );

        $this->assertText("User - Snack Balance", $actualSnackBalance, $this->snackBalance );
        $this->assertText("User - Soda Balance", $actualSodaBalance, $this->sodaBalance );
        $this->assertText("User - Snack Savings", $actualSnackSavings, $this->snackSavings );
        $this->assertText("User - Soda Savings", $actualSodaSavings, $this->sodaSavings );
        $this->assertText("User - Credits", $actualCredits, $this->creditsLeft );
    }
}