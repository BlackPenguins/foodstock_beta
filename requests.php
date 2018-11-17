<?php
    include( "appendix.php" );
    
    $url = REQUESTS_LINK;
    include( HEADER_PATH );
?>

<script type="text/javascript">
    function toggleCompleted( requestID ) {
        $.post("<?php echo AJAX_LINK; ?>", { 
            type:'ToggleRequestCompleted',
            id:requestID,
        },function(data) {
        });
    }
</script>
<?php
    echo "<div style='padding: 10px; background-color:#d03030; border-bottom: 3px solid #000;'>";
    echo "<button style='padding:5px; margin:0px 5px;' id='request_item_button' class='item_button ui-button ui-widget-content ui-corner-all'>Request Snack or Soda</button>";
    echo "<button style='padding:5px; margin:0px 5px;' id='request_feature_button' class='item_button ui-button ui-widget-content ui-corner-all'>Request Feature</button>";
    echo "<button style='padding:5px; margin:0px 5px;' id='report_bug_button' class='item_button ui-button ui-widget-content ui-corner-all'>Report Bug</button>";
    echo "</div>";
    
    // ------------------------------------
    // REQUEST MODAL
    // ------------------------------------
    $itemType_options = "";
    $itemType_options = $itemType_options . "<option value='Soda'>Soda</option>";
    $itemType_options = $itemType_options . "<option value='Snack'>Snack</option>";
    
    $itemType_dropdown = "<select id='ItemTypeDropdown_Request' name='ItemTypeDropdown_Request' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>$itemType_options</select>";
        
    echo "<div id='request_item' title='Request Item' style='display:none;'>";
    echo "<form id='request_item_form' class='fancy' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
    echo "<fieldset>";
    echo "<label style='padding:5px 0px;' for='ItemTypeDropdown_Request'>Type</label>";
    echo $itemType_dropdown;
    echo "<label style='padding:5px 0px;' for='ItemName_Request'>Item</label>";
    echo "<input type='text' name='ItemName_Request' class='text ui-widget-content ui-corner-all'/>";
    echo "<label style='padding:5px 0px;' for='Note'>Note</label>";
    echo "<input type='text' name='Note_Request' class='text ui-widget-content ui-corner-all'/>";

    echo "<input type='hidden' name='Request' value='Request'/><br>";
    echo "<input type='hidden' name='redirectURL' value='" . REQUESTS_LINK . "'/><br>";

    echo "</fieldset>";
    echo "</form>";
    echo "</div>";
    
    echo "<div id='request_feature' title='Request Feature' style='display:none;'>";
    echo "<form id='request_feature_form' class='fancy' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
    echo "<fieldset>";
    echo "<label style='padding:5px 0px;' for='ItemName_Request'>Item</label>";
    echo "<input type='text' name='ItemName_Request' class='text ui-widget-content ui-corner-all'/>";
    
    echo "<input type='hidden' name='ItemTypeDropdown_Request' value='Feature'/><br>";
    echo "<input type='hidden' name='Request' value='Request'/><br>";
    echo "<input type='hidden' name='redirectURL' value='" . REQUESTS_LINK . "'/><br>";
    
    echo "</fieldset>";
    echo "</form>";
    echo "</div>";
    
    echo "<div id='report_bug' title='Report Bug' style='display:none;'>";
    echo "<form id='report_bug_form' class='fancy' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
    echo "<fieldset>";
    echo "<label style='padding:5px 0px;' for='ItemName_Request'>Item</label>";
    echo "<input type='text' name='ItemName_Request' class='text ui-widget-content ui-corner-all'/>";
    
    echo "<input type='hidden' name='ItemTypeDropdown_Request' value='Bug'/><br>";
    echo "<input type='hidden' name='Request' value='Request'/><br>";
    echo "<input type='hidden' name='redirectURL' value='" . REQUESTS_LINK . "'/><br>";
    
    echo "</fieldset>";
    echo "</form>";
    echo "</div>";

    drawTable($db, $isLoggedInAdmin, "Requests", "'Soda','Snack'");
    drawTable($db, $isLoggedInAdmin, "Feature Requests", "'Feature'");
    drawTable($db, $isLoggedInAdmin, "Bug Reports", "'Bug'");

    
    function drawTable( $db, $isLoggedInAdmin, $title, $type ) {
        // ------------------------------------
        // REQUESTS TABLE
        // ------------------------------------
        echo "<div class='soda_popout'  style='margin:10px; padding:5px;'><span style='font-size:26px;'>$title</span> <span style='font-size:0.8em;'></span></div>";
        echo "<div style='margin-bottom:50px;' id='users'>";
        echo "<table style='font-size:12; border-collapse:collapse; margin:10px; width:100%'>";
        echo "<thead><tr>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'></th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item Name</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item Type</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Requested By</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Note</th>";
        
        echo "</tr></thead>";
        
        $results = $db->query("SELECT r.ID, r.Completed, u.FirstName, u.LastName, r.ItemName, r.ItemType, r.Date, r.Note  FROM REQUESTS r JOIN User u ON r.UserID = u.UserID WHERE r.ItemType in (" . $type . ") ORDER BY Completed ASC, r.Date DESC");
        while ($row = $results->fetchArray()) {
            echo "<tr>";
        
        
            $completedMark = "&#9746;";
            $completedCellColor = "#bb3f3f";
            $completedMarkColor = "#6b1010";
        
            if( $row['Completed'] == 1 ) {
                $completedMark = "&#9745;";
                $completedCellColor = "#44b376";
                $completedMarkColor = "#0b562d";
            }
        
            $onClick = "";
        
            if( $isLoggedInAdmin ) {
                $onClick = " onclick='toggleCompleted(" . $row['ID'] . ");'";
            }
        
            echo "<td style='font-size:1.6em; cursor:pointer; text-align:center; font-weight:bold; color: $completedMarkColor; background-color: $completedCellColor; border:1px #000 solid;'> <span$onClick>$completedMark </span></td>";
        
            echo "<td style='padding:5px; background-color: $completedCellColor; border:1px #000 solid;'>" . $row['ItemName'] . "</td>";
            echo "<td style='padding:5px; background-color: $completedCellColor; border:1px #000 solid;'>" . $row['ItemType'] . "</td>";
            $fullName = $row['FirstName'] . " " . $row['LastName'];
            echo "<td style='padding:5px; background-color: $completedCellColor; border:1px #000 solid;'>" . $fullName . "</td>";
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
            echo "<td style='padding:5px; background-color: $completedCellColor; border:1px #000 solid;'>" . $date_object->format('m/d/Y  [h:i:s A]') . "</td>";
            echo "<td style='padding:5px; background-color: $completedCellColor; border:1px #000 solid;'>" . $row['Note'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
    }
?>

</body>