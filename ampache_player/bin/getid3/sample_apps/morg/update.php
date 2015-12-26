#!/usr/bin/php -q 
<?php

// +----------------------------------------------------------------------+
// | PHP version 5.1.4                                                    |
// +----------------------------------------------------------------------+
// | Placed in public domain by Allan Hansen, 2006. Share and enjoy!      |
// +----------------------------------------------------------------------+
// | update.php                                                           |
// | Shell script to extract extended file info and generate statistics.  |
// +----------------------------------------------------------------------+
// | Authors: Allan Hansen <ah@artemis.dk>                                |
// +----------------------------------------------------------------------+
//
// $Id: update.php,v 1.42 2006/12/25 23:45:00 ah Exp $


// extract and change to current directory
$path_info = pathinfo($_SERVER["argv"][0]);
$curr_dir = $path_info['dirname'];
if (!$curr_dir) {
    die("ERROR: Cannot extract directory of update.php (cannot run from browser). Run with full path.\n");
}
chdir($curr_dir);        


require 'morg/morg_analyze.php';

$app = new morg_analyze;
$app->run();

?>
