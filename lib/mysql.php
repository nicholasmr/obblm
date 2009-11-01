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

/* THIS FILE is used for MySQL-helper routines */


// These are the OBBLM core tables.

# Commonly used col. defs.
$CT_cols = array(
    T_OBJ_PLAYER => 'MEDIUMINT SIGNED', # Negative IDs are stars.
    T_OBJ_TEAM   => 'MEDIUMINT UNSIGNED',
    T_OBJ_COACH  => 'MEDIUMINT UNSIGNED',
    T_OBJ_RACE   => 'TINYINT UNSIGNED',
    T_OBJ_STAR   => 'SMALLINT SIGNED', # All star IDs are negative.
    'pos_id'     => 'SMALLINT UNSIGNED', # Position ID/"name ID" of player on race roster.
    T_NODE_MATCH      => 'MEDIUMINT SIGNED',
    T_NODE_TOURNAMENT => 'MEDIUMINT UNSIGNED',
    T_NODE_DIVISION   => 'MEDIUMINT UNSIGNED',
    T_NODE_LEAGUE     => 'MEDIUMINT UNSIGNED',
    
    'tv' => 'MEDIUMINT UNSIGNED', # Team value
    'pv' => 'MEDIUMINT UNSIGNED', # Player value
    'chr' => 'TINYINT UNSIGNED', # ma, st, ag, av (inj, def and ach)
    'elo' => 'FLOAT',
    'team_cnt' => 'TINYINT UNSIGNED', # Teams count for races and coaches.
    'streak' => 'SMALLINT UNSIGNED',
    'skills' => 'VARCHAR('.(19+20*3).')', # Set limit to 20 skills, ie. chars = 19 commas + 20*3 (max 20 integers of 3 decimals (assumed upper limit)).
);

