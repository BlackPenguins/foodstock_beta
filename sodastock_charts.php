<?php
date_default_timezone_set('America/New_York');

$db = new SQLite3('db/item.db');
if (!$db) die ($error);

/*
$previousAmounts = array();
$idOrder = array();

echo "<table>";
echo "<thead><tr>";
    echo "<th style='padding:5px; border:1px #000 solid;' align='left'>&nbsp;</th>";
$results = $db->query('SELECT ID, Name from Item order by totalcans desc');
while ($row = $results->fetchArray()) {
    echo "<th style='padding:5px; border:1px #000 solid;' align='left'>$row[1] ($row[0])</th>";
    $previousAmounts[$row[0]] = 0;
    $idOrder[] = $row[0];
}
echo "</tr></thead>";

$previousTime = "";
$results = $db->query('SELECT s.Name, r.Date, s.ID, r.ShelfQuantity, r.BackstockQuantity, r.Price FROM Daily_Amount r JOIN Item s ON r.itemID = s.id  WHERE r.Date > "2015-04-03 15:47:08" ORDER BY r.Date ASC');
while ($row = $results->fetchArray()) {
    $currentTime = $row[1];
    error_log("Current Time: [" . $currentTime . "]");
    if( $previousTime == "" || $currentTime != $previousTime ) {
        error_log("NEW TIME: [" . $currentTime . "|". $previousTime . "]");
        if($previousTime != "") {
            error_log("-- NOT FIRST TIME");
            // Not the first time
            echo "<td>" . $previousTime . "</td>";
            foreach($idOrder as $currentID) {
                echo "<td>" . $previousAmounts[$currentID] . "</td>";
            }
            
            // Go through the array and print the data
            echo "</tr>";
        }
        $previousTime =  $currentTime;
        echo "<tr>";
        
        
    }
    
    error_log("Storing [$row[0] ($row[2])] with [$row[3]]");
    $previousAmounts[$row[2]] = $row[3];
}
echo "</tr>";
echo "</table>";

echo "<table>";
echo "<thead><tr>";
    echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
    echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
    echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Shelf Quantity</th>";
    echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Backstock Quantity</th>";
    echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Price</th>";

    echo "</tr></thead>";
    

$results = $db->query('SELECT s.Name, r.Date, r.ShelfQuantity, r.BackstockQuantity, r.Price FROM Daily_Amount r JOIN Item s ON r.itemID = s.id  WHERE r.Date > "2015-04-03 15:47:08" ORDER BY r.Date ASC');
while ($row = $results->fetchArray()) {
    echo "<tr>";
    echo "<td style='padding:5px; border:1px #000 solid;'>$row[0]</td>";
    echo "<td style='padding:5px; border:1px #000 solid;'>$row[1]</td>";
    echo "<td style='padding:5px; border:1px #000 solid;'>$row[2]</td>";
    echo "<td style='padding:5px; border:1px #000 solid;'>$row[3]</td>";
    echo "<td style='padding:5px; border:1px #000 solid;'>$row[4]</td>";
    echo "</tr>";

    //if($dailyData[$row[1]]) {}
}
echo "</table>";
*/
?>

<!--Load the AJAX API-->
    <script type="text/javascript"
          src="https://www.google.com/jsapi?autoload={
            'modules':[{
              'name':'visualization',
              'version':'1',
              'packages':['corechart']
            }]
          }"></script>

    <script type="text/javascript">
      google.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable([
        <?php
            $previousAmounts = array();
            $idOrder = array();


            echo "[";
            echo "'Date' ";
            $results = $db->query("SELECT ID, Name from Item WHERE Type ='" . $itemType . "' order by totalcans desc");
            while ($row = $results->fetchArray()) {
                echo ",";
                echo "'$row['Name']'";
                $previousAmounts[$row['ID']] = 0;
                $idOrder[] = $row['ID'];
            }
            echo "]";

            $previousTime = "";
            $results = $db->query('SELECT s.Name, r.Date, s.ID, r.ShelfQuantity, r.BackstockQuantity, r.Price FROM Daily_Amount r JOIN Item s ON r.itemID = s.id  WHERE r.Date > "2015-04-03 15:47:08" ORDER BY r.Date ASC');
            while ($row = $results->fetchArray()) {
                $currentTime = $row['Date'];
                //error_log("Current Time: [" . $currentTime . "]");
                if( $previousTime == "" || $currentTime != $previousTime ) {
                    error_log("NEW TIME: [" . $currentTime . "|". $previousTime . "]");
                    if($previousTime != "") {
                        //error_log("-- NOT FIRST TIME");
                        // Not the first time
                        $dateForms = DateTime::createFromFormat('Y-m-d H:i:s', $previousTime);
                        echo "'" . $dateForms->format('m/d/Y') . "'";
                        foreach($idOrder as $currentID) {
                            echo ",";
                            echo $previousAmounts[$currentID];
                        }
                        
                        // Go through the array and print the data
                        echo "]";
                    }
                    $previousTime =  $currentTime;
                    echo ",\n[";
                    
                    
                }
                
                //error_log("Storing [$row[0] ($row[2])] with [$row[3]]");
                $previousAmounts[$row['ID']] = $row['ShelfQuantity'];
            }
            
            // That last row would be ignored, so print it now
            echo "'" . $previousTime . "'";
            foreach($idOrder as $currentID) {
                echo ",";
                echo $previousAmounts[$currentID];
            }
            
            // Go through the array and print the data
            echo "]";
        ?>
        ]);

        var options = {
          title: 'Item Inventory',
          width: 8000,
          height: 800,
          chartArea: {
            left:150,
            top:40,
            bottom:300
          },
          curveType: 'function',
          legend: { position: 'bottom' },
          vAxis: {
            viewWindow: {
                min: 0.0,
                max: 30.0
            }
          },
          hAxis: {
            showTextEvery: 4
          }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);
      }
    </script>
    
<?php
echo "<div id='curve_chart'></div>";
?>