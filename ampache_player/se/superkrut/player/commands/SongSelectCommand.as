package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	import com.adobe.cairngorm.control.CairngormEventDispatcher;
	import com.adobe.cairngorm.model.ModelLocator;
	
	import flash.events.KeyboardEvent;
	import flash.events.TimerEvent;
	import flash.ui.Keyboard;
	import flash.utils.Timer;
	
	import mx.collections.ArrayCollection;
	import mx.controls.Alert;
	import mx.events.ListEvent;
	
	import se.superkrut.player.events.SongSelectEvent;
	import se.superkrut.player.model.PlayerModelLocator;
	import se.superkrut.player.vo.UserVo;

	public class SongSelectCommand implements ICommand
	{
		private static var CLICKDELAY:int = 250;
		private var model:PlayerModelLocator;
		private var waitTimer:Timer;
		private var selectedItem:Object;
		
		public function execute( event : CairngormEvent ): void
		{
		    var songSelectEvent:SongSelectEvent = SongSelectEvent(event);
			var listEvent:ListEvent = songSelectEvent.listEvent;

			selectedItem = listEvent.currentTarget.selectedItem;
			model = PlayerModelLocator.getInstance();

			model.selectedElement = selectedItem;
		}
	}
}