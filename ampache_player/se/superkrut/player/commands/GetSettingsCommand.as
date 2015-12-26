package se.superkrut.player.commands
{
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	import com.adobe.cairngorm.control.CairngormEventDispatcher;
	import com.adobe.cairngorm.model.ModelLocator;
	
	import mx.collections.ArrayCollection;
	import mx.controls.Alert;
	import mx.managers.SystemManager;
	import mx.rpc.IResponder;
	import mx.rpc.events.FaultEvent;
	
	import se.superkrut.player.business.CatalogDelegate;
	import se.superkrut.player.events.GetSettingsEvent;
	import se.superkrut.player.events.LoadSongsEvent;
	import se.superkrut.player.model.PlayerModelLocator;
	import se.superkrut.player.vo.SettingsVo;
	import se.superkrut.player.vo.UpdateVo;
	import se.superkrut.player.vo.UserVo;
	
	public class GetSettingsCommand implements ICommand, IResponder
	{
		public function execute( event : CairngormEvent ): void
		{
		    var model:PlayerModelLocator = PlayerModelLocator.getInstance();
		    var getSettingsEvent:GetSettingsEvent = GetSettingsEvent(event);

		    var delegate : CatalogDelegate = new CatalogDelegate( this );

		    delegate.getSettings(model.user.username, model.user.password);
		}
	
		public function result( event : Object ) : void
		{				
			var res:Object = event.result;
			var visualisations:Array = res.visualisations;
			var model:PlayerModelLocator = PlayerModelLocator.getInstance();
			model.settings.visualisations = ["none"].concat(visualisations);
			model.settings.maxUploadSize = res.max_upload;
			var active:String = model.settings.activeVisualisation;
			for(var i:uint = 0;i<visualisations.length;i++)
			{
				if(visualisations[i] == active)
				{
					model.settings.activeIndex = i+1;
					break;
				}
			}
		}
	
		public function fault( event : Object ) : void
		{
			var faultEvent : FaultEvent = FaultEvent( event );
			Alert.show( "getUpdate failure" );
		}
	}
}