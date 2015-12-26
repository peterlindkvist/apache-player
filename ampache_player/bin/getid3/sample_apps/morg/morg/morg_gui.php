<?php

// +----------------------------------------------------------------------+
// | PHP version 5.1.4                                                    |
// +----------------------------------------------------------------------+
// | Placed in public domain by Allan Hansen, 2006. Share and enjoy!      |
// +----------------------------------------------------------------------+
// | morg_gui.php                                                         |
// | MORG GUI application.                                                |
// +----------------------------------------------------------------------+
// | Authors: Allan Hansen <ah@artemis.dk>                                |
// +----------------------------------------------------------------------+
//
// $Id: morg_gui.php,v 1.2 2006/12/26 01:11:14 ah Exp $


require_once 'morg/morg.php';
require_once 'morg/abstraction.php';



//// Main application

class morg_gui extends morg
{

    protected $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    protected $charset = 'UTF-8';
    protected $css     = 'morg.css';

    protected $tool_tips;
    protected $t;

    protected $action;


    ///////////////////////////////////////////////////////////////////////////
    //// Application Logic ////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    public function run()
    {

        // change userbased settings/cookies
        if (isset($_GET['set_show_numbers'])) {
            setcookie('show_numbers', !@$_COOKIE['show_numbers'], time()+3600*24*365*3);
            $this->redirect(str_replace('&set_show_numbers=1', '', $_SERVER['REQUEST_URI']));
        }
        if (isset($_GET["set_show_playtime"])) {
            setcookie('show_playtime', !@$_COOKIE['show_playtime'], time()+3600*24*365*3);
            $this->redirect(str_replace('&set_show_playtime=1', '', $_SERVER['REQUEST_URI']));
        }
        if (isset($_GET["set_sort_alpha"])) {
            setcookie('sort_alpha', !@$_COOKIE['sort_alpha'], time()+3600*24*365*3);
            $this->redirect(str_replace('&set_sort_alpha=1', '', $_SERVER['REQUEST_URI']));
        }
        if (isset($_GET["set_sort_mode"])) {
            $new_sort_mode = @$_COOKIE['sort_mode'] >= 3 ? 0 : @$_COOKIE['sort_mode'] + 1;
            setcookie('sort_mode', $new_sort_mode, time()+3600*24*365*3);
            $this->redirect(str_replace('&set_sort_mode=1', '', $_SERVER['REQUEST_URI']));
        }

        $req_auth        = 1;
        $req_location    = 2;
        $req_field       = 4;

        // allowed actions  - 'name' => sum of requirements
        $actions = array (

            'toc'                => $req_auth,
            'browse'             => $req_auth + $req_location,
            'play'               => $req_auth + $req_location,
            'image'              => $req_auth + $req_location,
            'stream'             => $req_auth + $req_location,
            'stats'              => $req_auth,
            'stats_formats'      => $req_auth,
            'stats_formats_list' => $req_auth,
            'stats_fields'       => $req_auth,
            'stats_field_dist'   => $req_auth + $req_field,
            'stats_field_files'  => $req_auth + $req_field,
            'stats_duplicates1'  => $req_auth,
            'stats_duplicates2'  => $req_auth,
            'stats_tagless'      => $req_auth,
            'stats_key_field'    => $req_auth + $req_field,
            'stats_replaygain'   => $req_auth,
            'stats_artless_dirs' => $req_auth,
            'search'             => $req_auth,
            'search_results'     => $req_auth,
            'config'             => $req_auth,
        );

        // action from GET only
        $this->action  = @$_GET['action'];

        // first action is default action
        if (!$this->action) {
            $this->action = key($actions);
        }

        // check if action is allowed
        if (!isset($actions[$this->action])) {
            $this->forbidden();
        }
        
        // authorized ip/range required?
        if ($req_auth & $actions[$this->action]) {
            
            // allow access if no ip/ranges defined
            if ($auth_ips = $this->auth_ips()) {

                // init
                $authorized = false;

                // explode user's ip by dot
                $ra = explode('.', $_SERVER['REMOTE_ADDR']);
            
                // loop thru auth ips/ranges
                foreach ($auth_ips as $ip) {
            
                    // explode auth ip/range by dot
                    $ip = explode('.', $ip);
            
                    // perform ip/range match 
                    if ($ra[0] == $ip[0]  &&  $ra[1] == $ip[1]  &&  ($ip[2] == '*' || $ra[2] == $ip[2])  &&  ($ip[3] == '*' || $ra[3] == $ip[3])) {
                        $authorized = true;
                        break;
                    }
                }

                if (!$authorized) {
                    $this->fail('Your ip number, ' . $_SERVER['REMOTE_ADDR'] . ' is not allowed to access this page.');
                }
            }
        }

        // location required?
        if ($req_location & $actions[$this->action]) {

            // no location specified
            if (!@$_GET['location']) {
                $this->bad_request();
            }

            // strip any slashes added by magic quotes
            $this->location = get_magic_quotes_gpc() ? stripslashes($_GET['location']) : $_GET['location'];

            // decode location
            $this->location = rawurldecode($this->location);

            // security: check that cover is within roots()
            while (true) {
                foreach ($this->roots() as $server_path) {
                    if (ereg('^' . $server_path, $this->location)) {
                        break 2;
                    }
                }
                $this->forbidden();
            }

            if (!file_exists($this->location)) {
                $this->fail("Non-existant: $this->location");
            }

            if (!is_readable($this->location)) {
               $fail->error("Read access denied to $this->location");
            }
        }

        // field name required?
        if ($req_field & $actions[$this->action]) {

            // no field specified
            if (!$this->field = @$_GET['field']) {
                $this->bad_request();
            }

            if (!preg_match('/^[a-z_]+$/', $this->field)) {
                $this->bad_request();
            }
        }

        // Call action method
        $action = 'action_' . $this->action;
        $this->$action();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Table of Contents ////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_toc()
    {
        $g = new xml_gen;

        // init arrays containing filenames of directories
        $directories = $categories = array ();

        // scan root
        foreach ($this->roots() as $path) {

            if (!$dir = @opendir($path)) {
                $this->error("Unable to open <b>$path</b>");
            }

            elseif (!is_readable($path)) {
               $this->error("Read access denied to $path");
            }

            // list root directory
            while ($filename = readdir($dir))  {
                if (is_dir("$path/$filename")  &&  $filename[0] != '.' ) {

                    // convert filename to UTF-8 for sorting (___path is appended to allow same filenames in different roots)
                    $sort_name = strtoupper(@iconv($this->server_cp, 'UTF-8', $filename . '___' . $path));

                    // convert filename to uppercase for sorting
                    $sort_name = mb_strtoupper($sort_name, 'UTF-8');

                    // directory has category_prefix - will be listed at the top
                    if (mb_substr($sort_name, 0, 1, 'UTF-8') == $this->category_prefix) {

                        $categories[$sort_name] = array ("$path/$filename", $filename);
                    }

                    // normal directory
                    else {

                        // build array  'sort_name' => array (full_path, filename)
                        $directories[$sort_name] = array ("$path/$filename", $filename);
                    }
                }
            }
            closedir($dir);
        }

        // sort
        uksort($categories,  'strcoll');
        uksort($directories, 'strcoll');

        // merge categories and directories to one array
        $root = array_merge($categories, $directories);
        
        if (!$root) {
            $this->redirect('?action=config');
        }

        // generate array with xml content
        $last_first_letter = null;
        $xml = array ();
        foreach ($root as $sort_name => $path_info) {

            list($path, $filename) = $path_info;

            // remove any starting quotes from sort_name - i.e. 'Til Tuesday
            $clean_name = preg_replace("/^['`´\"]*/", '', $sort_name);

            // new section (new first letter)
            $first_letter = mb_substr($clean_name, 0, 1, 'UTF-8');
            if ($last_first_letter != $first_letter) {
                $xml[] = false;
            }
            $last_first_letter = $first_letter;

            // add directory
            $xml[] = $g->a("./?action=browse&location=".urlencode($path), $this->display_filename($filename));
        }

        // calc approx lines per column
        $lines =  sizeof($xml) / $this->toc_columns;

        // split $xml array into columns ...
        for ($column = 0; $column < $this->toc_columns; $column++) {

            // ... each containing approx $lines lines
            for ($i = 0; $i < $lines; $i++) {
                $columns_xml[$column][] = array_shift($xml);
            }

            // remove first element if section header (false)
            if (@$columns_xml[$column][0] === false) {
                array_shift($columns_xml[$column]);
            }

            // take next element if it's alone
            if (@$xml[0] && $xml[1] === false) {
                $columns_xml[$column][] = array_shift($xml);
            }
        }

        // calc column width
        $column_width = floor(100/$this->toc_columns) . '%';

        // begin output
        $this->head('Table of Contents');

        $t = new table($this->toc_columns, "id='toc'");

        for ($column = 0; $column < $this->toc_columns; $column++) {
            $t->data(implode($g->br(), $columns_xml[$column]), "style='width: $column_width'");
        }

        $t->done();

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Browse (Directory Listing) ///////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_browse()
    {
        $g = new xml_gen;

        // get title from path
        $title = $this->get_title_from_path($this->location);

        // up link -- resolve
        $path  = ereg_replace("(.*)/(.*)", "\\1", $this->location);
        $up = $this->remove_root($path) ? './?action=browse&location='.urlencode($path) : './';
        unset($path);

        // encode location for play link
        $play = urlencode($this->location);

        // scan and process directory
        list ($directories, $files) = $this->scan_location($this->location);

        // init arrays holding file information
        $audio_files = $images = array ();

        // loop thru files and analyze
        foreach ($files as $file_info) {

            // unpack
            list($path, $name, $track) = $file_info;

            // analyze file, and get extended info
            try {
                $ext_info = $this->get_extended_file_info($this->analyze_file($path));
            }
            catch (Exception $e) {
                continue;
            }

            // audio file
            if ($ext_info->playtime) {
                $audio_files[] = array ($path, $name, $track, $ext_info);
            }

            // image file
            elseif (in_array($ext_info->format_name, array ('jpg/', 'png/', 'gif/'))) {
                $images[] = $path;
            }
        }

        // free some memory
        unset($files);

        // begin output
        $this->head($title, $up, $play, !empty($directories), !empty($audio_files));

        // divide screen in two colums
        $z = new table(2);

        //// left column - directories and files
        $z->data(null, "id='dirlist_left'");

        // table for directories and files
        $t = new table(2, "class='list_view'");

        // init
        $playing_time = 0;

        // loop thru directories (if any)
        foreach ($directories as $directory_info) {

            // unpack
            list ($path, $filename, $year) = $directory_info;

            // output directory name
            $t->data();
            echo $g->b($g->a('./?action=browse&location='.urlencode($path), $this->display_filename($filename)));

            // output year
            if ($year) {
                echo " ($year)";
            }

            // Output playing time of directory
            $t->data(null, "class='playtime'");
            if (!empty($_COOKIE['show_playtime'])) {
                $playtime = $this->get_directory_playtime($path);
                echo $this->display_seconds_human_readable($playtime);
                $playing_time += $playtime;
            }
        }

        // loop thru audio_files (if any)
        foreach ($audio_files as $file_info) {

            // unpack
            list($path, $name, $track, $ext_info) = $file_info;

            // output track name
            $t->data();

            // file is ordinary audio file
            if (!empty($_COOKIE['show_numbers'])  &&  $track) {
                $name = "$track $name";
            }
            echo $g->a('./?action=play&location='.urlencode($path), $this->display_filename($name), $this->generate_tooltip_xml($ext_info));

            // output playing time
            $t->data(null, "class='playtime'");

            if (!empty($_COOKIE['show_playtime'])) {
                echo $this->display_seconds_human_readable($ext_info->playtime);
                $playing_time += $ext_info->playtime;
            }
        }

        // output total playing time
        if (!empty($_COOKIE['show_playtime']) && $playing_time) {
            $t->data();
            $t->data(null, "class='playtime_total'");
            echo $this->display_seconds_human_readable($playing_time);
        }

        $t->done();


        //// right column - images
        $z->data(false, "id='dirlist_right'");

        if ($images) {

            // loop thru images array and check permissions
            foreach ($images as $path) {

                // output warning if image isn't readable
                if (!is_readable($path)) {
                    echo $g->p("$path is unreadable.");
                }
            }

            // sort images - just alphabetically
            sort($images);

            // show first image
            $image = $images[0];
            echo $g->img('./?action=image&location='.urlencode($image), "id='cover'");

            // show multiple images
            if (sizeof($images) > 1) {

                // shuffle images
                shuffle($images);

                // replace filename with URI
                for ($i = 0; $i < sizeof($images); $i++) {
                    $images[$i] = './?action=image&location='.urlencode($images[$i]);
                }

                // client side slide show
                $img_array = implode("','", $images);
                echo "
                <script type='text/javascript'>
                <!--
                var slide_active = 1;
                var slide_images = new Array('$img_array');
                var which_image  = 0;

                function slide_it()
                {
                    if (slide_active == 1) {

                        document.getElementById('cover').src = slide_images[which_image]

                        if (which_image < slide_images.length-1)
                            which_image++
                        else
                            which_image=0
                    }

                    setTimeout('slide_it()', 1000 * 15)
                }
                slide_it();
                // -->
                </script>";

                // show dots - one for each image - in seperate layer
                $width = 25 * sizeof($images);
                $left  = 862 - $width;

                // generate xml for dots
                foreach ($images as $image) {
                    $dots[] = $g->a_img('javascript:void(0)', 'images/dot.png', false, 'images/doto.png', array('cover', $image, $image), array("slide_active=0","slide_active=1"));
                }

                // output dots
                echo $g->div(implode('', $dots), "id='dots'");
            }
        }

        $z->done();

        $this->foot();
    }




    ///////////////////////////////////////////////////////////////////////////
    //// Action: Generate Play List ///////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_play()
    {
        // play single file
        if (is_file($this->location)) {
            $playlist = array ($this->client_path($this->location));
        }

        // play directory
        else {

            // get list of files in sub directories
            $files = array ();
            $this->build_play_list($files, $this->location);

            // loop thru files and generate playlist
            $playlist = array ();
            foreach ($files as $path) {

                // winamp?
                if ($this->playlist_format == 'winamp') {

                    // filename without extension
                    $parts = pathinfo($path);
                    $name  = substr($parts['basename'], 0, strlen($parts['basename']) - strlen($parts['extension']));

                    // add false EXTINF for Winamp - will load faster
                    $playlist[] = "#EXTINF:0 $name\n";
                }

                // add playlist entry
                $playlist[] = $this->client_path($path);
            }
        }

        // send proper http headers
        header("Content-Type: audio/mpegurl");
        header("Connection: close");

        // winamp - write EXT M3U
        if ($this->playlist_format == 'winamp') {
            echo "#EXTM3U\n";
        }

        // output playlist
        echo implode("\n", $playlist);
        die();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Show Image //////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_image()
    {
        // get and send MIME type + content length
        list(,,$type) = @getimagesize($this->location);

        // invalid type?
        if ($type === false) {
            $this->fail("$this->location is not a valid image.");
        }

        // send headers
        header('Content-type: '   . image_type_to_mime_type($type));
        header('Content-length: ' . filesize($this->location));
        header('Last-Modified: '  . gmdate("D, d M Y H:i:s T", filemtime($this->location)));

        // send image data
        $fp = fopen($this->location, 'rb');
        fpassthru($fp);
        die();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Stream Audio File ////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stream()
    {
        // only stream single file
        if (!is_file($this->location)) {
            $this->bad_request();
        }

        // analyze to get mime_type
        $ext_info = $this->get_extended_file_info($this->analyze_file($this->location));
        
        // send proper http headers
        header('Content-Type: '   . $ext_info->mime_type);
        header('Content-length: ' . $ext_info->filesize);
        header('Last-Modified: '  . gmdate("D, d M Y H:i:s T", $ext_info->filemtime));
        
        // send file data
        $fp = fopen($this->location, 'rb');
        fpassthru($fp);
        die();
    }
    
    
    
    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics ///////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics', './');

        // general stats
        echo $g->h4('General Statistics');
        $t = new Table(2, "id='stats'", null, 'left;right;left');

        $t->data('Total number of tracks:');
        $t->data(number_format($s->number_of_tracks()));


        // filesize stats
        $t->row_attr("class='add_space'");
        $t->data('Total size of tracks:');
        $t->data($this->display_bytes_human_readable($s->size_of_tracks()));

        $t->data('Average track size:');
        $t->data($this->display_bytes_human_readable($s->average_size_of_tracks()));


        // playing time stats
        $t->row_attr("class='add_space'");
        $t->data('Total track length:');
        $t->data($this->display_seconds_as_days($s->length_of_tracks()));

        $t->data('Total track length:');
        $t->data($this->display_seconds_human_readable($s->length_of_tracks()));

        $t->data('Average track length:');
        $t->data($this->display_seconds_human_readable($s->average_length_of_tracks()));

        $t->done();


        // file formats stats
        echo $g->h4('File Formats');
        $t = new Table(5, "id='stats2'", null, 'left;right;right;right;right');

        foreach ($s->format_names_array() as $format_name_id => $format_name) {

            $t->data($format_name);

            $t->data(number_format($s->number_of_tracks($format_name_id)) . ' files');

            $t->data($this->display_bytes_human_readable($s->size_of_tracks($format_name_id)));

            $t->data($this->display_seconds_human_readable($s->length_of_tracks($format_name_id)));

            $t->data(number_format($s->average_bit_rate($format_name_id)/1000) . ' kbps');
        }

        $t->done();


        // reports
        echo $g->h4('Reports');
        $t = new Table(3, "id='stats3'");

        $t->data('File Formats/Encoders');
        $t->data();
        $t->data('[' . $g->a('./?action=stats_formats', 'view', "title='View report'") . ']');

        $t->data('Fields');
        $t->data();
        $t->data('[' . $g->a('./?action=stats_fields', 'view', "title='View report'") . ']');

        $t->data('Duplicate Files (md5_data)');
        $t->data();
        $t->data('[' . $g->a('./?action=stats_duplicates1&field=artist', 'view', "title='View report'") . ']');
        
        $t->data('Duplicate Files (metadata)');
        $t->data();
        $t->data('[' . $g->a('./?action=stats_duplicates2&field=artist', 'view', "title='View report'") . ']');

        $t->done();


        // problems
        echo $g->h4('Problems');
        $t = new Table(3, "id='stats4'");

        $t->data('Files not tagged');
        $t->data(number_format($s->count_tagless()) . ' items');
        $t->data('[' . $g->a('./?action=stats_tagless', 'view', "title='View report'") . ']');

        // missing key fields
        foreach ($this->key_fields() as $field) {
            $t->data('Files missing ' . $field . ' field');
            $t->data(number_format($s->count_missing_key_field($field)) . ' items');
            $t->data('[' . $g->a('./?action=stats_key_field&field='.$field, 'view', "title='View report'") . ']');
        }

        $t->data('Files missing replaygain info');
        $t->data(number_format($s->count_missing_replaygain()) . ' items');
        $t->data('[' . $g->a('./?action=stats_replaygain', 'view', "title='View report'") . ']');

        $t->data('Directories without artwork');
        $t->data(number_format($s->count_artless_directories()) . ' items');
        $t->data('[' . $g->a('./?action=stats_artless_dirs', 'view', "title='View report'") . ']');

        $t->done();

        $this->foot();
    }




    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - File Formats ////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_formats()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - File Formats/Encoders', './?action=stats');

        // output report
        $t = new table(3, "class='report'", null, 'left;left;right');

        foreach ($s->formats_data() as $format_name => $rows) {

            $t->data($format_name, "colspan='3' class='header'");

            foreach ($rows as $row) {

                list($encoder_version, $encoder_options, $count, $format_name_id, $encoder_version_id, $encoder_options_id) = $row;

                // generate uri to list files
                $uri = "./?action=stats_formats_list&format_name_id=$format_name_id&encoder_version_id=$encoder_version_id&encoder_options_id=$encoder_options_id";

                $t->data($g->a($uri, $encoder_version ? $encoder_version : '(unknown encoder)'));
                $t->data($g->a($uri, $encoder_options));
                $t->data(number_format($count));
            }

            $t->row_attr("class='add_space'");
        }

        $t->done();

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - File Formats - List of Files ////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_formats_list()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - File Formats/Encoders', './?action=stats_formats');

        list ($title, $data) = $s->formats_list_data($_GET['format_name_id'], $_GET['encoder_version_id'], $_GET['encoder_options_id']);

        echo $g->h2($title);

        $this->generate_file_view($data);

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - Fields //////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_fields()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - Fields', './?action=stats');

        $t = new table(4, "class='report'");

        $t->data('field', "class='header'");
        $t->data('#used', "class='header'");
        $t->data();
        $t->data();

        foreach ($s->fields_data() as $field => $count) {

            $t->data($field);
            $t->data(number_format($count));
            $t->data('[' . $g->a("./?action=stats_field_dist&field=$field", 'distribution') . ']');
            $t->data('[' . $g->a("./?action=stats_field_files&field=$field", 'files')  . ']');
        }

        $t->done();

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - Field Distrubution //////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_field_dist()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - Field Distribution', './?action=stats_fields');

        echo $g->h2($this->field);

        // links to order by
        echo $g->p(
            '[' . $g->a('./?action=stats_field_dist&field='.$this->field.'&sort_by=alpha', 'order alphabetically', "class='uline'") . '] ' .
            '[' . $g->a('./?action=stats_field_dist&field='.$this->field.'&sort_by=playt', 'order by playtime')                     . '] ' .
            '[' . $g->a('./?action=stats_field_dist&field='.$this->field.'&sort_by=count', 'order by filecount')                    . ']',
        "class='order_by'");


        switch (@$_GET['sort_by']) {

            case 'alpha':
                $data = $s->field_distribution_data($this->field, 'field');
                uksort($data, 'strcoll');   // sort data using strcoll insteaf of mysql order by
                break;

            case 'count':
                $data = $s->field_distribution_data($this->field, 'cnt desc, field');
                break;

            case 'playt':
            default:
                $data = $s->field_distribution_data($this->field, 'sum_playtime desc, field');
                break;
        }

        $t = new table(3, "class='report'");

        $t->data('value',    "class='header'");
        $t->data('playtime', "class='header'");
        $t->data('#files',   "class='header'");

        foreach ($data as $name => $data) {

            list ($playtime, $count) = $data;

            $t->data($g->a("./?action=search_results&field=$this->field&value=".rawurlencode($name),$name));
            $t->data($this->display_seconds_human_readable($playtime), "align='right'");
            $t->data(number_format($count), "align='right'");
        }

        $t->done();

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - Field Files /////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_field_files()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - Fields', './?action=stats_fields');

        echo $g->h2($this->field);

        $this->generate_file_view($s->field_files_data($this->field));

        $this->foot();
    }





    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - Duplicate Files (md5_data) //////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_duplicates1()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - Duplicate Files (md5_data)', './?action=stats');
        
        $duplicates = $s->duplicate_files_md5data_data();
        if (!$duplicates) {
            echo $g->p('No duplicates by md5_sum found.');
        }
        else {

            foreach ($duplicates as $hash => $data) {
                echo $g->h4($hash);
                $this->generate_file_view_simple($data);
            }
        }
        
        $this->foot();
    }




    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - Duplicate Files (metadata) //////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_duplicates2()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - Duplicate Files (metadata)', './?action=stats');

        echo $g->p('This only works on files tagged properly with artist and title.');
        
        $duplicates = $s->duplicate_files_metadata_data();
        if (!$duplicates) {
            echo $g->p('No duplicates by metadata found.');
        }
        else {
            foreach ($duplicates as $artist => $l2) {
                foreach ($l2 as $title => $data) {
                    echo $g->h4($artist . ' - ' . $title);
                    $this->generate_file_view_simple($data);
                }
            }
        }
        
        $this->foot();
    }


    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - Tagless Files ///////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_tagless()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - Tagless Files', './?action=stats');

