<?php
    include( "appendix.php" );
    
    $url = HELP_LINK;
    include( HEADER_PATH );
//    include_once( LOG_FUNCTIONS_PATH );
?>

<div id='container'>
    <div class='rounded_header'><span  class='title'>New Users / History</span></div>
    <div class='center_piece'>
    <div style='background-color: #c8e2ff; margin: 10px 20px; padding:15px; border: 1px solid #000000;' >
        Welcome to Sodastock! I sell both soda and snacks, available for everyone that works at RSA. Rick S. supplied the soda and then left the company in 2014. I took over several months later with selling the soda. Mike P. supplied the snacks and then passed the duty over to me in 2018. How it originally worked was people would drop money into the mugs in the cabinet or above the fridge and they would take what they want. I had been building this website for my own personal use to keep track of the inventory. It started as a Google Sheet, then became a very rough php project on my work computer, and has evolved into an entire purchasing and inventory system hosted on Vultr (so there is no downtime and it can be accessed outside of RSA when I am restocking items at the store). The massive revamp of the site happened in March 2018 when some co-ops said it would be easier for them if they could buy through the website and a tab would be kept for them. So I opened the site to all users and built a way for people to purchase through the website.
    </div>
    </div>

    <div class='rounded_header'><span  class='title'>How Does it Work?</span></div>
    <div class='center_piece'>
    <div style='background-color: #c8e2ff; margin: 10px 20px; padding:15px; border: 1px solid #000000;' >
            <ol>
                <li>Login to the site and choose the 'Snack' or 'Soda' section in the top-left.</li>
                <li>Search for your item using the search box.</li>
                <li>Once you find your item increase the quantity to the number you are going to purchase. For some small items like 'candy' you might buy more than one. A cart will appear at the top of the page once you add at least one of any item.</li>
                <li>Review your cart and make sure it is correct. As of now you cannot remove items from the cart. If you mess up just refresh the page.</li>
                <li>Once you verify the total and the items in the cart click the 'Purchase' button. That total will go towards your (green) balance in the top navigation bar. Throughout the month you can keep incrementing your balance as you buy more items.</li>
                <li>At the first of every month I will send out an automated message through slack telling you your balance for the previous month. You can then pay me through a variety of payment methods.</li>
                <li>This site is intended to be used for a total payment at the first of every month. <u>Not after every purchase.</u> If you start paying me in the middle of the month it gets confusing because then I have to keep track of which payments made online are marked off in SodaStock. </li>
            </ol>
    </div>
    </div>

    <div class='rounded_header'><span  class='title'>Why Do This?</span></div>
    <div class='center_piece'>
        <div style='background-color: #c8e2ff; margin: 10px 20px; padding:15px; border: 1px solid #000000;' >
            Many people don't like to carry cash on them so this offers an alternative that is easier to them. Almost everyone appears to have some way to pay online whether it be Venmo, PayPal, or any other cash app so paying off a balance is as easy as a click of a button. It benefits me because as you use the site you are updating the inventory of the items yourself so there is less work for me. And because of that I'll know when things are sold out faster than if I manually counted everything myself. As an incentive to use the site you'll notice that <b>there are cheaper prices if you buy through the site.</b>
        </div>
    </div>

    <div class='rounded_header'><span  class='title'>Pages</span></div>
    <div class='center_piece'>
        <div style='background-color: #c8e2ff; margin: 10px 20px; padding:15px; border: 1px solid #000000;' >
            Let's go through the various pages at the top of the page:<br>
            <div style='margin: 20px 0px;'>
                <span style='padding:0px;' class='nav_buttons nav_buttons_soda'>Soda Home</span> The page where you can view all soda items (in the fridge in Matt's cube) and purchase them.
            </div>

            <div style='margin: 20px 0px;'>
                <span style='padding:0px;' class='nav_buttons nav_buttons_snack'>Snack Home</span> The page where you can view all snack items (in the cabinet in the 3rd floor kitchen) and purchase them.<br>
            </div>

            <div style='margin: 20px 0px;'>
                <span style='padding:0px;' class='nav_buttons nav_buttons_requests'>Requests</span> The page where all bug reports, feature requests, and soda/snack requests can be viewed and submitted by admin or user. You'll notice that I'm usually the only one using this page to keep track of my feature requests and create a 'roadmap' but anyone can submit an idea here.<br>
            </div>

            <div style='margin: 20px 0px;'>
                <span style='padding:0px;' class='nav_buttons nav_buttons_stats'>Graphs</span> The page where you can view graphs for certain statistics of the site. Who is buying the most, what is bought the most, etc. For anything concerning user information the names of the people are masked behind random animal names so your purchasing habits are a secret.<br>
            </div>

            <div style='margin: 20px 0px;'>
                <span style='padding:0px;' class='nav_buttons nav_buttons_preferences'>Preferences</span> To make the website less confusing for new users by default a few things are disabled. You can enable them in here. You can also subscribe to restock messages and change your anonymous animal name.
            </div>

            <div style='margin: 20px 0px;'>
                <span style='padding:0px;' class='nav_buttons nav_buttons_admin'>Credits</span> A different way to purchase through the site. Some people don't like to pay at the end of every month so we now offer the ability to buy credits upfront. Just pay me through some payment method and I'll give you credits to spend on any item in the site. To buy credits you need to message me in slack after you pay me otherwise I won't notice. If you rarely buy items and you buy enough credits you could go several months without having to pay a balance. Once your credits are used up (even in mid-purchase) the rest of the total will go towards a balance like usual. At any time you can have your remaining credits sold and your money given back to you. This is also a good option for co-ops so I don't need to track them down on their last day asking them to pay a balance.<br>
            </div>

            <div style='margin: 20px 0px;'>
                <span style='padding:0px;' class='nav_buttons nav_buttons_billing'>Balance</span> Your current balance (for all months). If you click this button you'll be taken to a billing page which shows you the payments you have made and a purchase history page showing which items you have bought. This is mainly so there is understanding what was bought, what is owed, and why the balance is what it is for both the user and for me. When dealing with actual money I'm cautious about balances being correct so this keeps me in check. If there is ever a discrepancy with some total on this page <b>let me know</b>. I've been known to have bugs in this page (usually only display ones, not balance issues).<br>
            </div>

            <div style='margin: 20px 0px;'>
                <span style='background-color: #000000; color: #FFF; padding: 5px; font-weight: bold;' id='logout_link_s'>[Logout]</span> Um, it logs you out.<br>
            </div>

            <div style='margin: 20px 0px;'>
                <span style='background-color: #46465f; padding: 2px;' class="register">Register for a Discount!</span> The page to register for an account. There are no ties to a payment site. No credit card numbers are stored anywhere. All payments have to be done manually. The most personal information stored about you is your phone number. Passwords are also encrypted on creation so even I don't know what they are. That's why I can't give your password if you forget it. I need to reset it (which I have a button for that). Only the best security at RSA ;)<br>
            </div>
        </div>
    </div>

    <div class='rounded_header'><span  class='title'>Cash-Only</span></div>
    <div class='center_piece'>
        <div style='background-color: #c8e2ff; margin: 10px 20px; padding:15px; border: 1px solid #000000;' >
            One co-op said they wanted to still pay with change in the mug but they wanted to keep track through the site and still get the discounted price. The cash-only checkbox in the cart does just this. You check the box if you already dropped money in the mug and it will add it to your purchase history, decrement the quantity, but NOT add it to your balance.
        </div>
    </div>

    <div class='rounded_header'><span  class='title'>5 Minute Reminder</span></div>
    <div class='center_piece'>
        <div style='background-color: #c8e2ff; margin: 10px 20px; padding:15px; border: 1px solid #000000;' >
            I have been losing money for a while and it might be because people are taking items but forgetting to buy it from the site afterwards. A lot can happen between grabbing something and getting back to your desk so it's understandable. I built in a pop-up message that appears after five minutes of being idle on the site without buying anything to help remind you to purchase your item. Now you can open the site, grab the item, and if you get back to your desk after five minutes there will be a reminder waiting for you.
        </div>
    </div>
</div>

</body>