package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	import mx.events.DragEvent;
	import flash.events.MouseEvent;
	
	public class LoadPlaylistEvent extends CairngormEvent
	{
		public static var LOAD_PLAYLIST_EVENT:String = "load_playlist_event";
		
		public function LoadPlaylistEvent()
		{
			super(LOAD_PLAYLIST_EVENT);
		}
	}
}