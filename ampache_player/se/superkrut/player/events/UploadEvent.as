package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	
	public class UploadEvent extends CairngormEvent
	{
		public static var UPLOAD_EVENT:String = "upload_event";
		
		public function UploadEvent()
		{
			super(UPLOAD_EVENT);
		}
	}
}