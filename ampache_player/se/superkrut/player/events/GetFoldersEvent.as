package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	import mx.events.TreeEvent;
	
	public class GetFoldersEvent extends CairngormEvent
	{
		public static var GET_FOLDERS_EVENT:String = "get_folders_event";
		public var openingEvent:TreeEvent;
		
		public function GetFoldersEvent(openingEvent:TreeEvent)
		{
			super(GET_FOLDERS_EVENT);
			this.openingEvent = openingEvent;
		}
	}
}