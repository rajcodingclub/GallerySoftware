<?php
$servername="localhost";
$username="root";
$dbpassword="mysql";
$dbname="updated_gallery";

$con=mysqli_connect($servername,$username,$dbpassword,$dbname);
if(!$con){
    die('connection failed'.mysqli_connect_error()) ;
}
?>