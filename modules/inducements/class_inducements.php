<?php

/*
 *  Copyright (c) Daniel Straalman <email protected> 2009. All Rights Reserved.
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

class IndcPage implements ModuleInterface
{

    public static function getModuleAttributes()
    {
        return array(
            'author'     => 'Daniel Straalman',
            'moduleName' => 'Inducements',
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

    public static function triggerHandler($type, $argv){}

    static public function main($argv)
    {
        // Check if teamid is provided, else show error mess
        $team_id = $_GET['team_id'];
        if (!get_alt_col('teams', 'team_id', $team_id, 'team_id'))
            fatal("Invalid team ID.");

        global $stars, $DEA, $rules, $skillarray, $inducements, $racesNoApothecary, $starpairs;

        // Move these constants to header.php?
        define('MAX_STARS', 2);
        define('MERC_EXTRA_COST', 30000);
        define('MERC_EXTRA_SKILL_COST', 50000);

        $ind_cost=0;
        $redirectlink = 'handler.php?type=roster&detailed=0&team_id='.$team_id;

        $t = new Team($team_id);

        $star_list[0] = '      <option value="0">-No Induced Stars-</option>' . "\n";
        foreach ($stars as $s => $d) {
            if (in_array($t->f_race_id, $d['races'])) { // Only display available Stars
                if (in_array($d['id'], $starpairs)) // Hide Child Stars
                    $star_list[0] .= "      <option ".((in_array($t->f_race_id, $d['races'])) ? 'style="display: none; background-color: '.COLOR_HTML_READY.';" ' : '')."value=\"$d[id]\">$s</option>\n";
                else
                    $star_list[0] .= "      <option ".((in_array($t->f_race_id, $d['races'])) ? 'style="background-color: '.COLOR_HTML_READY.';" ' : '')."value=\"$d[id]\">$s</option>\n";
            }
        }

?>
<!--
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<link type="text/css" href="css/stylesheet1.css" rel="stylesheet">
-->
<style type="text/css">
th { background-color: #EEEEEE; color: #000000; font: bold 12px Tahoma; }
th.left { text-align: left; }
td { background-color: #EEEEEE; color: #000000; font: 13px Tahoma; }
td.boxie { background-color: #EEEEEE; color: #000000; }
td.cent { text-align: center; }
td.cent2 { text-align: center; background-color: #EEEEEE; color: #000000; }
td.right { text-align: right; background-color: #EEEEEE; color: #000000; }
</style>
<script language="javascript">
<!--
function SendToPDF()
{
    document.InduceForm.action = "<?php print $redirectlink ?>"    // Redirect to pdf_roster
    document.InduceForm.submit();        // Submit the page
    return true;
}
-->
</script>
<!--
</head>
<body>
<div class="everything">
<div class="section">
-->
<?php title('Inducements try-out');?>
<form action="" method="post" name="InduceForm">
<table> <!-- Star Players -->
    <tr>
        <th class="left">Star Name</th>
        <th>Cost</th>
        <th>MA</th>
        <th>ST</th>
        <th>AG</th>
        <th>AV</th>
        <th class="left">Skills</th> <!-- <td>Cp</td><td>Td</td><td>Int</td><td>Cas</td><td>BH</td><td>Si</td><td>Ki</td><td>MVP</td><td>SPP</td> -->
    </tr>
<?php
        $i=1;
        $starcnt = 1;
        while ($i <= MAX_STARS) {
            print "  <tr>\n";
            if (array_key_exists("Star$starcnt", $_POST)) {
                $sid=$_POST["Star$starcnt"];
                if ($sid != 0) {
                    $s = new Star($sid);
                    $star_list[$starcnt] = $star_list[0];
                    // Update display of selected Star
                    // Ignore if Child Star, will be handled by parent entry
                    if (!(in_array($sid, $starpairs))) {
                        $star_list[$starcnt] = str_replace('option value="'.$sid.'"','option selected value="'.$sid.'"',$star_list[$starcnt]);
                        $star_list[$starcnt] = str_replace('option style="background-color: '.COLOR_HTML_READY.';" value="'.$sid.'"', 'option selected style="background-color: '.COLOR_HTML_READY.';" value="' . $sid.'"', $star_list[$starcnt]);
                        // Remove selected player from default list
                        $star_list[0] = str_replace('<option value="'.$sid.'">'.$s->name."</option>\n",'',$star_list[0]);
                        $star_list[0] = str_replace('option style="background-color: '.COLOR_HTML_READY.';" value="'.$sid.'">'.$s->name."</option>\n", '', $star_list[0]);
                        // Display Star entry
                        print '    <td class="boxie"><SELECT name="Star' . $starcnt . '" onChange="this.form.submit()">' . "\n";
                        print $star_list[$starcnt];
                        print '    </SELECT></td>' . "\n";
                        print '<td class="cent">'.str_replace('000','',$s->cost)."k</td>\n<td class=\"cent\">".
                            $s->ma."</td>\n<td class=\"cent\">".$s->st."</td>\n<td class=\"cent\">".$s->ag."</td>\n<td class=\"cent\">".$s->av."</td>\n<td>\n<small>".skillsTrans($s->skills)."</small></td>\n";
                        print "</tr>\n";
                        $ind_cost+=$s->cost;
                    }
                    // Check for child
                    if (array_key_exists($sid, $starpairs)) {
                        // Parent Star selected
                        $starcnt++;
                        $sid = $starpairs[$sid];
                        $s = new Star($sid);
                        // Display Child
                        print '    <td class="boxie"><SELECT disabled name="Star' . $starcnt . '">' . "\n";
                        print '    <option value="'.$sid.'">'.$s->name.'</option>' . "\n";
                        print '    </SELECT></td>' . "\n";
                        print '<td class="cent">'.str_replace('000','',$s->cost)."k</td>\n<td class=\"cent\">".
                            $s->ma."</td>\n<td class=\"cent\">".$s->st."</td>\n<td class=\"cent\">".$s->ag."</td>\n<td class=\"cent\">".$s->av."</td>\n<td>\n<small>".skillsTrans($s->skills)."</small></td>\n";
                        print "</tr>\n";
                    } 
                    $i++;
                    $starcnt++;
                    // Back to start of while
                    continue;
                }
            }
        print '    <td class="boxie"><SELECT name="Star' . $starcnt . '" onChange="this.form.submit()">' . "\n";
        print $star_list[0];
        print '    </SELECT>' . "\n";
        print '</tr>' . "\n";
        $i++;
        $starcnt++;
        break;
        }
?>
</table> <!-- End of Star Player Table -->
<table> <!-- Mercenaries Table -->
    <tr>
        <td class="indtitle">Mercenaries</td>
        <td class="indtitle">Position</td>
        <td class="indtitle">Cost</td>
        <td class="indtitle">MA</td>
        <td class="indtitle">ST</td>
        <td class="indtitle">AG</td>
        <td class="indtitle">AV</td>
        <td class="indtitle">Skills</td>
        <td class="indtitle">Extra Skill</td>
    </tr>
<?php
        // Validate to not exceed maximum number of positionals? Leaving it open for now.
        $merc_list[0] = '            <option value="0">-No Induced Mercs-</option>' . "\n";
        $merc = array(0=>'No Merc');
        $i=0;
        foreach ($DEA[$t->f_rname]["players"] as $p => $m) {
            $i++;
            $merc_list[0] .= '            <option value="'."$i".'">'."Merc $p".'</option>' . "\n";
            array_push($merc, $m);
            $pos[$i] = $p;
        }
        $i=1;
        while (isset($_POST["Merc$i"])) {
            print "    <tr>\n";
            if ($_POST["Merc$i"] != '0') {
                $mid=$_POST["Merc$i"];
                if (isset($_POST["Extra$i"])) {
                    $extra_skill_cost = ($_POST["Extra$i"] == '-No Extra Skill-') ? 0 : MERC_EXTRA_SKILL_COST;
                    $extra[$i] = $_POST["Extra$i"];
                } else {
                    $extra_skill_cost = 0;
                    $extra[$i] = false;
                }   
                // Fill skill list from what normal skills positional has to chose from
                $n_skills = $DEA[$t->f_rname]['players'][str_replace('Merc ','',$pos[$mid])]['norm'];
                $extra_list[$i] = "            <option>-No Extra Skill-</option>\n";
                foreach ($n_skills as $category) {
                    foreach ($skillarray[$category] as $id => $skill) {
                        if (!in_array($id, $merc[$mid]["def"])) {
                            $extra_list[$i] .= '<option>'.skillsTrans($id).'</option>'."\n";
                        }
                    }
                }
                $merc_list[$i] = str_replace('<option value="'.$mid.'"','<option selected value="'.$mid.'"', $merc_list[0]);
                print '        <td><SELECT name="Merc' . $i . '" onChange="this.form.submit()">' . "\n";
                print $merc_list[$i];
                $cost[$i] = (int) $merc[$mid]["cost"] + MERC_EXTRA_COST + $extra_skill_cost;
                echo "        </SELECT></td>\n";
                if (!in_array(99, $merc[$mid]["def"]))
                    array_unshift($merc[$mid]["def"], 99);  // Adding Loner to default skills if Merc does not have Loner already
                $def_skills = skillsTrans($merc[$mid]["def"]);
                if (empty($def_skills)) $def_skills[] = '&nbsp;';
                    print "        <td>$pos[$mid]</td><td>".str_replace('000','',$cost[$i])."k</td><td class=\"cent\">".$merc[$mid]["ma"]."</td><td class=\"cent\">".$merc[$mid]["st"]."</td>";
                print "<td class=\"cent\">".$merc[$mid]["ag"]."</td><td class=\"cent\">".$merc[$mid]["av"]."</td><td><small>".implode(', ',$def_skills)."</small></td>\n";
                if ($extra[$i] != false)
                    $extra_list[$i] = str_replace('<option>'.$extra[$i].'</option>', '<option selected>'.$extra[$i].'</option>', $extra_list[$i]);
                echo '        <td><SELECT name="Extra'.$i.'" onChange="this.form.submit()">'."\n";
                print $extra_list[$i];
                echo "        </SELECT></td>\n";
                echo "    </tr>\n";
                $ind_cost+=$cost[$i];
                $i++;
                continue;
            } else {
                $merc_list[$i] = $merc_list[0];
                break;
            }
        }
        echo "    <tr>\n";
        echo '      <td><SELECT name="Merc' . $i . '" onChange="this.form.submit()">' . "\n";
        print $merc_list[0];
        echo "      </SELECT></td>\n";
        echo "    </tr>\n";
?>
</table> <!-- End of Mercenaries Table -->
<table> <!-- Inducements Table -->
<tr><td>
<table>
    <tr>
        <td class="indtitle">Inducement</td>
        <td class="indtitle">#</td>
        <td class="indtitle">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td class="indtitle">Cost</td>
        <td class="indtitle">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td class="indtitle">Total Cost</td>
    </tr>
<?php
        // Regular inducements
        $r=$t->f_rname;
        $rid=$t->f_race_id;
        // Checking if team can hire Igor or Wandering Apo
        unset($inducements[in_array($rid, $racesNoApothecary) ? 'Wandering Apothecaries' : 'Igor']);

        foreach ($inducements as $ind_name => $ind) {
            $this_cost = $ind[in_array($rid, $ind['reduced_cost_races']) ? 'reduced_cost' : 'cost']; # Reduced cost?
            if ($this_cost > 0) {   // Do not display ineligible Inducements
                echo '<tr>';
                print '<td>'.$ind_name.' (0-'.$ind['max'].')</td>';
                echo '<td><SELECT name="'.str_replace(' ','_',$ind_name).'" onChange="this.form.submit()">'; // Changing spaces to underscores for (ugly?) POST workaround
                $ind_list = "<option>0</option>\n";
                for ($i=1;$i<=$ind['max'];$i++) {
                    $ind_list .= '<option>'.$i."</option>\n";
                }
                $pi=0;
                if (isset($_POST[str_replace(' ','_',$ind_name)])) {
                    $pi = $_POST[str_replace(' ','_',$ind_name)];
                    if ($pi != 0)
                        $ind_list=str_replace('<option>'.$pi.'</option>', '<option selected>'.$pi.'</option>', $ind_list);
                }
                print $ind_list;
                echo '</SELECT></td>';
                echo '<td class="cent2">x</td><td class="cent">'.($this_cost/1000).'k</td>';
                echo '<td class="cent2">=</td>';
                $ind_cost+=$pi*$this_cost;
                echo '<td class="cent">'.($pi*$this_cost/1000).'k</td>';
                echo '</tr>';
            }
        }

        // Cards
        $card_list = ''; # Declare.
        echo '<tr>';
        echo '<td>Card budget</td><td class="cent2">&nbsp;</td><td class="cent2">&nbsp;</td>';
        echo '<td><SELECT name="Card" onChange="this.form.submit()">';
        for ($i=0;$i<=1000;$i+=50) {
            $card_list .= '<option>'.$i."k</option>\n";
        }
        $cardb = '';
        if (isset($_POST["Card"])) {
            $cardb = $_POST["Card"];
            if ($cardb != 0) {
                $card_list = str_replace('<option>'.$cardb.'</option>', '<option selected>'.$cardb.'</option>', $card_list);
            }
        }
        if ($cardb != '') {
            $card_cost = intval(str_replace('k','',$cardb));
            $ind_cost += $card_cost * 1000;
        } else {
            $cardb = '0k';
        }
        print $card_list;
        echo '</SELECT></td>';
        echo '<td class="cent2">=</td><td class="cent">'.$cardb.'</td>';
        echo '</tr>';
?>
<tr>
<td class="right" colspan="6"><br><input type="submit" name="Submit" value="Create PDF roster" onclick="return SendToPDF();"></td></tr>
<tr><td><a href="<?php echo urlcompile(T_URL_PROFILE,T_OBJ_TEAM,$team_id,false,false); ?>"> <- Back to team page</a></td></tr>
</table>
</td><td class="cent2">
<table>
<?php
        function kilo($str) {
            if (strpos($str, '000000')) return str_replace('000','',$str) . '000';
            else return str_replace('000','',$str);
        }

        echo '<tr><td class="indtitle">Team Value:</td><td class="indtitle">'.kilo($t->value).'k</td></tr>';
        echo '<tr><td class="indtitle">Inducements Value:</td><td class="indtitle">'.kilo($ind_cost).'k</td></tr>';
        echo '<tr><td class="indtitle">Match Value:</td><td class="indtitle">'.kilo($ind_cost + $t->value).'k</td></tr>';
?>

</table>
</td>
</tr>

</table> <!-- End of Inducements Table -->
</form>
<!--
</div>
</div>
</body>
</html>
-->

<?php
    } // End of Main function
} // End of Class
