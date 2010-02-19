<?php

$_t_player_types_skill_normal = 
'
DROP TABLE IF EXISTS "Player_Type_Skill_Categories_Normal";
CREATE TABLE Player_Type_Skill_Categories_Normal (ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,idPlayer_Types INTEGER ,idSkill_Categories INTEGER ,DESCRIPTION VARCHAR(255) );
';

$_dat_skill_normal =
"
INSERT INTO player_type_skill_categories_normal VALUES (1, 1, 1, 'Human Lineman General');
INSERT INTO player_type_skill_categories_normal VALUES (2, 2, 1, 'Human Catcher General');
INSERT INTO player_type_skill_categories_normal VALUES (3, 2, 2, 'Human Catcher Agility');
INSERT INTO player_type_skill_categories_normal VALUES (4, 3, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (5, 3, 3, '');
INSERT INTO player_type_skill_categories_normal VALUES (6, 4, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (7, 4, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (8, 5, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (9, 6, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (10, 6, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (11, 7, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (12, 7, 3, '');
INSERT INTO player_type_skill_categories_normal VALUES (13, 8, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (14, 8, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (15, 9, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (16, 9, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (17, 10, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (18, 11, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (19, 11, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (20, 12, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (21, 12, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (22, 13, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (23, 13, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (24, 13, 3, '');
INSERT INTO player_type_skill_categories_normal VALUES (25, 14, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (26, 14, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (27, 15, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (28, 16, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (29, 17, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (30, 17, 3, '');
INSERT INTO player_type_skill_categories_normal VALUES (31, 18, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (32, 18, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (33, 19, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (34, 19, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (35, 20, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (36, 21, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (37, 22, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (38, 23, 3, '');
INSERT INTO player_type_skill_categories_normal VALUES (39, 23, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (40, 24, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (41, 24, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (42, 25, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (43, 25, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (44, 26, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (45, 27, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (46, 28, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (47, 28, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (48, 29, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (49, 30, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (50, 31, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (51, 32, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (52, 33, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (53, 33, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (54, 33, 5, '');
INSERT INTO player_type_skill_categories_normal VALUES (56, 34, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (57, 34, 5, '');
INSERT INTO player_type_skill_categories_normal VALUES (60, 44, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (61, 32, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (62, 32, 5, '');
INSERT INTO player_type_skill_categories_normal VALUES (64, 45, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (65, 46, 4, '');
INSERT INTO player_type_skill_categories_normal VALUES (66, 47, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (67, 47, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (68, 48, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (69, 48, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (70, 48, 3, '');
INSERT INTO player_type_skill_categories_normal VALUES (71, 49, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (72, 49, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (73, 50, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (74, 50, 2, '');
INSERT INTO player_type_skill_categories_normal VALUES (75, 51, 1, '');
INSERT INTO player_type_skill_categories_normal VALUES (76, 51, 2, '');
";
?>