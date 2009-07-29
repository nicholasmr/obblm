<?php
    require( 'class_registration.php' );
    #$username = 'funnyfingers1';
    #$password = 'testtest';
    #$email = 'funnyfingers@hotmail.com';

    if ( isset($_POST['new_name']) && isset($_POST['new_mail']) && isset($_POST['new_passwd']) )
    {

        $username = $_POST['new_name'];
        $password = $_POST['new_passwd'];
        $email = $_POST['new_mail'];
        submitForm($username, $password, $email);

    }
    else
    {

        Print "<html><body>";
        Print Registration::form();
        Print "</body></html>";

    }

    function submitForm($username, $password, $email) {

        $register = new Registration($username, $password, $email);
        Print "If you  do not see an error message ,than the registration was successful";
        Print "<br>{$register->error}<br>";

    }



?>
