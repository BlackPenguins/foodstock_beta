<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );

    $url = ADMIN_CHECKLIST_LINK;
    include( HEADER_PATH );
    
    echo "<span class='admin_box'>";
    DrawChecklistTable( $db, "RefillTrigger", "Needs Refill from Desk" );
    DrawChecklistTable( $db, "RestockTrigger", "Needs Restock from Store" );
    echo "</span>";

    function DrawChecklistTable( $db, $checklistType, $title ) {
        echo "<div class='rounded_header'><span class='title'>$title</span></div>";

        echo "<div class='center_piece'>";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr>";
        echo "<th class='admin_header_column' align='left'>&nbsp;</th>";
        echo "<th class='admin_header_column' align='left'>Name</th>";
        echo "<th class='admin_header_column' align='left'>Type</th>";
        echo "<th class='admin_header_column' align='left'>Shelf Quantity</th>";
        echo "<th class='admin_header_column' align='left'>Backstock Quantity</th>";

        echo "</tr></thead>";


        $results = getChecklistResults($db, $checklistType, "LIST" );
        while ($row = $results->fetchArray()) {
            $isDiscontinued = $row['Retired'] == 1;
            $isBought = $row['IsBought'] == 1;
            $itemID = $row['ID'];

            if( $isBought == 1 ) {
                $rowClass = "class='completed'";
            } else if( $isDiscontinued ) {
                $rowClass = "class='discontinued_row'";
            } else {
                $rowClass = "";
            }

            echo "<tr $rowClass id='checklist_row_$itemID'>";
            drawCheckListRow( $isBought, $itemID, $row['Name'], $row['Type'], $row['ShelfQuantity'], $row['BackstockQuantity'], $isDiscontinued );
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
        echo "</div>";
    }
?>

<script type="text/javascript">

    function toggleCompleted( itemID ) {
        $.post("<?php echo AJAX_LINK; ?>", {
            type:'UpdateChecklist',
            id:itemID,
        },function(data) {
            console.log("Updating row [" + itemID + "]" );
            var checklistRow = $('#checklist_row_' + itemID);
            checklistRow.html(data);

           if( checklistRow.hasClass( "completed" ) ) {
               checklistRow.removeClass( "completed" );
           } else {
               checklistRow.addClass( "completed" );
               if( checklistRow.hasClass( "discontinued_row" ) ) {
                   checklistRow.removeClass( "discontinued_row" );
               }
           }
        });
    }
</script>

</body>