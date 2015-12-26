package se.superkrut.player.business
{
	import mx.rpc.IResponder;
	import com.adobe.cairngorm.business.ServiceLocator;
	import com.adobe.cairngorm.control.CairngormEvent;
	
	public class CatalogDelegate
	{
		private var responder : IResponder;
		private var service : Object;
		
		public function CatalogDelegate( responder : IResponder)
		{		
			this.service = ServiceLocator.getInstance().getRemoteObject( "ampacheService" );
			this.responder = responder;
		}
		
		public function getUser(username:String, password:String) : void
		{			
			var call : Object = service.getUser(username, password);
			call.addResponder( responder );
		}
		
		public function getSongs(username:String, password:String, start:uint, length:uint, updateTime:int) : void
		{			
			var call : Object = service.getSongs(username, password, start, length, updateTime);
			call.addResponder( responder );
		}
		
		public function getCatalogs(username:String, password:String):void
		{
			var call : Object = service.getCatalogs(username, password);
			call.addResponder( responder );
		}

		public function getFolder(username:String, password:String, catalog:String, folder:String):void
		{
			var call : Object = service.getFolder(username, password, catalog, folder);
			call.addResponder( responder );
		}
		
		public function getSongsByFolder(username:String, password:String, folders:Array):void
		{
			var call : Object = service.getSongsByFolder(username, password, folders);
			call.addResponder( responder );
		}
		
		public function getSettings(username:String, password:String):void
		{
			 var call : Object = service.getSettings(username, password)
			 call.addResponder( responder );
		}
		
		public function getPlaylists(username:String, password:String):void
		{
			 var call : Object = service.getPlaylists(username, password)
			 call.addResponder( responder );
		}
		
		public function createPlaylist(username:String, password:String, name:String, songids:Array):void
		{
			 var call : Object = service.createPlaylist(username, password, name, songids)
			 call.addResponder( responder );
		}
		
		public function updatePlaylist(username:String, password:String, name:String, songids:Array):void
		{
			 var call : Object = service.createPlaylist(username, password, name, songids)
			 call.addResponder( responder );
		}
	}
}