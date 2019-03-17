<?php
include(__DIR__ . "/../appendix.php" );

function Login($db) {
    session_start();
    
    if( isset( $_SESSION['LoggedIn'] ) && $_SESSION['LoggedIn'] == true ) {
        // Already logged in - Recache everything
        $results = $db->query("SELECT * FROM User WHERE UserName = '" . $_SESSION['UserName'] . "'");
        $row = $results->fetchArray();
        $_SESSION['SlackID'] = $row['SlackID'];
        $_SESSION['SodaBalance'] = $row['SodaBalance'];
        $_SESSION['SnackBalance'] = $row['SnackBalance'];
        $_SESSION['InactiveUser'] = $row['Inactive'] == 1;
        error_log( "Recached SlackID [" . $_SESSION['SlackID'] . "] for [" . $_SESSION['UserName'] . "]" );
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

function LoginWithProxy($db, $isProxy, $username, $password_sha1) {
    if (session_status() == PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    
    session_start();
    
    if( $isProxy ) {
        $results = $db->query("SELECT * FROM User WHERE UserName = '" . $username . "'" );
    } else {
        $results = $db->query("SELECT * FROM User WHERE UserName = '" . $username . "' AND Password  = '" . $password_sha1 . "'");
    }
    
    $row = $results->fetchArray();
    $userExists = $row != false;
    
    if( $userExists ) {
        $firstName = $row['FirstName'];
        $lastName = $row['LastName'];
        $userID = $row['UserID'];
        $sodaBalance = $row['SodaBalance'];
        $snackBalance = $row['SnackBalance'];
        $slackID = $row['SlackID'];
        $inactiveUser = $row['Inactive'] == 1;
        error_log("Logging in with [$username] [$userID] [$sodaBalance][$snackBalance]");
        $_SESSION['LoggedIn'] = true;
        $_SESSION['UserName'] = $username;
        $_SESSION['FirstName'] = $firstName;
        $_SESSION['LastName'] = $lastName;
        $_SESSION['UserID'] = $userID;
        $_SESSION['SodaBalance'] = $sodaBalance;
        $_SESSION['SnackBalance'] = $snackBalance;
        $_SESSION['SlackID'] = $slackID;
        $_SESSION['InactiveUser'] = $inactiveUser;
        $_SESSION['IsAdmin'] = $username == 'mmiles';
    } else {
        $_SESSION['LoggedIn'] = false;
        $_SESSION['UserName'] = null;
        
        echo "<div style='padding:30px; font-weight:bold; font-size:1.3em;'>Incorrect password!</div>";
    }
}

function DisplayUserMessage() {
    
    
    if( isset( $_SESSION['UserMessage'])) {
        echo "<div id='notification'>";
        echo $_SESSION['UserMessage'];
        echo "<button style='float:right; margin: 10px;' onclick='$(\"#notification\").hide();' id='close-notification'>Close Messages</button>";
        echo "</div>";
        
//         echo "<script>alert('" . $_SESSION['UserMessage'] . "');</script>";
        unset( $_SESSION['UserMessage'] );
    }
}

function IsLoggedIn(){        
       return $_SESSION['LoggedIn'];
}

function IsAdminLoggedIn(){
    return isset( $_SESSION['IsAdmin'] ) && $_SESSION['IsAdmin'];
}

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
    
    $db->exec("INSERT INTO Visits (IP, Date, Agent, Page) VALUES( '$ipAddress', '$date', '$agent', '$title')");
    
    // Ignore me
    if( $ipAddress != "192.9.200.54" && $ipAddress  != "::1" && $ipAddress != "72.225.38.26" ) {
        sendSlackMessageToSlackBot($title . " visited by [" . $ipAddress . "] on [" . $agent . "]", ":earth_americas:", "SITE VISIT" );
    }
}

function addToValue( $db, $tableName, $columnName, $valueToAdd, $whereClause, $doAdd ) {
    $results = $db->query("SELECT $columnName FROM $tableName $whereClause" );
    $row = $results->fetchArray();
    $columnValue = $row[$columnName];
    
    $finalValue;
    if( $doAdd ) {
        $finalValue = round( $columnValue + $valueToAdd, 2 );
    } else {
        $finalValue = round( $columnValue - $valueToAdd, 2 );
    }
    
    error_log( "[$tableName/$columnName] TABLE COLUMN --- [$columnValue] " . ( $doAdd ? "+" : "-" ) . " [$valueToAdd] = [$finalValue]" );
    
    return $finalValue;
}
?>