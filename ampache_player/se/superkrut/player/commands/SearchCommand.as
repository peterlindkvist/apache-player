package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	
	import mx.collections.ArrayCollection;
	
	import se.superkrut.player.events.SearchEvent;
	import se.superkrut.player.model.PlayerModelLocator;
	import se.superkrut.player.vo.UpdateVo;
	
	public class SearchCommand implements ICommand
	{
		public function execute( event : CairngormEvent ): void
		{
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			var searchEvent:SearchEvent = SearchEvent(event);
			var searchStr:String = searchEvent.searchStr.toLowerCase();
			if(searchStr == "")
			{
				model.songs_ac = new ArrayCollection(model.all_songs_arr);
			}
			else
			{
				var res:Array = new Array();
				var allSongs:Array = model.all_songs_arr;
				var t:Number = (new Date()).getTime();
				for(var i:uint = 0;i<allSongs.length;i++)
				{
					var song:Object = allSongs[i];
	/*				if(song.title.toLowerCase().indexOf(searchStr) != -1 ||
						song.artist.toLowerCase().indexOf(searchStr) != -1 ||
						song.album.toLowerCase().indexOf(searchStr) != -1 ||
						song.genre.toLowerCase().indexOf(searchStr) != -1)
					{*/
					if((song.title+":::"+song.artist+":::"+song.album+":::"+song.genre).toLowerCase().indexOf(searchStr) != -1)
					{
						res.push(song);
					}
				}
				trace("filter time ("+searchStr+"):"+((new Date()).getTime() - t)+"ms");
				model.songs_ac = new ArrayCollection(res);
			}
		}
	}
}