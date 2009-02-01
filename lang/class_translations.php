<?php
/**
 *  Copyright (c) Juergen Unfried <juergen.unfried@gmail.com>2009. All Rights Reserved.
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
 
 /**
  * Class Translations
  * Usage example:
  * --------------
  * require_once 'class.translations.php';
  * $lang = new Translations();
  * $nodeValue = $lang->getTranslation('obblm/about', 'en-GB')
  * 
  * PUBLIC FUNCTION OVERVIEW
  * ------------------------
  * getTranslation 		- Returns the Text for the specified key/language.
  * getVersion			- Returns the version number for the OBBLM.
  * getTranslatorsFor	- Returns an array of translators for the specified langauge.
  * getDisplayNameFor	- Returns the display name for the specified locale.
  * getDisplayIconFor	- Returns the display icon filename for the specified locale.
  */
class Translations {
 	/** 
	 * DOMDocument for the xml file.
	 */
	private $doc;
	
	/** 
	 * The default langague to use if no language is specified.
	 */
	private $defaultLang;
	
	/** 
	 * The translation file.
	 */
	private $translationFile;
	
	/**
	 * Constructor for the Translations class
	 *
	 */
	public function __construct($lang = '') {
   		$this->doc = null;
   		$this->defaultLang = $lang != '' ? $lang : 'en-GB';
   		$this->translationFile = 'lang/translations.xml';
  	}
	
  	/**
  	 * Loads the XML File into the DOMDocument.
  	 *
  	 * @param String $filename
  	 */
  	private function loadTranslationFile($filename = '') {
  		if ($this->doc != null) {
  			return;
  		}
  		
  		$file = $filename == '' ? $this->translationFile : $filename;
  		
  		$this->doc = new DOMDocument();
  		$this->doc->Load($file);
  	}
  	
  	/**
  	 * Returns the Text for the specified key/language.
  	 * 
  	 * Converts the key and language parameters to an xpath
  	 * and returns the node value of the first node. If the
  	 * node value is empty and the fallback is set to true then
  	 * the function is called again to return the same key for
  	 * the default language. 
  	 *
  	 * @param String $key the key for the translation
  	 * @param String $lang the language to look up
  	 * @param boolean $fallback true if a lookup with default language
  	 * 				  for the same key shoudl be done if the result was empty. 
  	 * @return String the node value for the key/language pair
  	 */
  	public function getTrn($key, $lang = '', $fallback = true) {
  		return $this->getTranslation($key, $lang, $fallback);
  	}
  	public function getTranslation($key, $vars = '', $lang = '', $fallback = true) { 		
		$this->loadTranslationFile();
		
		$l = $lang == '' ? $this->defaultLang : $lang;
		
		$xpath = new DOMXpath($this->doc);
		$query = $xpath->query("//obblm/$l/$key");

		@$nodeValue = $query->item(0)->nodeValue;
		$nodeValue = (String)$nodeValue;

		if ($nodeValue == '' && $fallback == true) {
			return $this->getTranslation($key, '', false);
		}
		
		return $this->replaceVars($nodeValue, $vars);
	}
	
	/**
	 * Replaces all variables in a string with the new values given in the array.
	 * 
	 * Variables will be in smarty format: {$varname}.
	 * For easier usage onle the varname needs to be specified.
	 * 
	 * Example: array('varname' => 'varvalue') as vars array will replace
	 * all {$varname} tags with varvalue.
	 *
	 * @param String the text to look and replace variables
	 * @param array of key/values with the variable name and new value pairs
	 * 
	 * @return String the result string.
	 */
	private function replaceVars($subject, $vars) {
		if (!is_array($vars)) return $subject;
		
		// First we need to "copy" the array to prefix the arraykey with {$
		// and suffix it with }
		reset($vars);
		while (list($k, $v) = each($vars)) {
		   $newVars['{$' . $k . '}'] = $v;
		}	
		return str_replace(array_keys($newVars), array_values($newVars), $subject);		//
	}
	
	/**
	 * Returns the version number for the OBBLM.
	 * 
	 * The version of the OBBLM is a attribute in the root
	 * node of the translation file.
	 * 
	 * @return String the version string
	 */
	public function getVersion() {
		$this->loadTranslationFile();	
		
		return $this->doc->getElementsByTagName("obblm")->item(0)->getAttribute('version');
	}
	
	/**
	 * Returns an array of translators for the specified langauge.
	 * 
	 * Parses the xml file for translators for the specified locale,
	 * and returns them as array.
	 * 
	 * @param String the locale to look for translators (i.e. en-UK, de-DE)
	 * 
	 * @return Array an array of translators for the specified locale
	 */
	public function getTranslatorsFor($lang = '') {
		$this->loadTranslationFile();

		$l = $lang == '' ? $this->defaultLang : $lang;
		
		$xpath = new DOMXpath($this->doc);
		$elements = $query = $xpath->query("//obblm/$l/translators/translator");
		
		$translators = array();
		foreach($elements as $element) {
			$translators[] = $element->nodeValue;
		}
		
		return $translators;
	}
	
	/**
	 * Returns the display name for the specified locale.
	 * 
	 * @param String the locale
	 * 
	 * @return String the displayname for the locale
	 */
	public function getDisplayNameFor($lang = '') {
		$this->loadTranslationFile();
		
		$l = $lang == '' ? $this->defaultLang : $lang;
		
		$xpath = new DOMXpath($this->doc);
		$elements = $query = $xpath->query("//obblm/$l/displayName");

		$nodeValue = $query->item(0)->nodeValue;
		
		return (String)$nodeValue;
	}
	
 	/**
	 * Returns the display icon filename for the specified locale.
	 * 
	 * @param String the locale
	 * 
	 * @return String the displayicon for the locale
	 */
	public function getDisplayIconFor($lang = '') {
		$this->loadTranslationFile();

		$l = $lang == '' ? $this->defaultLang : $lang;
		
		$xpath = new DOMXpath($this->doc);
		$elements = $query = $xpath->query("//obblm/$l/displayIcon");

		$nodeValue = $query->item(0)->nodeValue;
		
		return (String)$nodeValue;
	}
}	
?>
