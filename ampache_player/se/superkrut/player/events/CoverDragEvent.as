package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	import mx.events.DragEvent;
	import flash.events.MouseEvent;
	
	public class CoverDragEvent extends CairngormEvent
	{
		public static var COVER_DRAG_EVENT:String = "cover_drag_event";
		public var mouseEvent:MouseEvent;
		public var selectedItems:Array;
		
		public function CoverDragEvent(event:MouseEvent, selectedItems:Array)
		{
			super(COVER_DRAG_EVENT);
			mouseEvent = event;
			this.selectedItems = selectedItems;
		}
	}
}