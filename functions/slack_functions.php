<?php
    function sendSlackMessageToMatt( $slackMessage, $emoji, $botName, $color ) {
            sendMessageToBot( "U1FEGH4U9", $emoji, $botName, $slackMessage, $color );
    //     sendSlackMessagePOST( "@mmiles", $emoji, $botName, $slackMessage );
    }
    
    function sendSlackMessageToUser( $slackID, $slackMessage, $emoji, $botName, $color ) {
        sendMessageToBot( $slackID, $emoji, $botName, $slackMessage, $color );
    //     sendSlackMessagePOST( "@" . $slackID, $emoji, $botName, $slackMessage );
    }
    
    function sendSlackMessageToRandom( $slackMessage, $emoji, $botName ) {
        sendSlackMessagePOST( "#random", $emoji, $botName, $slackMessage );
    }
    
    function sendSlackMessageToSlackBot( $slackMessage, $emoji, $botName ) {
       sendSlackMessagePOST( "@mmiles", $emoji, $botName, $slackMessage );
    }
    
    
    function sendMessageToBot( $slackID, $emoji, $botName, $slackMessage, $color  ){
        if( $_SERVER['SERVER_ADDR'] == "::1" || $_SERVER['SERVER_ADDR'] == "72.225.38.26" ) {
            $fakeSendMessage = "";
            if( $slackID != "U1FEGH4U9" ) {
                $fakeSendMessage = " - sent to $slackID";
            }
            $slackMessage = "`[TEST SERVER$fakeSendMessage]`\n" . $slackMessage;
            $slackID = "U1FEGH4U9";
        }
        
        $response = sendRequestToSlack( "https://slack.com/api/im.open?user=" . $slackID );
        $responseJSON = json_decode( $response );
        $sessionID = $responseJSON->channel->id;
        
        $attachmentParams = array([
                "fallback" => $slackMessage,
                "text" => $slackMessage,
                "color" => $color,
                "mrkdwn_in" => "[\"text\"]"
                ]);
         
         
        $emoji = urlencode( $emoji );
        $botName = urlencode( $botName );
        $attachmentEncoded = urlencode( json_encode( $attachmentParams ) );
         
        error_log("Sending DM:\nSlack ID: [" . $slackID . "]\nSession ID:[" . $sessionID . "]\nEmoji: [" . $emoji . "]\nBot Name: [" . $botName . "]\nMessage: [" . $attachmentEncoded . "]" );
        
        $chatMessage = "https://slack.com/api/chat.postMessage?as_user=false&username=" . $botName . "&attachments=" . $attachmentEncoded . "&icon_emoji=" . $emoji . "&channel=" . $sessionID;
         
        $response = sendRequestToSlack( $chatMessage );
        error_log("Message [" .  $response . "]" );
    }
    
    function sendRequestToSlack( $url ) {
        $token = "xoxb-49480869793-411237841957-D8mSfnFpcTzLgj0tFAWqHdZ9";
        $finalURL = $url . "&token=" . $token;
    
        // open connection
        $ch = curl_init();
        
        error_log( "REQUEST URL: [" . $finalURL . "]" );
        
        // set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $finalURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // execute post
        $result = curl_exec($ch);
        
        error_log( "RESPONSE: [" . $result . "] [" . curl_error($ch) . "]" );
        // close connection
        curl_close($ch);
        
        return $result;
    }
    
    // DEPRECATED!!!
    function sendSlackMessagePOST( $slackID, $emoji, $botName, $slackMessage ) {
        
        if( $_SERVER['SERVER_ADDR'] == "::1" || $_SERVER['SERVER_ADDR'] == "72.225.38.26" ) {
            sendMessageToBot( "U1FEGH4U9", $emoji, $botName, $slackMessage, "#000000" );
        } else {
        
            error_log("Sending SlackBot/Channel Message:\nSlack ID: [" . $slackID . "]\nEmoji: [" . $emoji . "]\nBot Name: [" . $botName . "]\nMessage: [" . $slackMessage . "]" );
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