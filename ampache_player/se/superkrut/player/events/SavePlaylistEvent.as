package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	
	public class SavePlaylistEvent extends CairngormEvent
	{
		public static var SAVE_PLAYLIST_EVENT:String = "save_playlist_event";
		public var playlistname:String;
		public var newName:String;
		
		public function SavePlaylistEvent(playlistname:String, newName:String)
		{
			super(SAVE_PLAYLIST_EVENT);
			this.playlistname = playlistname;
			this.newName = newName;
		}
	}
}