<?php
include(__DIR__ . "/../appendix.php" );

function DisplayPaymentMethods() {
    echo "<div style='margin:10px 15px; padding:5px; width: 95%;'>";
    echo "<div style='background-color: #bd7949; padding:5px; border-top: 3px solid #000; border-right: 3px solid #000; border-left: 3px solid #000; border-bottom: 2px solid #000; '>";
    echo "<span style='vertical-align:top; font-weight:bold;'>Supported Payment Methods:</span>"; 
    echo "</div>";
    
    echo "<div style='padding: 10px; display:flex; align-items:stretch; font-weight:bold; background-color: #d89465; border-right: 3px solid #000; border-left: 3px solid #000; border-bottom: 3px solid #000;'>";
    
    $flexCSS = "padding:5px; border: 2px dashed #c16a2c; display:flex; align-items:center; margin:0px 10px;";
    echo "<span style='$flexCSS'>";
    echo "<img style='width:34px; margin-right:5px;' title='Square Cash App' src='" . IMAGES_LINK . "square_cash.png'/> \$mtm4440";
    echo "</span>";
    
    echo "<span style='$flexCSS'>";
    echo "<img style='width:35px; margin-right:5px;' title='Venmo App' src='" . IMAGES_LINK . "venmo.png'/> @Matt-Miles-17";
    echo "</span>";
    
    echo "<span style='$flexCSS'>";
    echo "<img style='width:37px; margin-right:5px;' title=\"Seriously needed a hover-text for this?  It's PayPal.\" src='" . IMAGES_LINK . "paypal.png'/> lightwave365@yahoo.com";
    echo "</span>";
    
    echo "<span style='$flexCSS'>";
    echo "<img style='width:30px; margin-right:5px;' title='Send through Facebook' src='" . IMAGES_LINK . "facebook.png'/>  mattmiles17";
    echo "</span>";
    
    echo "<span style='$flexCSS'>";
    echo "<img style='width:30px; margin-right:5px;' title='Cash in Hand' src='" . IMAGES_LINK . "cash_in_hand.png'/> Location: My Cube";
    echo "</span>";
    
    echo "<span style='$flexCSS'>";
    echo "<img style='width:30px; margin-right:5px;' title='Google Pay' src='" . IMAGES_LINK . "google_pay.jpg'/>mtm4440@g.rit.edu";
    echo "</span>";
    
    echo "<span style='$flexCSS font-size:0.7em;'>";
    echo "Or you can suggest something else - be a trendsetter.";
    echo "</span>";
    
    echo "</div>";
    echo "</div>";
}

function DisplayAgoTime( $dateBefore, $dateNow ) {
    $date_object = new DateTime();

    if( $dateBefore != "") {
        $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $dateBefore);
    }

    $time_since = $dateNow->diff($date_object);
    
    $days_ago = $time_since->format('%a');
    $hours_ago = $time_since->format('%h');
    $minutes_ago = $time_since->format('%i');
    $seconds_ago = $time_since->format('%s');

    $ago_text = "UNKNOWN";

    if($days_ago >= 1) {
        $ago_text = $days_ago . " day". ( ( $days_ago == 1 )? (""):("s") ). "  ago...";
    } else if($hours_ago >= 1) {
        $ago_text = $hours_ago . " hour". ( ( $hours_ago == 1 )? (""):("s") ). " ago...";
    } else if($minutes_ago >= 1) {
        $ago_text = $minutes_ago . " minute". ( ( $minutes_ago == 1 )? (""):("s") ). "  ago...";
    } else  if($seconds_ago >= 1) {
        $ago_text = $seconds_ago . " second". ( ( $seconds_ago == 1 )? (""):("s") ). "  ago...";
    }

    return $ago_text;
}

function getPriceDisplay($price) {
    if( $price >= 1.00 ) {
        $price = "$" . number_format($price,2);
    } else {
        $price = $price * 100;
        $price = $price . "&cent;";
    }
    
    return $price;
}

function DisplayPreview($item_name, $soldOut, $imageURL) {
    $opacity = "1.0";

    if( $soldOut == true ) {
        $opacity = "0.4";
    }
    
    if( $imageURL == "" ) {
        echo "<div class='vcenter' style='background-color:#212121; font-weight:bold;  word-spacing:200px; height:200px; color:#FFFFFF'><span>".$item_name."</span></div>";
    } else {
        echo "<img src='" . PREVIEW_IMAGES_NORMAL . $imageURL . "' style = 'height:200px; max-width:100%; opacity:$opacity' />";
    }
}

function DisplayShelfCan($item_name, $thumbURL) {
    if( $thumbURL == "" ) {
        echo "<img title='$item_name' style='padding:5px;' src='" . PREVIEW_IMAGES_THUMBS . "not_found_sm.png' />";
    } else {
        echo "<img title='$item_name' style='padding:5px;' src='" . PREVIEW_IMAGES_THUMBS . $thumbURL . "' />";
    }
}
?>