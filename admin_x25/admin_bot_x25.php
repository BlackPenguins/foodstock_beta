<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_BOT_LINK;
    include( HEADER_PATH );
    
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
    echo "<form style='display:inline;' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
    echo "<textarea name='BotMessage' rows='10' cols='100'></textarea><br>";
    echo "Bot Name: <input name='BotName' value='FoodStockBot'/><br>";
    echo "Icon: <input name='Emoji' value='sodabot'/><br>";
    echo "<input type='submit' value='Send Message!'</input>";
    echo "<input type='hidden' name='SendBot' value='SendBot'/><br>";
    echo "<input type='hidden' name='redirectURL' value='" . ADMIN_BOT_LINK . "'/><br>";
    echo "</form>";
    echo "</span>";
   
?>

</body>