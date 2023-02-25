<?php
define( "NONE", "NONE" );

define( "CHRISTMAS", "CHRISTMAS" );
define( "ST_PATRICKS_DAY", "ST_PATRICKS_DAY" );
define( "APRIL_FOOLS", "APRIL_FOOLS" );
define( "CORONA", "CORONA" );

$holiday = null;

function buildHolidayCache() {
    global $holiday;

    if( $holiday == null ) {
        $holiday = NONE;
        testAndSetHoliday(CHRISTMAS, array("12/01/2020", "12/26/2020"));
        testAndSetHoliday(ST_PATRICKS_DAY, array("03/10/2020", "03/17/2020"));
        testAndSetHoliday(CORONA, array("03/18/2020", "12/31/2020"));
        testAndSetHoliday(APRIL_FOOLS, array("04/01/2020", "04/01/2020"));
    }
}

//---------------------------------------------------------
// STYLYERS
//---------------------------------------------------------

function getHolidayClass( $class ) {
    buildHolidayCache();

    if( isDateHoliday( ST_PATRICKS_DAY ) ) {
        $class = "patricks_" . $class;
    } else if( isDateHoliday( APRIL_FOOLS ) ) {
        $class = "fools_" . $class;
    } else if( isDateHoliday( CHRISTMAS ) ) {
        $class = "xmas_" . $class;
    }

    return $class;
}

function printHolidayUserSuffix() {
    if( isDateHoliday( APRIL_FOOLS ) ) {
        echo " - Amateur Hacker";
    }
}

function printHolidayBanner() {
    if( isDateHoliday( CORONA ) ) {
        echo "<span style='position:absolute; top:0; left:0; z-index: 100; display:block;'><img style='width:100%;' src='" . IMAGES_LINK . "quarantined.png' /></span>";
    }
}

function getHolidayBalanceLabel() {
    if( isDateHoliday( ST_PATRICKS_DAY ) ) {
        return "Pot of Gold";
    } else {
        return "Balance";
    }
}

function printPurchaseHistorySubtitle() {
    if( isDateHoliday( APRIL_FOOLS ) ) {
        echo "<div style='position:absolute; bottom: 0; left: 0; color: #7138ef;' class='total_details_box'><b>(Serious money business here so no fools on this page.)</b></div>";
    } else if( isDateHoliday( APRIL_FOOLS ) ) {
        echo "<div style='position:absolute; bottom: 0; left: 0; color: #b70006;' class='total_details_box'><b>Happy Holidays!</b></div>";
    } else if( isDateHoliday( ST_PATRICKS_DAY ) ) {
        echo "<div style='position:absolute; bottom: 0; left: 0; color: #007b17;' class='total_details_box'><b>Happy St. Patrick's Day!</b></div>";
    }
}

function getHolidayVersion( $version ) {
    if( isDateHoliday( APRIL_FOOLS ) ) {
        return "Fools";
    } else if( isDateHoliday( ST_PATRICKS_DAY ) ) {
        return "St. Patricks ($version)";
    } else if( isDateHoliday( CHRISTMAS ) ) {
        return "Christmas ($version)";
    } else {
        return $version;
    }
}

