<?php

// +----------------------------------------------------------------------+
// | PHP version 5.1.4                                                    |
// +----------------------------------------------------------------------+
// | Placed in public domain by Allan Hansen, 2006. Share and enjoy!      |
// +----------------------------------------------------------------------+
// | morg.php                                                             |
// | MORG abstract application.                                           |
// +----------------------------------------------------------------------+
// | Authors: Allan Hansen <ah@artemis.dk>                                |
// +----------------------------------------------------------------------+
//
// $Id: morg.php,v 1.2 2006/12/26 01:11:14 ah Exp $


require_once 'morg/application.php';
require_once 'morg/mysql.php';


abstract class morg extends application
{
    const version = '4.0beta1';


    /**
    * Regular Expression match for files.
    * Read: NN*TTTTT.EXT    NN=track number, TTTTT=title, *=seperators: dots, spaces or minuses, EXT = file extention
    *
    * Example: "Rebel Yell.mp3"
    * Example: "01. Rebel Yell.mp3"
    * Example: "01 Rebel Yell.mp3"
    * Example: "01 - Rebel Yell.mp3"
    * Example: "001 Rebel Yell.mp3"
    */

    const filename_pattern = '/^([0-9]{0,3})[\. -]+(.*)\.([a-z0-9]{2,4})$/i';


    /**
    * Regular Expression match dor directories.
    * Read: TTTTT (YYYYL)	TTTTT=title, YYYY=year, L=letter - a = first record this year, b = second record ...
    *
    * Example: "Easy Action (1970)"         -   only one record in 1970
    * Example: "Love It to Death (1971a)"   -   first record in 1971
    * Example: "Killer (1971b)"             -   secondt record in 1971
    */

    const directory_pattern = '/^(.+) \(([1-2][0-9]{3}[a-d]{0,1})\)$/i';


    protected $password;
    protected $server_cp;
    protected $client_cp;
    protected $client_type;
    protected $client_params;
    protected $locale;
    protected $playlist_format;
    protected $toc_columns;
    protected $browser_behaviour;
    protected $assume1;
    protected $assume2;
    protected $assume3;
    protected $assume4;
    protected $assume5;
    protected $assume6;
    protected $assume7;
    protected $category_prefix;


    /**
    * Constructor - test ennviroment and configuration.
    */

    public function __construct()
    {
        // require PHP 5.1.4
        if (strnatcmp(phpversion(), '5.1.4') == -1) {
            $this->fail('PHP version 5.1.4 or newer is required.');
        }

        // require safe mode = Off
        if (get_cfg_var('safe_mode')) {
            $this->fail('PHP is being run in safe mode. Change in php.ini.');
        }

        // require mysql support in PHP
        if (!extension_loaded('mysql')) {
            $this->fail('PHP is missing the required mysql extention.');
        }

        // require iconv support in PHP
        if (!extension_loaded('iconv')) {
            $this->fail('PHP is missing the required iconv extention.');
        }
        
        // require mbstring support in PHP
        if (!extension_loaded('mbstring')) {
            $this->fail('PHP is missing the required mbstring extention.');
        }

        if (!file_exists('config.php')) {
            $this->fail('config.php is missing. Run the install tool.');
        }

        // connect to mysql
        require_once 'config.php';
        $this->db_server   = MYSQL_SERVER;  
        $this->db_database = MYSQL_DATABASE;
        $this->db_username = MYSQL_USERNAME;
        $this->db_password = MYSQL_PASSWORD;
        try {
            $this->db_connect();
        }
        catch (exception $e) {
            $this->fail('Could not connect to mysql database');
        }
        
        // check mysql version
        $this->dbh->query('select @@version');
        $this->dbh->next_record();
        if (strnatcmp($this->dbh->f('@@version'), '5.0.19')  == -1) {
            $this->fail('MySQL 5.0.19 or newer is required.');
        }
        
        // check for innodb support in mysql
        $this->dbh->query('select @@have_innodb');
        $this->dbh->next_record();
        if ($this->dbh->f('@@have_innodb') != 'YES') {
            $this->fail('MySQL does not have the required InnoDB support.');
        }
        
        // check for empty database
        $this->dbh->query('show tables');
        $this->dbh->next_record();
        if (!$this->dbh->num_rows()) {
            $this->fail('Please import morg.sql mysql database.');
        }
        
        // read configuration
        $this->dbh->query('select * from morg_config');
        if ($this->dbh->next_record()) {

            $this->password          = $this->dbh->f('password');
            $this->server_cp         = $this->dbh->f('server_cp');
            $this->client_cp         = $this->dbh->f('client_cp');
            $this->client_type       = $this->dbh->f('client_type');
            $this->client_params     = $this->dbh->f('client_params');
            $this->locale            = $this->dbh->f('locale');
            $this->playlist_format   = $this->dbh->f('playlist_format');
            $this->toc_columns       = $this->dbh->f('toc_columns');
            $this->browser_behaviour = $this->dbh->f('browser_behaviour');
            $this->assume1           = $this->dbh->f('assume1');
            $this->assume2           = $this->dbh->f('assume2');
            $this->assume3           = $this->dbh->f('assume3');
            $this->assume4           = $this->dbh->f('assume4');
            $this->assume5           = $this->dbh->f('assume5');
            $this->assume6           = $this->dbh->f('assume6');
            $this->assume7           = $this->dbh->f('assume7');
            $this->category_prefix   = $this->dbh->f('category_prefix');
        }
        
        // localised sortorder and strtoupper
        setlocale(LC_COLLATE, $this->locale . '.UTF-8');
        setlocale(LC_CTYPE,   $this->locale . '.UTF-8');
    }


