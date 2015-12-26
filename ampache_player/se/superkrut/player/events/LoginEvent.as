package se.superkrut.player.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	
	public class LoginEvent extends CairngormEvent
	{
		public static var LOGIN_EVENT:String = "login_event";
		public var username:String;
		public var password:String;
		
		public function LoginEvent(username:String, password:String)
		{
			super(LOGIN_EVENT);
			this.username = username;
			this.password = password;
		}
	}
}