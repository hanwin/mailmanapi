<?php
    require("config.php");
    //unsubscribe from the list
    $mail = "Test1 <test1@testmail.c>";
    $conn->unsubscribe($mail);
    echo $conn->roster();

    //unsubscribe multiple addresses at once
    $arr_mail = array();
    $arr_mail[0] = "Test2 <test2@testmail.c>";
    $arr_mail[1] = "Test3 <test3@testmail.c>";
    $conn->unsubscribe($arr_mail);

    echo $conn->roster();
?>
