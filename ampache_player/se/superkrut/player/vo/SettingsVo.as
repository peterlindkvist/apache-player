package se.superkrut.player.vo
{
	import flash.net.SharedObject;
	
	[Bindable]
	public class SettingsVo
	{
		private var _activeVisualisation:String;
		public var activeIndex:Number;
		public var visualisations:Array;
		public var username:String;
		public var password:String;
		private var _random_adds:int;
		private var so:SharedObject;
		public var maxUploadSize:String;
		
		public function SettingsVo()
		{
			visualisations = new Array();
			so = SharedObject.getLocal("settings");

			if(so.data.settings == undefined)
			{
				so.data.settings = new Object();
				_activeVisualisation = "none";
				_random_adds = 10;
			}
			else
			{
				_activeVisualisation = so.data.settings.activeVisualisation;
				_random_adds = so.data.settings.random_adds;
			}
		}
		
		public function get activeVisualisation():String
		{
			return _activeVisualisation;
		}
		
		public function set activeVisualisation(visual:String):void
		{
			_activeVisualisation = visual;
			so.data.settings.activeVisualisation = _activeVisualisation;
		}
		
		public function get random_adds():int
		{
			return _random_adds;
		}
		
		public function set random_adds(random:int):void
		{
			_random_adds = random;
			so.data.settings.random_adds = _random_adds;
		}
	}
}