        $this->generate_file_view($s->tagless_data());

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - Missing Key Fields //////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_key_field()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - Missing Key Field: ' . Ucfirst($this->field), './?action=stats');

        $this->generate_file_view($s->missing_key_field_data($this->field));

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - Files Missing Replaygain Information ////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_replaygain()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - Files Missing Replaygain Information', './?action=stats');

        $this->generate_file_view($s->missing_replaygain_data());

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Statistics - Directories Without Artwork /////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_stats_artless_dirs()
    {
        $g = new xml_gen;
        $s = new morg_stats($this->dbh);

        $this->head('Statistics - Directories Without Artwork', './?action=stats');

        $this->generate_file_view($s->artless_directories_data());

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Search Form //////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_search()
    {
        $g = new xml_gen;

        // get values for collation select
        $collations['utf8_general_ci'] = 'Case Insensitive (General)';
        $this->dbh->query('select * from morg_collation where collation!="utf8_general_ci" group by collation order by description');
        while ($this->dbh->next_record()) {
            $collations[$this->dbh->f('collation')] = 'Case Insensitive (' . $this->dbh->f('description') . ')';
        }
        $collations['utf8_bin'] = 'Case Sensitive';

        $this->head("Search Metadata", "./");

        // output search form
        $f = new form('./?action=search_results');
        $f->values(unserialize(@$_COOKIE['search']));

        $t = new table(1, "class='form'");

        $t->data('File Name:', "class='header_first'");
        $t->data();
        $f->text('filename');
        $f->focus();
        echo $g->img('images/tip.png', $this->tool_tips->create('Search any part of full filename (ignores Match selection).'));

        $t->data('Metadata:', "class='header'");

        $t->data('Artist:');
        $t->data();
        $f->text('artist');
        echo $g->img('images/tip.png', $this->tool_tips->create('Search metadata fields: artist, performer and ensemble.'));

        $t->data('Album:');
        $t->data();
        $f->text('album');
        echo $g->img('images/tip.png', $this->tool_tips->create('Search metadata field: album.'));

        $t->data('Title:');
        $t->data();
        $f->text('title');
        echo $g->img('images/tip.png', $this->tool_tips->create('Search metadata field: title.'));

        $t->data('Year/date:');
        $t->data();
        $f->text('date');
        echo $g->img('images/tip.png', $this->tool_tips->create('Search metadata fields: date, year.'));

        $t->data('Other:');
        $t->data();
        $f->text('other');
        echo $g->img('images/tip.png', $this->tool_tips->create('Search all metadata fields except: artist, performer, ensemble, album, title, date, year'));

        $t->data('Custom:');
        $t->data();
        $f->text("field", "id='custom1'");
        $f->text("value", "id='custom2'");
        echo $g->img('images/tip.png', $this->tool_tips->create('Search specific metadata field.'));

        $t->data('Match:', "class='header'");
        $t->data();
        $f->select('match', array (1 => 'Any part of words', 2 => 'Start or end of words', 3 => 'Whole words only', 4 => 'Exact matches only'));
        echo $g->img('images/tip.png', $this->tool_tips->create('How to match search phrases.'));

        $t->data('Method:', "class='header'");
        $t->data();
        $f->select('collation', $collations);
        echo $g->img('images/tip.png', $this->tool_tips->create(utf8_encode('The different language options for case insitive search makes character substitions relevant to that language, e.g. ß=ss in German. General should suffice for languages not listed specifically, English, French, etc.')));

        $t->data(null, "class='submit'");
        $f->submit('Search');

        $t->done();
        $f->done();

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Search Results ///////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_search_results()
    {
        // save search phrases until user closes browser
        setcookie('search', serialize(@$_POST));

        // add magic quotes to all values in $_REQUEST
        if (!get_magic_quotes_gpc()) {
            foreach ($_REQUEST as $key => $value) {
                $_REQUEST[$key] = addslashes($value);
            }
        }

        // subquery sql
        $ssql = "
            select
                getid3_comment.file_id
            from
                getid3_field,
                getid3_value,
                getid3_comment
            where
                getid3_comment.field_id = getid3_field.id and 
                getid3_comment.value_id = getid3_value.id
        ";

        // collate all comparisons with this statement
        $collate = @$_REQUEST['collation'] ? $_REQUEST['collation'] : 'utf8_bin';

        // generate sql/where statements
        $where = $where_match = array ();

        if (@$_REQUEST['filename']) {
            // always search any part of filename
            $where[] = "(concat(getid3_directory.filename, '/', getid3_file.filename) like '%$_REQUEST[filename]%' collate $collate)";
        }

        if (@$_REQUEST['artist']) {
            $where_match[] = array ($_REQUEST['artist'], true, array ('artist', 'performer', 'ensemble'));
        }

        if (@$_REQUEST['album']) {
            $where_match[] = array ($_REQUEST['album'], true, array ('album'));
        }

        if (@$_REQUEST['title']) {
            $where_match[] = array ($_REQUEST['title'], true, array ('title'));
        }

        if (@$_REQUEST['date']) {
            $where_match[] = array ($_REQUEST['date'], true, array ('date', 'year'));
        }

        if (@$_REQUEST['other']) {
            $where_match[] = array ($_REQUEST['other'], false, array ('artist', 'performer', 'ensemble', 'album', 'title', 'date', 'year'));
        }

        if (@$_REQUEST['field']  &&  @$_REQUEST['value']) {
            $where_match[] = array ($_REQUEST['value'], true, array ($_REQUEST['field']));
        }
        
        if (!$where && !$where_match) {
            $this->redirect('./?action=search');
        }

        // loop thru where_match and generate where statements
        foreach ($where_match as $info) {

            list ($value, $flag_allowed, $fields) = $info;

            switch ($_REQUEST['match']) {

                // match any part of word
                case 1:
                    $like_match = "(getid3_value.name like '%$value%' collate $collate)";
                    break;

                // start or end of words
                case 2:
                    $like_match = "((concat(' ', getid3_value.name) like '% $value%' collate $collate) or (concat(getid3_value.name, ' ') like '%$value %' collate $collate))";
                    break;

                // whole words only
                case 3:
                    $like_match = "(concat(' ', getid3_value.name, ' ') like '% $value %' collate $collate)";
                    break;

                // exact matches only
                case 4:
                default:
                    $like_match = "getid3_value.name = '$value' collate $collate";
                    break;
            }

            // convert $where_match into $where
            $not = $flag_allowed ? '' : 'not';
            $where[] = "getid3_file.id in (" . $ssql . "and getid3_field.name $not in ('" . implode("', '", $fields) . "') and $like_match )";
        }

        // perform search
        $this->dbh->query("
            select 
                getid3_file.id, 
                concat(getid3_directory.filename, '/', getid3_file.filename) as filename
            from 
                getid3_file,
                getid3_directory
            where 
                getid3_file.directory_id = getid3_directory.id and 
                getid3_file.playtime > 0 and " .
                implode(' and ', $where)
        );

        $data = array ();

        while ($this->dbh->next_record()) {
            $data[$this->dbh->f('id')] = $this->dbh->f('filename');
        }
        
        $g = new xml_gen;

        $this->head("Search Results", "./");

        // no results?
        if (!$data) {
            echo $g->p('No audio files matched your criteria.');
        }

        // view result
        else {
            $this->generate_file_view($data);
        }

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Action: Config ///////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    protected function action_config()
    {
        //// login
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && @$_POST['password']) {

            setcookie('hash', md5($_POST['password']));
            $this->redirect('./?action=config');
        }
        

        //// save configuration and redirect to toc
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // add/strip slashes to text fields
            if (!get_magic_quotes_gpc()) {
                $_POST['roots']           = addslashes($_POST['roots']);
                $_POST['client_params']   = addslashes($_POST['client_params']);
                $_POST['category_prefix'] = addslashes($_POST['category_prefix']);
                $_POST['key_fields']      = addslashes($_POST['key_fields']);
                $_POST['hidden_fields']   = addslashes($_POST['hidden_fields']);
            }
            
            // convert checkboxes to yes/no
            for ($i = 1; $i <= 7; $i++) {
                $_POST['assume'.$i] = @$_POST['assume'.$i] ? 'yes' : 'no';
            }

            // begin transaction
            $this->begin();

            // update morg_config
            $this->dbh->query("
                update
                    morg_config
                set
                    server_cp         = '$_POST[server_cp]',
                    client_cp         = '$_POST[client_cp]',
                    client_type       = '$_POST[client_type]',
                    client_params     = '$_POST[client_params]',
                    locale            = '$_POST[locale]',
                    playlist_format   = '$_POST[playlist_format]',
                    toc_columns       = '$_POST[toc_columns]',
                    browser_behaviour = '$_POST[browser_behaviour]',
                    assume1           = '$_POST[assume1]',
                    assume2           = '$_POST[assume2]',
                    assume3           = '$_POST[assume3]',
                    assume4           = '$_POST[assume4]',
                    assume5           = '$_POST[assume5]',
                    assume6           = '$_POST[assume6]',
                    assume7           = '$_POST[assume7]',
                    category_prefix   = '$_POST[category_prefix]'
            ");

            // change config password
            if (@$_POST['set_password']) {

                // store hash instead of password
                $password = md5($_POST['set_password']);
                $this->dbh->query("update morg_config set password='$password'");
            }
            
            // flag all roots for deletion
            $this->dbh->query("update morg_config_root set flag = 'delete'");

            // check and insert roots
            foreach (explode("\n", $_POST['roots']) as $root) {
                
                // remove whitespace from user and possible \r from browser
                $root = trim($root);
                
                // convert user specified directory to unix style
                $root = str_replace('\\\\', '/', str_replace(':', '|', $root));     // using \\\\ caused by addslashes()
                
                // remove trailing slash
                $root = ereg_replace('/$', '', $root);
                
                // check for directory existance
                if (!is_dir(stripslashes($root))) {
                    $this->fail("Directory $root does not exists.");
                }
                
                // check whether directory is readable
                if (!is_readable(stripslashes($root))) {
                    $this->fail("Directory $root is not readable.");
                }
                
                // exitst? reset flag 
                $this->dbh->query("update morg_config_root set flag = 'none' where server_path = '$root'");
                
                // new root, add
                if (!$this->dbh->affected_rows()) {
                    $this->dbh->query("insert into morg_config_root (server_path) values ('$root')");
                }
            }
            
            // delete all roots flagged for deletion (will cascade into getid3_directory and further into getid3_file)
            $this->dbh->query("delete from morg_config_root where flag = 'delete'");
            
            // delete all entries in morg_config_*
            $this->dbh->query('delete from morg_config_key_fields');
            $this->dbh->query('delete from morg_config_hidden_fields');
            $this->dbh->query('delete from morg_config_auth');

            // insert values in morg_config_*_fields
            foreach (explode("\n", $_POST['key_fields']) as $field) {
                $this->dbh->query("replace into morg_config_key_fields (name) values ('" . strtolower(trim($field)) . "')");
            }
            foreach (explode("\n", $_POST['hidden_fields']) as $field) {
                $this->dbh->query("replace into morg_config_hidden_fields (name) values ('" . strtolower(trim($field)) . "')");
            }

            // check and insert ips/ip ranges
            foreach (explode("\n", $_POST['auth_ips']) as $ip_) {
                
                // skip whitespace
                if (!$ip_ = trim($ip_)) {
                    continue;
                }
                
                // check
                if (!preg_match("/^([0-9]+)\.([0-9]+)\.([0-9]+|\*)\.([0-9]+|\*)$/", $ip_, $r) || $r[1] > 255 || $r[2] > 255 || ($r[3] != "*" && $r[3] > 255) || ($r[4] != "*" && $r[4] > 255)) {
                    $this->fail("Invalid value in authorization 'Restrict user access to these ip number/ranges': $ip_");
                }

                // insert/replace
                $this->dbh->query("replace into morg_config_auth (range) values ('$ip_')");
            }

            // commit and redirect to TOC
            $this->commit() and $this->redirect('./');
        }


        //// check/ask for password
        if ($this->password && ($this->password != @$_COOKIE['hash'])) {

            // form
            $this->head("Configuration", "./");

            $g = new xml_gen;

            echo $g->h4('You will remained logged in until browser is closed.');
            echo $g->br();

            $f = new form('./?action=config');
            $t = new table(1, "class='form'");

            $t->data('Enter Configuration Password:');
            $t->data();
            $f->password('password', "style='width: 210px; margin-right: 16px'");
            $f->focus();

            $t->data(null, "class='submit'");
            $f->Submit('Login');

            $t->done();
            $f->done();

            $this->foot();
            die();
        }


        //// config form

        // set default values
        $config = new stdClass;
        $config->server_cp         = $this->server_cp;
        $config->client_cp         = $this->client_cp;
        $config->client_type       = $this->client_type;
        $config->client_params     = $this->client_params;
        $config->locale            = $this->locale;
        $config->playlist_format   = $this->playlist_format;
        $config->toc_columns       = $this->toc_columns;
        $config->browser_behaviour = $this->browser_behaviour;
        $config->assume1           = $this->assume1 == 'yes' ? true : false;
        $config->assume2           = $this->assume2 == 'yes' ? true : false;
        $config->assume3           = $this->assume3 == 'yes' ? true : false;
        $config->assume4           = $this->assume4 == 'yes' ? true : false;
        $config->assume5           = $this->assume5 == 'yes' ? true : false;
        $config->assume6           = $this->assume6 == 'yes' ? true : false;
        $config->assume7           = $this->assume7 == 'yes' ? true : false;
        $config->category_prefix   = $this->category_prefix;

        $config->roots             = implode("\n", $this->roots());
        $config->key_fields        = implode("\n", $this->key_fields());
        $config->hidden_fields     = implode("\n", $this->hidden_fields());
        $config->auth_ips          = implode("\n", $this->auth_ips());

        // get select values for charsets 
        $this->dbh->query('select * from morg_charset');
        while ($this->dbh->next_record()) {
            $charsets[$this->dbh->f('charset')] = $this->dbh->f('charset') . ' - ' . $this->dbh->f('description');
        }
        $this->dbh->query('select * from morg_locale order by description');
        while ($this->dbh->next_record()) {
            $locales[$this->dbh->f('locale')] = $this->dbh->f('description');
        }

        // sort charset naturally
        uksort($charsets, "strnatcasecmp");


        // form
        $this->head("Configuration", "./");

        $g = new xml_gen;

        $f = new form('./?action=config');
        $f->values($config);

        $t = new table(1, "class='form'");

        $t->data('MORG Server:', "class='header_first'");

        $t->data('Directories containing audio files:');
        $t->data();
        $f->textarea('roots');
        $f->focus();
        echo $g->img('images/tip.png', $this->tool_tips->create('Directories containing audio files, one per line. These directories will be scanned recursively. The web server must have read access to these files (web server does not have access to network drives under Windows). Note: Windows filenames will be converted to UNIX style, i.e. c:\music becomes c:/music'));
        
        $t->data('Codepage');
        $t->data();
        $f->select('server_cp', $charsets);
        echo $g->img('images/tip.png', $this->tool_tips->create('Select charset used by server file system. This selection must match reality, if you select the wrong charset, non-English characters may not display correctly.'));


        $t->data('Client (player):', "class='header'");

        $t->data('Codepage:');
        $t->data();
        $f->select('client_cp', $charsets);
        echo $g->img('images/tip.png', $this->tool_tips->create('Select charset used by client file system. This selection must match reality, if you select the wrong charset, non-English characters may not display correctly.'));
        
        $t->data('Type:');
        $t->data();
        $f->select('client_type', array ('local_fs' => 'Local file system (client = server)', 'http' => 'Stream via http', 'lan_windows' => 'Windows LAN', 'lan_unix' => 'UNIX LAN'));
        echo $g->img('images/tip.png', $this->tool_tips->create('Select <i>Local file system</i> if server and client is the same machine.<br><br><i>Stream via http</i> is a network solution that works with most players.<br>Problems with tags and seeking can occur.<br><br>For network solutions via samba/nfs select <i>Windows/UNIX LAN</i>.<br>Remember to set parameters below. If server sees files as /bulk/music <br>and server sees files as m:\\, then add the param /bulk/music=m:\\'));

        $t->data('LAN Parameters:');
        $t->data();
        $f->textarea('client_params');
        echo $g->img('images/tip.png', $this->tool_tips->create('See tooltip above.'));

        $t->data('Locale:', "class='header'");
        $t->data();
        $f->select('locale', $locales);
        echo $g->img('images/tip.png', $this->tool_tips->create('Select desired locale. This will affect the sortorder in MORG.'));


        $t->data('Playlist Format:', "class='header'");
        $t->data();
        $f->select('playlist_format', array ('m3u' => 'Standard m3u', 'foobar2000' => 'Foobar2000', 'winamp' => 'Winamp'));
        echo $g->img('images/tip.png', $this->tool_tips->create('Select playlist format. The Foobar2000 selection will add 2 seconds of silence between albums. If this behaviour in unwanted, select Standard m3u instead.'));


        $t->data('Table of Contents:', "class='header'");
        $t->data();
        $f->select('toc_columns', array (1 => '1 column', 2 => '2 columns', 3 => '3 columns', 4 => '4 columns', 5 => '5 columns', 6 => '6 columns', 7 => '7 columns', 8 => '8 columns'));
        echo $g->img('images/tip.png', $this->tool_tips->create('Select desired number of columns in Table of Contents.'));


        $t->data('Browser Behaviour:', "class='header'");
        $t->data();
        $f->select('browser_behaviour', array ('standard' => 'Standard', 'maximize' => 'Maximize Browser Window'));
        echo $g->img('images/tip.png', $this->tool_tips->create('Standard does nothing. Maximize Browser Window should be self-explanatory.'));


        $t->data('Filenames Containing Underscores:', "class='header'");
        $t->data();
        $f->checkbox('assume1', 'Assume _word_ is "word"');
        $t->data();
        $f->checkbox('assume2', 'Assume _title_ is "title"');
        $t->data();
        $f->checkbox('assume4', 'Assume title_ is title?');
        $t->data();
        $f->checkbox('assume3', 'Assume _...  is ?... ');
        $t->data();
        $f->checkbox('assume5', 'Assume space_space is /');
        $t->data();
        $f->checkbox('assume6', 'Assume something_space is something:');
        $t->data();
        $f->checkbox('assume7', 'Assume remaining _ are /');


        $t->data('Category Prefix:', "class='header'");
        $t->data();
        $f->text('category_prefix');
        $f->validate_string(1, 1, 'Catgory Prefix must be one character.');
        echo $g->img('images/tip.png', $this->tool_tips->create('Select prefix character for categories, e.g. @Soundtracks. Categories are listed at the very top in the table of contents.'));


        $t->data('Metadata:', "class='header'");

        $t->data('Key Fields:');
        $t->data();
        $f->textarea('key_fields');
        echo $g->img('images/tip.png', $this->tool_tips->create('Key fields are fields that should always be present in tags. This selection affects available reports under Statistics.'));

        $t->data('Hidden Fields:');
        $t->data();
        $f->textarea('hidden_fields');
        echo $g->img('images/tip.png', $this->tool_tips->create('Hidden fields will not be shown in tool tips.'));


        $t->data('Security:', "class='header'");

        $t->data('New Configuration Password:');
        $t->data();
        $f->text('set_password');
        echo $g->img('images/tip.png', $this->tool_tips->create('Password to protect the configuration. Once set it cannot be removed - empty = leave unchanged.'));

        $t->data('Restrict user access to these ip number/ranges:');
        $t->data();
        $f->textarea('auth_ips');
        echo $g->img('images/tip.png', $this->tool_tips->create('Enter ip numbers/ranges, one per line. Example of range: 192.168.*.*'));


        $t->data(null, "class='submit'");
        $f->submit(' Save ');

        $t->done();
        $f->done();

        $this->foot();
    }



    ///////////////////////////////////////////////////////////////////////////
    //// Layout ///////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////


    /**
    * Common XHTML header
    *
    * @param    string      title       Title
    * @param    string      up          Uri to up button or false
    * @param    string      play        Uri to play button or false
    * @param    bool        sort        Sort buttons active
    * @param    bool        numbers     Show numbers button active
    */

    protected function head($title, $up = false, $play = false, $sort = false, $numbers = false)
    {
        echo parent::head($title);

        // initialize tool tips
        $this->tool_tips = new tool_tips;

        $g = new xml_gen;

        // generate menu xml

        // up button
        $menu_xml[] = $up ? $g->a_img($up, 'images/up.png', "alt='Up one level' title='Up one level'", 'images/up-c.png') : $g->img('images/up-g.png');

        // play button
        $menu_xml[] = $play ? $g->a_img("./?action=play&location=$play", 'images/play.png', "alt='Play all tracks' title='Play all tracks'", 'images/play-c.png') : $g->img('images/play-g.png');

        // spacer
        $menu_xml[] = $g->space();

        // search button
        $menu_xml[] = $g->a_img("./?action=search", 'images/search.png', "alt='Search Metadata' title='Search Metadata'", 'images/search-c.png');

        // stats button
        $menu_xml[] = $g->a_img("./?action=stats", 'images/stats.png', "alt='Statictics' title='Statictics'", 'images/stats-c.png');

        // config button
        $menu_xml[] = $g->a_img('./?action=config', 'images/config.png', "alt='Configuration' title='Configuration'", 'images/config-c.png');

        // all music link artist search
        $amg_link = ($this->action != 'toc') ? "javascript:document.forms.AMG.submit()" : "http://www.allmusic.com/";
        $menu_xml[] = $g->a_img($amg_link, 'images/amg.png', "alt='All Music Guide' title='All Music Guide'", 'images/amg-c.png');

        // google image search
        $goo_link = ($this->action != 'toc') ? "javascript:google()" : "http://www.google.com/";
        $menu_xml[] = $g->a_img($goo_link, 'images/google.png', "alt='Google Image Search' title='Google Image Search'", 'images/google-c.png');

        // spacer
        $menu_xml[] = $g->space();

        // numbers button
        $img = @$_COOKIE['show_numbers'] ? 'numb' : 'num0';
        $menu_xml[] = $numbers ? $g->a_img($_SERVER['REQUEST_URI'].'&set_show_numbers=1',  "images/$img.png", "alt='Toggle track numbers' title='Toggle track numbers'", "images/$img-c.png") : $g->img("images/$img-g.png");

        // playing time button
        $img = @$_COOKIE['show_playtime'] ? 'time' : 'time0';
        $menu_xml[] = $sort ? $g->a_img($_SERVER['REQUEST_URI'].'&set_show_playtime=1', "images/$img.png", "alt='Toggle playing time' title='Toggle playing time'", "images/$img-c.png") : $g->img("images/$img-g.png");

        // sort alpha button
        $img = $this->get_sort_mode();
        $menu_xml[] = $sort ? $g->a_img($_SERVER['REQUEST_URI'].'&set_sort_alpha=1', "images/$img.png", "alt='Toggle sort moder' title='Toggle sort mode'", "images/$img-c.png") : $g->img("images/$img-g.png");

        // sort desc button
        $img = $this->get_sort_direction();
        $menu_xml[] = $sort ? $g->a_img($_SERVER['REQUEST_URI']."&set_sort_mode=1", "images/$img.png", "alt='Toggle sort direction' title='Toggle sort direction'", "images/$img-c.png") : $g->img("images/$img-g.png");

        // output menu and title in main table
        $this->t = new table(2, "id='main'");

        $this->t->data(null, "id='menu'");
        $t = new table(1);
        foreach ($menu_xml as $xml) {
            $t->data($xml);
        }
        $t->done();

        $this->t->data($g->h1($title), "id='body'");

        // all music guide code
        $search_string = ereg("^[#@!\+]?([^:]+)", $title, $regs) ? $regs[1] : "";   // first part of title - split by :, remove special chars
        echo "
            <form action='http://www.allmusic.com/cg/amg.dll' method='post' target='AMG' name='AMG'>
            <input type='hidden' name='P'   value='amg' />
            <input type='hidden' name='uid' value='SEARCH' />
            <input type='hidden' name='sql' value=\"$search_string\" />
            <input type='hidden' id='buttons' name='opt1' value='1' />
            </form>
        ";

        // google image search code
        $search_string = urlencode(utf8_decode(str_replace(":", '', $title)));
        echo "
            <script type='text/javascript'>
            <!--
            function google()
            {
                window.open('http://images.google.com/images?as_q=$search_string&svnum=10&hl=en&imgsz=medium|large|xlarge&safe=off',  'google2', 'resizable,scrollbars,menubar,status,toolbar,location')
                window.open('http://images.google.com/images?as_q=$search_string&svnum=10&hl=en&imgsz=xxlarge&safe=off',              'google1', 'resizable,scrollbars,menubar,status,toolbar,location')
            }
            //-->
            </script>
        ";

        // maximize browser window?
        if ($this->browser_behaviour == 'maximize') {
            echo "
                <script type='text/javascript'>
                <!--
                self.moveTo(0,0);
                self.resizeTo(screen.availWidth, screen.availHeight);
                // -->
                </script>
            ";
        }
    }



    /**
    * Common XHTML footer
    */

    protected function foot()
    {
        // finaluse main table
        $this->t->done();

        // finalise tool tips
        $this->tool_tips->done();

        echo parent::foot();
    }



    /**
    * Generate XML for file view
    *
    * @param    array of int => string     files       getid3.id => getid3.filename
    */

    function generate_file_view($files)
    {
        $g = new xml_gen;
        
        // sort by filename without root (may yield strange results with multiple roots)
        uasort($files, 'strcoll');

        // init array storing old values
        $old = '';
        
        // loop thru files
        foreach ($files as $id => $filename) {
            
            // skip files deleted since update ran last time
            if (!file_exists($filename)) {
                continue;
            }
    
            // get directory and filename
            $path_info = pathinfo($filename);
            
            // get root directory (within roots)
            $root = $this->get_root($filename);
    
            // changing directory
            if (@$old != $path_info['dirname']) {
                
                // save current as old
                $old = $path_info['dirname'];
                
                // split dirname by /
                $parts = explode('/', $this->remove_root($path_info['dirname']));
                
                // init 
                $header    = '';
                $directory = $root;
                
                // loop thru parts (directories) and add to hlinks
                for ($i = 1; $i < sizeof($parts); $i++) {
                    
                    // build directory string
                    $directory .= '/' . $parts[$i];
                    
                    // convert directory from UTF-8 (database) to server-side codepage
                    $path = iconv('UTF-8', $this->server_cp, $directory);
                    
                    // add link to header string
                    $header .= $g->a('./?action=browse&location='.urlencode($path), $parts[$i]) . ' -> ';
                }
                
                // output header
                echo $g->h4($header);
            }

            // increase filecount
            @$count++;

            // link to play filename and tooltip on first 200 entries (more tooltips may kill browser)
            $path = iconv('UTF-8', $this->server_cp, $filename);
            echo $g->p($g->a('./?action=play&location='.urlencode($path), $path_info['basename'], $count > 200 ? null : $this->generate_tooltip_xml($this->get_extended_file_info($id))));
        }
    }



    /**
    * Generate XML for simple file view
    *
    * @param    array of int => string     files       getid3.id => getid3.filename
    */

    function generate_file_view_simple($files)
    {
        $g = new xml_gen;
        
        // sort by filename without root (may yield strange results with multiple roots)
        uasort($files, 'strcoll');

        // loop thru files
        foreach ($files as $id => $filename) {
    
            // increase filecount
            @$count++;

            // link to play filename and tooltip on first 50 entries (more tooltips may kill browser)
            $path = iconv('UTF-8', $this->server_cp, $filename);
            echo $g->p($g->a('./?action=play&location='.urlencode($path), $filename, $count > 50 ? null : $this->generate_tooltip_xml($this->get_extended_file_info($id))));
        }
    }



    /**
    * Generate XML for tooltips
    *
    * @param    array     info           from get_extended_file_info()
    */

    function generate_tooltip_xml($info)
    {
        static $order = array (
                            array ('genre'),
                            array ('title', 'artist', 'album', 'part', 'opus', 'tracknumber', 'track', 'discnumber', 'label'),
                            array ('date', 'year', 'released', 'version', 'sourcemedia'),
                            array ('location', 'language'),
                            array ('composer', 'lyricist', 'author', 'conductor', 'ensemble', 'arranger', 'performer'),
                            array ('comment'),
                            array ('backup'),
                            array ('*')
                        );

        $g = new xml_gen;

        // no proper extended info found?
        if (empty($info->format_name)) {
            return;
        }

        // generate xml for format infomation
        $xml  = $g->b($info->format_name) . ($info->encoder_version ? '; ' . $info->encoder_version : '') . $g->br();

        // ... technical information about track
        $xml .= number_format($info->sample_rate/1000) . 'k/' . $info->bits_per_sample . '/' . $info->channels . '; ';
        $xml .= number_format($info->avg_bit_rate/1000) . ' kbps' . ($info->bitrate_mode ? '(' . $info->bitrate_mode . ')' : '') . '; ';
        $xml .= morg_gui::number_format_significant_ciphers($info->filesize/1024/1024, 2) . ' Mb; ';
        $xml .= $this->display_seconds_human_readable($info->playtime);


        // metadata
        if (!empty($info->comments)) {

            // remove hidden fields
            foreach ($this->hidden_fields() as $field) {
                if (isset($info->comments[$field])) {
                    unset($info->comments[$field]);
                }
            }

            // sort 'date'
            if (isset($info->comments['date'])) {
                sort($info->comments['date']);
            }

            // begin comments table
            $xml .= "<table cellpadding='0' cellspacing='0' border='0' class='meta'>";

            // loop thru tooltips groups
            foreach ($order as $fields) {

                // reset addline flag
                $add_line = false;

                // loop thru fields in group
                foreach ($fields as $field) {

                    // does field exists in comments?
                    if ($field != "*" && !empty($info->comments[$field])) {

                        // extract values
                        $key    = $field;
                        $values = $info->comments[$field];

                        // if multiple values, make pluralis of $key
                        if (sizeof($values) >= 2 && ereg("s$", $key)) {
                            $key = "${key}es";
                        }
                        elseif (sizeof($values) >= 2) {
                            $key = "${key}s";
                        }

                        // unpack values
                        $values = implode($g->br(), $values);

                        // add row
                        $xml .= "<tr><td valign='top'>$key</td><td>$values</td></tr>";

                        // remove field from comment
                        unset($info->comments[$field]);

                        // set addline flag
                        $add_line = true;
                    }

                    // remaining fields outside ordered groups
                    elseif ($field == '*') {

                        // loop thru remaining comments
                        foreach ($info->comments as $key => $values) {

                            // if multiple values, make pluralis of $key
                            if (sizeof($values) >= 2 && ereg("s$", $key)) {
                                $key = "${key}es";
                            }
                            elseif (sizeof($values) >= 2) {
                                $key = "${key}s";
                            }

                            // unpack values
                            $values = implode("<br>", $values);

                            // add row
                            $xml .= "<tr><td valign='top'>$key</td><td>$values</td></tr>";
                        }

                        // no line after last group
                        $add_line = false;
                    }
                }

                // add empty row
                if ($add_line && $info->comments) {
                    $xml .= "<tr><td colspan='2' style='font: 6px serif;'>&nbsp;</td></tr>";
                }
            }

            // finalise comments table
            $xml .= "</table>";
        }

        // create tooltip
        return $this->tool_tips->create($xml, 200);
    }




    ///////////////////////////////////////////////////////////////////////////
    //// Support //////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////


    /**
    * Handle fatal errors.
    */

    protected function fail($msg)
    {
        $this->rollback();
        
        $this->head("Fatal Error");
        echo xml_gen::p($msg);
        $this->foot();
    }



    /**
    * Scan directory.
    *
    * @return   string[][]      array (directories[], files[])
    */

    protected function scan_location($path)
    {
        // init
        $directories = $files = array ();

        // open directory
        $dir = opendir($path);

        // read directory
        while ($filename = readdir($dir)) {

            // found current or parent directory? or hidden file?
            if ($filename[0] == '.') {
                continue;
            }

            // convert filename to UTF-8 for sorting
            $sort_name = strtoupper(@iconv($this->server_cp, 'UTF-8', $filename));

            // convert filename to uppercase for sorting
            $sort_name = mb_strtoupper($sort_name, 'UTF-8');

            // found directory?
            if (is_dir("$path/$filename")  &&  $filename[0] != '.') {

                // perform regular expression match on filename to isolate year - i.e. (1976) or (1976b)
                if (preg_match(morg::directory_pattern, $filename, $regs)) {

                    // sort alphabetically
                    if ($this->get_sort_mode() == 'alpha') {
                        $sort_year = '00000';
                    }

                    // sort cronologically
                    else {
                        // append 'a' to 4 digit years
                        $sort_year = substr($regs[2].'a', 0, 5);
                    }

                    // add directory to array (full_path, name_without_year, year_without_letter)
                    $directories[$sort_year.$sort_name] = array ("$path/$filename", $regs[1], substr($regs[2], 0, 4));
                }

                // no year found - use 00000 for sorting instead
                else {

                    // default sort_year
                    $sort_year = '00000';

                    // sorting cronologically - asc2 or des2
                    if ($this->get_sort_direction() == 'des' || $this->get_sort_direction() == 'asc2') {
                        $sort_year = 'ZZZZZ';
                    }

                    // add directory to array (full_path, filename, null)
                    $directories[$sort_year.$sort_name] = array ("$path/$filename", $filename, null);
                }
            }

            // found file
            else {

                // perform regular expression match on file to isolate track number and file extention
                if (preg_match(morg::filename_pattern, $filename, $regs)) {

                    // match found - extract parts
                    $track = $regs[1];
                    $name  = $regs[2];
                    $ext   = $regs[3];

                    // sort by track number
                    $strack = 1000 + $track;

                    // add parts to array
                    $files[$strack . $sort_name] = array ("$path/$filename", $name, $track);
                }

                // strange filename - add entire name to array
                else {
                    $files['1000' . $sort_name] = array ("$path/$filename", $filename, 0);
                }
            }
        }
        closedir($dir);

        // sort $directories and $files arrays
        uksort($directories, 'strcoll');
        if (substr($this->get_sort_direction(), 0, 3) == 'des') {
            $directories = array_reverse($directories, true);
        }
        asort($files);

        // return arrays
        return array ($directories, $files);
    }



    /**
    * Return array of key fields
    */

    function key_fields()
    {
        $result = array ();

        $this->dbh->query('select * from morg_config_key_fields');
        while ($this->dbh->next_record()) {
            $result[] = $this->dbh->f('name');
        }

        return $result;
    }



    /**
    * Read array of hidden fields
    */

    function hidden_fields()
    {
        static $result;

        if (isset($result)) {
            return $result;
        }

        $result = array ();

        $this->dbh->query('select * from morg_config_hidden_fields');
        while ($this->dbh->next_record()) {
            $result[] = $this->dbh->f('name');
        }

        return $result;
    }



    /**
    * Return array of authorized ip numbers/ranges
    */

    function auth_ips()
    {
        $result = array ();

        $this->dbh->query('select * from morg_config_auth');
        while ($this->dbh->next_record()) {
            $result[] = $this->dbh->f('range');
        }

        return $result;
    }
    
    
    
    /**
    * Convert server path to client path
    */

    protected function client_path($path)
    {
        // ignore silence:// entries (foobar2000 specific)
        if (preg_match("/^silence:\/\//", $path)) {
            return $path;
        }
        
        if ($this->client_type == 'local_fs') {
            return $path;
        }
        
        if ($this->client_type == 'http') {
            return (@$_SERVER['https'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['PHP_SELF'] . '?action=stream&location='.urlencode($path);
        }
        
        
        // windows or unix lan
        if ($this->client_params) {
            foreach (explode("\n", $this->client_params) as $replace_string) {
                list ($src, $dst) = explode('=', $replace_string);
                $path = str_replace($src, $dst, $path);
            }
        }

        // convert unix slashes to windows slashes
        if ($this->client_type == 'lan_windows') {
            $path = str_replace('/', '\\', $path);
        }

        // convert to proper charset and return
        return @iconv($this->server_cp, $this->client_cp, $path);
    }



    /**
    * Remove root from path and return title in desired format
    */

    protected function get_title_from_path($path)
    {
        //// resolve title

        // remove root
        $title = $this->remove_root($path);

        // remove beginning /
        $title = preg_replace('/^\//', '', $title);

        // replace / with :
        $title = str_replace("/", ": ", $title);

        // remove year
        if (preg_match(morg::directory_pattern, $title, $regs)) {
            return $this->display_filename(trim($regs[1]));
        }

        return $this->display_filename($title);
    }



    /**
    * Get sort mode based on cookie-
    */

    protected function get_sort_mode()
    {
        return @$_COOKIE['sort_alpha'] ? 'alpha' : 'cron';
    }



    /**
    * Get sort direction based on cookies.
    */

    protected function get_sort_direction()
    {
        if ($this->get_sort_mode() == 'alpha') {
            $sort_orders = array (0 => 'asc', 1 => 'des', 2 => 'asc', 3 => 'des');
        }
        else {
            $sort_orders = array (0 => 'asc', 1 => 'des', 2 => 'asc2', 3 => 'des2');
        }
        return isset($_COOKIE['sort_mode']) ? $sort_orders[$_COOKIE['sort_mode']] : 'asc';
    }



    /**
    * Convert filename to UTF-8 displayable string.
    * Also make assumptions on the meaning of underscores and convert charsets.
    */

    protected function display_filename($filename)
    {
        // convert filename to UTF-8 (display)
        $result = @iconv($this->server_cp, 'UTF-8', $filename);

        // replace & with &amp;
        $result = str_replace('&', '&amp;', $result);

        // assume _word_ is "word"
        if ($this->assume1 == 'yes') {
            $result = eregi_replace(' _([a-z]+)_ ', '"\\1"', $result);
        }

        // assume _title_ is "title"
        if ($this->assume2 == 'yes') {
            $result = eregi_replace("^_(.+)_$", '"\\1"', $result);
        }

        // assume _...  is ?...
        if ($this->assume3 == 'yes') {
            $result = str_replace("_... ", "?... ", $result);
        }

        // assume title_ is title?
        if ($this->assume4 == 'yes') {
            $result = ereg_replace('_$', '?', $result);
        }

        // assume sp_sp is /
        if ($this->assume5 == 'yes') {
            $result = str_replace(' _ ', ' / ', $result);
        }

        // assume something_sp is :
        if ($this->assume6 == 'yes') {
            $result = str_replace('_ ', ': ',  $result);
        }

        // assume remaining _s are /, add space on each side
        if ($this->assume7 == 'yes') {
            $result = str_replace('_', ' / ',   $result);
        }

        return $result;
    }



    /**
    * Get playtime for directory.
    */

    protected function get_directory_playtime($path)
    {
        $path = addslashes(@iconv($this->server_cp, 'UTF-8', $path));

        $this->dbh->query("select sum(playtime) as playt from getid3_file where (filename like '$path/%')");
        $this->dbh->next_record();
        return $this->dbh->f('playt');
    }



    /**
    * Get extended file information from mysql and return in stdObject
    */

    protected function get_extended_file_info($file_id)
    {
        if (!(int)$file_id) {
            return;
        }

        // init result object
        $result = new stdClass;

        // get extended file info
        $this->dbh->query("
            select
                getid3_file.*,
                getid3_format_name.mime_type,
                getid3_format_name.name     as format_name,
                getid3_encoder_version.name as encoder_version,
                getid3_encoder_options.name as encoder_options,
                getid3_bitrate_mode.name    as bitrate_mode,
                getid3_channel_mode.name    as channel_mode

            from
                getid3_file,
                getid3_format_name,
                getid3_encoder_version,
                getid3_encoder_options,
                getid3_bitrate_mode,
                getid3_channel_mode

            where
                getid3_file.id                 = $file_id                  and 
                getid3_file.format_name_id     = getid3_format_name.id     and 
                getid3_file.encoder_version_id = getid3_encoder_version.id and 
                getid3_file.encoder_options_id = getid3_encoder_options.id and 
                getid3_file.bitrate_mode_id    = getid3_bitrate_mode.id    and 
                getid3_file.channel_mode_id    = getid3_channel_mode.id
        ");

        // add extended file into to result object (audio file)
        if ($this->dbh->next_record()) {

            $result->id                    = $file_id;
        	$result->filename              = $this->dbh->f('filename');
        	$result->filemtime             = $this->dbh->f('filemtime');
        	$result->filesize              = $this->dbh->f('filesize');
        	$result->mime_type             = $this->dbh->f('mime_type');
        	$result->format_name           = $this->dbh->f('format_name');
        	$result->format_name           = $this->dbh->f('format_name');
        	$result->encoder_version       = $this->dbh->f('encoder_version');
        	$result->encoder_options       = $this->dbh->f('encoder_options');
        	$result->bitrate_mode  	       = $this->dbh->f('bitrate_mode');
        	$result->channel_mode  	       = $this->dbh->f('channel_mode');
        	$result->sample_rate           = $this->dbh->f('sample_rate');
        	$result->bits_per_sample       = $this->dbh->f('bits_per_sample');
        	$result->channels              = $this->dbh->f('channels');
        	$result->lossless              = $this->dbh->f('lossless');
        	$result->playtime              = $this->dbh->f('playtime');
        	$result->avg_bit_rate          = $this->dbh->f('avg_bit_rate');
        	$result->replaygain_track_gain = $this->dbh->f('replaygain_track_gain');
        	$result->replaygain_album_gain = $this->dbh->f('replaygain_album_gain');

        	// assume 16 bps for stereo 44100 files (CDDA)
            if (!$result->bits_per_sample && $result->channels == 2 && $result->sample_rate == 44100) {
                $result->bits_per_sample = 16;
            }

            // get metadata
            $this->dbh->query("
                select
                    getid3_field.name as field,
                    getid3_value.name as value
                from
                    getid3_comment,
                    getid3_field,
                    getid3_value
                where
                    getid3_comment.file_id  = $file_id        and 
                    getid3_comment.field_id = getid3_field.id and 
                    getid3_comment.value_id = getid3_value.id
            ");

            // add metadata to result object
            while ($this->dbh->next_record()) {

                $result->comments[$this->dbh->f('field')][] = $this->dbh->f('value');
            }
        }

        // not an audio file
        else {

            // get basic info
            $this->dbh->query("
                select
                    getid3_file.id,
                    getid3_format_name.name as format_name

                from
                    getid3_file,
                    getid3_format_name

                where
                    getid3_file.id             = $file_id and 
                    getid3_file.format_name_id = getid3_format_name.id
            ");

            // add basic info to result object
            if ($this->dbh->next_record()) {
                $result->id          = $file_id;
            	$result->format_name = $this->dbh->f('format_name');
            }
        }

        return $result;
    }



    /**
    * Build playlist from direcory
    *
    * @return   array of string     Paths
    */

    protected function build_play_list(&$array, $path)
    {
        // read directory
        list($directories, $files) = $this->scan_location($path);

        // add directories recursively
        foreach ($directories as $info) {
            $this->build_play_list($array, $info[0]);
        }

        // loop thru files
        foreach ($files as $info) {

            // analyze file, and get extended info (playtime needed)
            try {
                $file_info = $this->get_extended_file_info($this->analyze_file($info[0]));
            }
            catch (Exception $e) {
                continue;
            }

            // add audio files only
            if ($file_info->playtime) {
                $array[] = $info[0];
            }
        }

        // foobar2000 - add 2 seconds silence between albums
        if ($this->playlist_format == 'foobar2000') {
            $array[] = "silence://2";
        }
    }



    /**
    * Return number of bytes in human readable format.
    */

    public static function display_bytes_human_readable($bytes, $significant_ciphers = 3)
    {
        $units = array ('kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');

        $unit = '';

        while ($bytes >= 1024) {

            $unit = array_shift($units);
            $bytes /= 1024;
        }

        return morg_gui::number_format_significant_ciphers($bytes, $significant_ciphers) . ' ' . $unit;
    }



    /**
    * Display number of seconds in human readable format.
    *
    * Returns string    - HHHH:MM:SS
    */

    public static function display_seconds_human_readable($seconds)
    {
        // hours  - 117:05:21   07:09:32     09:32    00:32
        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds - $hours*3600) / 60);
        $seconds = floor(round($seconds - $hours*3600 - $minutes*60));

        $result = str_pad($minutes, 2, "0", STR_PAD_LEFT) . ':' . str_pad($seconds, 2, "0", STR_PAD_LEFT);

        if ($hours) {
            $result = str_pad($hours, 2, "0", STR_PAD_LEFT) . ':' . $result;
        }

        return $result;
    }



    /**
    * Display number of seconds in human readable format.
    *
    * Returns string    - HHHH:MM:SS
    */

    public static function display_seconds_as_days($seconds, $significant_ciphers = 3)
    {
        // days  - 3.34 days        33,4 days       334 days
        if ($seconds >= 86400) {
            $days = $seconds/86400;
            return morg_gui::number_format_significant_ciphers($days, $significant_ciphers) . ' days';
        }
    }


    /**
    * Number format with significant ciphers.
    */

    public static function number_format_significant_ciphers($number, $significant_ciphers)
    {
        return number_format($number, max(0, $significant_ciphers - strlen(floor($number))));
    }


}



//// Support class for stats

// Note: This class could get better performance speed-wise with caching
// and by calculating average results instead of running queries.

class morg_stats
{

    protected $dbh;


    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }



    /**
    * Number of audio files, total or by format_name_id.
    */

    public function number_of_tracks($format_name_id = null)
    {
        $this->dbh->query("select count(*) as cnt from getid3_file where playtime > 0" . ($format_name_id ? ' and format_name_id='.$format_name_id : ''));
        $this->dbh->next_record();
        return $this->dbh->f('cnt');
    }



    /**
    * Size of audio files, total or by format_name_id.
    */

    public function size_of_tracks($format_name_id = null)
    {
        $this->dbh->query("select sum(filesize) as sum from getid3_file where playtime > 0" . ($format_name_id ? ' and format_name_id='.$format_name_id : ''));
        $this->dbh->next_record();
        return $this->dbh->f('sum');
    }



    /**
    * Average size of audio files, total or by format_name_id.
    */

    public function average_size_of_tracks($format_name_id = null)
    {
        $this->dbh->query("select avg(filesize) as avg from getid3_file where playtime > 0" . ($format_name_id ? ' and format_name_id='.$format_name_id : ''));
        $this->dbh->next_record();
        return $this->dbh->f('avg');
    }



    /**
    * Length (playtime) of audio files, total or by format_name_id.
    */

    public function length_of_tracks($format_name_id = null)
    {
        $this->dbh->query("select sum(playtime) as sum from getid3_file where playtime > 0" . ($format_name_id ? ' and format_name_id='.$format_name_id : ''));
        $this->dbh->next_record();
        return $this->dbh->f('sum');
    }



    /**
    * Average length (playtime) of audio files, total or by format_name_id.
    */

    public function average_length_of_tracks($format_name_id = null)
    {
        $this->dbh->query("select avg(playtime) as avg from getid3_file where playtime > 0" . ($format_name_id ? ' and format_name_id='.$format_name_id : ''));
        $this->dbh->next_record();
        return $this->dbh->f('avg');
    }



    /**
    * Average bit rate of audio files, total or by format_name_id.
    */

    public function average_bit_rate($format_name_id = null)
    {
        $this->dbh->query("select sum(playtime) as sum1, sum(avg_bit_rate*playtime) as sum2 from getid3_file where playtime > 0" . ($format_name_id ? ' and format_name_id='.$format_name_id : ''));
        $this->dbh->next_record();
        return $this->dbh->f('sum2') / $this->dbh->f('sum1');
    }



    /**
    * Array containing id and names of all audio file formats.
    */

    public function format_names_array()
    {
        $result = array ();

        $this->dbh->query("select getid3_format_name. * from getid3_file, getid3_format_name where getid3_file.format_name_id = getid3_format_name.id and playtime >0 group by id order by name");
        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('id')] = $this->dbh->f('name');
        }

        return $result;
    }



    /**
    * Array containing data for the File Format Report.
    */

    public function formats_data()
    {
        $result = array ();

        $this->dbh->query('
            select
                getid3_format_name.id       as format_name_id,
                getid3_format_name.name     as format_name,
                getid3_encoder_version.id   as encoder_version_id,
                getid3_encoder_version.name as encoder_version,
                getid3_encoder_options.id   as encoder_options_id,
                getid3_encoder_options.name as encoder_options,
                count(*) as cnt
            from
                getid3_file,
                getid3_format_name,
                getid3_encoder_version,
                getid3_encoder_options
            where
                getid3_file.format_name_id = getid3_format_name.id         and 
                getid3_file.encoder_version_id = getid3_encoder_version.id and 
                getid3_file.encoder_options_id = getid3_encoder_options.id and 
                getid3_file.playtime > 0
            group by
                getid3_file.format_name_id,
                getid3_file.encoder_version_id,
                getid3_file.encoder_options_id
            order by
                getid3_format_name.name,
                getid3_encoder_version.name,
                getid3_encoder_options.name
        ');

        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('format_name')][] = array ($this->dbh->f('encoder_version'), $this->dbh->f('encoder_options'), $this->dbh->f('cnt'), $this->dbh->f('format_name_id'), $this->dbh->f('encoder_version_id'), $this->dbh->f('encoder_options_id'));
        }

        return $result;
    }



    /**
    * Array containing title and filesnames for the SPECIFIC File Format Report.
    */

    public function formats_list_data($format_name_id, $encoder_version_id, $encoder_options_id)
    {
        $this->dbh->query("select name from getid3_format_name where id=$format_name_id");
        $this->dbh->next_record();
        $title = $this->dbh->f('name');

        $this->dbh->query("select name from getid3_encoder_version where id=$encoder_version_id");
        $this->dbh->next_record();
        $title .= $this->dbh->f('name') ? ', ' . $this->dbh->f('name') : '';

        $this->dbh->query("select name from getid3_encoder_options where id=$encoder_options_id");
        $this->dbh->next_record();
        $title .= $this->dbh->f('name') ? ', ' . $this->dbh->f('name') : '';


        $this->dbh->query("
            select
                getid3_file.id, 
                concat(getid3_directory.filename, '/', getid3_file.filename) as filename
            from 
                getid3_file,
                getid3_directory
            where 
                getid3_file.directory_id = getid3_directory.id and
                getid3_file.format_name_id     = $_GET[format_name_id]     and 
                getid3_file.encoder_version_id = $_GET[encoder_version_id] and 
                getid3_file.encoder_options_id = $_GET[encoder_options_id] and 
                getid3_file.playtime > 0
        ");

        $data = array ();

        while ($this->dbh->next_record()) {
            $data[$this->dbh->f('id')] = $this->dbh->f('filename');
        }

        return array ($title, $data);
    }



    /**
    * Array containing fields in use and number of time used.
    */

    public function fields_data()
    {
        $result = array ();

        $this->dbh->query("
            select
                getid3_field.name,
                count(*) as cnt
            from
                getid3_file,
                getid3_comment,
                getid3_field
            where
                getid3_comment.file_id  = getid3_file.id  and 
                getid3_comment.field_id = getid3_field.id and 
                getid3_file.playtime > 0
            group by
                getid3_field.name
            order by
                getid3_field.name
        ");

        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('name')] = $this->dbh->f('cnt');
        }

        return $result;
    }



    /**
    * Array containing data for field distribution field report.
    */

    public function field_distribution_data($field, $order_by)
    {
        $result = array ();

        // get data for report
        $this->dbh->query("
            select
                getid3_value.name as field,
                sum(getid3_file.playtime) as sum_playtime,
                count(getid3_file.id) as cnt
            from
                getid3_file,
                getid3_comment,
                getid3_field,
                getid3_value
            where
                getid3_comment.file_id  = getid3_file.id  and 
                getid3_comment.field_id = getid3_field.id and 
                getid3_comment.value_id = getid3_value.id and 
                getid3_field.name = '$field'              and 
                getid3_file.playtime > 0
            group by
                field
            order by
                $order_by
        ");

        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('field')] = array ($this->dbh->f('sum_playtime'), $this->dbh->f('cnt'));
        }

        return $result;

    }



