package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	import com.adobe.cairngorm.control.CairngormEventDispatcher;
	import com.adobe.cairngorm.model.ModelLocator;
	
	import mx.collections.ArrayCollection;
	import mx.controls.Alert;
	import mx.rpc.IResponder;
	import mx.rpc.events.FaultEvent;
	
	import se.superkrut.player.business.CatalogDelegate;
	import se.superkrut.player.events.LoadSongsEvent;
	import se.superkrut.player.events.LoginEvent;
	import se.superkrut.player.model.PlayerModelLocator;
	import se.superkrut.player.vo.UserVo;
	import se.superkrut.player.events.GetSettingsEvent;
	import flash.net.SharedObject;
	import se.superkrut.player.events.LoadPlaylistEvent;
	
	public class LoginCommand implements ICommand, IResponder
	{
		public function execute( event : CairngormEvent ): void
		{
		    var loginEvent:LoginEvent = LoginEvent(event);

		    var model:PlayerModelLocator = PlayerModelLocator.getInstance();
		    model.user = new UserVo();
		    model.user.username = loginEvent.username;
		    model.user.password = loginEvent.password;
		    
		    var delegate : CatalogDelegate = new CatalogDelegate( this );

		    delegate.getUser(loginEvent.username, loginEvent.password);
		}
	
		public function result( event : Object ) : void
		{				
			var user:ArrayCollection = event.result;
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			
			if(user.length > 0)
			{
				model.user.ampacheUser = user[0];
				
				model.applicationState = PlayerModelLocator.VIEW_PLAYER;
				
				var so:SharedObject = SharedObject.getLocal("settings");
	    		so.data.user = model.user;
	    		so.flush();

				CairngormEventDispatcher.getInstance().dispatchEvent(new GetSettingsEvent());				
				CairngormEventDispatcher.getInstance().dispatchEvent(new LoadSongsEvent());
				CairngormEventDispatcher.getInstance().dispatchEvent(new LoadPlaylistEvent());
			}
			else
			{
				Alert.show("wrong password or missing user");
			}
		}
	
		public function fault( event : Object ) : void
		{
			var faultEvent : FaultEvent = FaultEvent( event );
			Alert.show( "login failure" );
		}
	}
}