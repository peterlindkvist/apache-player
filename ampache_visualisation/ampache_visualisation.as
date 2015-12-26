package {
	import flash.display.Sprite;
    import flash.display.Graphics;
    import flash.events.Event;
    import flash.media.Sound;
    import flash.media.SoundChannel;
    import flash.media.SoundMixer;
    import flash.net.URLRequest;
    import flash.utils.ByteArray;
    import flash.text.TextField;

	public class ampache_visualisation extends Sprite
	{
		public function ampache_visualisation() 
		{
            addEventListener(Event.ENTER_FRAME, onEnterFrame);
        }

        private function onEnterFrame(event:Event):void {
            var bytes:ByteArray = new ByteArray();
            const PLOT_HEIGHT:int = 200;
            const CHANNEL_LENGTH:int = 256;
			
			if(!SoundMixer.areSoundsInaccessible()){
	            SoundMixer.computeSpectrum(bytes, false, 0);
	            
	            var g:Graphics = this.graphics;
	            
	            g.clear();
	       
	            g.lineStyle(0, 0x444444);
	            g.beginFill(0x444444);
	            g.moveTo(0, PLOT_HEIGHT);
	            
	            var n:Number = 0;
	            
	            for (var i:int = 0; i < CHANNEL_LENGTH; i++) {
	                n = (bytes.readFloat() * PLOT_HEIGHT);
	                g.lineTo(i * 2, PLOT_HEIGHT - n);
	            }
	
	            g.lineTo(CHANNEL_LENGTH * 2, PLOT_HEIGHT);
	            g.endFill();
	 
	            g.lineStyle(0, 0x990000);
	            g.beginFill(0x990000, 0.5);
	            g.moveTo(CHANNEL_LENGTH * 2, PLOT_HEIGHT);
	            
	            for (i = CHANNEL_LENGTH; i > 0; i--) {
	                n = (bytes.readFloat() * PLOT_HEIGHT);
	                g.lineTo(i * 2, PLOT_HEIGHT - n);
	            }
	  
	            g.lineTo(0, PLOT_HEIGHT);
	            g.endFill();
	  		}
        }
    }
}