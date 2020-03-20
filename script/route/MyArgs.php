<?php

class MyArgs
{

    static $_basePath;

    static $opts = "c:a:";

    public static function getBasePath(){
        if(self::$_basePath === null){
            self::$_basePath = $_SERVER['SCRIPT_FILENAME'];
            $pos = strrpos(self::$_basePath,'/');
            self::$_basePath = substr(self::$_basePath,0,$pos);
            if(self::$_basePath){
                self::$_basePath = rtrim(self::$_basePath,'/').'/';
            }
        }
        return self::$_basePath;
    }

    public static function getController($default = null)
    {
        $ctrl = getopt(self::$opts);
        return isset($ctrl['c'])?$ctrl['c']:$default;
    }
    
    public static function getAction($default = null)
    {
        $method = getopt(self::$opts);
        return isset($method['a'])?$method['a']:$default;
    }

    public static function getArgs(){
        $args = getopt(self::$opts);
        self::shift($args);
        $argv = $GLOBALS['argv'];
        array_shift($argv);
        return $argv;
    }

    static function shift($options_array)
    {
        foreach( $options_array as $o => $a )
        {
            // Look for all occurrences of option in argv and remove if found :
            // ----------------------------------------------------------------
            // Look for occurrences of -o (simple option with no value) or -o<val> (no space in between):
            while($k=array_search("-".$o.$a,$GLOBALS['argv']))
            {    // If found remove from argv:
                if($k)
                    unset($GLOBALS['argv'][$k]);
            }
            // Look for remaining occurrences of -o <val> (space in between):
            while($k=array_search("-".$o,$GLOBALS['argv']))
            {    // If found remove both option and value from argv:
                if($k)
                {    unset($GLOBALS['argv'][$k]);
                    unset($GLOBALS['argv'][$k+1]);
                }
            }
        }
        // Reindex :
        $GLOBALS['argv']=array_merge($GLOBALS['argv']);
    }
}