$core_tables = array(
    'coaches' => array(
        'coach_id'  => $CT_cols[T_OBJ_COACH].' NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'name'      => 'VARCHAR(50)',
        'realname'  => 'VARCHAR(50)',
        'passwd'    => 'VARCHAR(32)',
        'mail'      => 'VARCHAR(129)',
        'phone'     => 'VARCHAR(25) NOT NULL',
        'ring'      => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
        'settings'  => 'VARCHAR(320) NOT NULL',
        'retired'   => 'BOOLEAN NOT NULL DEFAULT 0',
        'com_lid'   => $CT_cols[T_NODE_LEAGUE].' NOT NULL DEFAULT 0',
        // Dynamic properties (DPROPS)
        'elo'   => $CT_cols['elo'].' DEFAULT NULL', # All-time ELO (across all matches).
        'swon'  => $CT_cols['streak'],
        'sdraw' => $CT_cols['streak'],
        'slost' => $CT_cols['streak'],
        'team_cnt' => $CT_cols['team_cnt'],
    ),
    'teams' => array(
        'team_id'           => $CT_cols[T_OBJ_TEAM].' NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'name'              => 'VARCHAR(50)',
        'owned_by_coach_id' => $CT_cols[T_OBJ_COACH],
        'f_race_id'         => $CT_cols[T_OBJ_RACE].' NOT NULL DEFAULT 0',
        'f_lid'             => $CT_cols[T_NODE_LEAGUE].' NOT NULL DEFAULT 0',
        'treasury'          => 'BIGINT SIGNED',
        'apothecary'        => 'BOOLEAN',
        'rerolls'           => 'MEDIUMINT UNSIGNED',
        'fan_factor'        => 'MEDIUMINT UNSIGNED',
        'ass_coaches'       => 'MEDIUMINT UNSIGNED',
        'cheerleaders'      => 'MEDIUMINT UNSIGNED',
        'rdy'               => 'BOOLEAN NOT NULL DEFAULT 1',
        'imported'          => 'BOOLEAN NOT NULL DEFAULT 0',
        'retired'           => 'BOOLEAN NOT NULL DEFAULT 0',
        'won_0'     => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
        'lost_0'    => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
        'draw_0'    => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
        'sw_0'      => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
        'sl_0'      => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
        'sd_0'      => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
        'wt_0'      => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
        'gf_0'      => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
        'ga_0'      => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
        'tcas_0'    => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
        'elo_0'     => $CT_cols['elo'].' NOT NULL DEFAULT 0',
        // Dynamic properties (DPROPS)
        'tv'    => $CT_cols['tv'],
        'elo'   => $CT_cols['elo'].' DEFAULT NULL', # All-time ELO (across all matches).
        'swon'  => $CT_cols['streak'],
        'sdraw' => $CT_cols['streak'],
        'slost' => $CT_cols['streak'],
    ),
    'players' => array(
        'player_id'         => $CT_cols[T_OBJ_PLAYER].' NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'type'              => 'TINYINT UNSIGNED DEFAULT 1',
        'name'              => 'VARCHAR(50)',
        'owned_by_team_id'  => $CT_cols[T_OBJ_TEAM],
        'nr'                => 'MEDIUMINT UNSIGNED',
        'f_pos_id'          => $CT_cols['pos_id'],
        'position'          => 'VARCHAR(30)',# DEPRECATED!!!
        'date_bought'       => 'DATETIME',
        'date_sold'         => 'DATETIME',
        'ach_ma'            => $CT_cols['chr'],
        'ach_st'            => $CT_cols['chr'],
        'ach_ag'            => $CT_cols['chr'],
        'ach_av'            => $CT_cols['chr'],
        'ach_nor_skills'    => $CT_cols['skills'],
        'ach_dob_skills'    => $CT_cols['skills'],
        'extra_skills'      => $CT_cols['skills'],
        'extra_spp'         => 'MEDIUMINT SIGNED',
        'extra_val'         => $CT_cols['pv'].' NOT NULL DEFAULT 0',
        // Dynamic properties (DPROPS)
        'value'             => $CT_cols['pv'],
        'status'            => 'TINYINT UNSIGNED',
        'date_died'         => 'DATETIME',
        'ma'                => $CT_cols['chr'],
        'st'                => $CT_cols['chr'],
        'ag'                => $CT_cols['chr'],
        'av'                => $CT_cols['chr'],
        'inj_ma'            => $CT_cols['chr'],
        'inj_st'            => $CT_cols['chr'],
        'inj_ag'            => $CT_cols['chr'],
        'inj_av'            => $CT_cols['chr'],
        'inj_ni'            => $CT_cols['chr'],
    ),
    'leagues' => array(
        'lid'       => $CT_cols[T_NODE_LEAGUE].' NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'name'      => 'VARCHAR(50)',
        'location'  => 'VARCHAR(50)',
        'date'      => 'DATETIME',

    ),
    'divisions' => array(
        'did'   => $CT_cols[T_NODE_DIVISION].' NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'f_lid' => $CT_cols[T_NODE_LEAGUE],
        'name'  => 'VARCHAR(50)',

    ),
    'tours' => array(
        'tour_id'       => $CT_cols[T_NODE_TOURNAMENT].' NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'f_did'         => $CT_cols[T_NODE_DIVISION],
        'name'          => 'VARCHAR(50)',
        'type'          => 'TINYINT UNSIGNED',
        'date_created'  => 'DATETIME',
        'rs'            => 'TINYINT UNSIGNED DEFAULT 1',
        'locked'        => 'BOOLEAN',
    ),
    'matches' => array(
        'match_id'      => $CT_cols[T_NODE_MATCH].' NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'round'         => 'TINYINT UNSIGNED',
        'f_tour_id'     => $CT_cols[T_NODE_TOURNAMENT],
        'locked'        => 'BOOLEAN',
        'submitter_id'  => $CT_cols[T_OBJ_COACH],
        'stadium'       => $CT_cols[T_OBJ_TEAM],
        'gate'          => 'MEDIUMINT UNSIGNED',
        'fans'          => 'MEDIUMINT UNSIGNED NOT NULL DEFAULT 0',
        'ffactor1'      => 'TINYINT SIGNED',
        'ffactor2'      => 'TINYINT SIGNED',
        'income1'       => 'MEDIUMINT SIGNED',
        'income2'       => 'MEDIUMINT SIGNED',
        'team1_id'      => $CT_cols[T_OBJ_TEAM],
        'team2_id'      => $CT_cols[T_OBJ_TEAM],
        'date_created'  => 'DATETIME',
        'date_played'   => 'DATETIME',
        'date_modified' => 'DATETIME',
        'team1_score'   => 'TINYINT UNSIGNED',
        'team2_score'   => 'TINYINT UNSIGNED',
        'smp1'          => 'TINYINT SIGNED NOT NULL DEFAULT 0',
        'smp2'          => 'TINYINT SIGNED NOT NULL DEFAULT 0',
        'tcas1'         => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
        'tcas2'         => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
        'fame1'         => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
        'fame2'         => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
        'tv1'           => $CT_cols['tv'].' NOT NULL DEFAULT 0',
        'tv2'           => $CT_cols['tv'].' NOT NULL DEFAULT 0',
    ),
    'match_data' => array(
        'f_coach_id'    => $CT_cols[T_OBJ_COACH],
        'f_team_id'     => $CT_cols[T_OBJ_TEAM],
        'f_player_id'   => $CT_cols[T_OBJ_PLAYER],
        'f_race_id'     => $CT_cols[T_OBJ_RACE],
        'f_match_id'    => $CT_cols[T_NODE_MATCH],
        'f_tour_id'     => $CT_cols[T_NODE_TOURNAMENT],
        'f_did'         => $CT_cols[T_NODE_DIVISION],
        'f_lid'         => $CT_cols[T_NODE_LEAGUE],
        'mvp'           => 'TINYINT UNSIGNED',
        'cp'            => 'TINYINT UNSIGNED',
        'td'            => 'TINYINT UNSIGNED',
        'intcpt'        => 'TINYINT UNSIGNED',
        'bh'            => 'TINYINT UNSIGNED',
        'si'            => 'TINYINT UNSIGNED',
        'ki'            => 'TINYINT UNSIGNED',
        'inj'           => 'TINYINT UNSIGNED',
        'agn1'          => 'TINYINT UNSIGNED',
        'agn2'          => 'TINYINT UNSIGNED',
        'mg'            => 'BOOLEAN NOT NULL DEFAULT FALSE',
    ),
    'texts' => array(
        'txt_id'    => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'type'      => 'TINYINT UNSIGNED',
        'f_id'      => 'MEDIUMINT UNSIGNED',
        'date'      => 'DATETIME',
        'txt2'      => 'TEXT',
        'txt'       => 'TEXT',
    ),
    'game_data_players' => array(
        'pos_id'    => $CT_cols['pos_id'],
        'f_race_id' => $CT_cols[T_OBJ_RACE],
        'pos'    => 'VARCHAR(60)',
        'cost'   => 'MEDIUMINT UNSIGNED',
        'qty'    => 'TINYINT UNSIGNED',
        'ma'     => $CT_cols['chr'],
        'st'     => $CT_cols['chr'],
        'ag'     => $CT_cols['chr'],
        'av'     => $CT_cols['chr'],
        'skills' => $CT_cols['skills'],
        'norm'   => 'VARCHAR(6)', # Max used is 4 chars, but set to 6 to be sure.
        'doub'   => 'VARCHAR(6)', # Max used is 4 chars, but set to 6 to be sure.
    ),
    'game_data_stars' => array(
        'star_id'=> $CT_cols[T_OBJ_STAR],
        'name'   => 'VARCHAR(60)',
        'cost'   => 'MEDIUMINT UNSIGNED',
        'races'  => 'VARCHAR('.(29+30*2).')', # Race IDs that may hire star. Total of (less than) 30 races of each two digit race ID + 29 commas = 29+30*2
        'ma'     => $CT_cols['chr'],
        'st'     => $CT_cols['chr'],
        'ag'     => $CT_cols['chr'],
        'av'     => $CT_cols['chr'],
        'skills' => $CT_cols['skills'],
    ),
    'game_data_races' => array(
        'race_id' => $CT_cols[T_OBJ_RACE],
        'name'    => 'VARCHAR(50)',
        'cost_rr' => 'MEDIUMINT UNSIGNED',
        // Dynamic properties (DPROPS)
        'team_cnt' => $CT_cols['team_cnt'],
    ),
);

