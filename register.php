<?php
        include( "appendix.php" );
        
        $url = REGISTER_LINK;
        include( HEADER_PATH );
        
        $userMessage = "";
        $userError = "";
        

        
        if( $userError != "" ) {
               echo "<div style='text-align:center; padding:20px; font-size:1.2em; color:#FFF; border:3px solid #c71d1d; background-color:#de2c2c;'><span>$userError</span></div>";
        }
        
        if( $userMessage != "" ) {
            echo "<div style='text-align:center; padding:20px; font-size:1.2em; color:#FFF; border:3px solid #1dc73a; background-color:#2cde5b;'><span>$userMessage</span></div>";
        }
        
        echo "<div style='margin: 0 auto;' class='fancy'>";
        echo "<form style='width:400px; margin: 0 auto;' id='add_item_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        
        echo "<fieldset style='padding:0px 20px 20px 20px; font-size:1.2em; color:#FFF; border:3px solid #1d6cc7; background-color:#2c7ede;'>";
        echo "<h1 style='text-align:center;'>Create an Account</h1>";
        
        echo "<label for='UserName'>User Name*</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='text' autocorrect='off' autocapitalize='off' maxlength='15'; name='UserName'>";
        echo "<div style='font-size:0.9em; color:#e9ef00; line-height:15px; margin-bottom:20px;'>If you are a co-op <u>do not use your co-op account</u>, like 'qacoop2'. You will not be sharing balances with other future co-ops. Create an account with your name, like 'mmiles'.</div>";
        
        echo "<label for='Password'>Password*</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='password' autocorrect='off' autocapitalize='off' maxlength='40'; name='Password'>";
        echo "<div style='font-size:0.9em; color:#e9ef00; line-height:15px; margin-bottom:20px;'>Passwords are NOT stored in plain-text in the database. They are encrypted on account creation (so even I don't know them).</div>";
        
        echo "<label for='PasswordAgain'>Confirm Password*</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='password' autocorrect='off' autocapitalize='off' maxlength='40'; name='PasswordAgain'>";
        
        echo "<label for='FirstName'>First Name*</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='text' autocorrect='off' autocapitalize='off' maxlength='20' name='FirstName'>";
        
        echo "<label for='LastName'>Last Name*</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='text' autocorrect='off' autocapitalize='off' maxlength='20' name='LastName'>";

        echo "<label for='PhoneNumber'>Phone Number</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='text' autocorrect='off' autocapitalize='off' maxlength='20' name='PhoneNumber'>";
        echo "<span style='font-size:0.9em; color:#e9ef00; line-height:15px;'>If you are a co-op and you leave RSA without paying off your balance it would be nice if I could contact you. :p</span>";

        echo "<input type='hidden' name='redirectURL' value='" . REGISTER_LINK . "'/>";

        echo "<input type='hidden' name='RegisterUser' value='RegisterUser'/>";
        
        echo "<input class='ui-button' style='padding:10px; text-align:center; width:100%; margin: 40px 0px 0px 0px' type='submit' name='Register_User' value='Register'/><br>";

        echo "</fieldset>";
        echo "</form>";
        
        echo "</div>";
        $db->close();
?>
</body>