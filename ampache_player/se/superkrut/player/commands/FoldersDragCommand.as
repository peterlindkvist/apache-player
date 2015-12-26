package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	
	import mx.collections.ArrayCollection;
	import mx.controls.Alert;
	import mx.core.DragSource;
	import mx.events.DragEvent;
	import mx.rpc.IResponder;
	import mx.rpc.events.FaultEvent;
	
	import se.superkrut.player.business.CatalogDelegate;
	import se.superkrut.player.events.FoldersDragEvent;
	import se.superkrut.player.model.PlayerModelLocator;
	import mx.managers.DragManager;
	
	public class FoldersDragCommand implements ICommand, IResponder
	{
		private var dragEvent:DragEvent;
		private var pasteId:Number;
		
		public function execute( event : CairngormEvent ): void
		{
		    var delegate : CatalogDelegate = new CatalogDelegate( this );
			dragEvent = FoldersDragEvent(event).dragEvent;
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			
			if(!model.isFolderDraging)
			{
				model.isFolderDraging = true;
			
				var dragSource:DragSource = dragEvent.dragSource;
				var treeItems:Array = dragSource.dataForFormat("treeItems") as Array;
				var folders:Array = new Array();
				for(var i:int = 0;i<treeItems.length;i++)
				{
					var items:XML = treeItems[i];
					
					for(var j:int = 0;j<items.length();j++)
					{
						folders.push({catalog:items[j].@cat+"", path:items[j].@path+""});
					}
				}
				pasteId = Math.random();
				dragSource.addData([{pasteid:pasteId, artist:"waiting..."}] ,"items");
				delegate.getSongsByFolder(model.user.username, model.user.password, folders);
			}
		}
	
		public function result( event : Object ) : void
		{				
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			var dragSource:DragSource = dragEvent.dragSource;
			var songs:ArrayCollection = event.result;

			
			if(model.isFolderDraging){
				var len:uint = songs.length;
				var items:Array = new Array(len);		

				for(var i:int = 0;i<len;i++){
					var song:Object = songs[i];
					items[len-i] = song;
					trace(i+"adddrag"+songs[i].title+":"+songs[i].artist);
				}
				dragSource.addData(items,"items");
			}
			else
			{
				var pasteIndex:uint;
				var playlist:Array = model.playlist_active_ac.source;
				for(var j:uint = 0;j<playlist.length;j++)
				{
					if(playlist[j].pasteid == pasteId)
					{
						pasteIndex = j;
						break;
					}
				}

				model.playlist_active_ac.removeItemAt(pasteIndex);
				for(var k:int = 0;k<songs.length;k++)
				{
					trace(k+"add"+songs[k].title+":"+songs[k].artist);
					model.playlist_active_ac.addItemAt(songs[k], pasteIndex+k);
				}
			}
		}
	
		public function fault( event : Object ) : void
		{
			var faultEvent : FaultEvent = FaultEvent( event );
			Alert.show( "songs could not be retrieved!" );
		}
	}
}