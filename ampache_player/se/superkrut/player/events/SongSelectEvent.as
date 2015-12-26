package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	import mx.events.ListEvent;
	
	public class SongSelectEvent extends CairngormEvent
	{
		public static var SONG_SELECT_EVENT:String = "song_select";
		public var listEvent:ListEvent;
		
		public function SongSelectEvent(event:ListEvent)
		{
			super(SONG_SELECT_EVENT);
			this.listEvent = event;
		}
	}
}