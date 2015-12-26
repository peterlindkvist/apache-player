package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	
	import mx.controls.Alert;
	import mx.rpc.IResponder;
	import mx.rpc.events.FaultEvent;
	
	import se.superkrut.player.business.CatalogDelegate;
	import se.superkrut.player.model.PlayerModelLocator;
	import se.superkrut.player.events.SavePlaylistEvent;
	
	public class SavePlaylistCommand implements ICommand, IResponder
	{
		public function execute(event:CairngormEvent):void
		{
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			var saveEvent:SavePlaylistEvent = SavePlaylistEvent(event);
			var playlistname:String = saveEvent.playlistname;
			var newName:String = saveEvent.newName;
			var songids:Array = new Array();
			var playlist:Array = model.playlist_active_ac.source;
			for(var i:int = 0;i<playlist.length;i++)
			{
				songids.push(playlist[i]['id']);
			}
			
			var delegate : CatalogDelegate = new CatalogDelegate( this );
			if(playlistname == "local" || newName != "")
			{
				if(newName == "") newName = "local_"+Math.floor(Math.random()*10000);
				delegate.createPlaylist(model.user.username, model.user.password, newName, songids);	
			}
			else
			{
				delegate.updatePlaylist(model.user.username, model.user.password, playlistname, songids);
			}
		}
		
		public function result( event : Object) : void
		{				
			
		}
		
		public function fault( event : Object ) : void
		{
			var faultEvent : FaultEvent = FaultEvent( event );
			Alert.show( "saveplaylist failure" );
		}
	}
}