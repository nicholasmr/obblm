<?php

class Email
{
    private static function checkEmail($email) {
        #'/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' : 
        #'/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' 
        $status = true;
        $domain = '';
        if (strpos($email, '@')) 
            list($emailuser,$domain) = split("@",$email);
        $emailexp = "/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i";
        if (!preg_match($emailexp, $email)) 
            $status = false;
        if (!getmxrr($domain, $mxrecords) || !$status)
            $status = false;
        return $status;
    }
    
    public static function getAdministratorEmails() {
        $email = '';
        $sep_comma = "";
        $coaches = Coach::getCoaches();
        foreach ($coaches as $c) {
            if ($c->ring === Coach::T_RING_GLOBAL_ADMIN && Email::checkEmail($c->mail)) {
                $email = $email.$sep_comma.$c->mail;
                if ( $email ) 
                    $sep_comma = ", ";
            }
        }
        return $email;
    }
    
    public static function getLeagueCommissionerEmails($leagueID) {
        $email = '';
        $sep_comma = "";
        $coaches = Coach::getCoaches();
        foreach ($coaches as $c) {
            if($c->isNodeCommish(T_NODE_LEAGUE, $leagueID) && Email::checkEmail($c->mail)) {
                $email = $email.$sep_comma.$c->mail;
                
                if ( $email ) 
                    $sep_comma = ", ";
            }
        }
        return $email;
    }
}