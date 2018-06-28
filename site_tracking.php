<?php
    $ip = $_SERVER["REMOTE_ADDR"];
    $date = date('Y-m-d H:i:s', time());
    $agent = "Not Found";
    
    if(isset($_SERVER['HTTP_USER_AGENT']) == true)
        $agent = $_SERVER['HTTP_USER_AGENT'];
    
    mysql_connect("127.0.0.1","root","symbg1730");
    mysql_select_db("mcprem") or die( "Unable to select database");
    $query = "INSERT INTO pagevisits VALUES ('".$pageName."','".$ip."','".$date."','".$agent."')";
    mysql_query($query);
    mysql_close();
    
                                            
    /* OLD FLAT FILE WAY
    $myFile = "data/page-views-".$pageName.".dat";
    $fh = fopen($myFile, 'a') or die("can't open file to write");
    $stringData = $_SERVER["REMOTE_ADDR"]."-".date("M j, Y [h:i:s A]")."-".$_SERVER['HTTP_USER_AGENT'];
    fwrite($fh, $stringData."\n");
    fclose($fh);
    */
?>