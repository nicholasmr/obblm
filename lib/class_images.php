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

define('NO_PIC', IMG.'/nopic.jpg'); # Used when no picture is uploaded.
define('UPLOAD_DIR', IMG);

define('IMGTYPE_PLAYER',      1);
define('IMGTYPE_TEAMLOGO',    2);
define('IMGTYPE_TEAMSTADIUM', 3);
define('IMGTYPE_COACH',       4);

define('IMGPATH_PLAYERS',      UPLOAD_DIR.'/players');
define('IMGPATH_TEAMLOGOS',    UPLOAD_DIR.'/teams');
define('IMGPATH_TEAMSTADIUMS', UPLOAD_DIR.'/stadiums');
define('IMGPATH_COACHES',      UPLOAD_DIR.'/coaches');

class Image
{
    /***************
     * Properties 
     ***************/

    public $obj = null;
    public $obj_id = 0;
    
    public static $defaultHTMLUploadName = 'pic'; # The default value of the file's <input type='file'> name field.
    
    // Supported file types.
    private static $supportedExtensions = array(
        # mime type => extension
        'image/gif'  => 'gif', 
        'image/jpeg' => 'jpeg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
    );
    
    // Type to path mappings.
    private static $typeToPathMappings = array(
        IMGTYPE_PLAYER      => IMGPATH_PLAYERS,
        IMGTYPE_TEAMLOGO    => IMGPATH_TEAMLOGOS,
        IMGTYPE_TEAMSTADIUM => IMGPATH_TEAMSTADIUMS,
        IMGTYPE_COACH       => IMGPATH_COACHES,
    );
    
    /***************
     * Methods 
     ***************/
    
    public function __construct($obj, $obj_id) {
        $this->obj = $obj;
        $this->obj_id = $obj_id;
    }

    public function getPath() 
    {
        foreach (self::$supportedExtensions as $ext) {
            if (file_exists($filePath = self::$typeToPathMappings[$this->obj].'/'.$this->obj_id.'.'.$ext)) {
                return $filePath;
            }
        }
        
        // Else return default image.
        if ($this->obj == IMGTYPE_TEAMLOGO) {
            $r = new Race(get_alt_col('teams', 'team_id', $this->obj_id, 'f_race_id'));
            $roster = $r->getRoster();
            return $roster['other']['icon'];
        }
        else {
            return NO_PIC;
        }
    }

    public function save($file_name = false) 
    {
        // $file_name must be a valid key in the $_FILES array.
    
        // Use default file name?
        if (!$file_name) {
            $file_name = self::$defaultHTMLUploadName;
        }
        
        // Errors?        
        if (!isset($_FILES[$file_name]['tmp_name'])) {
            return array(false, 'Internal error: Can\'t find the uploaded file in PHP $_FILES array.');
        }
        if (!in_array($_FILES[$file_name]['type'], array_keys(self::$supportedExtensions))) {
            return array(false, 'Sorry, the uploaded file has an unsupported extension. The following extensions are supported: '.implode(', ', array_values(self::$supportedExtensions)));
        }
        
        // Create parent dir if non existing.
        if (!is_dir(self::$typeToPathMappings[$this->obj])) {
            mkdir(self::$typeToPathMappings[$this->obj]);
        }
        
        // Move file away from temp location.
        if (move_uploaded_file(
                $A = $_FILES[$file_name]['tmp_name'], 
                $B = self::$typeToPathMappings[$this->obj].'/'.$this->obj_id.'.'.self::$supportedExtensions[$_FILES[$file_name]['type']]
            )) {
            # If suceeded remove all other possible existing files.
            foreach (self::$supportedExtensions as $mimeType => $ext) {
                if ($mimeType != $_FILES[$file_name]['type']) {
                    @unlink(self::$typeToPathMappings[$this->obj].'/'.$this->obj_id.'.'.$ext);
                }
            }
        }
        else {
            return array(false, 'Internal error: Failed to move file from "'.$A.'" to "'.$B.'"');
        }
        
        return array(true, true);
    }
    
    public static function makeBox($obj, $obj_id, $showUploadForm = false, $suffix = false) 
    {
        // Prints a nice picture box.    
        $height = $width = 250; # Picture dimensions.
        $img = new Image($obj, $obj_id);
        
        ?>
        <img alt="Image" height="<?php echo $height;?>" width="<?php echo $width;?>" src="<?php echo $img->getPath()?>">
        <br><br>
        <?php
        if ($showUploadForm) {
            ?>
            <form method='POST' enctype="multipart/form-data">
                <input type="hidden" name="type" value="pic">
                Upload new image (preferably <?php echo "${width}x${height}";?>): <br>
                <input name="<?php echo self::$defaultHTMLUploadName.(($suffix) ? $suffix : '')?>" type="file"><br>
                <input type="submit" name="pic_upload" value="Upload">
            </form>
            <?php
        }
    }

}
