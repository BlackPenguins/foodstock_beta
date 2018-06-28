<?php
date_default_timezone_set('America/New_York');

// ------------------------------------
// HANDLE QUERIES
// ------------------------------------
if(isset($_POST['token'])) 
{
    if( $_POST['token'] == "Abz05Js8of24rIVP8Bpf0QUi" ) {
        error_log("---------------- INCOMING NEW REQUEST" );
        error_log("TOKEN: [" . $_POST["token"] . "]" );
        error_log("TEAM ID: [" . $_POST["team_id"] . "]" );
        error_log("TEAM DOMAIN: [" . $_POST["team_domain"] . "]" );
        error_log("CHANNEL ID: [" . $_POST["channel_id"] . "]" );
        error_log("CHANNEL NAME: [" . $_POST["channel_name"] . "]" );
        error_log("USER ID: [" . $_POST["user_id"] . "]" );
        error_log("USER NAME: [" . $_POST["user_name"] . "]" );
        error_log("TEXT: [" . $_POST["text"] . "]" );
        error_log("RESPONSE URL: [" . $_POST["response_url"] . "]" );
        
        $db = new SQLite3('db/item.db');
        if (!$db) die ($error);

        $slackMessageItems = "";
        
        $results = $db->query("SELECT ID, BackStockQuantity, ShelfQuantity, Price, Name FROM Item WHERE Retired = 0 AND Hidden != 1 AND (BackstockQuantity != 0 OR ShelfQuantity != 0) Order By Name");
        while ($row = $results->fetchArray()) {
            $backstockQuantity = $row[1];
            $shelfQuantity = $row[2];
            $price = $row[3];
            $itemName = $row[4];
            
            $slackMessageItems = $slackMessageItems . "•  *" . $itemName . ":* *" . $shelfQuantity . " cans* (" . ( $shelfQuantity + $backstockQuantity ) . " total)\n";
        }

        echo "SODA LIST\n\n$slackMessageItems";
    } else {
        error_log("UNAUTHORIZED ATTEMPT: " . $_POST['token'] );
    }
}
?>