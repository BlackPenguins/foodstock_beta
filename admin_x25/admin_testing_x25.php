<?php

    include(__DIR__ . "/../appendix.php");
    include_once(ACTION_FUNCTIONS_PATH);
    include_once(TESTING_BASE_OBJ);
    include_once(TESTING_OBJ);
    include_once(ITEM_COST_DETAILS_OBJ);
    include_once(USER_OBJ);

    ini_set('max_execution_time', 3600);
    $dbPath = getTestDB();

    // Global Vars
    $sodaShelfQuantity = 0;
    $sodaBackstockQuantity = 0;
    $sodaItemIncome = 0;
    $sodaItemExpenses = 0;
    $sodaSiteIncome = 0;
    $sodaSiteExpenses = 0;


    echo "<h1>Testing with [$dbPath] Database</h1>";

    $db = new SQLite3( $dbPath );
    if (!$db) die ($error);

    echo "<table style='border-collapse:collapse; width: 100%;'>";
    echo "<tr>";
    echo "<th>Status</th><th>Test Name</th><th>Actual</th><th>Expected</th>";
    echo "</tr>";

    $mainTest = new Testing();
    $mainTest->runTest( $db );

    echo "</table>";
?>