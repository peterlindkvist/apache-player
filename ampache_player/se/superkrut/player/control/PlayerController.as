package se.superkrut.player.control
{
	import com.adobe.cairngorm.control.FrontController;
	
	import se.superkrut.player.commands.AddRandomCommand;
	import se.superkrut.player.commands.CoverDragCommand;
	import se.superkrut.player.commands.FoldersDragCommand;
	import se.superkrut.player.commands.GetCatalogsCommand;
	import se.superkrut.player.commands.GetFoldersCommand;
	import se.superkrut.player.commands.GetSettingsCommand;
	import se.superkrut.player.commands.LoadPlaylistCommand;
	import se.superkrut.player.commands.LoadSongsCommand;
	import se.superkrut.player.commands.LoginCommand;
	import se.superkrut.player.commands.SavePlaylistCommand;
	import se.superkrut.player.commands.SearchCommand;
	import se.superkrut.player.commands.SongSelectCommand;
	import se.superkrut.player.commands.UploadCommand;
	import se.superkrut.player.events.AddRandomEvent;
	import se.superkrut.player.events.CoverDragEvent;
	import se.superkrut.player.events.FoldersDragEvent;
	import se.superkrut.player.events.GetCatalogsEvent;
	import se.superkrut.player.events.GetFoldersEvent;
	import se.superkrut.player.events.GetSettingsEvent;
	import se.superkrut.player.events.LoadPlaylistEvent;
	import se.superkrut.player.events.LoadSongsEvent;
	import se.superkrut.player.events.LoginEvent;
	import se.superkrut.player.events.SavePlaylistEvent;
	import se.superkrut.player.events.SearchEvent;
	import se.superkrut.player.events.SongSelectEvent;
	import se.superkrut.player.events.UploadEvent;
	   
   
   public class PlayerController extends FrontController
   {
    	public function PlayerController()
    	{
	    	initialiseCommands();
		}
		
		public function initialiseCommands() : void
		{
			addCommand(LoginEvent.LOGIN_EVENT, LoginCommand );
			addCommand(LoadSongsEvent.LOAD_SONGS_EVENT, LoadSongsCommand );
			addCommand(GetCatalogsEvent.GET_CATALOGS_EVENT, GetCatalogsCommand);
			addCommand(GetFoldersEvent.GET_FOLDERS_EVENT, GetFoldersCommand);
			addCommand(SongSelectEvent.SONG_SELECT_EVENT, SongSelectCommand);
			addCommand(FoldersDragEvent.FOLDERS_DRAG_EVENT, FoldersDragCommand);
			addCommand(CoverDragEvent.COVER_DRAG_EVENT, CoverDragCommand);
			addCommand(GetSettingsEvent.GET_SETTINGS_EVENT, GetSettingsCommand);
			addCommand(SearchEvent.SEARCH_EVENT, SearchCommand);
			addCommand(LoadPlaylistEvent.LOAD_PLAYLIST_EVENT, LoadPlaylistCommand);
			addCommand(SavePlaylistEvent.SAVE_PLAYLIST_EVENT, SavePlaylistCommand);
			addCommand(UploadEvent.UPLOAD_EVENT, UploadCommand);
			addCommand(AddRandomEvent.ADD_RANDOM_EVENT, AddRandomCommand);
		}
	}
}
