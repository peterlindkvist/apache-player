package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	
	import mx.collections.ArrayCollection;
	import mx.controls.Alert;
	import mx.controls.Tree;
	import mx.rpc.IResponder;
	import mx.rpc.events.FaultEvent;
	
	import se.superkrut.player.business.CatalogDelegate;
	import se.superkrut.player.events.GetFoldersEvent;
	import se.superkrut.player.model.PlayerModelLocator;
	
	public class GetFoldersCommand implements ICommand, IResponder
	{
		public function execute( event : CairngormEvent ): void
		{
		    var model:PlayerModelLocator = PlayerModelLocator.getInstance();
		    var getFoldersEvent:GetFoldersEvent = GetFoldersEvent(event);
		    model.openingEvent = getFoldersEvent.openingEvent;
		    var item:XML = XML(getFoldersEvent.openingEvent.item);
		    var delegate : CatalogDelegate = new CatalogDelegate( this );
		    delegate.getFolder(model.user.username, model.user.password, item.@cat+"", item.@path+"");
		}
	
		public function result( event : Object ) : void
		{				
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			var xmlstr:String = event.result as String;
			trace("recieve: "+xmlstr);
			var xml:XML = new XML(xmlstr);
			var item:Object = model.openingEvent.item;
			var tree:Tree = Tree(model.openingEvent.currentTarget);
	
			item.dir = xml.dir;

			tree.expandItem(item, false); //to update the graphic
			tree.expandItem(item, true);
		}
	
		public function fault( event : Object ) : void
		{
			var faultEvent : FaultEvent = FaultEvent( event );
			Alert.show( "folders could not be retrieved!" );
		}
	}
}