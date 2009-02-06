<?php

/*
 *  Copyright (c) Niels Orsleff Justesen <njustesen@gmail.com> and Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007. All Rights Reserved.
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

function get_list($table, $col, $val, $new_col) {
    $result = mysql_query("SELECT $new_col FROM $table WHERE $col = '$val'");
    if (mysql_num_rows($result) <= 0)
        return array();
    
    $row = mysql_assoc($result);
    return explode(',', $row[$new_col]);
}

function set_list($table, $col, $val, $new_col, $new_val = array()) {
    $new_val = implode(',', $new_val);
    if (mysql_query("UPDATE $table SET $new_col = '$new_val' WHERE $col = '$val'")) 
        return true;
    else
        return false;
}

function setup_tables() {

    /*
     *  MySQL datatypes:
     *
     *      TINYINT   UNSIGNED  = max 255
     *      MEDIUMINT UNSIGNED  = max 16777215
     *      BIGINT    UNSIGNED  = max 18446744073709551615
     *
     *  http://dev.mysql.com/doc/refman/5.1/en/data-types.html
     */

    // Connect to MySQL
    $conn = mysql_up();

    // Small subroutine used by outer function.
    if (!function_exists('mk_table')) {
        function mk_table($query, $table) {
            if (mysql_query($query))
                echo "<font color='green'>Created $table table successfully.</font><br>\n";
            else
                echo "<font color='red'>Failed creating $table table.</font><br>\n";
        }
    }

    /* Table creation queries */

    $query = 'CREATE TABLE IF NOT EXISTS coaches
                (
                coach_id        MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name            VARCHAR(50),
                passwd          VARCHAR(32),
                mail            VARCHAR(129),
                admin           BOOLEAN
                )';
    mk_table($query, 'coaches');

    $query = 'CREATE TABLE IF NOT EXISTS teams
                (
                team_id             MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name                VARCHAR(50),
                owned_by_coach_id   MEDIUMINT UNSIGNED,
                race                VARCHAR(20),
                treasury            BIGINT SIGNED,
                apothecary          BOOLEAN,
                rerolls             MEDIUMINT UNSIGNED,
                fan_factor          MEDIUMINT UNSIGNED,
                ass_coaches         MEDIUMINT UNSIGNED,
                cheerleaders        MEDIUMINT UNSIGNED
                )';
    mk_table($query, 'teams');

    $query = 'CREATE TABLE IF NOT EXISTS players
                (
                player_id           MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                type                TINYINT UNSIGNED DEFAULT 1,
                name                VARCHAR(50),
                owned_by_team_id    MEDIUMINT UNSIGNED,
                nr                  MEDIUMINT UNSIGNED,
                position            VARCHAR(50),
                date_bought         DATETIME,
                date_sold           DATETIME,
                ach_ma              TINYINT UNSIGNED,
                ach_st              TINYINT UNSIGNED,
                ach_ag              TINYINT UNSIGNED,
                ach_av              TINYINT UNSIGNED,
                ach_nor_skills      VARCHAR(320),
                ach_dob_skills      VARCHAR(320),
                extra_skills        VARCHAR(320),
                extra_spp           MEDIUMINT SIGNED
                )';
    /*
        Note: 320 chars comes from:
        Chars = Max_number_of_skills * (char_lenght_of_longest_skillname + 1_delimter_char)
        Chars = 16 * (19 + 1) = 320
    */
    mk_table($query, 'players');

    $query = 'CREATE TABLE IF NOT EXISTS matches
                (
                match_id            MEDIUMINT SIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                round               TINYINT UNSIGNED,
                f_tour_id           MEDIUMINT UNSIGNED,
                locked              BOOLEAN,
                submitter_id        MEDIUMINT UNSIGNED,
                stadium             MEDIUMINT UNSIGNED,
                gate                MEDIUMINT UNSIGNED,
                ffactor1            TINYINT SIGNED,
                ffactor2            TINYINT SIGNED,
                income1             MEDIUMINT SIGNED,
                income2             MEDIUMINT SIGNED,
                team1_id            MEDIUMINT UNSIGNED,
                team2_id            MEDIUMINT UNSIGNED,
                date_created        DATETIME,
                date_played         DATETIME,
                date_modified       DATETIME,
                team1_score         TINYINT UNSIGNED,
                team2_score         TINYINT UNSIGNED,
                smp1                TINYINT SIGNED DEFAULT 0,
                smp2                TINYINT SIGNED DEFAULT 0,
                tcas1               TINYINT UNSIGNED DEFAULT 0,
                tcas2               TINYINT UNSIGNED DEFAULT 0
                )';
    mk_table($query, 'matches');

    $query = 'CREATE TABLE IF NOT EXISTS tours
                (
                tour_id         MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name            VARCHAR(50),
                type            TINYINT UNSIGNED,
                date_created    DATETIME,
                rs              TINYINT UNSIGNED DEFAULT 1
                )';
    mk_table($query, 'tours');

    // Note: "f_" is a abbreviation for "from_".

    $query = 'CREATE TABLE IF NOT EXISTS match_data
                (
                f_coach_id          MEDIUMINT UNSIGNED,
                f_team_id           MEDIUMINT UNSIGNED,
                f_player_id         MEDIUMINT SIGNED,
                f_match_id          MEDIUMINT SIGNED,
                f_tour_id           MEDIUMINT UNSIGNED,
                mvp                 TINYINT UNSIGNED,
                cp                  TINYINT UNSIGNED,
                td                  TINYINT UNSIGNED,
                intcpt              TINYINT UNSIGNED,
                bh                  TINYINT UNSIGNED,
                si                  TINYINT UNSIGNED,
                ki                  TINYINT UNSIGNED,
                inj                 TINYINT UNSIGNED,
                agn1                TINYINT UNSIGNED,
                agn2                TINYINT UNSIGNED

                )';
    mk_table($query, 'match_data');

    $query = 'CREATE TABLE IF NOT EXISTS texts
                (
                txt_id  MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                type    TINYINT UNSIGNED,
                f_id    MEDIUMINT UNSIGNED,
                date    DATETIME,
                txt2    TEXT,
                txt     TEXT
                )';
    mk_table($query, 'texts');

    /* Add tables indexes/keys. */
    
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
        ALTER TABLE match_data  ADD INDEX idx_c_m                   (f_coach_id, f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_p_tr                  (f_player_id,f_tour_id);
        ALTER TABLE match_data  ADD INDEX idx_t_tr                  (f_team_id,  f_tour_id);
        ALTER TABLE match_data  ADD INDEX idx_c_tr                  (f_coach_id, f_tour_id);
    ";

    foreach (explode(';', $indexes) as $query) {
        $query = trim($query);
        if (!empty($query)) {
            mysql_query($query);
        }
    }

    /* Create root user and leave welcome message on messageboard*/

    if (Coach::create(array('name' => 'root', 'passwd' => 'root', 'admin' => true, 'mail' => 'None')))
        echo "<font color=green>Created root user successfully.</font><br>\n";
    else
        echo "<font color=red>Failed to create root user.</font><br>\n";
        
    Message::create(array(
        'f_coach_id' => 1, 
        'title'      => 'OBBLM installed!', 
        'msg'        => 'Congratulations! You have successfully installed Online Blood Bowl League Manager. See "about" and "introduction" for more information.'));

    mysql_close($conn);
    return true;
}

?>
