<?php
/*
 * Selects a random team from the current tournament and prints
 * team information about that team on the front page.
 *
 * Author Daniel Henriksson, 2010
 *
 */
class InFocus implements ModuleInterface
{

    public static function getModuleAttributes()
    {
        return array(
            'author'     => 'Daniel Henriksson',
            'moduleName' => 'In Focus',
            'date'       => '2010',
            'setCanvas'  => false, 
        );
    }

    public static function getModuleTables(){return array();}
    public static function getModuleUpgradeSQL(){return array();}
    public static function triggerHandler($type, $argv){    }

    //Called by Module::run, which in turns calls the function named by the first argument
    public static function main($argv)
    {
        $func = array_shift($argv);
        return call_user_func_array(array(__CLASS__, $func), $argv);
    }

    /**
     * Generates the HTML code for the inFocus box
     */
    static function renderHTML($teams){

        //Create a new array of teams to display
        $ids = array();
        foreach ($teams as $team) {
            if ($team['retired'] == 0) {
                $ids[] = $team['team_id'];
            }
        }

        //Do nothing if there aren't any valid teams 
        if (sizeof($ids) == 0) {
            return;
        }


        global $lng;

        //Select random team
        $teamKey = array_rand($ids);
        $teamId = $ids[$teamKey];
        $team = new Team($teamId);
        $teamLink =  "<a href='".urlcompile(T_URL_PROFILE,T_OBJ_TEAM,$teamId,false,false)."'>$team->name</a>";

        //Create $logo_html
        $img = new ImageSubSys(IMGTYPE_TEAMLOGO, $team->team_id);
        $logo_html = "<img border='0px' height='100' width='100' alt='Team race picture' src='".$img->getPath($team->f_race_id)."'>";

        //Create $starPlayers array used to display the three most experienced players on the team
        $players = $team->getPlayers();
        $starPlayers = array();

        foreach ($players as $p) {
            if ($p->is_dead || $p->is_sold) {
                continue;
            }

            $name = preg_replace('/\s/', '&nbsp;', $p->name);
            $spp = $p->mv_spp;
            $starPlayers[] = array('name' => $name, 'spp' => $spp);
        }

        //Sort the array
        usort($starPlayers, create_function('$objA,$objB', 'return ($objA["spp"] < $objB["spp"]) ? +1 : -1;'));

?>
    <style type="text/css">
        /* InFocus Mod */

        #inFocusBox .leftContentTd{
            font-weight: bold;
            padding-right: 1em;
        }

        #inFocusBox .teamLogo {
            float: left;
            margin: 0 36px 0 20px;
        }

        #inFocusBox .teamName {
            font-weight: bold;
        }

        #inFocusContent {
            position:relative;
            left: 160px;
            height: 80px;
        }

        #inFocusContent P {
            font-variant: small-caps;
            font-weight: bold;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        #inFocusContent DIV {
            position:absolute;
            top:0;
            left:0;
            z-index:8;
        }

        #inFocusContent DIV.invisible {
            display: none;
        }

        #inFocusContent DIV.inFocus {
            z-index:10;
            display: inline;
        }

        #inFocusContent DIV.last-inFocus {
            z-index:9;redeclare compare_spp
        }
    </style>
           <h3 class='boxTitle1' style='border-top: 1px solid;'><?php echo $lng->getTrn('name', 'InFocus'); ?></h3>
                <div class='boxBody' id="inFocusBox">
                    <div class='teamLogo'>
                        <?php echo $logo_html; ?>
                    </div>
                    <div class='teamName'>
                        <h3><?php echo $teamLink; ?></h3>
                    </div>
                    <div id="inFocusContent">
                        <div class="inFocus">
                            <p><?php echo $lng->getTrn('general_info', 'InFocus'); ?></p>
                            <table>
                                <tr>
                                    <td class="leftContentTd"><?php echo $lng->getTrn('common/coach'); ?>:</td><td><?php echo $team->f_cname; ?></td>
                                </tr>
                                <tr>
                                    <td class="leftContentTd"><?php echo $lng->getTrn('common/race'); ?>:</td><td><?php echo $team->f_rname; ?></td>
                                </tr>
                                <tr>
                                    <td class="leftContentTd"><?php echo $lng->getTrn('team_value', 'InFocus'); ?>:</td><td><?php echo (string)($team->tv / 1000); ?>k</td>
                                </tr>
                            </table>
                        </div>
                        <div class="invisible">
                            <p><?php echo $lng->getTrn('star_players', 'InFocus'); ?></p>
                            <table>
<?php
        $maxPlayers = 3;
        $counter = 0;
        foreach($starPlayers as $player) {
            echo "<tr><td class='leftContentTd'>".$player['name']."</td><td>".$player['spp']." spp</td></tr>";
            $counter++;
            if ($counter >= $maxPlayers) {
                break;
            }
        }
?>
                            </table>
                        </div>
                    </div>
                </div>
        <script>
        /* 
         * This script creates a slideshow of all <div>s in the "inFocusContent" div
         * 
         * Based on an example by Jon Raasch:
         *
         * http://jonraasch.com/blog/a-simple-jquery-slideshow
         */
        function nextContent() {
            var $currentDiv = $('#inFocusContent DIV.inFocus');

            var $nextDiv = $currentDiv.next().length ? $currentDiv.next() : $('#inFocusContent DIV:first');

            $currentDiv.addClass('last-inFocus');

            //Fade current out
            $currentDiv.animate({opacity: 0.0}, 500, function() {
                $currentDiv.removeClass('inFocus last-inFocus');
                $currentDiv.addClass('invisible');
            });

            //Fade next in
            $nextDiv.css({opacity: 0.0})
                .addClass('inFocus')
                .animate({opacity: 1.0}, 500, function() {
                });
        }

        $(function() {
            setInterval( "nextContent()", 5000 );
        });

        </script>
<?php
    }
}

?>
