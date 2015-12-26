package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	
	import flash.events.Event;
	
	import mx.controls.Alert;
	import mx.rpc.IResponder;
	import mx.rpc.events.FaultEvent;
	
	import se.superkrut.player.business.CatalogDelegate;
	import se.superkrut.player.model.PlayerModelLocator;
	import se.superkrut.player.util.CustomFileReferenceList;
	import com.adobe.cairngorm.control.CairngormEventDispatcher;
	import se.superkrut.player.events.LoadSongsEvent;
	
	public class UploadCommand implements ICommand
	{
		public function execute( event : CairngormEvent ): void
		{
			var fileRefList:CustomFileReferenceList = new CustomFileReferenceList();
	        fileRefList.addEventListener(CustomFileReferenceList.LIST_COMPLETE, listCompleteHandler);
	        fileRefList.browse([fileRefList.getMp3TypeFilter()]);
		}
		
		public function listCompleteHandler(event:Event):void
		{
			trace("update command complete");
   			CairngormEventDispatcher.getInstance().dispatchEvent(new LoadSongsEvent());
		}
	}
}