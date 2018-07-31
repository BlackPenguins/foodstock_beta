<?php
    $statement = "";
    
    // Add Completed and ID to Requests
    
    /*
         $statement = "UPDATE Item SET TotalExpenses = TotalExpenses - 3.49, BackstockQuantity = BackstockQuantity - 12, TotalCans = TotalCans - 12 where ID = 29";
        
        $statement = "UPDATE ITEM SET Price = 0.40 WHERE name = 'Swiss Miss Dark Chocolate';";
        $statement = "ALTER TABLE ITEM ADD COLUMN ImageURL TEXT;"
        $statement = "ALTER TABLE ITEM ADD COLUMN ThumbURL TEXT;"
        $statement = "ALTER TABLE ITEM ADD COLUMN UnitName TEXT;"
        $statement = "UPDATE ITEM SET Retired = 1 WHERE name = 'Cherry Zero';";
        
        $statement = "CREATE TABLE Item(id integer PRIMARY KEY AUTOINCREMENT, name text, date text, chartcolor text, totalcans integer, backstockquantity integer, shelfquantity integer, price real,  totalincome real, totalexpenses real )";
        $statement = "CREATE TABLE Restock(itemid integer, date text, numberofcans integer, cost real )";
        $statement = "CREATE TABLE Daily_Amount(itemid integer, date text, backstockquantitybefore integer, backstockquantity integer, shelfquantitybefore integer, shelfquantity integer, price real, restock integer )";
        $statement = "DROP TABLE Item;";
        
        
        
        function slackPrivate() {
    // Find the slack user ID
    String slackUserIDQuery = "select slack_id from users where username =  '" + slackUserName + "'";
    String slackID = null;
    
    try ( Statement statement = dbConn.createStatement(); 
           ResultSet slackUserIDResults = statement.executeQuery( slackUserIDQuery ) ) {
        
        while (slackUserIDResults.next()) {
            slackID = slackUserIDResults.getString("slack_id");
        }
    } catch (SQLException e) {
        logger.error( "SQL: [" + e.getLocalizedMessage() + "]" );
    }
    
    if( slackID == null ) {
        slackMessage = "*I'M LOST AND CONFUSED! :tired_face: I was unable to find the slack ID for the recipient of this message. Please update the USERS table in Prod DB: [" + slackUserName + "]*\n\n" + slackMessage + "\n\n<@" + slackUserName + ">";
        slackFallbackMessage = "I'M LOST AND CONFUSED! Fix me.";
        sendSlackMessageToChannel( slackMessage, slackFallbackMessage, passed );
        logger.error( "Could not send slack DM message because Slack User ID was not found or NULL." );
    } else {
    
        HttpURLConnection connection = null;
        try {
            String slackIcon = "";
            String color = "";
            String botName = "";
            
            if( passed ) {
                slackFallbackMessage = "PASSED\n" + slackFallbackMessage;
                botName = "Crushinator - PASSED";
                slackIcon = ":smiley_green:";
                color = "#90EE90";
            } else {
                slackFallbackMessage = "FAILED\n" + slackFallbackMessage;
                botName = "Crushinator - FAILED";
                slackIcon = ":rage:";
                color = "#EE4545";
            }
            
            org.json.JSONObject userSessionJSON = sendRequestToSlack( "https://slack.com/api/im.open?user=" + slackID );
            
            org.json.JSONObject channel = userSessionJSON.getJSONObject( "channel" );
            String sessionID = channel.getString( "id" );
            
            JSONObject attachment = new JSONObject();
            attachment.put( "fallback", slackFallbackMessage );
            attachment.put( "text", slackMessage );
            attachment.put( "color", color );
            attachment.put( "mrkdwn_in", "[\"text\"]");
            
            String attachmentEncoded = URLEncoder.encode( "[" + attachment.toString() + "]", "UTF-8");
            String slackIconEncoded = URLEncoder.encode( slackIcon, "UTF-8");
            String botNameEncoded = URLEncoder.encode( botName, "UTF-8");
            
            org.json.JSONObject messageJSON = sendRequestToSlack( "https://slack.com/api/chat.postMessage?as_user=false&username=" + botNameEncoded + "&attachments=" + attachmentEncoded + "&icon_emoji=" + slackIconEncoded + "&channel=" + sessionID );

            
            logger.info( "User Session Response from Slack (api): [" + userSessionJSON.toString() + "]" );
            logger.info( "Message Response from Slack (api): [" + messageJSON.toString() + "]" );
            
        } catch (Exception e) {
            logger.error( e.getLocalizedMessage() );
        } finally {
            if (connection != null) {
                connection.disconnect();
            }
        }
    }
}
    */
    
    $db = new SQLite3("db/item.db");
    if (!$db) die ($error);
    
