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
    
    if( $statement != "" ) {
        echo "Executing......<br>";
    
        $db->exec($statement);
    
        echo "DONE!<br><br>";
    }
?>