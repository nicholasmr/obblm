<?php
/**
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
 
class Translations 
{

public static $registeredLanguages = array('en-GB');
private $lang = 'en-GB';
private $translationFiles = array();
private $docs = array(); # DOMDocument for the xml files.
const main = 'main'; # $this->docs[] key of main translation file.
const fallback = 'en-GB';

public function __construct($lang = false) {
	if ($lang) {
		$this->lang = $lang;
	}
	$this->registerTranslationFile(self::main, 'lang/translations.xml');
}

public function registerTranslationFile($doc, $file) {
	$this->translationFiles[$doc] = $file;
	$this->docs[$doc] = new DOMDocument();
	$this->docs[$doc]->Load($file);
}
	
public function getTrn($key, $doc = false) { 		

    if (!$doc) {
        $doc = self::main;
    }
    if (!in_array($doc, array_keys($this->docs))) {
        fatal("The translation document '$doc' does not exist. The available documents are: ".implode(', ', array_keys($this->docs)));
    }

    $xpath = new DOMXpath($this->docs[$doc]);
    $query = $xpath->query("//$this->lang/$key");
    if ($query->length == 0) {
        # Try fallback language
        $query = $xpath->query("//".self::fallback."/$key");
    }
    return (string) $query->item(0)->nodeValue;
}

}	
?>
