<?php
const DATA_PATH = 'data/';

class PathFinder{
    private $_paths = array();
    private $_root;
    private static $inst;
    private $_debug = false;
    private $_web_root;
    private $_members = array();
    private $exclude_dirs = array();
    private $is_loaded = false;
    private $cache_path = DATA_PATH;
    private $cache_filename = 'directories.text';
    private $_pwd;

    public function __construct(){}

    public function init($force = false)
    {
        if( !is_dir($this->cache_path) )
            shell_exec('mkdir -p '. escapeshellarg($this->cache_path));

        $root = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
        $this->setRootPath(implode('/', $root));
        $pwd = array_pop($root);
        $this->setPWD($pwd);

        // Try to Load cached directories first
        if( $force === false && file_exists($this->cache_path .'/'. $this->cache_filename) ){
            $path_data = unserialize(file_get_contents($this->cache_path .'/'. $this->cache_filename));

            if( is_array($path_data) ){
                $this->_paths = $path_data;
                $this->is_loaded = true;
            }
        }
        else
            echo 'Forced or cache file does not exists';

        return $this;
    }

    // Automatically traverse the project folder structure
    // and map the web and system path to all directories
    // from most to least specificity
    public function loadDirectories($sys_root = '', $web_root = '')
    {
        if( $this->loaded() )
            return;

        $pwd = $this->getPWD(); // present working directory
        $root = '';
        $wroot = '';

        if( $sys_root != '' )
            $root = $sys_root;

        if( $web_root != '' )
            $wroot = $web_root;

        if( !isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] == '' )
            $host = 'localhost';
        else
            $host = $_SERVER['HTTP_HOST'];

        // Initialize web root
        if( $wroot == '' )
            $this->setWebPath('http'. (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's': '') .'://' . $host .'/'. $pwd);
        else
            $this->setWebPath($wroot);

        // initialize local root
        if( $root != '' )
            $this->setRootPath($root);

        $root = $this->getRootPath();

        // Get all directories
        // FIXME: for the purpose of this project scandir will be used instead of shell because this is an XAMP server and it uses window cmd to run scripts.
//        $excludes = implode('\|', $this->getExcludedDirectories());
//        $command = 'find -L '. escapeshellarg($root) .' -type d | grep -v '. escapeshellarg($excludes);
//        $dirs = explode("\n", shell_exec($command));

        $dirs = scandir($this->getRootPath());

        // Filter out the root from the directory paths
        $func = function($v) use($root)
        {
            return str_replace($root, '', $v);
        };

        $dirs = array_map($func, $dirs);

        foreach( $dirs as $dir )
        {
            if( !is_dir($dir) )
                continue;

            $this->_paths[$dir][] = array('web'=>$this->_web_root .'/'. $dir . '/', 'root'=>$root .'/'. $dir .'/');
        }

        $this->is_loaded = true;

        // Now cache the directories
        file_put_contents($this->cache_path . $this->cache_filename, serialize($this->_paths));
    }

    public function setExcludedDirectories(Array $dirs)
    {
        $this->exclude_dirs = $dirs;
    }

    public function getExcludedDirectories()
    {
        return $this->exclude_dirs;
    }

    public function getPath($filename, $dir_name = '', $url = true)
    {
        $path_type = ($url ? 'web' : 'root');
        $path = $this->retrievePath($filename, $path_type);

//     echo $path;

        if( $path )
        {
            if( $this->_debug )
                echo $filename .' From Cache: '. $path .'<br>';

            return $path;
        }

        // If the path does not exists in the cached directories
        // Reload directories to see if it is in a new directory
//         $this->loadDirectories();
        $paths = $this->_paths;


//       echo '<pre>'. var_export($paths, true) .'</pre>';

        if( $dir_name != '' )
        {
            if( isset($paths[$dir_name]) )
                $paths = array_reverse($paths[$dir_name]);
            else
                return false;

            foreach( $paths as $dir_array )
            {
                $dir_array = array_reverse($dir_array);
                $abs_file_path = $dir_array['root'] . $filename;
                $web_file_path = $dir_array['web'] . $filename;
                $valid_path = $this->validatePath($filename, $abs_file_path);

                if( $valid_path )
                {
                    if( $url )
                    {
                        $this->savePath($filename, $web_file_path, 'web');
                        return $web_file_path;
                    }
                    else
                    {
                        $this->savePath($filename, $abs_file_path, 'root');
                        return $abs_file_path;
                    }
                }
            }

            return $this->validatePath($filename, $path);
        }
        else
        {
            //var_export($paths);

            foreach( $paths as $dirname=>$array )
            {
                $array = array_reverse($array);
                foreach( $array as $dir_array )
                {
                    $dir_array = array_reverse($dir_array);
                    $abs_file_path = $dir_array['root'] . $filename;
                    $web_file_path = $dir_array['web'] . $filename;
                    $valid_path = $this->validatePath($filename, $abs_file_path);

                    if( $valid_path )
                    {
                        if( $url )
                            return $web_file_path;
                        else
                            return $abs_file_path;
                    }
                }
            }
        }

        return false;
    }

    public function getRawPaths()
    {
        return $this->_paths;
    }

    public function getMembers(){
        return $this->_members;
    }

    // Only validates system file paths not urls
    // so $path must be the system file path
    private function validatePath($name, $path)
    {
        if (is_file($path) || file_exists($path)) {
            //echo $path .'<br>';
            return $path;
        }

        return false;
    }

    public function savePath($filename, $path, $path_type = 'web')
    {
        $this->_members[$filename][$path_type] = $path;
    }

    public function retrievePath($filename, $path_type = 'web')
    {
        if( isset($this->_members[$filename][$path_type]) )
            return $this->_members[$filename][$path_type];

        return false;
    }

    public function setWebPath($web){
        if( $web != '' )
            $this->_web_root = $web;
    }

    public function getWebPath(){
        return $this->_web_root;
    }

    public function setRootPath($root)
    {
        if( $root != '' )
            $this->_root = $root;
    }

    public function getRootPath()
    {
        return $this->_root;
    }

    public function setPWD($root)
    {
        if( $root != '' )
            $this->_pwd = $root;
    }

    public function getPWD()
    {
        return $this->_pwd;
    }

    public function loaded()
    {
        return $this->is_loaded;
    }

    /**
     * Get instance of the object (the singleton method)
     *
     * @return  PathFinder
     */
    public static function getInstance()
    {
        if( !isset(self::$inst) )
            self::$inst = new self;

        return self::$inst;
    }

    public function debug($debug = false)
    {
        $this->_debug = $debug;
    }
}