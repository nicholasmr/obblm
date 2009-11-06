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
        # Add MySQL function getPlayerStatus(pid, mid).
        'DROP FUNCTION IF EXISTS getPlayerStatus',
        'CREATE FUNCTION getPlayerStatus(pid MEDIUMINT UNSIGNED, mid MEDIUMINT SIGNED) 
            RETURNS TINYINT UNSIGNED 
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            DECLARE status TINYINT UNSIGNED DEFAULT NULL;
            SELECT inj INTO status FROM match_data, matches WHERE 
                    match_data.f_player_id = pid AND
                    matches.match_id = match_data.f_match_id AND
                    matches.date_played IS NOT NULL AND
                    matches.date_played < (SELECT date_played FROM matches WHERE matches.match_id = mid)
                    ORDER BY date_played DESC LIMIT 1;
            RETURN IF(status IS NULL, 1, status);
        END',
        # Add mg (miss game) indicator in player's match data.
        SQLUpgrade::runIfColumnNotExists('match_data', 'mg', 'ALTER TABLE match_data ADD COLUMN mg BOOLEAN NOT NULL DEFAULT FALSE'),
        'UPDATE match_data SET mg = IF(f_player_id < 0, FALSE, IF(getPlayerStatus(f_player_id, f_match_id) = 1, FALSE, TRUE))',
        
        # Migrate to using player position IDs 
#        'UPDATE players,teams,game_data_players SET f_pos_id = pos_id WHERE owned_by_team_id = team_id AND teams.f_race_id = game_data_players.f_race_id AND position = pos',
        # Migrate to using skill IDs 
#        'UPDATE players 
#            SET ach_nor_skills = REPLACE(ach_nor_skills, "Bone Head", "Bone-Head"),
#                ach_dob_skills = REPLACE(ach_dob_skills, "Bone Head", "Bone-Head")',
#            # We do a double reverse replacement to prevent replacing "Claw/Claws" as "Claw/Claw/Claws"
#        'UPDATE players 
#            SET ach_nor_skills = REPLACE(REPLACE(ach_nor_skills, "Claw/Claws", "Claws"), "Claws", "Claw/Claws"),
#                ach_dob_skills = REPLACE(REPLACE(ach_dob_skills, "Claw/Claws", "Claws"), "Claws", "Claw/Claws")',

#        'UPDATE players, game_data_skills 
#            SET ach_nor_skills = REPLACE(ach_nor_skills, game_data_skills.name, skill_id), 
#                ach_dob_skills = REPLACE(ach_dob_skills, game_data_skills.name, skill_id)'
#        UPDATE players SET `ach_nor_skills` = REPLACE(`ach_nor_skills`, "Dumb-Off", "Dump-Off") WHERE `ach_nor_skills` LIKE "%Dumb-Off%";
    ),
);

?>
