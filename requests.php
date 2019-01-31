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

    function togglePriority( requestID, priority ) {
        $.post("<?php echo AJAX_LINK; ?>", { 
            type:'ToggleRequestPriority',
            id:requestID,
            priority:priority,
        },function(data) {
        });
    }
</script>
<?php
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
    echo "<label style='padding:5px 0px;' for='Priority_Request'>Priority</label>";
    echo "<select id='Priority_Request' name='Priority_Request' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>";
    echo "<option value='High'>High</option>";
    echo "<option value='Medium'>Medium</option>";
    echo "<option value='Low'>Low</option>";
    echo "</select>";
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
    echo "<label style='padding:5px 0px;' for='Priority_Request'>Priority</label>";
    echo "<select id='Priority_Request' name='Priority_Request' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>";
    echo "<option value='High'>High</option>";
    echo "<option value='Medium'>Medium</option>";
    echo "<option value='Low'>Low</option>";
    echo "</select>";
    
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
    echo "<label style='padding:5px 0px;' for='Priority_Request'>Priority</label>";
    echo "<select id='Priority_Request' name='Priority_Request' style='padding:5px; margin-bottom:12px; font-size:2em;' class='text ui-widget-content ui-corner-all'>";
    echo "<option value='High'>High</option>";
    echo "<option value='Medium'>Medium</option>";
    echo "<option value='Low'>Low</option>";
    echo "</select>";
    
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
        echo "<div class='rounded_header'><span id='$title' class='title'>$title</span>";
        
        echo "<span style='float:right; padding-right: 15px;'>";
        if( $title == "Requests" ) {
            echo "<button style='padding:5px; background:#b6b2e8;' id='request_item_button' class='item_button ui-button ui-widget-content ui-corner-all'>Request Snack or Soda</button>";
        } else if( $title == "Feature Requests" ) {
            echo "<button style='padding:5px; background:#b6b2e8;' id='request_feature_button' class='item_button ui-button ui-widget-content ui-corner-all'>Request Feature</button>";
        } else {
            echo "<button style='padding:5px; background:#b6b2e8;' id='report_bug_button' class='item_button ui-button ui-widget-content ui-corner-all'>Report Bug</button>";
        }
        
        echo "</span>";
        echo "</div>";
        
        $column1Width = 2;
        $column2Width = 3;
        $column3Width = 20;
        $column4Width = 5;
        $column5Width = 5;
        $column6Width = 5;
        $column7Width = 5;
        echo "<div class='center_piece'>";
        echo "<div class='rounded_table' style='margin-left: 2%; margin-right: 2%;'>";
        echo "<table style='table-layout: fixed;'>";
        echo "<thead><tr>";
        echo "<th style='width:$column1Width%; padding-left: 0px;'>&nbsp;</th>";
        echo "<th style='width:$column2Width%;'>Priority</th>";
        echo "<th style='width:$column3Width%;'>Item Name</th>";
        
        if( $title == "Requests" ) {
            echo "<th style='width:$column4Width%;'>Item Type</th>";
        }
        
        echo "<th style='width:$column5Width%;'>Requested By</th>";
        echo "<th style='width:$column6Width%;'>Date</th>";
        echo "<th style='width:$column7Width%; word-break:break-word;'>Note</th>";
        
        echo "</tr></thead>";
        
        $results = $db->query("SELECT r.ID, r.Priority, r.Completed, r.DateCompleted, u.FirstName, u.LastName, r.ItemName, r.ItemType, r.Date, r.Note  FROM REQUESTS r JOIN User u ON r.UserID = u.UserID WHERE r.ItemType in (" . $type . ") " . 
                "ORDER BY Completed ASC, " .
                "CASE WHEN Completed = 1 THEN DateCompleted ELSE " .
                "CASE WHEN Priority = 'High' THEN '3' WHEN Priority = 'Medium' THEN '2' ELSE '1' END " .
                "END DESC," .
                "r.Date DESC");
        while ($row = $results->fetchArray()) {
            $completedMark = "&#9746;";
            $completedClass = "in_progress";
            $completedMarkColor = "#6b1010";
            $dateCompleted = "";
        
            if( $row['Completed'] == 1 ) {
                $completedMark = "&#9745;";
                $completedClass = "completed";
                $completedMarkColor = "#0b562d";
                
                if( $row['DateCompleted'] != "" ) {
                    $completed_date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['DateCompleted']);
                    $dateCompleted = "<br><br><u>Completion Date:</u><br>" . $completed_date_object->format('m/d/Y  [h:i:s A]');
                }
            }
        
            echo "<tr class='$completedClass' style='display:table-row;'>";
            
            $onClick = "";
        
            if( $isLoggedInAdmin ) {
                $onClick = " onclick='toggleCompleted(" . $row['ID'] . ");'";
            }
        
            echo "<td style='padding-left: 0px; width:$column1Width%; font-size:1.6em; cursor:pointer; text-align:center; font-weight:bold; color: $completedMarkColor;'> <span$onClick>$completedMark </span></td>";
        
            echo "<td style='width:$column2Width%;'>";
            
            $priority = $row['Priority'];
            
            if( $isLoggedInAdmin ) {
                echo "<select onchange='togglePriority(" . $row['ID'] . ", this.value);' id='Priority_Request' name='Priority_Request' class='text ui-widget-content ui-corner-all'>";
                echo "<option " . ( $priority == "High"  ? "selected" : "" ) . " value='High'>High</option>";
                echo "<option " . ( $priority == "Medium"  ? "selected" : "" ) . " value='Medium'>Medium</option>";
                echo "<option " . ( $priority == "Low"  ? "selected" : "" ) . " value='Low'>Low</option>";
                echo "</select>";
            } else {
                echo $priority;
            }
            
            echo "</td>";
            
            echo "<td style='width:$column3Width%;'>" . $row['ItemName'] . "</td>";
            
            if( $title == "Requests" ) {
                echo "<td style='width:$column4Width%;'>" . $row['ItemType'] . "</td>";
            }
            
            $fullName = $row['FirstName'] . " " . $row['LastName'];
            echo "<td style='width:$column5Width%;'>" . $fullName . "</td>";
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
            echo "<td style='width:$column6Width%;'>" . $date_object->format('m/d/Y  [h:i:s A]') . "$dateCompleted</td>";
            echo "<td style='width:$column7Width%; word-break:break-word;'>" . $row['Note'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    }
?>

</body>