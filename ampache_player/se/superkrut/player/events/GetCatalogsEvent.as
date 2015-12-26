package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	
	public class GetCatalogsEvent extends CairngormEvent
	{
		public static var GET_CATALOGS_EVENT:String = "get_catalogs_event";
		
		public function GetCatalogsEvent()
		{
			super(GET_CATALOGS_EVENT);
		}
	}
}