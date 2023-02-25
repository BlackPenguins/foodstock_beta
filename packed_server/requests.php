<?php
    include( "appendix.php" );
    
    $url = REQUESTS_LINK;
    include( HEADER_PATH );
    include_once( HOLIDAY_FUNCTIONS_PATH );
//    include_once( LOG_FUNCTIONS_PATH );
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
    $itemType_options = "";
    $itemType_options = $itemType_options . "<option value='Soda'>Soda</option>";
    $itemType_options = $itemType_options . "<option value='Snack'>Snack</option>";
    
    $itemType_dropdown = "<select id='ItemTypeDropdown_Request' name='ItemTypeDropdown_Request' class='text ui-widget-content ui-corner-all'>$itemType_options</select>";

    buildRequestItemModal( $itemType_dropdown );
    buildRequestFeatureModal();
    buildReportBugModal();

    echo "<div id= 'container'>";
    drawTable($db, "Requests", [ "Soda", "Snack" ] );
    drawTable($db, "Feature Requests", [ "Feature" ] );
    drawTable($db, "Bug Reports", [ "Bug" ] );
    echo "</div>";

    function buildReportBugModal() {
        echo "<div id='report_bug_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar'>";
        echo "Report Bug";
        echo "<span id='report_bug_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='report_bug_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";

        echo "<ul>";

        echo "<li>";
        echo "<label for='ItemName_Request'>Details</label>";
        echo "<textarea cols='50' rows='5' name='ItemName_Request'></textarea>";
        echo "<span>The bug details</span>";
        echo "</li>";

        echo "<li class='buttons'>";
        echo "<input style='padding:10px;' type='submit' name='Report_Bug_Submit' value='Report Bug'/>";
        echo "</li>";

        echo "<input type='hidden' name='ItemTypeDropdown_Request' value='Bug'/>";
        echo "<input type='hidden' name='Request' value='Request'/>";
        echo "<input type='hidden' name='redirectURL' value='" . REQUESTS_LINK . "'/>";

        echo "</ul>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }

    function buildRequestFeatureModal() {
        echo "<div id='request_feature_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar'>";
        echo "Request Feature";
        echo "<span id='request_feature_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='request_feature_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";

        echo "<ul>";

        echo "<li>";
        echo "<label for='ItemName_Request'>Details</label>";
        echo "<textarea cols='50' rows='5' name='ItemName_Request'></textarea>";
        echo "<span>The feature details</span>";
        echo "</li>";

        echo "<li class='buttons'>";
        echo "<input style='padding:10px;' type='submit' name='Request_Feature_Submit' value='Request Feature'/>";
        echo "</li>";

        echo "<input type='hidden' name='ItemTypeDropdown_Request' value='Feature'/>";
        echo "<input type='hidden' name='Request' value='Request'/>";
        echo "<input type='hidden' name='redirectURL' value='" . REQUESTS_LINK . "'/>";

        echo "</ul>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }

    function buildRequestItemModal( $itemType_dropdown ) {
        echo "<div id='request_item_modal' class='neptuneModal'>";

        echo "<div class='neptuneModalContent'>";

        echo "<div class='neptuneTitleBar'>";
        echo "Request Item";
        echo "<span id='request_item_close_button' class='neptuneModalClose'>&times;</span>";
        echo "</div>";

        echo "<form class='neptuneForm' id='request_item_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";

        echo "<ul>";

        echo "<li>";
        echo "<label for='ItemTypeDropdown_Request'>Type</label>";
        echo $itemType_dropdown;
        echo "</li>";

        echo "<li>";
        echo "<label for='ItemName_Request'>Name</label>";
        echo "<input style='width:350px;' type='text' name='ItemName_Request'/>";
        echo "<span>The name of the item</span>";
        echo "</li>";

        echo "<li>";
        echo "<label for='Note_Request'>Notes</label>";
        echo "<textarea cols='50' rows='2' name='Note_Request'></textarea>";
        echo "<span>Additional notes about the item</span>";
        echo "</li>";

        echo "<li class='buttons'>";
        echo "<input style='padding:10px;' type='submit' name='Request_Item_Submit' value='Request Item'/>";
        echo "</li>";

        echo "<input type='hidden' name='Request' value='Request'/>";
        echo "<input type='hidden' name='redirectURL' value='" . REQUESTS_LINK . "'/>";

        echo "</ul>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }

    /**
     * @param $db SQLite3
     * @param $title
     * @param $type
     */
    function drawTable( $db, $title, $typeArray ) {
        // ------------------------------------
        // REQUESTS TABLE
        // ------------------------------------
        echo "<div class='page_header'><span id='$title' class='title'>$title</span>";

        if( IsLoggedIn() && !IsInactive() ) {
            echo "<span style='float:right; padding-right: 15px;'>";
            if ($title == "Requests") {
                echo "<button style='padding:5px; background:#b6b2e8;' id='request_item_button' class='ui-button ui-widget-content ui-corner-all'>Request Snack or Soda</button>";
            } else if ($title == "Feature Requests") {
                echo "<button style='padding:5px; background:#b6b2e8;' id='request_feature_button' class='ui-button ui-widget-content ui-corner-all'>Request Feature</button>";
            } else {
                echo "<button style='padding:5px; background:#b6b2e8;' id='report_bug_button' class='ui-button ui-widget-content ui-corner-all'>Report Bug</button>";
            }

            echo "</span>";
        }

        echo "</div>";
        
        $column1Width = 2;
        $column2Width = 3;
        $column3Width = 20;
        $column4Width = 5;
        $column5Width = 5;
        $column6Width = 5;
        $column7Width = 5;
        echo "<div class='center_piece'>";
        echo "<div class='rounded_table_no_border' style='margin-left: 2%; margin-right: 2%;'>";
        echo "<table style='table-layout: fixed;'>";
        echo "<thead><tr>";
        echo "<th class='hidden_mobile_column' style='width:$column1Width%; padding-left: 0px;'>&nbsp;</th>";
        echo "<th class='hidden_mobile_column' style='width:$column2Width%;'>Priority</th>";
        echo "<th style='width:$column3Width%;'>Item Name</th>";
        
        if( $title == "Requests" ) {
            echo "<th class='hidden_mobile_column' style='width:$column4Width%;'>Item Type</th>";
        }
        
        echo "<th class='hidden_mobile_column' style='width:$column5Width%;'>Requested By</th>";
        echo "<th class='hidden_mobile_column' style='width:$column6Width%;'>Date</th>";
        echo "<th class='hidden_mobile_column' style='width:$column7Width%; word-break:break-word;'>Note</th>";
        
        echo "</tr></thead>";

        $requestsQuery = "SELECT r.ID, r.Priority, r.Completed, r.DateCompleted, u.FirstName, u.LastName, r.ItemName, r.ItemType, r.Date, r.Note " .
            "FROM REQUESTS r JOIN User u ON r.UserID = u.UserID " .
            "WHERE r.ItemType in " . getPrepareStatementForInClause( count( $typeArray ) ) .
            "ORDER BY " .
            "CASE WHEN Completed == 0 OR Completed IS NULL THEN 1 ELSE 2 END ASC, " .
            "CASE WHEN Completed = 1 THEN DateCompleted ELSE " .
            "CASE WHEN Priority = '' OR Priority = 'Unassigned' THEN '7' WHEN Priority = 'In Progress' THEN '6' WHEN Priority = 'Next Release' THEN '5' WHEN Priority = 'Quick' THEN '4' WHEN Priority = 'High' THEN '3' WHEN Priority = 'Medium' THEN '2' ELSE '1' END " .
            "END DESC," .
            "r.Date DESC";
        $statement = $db->prepare( $requestsQuery );
        bindStatementsForInClause( $statement, $typeArray );

        $results = $statement->execute();

        while ($row = $results->fetchArray()) {
            $completedMark = "&#9746;";
            $completedClass = "in_progress";
            $completedMarkColor = "#6b1010";
            $dateCompleted = "";
        
            if( $row['Completed'] == 1 ) {
                if( $_SESSION['HideCompletedRequests'] == 1 ) {
                    continue;
                }
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
        
            if( IsAdminLoggedIn() ) {
                $onClick = " onclick='toggleCompleted(" . $row['ID'] . ");'";
            }
        
            echo "<td class='hidden_mobile_column' style='padding-left: 0px; width:$column1Width%; font-size:1.6em; cursor:pointer; text-align:center; font-weight:bold; color: $completedMarkColor;'> <span$onClick>$completedMark </span></td>";
        
            $priority = $row['Priority'];
            
            $priorityColor = "";

            if( $row['Completed'] != 1 ) {
                if ($priority == "Next Release") {
                    $priorityColor = "background-color:#b2e3ff;";
                } else if ($priority == "In Progress") {
                    $priorityColor = "background-color:#7cffa4;";
                } else if ($priority == "Quick") {
                    $priorityColor = "background-color:#ffe567;";
                } else if ($priority == "High") {
                    $priorityColor = "background-color:#ff9e9e;";
                } else if ($priority == "Medium") {
                    $priorityColor = "background-color:#fffca9;";
                } else if ($priority == "Low") {
                    $priorityColor = "background-color:#b2d8ff;";
                }
            }
            
            echo "<td class='hidden_mobile_column' style='width:$column2Width%; $priorityColor'>";
            
            
            if( IsAdminLoggedIn() && $row['Completed'] != 1 ) {
                echo "<select onchange='togglePriority(" . $row['ID'] . ", this.value);' id='Priority_Request' name='Priority_Request' class='text ui-widget-content ui-corner-all'>";
                echo "<option " . ( $priority == ""  ? "selected" : "" ) . " value='Unassigned'>Unassigned</option>";
                echo "<option " . ( $priority == "Next Release"  ? "selected" : "" ) . " value='Next Release'>Next Release</option>";
                echo "<option " . ( $priority == "In Progress"  ? "selected" : "" ) . " value='In Progress'>In Progress</option>";
                echo "<option " . ( $priority == "Quick"  ? "selected" : "" ) . " value='Quick'>Quick</option>";
                echo "<option " . ( $priority == "High"  ? "selected" : "" ) . " value='High'>High</option>";
                echo "<option " . ( $priority == "Medium"  ? "selected" : "" ) . " value='Medium'>Medium</option>";
                echo "<option " . ( $priority == "Low"  ? "selected" : "" ) . " value='Low'>Low</option>";
                echo "</select>";
            } else {
                echo "&nbsp;";
            }
            
            echo "</td>";
            
            echo "<td style='width:$column3Width%;'>" . getHolidayRequestItemName( strip_tags( $row['ItemName'] ) ) . "</td>";
            
            if( $title == "Requests" ) {
                echo "<td class='hidden_mobile_column' style='width:$column4Width%;'>" . strip_tags ($row['ItemType'] ) . "</td>";
            }
            
            $fullName = $row['FirstName'] . " " . $row['LastName'];
            echo "<td class='hidden_mobile_column' style='width:$column5Width%;'>" . $fullName . "</td>";
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
            echo "<td class='hidden_mobile_column' style='width:$column6Width%;'>" . $date_object->format('m/d/Y  [h:i:s A]') . "$dateCompleted</td>";
            echo "<td class='hidden_mobile_column' style='width:$column7Width%; word-break:break-word;'>" . strip_tags( $row['Note'] ) . "</td>";
            echo "</tr>";
        }

        if( $_SESSION['HideCompletedRequests'] == 1 ) {
            $numCols = 6;
            if( $title == "Requests" ) {
                $numCols++;
            }

            echo "<tr class='hidden_mobile_column completed'><td colspan='$numCols' style='text-align:center;'>Completed requests have been hidden. You can change this in <i>Preferences</i>.</td></tr>";
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    }
?>

<script>
    setupModal( "request_item" );
    setupModal( "request_feature" );
    setupModal( "report_bug" );
</script>

</body>