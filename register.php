<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="style.css"/>
<title>Register</title>
</head>

<body style='margin:0; padding:0;'>
<?php
        $db = new SQLite3("db/item.db");
        if (!$db) die ($error);
        
        date_default_timezone_set('America/New_York');
        
        $userMessage = "";
        $userError = "";
        
        if( isset( $_POST['register_user'] ) ) {
        	
        	if( $_POST['UserName'] == "" || $_POST['Password'] == "" || $_POST['FirstName'] == "" || $_POST['LastName'] == "" ) {
        		$userError = "You must provide User Name, Password, First Name, and Last Name.";
        	} else {
	        	$username = trim($_POST["UserName"]);
	        	$password_sha1 = sha1($_POST["Password"]);
	        	$firstName = $_POST["FirstName"];
	        	$lastName = $_POST["LastName"];
	        	$phoneNumber = "";
	        	
	        	if( isset( $_POST['PhoneNumber'] ) ) {
	        		$phoneNumber = $_POST["PhoneNumber"];
	        	}
	        	$date = date('Y-m-d H:i:s');
	        	 
	        	$results = $db->query("SELECT * FROM User WHERE UserName = '" . $username . "'");
	        	
	        	$userExists = $results->fetchArray() != false;
	        	
	        	if( $userExists ) {
	        		$userError = "User <b>$username</b> already exists!";
	        	} else {
	        		$db->exec("INSERT INTO User (UserName, Password, FirstName, LastName, PhoneNumber, DateCreated, Balance) VALUES( '$username', '$password_sha1', '$firstName', '$lastName', '$phoneNumber', '$date', 0.00)");
	        		$userMessage = "Registration complete! User <b>$username</b> has been created.";
	        	}
        	}
        }
        
        if( $userError != "" ) {
       		echo "<div style='text-align:center; padding:20px; font-size:1.2em; color:#FFF; border:3px solid #c71d1d; background-color:#de2c2c;'><span>$userError</span></div>";
        }
        
        if( $userMessage != "" ) {
        	echo "<div style='text-align:center; padding:20px; font-size:1.2em; color:#FFF; border:3px solid #1dc73a; background-color:#2cde5b;'><span>$userMessage</span></div>";
        }
        
        echo "<div style='margin: 0 auto;' class='fancy'>";
        echo "<form style='width:300px; margin: 0 auto;' id='add_item_form' enctype='multipart/form-data' action='register.php' method='POST'>";
        
        echo "<fieldset style='padding:0px 20px 20px 20px; font-size:1.2em; color:#FFF; border:3px solid #1d6cc7; background-color:#2c7ede;'>";
        echo "<h1>Create an Account</h1>";
        
        echo "<div><label style='padding:5px 0px;' for='UserName'>User Name*</label></div>";
        echo "<div><input style='width:270px;' type='text' autocorrect='off' autocapitalize='off' maxlength='15'; name='UserName'></div>";
        echo "<span style='font-size:0.9em; color:#e9ef00; line-height:15px;'>If you are a co-op <u>do not use your co-op account</u>, like 'qacoop2'. You will not be sharing balances with other future co-ops. Create an account with your name, like 'mmiles'.</span>";
        
        echo "<div style='margin: 20px 0px 2px 0px;'><label style='padding:5px 0px;' for='Password'>Password*</label></div>";
        echo "<div><input style='width:270px;' type='text' autocorrect='off' autocapitalize='off' maxlength='40'; name='Password'></div>";
        echo "<span style='font-size:0.9em; color:#e9ef00; line-height:15px;'>Passwords are NOT stored in plain-text in the database. They are encrypted on account creation.</span>";
        
        echo "<div style='margin: 20px 0px 2px 0px;'><label style='padding:5px 0px;' for='FirstName'>First Name*</label></div>";
        echo "<div><input style='width:270px;' type='text' autocorrect='off' autocapitalize='off' maxlength='20'; name='FirstName'></div>";
        
        echo "<div style='margin: 20px 0px 2px 0px;'><label style='padding:5px 0px;' for='LastName'>Last Name*</label></div>";
        echo "<div><input style='width:270px;' type='text' autocorrect='off' autocapitalize='off' maxlength='20'; name='LastName'></div>";

        echo "<div style='margin: 20px 0px 2px 0px;'><label style='padding:5px 0px;' for='PhoneNumber'>Phone Number</label></div>";
        echo "<div><input style='width:270px;' type='text' autocorrect='off' autocapitalize='off' maxlength='20'; name='PhoneNumber'></div>";
        echo "<span style='font-size:0.9em; color:#e9ef00; line-height:15px;'>If you are a co-op and you leave RSA without paying off your balance it would be nice if I could contact you. :p</span>";
        
        echo "<input type='hidden' name='register_user' value='register_user'/>";
        
        echo "<input class='ui-button' style='padding:10px; text-align:center; width:100%; margin: 40px 0px 0px 0px' type='submit' name='Register_User' value='Register'/><br>";

        echo "</fieldset>";
        echo "</form>";
        
        echo "<div style='width:300px; margin: 0 auto; padding:10px; text-align:center;'><b><a href='sodastock.php'>Return to Sodastock</a></b></div>";
        
        echo "</div>";
    	$db->close();
?>
</body>