<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2009. All Rights Reserved.
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

/*
    THIS FILE is mainly used for HTML-helper routines.
*/

function aasort(&$array, $args) {

    $sort_rule = ""; # Must be initialized in outer scope.
    foreach($args as $arg) {
        $order_field = substr($arg, 1, strlen($arg));
        foreach($array as $array_row) {
            $sort_array[$order_field][] = $array_row[$order_field];
        }
        $sort_rule .= '$sort_array["'.$order_field.'"], '.($arg[0] == "+" ? SORT_ASC : SORT_DESC).',';
    }
    eval ("array_multisort($sort_rule".' $array);');
}

// Sorts array of objects by common object properties. 
// Usage: objsort($obj_array, array('+X', '-Y')) ...to sort objects by X ascending followed by Y descending.
function objsort(&$obj_array, $fields)
{
    $idxs = count($fields)-1;   # Number of fields to sort by.
    $func = 'return ';          # Anonymous function used for sorting the object array.
    $parens = 0;                # Number of parentheses added to end of anonymous function.
    
    for ($i = 0; $i <= $idxs; $i++) {
        $field = substr($fields[$i], 1, strlen($fields[$i]));
        $sort_type = substr($fields[$i], 0, 1);
        $parens += ($i == $idxs ? 1 : 2);
        $func .= "\$a->$field " . ($sort_type == '+' ? '>' : '<') . " \$b->$field 
                    ? 1 
                    : (\$a->$field != \$b->$field 
                        ? -1 
                        : " . ($i == $idxs ? '0' : '(');
    }

    $func .= str_repeat(')', $parens) . ';';
    return usort($obj_array, create_function('$a, $b', $func));
}

// Returns what sort rule is to be used for different stats-table types.
function sort_rule($w) {
    
    $rule = array();
    
    switch ($w)
    {
        case 'streaks': // For streaks table.
            $rule = array('-row_won', '-row_draw', '+row_lost', '+name');
            break;
    
        case 'race_page': // Race's players table.
            $rule = array('+cost', '+position');
            break;
            
        case 'race': // "All races"-table
            $rule = array('-win_percentage', '-teams', '+race');
            break;
            
        case 'match': // Games played tables.
            $rule = array('-date_played');
            break;
    
        case 'coach': // "All coaches"-table
            $rule = array('-win_percentage', '-won_tours', '-cas', '+name');
            break;
            
        case 'team': // Overall team standings.
            $rule = array('-won', '-draw', '+lost', '-score_diff', '-cas', '+name');
            break;
            
        case 'player': // For team roaster player list.
            $rule = array('+nr', '+name');
            break;
            
        case 'player_overall': // "All players"-table
            $rule = array('-value', '-td', '-cas', '-spp', '+name');
            break;
            
        case 'star': // Stars table.
            $rule = array('-cost', '+name');
            break;
            
        case 'star_HH': // Stars hire history table.
            $rule = array('-date_played');
            break;
    }
    
    return $rule;
}


function rule_dict(array $rule) {
    
    /* Translates sort rules. */
    
    $d = array(
        'win_percentage'    => 'win percentage',
        'date_played'       => 'date played',
        'won_tours'         => 'won tours',
        'score_diff'        => 'score diff.',
        'tdcas'             => '{td+cas}',
        'row_won'           => 'won in row',
        'row_lost'          => 'lost in row',
        'row_draw'          => 'draw in row',
    );
    
    foreach ($rule as &$r) {
        $r = preg_replace('/_tour$/', '', $r);
        foreach ($d as $idx => $rpl) {
            $r = preg_replace("/$idx/", $rpl, $r);
        }
    }
    
    return $rule;
}


function pic_box($cur_img, $up_perm = false, $suffix = false) {
    
    ?>
    <img alt="Image" height="250" width="250" src="<?php echo $cur_img?>">
    <br><br>
    <?php
    if ($up_perm) {
        if (is_writable(UPLOAD_DIR)) {
            ?>
            <form method='POST' enctype="multipart/form-data">
                <input type="hidden" name="type" value="pic">
                Upload new image (250x250): <br>
                <input name="pic<?php echo ($suffix) ? $suffix : '' ?>" type="file"><br>
                <input type="submit" name="pic_upload" value="Upload">
            </form>
            <?php
        }
        else {
            echo "<br>Sorry. In order to upload images you must make the OBBLM subdirectory <i>".UPLOAD_DIR."</i> writable to the web server.";
        }
    }
}

function save_pic($fname, $path, $id) {

    if (isset($_FILES[$fname]['tmp_name'])) {
        if (!is_dir($path)) {
            mkdir($path);
        }
        $ext = '';
        switch ($_FILES[$fname]['type'])
        {
            case 'image/gif':  $ext = 'gif'; break;
            case 'image/jpeg': $ext = 'jpeg'; break;
            case 'image/jpg':  $ext = 'jpeg'; break;
            case 'image/png':  $ext = 'png'; break;
        }
        if ($ret = move_uploaded_file($_FILES[$fname]['tmp_name'], "$path/$id.$ext")) {
            foreach (array('gif', 'jpeg', 'jpg', 'png') as $t) {
                if ($t != $ext) {
                    @unlink("$path/$id.$t");
                }
            }
        }
    }
    else {
        return 3;
    }
    
    if (empty($ext)) {
        return 2;
    }
    elseif (!$ret) {
        return 1;
    }
    else {
        return 0; // OK!
    }
}

function get_pic($path, $id) {

    $p = "$path/$id.";
    
    if (file_exists($p.'gif')) return $p.'gif';
    elseif (file_exists($p.'jpeg')) return $p.'jpeg';
    elseif (file_exists($p.'jpg')) return $p.'jpg';
    elseif (file_exists($p.'png')) return $p.'png';
    else return NO_PIC;
}

// Prints an advanced sort table.
function sort_table($title, $lnk, array $objs, array $fields, array $std_sort, $sort = array(), $extra = array()) {

    /*  
        extra fields:
            tableWidth  => CSS style width value
            
            dashed => array(
                'condField' => field name,                    // When an object has this field's (condField) = fieldVal, then a "-" is put in the place of all values.
                'fieldVal'  => field value,
                'noDashFields' => array('field 1', 'field 2') // ...unless the field name is one of those specified in the array 'noDashFields'.
            );
            GETsuffix => suffix to paste into "dir" and "sort" GET strings.
            
            color => true/false. Boolean telling wheter or not we should look into each object for the field "HTMLfcolor" and "HTMLbcolor", and use these color codes to color the obj's row. Note: the object must contain the two previously stated fields, or else black-on-white is used as default.
            
            doNr => true/false. Boolean telling wheter or not to print the "Nr." column.
            limit => int. Stop printing rows when this row number is reached.
            anchor => string. Will create table sorting links, that include this identifier as an anchor.
            noHelp => true/false. Will enable/disable help link [?].
    */
    global $settings;
    
    $MASTER_SORT = array_merge($sort, $std_sort);
    objsort($objs, $MASTER_SORT);
    $no_print_fields = array();
    $DONR = (!array_key_exists('doNr', $extra) || $extra['doNr']) ? true : false;
    $LIMIT = (array_key_exists('limit', $extra)) ? $extra['limit'] : -1;
    $ANCHOR = (array_key_exists('anchor', $extra)) ? $extra['anchor'] : false;
    
    if ($DONR) {
        $fields = array_merge(array('nr' => array('desc' => 'Nr.')), $fields);
        array_push($no_print_fields, 'nr');
    }

    $CP = count($fields);
    
    ?>
    <table class="sort" <?php echo (array_key_exists('tableWidth', $extra)) ? "style='width: $extra[tableWidth];'" : '';?>>
        <tr>
            <td class="light" colspan="<?php echo $CP;?>"><b>
            <?php echo $title;?>&nbsp;
            <?php
            if (!array_key_exists('noHelp', $extra) || !$extra['noHelp']) {
                ?><a href="javascript:void(0);" onclick="window.open('html/table_desc.html','tableColumnDescriptions','width=600,height=400')">[?]</a><?php
            }
            ?>
            </b></td>
        </tr>
        <tr>
            <?php
            foreach ($fields as $f => $attr) 
                echo "<td><i>$attr[desc]</i></td>";
            ?>
        </tr>
        <tr>
        <?php
        foreach ($fields as $f => $attr) {
            if (in_array($f, $no_print_fields) || (array_key_exists('nosort', $attr) && $attr['nosort'])) {
                echo "<td></td>";
                continue;
            }
            if (array_key_exists('GETsuffix', $extra)) {
                $sort = 'sort'.$extra['GETsuffix'];
                $dir = 'dir'.$extra['GETsuffix'];
            }
            else {
                $sort = 'sort';
                $dir = 'dir';         
            }
            $anc = '';
            if ($ANCHOR) {
                $anc = "#$ANCHOR";
            }
                
            echo "<td><b><a href='$lnk&amp;$sort=$f&amp;$dir=a$anc' title='Sort ascending'>+</a>/<a href='$lnk&amp;$sort=$f&amp;$dir=d$anc' title='Sort descending'>-</a></b></td>";
        }
        ?>
        </tr>
        <tr><td colspan="<?php echo $CP;?>"><hr></td></tr>
        <?php
        $i = 1;
        foreach ($objs as $o) {
            $DASH = (array_key_exists('dashed', $extra) && $o->{$extra['dashed']['condField']} == $extra['dashed']['fieldVal']) ? true : false;
            if (array_key_exists('color', $extra)) {
                $td = "<td style='background-color: ".(isset($o->HTMLbcolor) ? $o->HTMLbcolor : 'white')."; color: ".(isset($o->HTMLfcolor) ? $o->HTMLfcolor : 'black').";'>";
            }
            else {
                $td = '<td>';
            }
            echo "<tr>\n";
            if ($DONR) {
                echo $td.$i."</td>\n";
            }
            foreach ($fields as $f => $a) { // Field => attributes
                if (!in_array($f, $no_print_fields)) {
                    if ($DASH && !in_array($f, $extra['dashed']['noDashFields'])) {
                        echo $td."-</td>\n";
                        continue;
                    }
                    $cpy = $o->$f; // Don't change the objects themselves! Make copies!
                    if (array_key_exists('kilo', $a) && $a['kilo'])
                        $cpy /= 1000;
                    if (is_float($cpy))
                        $cpy = sprintf("%1.2f", $cpy);
                    if (array_key_exists('suffix', $a) && $a['suffix'])
                        $cpy .= $a['suffix'];
                    if (array_key_exists('color', $a) && $a['color']) 
                        $cpy = "<font color='$a[color]'>".$cpy."</font>\n";
                    if (array_key_exists('href', $a) && $a['href']) 
                        $cpy  = "<a href='" . $a['href']['link'] . "&amp;" . $a['href']['field'] . "=" . $o->{$a['href']['value']} . "'>". $cpy . "</a>";

                    if (isset($o->{"${f}_color"})) {
                        echo "<td style='background-color: ".$o->{"${f}_color"}."; color: black;'>".$cpy."</td>\n";
                    }
                    else {
                        echo $td.$cpy."</td>\n";
                    }
                }
            }
            echo "</tr>\n";
            if ($i++ == $LIMIT) {
                break;
            }
        }
        if ($settings['show_sort_rule']) {
        ?>
        <tr>
            <td colspan="<?php echo $CP;?>">
            <hr>
            </td>
        </tr>
        <tr>
            <td align="right" colspan="<?php echo $CP;?>">
            <i>Sorted against: <?php echo implode(', ', rule_dict($MASTER_SORT));?></i>
            </td>
        </tr>
        <?php
        }
    echo "</table>\n";
}

// Log changes to team:
function logTeamAction($str, $tid) {
    @SiteLog::create("$_SESSION[coach] changed ".get_alt_col('teams', 'team_id', $tid, 'name').': '.$str, $_SESSION['coach_id']);
}

// Prints page title for main section pages.
function title($title) {
    echo "<h2>$title</h2>\n";
}

// Privileges error. Stop PHP interpreter and warn the user!
function fatal($err_msg) {
    die("<br><br><center><big><font color='red'><b>$err_msg</b></font></big></center><br>");
}

// Print a status message.
function status($status, $msg = '') {

        if ($status) { # Status == success
            echo "<div class=\"messageContainer green\">";
                echo "Request succeeded";
                if ($msg != ''){
                    echo " : $msg\n";
                }
            echo "</div>";
        } else { # Status == failure
                echo "<div class=\"messageContainer red\">";
                    echo "Request failed";
                if ($msg != ''){
                    echo " : $msg\n";
                }
            echo "</div>";
        }
        ?>
    <?php
}

function textdate($mysqldate, $noTime = false) {
    return date("D M j Y".(($noTime) ? '' : ' G:i:s'), strtotime($mysqldate));
}

// Returns HTML to show an icon with the result of a game
function matchresult_icon($result) {

    global $lng;

    $class = "";

    switch ($result){
        case "W":
            $class = "won";
            $title = $lng->getTrn('global/misc/gamewon');
            break;
        case "L":
            $class = "lost";
            $title = $lng->getTrn('global/misc/gamelost');
            break;
        case "D":
            $class = "draw";
            $title = $lng->getTrn('global/misc/gamedraw');
            break;
        default:
            $class = "";
            $title = $lng->getTrn('global/misc/gameunknown');
    }
    return "<div class='match_icon ". $class ."' title='". $title ."'></div>";
}

function frame_begin($stylesheet = false) {
    global $settings;
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
        <title><?php echo $settings['site_name']; ?> Blood Bowl League</title>
        <link type="text/css" href="css/stylesheet<?php echo ($stylesheet) ? $stylesheet : $settings['stylesheet']; ?>.css" rel="stylesheet">
        <link rel="alternate" type="application/rss+xml" title="RSS Feed"href="rss.xml" />
        <script type="text/javascript" src="lib/misc_functions.js"></script>
        
        <!-- CSS MENU (./cssmenu extension) -->
        <link href="cssmenu/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
        <link href="cssmenu/css/dropdown/themes/default/default.ultimate.css" media="all" rel="stylesheet" type="text/css" />
        <!--[if lt IE 7]>
        <script type="text/javascript" src="cssmenu/js/jquery/jquery.js"></script>
        <script type="text/javascript" src="cssmenu/js/jquery/jquery.dropdown.js"></script>
        <![endif]-->
    </head>
    <body>
        <div class="everything">
            <div class="banner"></div>
            <div class="menu">
                <?php make_menu(); ?>
            </div> <!-- Menu div end -->
            <div class="section"> <!-- This container holds the section specific content -->
    <?php
}

function frame_end() {
    ?>
                <!-- Pseudo container to force parent container to have the correct height for (potential) floating children -->
                <div style="clear: both;"></div> 
            </div> <!-- End of section div -->
        </div> <!-- End of everything div -->
    </body>
    </html>
    <?php
    return true;
}

function make_menu() {

    global $lng, $coach, $settings, $rules;
    
    ?>
    <ul id="nav" class="dropdown dropdown-horizontal">
        <?php 
        if (isset($_SESSION['logged_in'])) { ?><li><a href="index.php?logout=1">     <?php echo $lng->getTrn('global/secLinks/logout');?></a></li><?php }
        else                               { ?><li><a href="index.php?section=login"><?php echo $lng->getTrn('global/secLinks/login');?></a></li><?php }
        if (isset($_SESSION['logged_in'])) { 
            ?>
            <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/cc');?></span>
                <ul>
                    <li><a href='index.php?section=coachcorner'><?php echo $lng->getTrn('global/secLinks/coachcorner');?></a></li>
                    <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/myteams');?></span>
                        <ul>
                            <?php
                            foreach ($coach->getTeams() as $t) {
                                echo "<li><a href='index.php?section=coachcorner&amp;team_id=$t->team_id'>$t->name</a></li>\n";
                            }
                            echo "<li><a style='font-style: italic;' href='index.php?section=coachcorner&amp;team_id=new'>".$lng->getTrn('secs/cc/main/start_new')."</a></li>\n";
                            ?>
                        </ul>
                    </li>
                    <li><a href='index.php?section=coaches&amp;coach_id=<?php echo $coach->coach_id;?>'><?php echo $lng->getTrn('global/secLinks/myprofile');?></a></li>
                </ul>
            </li>
            <?php
        }
        if (isset($_SESSION['logged_in'])) {
            $ring_sys_access = array('usrman' => $lng->getTrn('secs/admin/um'), 'ldm' => $lng->getTrn('secs/admin/ldm'), 'chtr' => $lng->getTrn('secs/admin/th'), 'import' => $lng->getTrn('secs/admin/import'), 'ctman' => $lng->getTrn('secs/admin/delete'));
            $ring_com_access = array('tournament' => $lng->getTrn('secs/admin/schedule'), 'log' => $lng->getTrn('secs/admin/log'));
            if (is_object($coach) && $coach->ring <= RING_COM) {
                ?>
                <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/admin');?></span>
                    <ul>
                        <?php
                        foreach ($ring_com_access as $lnk => $desc) {
                            echo "<li><a href='index.php?section=admin&amp;subsec=$lnk'>$desc</a></li>\n";
                        }
                        if ($coach->ring == RING_SYS) {
                            foreach ($ring_sys_access as $lnk => $desc) {
                                echo "<li><a style='font-style: italic;' href='index.php?section=admin&amp;subsec=$lnk'>$desc</a></li>\n";
                            }            
                        }
                        ?>
                    </ul>
                </li>
                <?php
            }
        }
        ?>
        <li><a href="index.php?section=main"><?php echo $lng->getTrn('global/secLinks/home');?></a></li>
        <li><a href="index.php?section=teams"><?php echo $lng->getTrn('global/secLinks/teams');?></a></li>
        <li><a href="index.php?section=fixturelist"><?php echo $lng->getTrn('global/secLinks/fixtures');?></a></li>
        <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/statistics');?></span>
            <ul>
                <li><a href="index.php?section=standings"><?php echo $lng->getTrn('global/secLinks/standings');?></a></li>
                <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/specstandings');?></span>
                    <ul>
                        <?php
                        foreach (Tour::getTours() as $t) {
                            echo "<li><a href='index.php?section=fixturelist&amp;tour_id=$t->tour_id'>$t->name</a></li>\n";
                        }
                        ?>
                    </ul>
                </li>
                <li><a href="index.php?section=recent"><?php echo $lng->getTrn('global/secLinks/recent');?></a></li>
                <li><a href="index.php?section=players"><?php echo $lng->getTrn('global/secLinks/players');?></a></li>
                <li><a href="index.php?section=coaches"><?php echo $lng->getTrn('global/secLinks/coaches');?></a></li>
                <li><a href="index.php?section=races"><?php echo $lng->getTrn('global/secLinks/races');?></a></li>
                <?php
                if ($rules['enable_stars_mercs']) {
                    ?><li><a href="index.php?section=stars"><?php echo $lng->getTrn('global/secLinks/stars');?></a></li><?php
                }
                ?>
                <li><a href="handler.php?type=graph&amp;gtype=<?php echo SG_T_LEAGUE;?>&amp;id=none"><?php echo $lng->getTrn('secs/records/d_gstats');?></a></li>
            </ul>
        </li>
        <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/records');?></span>
            <ul>
                <li><a href="index.php?section=records&amp;subsec=hof"><?php echo $lng->getTrn('secs/records/d_hof');?></a></li>
                <li><a href="index.php?section=records&amp;subsec=wanted"><?php echo $lng->getTrn('secs/records/d_wanted');?></a></li>
                <li><a href="index.php?section=records&amp;subsec=memm"><?php echo $lng->getTrn('secs/records/d_memma');?></a></li>
                <li><a href="index.php?section=records&amp;subsec=prize"><?php echo $lng->getTrn('secs/records/d_prizes');?></a></li>
            </ul>
        </li>
        
        <li><a href="index.php?section=rules"><?php echo $lng->getTrn('global/secLinks/rules');?></a></li>
        <li><a href="index.php?section=gallery"><?php echo $lng->getTrn('global/secLinks/gallery');?></a></li>
        <li><a href="index.php?section=about">OBBLM</a></li>
        <?php 
        if ($settings['enable_guest_book']) {
            ?><li><a href="index.php?section=guest"><?php echo $lng->getTrn('global/secLinks/gb');?></a></li><?php
        }
        if (!empty($settings['forum_url'])) {
            ?><li><a href="<?php echo $settings['forum_url'];?>"><?php echo $lng->getTrn('global/secLinks/forum');?></a></li><?php
        }
        ?>
    </ul>
    <?php
}

function make_standings($grp, $node, $node_id, $opts) {

    /*
        Makes various kinds of standings tables.
    
        $grp and $node types are STATS_* types.
    */

    // Options.
    list($url, $GET_SS) = $opts;
    $extra = array();
    
    if (!$GET_SS) {$GET_SS = '';}
    else {$extra['GETsuffix'] = $GET_SS;}
    
    // Objects to sort.
    $objs = array();
    // Common $grp type fields we want to print.
    $fields = array(
        'won'               => array('desc' => 'W'), 
        'lost'              => array('desc' => 'L'), 
        'draw'              => array('desc' => 'D'), 
        'played'            => array('desc' => 'GP'), 
        'win_percentage'    => array('desc' => 'WIN%'), 
        'row_won'           => array('desc' => 'SW'), 
        'row_lost'          => array('desc' => 'SL'), 
        'row_draw'          => array('desc' => 'SD'), 
        'score_team'        => array('desc' => 'GF'),
        'score_opponent'    => array('desc' => 'GA'),
        'won_tours'         => array('desc' => 'WT'), 
        'td'                => array('desc' => 'Td'), 
        'cp'                => array('desc' => 'Cp'), 
        'intcpt'            => array('desc' => 'Int'), 
        'cas'               => array('desc' => 'Cas'), 
        'bh'                => array('desc' => 'BH'), 
        'si'                => array('desc' => 'Si'), 
        'ki'                => array('desc' => 'Ki'), 
    );
    
    switch ($grp)
    {
        case STATS_PLAYER:
            
            break;
            
        case STATS_TEAM:
        
            break;
            
        case STATS_RACE:
            array_merge(array(
                'race'  => array('desc' => 'Race', 'href' => array('link' => 'index.php?section=races', 'field' => 'race', 'value' => 'race_id')), 
                'teams' => array('desc' => 'Teams'),
            ), $fields);
            $extra['dashed'] = array('condField' => 'teams', 'fieldVal' => 0, 'noDashFields' => array('race'));
            
            $objs = Race::getRaces(true);
            foreach ($objs as $o) {
                $o->setStats(true);
            }
            
            break;
            
        case STATS_COACH:
            array_merge(array(
                'name'      => array('desc' => 'Coach', 'href' => array('link' => '???????????', 'field' => 'coach_id', 'value' => 'coach_id')),
                'teams_cnt' => array('desc' => 'Teams'), 
            ), $fields);        
            foreach ($c->teams as $t) {
                $t->setExtraStats();
                $t->setStreaks(false);
            }
            break;
    }


    sort_table(
        'TITLE !!!!!!!!!!!', 
        'index.php?section=races', 
        $races, 
        $fields, 
        sort_rule('race'), 
        (isset($_GET["sort$GET_SS"])) ? array((($_GET["dir$GET_SS"] == 'a') ? '+' : '-') . $_GET["sort$GET_SS"]) : array(),
        $extra
    );
}

function make_recentgames($grp, $id, $node, $node_id) {

}

?>
