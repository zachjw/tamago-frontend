﻿<?php
$connection = mysqli_connect("localhost","root","","cms") or die("Error " . mysqli_error($connection));
$sql = "SELECT id, name, latitude, longitude FROM incidents WHERE status=1";
$result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

$emparray[] = array();
while($row =mysqli_fetch_assoc($result))
{
	$emparray[] = $row;
}
echo json_encode($emparray);
mysqli_close($connection);
?>