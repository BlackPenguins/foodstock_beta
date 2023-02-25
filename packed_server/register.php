<?php
        include( "appendix.php" );
        
        $url = REGISTER_LINK;
        include( HEADER_PATH );
        
        echo "<div style='margin: 0 auto;' class='inline_form'>";
        echo "<form style='width:400px; margin: 0 auto;' id='add_item_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
        
        echo "<fieldset>";
        echo "<h1 style='text-align:center;'>Create an Account</h1>";
        
        echo "<label for='UserName'>User Name*</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='text' autocorrect='off' autocapitalize='off' maxlength='15'; name='UserName'>";
        echo "<div class='helptext'>If you are a co-op <u>do not use your co-op account</u>, like 'qacoop2'. You will not be sharing balances with other future co-ops. Create an account with your first/last name like 'mmiles' for Matt Miles.</div>";
        
        echo "<label for='Password'>Password*</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='password' autocorrect='off' autocapitalize='off' maxlength='40'; name='Password'>";
        echo "<div class='helptext'>Passwords are NOT stored in plain-text in the database. They are encrypted on account creation (so even I don't know them).</div>";
        
        echo "<label for='PasswordAgain'>Confirm Password*</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='password' autocorrect='off' autocapitalize='off' maxlength='40'; name='PasswordAgain'>";
        
        echo "<label for='FirstName'>First Name*</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='text' autocorrect='off' autocapitalize='off' maxlength='20' name='FirstName'>";
        
        echo "<label for='LastName'>Last Name*</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='text' autocorrect='off' autocapitalize='off' maxlength='20' name='LastName'>";

        echo "<label for='PhoneNumber'>Phone Number</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='text' autocorrect='off' autocapitalize='off' maxlength='20' name='PhoneNumber'>";
        echo "<span class='helptext'>If you are a co-op and you leave RSA without paying off your balance it would be nice if I could contact you.</span>";

        echo "<label for='CAPTCHA'>Humans only - answer this question.</label>";
        echo "<input style='width:100%;' class='text ui-widget-content ui-corner-all' type='text' autocorrect='off' autocapitalize='off' maxlength='20' name='CAPTCHA'>";
        echo "<span class='helptext'>As SodaStock gains traction and we get closer to global market domination we are attracting bots that are registering. So answer this question to prove you are human:<br><b>What are the 3 initials of our company?</b> <br>Your user account will also be disabled at first until Matt manually activates it for you. Sorry for the inconvenience.</span>";

        echo "<input type='hidden' name='redirectURL' value='" . REGISTER_LINK . "'/>";

        echo "<input type='hidden' name='RegisterUser' value='RegisterUser'/>";
        
        echo "<input class='button' type='submit' name='Register_User' value='Register'/><br>";

        echo "</fieldset>";
        echo "</form>";
        
        echo "</div>";
        $db->close();
?>
</body>