//     

    if( $statement != "" ) {
        echo "Executing......<br>";
    
        $db->exec($statement);
    
        echo "DONE!<br><br>";
    }
    
//     fixIncomes($db);
//     fixPurchaseHistoryLink($db);
    
    
    function fixPurchaseHistoryLink($db) {
        $results = $db->query("SELECT * from Purchase_history WHERE DailyAmountID IS NULL ORDER BY Date DESC");
        while ($row = $results->fetchArray()) {
            $date = $row['Date'];
            $itemID = $row['ItemID'];
            $purchaseID = $row['ID'];
    
            $resultsInner = $db->query("SELECT * from Daily_Amount WHERE Date = '$date' AND ItemID = $itemID AND PurchaseID = -1 ORDER BY Date DESC LIMIT 1");
            $rowInner = $resultsInner->fetchArray();
            $dailyAmountID = $rowInner['ID'];
    
            echo "SELECT * from Daily_Amount WHERE Date = '$date' AND ItemID = $itemID AND PurchaseID = -1 ORDER BY Date DESC LIMIT 1<br>";
    
            echo "UPDATE Daily_Amount SET PurchaseID = $purchaseID WHERE ID = $dailyAmountID<br>";
            echo "UPDATE Purchase_History SET DailyAmountID = $dailyAmountID WHERE ID = $purchaseID<br>";
    
            $db->exec( "UPDATE Daily_Amount SET PurchaseID = $purchaseID WHERE ID = $dailyAmountID" );
            $db->exec( "UPDATE Purchase_History SET DailyAmountID = $dailyAmountID WHERE ID = $purchaseID" );
        }
    }
    function fixIncomes($db) {
        
        echo "FIX INCOMES<br><br>";
        
        $dailyAmountArray = array();
        $dailyAmountArray[]= array(100, '2018-07-05 10:37:30',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-05 10:37:30',5, 0, 0, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-05 10:37:30',0, 4, 4, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-05 10:37:30',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-05 10:37:30',0, 12, 12, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-05 10:37:30',0, 12, 12, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-05 10:37:30',0, 14, 14, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-05 10:37:30',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-05 10:37:30',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-05 10:37:30',0, 15, 15, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-05 10:37:30',0, 16, 16, 0.25, 0);
        $dailyAmountArray[]= array(131, '2018-07-05 10:37:30',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-05 10:37:30',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(67, '2018-07-05 10:37:30',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-05 10:37:30',0, 6, 6, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-05 10:37:30',0, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-05 10:37:30',0, 16, 16, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-05 10:37:30',0, 12, 12, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-05 10:37:30',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-05 10:37:30',0, 10, 10, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-05 10:37:30',14, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-05 10:37:30',12, 0, 6, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-05 10:37:30',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-05 10:37:30',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-05 10:37:30',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-05 10:37:30',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-05 10:37:30',0, 3, 3, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-05 10:37:30',0, 45, 45, 0.2, 0);
        $dailyAmountArray[]= array(119, '2018-07-05 10:37:30',64, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(113, '2018-07-05 10:37:30',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(115, '2018-07-05 10:37:30',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-05 10:37:30',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-05 10:37:30',0, 7, 7, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-05 10:37:30',10, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(100, '2018-07-09 16:42:55',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-09 16:42:55',0, 0, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-09 16:42:55',0, 3, 3, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-09 16:42:55',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-09 16:42:55',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-09 16:42:55',16, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-09 16:42:55',0, 14, 14, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-09 16:42:55',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-09 16:42:55',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-09 16:42:55',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-09 16:42:55',0, 15, 15, 0.25, 0);
        $dailyAmountArray[]= array(131, '2018-07-09 16:42:55',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-09 16:42:55',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(67, '2018-07-09 16:42:55',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-09 16:42:55',0, 6, 6, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-09 16:42:55',0, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-09 16:42:55',10, 16, 16, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-09 16:42:55',0, 11, 11, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-09 16:42:55',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-09 16:42:55',0, 9, 9, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-09 16:42:55',14, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-09 16:42:55',12, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-09 16:42:55',18, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-09 16:42:55',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-09 16:42:55',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-09 16:42:55',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-09 16:42:55',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-09 16:42:55',0, 3, 3, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-09 16:42:55',0, 42, 42, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-09 16:42:55',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-09 16:42:55',64, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(113, '2018-07-09 16:42:55',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(115, '2018-07-09 16:42:55',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-09 16:42:55',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-09 16:42:55',0, 7, 7, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-09 16:42:55',10, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-09 16:42:55',15, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-16 11:03:46',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-16 11:03:46',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-16 11:03:46',30, 2, 1, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-16 11:03:46',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-16 11:03:46',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-16 11:03:46',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-16 11:03:46',20, 14, 0, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-16 11:03:46',25, 0, 0, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-16 11:03:46',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-16 11:03:46',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-16 11:03:46',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-16 11:03:46',0, 15, 15, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-16 11:03:46',106, 0, 0, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-16 11:03:46',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-16 11:03:46',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-16 11:03:46',15, 0, 0, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-16 11:03:46',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-16 11:03:46',0, 6, 6, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-16 11:03:46',12, 11, 0, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-16 11:03:46',30, 16, 0, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-16 11:03:46',0, 9, 9, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-16 11:03:46',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-16 11:03:46',12, 7, 0, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-16 11:03:46',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-16 11:03:46',12, 4, 0, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-16 11:03:46',18, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-16 11:03:46',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-16 11:03:46',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-16 11:03:46',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-16 11:03:46',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-16 11:03:46',12, 3, 0, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-16 11:03:46',0, 41, 41, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-16 11:03:46',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-16 11:03:46',64, 13, 13, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-16 11:03:46',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-16 11:03:46',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-16 11:03:46',0, 1, 1, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-16 11:03:46',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-16 11:03:46',15, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-16 11:07:16',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-16 11:07:16',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-16 11:07:16',0, 1, 31, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-16 11:07:16',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-16 11:07:16',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-16 11:07:16',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-16 11:07:16',0, 0, 20, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-16 11:07:16',0, 0, 25, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-16 11:07:16',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-16 11:07:16',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-16 11:07:16',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-16 11:07:16',0, 15, 15, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-16 11:07:16',0, 0, 106, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-16 11:07:16',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-16 11:07:16',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-16 11:07:16',0, 0, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-16 11:07:16',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-16 11:07:16',0, 6, 6, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-16 11:07:16',0, 0, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-16 11:07:16',0, 0, 40, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-16 11:07:16',0, 9, 9, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-16 11:07:16',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-16 11:07:16',0, 0, 12, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-16 11:07:16',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-16 11:07:16',6, 0, 6, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-16 11:07:16',12, 0, 6, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-16 11:07:16',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-16 11:07:16',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-16 11:07:16',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-16 11:07:16',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-16 11:07:16',0, 0, 12, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-16 11:07:16',0, 41, 41, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-16 11:07:16',0, 0, 20, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-16 11:07:16',64, 13, 13, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-16 11:07:16',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-16 11:07:16',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-16 11:07:16',0, 1, 0, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-16 11:07:16',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-16 11:07:16',5, 0, 10, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-16 11:11:11',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-16 11:11:11',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-16 11:11:11',0, 31, 31, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-16 11:11:11',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-16 11:11:11',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-16 11:11:11',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-16 11:11:11',0, 20, 20, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-16 11:11:11',0, 25, 25, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-16 11:11:11',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-16 11:11:11',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-16 11:11:11',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-16 11:11:11',0, 15, 15, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-16 11:11:11',0, 106, 106, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-16 11:11:11',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-16 11:11:11',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-16 11:11:11',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-16 11:11:11',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-16 11:11:11',0, 6, 6, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-16 11:11:11',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-16 11:11:11',0, 40, 40, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-16 11:11:11',0, 9, 9, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-16 11:11:11',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-16 11:11:11',0, 12, 11, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-16 11:11:11',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-16 11:11:11',6, 6, 6, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-16 11:11:11',12, 6, 6, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-16 11:11:11',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-16 11:11:11',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-16 11:11:11',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-16 11:11:11',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-16 11:11:11',0, 12, 12, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-16 11:11:11',0, 41, 41, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-16 11:11:11',0, 20, 20, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-16 11:11:11',64, 13, 13, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-16 11:11:11',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-16 11:11:11',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(85, '2018-07-16 11:11:11',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-16 11:11:11',5, 10, 10, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-18 15:11:31',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-18 15:11:31',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-18 15:11:31',0, 29, 29, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-18 15:11:31',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-18 15:11:31',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-18 15:11:31',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-18 15:11:31',0, 19, 19, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-18 15:11:31',0, 23, 23, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-18 15:11:31',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-18 15:11:31',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-18 15:11:31',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-18 15:11:31',0, 14, 14, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-18 15:11:31',0, 101, 101, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-18 15:11:31',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-18 15:11:31',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-18 15:11:31',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-18 15:11:31',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-18 15:11:31',0, 4, 4, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-18 15:11:31',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-18 15:11:31',0, 40, 40, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-18 15:11:31',0, 8, 8, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-18 15:11:31',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-18 15:11:31',0, 11, 11, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-18 15:11:31',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-18 15:11:31',6, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-18 15:11:31',12, 6, 6, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-18 15:11:31',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-18 15:11:31',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-18 15:11:31',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-18 15:11:31',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-18 15:11:31',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-18 15:11:31',0, 41, 41, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-18 15:11:31',0, 19, 19, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-18 15:11:31',64, 12, 0, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-18 15:11:31',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-18 15:11:31',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(85, '2018-07-18 15:11:31',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-18 15:11:31',5, 9, 9, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-18 15:11:51',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-18 15:11:51',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-18 15:11:51',0, 29, 29, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-18 15:11:51',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-18 15:11:51',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-18 15:11:51',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-18 15:11:51',0, 19, 19, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-18 15:11:51',0, 23, 23, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-18 15:11:51',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-18 15:11:51',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-18 15:11:51',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-18 15:11:51',0, 14, 14, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-18 15:11:51',0, 101, 101, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-18 15:11:51',0, 7, 7, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-18 15:11:51',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-18 15:11:51',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-18 15:11:51',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-18 15:11:51',0, 4, 4, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-18 15:11:51',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-18 15:11:51',0, 40, 40, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-18 15:11:51',0, 8, 8, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-18 15:11:51',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-18 15:11:51',0, 11, 11, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-18 15:11:51',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-18 15:11:51',6, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-18 15:11:51',12, 6, 6, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-18 15:11:51',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-18 15:11:51',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-18 15:11:51',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-18 15:11:51',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-18 15:11:51',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-18 15:11:51',0, 41, 41, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-18 15:11:51',0, 19, 19, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-18 15:11:51',32, 0, 32, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-18 15:11:51',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-18 15:11:51',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(85, '2018-07-18 15:11:51',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-18 15:11:51',5, 9, 9, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-23 11:11:08',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-23 11:11:08',0, 5, 5, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-23 11:11:08',0, 28, 28, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-23 11:11:08',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-23 11:11:08',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-23 11:11:08',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-23 11:11:08',0, 19, 19, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-23 11:11:08',0, 23, 23, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-23 11:11:08',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-23 11:11:08',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-23 11:11:08',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-23 11:11:08',0, 14, 14, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-23 11:11:08',0, 95, 95, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-23 11:11:08',0, 6, 6, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-23 11:11:08',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-23 11:11:08',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-23 11:11:08',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-23 11:11:08',0, 4, 4, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-23 11:11:08',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-23 11:11:08',0, 37, 37, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-23 11:11:08',0, 7, 7, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-23 11:11:08',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-23 11:11:08',0, 10, 10, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-23 11:11:08',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-23 11:11:08',6, 5, 5, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-23 11:11:08',12, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-23 11:11:08',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-23 11:11:08',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-23 11:11:08',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-23 11:11:08',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-23 11:11:08',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-23 11:11:08',0, 39, 39, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-23 11:11:08',0, 19, 19, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-23 11:11:08',32, 28, 28, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-23 11:11:08',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-23 11:11:08',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-23 11:11:08',18, 0, 18, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-23 11:11:08',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-23 11:11:08',5, 9, 9, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-26 09:23:27',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-26 09:23:27',0, 4, 4, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-26 09:23:27',0, 27, 27, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-26 09:23:27',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-26 09:23:27',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-26 09:23:27',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-26 09:23:27',0, 19, 19, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-26 09:23:27',0, 22, 22, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-26 09:23:27',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-26 09:23:27',0, 7, 7, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-26 09:23:27',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-26 09:23:27',0, 14, 14, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-26 09:23:27',0, 89, 89, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-26 09:23:27',0, 6, 6, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-26 09:23:27',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-26 09:23:27',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-26 09:23:27',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-26 09:23:27',0, 4, 4, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-26 09:23:27',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-26 09:23:27',0, 37, 37, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-26 09:23:27',0, 4, 4, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-26 09:23:27',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-26 09:23:27',0, 10, 10, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-26 09:23:27',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-26 09:23:27',6, 4, 0, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-26 09:23:27',12, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-26 09:23:27',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-26 09:23:27',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-26 09:23:27',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-26 09:23:27',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-26 09:23:27',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-26 09:23:27',0, 37, 37, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-26 09:23:27',0, 18, 18, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-26 09:23:27',32, 28, 28, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-26 09:23:27',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-26 09:23:27',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-26 09:23:27',18, 17, 17, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-26 09:23:27',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-26 09:23:27',5, 9, 9, 0.5, 0);
        $dailyAmountArray[]= array(100, '2018-07-26 09:23:40',0, 47, 47, 0.1, 0);
        $dailyAmountArray[]= array(108, '2018-07-26 09:23:40',0, 4, 4, 0.3, 0);
        $dailyAmountArray[]= array(68, '2018-07-26 09:23:40',0, 27, 27, 0.35, 0);
        $dailyAmountArray[]= array(124, '2018-07-26 09:23:40',0, 13, 13, 0.25, 0);
        $dailyAmountArray[]= array(117, '2018-07-26 09:23:40',0, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(87, '2018-07-26 09:23:40',16, 10, 10, 0.35, 0);
        $dailyAmountArray[]= array(88, '2018-07-26 09:23:40',0, 19, 19, 0.35, 0);
        $dailyAmountArray[]= array(135, '2018-07-26 09:23:40',0, 22, 22, 0.35, 0);
        $dailyAmountArray[]= array(118, '2018-07-26 09:23:40',20, 0, 0, 0.4, 0);
        $dailyAmountArray[]= array(69, '2018-07-26 09:23:40',0, 7, 7, 0.25, 0);
        $dailyAmountArray[]= array(121, '2018-07-26 09:23:40',0, 14, 14, 0.4, 0);
        $dailyAmountArray[]= array(129, '2018-07-26 09:23:40',0, 14, 14, 0.25, 0);
        $dailyAmountArray[]= array(65, '2018-07-26 09:23:40',0, 89, 89, 0.2, 0);
        $dailyAmountArray[]= array(131, '2018-07-26 09:23:40',0, 6, 6, 0.7, 0);
        $dailyAmountArray[]= array(130, '2018-07-26 09:23:40',0, 10, 10, 0.25, 0);
        $dailyAmountArray[]= array(89, '2018-07-26 09:23:40',0, 15, 15, 0.6, 0);
        $dailyAmountArray[]= array(67, '2018-07-26 09:23:40',0, 5, 5, 0.85, 0);
        $dailyAmountArray[]= array(123, '2018-07-26 09:23:40',0, 4, 4, 0.4, 0);
        $dailyAmountArray[]= array(84, '2018-07-26 09:23:40',0, 12, 12, 0.25, 0);
        $dailyAmountArray[]= array(86, '2018-07-26 09:23:40',0, 37, 37, 0.75, 0);
        $dailyAmountArray[]= array(101, '2018-07-26 09:23:40',0, 4, 4, 0.2, 0);
        $dailyAmountArray[]= array(82, '2018-07-26 09:23:40',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(73, '2018-07-26 09:23:40',0, 10, 10, 0.4, 0);
        $dailyAmountArray[]= array(72, '2018-07-26 09:23:40',14, 3, 3, 0.5, 0);
        $dailyAmountArray[]= array(70, '2018-07-26 09:23:40',0, 0, 6, 0.5, 0);
        $dailyAmountArray[]= array(71, '2018-07-26 09:23:40',12, 0, 0, 0.5, 0);
        $dailyAmountArray[]= array(127, '2018-07-26 09:23:40',0, 15, 15, 0.3, 0);
        $dailyAmountArray[]= array(128, '2018-07-26 09:23:40',0, 9, 9, 0.3, 0);
        $dailyAmountArray[]= array(126, '2018-07-26 09:23:40',0, 8, 8, 2, 0);
        $dailyAmountArray[]= array(66, '2018-07-26 09:23:40',0, 8, 8, 0.25, 0);
        $dailyAmountArray[]= array(122, '2018-07-26 09:23:40',0, 11, 11, 0.35, 0);
        $dailyAmountArray[]= array(74, '2018-07-26 09:23:40',0, 37, 37, 0.2, 0);
        $dailyAmountArray[]= array(134, '2018-07-26 09:23:40',0, 18, 18, 0.4, 0);
        $dailyAmountArray[]= array(119, '2018-07-26 09:23:40',32, 28, 28, 0.4, 0);
        $dailyAmountArray[]= array(115, '2018-07-26 09:23:40',0, 5, 5, 0.55, 0);
        $dailyAmountArray[]= array(114, '2018-07-26 09:23:40',0, 1, 1, 0.55, 0);
        $dailyAmountArray[]= array(133, '2018-07-26 09:23:40',18, 17, 17, 0.45, 0);
        $dailyAmountArray[]= array(85, '2018-07-26 09:23:40',10, 11, 11, 0.25, 0);
        $dailyAmountArray[]= array(83, '2018-07-26 09:23:40',5, 9, 9, 0.5, 0);
        
        $incomePerItem = array();
        $incomeCorrectPerItem = array();
        
        foreach( $dailyAmountArray as $day ) {
            $itemID = $day[0];
            $backstockAfter = $day[2];
            $shelfQuantityBefore = $day[3];
            $shelfQuantityAfter = $day[4];
            $price = $day[5];
            
            echo "Item $itemID at " . $day[1] . "<br>";
            
            $totalCansBefore = 0 + $shelfQuantityBefore;
            $totalCans = $backstockAfter + $shelfQuantityAfter;
            $income = (($totalCansBefore - $totalCans) * $price ) * -1;
            
            $incomeCorrect = 0;
            
            if( $shelfQuantityBefore > $shelfQuantityAfter ) {
                $incomeCorrect = ($shelfQuantityBefore - $shelfQuantityAfter) * $price;
            }
            
            echo "Income: $income<br><br>";
            
            if( array_key_exists( $itemID, $incomePerItem ) == false ) {
                $incomePerItem[$itemID] = 0.0;
            }
            
            if( array_key_exists( $itemID, $incomeCorrectPerItem ) == false ) {
                $incomeCorrectPerItem[$itemID] = 0.0;
            }
            
            $incomePerItem[$itemID] += $income;
            $incomeCorrectPerItem[$itemID] += $incomeCorrect;
        }
        
        $totalCorrections = 0.0;
        foreach( $incomePerItem as $itemID => $value ) {
            
            $resultsInner = $db->query("SELECT * from Item WHERE ID = $itemID");
            $rowInner = $resultsInner->fetchArray();
            
            $totalCans = $rowInner['TotalCans'];
            $totalIncome = $rowInner['TotalIncome'];
            $backstock = $rowInner['BackstockQuantity'];
            $shelf = $rowInner['ShelfQuantity'];
            $name = $rowInner['Name'];
            $priceSnack = $rowInner['Price'];
            $priceSnackDisc = $rowInner['DiscountPrice'];
            
            $totalUnits = $totalCans - ($shelf + $backstock);
            $newIncome = ($totalIncome + $value); // Remove the bad income
            $newIncome = $newIncome += $incomeCorrectPerItem[$itemID]; // Add the right income
            $expectedIncomeHigher = $totalUnits * $priceSnack;
            $expectedIncomeLower = $totalUnits * $priceSnackDisc;
            
            $correction = $value + $incomeCorrectPerItem[$itemID];
            
            $totalCorrections += $correction;
            
            $correct = "";
            
            if( $newIncome > $expectedIncomeLower && $newIncome < $expectedIncomeHigher ) {
                $correct = "CORRECT!!!";
            }
            if( $value != 0 ) {
                echo "Item $name ($itemID)---> FIX INCOME ($value) + ACTUAL INCOME ($totalIncome) Units: $totalUnits Price: $priceSnack Corrected:" . $incomeCorrectPerItem[$itemID] . "=========== PROJECTED NEW INCOME ($newIncome) &nbsp;&nbsp;&nbsp;&nbsp;LOW ($expectedIncomeLower) &nbsp;&nbsp;&nbsp;&nbsp;HIGH ($expectedIncomeHigher) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$correct <br><br>";
            }
            
            $db->exec("Update Item set TotalIncome = TotalIncome + $correction WHERE ID = $itemID");
            echo "Fixed $name with +$correction<br>";
        }
        
        $db->exec("Update Information set Income = Income + $totalCorrections WHERE ItemType ='Snack'");
        echo "Fixed Income with +$totalCorrections<br>";
        echo "CIMEOM FIX $totalCorrections";
    }
?>