package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	
	public class LoadSongsEvent extends CairngormEvent
	{
		public static var LOAD_SONGS_EVENT:String = "load_songs_event";
		public var forceLoad:Boolean;
		
		public function LoadSongsEvent(forceLoad:Boolean = false)
		{
			super(LOAD_SONGS_EVENT);
			this.forceLoad = forceLoad;
		}
	}
}