<?php

class FileManager
{
    static function getCssDirectoryName() {
        return realpath('./css');
    }

    static function getSettingsDirectoryName() {
        return realpath('./localsettings');
    }
    
    static function getAllCoreCssSheetFileNames() {
        $cssDirectory = FileManager::getCssDirectoryName();
        return glob($cssDirectory . '/stylesheet*.css');
    }
    
    static function writeFile($fileName, $fileContents) {
        $file = fopen($fileName, 'w');
        fwrite($file, $fileContents);
        fclose($file);
    }
    
    static function readFile($fileName) {
        $fileContents = '';
        $file = fopen($fileName, 'r');
        while($file && !feof($file))
             $fileContents .= fgets($file);
        fclose($file);
        return $fileContents;
    }
    
    static function copyFile($fileName, $newName) {
        return copy($fileName, $newName);
    }
}