/*
    MV tables
*/

// Common:
$mv_commoncols = array(
    # Node references
    'f_trid' => $CT_cols[T_NODE_TOURNAMENT],
    'f_did'  => $CT_cols[T_NODE_DIVISION],
    'f_lid'  => $CT_cols[T_NODE_LEAGUE],
    # Stats
    'mvp'       => 'SMALLINT UNSIGNED',
    'cp'        => 'SMALLINT UNSIGNED',
    'td'        => 'SMALLINT UNSIGNED',
    'intcpt'    => 'SMALLINT UNSIGNED',
    'bh'        => 'SMALLINT UNSIGNED',
    'ki'        => 'SMALLINT UNSIGNED',
    'si'        => 'SMALLINT UNSIGNED',
    'cas'       => 'SMALLINT UNSIGNED',
    'tdcas'     => 'SMALLINT UNSIGNED',
    'spp'       => 'SMALLINT UNSIGNED',
    'won'       => 'SMALLINT UNSIGNED',
    'lost'      => 'SMALLINT UNSIGNED',
    'draw'      => 'SMALLINT UNSIGNED',
    'played'    => 'SMALLINT UNSIGNED',
    'win_pct'   => 'FLOAT UNSIGNED',
    'ga'        => 'SMALLINT UNSIGNED',
    'gf'        => 'SMALLINT UNSIGNED',
);
$core_tables['mv_players'] = array(
    'f_pid' => $CT_cols[T_OBJ_PLAYER],
    'f_tid' => $CT_cols[T_OBJ_TEAM],
    'f_cid' => $CT_cols[T_OBJ_COACH],
    'f_rid' => $CT_cols[T_OBJ_RACE],
);
$core_tables['mv_teams'] = array(
    'f_tid' => $CT_cols[T_OBJ_TEAM],
    'f_cid' => $CT_cols[T_OBJ_COACH],
    'f_rid' => $CT_cols[T_OBJ_RACE],

    'elo'   => $CT_cols['elo'],
    'swon'  => $CT_cols['streak'],
    'sdraw' => $CT_cols['streak'],
    'slost' => $CT_cols['streak'],
);
$core_tables['mv_coaches'] = array(
    'f_cid' => $CT_cols[T_OBJ_COACH],

    'elo'   => $CT_cols['elo'],
    'swon'  => $CT_cols['streak'],
    'sdraw' => $CT_cols['streak'],
    'slost' => $CT_cols['streak'],
    'team_cnt' => $CT_cols['team_cnt'],
);
$core_tables['mv_races'] = array(
    'f_rid' => $CT_cols[T_OBJ_RACE],
    
    'team_cnt' => $CT_cols['team_cnt'],
);
foreach (array('players', 'teams', 'coaches', 'races') as $mv_tbl) {
    $idx = "mv_$mv_tbl";
    $core_tables[$idx] = array_merge($core_tables[$idx], $mv_commoncols);
}

