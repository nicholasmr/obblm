<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009-2010. All Rights Reserved.
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
    
    The 'class' field should be the name of the class that implements the interface "ModuleInterface".
    The main() containing class MUST be loaded in "filesLoadTime".
*/ 

Module::register(array(
    'class'         => 'IndcPage',
    'filesRunTime'  => array(),
    'filesLoadTime' => array('inducements/class_inducements.php')
));

Module::register(array(
    'class'         => 'PDFroster', 
    'filesRunTime'  => array('pdf/settings.php', 'pdf/bb_pdf_class.php'),
    'filesLoadTime' => array('pdf/pdf_roster.php')
));

Module::register(array(
    'class'         => 'RSSfeed', 
    'filesRunTime'  => array(),
    'filesLoadTime' => array('rss/class_rss.php')
));

Module::register(array(
    'class'         => 'SGraph', 
    'filesRunTime'  => array(),
    'filesLoadTime' => array('statsgraph/header.php', 'statsgraph/class_statsgraph.php')
));

Module::register(array(
    'class'         => 'Memmatches', 
    'filesRunTime'  => array(),
    'filesLoadTime' => array('memmatches/class_memmatches.php')
));

Module::register(array(
    'class'         => 'Wanted',
    'filesRunTime'  => array(),
    'filesLoadTime' => array('wanted/class_wanted.php')
));

Module::register(array(
    'class'         => 'HOF',
    'filesRunTime'  => array(),
    'filesLoadTime' => array('halloffame/class_hof.php')
));

Module::register(array(
    'class'         => 'Prize',
    'filesRunTime'  => array(),
    'filesLoadTime' => array('prizes/header.php', 'prizes/class_prize.php')
));

Module::register(array(
    'class'         => 'UPLOAD_BOTOCS', 
    'filesRunTime'  => array('leegmgr/class_match_botocs.php'),
    'filesLoadTime' => array('leegmgr/settings.php', 'leegmgr/class_upload_botocs.php')
));

Module::register(array(
    'class'         => 'XML_BOTOCS', 
    'filesRunTime'  => array(),
    'filesLoadTime' => array('leegmgr/settings.php', 'leegmgr/class_xml_botocs.php')
));

Module::register(array(
    'class'         => 'Registration', 
    'filesRunTime'  => array(),
    'filesLoadTime' => array('registration/settings.php', 'registration/class_registration.php')
));

Module::register(array(
    'class'         => 'LogSubSys', 
    'filesRunTime'  => array(),
    'filesLoadTime' => array('log/class_log.php', 'log/header.php')
));

Module::register(array(
    'class'         => 'Gallery', 
    'filesRunTime'  => array(),
    'filesLoadTime' => array('gallery/class_gallery.php')
));

Module::register(array(
    'class'         => 'Search', 
    'filesRunTime'  => array(),
    'filesLoadTime' => array('search/class_search.php')
));

Module::register(array(
    'class'         => 'UserScheduledGames',
    'filesRunTime'  => array(),
    'filesLoadTime' => array('usrsched/settings.php', 'usrsched/class_usrsched.php')
));

Module::register(array(
    'class'         => 'InFocus', 
    'filesRunTime'  => array(),
    'filesLoadTime' => array('infocus/class_infocus.php')
));

Module::register(array(
    'class'         => 'PDFMatchReport',
    'filesRunTime'  => array(),
    'filesLoadTime' => array('pdfmatchreport/class_pdfmatchreport.php')
));

?>
