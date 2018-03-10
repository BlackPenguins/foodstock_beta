<html>
<head>
<?php
session_start();
$lastPage = "sodastock.php";
session_destroy();
?>

<meta HTTP-EQUIV="REFRESH" content="0; url= <?php echo $lastPage; ?>">
</head>
</html>