<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009. All Rights Reserved.
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
    This header is used to register OBBLM modules.
    
    NOTE:
    -----
    filesRunTime are loaded when Module::run() is executed whilest filesLoadTime are loaded when Module::register() is executed.
*/ 

Module::register(array(
    'modname'       => 'inducements', 
    'author'        => 'Daniel Straalman',
    'date'          => '2009',
    'setCanvas'     => true, 
    'main'          => 'indcPage', 
    'filesRunTime'  => array('inducements/inducements.php'),
    'filesLoadTime' => array()
));

Module::register(array(
    'modname'       => 'pdfroster',
    'author'        => 'Daniel Straalman',
    'date'          => '2008-2009',
    'setCanvas'     => false, 
    'main'          => 'fpdf_roster', 
    'filesRunTime'  => array('pdf/settings.php', 'pdf/bb_pdf_class.php', 'pdf/pdf_roster.php'),
    'filesLoadTime' => array()
));

Module::register(array(
    'modname'       => 'rss',
    'author'        => 'Juergen Unfried',
    'date'          => '2008',
    'setCanvas'     => false, 
    'main'          => 'OBBLMRssWriter::main', 
    'filesRunTime'  => array('rss/class_rss.php'),
    'filesLoadTime' => array()
));

Module::register(array(
    'modname'       => 'statsgraph',
    'author'        => 'Nicholas Mossor Rathmann',
    'date'          => '2008-2009',
    'setCanvas'     => false, 
    'main'          => 'SGraph::make', 
    'filesRunTime'  => array('statsgraph/class_statsgraph.php'),
    'filesLoadTime' => array('statsgraph/header.php')
));

Module::register(array(
    'modname'       => 'teamxmlexport',
    'author'        => 'Nicholas Mossor Rathmann',
    'date'          => '2009',
    'setCanvas'     => false, 
    'main'          => 'Team_export::main', 
    'filesRunTime'  => array('teamxmlexport/class_team_export.php'),
    'filesLoadTime' => array()
));

Module::register(array(
    'modname'       => 'memmatches',
    'author'        => 'Nicholas Mossor Rathmann',
    'date'          => '2008-2009',
    'setCanvas'     => true, 
    'main'          => 'Memmatches::main', 
    'filesRunTime'  => array('memmatches/class_memmatches.php'),
    'filesLoadTime' => array()
));

Module::register(array(
    'modname'       => 'comparison',
    'author'        => 'Nicholas Mossor Rathmann',
    'date'          => '2009',
    'setCanvas'     => true, 
    'main'          => 'Comparison::main', 
    'filesRunTime'  => array('comparison/class_comparison.php'),
    'filesLoadTime' => array()
));

Module::register(array(
    'modname'       => 'leegmgr',
    'author'        => 'William Leonard',
    'date'          => '2009',
    'setCanvas'     => true, 
    'main'          => 'UPLOAD_BOTOCS::main', 
    'filesRunTime'  => array('leegmgr/class_match_botocs.php', 'leegmgr/class_upload_botocs.php'),
    'filesLoadTime' => array('leegmgr/settings.php')
));

Module::register(array(
    'modname'       => 'botocsxml',
    'author'        => 'William Leonard',
    'date'          => '2009',
    'setCanvas'     => false, 
    'main'          => 'XML_BOTOCS::main', 
    'filesRunTime'  => array('leegmgr/class_xml_botocs.php'),
    'filesLoadTime' => array('leegmgr/settings.php')
));

Module::register(array(
    'modname'       => 'registration',
    'author'        => 'William Leonard',
    'date'          => '2009',
    'setCanvas'     => true, 
    'main'          => 'Registration::main', 
    'filesRunTime'  => array('registration/class_registration.php'),
    'filesLoadTime' => array('registration/settings.php')
));

Module::register(array(
    'modname'       => 'cyanide_match_import',
    'author'        => 'Gr�gory Rom�',
    'date'          => '2009',
    'setCanvas'     => true,
    'main'          => 'main',
    'filesRunTime'  => array('cyanide/class_CyanideMatch.php', 'cyanide/match_import.php'),
    'filesLoadTime' => array('cyanide/settings.php')
));


Module::register(array(
    'modname'       => 'cyanide_team_import',
    'author'        => 'Gr�gory Rom�',
    'date'          => '2009',
    'setCanvas'     => true,
    'main'          => 'main',
    'filesRunTime'  => array('cyanide/class_CyanideTeam.php', 'cyanide/team_import.php'),
    'filesLoadTime' => array('cyanide/settings.php')
));

?>
