<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_BOT_LINK;
    include( HEADER_PATH );

    $botName = "FoodStockBot";
    $emoji = "sodabot";

    if( isset($_SESSION['BotName'] ) ) {
        $botName = $_SESSION['BotName'];
    }

    if( isset($_SESSION['Emoji'] ) ) {
        $emoji = $_SESSION['Emoji'];
    }

    echo "<span class='admin_box'>";
    echo "<form style='display:inline;' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
    echo "<textarea name='BotMessage' rows='10' cols='100'></textarea><br>";
    echo "Bot Name: <input name='BotName' value='$botName'/><br>";
    echo "Icon: <input name='Emoji' value='$emoji'/><br>";
    echo "<input type='submit' value='Send Message!'</input>";
    echo "<input type='hidden' name='SendBot' value='SendBot'/><br>";
    echo "<input type='hidden' name='redirectURL' value='" . ADMIN_BOT_LINK . "'/><br>";
    echo "</form>";
    echo "</span>";
   
?>

</body>