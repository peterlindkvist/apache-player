<?php

// +----------------------------------------------------------------------+
// | PHP version 5.1.4                                                    |
// +----------------------------------------------------------------------+
// | Placed in public domain by Allan Hansen, 2006. Share and enjoy!      | 
// +----------------------------------------------------------------------+
// | morg_update.php                                                      |
// | MORG analyse application.                                            |
// +----------------------------------------------------------------------+
// | Authors: Allan Hansen <ah@artemis.dk>                                |
// +----------------------------------------------------------------------+
//
// $Id: morg_analyze.php,v 1.1 2006/12/25 23:45:00 ah Exp $


require_once 'morg/morg.php';


class morg_analyze extends morg
{
    // array containing ids to delete    
    protected $delete_ids = array ();
    
    
    /**
    * Constructor
    */
    
    public function __construct()
    {
        parent::__construct();
        
        // Run for max 1 hour
        set_time_limit(3600); 
    }
    


    /**
    * RUN: Scan directory structure and analyze files.
    */

    public function run()
    {
        // output version number        
        $this->msg('MORG ' . morg::version . ' update' . "\n\n");
        
        // populate delete_ids
        $this->populate_delete_ids();
        
        // process music roots
        foreach ($this->roots() as $server_path) {
            $this->process_directory($server_path);
        }
        
        // delete old getid3_file entries
        $this->delete_old_entries();
        
        // flag directories without artwork
        $this->flag_directories_without_artwork();        
    }


    
    /**
    * Recursively scan directory structure.
    */
    
    protected function scan_directory($path, &$files)
    {
        static $counter;
                
        if (!$dir = @opendir($path)) {
            $this->error("Unable to open $path"); 
        }
        
        if (!is_readable($path)) {
            $this->error("Read access denied to $path"); 
        }
            
        else {
            
            while ($dir && $file = readdir($dir)) {
        
                // directories
                if (is_dir("$path/$file")  &&  $file[0]!='.') {
        
                    // go recursive 
                    $this->scan_directory("$path/$file", $files);
                } 
                
                // files (skip hidden)
                elseif ($file{0} != '.') {
                   
                    $files[] = "$path/$file";
                    
                    $counter++;
                    if (!($counter%1000)) {
                        $this->msg('#');
                    }
                }
            }
            closedir($dir);
        }        
    }


    
    /**
    * Analyze all files and subdirectories.
    */
    
    function process_directory($path) 
    {
        static $counter;
    
        // build array containing filenames
        $this->msg("Scanning directories... ");    
        $files = array ();
        $this->scan_directory($path, $files);
        $this->msg(" done\n");
        
        // analyze all files
        $this->msg("Analyzing files...      ");    
        foreach ($files as $filename) {
            
            try {
                
                $file_id = $this->analyze_file($filename);
                
                // remove file_id from delete_ids
                unset($this->delete_ids[$file_id]);
                
                $counter++;
                if (!($counter%250)) {
                    $this->msg('#');
                }            
            }
            catch (Exception $e) {
                
                $this->error($filename . ' skipped: ' . $e->getmessage());
            }
        }
        $this->msg(" done\n");
    }
    
    
    
    /**
    * Populate $this->delete_ids with ids from getid3_file.
    */
    
    protected function populate_delete_ids()
    {
        $this->dbh->query('select id from getid3_file');
        while ($this->dbh->next_record()) {
            $id = $this->dbh->f('id');
            $this->delete_ids[$id] = $id;
        }
    }
    
    
    
    /**
    * Delete old entries in getid3_file.
    */
    
    protected function delete_old_entries()
    {
        if ($this->delete_ids) {
            $this->dbh->query('delete from getid3_file where id in (' . implode(',', $this->delete_ids) . ')');
        }
    }
    
    
    
    /**
    * Find and flag directories without artwork.
    */
    
    protected function flag_directories_without_artwork()
    {
        // this process is done in a transaction to allow usage of statistics while updating
        $this->begin();
        
        // flag all directories as having no artwork (artwork may have been deleted)
        $this->dbh->query("update getid3_directory set artwork='no'");
        
        // flag all directories with artwork
        $this->dbh->query("
            update 
                getid3_directory 
            set 
                artwork='yes'
            where
                id in (            
                    select 
                        distinct(directory_id )
                    from 
                        getid3_file, 
                        getid3_format_name 
                    where 
                        getid3_format_name.id = getid3_file.format_name_id
                    and getid3_format_name.name in ('jpg/', 'png/', 'gif/')
                )
        ");
        
        // commit transaction
        $this->commit();
    }
    
    
    
    protected function fail($message)
    {
        die('ERROR: ' . $message . "\n");
    }
    
    
    protected function error($message)
    {
        $this->msg("\n" . $message . "\n");
    }
    
    
    
    /**
    * Handle messages - suppress in quiet mode
    */
    
    protected static function msg($msg) 
    { 
        if (!(isset($_SERVER["argv"][1]) && $_SERVER["argv"][1] == "-q")) {
            echo $msg; 
        }
    }

}


?>
