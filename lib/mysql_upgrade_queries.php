<?php

/*
    Load only this file on demand.
*/

$upgradeSQLs = array(
    '075-080' => array(
        # Add league relations.
        SQLUpgrade::runIfColumnNotExists('teams', 'f_lid',      'ALTER TABLE teams ADD COLUMN f_lid MEDIUMINT UNSIGNED NOT NULL DEFAULT 0 AFTER f_race_id'),
        SQLUpgrade::runIfColumnNotExists('coaches', 'com_lid',  'ALTER TABLE coaches ADD COLUMN com_lid MEDIUMINT UNSIGNED NOT NULL DEFAULT 0 AFTER retired'),
        # Delete, now modulized, type from texts.
        'DELETE FROM texts WHERE type = 8',
        SQLUpgrade::runIfColumnExists('matches', 'hash_botocs', 'ALTER TABLE matches DROP hash_botocs'),
        # Add mg (miss game) indicator in player's match data.
        SQLUpgrade::runIfColumnNotExists('match_data', 'mg', 'ALTER TABLE match_data ADD COLUMN mg BOOLEAN NOT NULL DEFAULT FALSE'),
        'UPDATE match_data SET mg = IF(f_player_id < 0, FALSE, IF(getPlayerStatus(f_player_id, f_match_id) = 1, FALSE, TRUE))',
        # Before migrating to using skill IDs we must correct a few skill names.
        SQLUpgrade::runIfColumnExists('players', 'ach_nor_skills', 'UPDATE players 
            SET ach_nor_skills = REPLACE(ach_nor_skills, "Bone Head", "Bone-Head"),
                ach_dob_skills = REPLACE(ach_dob_skills, "Bone Head", "Bone-Head")'),
            # We do a double reverse replacement to prevent replacing "Claw/Claws" as "Claw/Claw/Claws"
        SQLUpgrade::runIfColumnExists('players', 'ach_nor_skills', 'UPDATE players 
            SET ach_nor_skills = REPLACE(REPLACE(ach_nor_skills, "Claw/Claws", "Claws"), "Claws", "Claw/Claws"),
                ach_dob_skills = REPLACE(REPLACE(ach_dob_skills, "Claw/Claws", "Claws"), "Claws", "Claw/Claws")'),
                
        # Column alterations
        SQLUpgrade::runIfColumnExists('teams', 'elo_0', 'ALTER TABLE teams DROP elo_0'),
        SQLUpgrade::runIfColumnNOTExists('teams', 'played_0', 'ALTER TABLE teams ADD COLUMN played_0 SMALLINT UNSIGNED NOT NULL DEFAULT 0'),
        'UPDATE teams SET played_0 = won_0 + draw_0 + lost_0',
        'ALTER TABLE players MODIFY player_id MEDIUMINT SIGNED',
            # New FF col system.
        SQLUpgrade::runIfColumnNOTExists('players', 'ff_bought', 'ALTER TABLE players ADD COLUMN ff_bought TINYINT UNSIGNED AFTER rerolls'),
        SQLUpgrade::runIfColumnNOTExists('players', 'ff', 'ALTER TABLE players ADD COLUMN ff TINYINT UNSIGNED'),
        SQLUpgrade::runIfColumnExists('players', 'fan_factor', 'UPDATE players SET ff_bought = fan_factor'),
        SQLUpgrade::runIfColumnExists('players', 'fan_factor', 'ALTER TABLE players DROP fan_factor'),
        
        // Add DPROPS fields
        SQLUpgrade::runIfColumnNotExists('teams', 'swon', 'ALTER TABLE teams ADD COLUMN swon SMALLINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('teams', 'sdraw', 'ALTER TABLE teams ADD COLUMN sdraw SMALLINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('teams', 'slost', 'ALTER TABLE teams ADD COLUMN slost SMALLINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('teams', 'win_pct', 'ALTER TABLE teams ADD COLUMN win_pct FLOAT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('teams', 'wt_cnt', 'ALTER TABLE teams ADD COLUMN wt_cnt SMALLINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('teams', 'elo', 'ALTER TABLE teams ADD COLUMN elo FLOAT DEFAULT NULL'),
        SQLUpgrade::runIfColumnNotExists('teams', 'tv', 'ALTER TABLE teams ADD COLUMN tv MEDIUMINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('coaches', 'swon', 'ALTER TABLE coaches ADD COLUMN swon SMALLINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('coaches', 'sdraw', 'ALTER TABLE coaches ADD COLUMN sdraw SMALLINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('coaches', 'slost', 'ALTER TABLE coaches ADD COLUMN slost SMALLINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('coaches', 'win_pct', 'ALTER TABLE coaches ADD COLUMN win_pct FLOAT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('coaches', 'wt_cnt', 'ALTER TABLE coaches ADD COLUMN wt_cnt SMALLINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('coaches', 'elo', 'ALTER TABLE coaches ADD COLUMN elo FLOAT DEFAULT NULL'),
        SQLUpgrade::runIfColumnNotExists('coaches', 'team_cnt', 'ALTER TABLE coaches ADD COLUMN team_cnt TINYINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('players', 'value', 'ALTER TABLE players ADD COLUMN value MEDIUMINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('players', 'status', 'ALTER TABLE players ADD COLUMN status TINYINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('players', 'date_died', 'ALTER TABLE players ADD COLUMN date_died DATETIME'),
        SQLUpgrade::runIfColumnNotExists('players', 'ma', 'ALTER TABLE players ADD COLUMN ('.implode(', ', array_map(create_function('$f','return "$f TINYINT UNSIGNED";'), array('ma','st','ag','av','inj_ma','inj_st','inj_ag','inj_av','inj_ni'))).')'),
        SQLUpgrade::runIfColumnNotExists('players', 'win_pct', 'ALTER TABLE players ADD COLUMN win_pct FLOAT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('tours', 'begun', 'ALTER TABLE tours ADD COLUMN begun BOOLEAN'),
        SQLUpgrade::runIfColumnNotExists('tours', 'empty', 'ALTER TABLE tours ADD COLUMN empty BOOLEAN'),
        SQLUpgrade::runIfColumnNotExists('tours', 'finished', 'ALTER TABLE tours ADD COLUMN finished BOOLEAN'),
        SQLUpgrade::runIfColumnNotExists('tours', 'winner', 'ALTER TABLE tours ADD COLUMN winner MEDIUMINT UNSIGNED'),

        // Add relation fields
        SQLUpgrade::runIfColumnNotExists('players', 'f_cid', 'ALTER TABLE players ADD COLUMN f_cid MEDIUMINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('players', 'f_rid', 'ALTER TABLE players ADD COLUMN f_rid TINYINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('players', 'f_pos_id', 'ALTER TABLE players ADD COLUMN f_pos_id SMALLINT UNSIGNED'),
        SQLUpgrade::runIfColumnNotExists('players', 'f_tname', 'ALTER TABLE players ADD COLUMN f_tname VARCHAR(60)'),
        SQLUpgrade::runIfColumnNotExists('players', 'f_rname', 'ALTER TABLE players ADD COLUMN f_rname VARCHAR(60)'),
        SQLUpgrade::runIfColumnNotExists('players', 'f_cname', 'ALTER TABLE players ADD COLUMN f_cname VARCHAR(60)'),
        SQLUpgrade::runIfColumnNotExists('teams', 'f_rname', 'ALTER TABLE teams ADD COLUMN f_rname VARCHAR(60)'),
        SQLUpgrade::runIfColumnNotExists('teams', 'f_cname', 'ALTER TABLE teams ADD COLUMN f_cname VARCHAR(60)'),

        # Migrate to using player position IDs 
        SQLUpgrade::runIfColumnExists('players', 'position', 'UPDATE players,teams,game_data_players SET f_pos_id = pos_id WHERE owned_by_team_id = team_id AND teams.f_race_id = game_data_players.f_race_id AND position = pos'),
        SQLUpgrade::runIfColumnExists('players', 'position', 'ALTER TABLE players CHANGE COLUMN position f_pos_name VARCHAR(60)'),
    ),
);

/*
    Upgrade functions
*/

$upgradeFuncs = array(
    '075-080' => array('upgrade_075_080_pskills_migrate'),
);

function upgrade_075_080_pskills_migrate()
{
    # Note: Requires the players_skills table AND all skills name corrections made in DB so they fit $skillsididx.
    global $skillididx;
    global $skillididx_rvs; # Make global for use below (dirty trick).
    $skillididx_rvs = array_flip($skillididx);

    # Already run this version upgrade (note: this routine specifically does not remove the skills column(s))?
    if (!SQLUpgrade::doesColExist('players', 'ach_nor_skills')) {
        return true;
    }
    $status = true;
    $status &= (mysql_query("
        CREATE TABLE IF NOT EXISTS players_skills (
            f_pid      MEDIUMINT SIGNED NOT NULL,
            f_skill_id SMALLINT UNSIGNED NOT NULL,
            type       VARCHAR(1)
        )
    ") or die(mysql_error()));
    $players = get_rows(
        'players', 
        array('player_id', 'ach_nor_skills', 'ach_dob_skills', 'extra_skills'), 
        array('ach_nor_skills != "" OR ach_dob_skills != "" OR extra_skills != ""')
    );
        foreach ($players as $p) {
        foreach (array('N' => 'ach_nor_skills', 'D' => 'ach_dob_skills', 'E' => 'extra_skills') as $t => $grp) {
            $values = empty($p->$grp) ? array() : array_map(create_function('$s', 'global $skillididx_rvs; return "('.$p->player_id.',\''.$t.'\',".$skillididx_rvs[$s].")";'), explode(',', $p->$grp));

            if (!empty($values)) {
                $status &= (mysql_query("INSERT INTO players_skills(f_pid, type, f_skill_id) VALUES ".implode(',', $values)) or die(mysql_error()));
            }
        }
    }
    $sqls_drop = array(
        SQLUpgrade::runIfColumnExists('players', 'ach_nor_skills', 'ALTER TABLE players DROP ach_nor_skills'),
        SQLUpgrade::runIfColumnExists('players', 'ach_dob_skills', 'ALTER TABLE players DROP ach_dob_skills'),
        SQLUpgrade::runIfColumnExists('players', 'extra_skills', 'ALTER TABLE players DROP extra_skills'),
    );
    foreach ($sqls_drop as $query) {
        $status &= (mysql_query($query) or die(mysql_error()));
    }

    return $status;
}

?>
