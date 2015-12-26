package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	
	public class GetSettingsEvent extends CairngormEvent
	{
		public static var GET_SETTINGS_EVENT:String = "get_settings";
		
		public function GetSettingsEvent()
		{
			super(GET_SETTINGS_EVENT);
		}
	}
}