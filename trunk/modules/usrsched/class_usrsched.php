<?php
/*
 * Adds the opportunity for users to schedule their own games, within the bounds of a special round type.
 * Currently, only teams that have played at least one game are allowed to add new games.
 *
 * Author Daniel Henriksson, 2010
 *
 */

define('RT_USERSCHEDULED', 100); 

class UserScheduledGames implements ModuleInterface
{

    public static function getModuleAttributes()
    {
        return array(
            'author'     => 'Daniel Henriksson',
            'moduleName' => 'User Scheduled Games',
            'date'       => '2010',
            //This is set to true as this module uses a separate page, but this also means some of the HTML has to be
            //returned as text instead of printed to avoid the canvas being generated in-line.
            'setCanvas'  => true,
        );
    }

    public static function getModuleTables(){return array();}
    public static function getModuleUpgradeSQL(){return array();}
    public static function triggerHandler($type, $argv){}

    /*
     * Called by Module::run, and simply calls the specified method
     */
    public static function main($argv)
    {
        global $settings;
        if ( !$settings['usrsched_enabled'] ) die ("User Scheduler is currently disabled.");
        $func = array_shift($argv);
        return call_user_func_array(array(__CLASS__, $func), $argv);
    }

    /**
     * Generates the HTML for the add-team page.
     */
    private function renderAddGamePageHTML() {
    
        global $coach;
        global $lng;
        global $tours, $divisions, $leagues;
        global $settings;
        $lid_selected = HTMLOUT::getSelectedNodeLid();
        
        if (!is_object($coach)){
            status(false, "You must be logged in to schedule games");
            return;
        }

?>
    <div style='padding-top:40px; text-align: center;'>
    <form method="POST" action="">
        <b><?php echo $lng->getTrn('common/tournament'); ?></b>
            <select name='tour_id'>
                <?php
                foreach ($tours as $trid => $tr) {
                    if ($settings['usersched_local_view'] && $divisions[$tr['f_did']]['f_lid'] != $lid_selected) {
                        continue;
                    }
                    if ($tr['type'] == TT_FFA) {
                        echo "<option value='$trid'>".$leagues[$divisions[$tr['f_did']]['f_lid']]['lname'].", ".$divisions[$tr['f_did']]['dname'].": $tr[tname]</option>\n";
                    }
                }
                ?>
            </select>
        <b><?php echo $lng->getTrn('own_team', 'UserScheduledGames'); ?></b>
            <select name='own_team'>
<?php
        //Sort according to name
        foreach ($coach->getTeams() as $t) {
            if (!$t->rdy || $t->is_retired)
                continue;
            echo "<option value='$t->team_id'>$t->name</option>\n";
        }
?>
            </select>

        <b><?php echo $lng->getTrn('opposing_team', 'UserScheduledGames'); ?></b>
            <input type="text" id='opposing_team_autoselect' name="opposing_team_autocomplete" size="30" maxlength="50">
            <input type="hidden" id='opposing_team' name="opposing_team"> 

        <script>
            $(document).ready(function(){
                var options, b;

                options = { 
                    minChars:2, 
                        serviceUrl:'handler.php?type=autocomplete&obj=<?php echo T_OBJ_TEAM;?>',
                        onSelect: function(value, data){ $('#opposing_team').val(data); },
                };
                b = $('#opposing_team_autoselect').autocomplete(options);
            });
        </script>

        <input type="submit" name="creategame" value="<?php echo $lng->getTrn('add_game', 'UserScheduledGames'); ?>">
    </form>
    </div>
<?php
    } 


    /*
     * Called once the teams have been selected, actually creates the match
     */
    public static function creategame() {
    
        global $coach;
        
        //Check format of tour_id
        if (!(isset($_POST['tour_id']) && !preg_match("/[^0-9]/", $_POST['tour_id']))) {
            fatal ('Tournament Id not set, or could not be parsed');
            return;
        }

        //Check validity of tour_id
        if (!is_object($tour = new Tour($_POST['tour_id'])) || empty($tour->date_created)) {
            fatal('Sorry. Invalid tournament ID specified.');
            return;
        }

        //Ensure logged in
        if (!is_object($coach)) {
            fatal('You must log in to add new games.');
            return;
        }

        global $lng, $settings, $coach;

        $tourId = $_POST['tour_id'];
        $own_team = $_POST['own_team'];
        $opposing_team = $_POST['opposing_team'];

        list($exitStatus, $mid) = Match::create(array(
            'team1_id'  => $own_team,
            'team2_id'  => $opposing_team,
            'round'     => 1,
            'f_tour_id' => $tourId
        ));

        $status = true;
        $status &= !$exitStatus;

        status($status, $exitStatus ? Match::$T_CREATE_ERROR_MSGS[$exitStatus] : null);

        //If successful, redirect user to newly created match report
        if($status) { ?>
            <script type="text/javascript">
                //window.location = "index.php?section=matches&type=tourmatches&trid=<?php echo $tourId;?>";
            window.location = "index.php?section=matches&type=report&mid=<?php echo $mid; ?>";
            </script><?php

        //Otherwise, print error and input page again
        } else {
            self::renderAddGamePageHTML();
        }
    }

}
?>
