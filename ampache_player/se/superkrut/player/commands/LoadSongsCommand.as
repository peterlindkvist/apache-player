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
	import com.adobe.cairngorm.control.CairngormEventDispatcher;
	import se.superkrut.player.events.GetCatalogsEvent;
	import flash.net.SharedObject;
	import se.superkrut.player.events.LoadSongsEvent;
	
	public class LoadSongsCommand implements ICommand, IResponder
	{
		private var partlength:uint = 1000;
		private var partpos:uint = 0;
		private var newsongs:Array;
		private var lastUpdate:int;
		
		public function execute( event : CairngormEvent ): void
		{
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			var so:SharedObject = SharedObject.getLocal("catalog");
			var forceLoad:Boolean = LoadSongsEvent(event).forceLoad;
			lastUpdate = (so.data.lastUpdate == undefined)?-1:so.data.lastUpdate;
			if(so.data.songs == undefined || forceLoad) so.data.songs = new Array();
			if(forceLoad) lastUpdate = -1;

		    var delegate : CatalogDelegate = new CatalogDelegate( this );
		    delegate.getSongs(model.user.username, model.user.password, partpos, partlength, lastUpdate);
		}
	
		public function result( event : Object ) : void
		{				
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			var result:Object = event.result;
			var songs:Array = result.songs as Array;
			var updateTime:int = result.updateTime;
			
			if(partpos == 0) newsongs = new Array();
			newsongs = newsongs.concat(songs);
			if(songs.length == partlength) 
			{
				partpos += partlength;
				var delegate : CatalogDelegate = new CatalogDelegate( this );
				delegate.getSongs(model.user.username, model.user.password, partpos, partlength, lastUpdate);
			}
			else
			{
				trace("number of new songs:"+ newsongs.length);
				var so:SharedObject = SharedObject.getLocal("catalog");
				
				var allSongs:Array = so.data.songs.concat(newsongs);
				
				allSongs.sortOn(["artist", "album", "track"]);
			
				model.all_songs_arr = allSongs;
				model.songs_ac = new ArrayCollection(allSongs);
				
				model.albums_hash = new Object();
				model.albums_ac = new ArrayCollection();
				
				parseSongs(allSongs);
	

				so.data.songs = allSongs;
				so.data.lastUpdate = updateTime;

				so.flush();
				CairngormEventDispatcher.getInstance().dispatchEvent(new GetCatalogsEvent());
			}
		}
	
		public function fault( event : Object ) : void
		{
			var faultEvent : FaultEvent = FaultEvent( event );
			Alert.show( "songs could not be retrieved!" );
		}
		
		private function parseSongs(songs:Array):void
		{
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			
			for(var i:int = 0;i<songs.length;i++)
			{
				var song:Object = songs[i];
				var id:int = song.album_id;
				if(model.albums_hash[id] == null){
					model.albums_hash[id] = new Array();
					model.albums_ac.addItem({id:id, name:song.album, artist:song.artist});
				} 
				model.albums_hash[id].push(songs[i]);
			}
		}
	}
}