<?php
	class AmpacheService
	{
		var $db;
		var $winfs;
		var $player_path;
	
		function AmpacheService()
		{
			include_once("settings.php");
			$this->winfs = $system_winfs;
			$this->player_path = $system_player_path;
			$this->db = mysql_connect($mysql_host, $mysql_user, $mysql_pwd);
			mysql_select_db($mysql_db, $this->db);
		}

		function getArtists($user, $password)
		{
			if($this->hasAccess($user, $password)){
				$sql = "SELECT * FROM artist";
				return $this->_db_query($sql);
			}else return false;
		}

		function getAlbums($user, $password)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "SELECT id, name FROM album;";
				return $this->_db_query($sql);
			}else return false;
		}
		
		function getAlbumFromName($user, $password, $name)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "SELECT * FROM album WHERE name='$name' LIMIT 0,1;";
				echo $sql;
				return mysql_fetch_assoc($this->_db_query($sql));
			}else return false;
		}
		
		function createAlbum($user, $password, $name, $year)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "INSERT INTO album (name, year) VALUES ('$name', '$year')";
				echo $sql;
				$this->_db_query($sql);
				$id = mysql_insert_id($this->db);
				return $id;
			}else return false;
		}
		
		function getArtistFromName($user, $password, $name)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "SELECT * FROM artist WHERE name='$name' LIMIT 0,1;";
				echo $sql;
				return mysql_fetch_assoc($this->_db_query($sql));
			}else return false;
		}
		
		function createArtist($user, $password, $name)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "INSERT INTO artist (name) VALUES ('$name')";
				echo $sql;
				$this->_db_query($sql);
				$id = mysql_insert_id($this->db);
				return $id;
			}else return false;
		}
		
		function getCatalogFromName($user, $password, $name)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "SELECT * FROM catalog WHERE name='$name' LIMIT 0,1;";
				echo $sql;
				return mysql_fetch_assoc($this->_db_query($sql));
			}else return false;
		}
		
		function getSongFromData($user, $password, $title, $artist_id, $album_id, $year)
		{
			if($this->hasAccess($user, $password)){	
				$sql  = "SELECT * FROM song WHERE title='$title' AND artist='$artist_id' AND album='$album_id' AND year='$year' LIMIT 0,1";
				echo $sql;
				return mysql_fetch_assoc($this->_db_query($sql));
			}else return false;
		}
		
		function createSong($user, $password, $file, $catalog_id, $album_id, $year, $artist_id, $title, $bitrate, $rate, $size, $time, $addtime)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "INSERT INTO song (file, catalog, album, year, artist, title, bitrate, rate, size, time, addition_time) ";
				$sql .= "VALUES ('$file', '$catalog_id', ' $album_id', '$year', '$artist_id', '$title', '$bitrate', '$rate', '$size', '$time', '$addtime')";
				echo $sql;
				$this->_db_query($sql);
				$id = mysql_insert_id($this->db);
				return $id;
			}else return false;
		}
		
		function getSongsFromAlbum($user, $password, $id)
		{
			if($this->hasAccess($user, $password)){	
				$sql  = "SELECT song.id, song.title, artist.name AS artist, album.name AS album, album.id AS album_id, genre.name AS genre, song.file, song.time ";
				$sql .= "FROM song ";
				$sql .= "LEFT JOIN artist ON song.artist = artist.id ";
				$sql .= "LEFT JOIN album ON song.album = album.id ";
				$sql .= "LEFT JOIN genre ON song.genre = genre.id ";
				$sql .= "WHERE album.id='$id' ";
				$sql .= "ORDER BY song.track;";
		
				return $this->_db_query($sql);
			}else return false;
		}
		
		function getSongs($user, $password, $start, $length, $updateTime=-1)
		{
			if($this->hasAccess($user, $password)){
				$sql  = "SELECT song.id, song.title, artist.name AS artist, album.name AS album, album.id AS album_id, genre.name AS genre, song.file, song.time, song.track AS track ";
				$sql .= "FROM song ";
				$sql .= "LEFT JOIN artist ON song.artist = artist.id ";
				$sql .= "LEFT JOIN album ON song.album = album.id ";
				$sql .= "LEFT JOIN genre ON song.genre = genre.id ";
				$sql .= "WHERE song.update_time > '$updateTime' OR song.addition_time > '$updateTime' ";
				$sql .= "ORDER BY artist.name, album.name, song.track ";
				$sql .= "LIMIT $start, $length;";
				
				$res = $this->_db_query($sql);
				$songs = Array();
				while($r = mysql_fetch_assoc($res)){
					array_push($songs, $r);
				}
				//return $sql;
				
				$time_sql = "SELECT (SELECT update_time FROM song ORDER BY update_time DESC LIMIT 0,1) AS update_time, ";
				$time_sql .= "(SELECT addition_time FROM song ORDER BY addition_time DESC LIMIT 0,1) AS addition_time;";
				
				$ret = Array();
				$ret["songs"] = $songs;
				$ret["updateTime"] = time();
				return $ret;
				//return $this->_db_query($sql);
			}else return false;
		}
		
			
		function getSong($user, $password, $id)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "SELECT * FROM song WHERE id='$id'";
				return mysql_fetch_assoc($this->_db_query($sql));
			}else return false;
		}

		function getGenres($user, $password)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "SELECT * FROM genre";
				return $this->_db_query($sql);
			}else return false;
		}
		
		function getSessionId()
		{
			return session_id();
		}
		
		function getUser($user, $password)
		{
			$sql = "SELECT * FROM user WHERE username='$user'  AND password = PASSWORD('$password')";
			return $this->_db_query($sql);
		}
		
		function getPlaylists($user, $password)
		{
			if($this->hasAccess($user, $password)){
				$sql = "SELECT * FROM playlist ORDER BY name;";
				$res = $this->_db_query($sql);
				$ret = Array();
				while($r = mysql_fetch_array($res)){
					$plsql = "SELECT * FROM playlist_data WHERE playlist='".$r['id']."'";	
					$plres = $this->_db_query($plsql);
					$pl = Array();
					$plret = Array();
					while($plr = mysql_fetch_assoc($plres)){
						array_push($pl, $plr['song']);
					}
					$ret[$r['name']] = $pl;
				}
				return $ret;
			}else return false;
		}
		
		function createPlaylist($user, $password, $name, $songids)
		{
			if($this->hasAccess($user, $password)){
				$sql = "INSERT INTO playlist (name, user)  VALUES ('$name','$user')";
				$res = $this->_db_query($sql);
				
				$id = mysql_insert_id($this->db);
				for($i = 0 ; $i<sizeof($songids) ; $i++){
					$tmpsql = "INSERT INTO playlist_data (playlist,song) VALUES ('$id','" . $songids[$i] . "')";
					$res = $this->_db_query($tmpsql);
				}
				return $sql;
			}else return false;
		}
		
		function updatePlaylist($user, $password, $name, $songids)
		{
			if($this->hasAccess($user, $password)){
				$sql = "DELETE FROM playlist_data WHERE playlist = '$id'";
				$this->_db_query($sql);
				for($i = 0 ; $i<sizeof($songids) ; $i++){
					$sql = "INSERT INTO playlist_data (playlist,song) VALUES ('$id','" . $songids[$i] . "')";
					$this->_db_query($sql);
				}
				return true;
			}else return false;
		}
		
		function getSongsByFolder($user, $password, $items)
		{
			if($this->hasAccess($user, $password)){
				for($i = 0 ; $i<sizeof($items) ;$i++){
					$item = $items[$i];
					if($i == 0){
						$wheres  ="song.file LIKE '".$this->_getLikePath($item["catalog"], $item["path"])."%' ";
					}else{
						$wheres .="OR song.file LIKE '".$this->_getLikePath($item["catalog"], $item["path"])."%' ";
					}
				}

				$sql  = "SELECT song.id, song.title, artist.name AS artist, album.name AS album, album.id AS album_id, genre.name AS genre, song.file, song.time ";
				$sql .= "FROM song ";
				$sql .= "LEFT JOIN artist ON song.artist = artist.id ";
				$sql .= "LEFT JOIN album ON song.album = album.id ";
				$sql .= "LEFT JOIN genre ON song.genre = genre.id ";
				$sql .= "WHERE $wheres ";
				$sql .= "ORDER BY artist.name, album.name, song.track;";
				
				return $this->_db_query($sql);
			}else return false;
		}
		
		function getAlbum($user, $password, $id)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "SELECT * FROM album WHERE id='$id' LIMIT 0,1";
				$ret = mysql_fetch_assoc($this->_db_query($sql));
				return $ret;
			}else return false;
		}
		
		function getSettings($user, $password)
		{
			if($this->hasAccess($user, $password)){
				ini_set('post_max_size','50M');
				ini_set('upload_max_filesize','50M');
				ini_set('memory_limit','128M');
				$ret = Array();
				$ret["visualisations"] = $this->getFilesInFolder($user, $password, $this->player_path."visualisations");
				$ret["max_post"] = ini_get('post_max_size');
				$ret["max_upload"] = ini_get('upload_max_filesize');
				return $ret;
			}else return false;
		}
		
		function _getLikePath($catalog, $folder)
		{
			$sql = "SELECT path FROM catalog WHERE name = '$catalog';";
			$res = $this->_db_query($sql);
			$r = mysql_fetch_array($res);
			$fullpath = $r['path']."".$folder;
			if($this->winfs){
				$fullpath = str_replace("/", "\\", $fullpath); //windowsmode
			}else{
				$fullpath = str_replace("\\", "/", $fullpath); //unixmode
			}
			$fullpath = str_replace("\\", "\\\\\\\\", $fullpath); 	
			$fullpath = str_replace(" ", "\\\\ ", $fullpath); 
			//$fullpath = str_replace("_", "\\_ ", $fullpath); 
			return $fullpath;
			//return $catalog.":::".$folder;
		}
		
		function getPathFromCatalog($user, $password, $name)
		{
			if($this->hasAccess($user, $password)){
				$sql = "SELECT path FROM catalog WHERE name='$name';";
				$res = mysql_fetch_assoc($this->_db_query($sql));
				return $res['path'];
			}
		}
		
		function getCatalogs($user, $password)
		{
			if($this->hasAccess($user, $password)){
				$sql = "SELECT name, path FROM catalog;";
				$res = $this->_db_query($sql);
				$ret = "<root>";
				while($r = mysql_fetch_array($res)){
					$path = $r['path'];
					$name = $r['name'];
					
					$ret .= '<dir name="'.$name.'" path="" cat="'.$name.'"><dir name="wait..." /></dir>';
				}
				$ret .= "</root>";
				return $ret;
			}else return false;
		}
		
		function getFolder($user, $password, $catalog, $folder)
		{
			if($this->hasAccess($user, $password)){	
				$sql = "SELECT path FROM catalog WHERE name = '".$catalog."';";
				$res = $this->_db_query($sql);
				$r = mysql_fetch_array($res);
				
				$fullpath = $r['path']."/".$folder;
				
				$ret = "<root>";
				
				if ($handle = opendir($fullpath)) {
					while (false !== ($file = readdir($handle))) {
						$path = $folder."/".$file;
						if(is_dir($fullpath."/".$file)){
							if($file != "." && $file != ".."){
								$ret .= '<dir path="'.$path.'" name="'.$file.'" cat="'.$catalog.'"><dir name="wait..." /></dir>';
							}
						}else{
							$ret .= '<dir path="'.$path.'" name="'.$file.'" cat="'.$catalog.'"/>';
						}
					}
				}
				closedir($handle); 
				
				$ret .= '</root>';
				return $ret;
			}else return false;
		}
		
		function getFilesInFolder($user, $password, $path)
		{
			if($this->hasAccess($user, $password)){	
				$ret = Array();
				if(is_dir($path)){
					if ($handle = opendir($path)) {
						while (false !== ($file = readdir($handle))) { 
							if($file != "." && $file != ".."){
								array_push($ret , $file);
							}
						}
					}
					closedir($handle);
				}
				return $ret;
			}else return false;
		}
		
		
		function getFileSystem($user, $password, $useCache)
		{
			if($this->hasAccess($user, $password)){
				$useCache = true;
				if($useCache){
					$file = "filesystem.xml";
					$fp = fopen($file, "r");
					$ret = fread($fp, filesize($file));
					fclose($fp);
					return $ret;
				}else{
					$sql = "SELECT name, path FROM catalog;";
					$res = $this->_db_query($sql);
					//$ret = Array();
					$ret = '<root>';
					while($r = mysql_fetch_array($res)){
						$path = $r['path'];
						$name = $r['name'];
						
						$ret .= $this->_parsefolder($path, "");
						//$ret[$name] = $this->_parsefolder($path, $name);
					}
					$ret .= '</root>';
					$fp = fopen("filesystem.xml", "w+");
					fputs($fp, $ret);
					fclose($fp);
					return $ret;
				}
			}else return false;
		}
		
		function _parsefolder($path, $name)
		{
			$ret = '<directory path="'.urlencode($path).'" name="'.$name.'">';
			$path = $path."/".$name;
			if ($handle = opendir($path)) {
				while (false !== ($file = readdir($handle))) { 
					if(is_dir($path."/".$file))
					{
						if($file != "." && $file != "..")
						{
							$ret .= $this->_parsefolder($path, $file);
						}
					}
					else
					{
						$ret .= '<directory path="'.urlencode($path).'" name="'.$file.'" />';
					}
				}
			}
			closedir($handle); 
			
			$ret .= '</directory>';
			return $ret;
		} 
		
		//not in use
		function updateCatalogFolder($user, $password, $catalog, $folder)
		{
			if($this->hasAccess($user, $password)){
			
				return true;
			}else return false;
		}
		
		function _db_query($sql)
		{
			return mysql_query($sql, $this->db); //turn on escape
			//return mysql_query($this->_escape($sql), $this->db);
			//return mysql_query(mysql_real_escape_string($sql), $this->db);
		}
		
		function hasAccess($user, $password)
		{
			return mysql_num_rows($this->getUser($user, $password)) > 0;
		}
		
		/**
		 * Escape a SQL string
		 */
		function _escape($sql)
		{
			$args = func_get_args();
			foreach($args as $key => $val)
			{
				$args[$key] = mysql_real_escape_string($val);
			}
			
			$args[0] = $sql;
			return call_user_func_array('sprintf', $args);
		} 
	} // END class 
?>