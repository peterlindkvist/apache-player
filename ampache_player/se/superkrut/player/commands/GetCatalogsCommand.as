package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	
	import mx.collections.ArrayCollection;
	import mx.controls.Alert;
	import mx.rpc.IResponder;
	import mx.rpc.events.FaultEvent;
	
	import se.superkrut.player.business.CatalogDelegate;
	import se.superkrut.player.model.PlayerModelLocator;
	
	public class GetCatalogsCommand implements ICommand, IResponder
	{
		public function execute( event : CairngormEvent ): void
		{
		    var model:PlayerModelLocator = PlayerModelLocator.getInstance();
		    var delegate : CatalogDelegate = new CatalogDelegate( this );
		    delegate.getCatalogs(model.user.username, model.user.password);
		}
	
		public function result( event : Object ) : void
		{				
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			var xmlstr:String = event.result as String;
			var root_xml:XML = new XML(xmlstr);
			model.folders_xml = root_xml;

			var catalog_xml:XMLList = root_xml.dir;
			trace(catalog_xml+"::"+catalog_xml.length());
			var catalog_arr:Array = new Array();
			for(var j:int = 0;j<catalog_xml.length();j++)
			{
				catalog_arr.push(catalog_xml[j].@cat+"");
			}
			model.catalogs_arr = catalog_arr;
			model.upload.catalog = catalog_arr[0];
		}
	
		public function fault( event : Object ) : void
		{
			var faultEvent : FaultEvent = FaultEvent( event );
			Alert.show( "songs could not be retrieved!" );
		}
	}
}