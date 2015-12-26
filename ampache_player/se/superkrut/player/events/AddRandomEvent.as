package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	import mx.events.DragEvent;
	import flash.events.MouseEvent;
	
	public class AddRandomEvent extends CairngormEvent
	{
		public static var ADD_RANDOM_EVENT:String = "add_random_event";
		
		public function AddRandomEvent()
		{
			super(ADD_RANDOM_EVENT);
		}
	}
}