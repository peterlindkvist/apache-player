package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	
	public class SearchEvent extends CairngormEvent
	{
		public static var SEARCH_EVENT:String = "search_event";
		public var searchStr:String;
		
		public function SearchEvent(searchStr:String)
		{
			super(SEARCH_EVENT);
			this.searchStr = searchStr;
		}
	}
}