<?php
/**
 *  Copyright (c) Juergen Unfried <juergen.unfried@gmail.com> 2009. All Rights Reserved.
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
 * Usage example:
 * --------------
 * require_once 'class.OBBLMRssWriter.php';
 * $rss = new OBBLMRssWriter('OBBLM League xyz', 'http://www.xyz.com', 'The rss feed for the league....'EN', '1,2');
 * $rss->generateNewsRssFeed();
 *
 * NOTE: This class reads the version from the translations file, so it is required
 *       to include the translation class.
 * IMPORTANT: either place the class in the same folder as the translation xml file
 *            or change the translationFile path in the translation class to point to 
 *            the correct file (eg. ../lang/translations.xml instead of translations.xml
 * 
 * NOTE: don't forget to include the rss.xml file into the html header
 */ 

class OBBLMRssWriter {
	/**
	 * The name of the channel. It's how people refer to your service.
	 */
	private $title = '';
	
	/**
	 * The URL to the HTML website corresponding to the channel.
	 */
	private $link = '';
	
	/**
	 *  Phrase or sentence describing the channel.
	 */
	private $desc = '';
	
	/*
	 * A URL that points to the documentation for the format used in the RSS file.
	 */
	private $docs = 'http://www.rssboard.org/rss-specification';
	
	/**
	 * The language the channel is written in.
	 * A list of allowable values for this element, as provided by Netscape, is
	 * at http://www.rssboard.org/rss-language-codes
	 */
	private $lang = '';
	
	/**
	 * Types of texts in table texts that shall be included in the newsfeed.
	 * Either an array of numeric values or a comma seperated string.
	 * Default: 1;
	 */
	private $type = '1';
	
	/**
	 * Constructor for the OBBLMRssWriter class
	 *
	 * Required elements
	 * @param String the name of the channel
	 * @param String the url to the website
	 * @param String the description of the channel
	 * 
	 * Optional elements
	 * @param String The language of the channel
	 * @param Types of texts in tabe texts that shall be included in the newsfeed
	 */
	public function __construct($title, $link, $desc, $lang = '', $type = 1) {
   		$this->title = $title;
   		$this->link = $link;
   		$this->desc = $desc;
   		$this->lang = $lang;
   		$this->type = $type;
  	}
  	
  	/**
  	 * Generates the newsfeed and writes it to disc.
  	 *
  	 */
	function generateNewsRssFeed() {
        $dom = new DOMDocument();
        $dom->formatOutput = true;

        $el_root = $dom->appendChild($dom->createElement('rss'));
        $el_root->setAttribute('version', '2.0');
        
        $el_channel = $el_root->appendChild($dom->createElement('channel'));
        
        $el_channel->appendChild($dom->createElement('title', $this->title));
        $el_channel->appendChild($dom->createElement('link', $this->link));
        $el_channel->appendChild($dom->createElement('description', $this->desc));
        
        if ($this->lang != '') {
        	$el_channel->appendChild($dom->createElement('language', $this->lang));
        }
        
        $el_channel->appendChild($dom->createElement('docs', $this->docs));
        $el_channel->appendChild($dom->createElement('lastBuildDate', date(DATE_RSS)));
        $el_channel->appendChild($dom->createElement('generator', 'OBBLM ' . OBBLM_VERSION));
        
        if (is_array($this->type)) {
        	$this->type = implode(',', $this->type);
        }
        
        $sql = "SELECT UNIX_TIMESTAMP(date) as timestamp,txt,txt2 FROM texts WHERE type in ($this->type) ORDER BY timestamp desc LIMIT 20";
        	$result = mysql_query($sql);
        
        while ($row = mysql_fetch_assoc($result)) {
            $el_item = $dom->createElement('item');
            $el_item->appendChild($dom->createElement('title', $row['txt2']));
            $el_item->appendChild($dom->createElement('description', $row['txt']));
            $el_item->appendChild($dom->createElement('link', $this->link));
            $el_item->appendChild($dom->createElement('pubDate', date(DATE_RSS, $row['timestamp'])));
            
            $el_channel->appendChild($el_item);
        }
        
        // Write the file
        $handle = fopen ("rss.xml", "w");
        fwrite($handle, $dom->saveXML());
        fclose($handle);
        
        return $dom->saveXML();
    }
}
?>
