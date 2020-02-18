<?php
    include_once ( LOG_FUNCTIONS_PATH );

    function sendSlackMessageToMatt( $slackMessage, $emoji, $botName, $color ) {
            sendMessageToBot( "U1FEGH4U9", $emoji, $botName, $slackMessage, $color, "ADMIN" );
    //     sendSlackMessagePOST( "@mmiles", $emoji, $botName, $slackMessage );
    }
    
    function sendSlackMessageToUser( $slackID, $slackMessage, $emoji, $botName, $color, $username, $notifyAdmin ) {
        if( $slackID == "" ) {
            sendSlackMessageToMatt( "Failed to send notification for [" . $username . "] user. Create a SlackID!", ":no_entry:", "FoodStock - ERROR!!", "#bb3f3f" );
        } else {
            sendMessageToBot($slackID, $emoji, $botName, $slackMessage, $color, $username);

            // Only send to admin if the message wasn't already be sent to him
            // Avoid double slack messages
            if( $notifyAdmin && $slackID != "U1FEGH4U9" ) {
                sendSlackMessageToMatt( "*(" . $username . ")*\n$slackMessage", $emoji, $botName, $color );
            }
        }
    }
    
    function sendSlackMessageToRandom( $slackMessage, $emoji, $botName ) {
        sendSlackMessagePOST( "#random", $emoji, $botName, $slackMessage, false );
    }
    
    function sendSlackMessageToNerdHerd( $slackMessage, $emoji, $botName ) {
        sendSlackMessagePOST( "#the_nerd_herd", $emoji, $botName, $slackMessage, true );
    }

    function sendSlackMessageToNerdHerdTest( $slackMessage, $emoji, $botName ) {
        sendSlackMessagePOST( "#the_nerd_herd_test", $emoji, $botName, $slackMessage, true );
    }
    
    function sendSlackMessageToSlackBot( $slackMessage, $emoji, $botName ) {
       sendSlackMessagePOST( "@mmiles", $emoji, $botName, $slackMessage, false );
    }
    
    
    function sendMessageToBot( $slackID, $emoji, $botName, $slackMessage, $color, $username ){
        if( isTestServer() ) {
            $slackMessage = "`[sent to $username]`\n" . $slackMessage;
            sendSlackMessageToNerdHerdTest($slackMessage, $emoji, $botName );
        } else {

            $response = sendRequestToSlack("https://slack.com/api/im.open?user=" . $slackID);
            $responseJSON = json_decode($response);
            $sessionID = $responseJSON->channel->id;

            $fallbackMessage = str_replace("*", "", $slackMessage);
            $fallbackMessage = str_replace("`", "", $fallbackMessage);

            $attachmentParams = array([
                "fallback" => $fallbackMessage,
                "text" => $slackMessage,
                "color" => $color,
                "mrkdwn_in" => "[\"text\"]"
            ]);


            $emoji = urlencode($emoji);
            $botName = urlencode($botName);
            $attachmentEncoded = urlencode(json_encode($attachmentParams));

            log_slack("Sending DM:\nSlack ID: [" . $slackID . "]\nSession ID:[" . $sessionID . "]\nEmoji: [" . $emoji . "]\nBot Name: [" . $botName . "]\nMessage: [" . $attachmentEncoded . "]");

            $chatMessage = "https://slack.com/api/chat.postMessage?as_user=false&username=" . $botName . "&attachments=" . $attachmentEncoded . "&icon_emoji=" . $emoji . "&channel=" . $sessionID;

            $response = sendRequestToSlack($chatMessage);
            log_slack("Message [" . $response . "]");
        }
    }
    
    function sendRequestToSlack( $url ) {
        $token = "xoxb-49480869793-411237841957-D8mSfnFpcTzLgj0tFAWqHdZ9";
        $finalURL = $url . "&token=" . $token;
    
        // open connection
        $ch = curl_init();

        log_slack( "REQUEST URL: [" . $finalURL . "]" );
        
        // set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $finalURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // execute post
        $result = curl_exec($ch);

        log_slack( "RESPONSE: [" . $result . "] [" . curl_error($ch) . "]" );
        // close connection
        curl_close($ch);
        
        return $result;
    }
    
    // DEPRECATED!!!
    function sendSlackMessagePOST( $slackID, $emoji, $botName, $slackMessage, $bypassTestServer ) {
        
        if( $bypassTestServer == false && ( $_SERVER['SERVER_ADDR'] == "::1" || $_SERVER['SERVER_ADDR'] == "72.225.38.26" ) ) {
            sendMessageToBot( "U1FEGH4U9", $emoji, $botName, $slackMessage, "#000000", $slackID );
        } else {

            log_slack("Sending SlackBot/Channel Message:\nSlack ID: [" . $slackID . "]\nEmoji: [" . $emoji . "]\nBot Name: [" . $botName . "]\nMessage: [" . $slackMessage . "]" );
            $params = array( "channel" => $slackID, "icon_emoji" => $emoji , "username" => $botName, "text" => $slackMessage);
        
            $url = 'https://hooks.slack.com/services/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';
            
            $fields = array(
                'payload' => json_encode($params)
            );
            
            // build the urlencoded data
            $postvars = http_build_query($fields);
            
            // open connection
            $ch = curl_init();
            
            // set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            // execute post
            $result = curl_exec($ch);
            
            if( $result != "ok" ) {
                error_log("There was an error connecting to slack!!\nError Message: [" . $result . "] [" . curl_error($ch) . "]" );
            }
            // close connection
            curl_close($ch);
        }
    }
?>