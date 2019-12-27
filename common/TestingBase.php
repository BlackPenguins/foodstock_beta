<?php
class TestingBase
{
    static $totalTests = 0;
    static $totalPasses = 0;

    static $startTime = 0;

    static $currentSection = null;
    static $totalTestsSection = 0;
    static $totalPassesSection = 0;

    static $startTimeSection = 0;

    function isHidingSlack() {
        return true;
    }

    function addSection($title)
    {

        if( TestingBase::$currentSection != null && TestingBase::$totalTestsSection > 0 ) {
            $percentagePassed = (TestingBase::$totalPassesSection / TestingBase::$totalTestsSection) * 100;
            $percentagePassed = round($percentagePassed, 0);
            $totalTime = time() - TestingBase::$startTimeSection;
            $testStatus = TestingBase::$totalPassesSection != TestingBase::$totalTestsSection ? ":x:" : ":heavy_check_mark:";

//            sendSlackMessageToMatt("$testStatus Section *[" . TestingBase::$currentSection . "]* Complete - (" . TestingBase::$totalPassesSection . "/" . TestingBase::$totalTestsSection . ") tests passed - *$percentagePassed%* [$totalTime seconds]", ":chart_with_upwards_trend:", "FOODSTOCK TESTING", "#b7ab1a");
        }

        TestingBase::$currentSection = $title;
        TestingBase::$totalPassesSection = 0;
        TestingBase::$totalTestsSection = 0;
        TestingBase::$startTimeSection = time();

        echo "<tr>";
        echo "<td style='background-color: #506ab3; padding:10px; text-align: center; font-weight: bold; color: #fffac0; font-family: \"Roboto\", Arial; font-size: 1.2em; border: 1px solid #000000' colspan='4'>$title</td>";
        echo "<tr>";
    }

    function addDatapoint($message)
    {
        $pattern = '/(\[.*?\])/i';
        $replacement = "<span style='font-weight:bold; margin-left: 5px; margin-right: 5px;'>$1</span>";
        $message = preg_replace($pattern, $replacement, $message);

        $style = "background-color: #6FE5EA; padding:5px; color: #000000; font-family: \"Roboto\", Arial; font-size: 0.8em; border: 1px solid #000000;";
        echo "<tr>";
        echo "<td style='$style text-align:center;'>INFO</td>";
        echo "<td style='$style text-align:left;' colspan='3'>$message</td>";
        echo "<tr>";
    }

    function assertText($title, $actual, $expected)
    {
        $pass = $actual == $expected;

        if( !$pass && is_numeric( $actual ) && is_numeric( $expected ) ) {
            $actual = round($actual, 2);
            $expected = round($expected, 2);
            $pass = $actual == $expected;
        }

        TestingBase::$totalTests++;
        TestingBase::$totalTestsSection++;

        $rowColor = "#d34e4e";
        $passLabel = "FAILED";

        if( $pass ) {
            TestingBase::$totalPasses++;
            TestingBase::$totalPassesSection++;
            $rowColor = "#4ed37e";
            $passLabel = "PASSED";
        }

        $actualVarDump = "";
        $expectedVarDump = "";

        // Set to true when need to get debug info with inequalities
        if (false) {
            ob_start();
            var_dump($actual);
            $actualVarDump = " [" . ob_get_clean() . "] [$pass]";

            ob_start();
            var_dump($expected);
            $expectedVarDump = " [" . ob_get_clean() . "] [$pass]";
        }

        $style = "background-color:$rowColor; padding: 5px; border:#000000 1px solid; font-family: \"Roboto\", Arial; font-size: 0.8em;";

        echo "<tr>";
        echo "<td style='$style text-align:center;'>$passLabel</td>";
        echo "<td style='$style'>$title</td>";
        echo "<td style='$style'>$actual$actualVarDump</td>";
        echo "<td style='$style'>$expected$expectedVarDump</td>";
        echo "<tr>";
    }

    /**
     * @param $db SQLite3
     * @param $title
     * @param $sql
     * @param $expected
     */
    function assertColumn($db, $title, $sql, $expected)
    {
        $actual = $this->getValue($db, $sql);
        $this->assertText($title, $actual, $expected);
    }

    /**
     * @param $db SQLite3
     * @param $sql
     */
    function getValue($db, $sql)
    {
        $results = $db->query($sql);
        $resultRow = $results->fetchArray();
        return $resultRow[0];
    }
}
?>