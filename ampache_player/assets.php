<?php
/*

 Copyright (c) 2001 - 2006 Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License v2
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/
 
require("../amfphp/services/ampache/AmpacheService.php");
 
$as = new AmpacheService();
$id = $_GET['id'];
$user = $_GET['user'];
$password = $_GET['password'];
$type = $_GET['type'];

if($type == "cover")
{
	$imagetype = "art";
	$cover = $as->getAlbum($user, $password, $id);
	
	header("Content-type: ".$cover[$imagetype.'_mime']);
	echo $cover[$imagetype]; 
}
else
{
	$song = $as->getSong($user, $password, $id);
	$file = $song['file'];
	$fp = fopen($file, 'r');

	header("Accept-Ranges: bytes" );
	header("Content-Length: $song->size");
	header("Content-Type: audio/mpeg");
	echo fread($fp, filesize($file));
	fclose($fp);
}
?>