function getHolidayItemName( $itemName ) {
    buildHolidayCache();

    if( isDateHoliday( APRIL_FOOLS ) ) {
        switch( $itemName ) {
            case "Dr. Pepper": return "Lt. Pepper";
            case "Ginger Ale": return "Gingeraid";
            case "Pitch Black": return "Grape Mountain Dew";
            case "Mountain Dew": return "Mountain Don't";
            case "Vanilla Zero": return "Vanilla -1";
            case "Polar Water": return "Polar Bear Water";
            case "Barqs Root Beer": return "Bark's Root Bear";
            case "Coke Zero": return "Coke Absolute Zero";
            case "Sprite Zero": return "Sprite NaN";
            case "Fun-Size Candy": return "Christian's Babe Ruths";
            case "Slim Jim (Small Size)": return "Fat Jim (Small Size)";
            case "Slim Jim (Foot Long Size)": return "Overweight Jim (Foot Long Size)";
            case "Slim Jim (Monster Size)": return "Obese Jim (Monster Size)";
            case "CheezIts": return "Cheezy Squares";
            case "Spicy Doritos & Cheetos": return "Bitter Doritos & Cheetos";
            case "Fruit Roll Up": return "Fruit Roll Down";
            case "Muffins": return "Miniature Cupcakes";
            case "Pop-Tarts": return "Toaster Rectangles";
            case "Oreos": return "Hydrox";
            case "Microwave Popcorn": return "Radioactive Corn";
            case "Full-Size Candy Bars": return "Full-Size Diabetes";
            case "Nutty Buddy": return "Crazy Pals";
            case "Ramen Cup (Maruchan)": return "Sodium Cup";
            case "Hot Fries": return "Cold Fries";
            case "Progresso Soup": return "Your Backup Lunch";
            case "Kirkland Signature Popcorn": return "Inferior Signature Popcorn";
            case "Kirkland Signature Granola Bar...": return "Decent Signature Granola Bar...";
            default:
                return $itemName;
        }
    } else {
        return $itemName;
    }
}

function getHolidayHeader() {
     buildHolidayCache();

    if( isDateHoliday( APRIL_FOOLS ) ) {
        echo "<div style='display:flex; align-items:center;'>";
        echo "<span id='fool_holder'>";
        echo "<img src='" . IMAGES_LINK . "fools_1.jpg'/>";
        echo "</span>";
        echo "<script>var currentFool = 'fools_1.jpg'; setInterval(function(){ if( currentFool == 'fools_1.jpg' ) { currentFool='fools_2.jpg'; } else if( currentFool == 'fools_2.jpg' ) { currentFool='fools_3.jpg'; } else if( currentFool == 'fools_3.jpg' ) { currentFool='fools_4.jpg'; } else if( currentFool == 'fools_4.jpg' ) { currentFool='fools_5.jpg'; } else if( currentFool == 'fools_5.jpg' ) { currentFool='fools_1.jpg'; } $('#fool_holder').html( '<img src=\"" . IMAGES_LINK . "' + currentFool + '\"/>'); }, 8000);</script>";
        echo "</div>";
    } else if( isDateHoliday( CORONA ) ) {
        echo "<div style='color: #ffe000; display:flex; align-items:center;'>";
        echo "<img style='margin-right: 10px;' width='40px' src='" . IMAGES_LINK . "caution.png'/>Stay home. Why are you here?<br><br>Unfortunately, due to the lack of business we've had to layoff all of our non-existent workers. We hope to bounce back from this.<br>We also understand the urgent issue that our soda inventory will be expiring in 2 years and we are addressing your concerns.";
        echo "</div>";
    } else {
        echo "<div style='display:flex; align-items:center;'>";
        echo "<img width='40px' src='" . IMAGES_LINK . "sale.png'/>&nbsp;Discounted prices are only available when you buy through the site. Because of inflation, prices are a bit higher than before covid.";
        echo "</div>";

        echo "<div style='display:flex; align-items:center;'>";
        echo "<img width='40px' src='" . IMAGES_LINK . "handle_with_care.png'/>&nbsp;Remember to pick up your product first and have it physically in your hand before you buy on the website to avoid buying something that was recently all bought out by someone else.";
        echo "</div>";
    }
}