    /*
    * Array containing filenames of files containing specific field.
    */

    public function field_files_data($field)
    {
        $result = array ();

        $this->dbh->query("
            select
                getid3_file.id,
                concat(getid3_directory.filename, '/', getid3_file.filename) as filename
            from
                getid3_file,
                getid3_directory,
                getid3_comment,
                getid3_field
            where
                getid3_file.directory_id = getid3_directory.id and
                getid3_comment.file_id   = getid3_file.id      and 
                getid3_comment.field_id  = getid3_field.id     and 
                getid3_field.name = '$field'                   and 
                getid3_file.playtime > 0
            group by
                getid3_file.id
        ");

        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('id')] = $this->dbh->f('filename');
        }

        return $result;
    }


    /**
    * Number of audio files without replaygain information.
    */

    public function count_missing_replaygain()
    {
        $this->dbh->query("select count(*) as cnt from getid3_file where replaygain_track_gain is null and playtime > 0");
        $this->dbh->next_record();
        return $this->dbh->f('cnt');
    }



    /**
    * Array containing filenames of audio files without replaygain info.
    */

    public function missing_replaygain_data()
    {
        $result = array ();

        $this->dbh->query("
            select 
                getid3_file.id, 
                concat(getid3_directory.filename, '/', getid3_file.filename) as filename
            from 
                getid3_file,
                getid3_directory
            where 
                getid3_file.directory_id = getid3_directory.id and
                getid3_file.replaygain_track_gain is null      and 
                getid3_file.playtime > 0
        ");
        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('id')] = $this->dbh->f('filename');
        }

        return $result;
    }



    /**
    * Number of audio files without tags.
    */

    public function count_tagless()
    {
        $this->dbh->query("select count(*) as cnt from getid3_file where playtime >0 and id not in (select file_id from getid3_comment)");
        $this->dbh->next_record();
        return $this->dbh->f('cnt');
    }



    /**
    * Array containing filenames of audio files without tags.
    */

    public function tagless_data()
    {
        $result = array ();

        $this->dbh->query("
            select
                getid3_file.id, 
                concat(getid3_directory.filename, '/', getid3_file.filename) as filename
            from 
                getid3_file,
                getid3_directory
            where 
                getid3_file.directory_id = getid3_directory.id and
                getid3_file.playtime >0 and 
                getid3_file.id not in (select file_id from getid3_comment)
        ");
        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('id')] = $this->dbh->f('filename');
        }

        return $result;
    }



    /**
    * Number of audio files missing specific (key) field.
    */

    public function count_missing_key_field($field)
    {
        $this->dbh->query("
            select
                count(*) as cnt
            from
                getid3_file
            where
                playtime >0 and 
                id not in (
                select
                    file_id
                from
                    getid3_comment,
                    getid3_field
                where
                    getid3_comment.field_id = getid3_field.id and 
                    getid3_field.name = '$field'
            )

        ");
        $this->dbh->next_record();
        return $this->dbh->f('cnt');
    }



    /**
    * Array containing filenames of audio files missing specific (key) field.
    */

    public function missing_key_field_data($field)
    {
        $result = array ();

        $this->dbh->query("
            select
                getid3_file.id, 
                concat(getid3_directory.filename, '/', getid3_file.filename) as filename
            from 
                getid3_file,
                getid3_directory
            where 
                getid3_file.directory_id = getid3_directory.id and
                getid3_file.playtime >0 and 
                getid3_file.id not in (
                    select
                        file_id
                    from
                        getid3_comment,
                        getid3_field
                    where
                        getid3_comment.field_id = getid3_field.id and 
                        getid3_field.name = '$field'
                )

        ");
        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('id')] = $this->dbh->f('filename');
        }

        return $result;
    }



    /**
    * Number of directories without artwork (images).
    */

    public function count_artless_directories()
    {
        $this->dbh->query("
            select
                count(*) as cnt
            from
                getid3_directory
            where
                artwork = 'no'

        ");
        $this->dbh->next_record();
        return $this->dbh->f('cnt');
    }



    /**
    * Array containing filenames of directories without artwork (images).
    */

    public function artless_directories_data()
    {
        $result = array ();

        $this->dbh->query("
            select
                id,
                filename
            from
                getid3_directory
            where
                artwork = 'no'

        ");
        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('id')] = $this->dbh->f('filename');
        }

        return $result;
    }



    /**
    * Array containing data for the Duplicate Files Report (based on md5 checksum).
    */

    public function duplicate_files_md5data_data()
    {
        $result = $md5 = array ();

        $this->dbh->query("
            select
                md5_data,
                count(id) as cnt
            from
                getid3_file
            where
                md5_data is not null
            group by
                md5_data
            having
                count(id) >1
            order by
                cnt desc
        ");
        while ($this->dbh->next_record()) {
            $md5[] = $this->dbh->f('md5_data');
        }

        foreach ($md5 as $hash) {
            $this->dbh->query("
                select 
                    getid3_file.id, 
                    concat(getid3_directory.filename, '/', getid3_file.filename) as filename
                from 
                    getid3_file,
                    getid3_directory
                where 
                    getid3_file.directory_id = getid3_directory.id and
                    getid3_file.md5_data = '$hash'
            ");
            while ($this->dbh->next_record()) {
                $result[$hash][$this->dbh->f('id')] = $this->dbh->f('filename');
            }
        }

        return $result;
    }



    /**
    * Array containing data for the Duplicate Files Report (based on metadata).
    */

    public function duplicate_files_metadata_data()
    {
        $result = array ();

        // get artist and title for all file_ids (assuming only one value file_id)
        $this->dbh->query("
            select
                c1.file_id,
                v1.name artist,
                v2.name title
            from
                getid3_comment c1,
                getid3_comment c2,
                getid3_field f1,
                getid3_field f2,
                getid3_value v1,
                getid3_value v2
                
            where
                c1.file_id = c2.file_id and 
                c1.field_id = f1.id     and 
                c2.field_id = f2.id     and 
                c1.value_id = v1.id     and 
                c2.value_id = v2.id     and 
                f1.name = 'artist'      and 
                f2.name = 'title'
        ");
        
        // build array artist => title => file_ids (don't extract filenames here, will require too much memory)
        while ($this->dbh->next_record()) {
            $result[$this->dbh->f('artist')][$this->dbh->f('title')][$this->dbh->f('file_id')] = true;
        }

        // loop thru array 
        foreach ($result as $artist => $l2) {
            foreach ($l2 as $title => $file_ids) {
                
                // artist/title pairs with only one file - remove 
                if (sizeof($file_ids) == 1) {
                    unset($result[$artist][$title]);
                }
                
                // duplicate artist/title found
                else {
                    
                    // loop thru all file ids
                    foreach ($file_ids as $id => $true) {
                    
                        // replace true with filename
                        $this->dbh->query("
                            select 
                                concat(getid3_directory.filename, '/', getid3_file.filename) as filename
                            from 
                                getid3_file,
                                getid3_directory
                            where 
                                getid3_file.directory_id = getid3_directory.id and
                                getid3_file.id = $id
                        ");
                        $this->dbh->next_record();
                        $result[$artist][$title][$id] = $this->dbh->f('filename');
                    }
                }
            }
            
            // sort titles
            uksort($result[$artist], 'strcoll');
            
            // remove empty artists
            if (empty($result[$artist])) {
                unset($result[$artist]);
            }
        }
        
        // sort artists
        uksort($result, 'strcoll');
        
        return $result;
    }
}

?>