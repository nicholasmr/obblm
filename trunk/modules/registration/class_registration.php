<?php

/*
 *  Copyright (c) William Leonard <email protected> 2009. All Rights Reserved.
 *
 *
 *  This file is part of OBBLM.
 *
 *  OBBLM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  OBBLM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

require_once('modules/registration/header.php');
 
class Registration implements ModuleInterface
{
    /***************
     * Properties 
     ***************/

    //User table values
    public $username        = '';
    public $password        = '';
    public $email           = '';
    public $error           = '';

    //admin email
    public $email_admin     = ''; //This will be concatenated to with admin emails by the sendemail() method.
                                  //If you specify email addresses here, each address must be separated by a comma.
                                  //Example: 'john@example.com, jean@example.com' or 'john@example.com'

    //Password reset specific values
    public $reset_code      = '';
    
    /***************
     * Methods 
     ***************/
    
    function __construct($username, $password = "", $email = "", $form = "register") {

        switch ( $form ){

            case "register":
                $this->username = $username;
                $this->password = $password;
                $this->email = $email;

                // Check to see if coach name already exists.
                if ( !$this->chk_username() || !$this->chk_password() || !$this->chk_email() )
                {
                    return false;  //Use ->error to display the error message to the user.
                }

                $this->create();
                if ( !$this->sendemail() )
                {
                    $this->error = SEND_EMAIL_ERROR;
                }
                break;
            case "forgot":
                $this->username = $username;

                // Check to see if coach name exists.
                if ( $this->chk_username() || strlen($this->username) < 3 )
                {
                    $this->error = USERNAME_RESET_ERROR;
                    return false;  //Use ->error to display the error message to the user.
                }
                else $this->error = "";

                $this->email = get_alt_col(USERTABLE, USERNAME, $this->username, EMAIL);
                if ( !$this->chk_email() ) return false;
                $this->createResetCode();
                $this->sendResetemail();
                break;

        }


    }

    public function chk_username() {

        $status = true;
        $min_length = 3;

        if ( get_alt_col(USERTABLE, USERNAME, $this->username, USERNAME) || strlen($this->username) < $min_length )
        {
            $this->error = USERNAME_ERROR;
            $status = false;
        }

        return $status;

    }

    public function chk_password() {

        //Will add regexp check later
        $status = true;
        $min_length = 3;

        if ( strlen($this->username) < $min_length )
        {
            $this->error = PASSWORD_ERROR;
            $status = false;
        }
        else $this->password = md5( $this->password );

        return $status;

    }

    public function chk_email() {

        #'/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' : 
        #'/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' 

        $status = true;
        $domain = '';
        if ( strpos($this->email, '@') ) list($emailuser,$domain) = split("@",$this->email);

        $emailexp = "/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i";

        if ( !preg_match($emailexp, $this->email) ) $status = false;

        if ( !getmxrr($domain, $mxrecords) || !$status )
        {
            $this->error = EMAIL_ERROR;
            $status = false;
        }

        return $status;

    }

    public function create() {

        $status = true;
        $query = sprintf( "INSERT INTO %s ( %s, %s, %s, %s, %s ) VALUES ( '%s', '%s', '%s', %d, %d )",
                 USERTABLE,
                 USERNAME, PASSWORD, EMAIL, ACTIVATION, ACCESS,
                 mysql_real_escape_string($this->username), $this->password, mysql_real_escape_string($this->email), NOT_ACTIVATED, ACCESS_LEVEL );

        $results = mysql_query($query);
        if ( !$results )
        {
            $status = false;
            $this->error = mysql_error();
        }
                            
        return $status;

    }

    public function sendemail() {

        $status = true;

        global $settings;
        $webmaster = $settings['registration_webmaster'];

        $to      = $this->AdminEmails();
        $subject = EMAIL_SUBJECT;
        $message = EMAIL_MESSAGE.$this->username.", ".$this->email."\n"."http://".$_SERVER["SERVER_NAME"]."/index.php?section=admin&subsec=ctman";
        $headers = 'From: '.$webmaster. "\r\n" .
                   'Reply-To: '.$webmaster. "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        $mailed = mail($to, $subject, $message, $headers);

        if ( !$mailed ) $status = false;

        return $status;

    }

    public function AdminEmails() {

        $email = $this->email_admin;
        $sep_comma = "";
        if ( $email ) $sep_comma = ", ";
        $coaches = Coach::getCoaches();
        foreach ( $coaches as $c )
        {
            if ( $c->ring === 0 && $this->chk_email_Admin($c->mail) )
            {
                $email = $email.$sep_comma.$c->mail;
                if ( $email ) $sep_comma = ", ";
            }

        }
        return $email;

    }

    public function chk_email_Admin($email) {

        #'/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' : 
        #'/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' 

        $status = true;
        $domain = '';
        if ( strpos($email, '@') ) list($emailuser,$domain) = split("@",$email);

        $emailexp = "/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i";

        if ( !preg_match($emailexp, $email) ) $status = false;

        if ( !getmxrr($domain, $mxrecords) || !$status )
        {
            $status = false;
        }

        return $status;

    }

    /***************
     * Statics
     ***************/
     
    private static function form() {
        
        /**
         * Creates a registration form.
         *
         * 
         **/

        $form = "
        <form method='POST' action='handler.php?type=registration'>
            <div class='adminBox'>
                <div class='boxTitle3'>
                    Register
                </div>
                <div class='boxBody'>
                    Username :<br> <input type='text' name='new_name' size='20' maxlength='50'><br><br>
                    eMail :<br> <input type='text' name='new_mail' size='20' maxlength='129'><br><br>
                    Password :<br> <input type='password' name='new_passwd' size='20' maxlength='50'><br><br>
                    *Admin activation is required.
                    <br><br>
                    <input type='submit' name='button' value='Create user'>
                </div>
            </div>
        </form>
        ";

        return $form;
    }

    private static function submitForm($username, $password, $email) {

        $register = new Registration($username, $password, $email);
        if ( !$register->error )
        {
            Print SUCCESS_MSG;
            unset($register);
        }
        else
        {
            Print "<br><b>Error: {$register->error}</b><br>";
            unset($register);
            unset($_POST['new_name']);
            unset($_POST['new_mail']);
            unset($_POST['new_passwd']);
            Registration::main(array());
        }

    }

    public static function getModuleAttributes()
    {
        return array(
            'author'     => 'William Leonard',
            'moduleName' => 'Registration',
            'date'       => '2009',
            'setCanvas'  => true,
        );
    }

    public static function getModuleTables()
    {
        return array();
    }

    public static function getModuleUpgradeSQL()
    {
        return array();
    }

    public static function main($argv) {
        
        // Module registered main function.
        global $settings;
        if ( !$settings['allow_registration'] ) die ("Registration is currently disabled.");
        $form = "";
        if ( isset($_GET['form']) ) $form = $_GET['form'];

        if ( isset($_POST['new_name']) && isset($_POST['new_mail']) && isset($_POST['new_passwd']) )
        {

            $username = $_POST['new_name'];
            $password = $_POST['new_passwd'];
            $email = $_POST['new_mail'];
            self::submitForm($username, $password, $email);
            return true;

        }
        if ( $form == "forgot" )
        {

            if ( isset($_POST['new_name']) )
            {
                $username = $_POST['new_name'];
                self::forgotsubmitForm($username);
            }
            else
            {
                Print "<html><body>";
                Print Registration::forgotform();
                Print "</body></html>";
            }
        }
        else
        {

            Print "<html><body>";
            Print Registration::form();
            Print "</body></html>";

        }
    }

    private static function forgotform() {
        
        /**
         * Creates a forgot password form.
         *
         * 
         **/

        $form = "
        <form method='POST' action='handler.php?type=registration&form=forgot'>
            <div class='adminBox'>
                <div class='boxTitle3'>
                    Register
                </div>
                <div class='boxBody'>
                    Username :<br> <input type='text' name='new_name' size='20' maxlength='50'><br><br>
                    *Instructions to reset <br>your password will be <br>emailed to you.
                    <br><br>
                    <input type='submit' name='button' value='Reset password'>
                </div>
            </div>
        </form>
        ";

        return $form;

    }

    private static function forgotsubmitForm($username) {

        $register = new Registration($username, '', '',"forgot");
        if ( !$register->error )
        {
            Print "Instructions to reset your password have been sent to the email address on file.";
            unset($register);
        }
        else
        {
            Print "<br><b>Error: {$register->error}</b><br>";
            unset($register);
            unset($_POST['new_name']);
            Registration::main(array());
        }

    }

    function createResetCode() {

        $i = 0;
        while ( $i <10 )
        {
            $this->reset_code .= dechex ( rand(0, 15) );
            $i++;
        }

    }

    function sendResetemail() {

        $status = true;

        global $settings;
        $webmaster = $settings['registration_webmaster'];

        $to      = $this->email;
        $subject = 'Password Reset Instructions';
        $message = "Your password reset code is: ".$this->reset_code;
        $headers = 'From: '.$webmaster. "\r\n" .
                   'Reply-To: '.$webmaster. "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        $mailed = mail($to, $subject, $message, $headers);

        if ( !$mailed ) $status = false;

        return $status;

    }

}

?>
