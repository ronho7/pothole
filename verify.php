<?php
    include '../functions.php';
    $conn = mysqli_connect("", "", "", "");
    $result = mysqli_query($conn, "SELECT * FROM potholeuser WHERE uniqueID='" . $_REQUEST['code'] . "' AND verified=0");
    if ($result->num_rows > 0) {
        $pothole = $result->fetch_assoc();
        $body = '
            <body bgcolor=black>
            <font color=blue>You are verified.</font>
            </body>
        ';
        $result = mysqli_query($conn, "UPDATE potholeuser SET verified = 1 WHERE uniqueID='" . $_REQUEST['code']. "'");
        setcookie('userID', $pothole['ID'], time() + (86400 * 365), "/"); // 365 days
        sendmail($pothole['email'],'Pothole user','Verification',$body);
        $conn->close();
        header('location: main.php');
    } else {
        ECHO 'Sorry, this account belongs to someone else.';
        /*
        $sql = "DELETE FROM potholeuser WHERE uniqueID='" . $_REQUEST['code'] . "'";
        $delete_result=mysqli_query($conn, $sql);
        $conn->close();
        */
    }

?>