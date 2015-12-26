package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	
	import mx.collections.ArrayCollection;
	import mx.controls.Alert;
	import mx.core.DragSource;
	import mx.core.IUIComponent;
	import mx.events.DragEvent;
	import mx.managers.DragManager;
	import mx.rpc.IResponder;
	import mx.rpc.events.FaultEvent;
	
	import se.superkrut.player.business.CatalogDelegate;
	import se.superkrut.player.events.CoverDragEvent;
	import se.superkrut.player.events.FoldersDragEvent;
	import se.superkrut.player.model.PlayerModelLocator;
	import flash.events.MouseEvent;
	
	public class CoverDragCommand implements ICommand
	{
		
		public function execute( event : CairngormEvent ): void
		{
			var mouseEvent:MouseEvent = CoverDragEvent(event).mouseEvent;
			var selected:Array = CoverDragEvent(event).selectedItems;
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			
			var ds:DragSource = new DragSource();

			var items:Array = new Array();
			
			for(var i:int = 0;i<selected.length;i++)
			{
				var id:int = selected[i].id;
				var songs:Array = model.albums_hash[id];
				for(var j:int = 0;j<songs.length;j++)
				{
					var song:Object = songs[j];
					items.push(song);
				}
			}
			
            ds.addData(items, "items");

            DragManager.doDrag(IUIComponent(mouseEvent.target), ds, mouseEvent);
		}
	}
}