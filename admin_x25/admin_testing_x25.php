<?php

    include(__DIR__ . "/../appendix.php");
    include_once(HANDLE_FORMS_PATH);

    $dbPath = getTestDB();


    echo "<h1>Testing with [$dbPath] Database</h1>";

    $db = new SQLite3( $dbPath );
    if (!$db) die ($error);

    echo "<table style='border-collapse:collapse; width: 100%;'>";
    echo "<tr>";
    echo "<th>Status</th><th>Test Name</th><th>Expected</th><th>Actual</th>";
    echo "</tr>";

    addItem( $db, "Bobbles", "FF3300", 0.58, "Snack" );
    addItem( $db, "Norbles", "FF3300", 4.89, "Soda" );

    assertColumn( $db, "Add Snack - Price" , "SELECT Price From Item WHERE Name = 'Bobbles'", "58" );
    assertColumn( $db, "Add Snack - Name" , "SELECT Name From Item WHERE Name = 'Bobbles'", "Bobbles" );
    assertColumn( $db, "Add Snack - Type" , "SELECT Type From Item WHERE Name = 'Bobbles'", "Snack" );

    assertColumn( $db, "Add Soda - Price" , "SELECT Price From Item WHERE Name = 'Norbles'", "489" );
    assertColumn( $db, "Add Soda - Name" , "SELECT Name From Item WHERE Name = 'Norbles'", "Norbles" );
    assertColumn( $db, "Add Soda - Type" , "SELECT Type From Item WHERE Name = 'Norbles'", "Soda" );

    echo "</table>";
    
    function assertColumn( $db, $title, $sql, $expected ) {
        $results = $db->query( $sql );
        $resultRow = $results->fetchArray();
        $actual = $resultRow[0];

        $pass = $actual == $expected;
        $rowColor = $pass ? "#73ffa5" : "#ff7373";
        $passLabel = $pass ? "PASSED" : "FAILED";

        $style = "style='background-color:$rowColor; padding: 15px; border:#000000 1px solid;'";

        echo "<tr>";
        echo "<td $style>$passLabel</td>";
        echo "<td $style>$title</td>";
        echo "<td $style>$expected</td>";
        echo "<td $style>$actual</td>";
        echo "<tr>";
    }

?>