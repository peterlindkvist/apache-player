package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	import mx.events.DragEvent;
	
	public class FoldersDragEvent extends CairngormEvent
	{
		public static var FOLDERS_DRAG_EVENT:String = "folders_drag_event";
		public var dragEvent:DragEvent;
		
		public function FoldersDragEvent(event:DragEvent)
		{
			super(FOLDERS_DRAG_EVENT);
			dragEvent = event;
		}
	}
}