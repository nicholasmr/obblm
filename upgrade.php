<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009-2011. All Rights Reserved.
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

define('T_NO_STARTUP', true);
require('header.php');

?>
<html>
<head>
</head>
<body>
<br>
<big>
    <center>
        <b>OBBLM MySQL upgrade script</b>
    </center>
</big>
<br>
<small>
<?php
if (isset($_POST['version'])) {
    $upgradeMsgs = upgrade_database($_POST['version'], array('lrb' => isset($_POST['lrb']) ? $_POST['lrb'] : false));
    echo "<br><b><font color='green'>Done</font></b>";
    if (!empty($upgradeMsgs)) {
        echo "<br><br><b>IMPORTANT</b>:<br><ul>";
        echo array_strpack('<li>%s</li>', $upgradeMsgs, "\n");
        echo "</ul>";
    }
    echo "<br><hr>";
}
?>
Please make sure that the MySQL user and database you have specified in <i>settings.php</i> exist and are valid AND that the rules fields of the old settings file are consistant with the new settings file for those fields which are common.<br><br>
Now, click the appropriate SQL code to run depending on the version upgrade you are doing.<br><br>
<b>Please note:</b>
<ul>
<li>ALWAYS make sure you have a backup/dump of your OBBLM database before running the upgrade script.</li>
<li>If upgrading across two or more versions simply run the SQL code for the next version, one after the other until the latest version is reached.</li>
<li>If upgrading <i>from</i> versions previous of v. 0.75 you must consult the <i>INSTALL</i> file and run the listed SQL queries <u>manually</u>.</li>
</ul>

<br>
<form method="POST">
<table border='1' style='font-size:small; mergin: 5px;'>
    <tr style='font-weight:bold;'><td></td><td>Version upgrade</td><td>Required upgrade parameters</td></tr>
    <tr>
        <td><INPUT TYPE=RADIO NAME="version" VALUE="080-090"></td>
        <td>0.80 to 0.90</td>
        <td>&mdash;</td>
    </tr>
    <tr>
        <td><INPUT TYPE=RADIO NAME="version" VALUE="075-080"></td>
        <td>0.75 to 0.80</td>
        <td>The <u>current</u> 0.75 LRB used is: LRB5<INPUT TYPE=RADIO NAME="lrb" VALUE="5"> LRB5b/LRB6x<INPUT TYPE=RADIO NAME="lrb" VALUE="6x"></td>
    </tr>
</table>
    <br>
    <input type="submit" name='submit' value="Run upgrade SQLs" onclick="if(!confirm('Please backup your current database if you have not done so already.\n\nAre you sure you wish to continue?')){return false;}">
</form>
    <br>
    If you enjoy this software please support the further development of it by donating.<br>
    <br>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
    <input type="hidden" name="cmd" value="_s-xclick">
    <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHJwYJKoZIhvcNAQcEoIIHGDCCBxQCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYDAXl4ZznrQUskTlm4uZpyxI37sonv+BFdn4QsGv7GUzMGSR3WB/+Goi/rJytZwkE/71QLowqRZUVNWo52go7XKXkt/lE1Vh5en4FnGQzT2XLmQQeoP7EPuX8zmr6TYcSQ/QxHYcHgyNYhCDFRDEUy4kYUoU8WNNAxXagT8PbBQzTELMAkGBSsOAwIaBQAwgaQGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIoGFhfGVhqbyAgYArgtT6R30i19D1LExCFC6d4XKxaewWJYJFM4eCmkCIv+eUWRXxphelweB7+uzyvgQMeZOvZgPJAF/7EqDNakMvmlqWvvUVeCQIT8WeQMPP2y5Eybh8oRQMS0PvlVkrGj4BsUfTKvw/sz9Pg4xZVZ7YEKbNR+awZVPgd5wtaKLTqqCCA4cwggODMIIC7KADAgECAgEAMA0GCSqGSIb3DQEBBQUAMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTAeFw0wNDAyMTMxMDEzMTVaFw0zNTAyMTMxMDEzMTVaMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAwUdO3fxEzEtcnI7ZKZL412XvZPugoni7i7D7prCe0AtaHTc97CYgm7NsAtJyxNLixmhLV8pyIEaiHXWAh8fPKW+R017+EmXrr9EaquPmsVvTywAAE1PMNOKqo2kl4Gxiz9zZqIajOm1fZGWcGS0f5JQ2kBqNbvbg2/Za+GJ/qwUCAwEAAaOB7jCB6zAdBgNVHQ4EFgQUlp98u8ZvF71ZP1LXChvsENZklGswgbsGA1UdIwSBszCBsIAUlp98u8ZvF71ZP1LXChvsENZklGuhgZSkgZEwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tggEAMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADgYEAgV86VpqAWuXvX6Oro4qJ1tYVIT5DgWpE692Ag422H7yRIr/9j/iKG4Thia/Oflx4TdL+IFJBAyPK9v6zZNZtBgPBynXb048hsP16l2vi0k5Q2JKiPDsEfBhGI+HnxLXEaUWAcVfCsQFvd2A1sxRr67ip5y2wwBelUecP3AjJ+YcxggGaMIIBlgIBATCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwCQYFKw4DAhoFAKBdMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTEwMDMwMTIyMTQzMVowIwYJKoZIhvcNAQkEMRYEFN3mB1myNwGotEQV1MTNvFfRxOphMA0GCSqGSIb3DQEBAQUABIGAYnSeuLskvPZtw4HKYmhNUukMYVtZshxI1ebO9llut+PExFBdkPE7Ox0c0LfFmN+GBAntt1qE5ocKWB9WdKtjKSn3tpekXne1NUaNzq7YzQpKWornj79zhkrOEa8XjmKpV5mSN7bPaZ1AbzI1gvvXjP95lusjFCwe27npnuaSaYQ=-----END PKCS7-----
    ">
    <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
    <img alt="" border="0" src="https://www.paypal.com/da_DK/i/scr/pixel.gif" width="1" height="1">
    </form>

</small>
</body>
</html>
