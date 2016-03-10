<?php
    include_once 'easepay-lib.php';
    $SALT='XXXXXXXXX';
    $result = response( $_POST, $SALT );
    print_r($result);
    print_r($_POST);
    

?>
