<?php

class Settings
{
    public static function getValueOrDefault($key, $default) {
        global $settings;
        if(!isset($settings[$key]))
            return $default;
        return $settings[$key];            
    }
}