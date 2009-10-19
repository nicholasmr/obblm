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
$core_tables = array(
    'coaches' => array(
        'coach_id'  => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'name'      => 'VARCHAR(50)',
        'realname'  => 'VARCHAR(50)',
        'passwd'    => 'VARCHAR(32)',
        'mail'      => 'VARCHAR(129)',
        'phone'     => 'VARCHAR(25) NOT NULL',
        'ring'      => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
        'settings'  => 'VARCHAR(320) NOT NULL',
        'retired'   => 'BOOLEAN NOT NULL DEFAULT 0',
        'com_lid'   => 'MEDIUMINT UNSIGNED NOT NULL DEFAULT 0',
    ),
    'teams' => array(
        'team_id'           => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'name'              => 'VARCHAR(50)',
        'owned_by_coach_id' => 'MEDIUMINT UNSIGNED',
        'f_race_id'         => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
        'f_lid'             => 'MEDIUMINT UNSIGNED NOT NULL DEFAULT 0',
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
        'elo_0'     => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
    ),
    'players' => array(
        'player_id'         => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'type'              => 'TINYINT UNSIGNED DEFAULT 1',
        'name'              => 'VARCHAR(50)',
        'owned_by_team_id'  => 'MEDIUMINT UNSIGNED',
        'nr'                => 'MEDIUMINT UNSIGNED',
        'position'          => 'VARCHAR(50)',
        'date_bought'       => 'DATETIME',
        'date_sold'         => 'DATETIME',
        'ach_ma'            => 'TINYINT UNSIGNED',
        'ach_st'            => 'TINYINT UNSIGNED',
        'ach_ag'            => 'TINYINT UNSIGNED',
        'ach_av'            => 'TINYINT UNSIGNED',
        'ach_nor_skills'    => 'VARCHAR(320)',
        'ach_dob_skills'    => 'VARCHAR(320)',
        'extra_skills'      => 'VARCHAR(320)',
        'extra_spp'         => 'MEDIUMINT SIGNED',
        'extra_val'         => 'MEDIUMINT SIGNED NOT NULL DEFAULT 0',
    ),
    'leagues' => array(
        'lid'       => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'name'      => 'VARCHAR(50)',
        'location'  => 'VARCHAR(50)',
        'date'      => 'DATETIME',

    ),
    'divisions' => array(
        'did'   => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'f_lid' => 'MEDIUMINT UNSIGNED',
        'name'  => 'VARCHAR(50)',

    ),
    'tours' => array(
        'tour_id'       => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'f_did'         => 'MEDIUMINT UNSIGNED',
        'name'          => 'VARCHAR(50)',
        'type'          => 'TINYINT UNSIGNED',
        'date_created'  => 'DATETIME',
        'rs'            => 'TINYINT UNSIGNED DEFAULT 1',
        'locked'        => 'BOOLEAN',
    ),
    'matches' => array(
        'match_id'      => 'MEDIUMINT SIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'round'         => 'TINYINT UNSIGNED',
        'f_tour_id'     => 'MEDIUMINT UNSIGNED',
        'locked'        => 'BOOLEAN',
        'submitter_id'  => 'MEDIUMINT UNSIGNED',
        'stadium'       => 'MEDIUMINT UNSIGNED',
        'gate'          => 'MEDIUMINT UNSIGNED',
        'fans'          => 'MEDIUMINT UNSIGNED NOT NULL DEFAULT 0',
        'ffactor1'      => 'TINYINT SIGNED',
        'ffactor2'      => 'TINYINT SIGNED',
        'income1'       => 'MEDIUMINT SIGNED',
        'income2'       => 'MEDIUMINT SIGNED',
        'team1_id'      => 'MEDIUMINT UNSIGNED',
        'team2_id'      => 'MEDIUMINT UNSIGNED',
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
        'tv1'           => 'MEDIUMINT UNSIGNED NOT NULL DEFAULT 0',
        'tv2'           => 'MEDIUMINT UNSIGNED NOT NULL DEFAULT 0',
    ),
    'match_data' => array(
        'f_coach_id'    => 'MEDIUMINT UNSIGNED',
        'f_team_id'     => 'MEDIUMINT UNSIGNED',
        'f_player_id'   => 'MEDIUMINT SIGNED',
        'f_race_id'     => 'TINYINT UNSIGNED',
        'f_match_id'    => 'MEDIUMINT SIGNED',
        'f_tour_id'     => 'MEDIUMINT UNSIGNED',
        'f_did'         => 'MEDIUMINT UNSIGNED',
        'f_lid'         => 'MEDIUMINT UNSIGNED',
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
);

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
    
    // Add tables indexes/keys.
    $indexes = "
        ALTER TABLE texts       ADD INDEX idx_f_id                  (f_id);
        ALTER TABLE texts       ADD INDEX idx_type                  (type);
        ALTER TABLE players     ADD INDEX idx_owned_by_team_id      (owned_by_team_id);
        ALTER TABLE teams       ADD INDEX idx_owned_by_coach_id     (owned_by_coach_id);
        ALTER TABLE matches     ADD INDEX idx_f_tour_id             (f_tour_id);
        ALTER TABLE matches     ADD INDEX idx_team1_id_team2_id     (team1_id,team2_id);
        ALTER TABLE matches     ADD INDEX idx_team2_id              (team2_id);
        ALTER TABLE match_data  ADD INDEX idx_m                     (f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_tr                    (f_tour_id);
        ALTER TABLE match_data  ADD INDEX idx_p_m                   (f_player_id,f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_t_m                   (f_team_id,  f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_r_m                   (f_race_id,  f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_c_m                   (f_coach_id, f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_p_tr                  (f_player_id,f_tour_id);
        ALTER TABLE match_data  ADD INDEX idx_t_tr                  (f_team_id,  f_tour_id);
        ALTER TABLE match_data  ADD INDEX idx_r_tr                  (f_race_id,  f_tour_id);
        ALTER TABLE match_data  ADD INDEX idx_c_tr                  (f_coach_id, f_tour_id);
    ";
    $status = true;
    foreach (explode(';', $indexes) as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $status &= mysql_query($query);
        }
    }
    echo ($tblStat)
        ? "<font color='green'>OK &mdash; applied table indexes</font><br>\n"
        : "<font color='red'>FAILED &mdash; could not apply one more more table indexes</font><br>\n";

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

    $core_SQLs = array();
    switch ($version)
    {
        case '075-080':
            $core_SQLs = array(
                SQLUpgrade::runIfColumnNotExists('teams', 'f_lid',      'ALTER TABLE teams ADD COLUMN f_lid MEDIUMINT UNSIGNED NOT NULL DEFAULT 0 AFTER f_race_id'),
                SQLUpgrade::runIfColumnNotExists('coaches', 'com_lid',  'ALTER TABLE coaches ADD COLUMN com_lid MEDIUMINT UNSIGNED NOT NULL DEFAULT 0 AFTER retired'),
                'DELETE FROM texts WHERE type = 8',
                SQLUpgrade::runIfColumnExists('matches', 'hash_botocs', 'ALTER TABLE matches DROP hash_botocs'),
                # Add mg (miss game) indicator in player's match data.
                SQLUpgrade::runIfColumnNotExists('match_data', 'mg', 'ALTER TABLE match_data ADD COLUMN mg BOOLEAN NOT NULL DEFAULT FALSE'),
                SQLUpgrade::runIfTrue('SELECT COUNT(*) FROM match_data', 
                    'UPDATE match_data, (
                    '.implode(' UNION ', array_map(
                        create_function('$o','return "(SELECT $o->f_player_id AS \'f_player_id\', $o->f_match_id AS \'f_match_id\', ".((int) (Player::getPlayerStatus($o->f_player_id,$o->f_match_id) == '.MNG.'))." AS \'mg\')";'), 
                        get_rows('match_data', array('f_match_id', 'f_player_id'))
                    )).'
                    ) AS tmpTbl 
                    SET match_data.mg = tmpTbl.mg WHERE match_data.f_match_id = tmpTbl.f_match_id AND match_data.f_player_id = tmpTbl.f_player_id'
                ),
            );
            break;

        default:
            die('Undefined version upgrade specified.');
    }

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
    echo "<b>Running SQLs for core system upgrade...</b><br>\n";
    $status = true;
    foreach ($core_SQLs as $query) {
        $status &= (mysql_query($query) or die(mysql_error()));
    }
    echo ($status) ? "<font color='green'>OK &mdash; Core SQLs</font><br>\n" : "<font color='red'>FAILED &mdash; Core SQLs</font><br>\n";
    
    // Done!
    mysql_close($conn);
    return true;
}

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