    /**
    * Get array with roots.
    *
    * @return   array of (root_id => server_path)
    */

    protected function roots()
    {
        static $result = array ();
        
        if ($result) {
            return $result;
        }
        
        $this->dbh->query('select id, server_path from morg_config_root');
        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('id')] = $this->dbh->f('server_path');
        }

        return $result;
    }



    /**
    * Get root from path (server)
    */

    function get_root($path)
    {
        foreach ($this->roots() as $server_path) {
            if (strstr('/'.$path, '/'.$server_path)) {
                return $server_path;
            }
        }
    }
    
    
    
    /**
    * Get root id from path (server)
    */

    function get_root_id($path)
    {
        foreach ($this->roots() as $id => $server_path) {
            if (strstr('/'.$path, '/'.$server_path)) {
                return $id;
            }
        }
    }
    
    
    
    /**
    * Remove root from path.
    */

    function remove_root($path)
    {
        return str_replace($this->get_root($path), '', $path);
    }


    
    /**
    * Analyze file with getid3 and return mysql file_id.
    */

    protected function analyze_file($filename)
    {
        // get filesize (>2Gb files not supported)
        if (!$filesize = @filesize($filename)) {
            return 0;
        }
        
        // get date file last modified
        $filemtime = filemtime($filename);

        // extract dir- and basename in utf8 and add slashes for mysql
        $path_info    = pathinfo(@iconv($this->server_cp, 'UTF-8', $filename));
        $filename_sls = addslashes($path_info['basename']);
        $dirname_sls  = addslashes($path_info['dirname']);
        
        // get directory_id from dirname_sls
        $this->dbh->query("select id from getid3_directory where filename='$dirname_sls'");
        if ($this->dbh->next_record()) {
            $directory_id = $this->dbh->f('id');
        }
        else {
            $root_id = $this->get_root_id($filename);
            $this->dbh->query("insert into getid3_directory (root_id, filename) values ($root_id, '$dirname_sls')");
            $directory_id = $this->dbh->insert_id();
        }

        // cached? - determined by directory_id, filename and filemtime
        $this->dbh->query("select id from getid3_file where directory_id = $directory_id and filename = '$filename_sls' and filemtime = $filemtime and filesize = $filesize");
        if ($this->dbh->next_record()) {

            // great - return id
            return $this->dbh->f('id');
        }


        // not cached - analyze file with getid3
        require_once 'getid3/getid3.php';

        // initialize getID3 engine
        $getid3 = new getID3;
        $getid3->encoding = 'UTF-8';
        
        $getid3->option_md5_data        = true;
        $getid3->option_md5_data_source = true;

        try {
            $getid3->Analyze($filename);
        }
        catch (getid3_exception $e) {

            // non media files (garbage)
            if ($e->getmessage() == 'Unable to determine file format') {

                // insert in database - no need to analyse again
                $this->dbh->query("replace into getid3_file (filemtime, directory_id, filename) values ($filemtime, $directory_id, '$filename_sls')");
                return $this->dbh->insert_id();
            }

            // rethrow
            else {
                throw $e;
            }
        }

        $format_name    = @$getid3->info['fileformat'] . (@$getid3->info['audio']['dataformat'] != @$getid3->info['fileformat'] ? '/' . @$getid3->info['audio']['dataformat'] : '' );
        $format_name_id = $this->getid3_lookup_format_name_id($format_name, @$getid3->info['mime_type']);

        // skip for non audio files - i.e. images
        if (@$getid3->info['audio']) {

            $encoder_version_id = $this->getid3_lookup_id(@$getid3->info['audio']['encoder'],         'encoder_version');
            $encoder_options_id = $this->getid3_lookup_id(@$getid3->info['audio']['encoder_options'], 'encoder_options');
            $bitrate_mode_id    = $this->getid3_lookup_id(@$getid3->info['audio']['bitrate_mode'],    'bitrate_mode');
            $channel_mode_id    = $this->getid3_lookup_id(@$getid3->info['audio']['channelmode'],     'channel_mode');

            $sample_rate        = (int)@$getid3->info['audio']['sample_rate'];
            $bits_per_sample    = (int)@$getid3->info['audio']['bits_per_sample'];
            $channels           = (int)@$getid3->info['audio']['channels'];
            $lossless           = (int)@$getid3->info['audio']['lossless'];

            $playtime           = (float)@$getid3->info['playtime_seconds'];
            $avg_bit_rate  	    = (float)@$getid3->info['bitrate'];
            $rg_track_gain  	= isset($getid3->info['replay_gain']['track']['adjustment']) ? (float)$getid3->info['replay_gain']['track']['adjustment'] : 'null';
            $rg_album_gain      = isset($getid3->info['replay_gain']['album']['adjustment']) ? (float)$getid3->info['replay_gain']['album']['adjustment'] : 'null';
            
            $md5_data           = $getid3->info['md5_data'];
            
            // insert audio file entry
            $this->dbh->query("replace into getid3_file (directory_id, filename, filemtime, filesize, format_name_id, encoder_version_id, encoder_options_id, bitrate_mode_id, channel_mode_id, sample_rate, bits_per_sample, channels, lossless, playtime, avg_bit_rate, replaygain_track_gain, replaygain_album_gain, md5_data) values ($directory_id, '$filename_sls', $filemtime, $filesize, $format_name_id, 0$encoder_version_id, 0$encoder_options_id, 0$bitrate_mode_id, 0$channel_mode_id, $sample_rate, $bits_per_sample, $channels, $lossless, $playtime, $avg_bit_rate, $rg_track_gain, $rg_album_gain, '$md5_data')");
        }
        
        // insert non-audio file entry
        else {
            // insert audio file entry
            $this->dbh->query("replace into getid3_file (directory_id, filename, filemtime, filesize, format_name_id) values ($directory_id, '$filename_sls', $filemtime, $filesize, $format_name_id)");
        }
        
        $file_id = $this->dbh->insert_id();

        // loop thru tags
        if (@$getid3->info['tags']) {
            foreach ($getid3->info['tags'] as $tag_name => $tag_data) {

                // loop thru fields
                foreach ($tag_data as $field_name => $values) {

                    // loop thru values
                    foreach ($values as $value) {

                        $field_id = $this->getid3_lookup_id($field_name, 'field');
                        $value_id = $this->getid3_lookup_id($value,      'value');

                        // insert comments entry
                        $this->dbh->query("replace into getid3_comment (file_id, field_id, value_id) values ($file_id, $field_id, $value_id)");
                    }
                }
            }
        }

        return $file_id;
    }
    
    
    
    /**
    * Look up or insert value in getid3_format_name table
    */

    function getid3_lookup_format_name_id($name, $mime_type) {

        // NOTE: It might be a very good idea to use some memory for caching in order to save queries.

        // truncate name to 255 (limit in mysql index)
        $name = addslashes(substr($name, 0, 255));
        
        $this->dbh->query("select id from getid3_format_name where name='$name'");
        if ($this->dbh->next_record()) {
            return $this->dbh->f('id');
        }

        $mime_type = addslashes($mime_type);
        $this->dbh->query("replace into getid3_format_name (name, mime_type) values ('$name', '$mime_type')");
        return $this->dbh->insert_id();
    }
    
    
    
    /**
    * Look up or insert value in "getid3_lookup" table
    */

    function getid3_lookup_id($name, $table) {

        // NOTE: It might be a very good idea to use some memory for caching in order to save queries.

        // truncate name to 255 (limit in mysql index)
        $name = addslashes(substr($name, 0, 255));

        $this->dbh->query("select id from getid3_$table where name='$name'");
        if ($this->dbh->next_record()) {
            return $this->dbh->f('id');
        }

        $this->dbh->query("replace into getid3_$table (name) values ('$name')");
        return $this->dbh->insert_id();
    }
    
    

    /**
    * Abstract error handling.
    */

    abstract protected function fail($message);

}


?>