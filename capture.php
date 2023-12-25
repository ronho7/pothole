<?php
$conn = mysqli_connect("", "", "", "");
$sql  = "INSERT INTO pothole (latitude,longitude) VALUES (0,0)";
$result = mysqli_query($conn,$sql);
$lastid = mysqli_insert_id($conn);
$imageData = $_POST['imageData'];
$imageData = str_replace('data:image/png;base64,', '', $imageData);
$imageData = base64_decode($imageData);
$filename = 'image/' . $lastid . '.png';
file_put_contents($filename, $imageData);
$conn->close();
echo $lastid;
?>