function getHolidayRequestItemName( $itemName ) {
    if( isDateHoliday( APRIL_FOOLS ) ) {
        switch( rand(1,10) ) {
            case 1:
                return "Noah and Reese was here.";
            case 2:
                return "Noah was here.";
            case 3:
                return "Reese was here.";
            case 4:
                return "Reese tripped Noah to get here.";
            case 5:
                return "Noah wins everything.";
            case 6:
                return "Reese > Noah.";
            case 7:
                return "Noah: Come at me bro.";
            case 8:
                return "Reese: I don't even work here anymore. Why do I care about this?";
            case 9:
                return "Noah shoved Reese to get here.";
            case 10:
                return "Christian: I'm just here for pizza and babe ruths.";
        }
    } else {
        return $itemName;
    }
}
function printHolidayPriceIcon( $price ) {
    buildHolidayCache();

    $smallPriceFont = "";
    $priceBackground = "";

    if( isDateHoliday( CHRISTMAS ) ) {
        echo "<img style='position:absolute; top:14px; right:17px; z-index:200;' src='" . IMAGES_LINK . "wreath.png'/>";
        $priceBackground = "price_background";
    } else if( isDateHoliday( ST_PATRICKS_DAY ) ) {
        echo "<img style='position:absolute; top:14px; right:7px; z-index:0; width:64px;' src='" . IMAGES_LINK . "clover.png'/>";
    } else if( isDateHoliday( APRIL_FOOLS ) ) {
        $priceBackground = "price_background";
//        $smallPriceFont = "style='font-size: 0.98em; padding:17.5px 0px;'";

        $randomPrice = "BUG";

        switch( rand(1,10) ) {
            case 1:
                // Broken price
                $randomPrice = "$" . rand(3, 999);
                break;
            case 2:
                 // Question mark
                $randomPrice = "???";
                break;
            case 3:
                //Hour glass
                $randomPrice = "&#8987;";
                break;
            case 4:
                // Hotdog
                $randomPrice = "&#127789;";
                break;
            case 5:
                // Poop
                $randomPrice = "&#128169;";
                break;
            case 6:
                // Joker
                $randomPrice = "&#127183;";
                break;
            case 7:
                // NPE
                $randomPrice = "NaN";
                break;
            case 8:
                // Penguin
                $randomPrice = "&#128039;";
                break;
            case 9:
                $randomPrice = "&#129386;";
                break;
            case 10:
                $randomPrice = "wrapping issues";
                break;
        }

        echo "<div $smallPriceFont class='price_text price_background fools_price'>";
        echo $randomPrice;
        echo "</div>";
    } else {
         $priceBackground = "price_background";
    }

    if( substr( $price, 0, 1 ) == "$" ) {
        $smallPriceFont = "style='font-size: 0.9em; padding:17.5px 0px;'";
    }

    echo "<div $smallPriceFont class='price_text $priceBackground'>";
    echo $price;
    echo "</div>";

}

function printHolidayLights() {
    buildHolidayCache();

    if( isDateHoliday( CHRISTMAS ) ) {
        echo "<ul style='top: 2px;' class='lightrope'>" .
             "<li title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
             "<li title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
             "<li title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
             "<li title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
             "<li title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
             "<li title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
             "<li title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
             "<li title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
             "<li title='Break Me!' onclick=\"breakBulb(this);\"></li>" .
             "</ul>";
    }
}

//---------------------------------------------------------
// TESTING FRAMEWORK
//---------------------------------------------------------
function testAndSetHoliday( $holidayLabel, $holidayDateRange ) {
    global $holiday;
    $today = new DateTime();

    $startDate = null;
    $endDate = null;
    $isToday = false;

    if( count( $holidayDateRange ) == 2 ) {
        $startDate =  new DateTime( $holidayDateRange[0] );
        $endDate =  new DateTime( $holidayDateRange[1] );
    } else if( count( $holidayDateRange ) == 1 ) {
        $startDate =  new DateTime( $holidayDateRange[0] );
        $endDate =  new DateTime( $holidayDateRange[0] );
    }

    if( $startDate != null && $endDate != null ) {
        // Add 1 day so it includes til midnight of next day
        $endDate->add(new DateInterval('P1D' ) );

//        error_log("Found [" . count( $holidayDateRange ) . "] dates. Comparing [" . date_format($today, 'Y-m-d H:i:s') . "] TODAY with [" . date_format($startDate, 'Y-m-d H:i:s') . "] - [" . date_format($endDate, 'Y-m-d H:i:s') . "]" );
        $isToday = $today >= $startDate && $today <= $endDate;
    } else {
//        error_log("Holiday has too many ranges [$holidayDateRange].");
    }

    if( $isToday ) {
        $holiday = $holidayLabel;
    }
}

function isDateHoliday( $testHoliday ) {
    global $holiday;

    return $testHoliday == $holiday;
}