<?php
include(__DIR__ . "/../appendix.php" );
include_once( LOG_FUNCTIONS_PATH );

/**
 * @param $db SQLite3
 */
function Login($db) {
    session_start();
    
    if( isset( $_SESSION['LoggedIn'] ) && $_SESSION['LoggedIn'] == true ) {
        // Already logged in - Recache everything
        $statement = $db->prepare("SELECT * FROM User WHERE UserName = :userName");
        $statement->bindValue( ":userName", $_SESSION['UserName'] );
        $results = $statement->execute();

        $row = $results->fetchArray();
        $_SESSION['SlackID'] = $row['SlackID'];
        $_SESSION['SodaBalance'] = $row['SodaBalance'];
        $_SESSION['SnackBalance'] = $row['SnackBalance'];
        $_SESSION['Credits'] = $row['Credits'];
        $_SESSION['InactiveUser'] = $row['Inactive'] == 1;
        $_SESSION['IsVendor'] = $row['IsVendor'] == 1;
        log_debug( "Recached SlackID [" . $_SESSION['SlackID'] . "] for [" . $_SESSION['UserName'] . "]" );
        return;
    }
    
    if( !isset( $_POST['login_username'] ) || !isset( $_POST['login_password'] ) ) {
        // Missing fields - reject login
        $_SESSION['LoggedIn'] = false;
        $_SESSION['UserName'] = null;
        return;
    }
    
    $username = $db->escapeString( $_POST['login_username'] );
    $password_sha1 = sha1( $db->escapeString( $_POST['login_password'] ) );
    
    LoginWithProxy( $db, false, $username, $password_sha1 );
}

/**
 * @param $db SQLite3
 * @param $isProxy
 * @param $username
 * @param $password_sha1
 */
