<?php
require($_SERVER['DOCUMENT_ROOT']."/amfphp/services/ampache/AmpacheService.php");
require_once('getid3/getid3/getid3.php');

$as = new AmpacheService();

$user = $_POST['user'];
$password = $_POST['password'];
$catalog = $_POST['catalog'];
$folder = $_POST['folder'];
echo "has access".$as->hasAccess($user, $password).";".$user.":".$passsword; 
if($as->hasAccess($user, $password))
{
	$cat_folder = $as->getPathFromCatalog($user, $password, $catalog);
	// fix windows mode
	if($as->winfs){
		$separator = "\\";
	}else{
		$separator = "/";
	}
	echo "sep: ".separator.";".$as->winfs;
	$path = $cat_folder.$separator.$folder;
	
	$index = "Filedata";
	
	$filename = $_FILES[$index]["name"];
	$filesize = $_FILES[$index]["size"];
	$tmpname  = $_FILES[$index]["tmp_name"];
	$fullname = $path.$separator.$filename;
	
	if(preg_match('/\.mp3$/', $filename)){
		echo "catfolder: ". $cat_folder."\n";
		echo "folder: ". $path."\n";
		echo "fullname: ". $fullname."\n";
		
		if(!is_dir($path)){
			mkdir($path);
		}
		if(move_uploaded_file($tmpname, $fullname))
		{ 

			$getid3 = new getID3;

			// Tell getID3() to use UTF-8 encoding - must send proper header as well.
			$getid3->encoding = 'UTF-8';
			
			try {

				$getid3->Analyze($fullname);
				$bitrate = $getid3->info['audio']['bitrate'];
				$rate    = $getid3->info['audio']['sample_rate'];
				$year    = $getid3->info['id3v1']['year'];
				$artist  = $getid3->info['id3v1']['artist'];
				$album   = $getid3->info['id3v1']['album'];
				$track   = $getid3->info['id3v1']['track'];
				$title   = $getid3->info['id3v1']['title'];
				$time    = round($getid3->info['playtime_seconds']);
				$size    = filesize($fullname);
				$addtime = time();
				$sqlfullname = str_replace("\\", "\\\\", $fullname);
				if($title == "") $title = $filename;
				
				$catalog_obj = $as->getCatalogFromName($user, $password, $catalog);
				$catalog_id = $catalog_obj['id'];
				
				$album_obj = $as->getAlbumFromName($user, $password, $album);
				if($album_obj['id'] == "") $album_id = $as->createAlbum($user, $password, $album, $year);
				else $album_id = $album_obj['id'];
				
				$artist_obj = $as->getArtistFromName($user, $password, $artist);
				if($artist_obj['id'] == "") $artist_id = $as->createArtist($user, $password, $artist);
				else $artist_id = $artist_obj['id'];
				
				$song_obj = $as->getSongFromData($user, $password, $title, $artist_id, $album_id, $year);
				if($song_obj['id'] == "") $song_id = $as->createSong($user, $password, $sqlfullname, $catalog_id, $album_id, $year, $artist_id, $title, $bitrate, $rate, $size, $time, $addtime);
				else $song_id = $song_obj['id'];
			}
			catch (Exception $e) {
				
				echo 'An error occured: ' .  $e->message;
			}
		}
	}else{
		echo "no mp3";
	}
}else{ 
	echo "access denied";
}
?>