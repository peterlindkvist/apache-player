package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	import se.superkrut.player.model.PlayerModelLocator;
	
	public class AddRandomCommand implements ICommand
	{
		public function execute( event : CairngormEvent ): void
		{
			trace("exec random");
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			var songs:Array = new Array();
			var allsongs:Array = model.songs_ac.source;
			var len:uint = allsongs.length;
			var adds:int = model.settings.random_adds;
			for(var i:uint = 0;i<adds;i++)
			{
				var rnd:uint = Math.round(Math.random()*Number.MAX_VALUE)%len;
				model.playlist_active_ac.addItem(allsongs[rnd]);
			}
		}
	}
}