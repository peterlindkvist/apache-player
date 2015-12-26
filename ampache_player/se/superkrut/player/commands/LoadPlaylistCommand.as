package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	
	import mx.controls.Alert;
	import mx.rpc.IResponder;
	import mx.rpc.events.FaultEvent;
	
	import se.superkrut.player.business.CatalogDelegate;
	import se.superkrut.player.events.LoadPlaylistEvent;
	import se.superkrut.player.model.PlayerModelLocator;
	
	public class LoadPlaylistCommand implements ICommand, IResponder
	{
		public function execute(event:CairngormEvent):void
		{
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			
			var delegate : CatalogDelegate = new CatalogDelegate( this );
		    delegate.getPlaylists(model.user.username, model.user.password);
		}
		
		public function result( event : Object) : void
		{				
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			var playlists:Object = event.result;

			var names:Array = new Array();
			for(var s:String in playlists)
			{
				names.push(s);
			}
			
			model.playlistNames = ["local"].concat(names);
			model.playlists = playlists;
		}
		
		public function fault( event : Object ) : void
		{
			var faultEvent : FaultEvent = FaultEvent( event );
			Alert.show( "loadplaylist failure" );
		}
	}
}