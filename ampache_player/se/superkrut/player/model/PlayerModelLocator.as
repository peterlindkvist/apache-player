package se.superkrut.player.model
{
	import com.adobe.cairngorm.CairngormError;
	import com.adobe.cairngorm.CairngormMessageCodes;
	import com.adobe.cairngorm.model.IModelLocator;
	
	import mx.collections.ArrayCollection;
	import mx.events.TreeEvent;
	
	import se.superkrut.player.vo.UserVo;
	import flash.events.EventDispatcher;
	import flash.events.Event;
	import se.superkrut.player.vo.SettingsVo;
	import flash.net.SharedObject;
	import se.superkrut.player.vo.UploadVo;
	import se.superkrut.player.vo.UpdateVo;
	
	[Bindable]
	public class PlayerModelLocator extends EventDispatcher implements IModelLocator
	{
		private static var instance:PlayerModelLocator;
		public static var VIEW_LOGIN:String = "login";
		public static var VIEW_PLAYER:String = "";
		
		public static var EVENT_SET_PLAYITEM:String = "set_playitem";
		
		public var user:UserVo;
		
		public var albums_hash:Object;
		public var albums_ac:ArrayCollection;
		public var songs_ac:ArrayCollection;
		public var all_songs_arr:Array;
		public var folders_xml:XML;
		public var catalogs_arr:Array;
		public var playlist_active_ac:ArrayCollection;
		public var playlist_local:Array;
		public var playlistNames:Array;
		public var playlists:Object;

		public var selectedElement:Object;
		public var clickTime:Number;
		public var doubleClickTime:Number;
		
		public var openingEvent:TreeEvent;
		
		public var isFolderDraging:Boolean;
		
		public var applicationState:String = VIEW_LOGIN;
		private var _playItem:Object;
		public var settings:SettingsVo;
		public var upload:UploadVo;
		
		public static function getInstance() : PlayerModelLocator 
		{
			if ( instance == null )
				instance = new PlayerModelLocator();

			return instance;
		}

		public  function PlayerModelLocator():void
		{
			if ( instance != null )
			{
				throw new CairngormError(CairngormMessageCodes.SINGLETON_EXCEPTION, "PlayerModelLocator" );
			}
    		instance = this;
    		playlist_active_ac = new ArrayCollection();
    		playItem = null;
    		isFolderDraging = false;
    		settings = new SettingsVo();
   			user = new UserVo();
   			upload = new UploadVo();
   			
    		var so:SharedObject = SharedObject.getLocal("settings");
    		if(so.data.user == undefined)
    		{
    			user.username = "admin";
    			user.password  = "apa";
    		}
    		else
    		{
    			user.username = so.data.user.username;
    			user.password = so.data.user.password;
    		}
		}
		
		public function set playItem(item:Object):void
		{
			_playItem = item;
			dispatchEvent(new Event(EVENT_SET_PLAYITEM));
		}
		
		public function get playItem():Object
		{
			return _playItem;
		}
	}
}