<?php
	$statement = "";
	
	// Add Completed and ID to Requests
	
	/*
Sending payload: payload={"channel":"@alomeli","icon_emoji":":shopping_trolley:","username":"SnackStock - RECEIPT","text":"- Spicy Doritos & Cheetos ($0.35)\n*Total Price:* $0.35\n*Your Snack Balance:* $1.10\n"}
snackstock.php:21 Sending payload: payload={"channel":"@mmiles","icon_emoji":":shopping_trolley:","username":"SnackStock - RECEIPT","text":"*(ALOMELI)*\n- Spicy Doritos & Cheetos ($0.35)\n*Total Price:* $0.35\n*Your Snack Balance:* $1.10\n"}
snackstock.php:21 Sending payload: payload={"channel":"@mmiles","icon_emoji":":earth_americas:","username":"SodaStock - VISIT","text":"SnackStock visited by [alomeli] on [Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.146 Safari/537.36]"}
snackstock.php:45 Updating Card Area with []...

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
        
        
    private static org.json.JSONObject sendRequestToSlack( String url ) {
        HttpURLConnection connection = null;
        String token = "xoxb-296800983344-LzbVt56fgbGSsYi9Ga42YkTF";
        org.json.JSONObject json = null;
        BufferedReader responseReader = null;
        
        String finalURL = url + "&token=" + token;
        
        logger.info( "Sending the following URL to slack: [" + finalURL + "]" );
        
        try {
            connection = (HttpURLConnection) new URL( finalURL ).openConnection();
            connection.setRequestMethod("POST");
            connection.setConnectTimeout(5000);
            connection.setUseCaches(false);
            connection.setDoInput(true);
            connection.setDoOutput(true);
            
            DataOutputStream requestToSlack = new DataOutputStream( connection.getOutputStream() );
            requestToSlack.flush();
            requestToSlack.close();

            // Report any errors with the API
            InputStream responseFromSlack = connection.getInputStream();
            responseReader = new BufferedReader( new InputStreamReader( responseFromSlack ) );
            StringBuilder responseBuilder = new StringBuilder();
            String line;
            
            while ( ( line = responseReader.readLine() ) != null ) {
                responseBuilder.append( line );
            }
            
            json = new org.json.JSONObject( responseBuilder.toString() );
            
        } catch (Exception e) {
            logger.error( e.getLocalizedMessage() );
        } finally {
            if( responseReader != null ) {
                try { responseReader.close(); } catch ( IOException e ) {}
            }
            
            if (connection != null) {
                connection.disconnect();
            }
        }
        
        return json;
    
	*/
	
	if( $statement != "" ) {
		echo "Executing......<br>";
	
		$db->exec($statement);
	
		echo "DONE!<br><br>";
	}
?>