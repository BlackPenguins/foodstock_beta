<html>
<head>
<?php
include( "appendix.php" );

session_start();
$lastPage = SODASTOCK_LINK;
session_destroy();
?>

<meta HTTP-EQUIV="REFRESH" content="0; url= <?php echo $lastPage; ?>">
</head>
</html>