function mysql_up($do_table_check = false) {

    // Brings up MySQL for use in PHP execution.

    global $db_host, $db_user, $db_passwd, $db_name; // From settings.php
    
    $conn = mysql_connect($db_host, $db_user, $db_passwd);
    
    if (!$conn)
        die("<font color='red'><b>Could not connect to the MySQL server. 
            <ul>
                <li>Is the MySQL server running?</li>
                <li>Are the settings in settings.php correct?</li>
                <li>Is PHP set up correctly?</li>
            </ul></b></font>");

    if (!mysql_select_db($db_name))
        die("<font color='red'><b>Could not select the database '$db_name'. 
            <ul>
                <li>Does the database exist?</li>
                <li>Does the specified user '$db_user' have the correct privileges?</li>
            </ul>
            Try running the install script again.</b></font>");

    // Test if all tables exist.
    if ($do_table_check) {
        global $core_tables;
        $tables_expected = array_keys($core_tables);
        $tables_found = array();
        $query = "SHOW TABLES";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
            array_push($tables_found, $row[0]);
        }
        $tables_diff = array_diff($tables_expected, $tables_found);
        if (count($tables_diff) > 0) {
            die("<font color='red'><b>Could not find all the expected tables in database. Try running the install script again.<br><br>
                <i>Tables missing:</i><br> ". implode(', ', $tables_diff) ."
                </b></font>");  
        }
    }

    return $conn;
}

function get_alt_col($V, $X, $Y, $Z) {

    /*
     *  Get Alternative Column
     *
     *  $V = table
     *  $X = look-up column
     *  $Y = look-up value
     *  $Z = column to return value from.
     */

    $result = mysql_query("SELECT * FROM $V WHERE $X = '" . mysql_real_escape_string($Y) . "'");

    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        return $row[$Z];
    }

    return null;
}


function get_rows($tbl, array $getFields) {
    /* 
        Useful for when wanting to quickly make objects with basic fields.
        
        Ex: Get all teams' name and ID:
            get_rows('teams', array('team_id', 'name'));
        ...will return an (unsorted) array of objects with the attributes 'team_id' and 'name', found in the teams table.
    */
    $query = 'SELECT '.implode(',', $getFields)." FROM $tbl";
    $result = mysql_query($query);
    $ret = array();
    while ($row = mysql_fetch_object($result)) {
        $ret[] = $row;
    }
    return $ret;
}

$relations_node = array(
    T_NODE_MATCH        => array('id' => 'match_id', 'parent_id' => 'f_tour_id', 'tbl' => 'matches'),
    T_NODE_TOURNAMENT   => array('id' => 'tour_id',  'parent_id' => 'f_did',     'tbl' => 'tours'),
    T_NODE_DIVISION     => array('id' => 'did',      'parent_id' => 'f_lid',     'tbl' => 'divisions'),
    T_NODE_LEAGUE       => array('id' => 'lid',      'parent_id' => null,        'tbl' => 'leagues'),
);
$relations_obj = array(
    T_OBJ_PLAYER => array('id' => 'player_id', 'parent_id' => 'owned_by_team_id',   'tbl' => 'players'),
    T_OBJ_TEAM   => array('id' => 'team_id',   'parent_id' => 'owned_by_coach_id',  'tbl' => 'teams'),
    T_OBJ_COACH  => array('id' => 'coach_id',  'parent_id' => null,                 'tbl' => 'coaches'),
);
function get_parent_id($type, $id, $parent_type) {
    global $relations_node, $relations_obj;
    $relations = in_array($type, array_keys($relations_node)) ? $relations_node : $relations_obj;
    if ($type >= $parent_type)
        return null;
    # Don't include tables below $node OR above $parent_node OR parent_node table itself!
    list($zeroEntry) = array_keys($relations);
    $REL_trimmed = array_slice($relations, $type-$zeroEntry, $parent_type-$type);
    $REL_trimmed_padded = $REL_trimmed; 
    $tables = array_map(create_function('$rl', 'return $rl["tbl"];'), $REL_trimmed);
    array_pop($REL_trimmed);
    array_shift($REL_trimmed_padded);
    $wheres = array_map(create_function('$rl,$rl_next', 'return "$rl[tbl].$rl[parent_id] = $rl_next[tbl].$rl_next[id]";'), $REL_trimmed, $REL_trimmed_padded);
    $query = 'SELECT '.$relations[$parent_type-1]['parent_id'].' AS "parent_id" FROM '.implode(',', $tables).' WHERE '.$relations[$type]['id']."=$id".((!empty($wheres)) ? ' AND '.implode(' AND ', $wheres) : '');
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
    return $row['parent_id'];
}

function get_parent_name($type, $id, $parent_type) {
    global $relations_node, $relations_obj;
    $relations = in_array($type, array_keys($relations_node)) ? $relations_node : $relations_obj;
    return get_alt_col($relations[$parent_type]['tbl'], $relations[$parent_type]['id'], get_parent_id($type,$id,$parent_type), 'name');
}

function get_list($table, $col, $val, $new_col) {
    $result = mysql_query("SELECT $new_col FROM $table WHERE $col = '$val'");
    if (mysql_num_rows($result) <= 0)
        return array();
    
    $row = mysql_fetch_assoc($result);
    return (empty($row[$new_col])) ? array() : explode(',', $row[$new_col]);
}

function set_list($table, $col, $val, $new_col, $new_val = array()) {
    $new_val = implode(',', $new_val);
    if (mysql_query("UPDATE $table SET $new_col = '$new_val' WHERE $col = '$val'")) 
        return true;
    else
        return false;
}

function setup_database() {

    global $core_tables;
    $conn = mysql_up();
    require_once('lib/class_sqlcore.php');

    // Create core tables.
    echo "<b>Creating core tables...</b><br>\n";
    foreach ($core_tables as $tblName => $def) {    
        echo (Table::createTable($tblName, $def))
            ? "<font color='green'>OK &mdash; $tblName</font><br>\n"
            : "<font color='red'>FAILED &mdash; $tblName</font><br>\n";
    }
    
    // Create tables used by modules.
    echo "<b>Creating module tables...</b><br>\n";
    foreach (Module::createAllRequiredTables() as $module => $tables) {
        foreach ($tables as $name => $tblStat) {
            echo ($tblStat)
                ? "<font color='green'>OK &mdash; $name</font><br>\n"
                : "<font color='red'>FAILED &mdash; $name</font><br>\n";
        }
    }

    echo "<b>Other tasks...</b><br>\n";
    
    echo (SQLCore::syncGameData()) 
        ? "<font color='green'>OK &mdash; Synchronize game data with database</font><br>\n" 
        : "<font color='red'>FAILED &mdash; Error whilst synchronizing game data with database</font><br>\n";
    
    echo (SQLCore::installTableIndexes(true))
        ? "<font color='green'>OK &mdash; applied table indexes</font><br>\n"
        : "<font color='red'>FAILED &mdash; could not apply one more more table indexes</font><br>\n";

    echo (SQLCore::installProcsAndFuncs(true))
        ? "<font color='green'>OK &mdash; created MySQL functions/procedures</font><br>\n"
        : "<font color='red'>FAILED &mdash; could not create MySQL functions/procedures</font><br>\n";

    echo (SQLCore::setTriggers(true))
        ? "<font color='green'>OK &mdash; created MySQL triggers</font><br>\n"
        : "<font color='red'>FAILED &mdash; could not create MySQL triggers</font><br>\n";

    // Create root user and leave welcome message on messageboard
    echo (Coach::create(array('name' => 'root', 'realname' => 'root', 'passwd' => 'root', 'ring' => RING_SYS, 'mail' => 'None', 'phone' => ''))) 
        ? "<font color=green>OK &mdash; root user created.</font><br>\n"
        : "<font color=red>FAILED &mdash; root user was not created.</font><br>\n";

    Message::create(array(
        'f_coach_id' => 1, 
        'title'      => 'OBBLM installed!', 
        'msg'        => 'Congratulations! You have successfully installed Online Blood Bowl League Manager. See "about" and "introduction" for more information.'));
    
    // Done!
    mysql_close($conn);
    return true;
}

function upgrade_database($version)
{
    $conn = mysql_up();
    require_once('lib/class_sqlcore.php');
    require_once('lib/mysql_upgrade_queries');
        
    // Modules
    echo "<b>Running SQLs for modules upgrade...</b><br>\n";
    foreach (Module::getAllUpgradeSQLs($version) as $modname => $SQLs) {
        if (empty($SQLs))
            continue;
        $status = true;
        foreach ($SQLs as $query) {    
            $status &= (mysql_query($query) or die(mysql_error()));
        }
        echo ($status) ? "<font color='green'>OK &mdash; SQLs of $modname</font><br>\n" : "<font color='red'>FAILED &mdash; SQLs of $modname</font><br>\n";
    }

    // Core
    $core_SQLs = $upgradeSQLs[$version];
    echo "<b>Running SQLs for core system upgrade...</b><br>\n";
    $status = true;
    foreach ($core_SQLs as $query) {
        $status &= (mysql_query($query) or die(mysql_error()));
    }
    echo ($status) ? "<font color='green'>OK &mdash; Core SQLs</font><br>\n" : "<font color='red'>FAILED &mdash; Core SQLs</font><br>\n";
    
    // Sync game data
    echo (SQLCore::syncGameData()) 
        ? "<font color='green'>OK &mdash; Synchronized game data with database</font><br>\n" 
        : "<font color='red'>FAILED &mdash; Error whilst synchronizing game data with database</font><br>\n";
    
    // Done!
    mysql_close($conn);
    return true;
}

/*
    Provides helper/shortcut-routines for writing upgrade SQL code.
*/

class SQLUpgrade
{
    public static function runIfColumnNotExists($tbl, $col, $query)
    {
        $colCheck = "SELECT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE COLUMN_NAME='$col' AND TABLE_NAME='$tbl') AS 'exists'";
        $result = mysql_query($colCheck);
        $row = mysql_fetch_assoc($result);
        return ((int) $row['exists']) ? 'SELECT \'1\'' : $query;
    }
    
    // EXACTLY like runIfColumnNotExists(), but has the logic reversed at the return statement.
    public static function runIfColumnExists($tbl, $col, $query)
    {
        $colCheck = "SELECT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE COLUMN_NAME='$col' AND TABLE_NAME='$tbl') AS 'exists'";
        $result = mysql_query($colCheck);
        $row = mysql_fetch_assoc($result);
        return ((int) $row['exists']) ? $query : 'SELECT \'1\'';
    }
    
    public static function runIfTrue($evalQuery, $query)
    {
        $result = mysql_query($evalQuery);
        $row = mysql_fetch_row($result);
        return ((int) $row[0]) ? $query : 'SELECT \'1\'';
    }
}

?>