function LoginWithProxy($db, $isProxy, $username, $password_sha1) {
    if (session_status() == PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    
    session_start();
    
    if( $isProxy ) {
        $statement = $db->prepare("SELECT * FROM User WHERE UserName = :userName" );
        $statement->bindValue( ":userName", $username );
        $results = $statement->execute();
    } else {
        $statement = $db->prepare("SELECT * FROM User WHERE UserName = :userName AND Password  = :password");
        $statement->bindValue( ":userName", $username );
        $statement->bindValue( ":password", $password_sha1 );
        $results = $statement->execute();
    }
    
    $row = $results->fetchArray();
    $userExists = $row != false;
    
    if( $userExists ) {
        $firstName = $row['FirstName'];
        $lastName = $row['LastName'];
        $userID = $row['UserID'];
        $sodaBalance = $row['SodaBalance'];
        $snackBalance = $row['SnackBalance'];
        $credits = $row['Credits'];
        $slackID = $row['SlackID'];
        $anonName = $row['AnonName'];
        $showDiscontinued = $row['ShowDiscontinued'];
        $showCashOnly = $row['ShowCashOnly'];
        $showCredit = $row['ShowCredit'];
        $showItemStats = $row['ShowItemStats'];
        $showShelf = $row['ShowShelf'];
        $subscribeRestocks = $row['SubscribeRestocks'];
        $showTrending = $row['ShowTrending'];
        $hideCompletedRequests = $row['HideCompletedRequests'];

        $inactiveUser = $row['Inactive'] == 1;
        $isVendor = $row['IsVendor'] == 1;
        log_debug("Logging in with [$username] UserID[$userID] Soda[$sodaBalance] Snack[$snackBalance]");
        $_SESSION['LoggedIn'] = true;
        $_SESSION['UserName'] = $username;
        $_SESSION['FirstName'] = $firstName;
        $_SESSION['LastName'] = $lastName;
        $_SESSION['UserID'] = $userID;
        $_SESSION['SodaBalance'] = $sodaBalance;
        $_SESSION['SnackBalance'] = $snackBalance;
        $_SESSION['Credits'] = $credits;
        $_SESSION['SlackID'] = $slackID;
        $_SESSION['InactiveUser'] = $inactiveUser;
        $_SESSION['IsVendor'] = $isVendor;

        $_SESSION['AnonName'] = $anonName;
        $_SESSION['ShowDiscontinued'] = $showDiscontinued;
        $_SESSION['ShowCashOnly'] = $showCashOnly;
        $_SESSION['ShowCredit'] = $showCredit;
        $_SESSION['ShowItemStats'] = $showItemStats;
        $_SESSION['ShowShelf'] = $showShelf;
        $_SESSION['SubscribeRestocks'] = $subscribeRestocks;
        $_SESSION['ShowTrending'] = $showTrending;
        $_SESSION['HideCompletedRequests'] = $hideCompletedRequests;

        $_SESSION['IsAdmin'] = $username == 'mmiles';
    } else {
        $_SESSION['LoggedIn'] = false;
        $_SESSION['UserName'] = null;

        $_SESSION['InfoMessage'] = "Incorrect Password";
    }
}

function SetInfoMessage( $message ) {
    SetMessage( "InfoMessage", $message );
}

function SetUserErrorMessage( $message ) {
    SetMessage( "UserErrorMessage", $message );
}

function SetSystemErrorMessage( $message ) {
    SetMessage( "SystemErrorMessage", $message );
}

function SetMessage( $messageType, $message ) {
    if( isset( $_SESSION[$messageType] ) ) {
        $currentArray = $_SESSION[$messageType];
        $currentArray[] = $message;
        $_SESSION[$messageType] = $currentArray;
    } else {
        $newArray = array();
        $newArray[] = $message;
        $_SESSION[$messageType] = $newArray;
    }
}

function DisplayMessages() {
    echo "<div class='notification'>";
    $hasInfoMessages = DisplayMessage( "InfoMessage", "info" );
    $hasUserErrorMessages = DisplayMessage( "UserErrorMessage", "userError" );
    $hasSystemErrorMessages = DisplayMessage( "SystemErrorMessage", "systemError" );

    if( $hasInfoMessages || $hasUserErrorMessages || $hasSystemErrorMessages ) {
        echo "<button onclick='$(\".notification\").hide();' id='close-notification'>Close Messages</button>";
        echo "<script>setTimeout( function() { $('.notification').toggle('slide', { direction: 'up' }, 1000) }, 3000 );</script>";
    }

    echo "</div>";
}

function DisplayMessage( $messageType, $messageClass ) {
    if( isset( $_SESSION[$messageType])) {
        foreach( $_SESSION[$messageType] as $key => $value ) {
            echo "<div class='$messageClass'>";
            echo $value;
            echo "</div>";
        }
        unset( $_SESSION[$messageType] );
        return TRUE;
    }
    return FALSE;
}

function GetMessage( $messageType ) {
    if( isset( $_SESSION[$messageType])) {
        $retValue = "";
        foreach( $_SESSION[$messageType] as $key => $value ) {
            $retValue .= $value;
        }
        unset( $_SESSION[$messageType] );
        return $retValue;
    }
    return "NOTHING WAS SET";
}

function IsLoggedIn(){
    return isset( $_SESSION['LoggedIn'] ) && $_SESSION['LoggedIn'];
}

function IsInactive(){
    return isset( $_SESSION['InactiveUser'] ) && $_SESSION['InactiveUser'] == 1;
}

function IsAdminLoggedIn(){
    return isset( $_SESSION['IsAdmin'] ) && $_SESSION['IsAdmin'];
}

function IsVendor(){
    return isset( $_SESSION['IsVendor'] ) && $_SESSION['IsVendor'];
}

/**
 * @param $db SQLite3
 * @param $title
 */
function TrackVisit($db, $title){   
    if( IsAdminLoggedIn() || !IsLoggedIn() ) {
        // Don't track the admin, or logged out people
        return;
    }
    
    $ipAddress = $_SESSION['UserName'];
    
    $date = date('Y-m-d H:i:s', time());
    $agent = "Not Found";
    
    if(isset($_SERVER['HTTP_USER_AGENT']) == true) {
        $agent = $_SERVER['HTTP_USER_AGENT'];
    }

    $statement = $db->prepare( "INSERT INTO Visits (IP, Date, Agent, Page) VALUES( :ipAddress, :date, :agent, :title)" );
    $statement->bindValue(":ipAddress", $ipAddress );
    $statement->bindValue(":date", $date );
    $statement->bindValue(":agent", $agent );
    $statement->bindValue(":title", $title );
    $statement->execute();

    // Ignore me
    if( $ipAddress != "192.9.200.54" && $ipAddress  != "::1" && $ipAddress != "72.225.38.26" ) {
        sendSlackMessageToSlackBot($title . " visited by [" . $ipAddress . "] on [" . $agent . "]", ":earth_americas:", "SITE VISIT" );
    }
}
?>