<?php
// User table information
define('USERTABLE', 'coaches');
define('USERNAME', 'name');
define('PASSWORD', 'passwd');
define('EMAIL', 'mail');
define('ACTIVATION', 'retired');
define('NOT_ACTIVATED', 1);
define('IS_ACTIVATED', 0);
define('ACCESS', 'ring');
define('ACCESS_LEVEL', 2);

// Error messages *NOTE: Error messages are not concatenated as only one needs to be seen by the user.
define('USERNAME_ERROR', 'The username already exists or must be at least 3 characters long.');
define('PASSWORD_ERROR', 'The password must be at least 5 characters long');
define('EMAIL_ERROR', 'The email address is not valid.');
define('EMAIL_SUBJECT', 'New user registration');
define('EMAIL_MESSAGE', 'You have received a new registration.  To activate the user you must un-retire the coach using the admin page.

coach: ');
define('SEND_EMAIL_ERROR', 'Registration was successful, but the administrators were not notified via email.  It may take longer to activate your account.');
define('SUCCESS_MSG', 'Registration was successful.  A site administrator will enable your account or contact you for verification.');
define('USERNAME_RESET_ERROR', 'The username does not exist.');
?>
