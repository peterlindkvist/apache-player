package se.superkrut.player.util
{
	import flash.events.*;
	import flash.net.FileFilter;
	import flash.net.FileReference;
	import flash.net.FileReferenceList;
	import flash.net.URLRequest;
	
	import se.superkrut.player.model.PlayerModelLocator;
	import mx.preloaders.DownloadProgressBar;
	import mx.collections.ArrayCollection;
	import flash.net.URLRequestMethod;
	import flash.net.URLVariables;
	 
	public class CustomFileReferenceList extends FileReferenceList
	 {
		public static var LIST_COMPLETE:String = "listComplete";
	    private var uploadURL:URLRequest;
	    private var pendingFiles:Array;
	    private var model:PlayerModelLocator;
	
	    public function CustomFileReferenceList() {
			model = PlayerModelLocator.getInstance();
	        uploadURL = new URLRequest();
	        uploadURL.url = "upload.php";


	        uploadURL.method  = URLRequestMethod.POST;
	        initializeListListeners();
	    }
	
	    private function initializeListListeners():void {
	        addEventListener(Event.SELECT, selectHandler);
	        addEventListener(Event.CANCEL, cancelHandler);
	    }
	
	    public function getMp3TypeFilter():FileFilter {
            return new FileFilter("Audio (*.mp3)", "*.mp3");
        }
	 
 
	    private function addPendingFile(file:FileReference):void {
	        trace("addPendingFile: name=" + file.name+":"+model.upload.fileObjs_ac.length);
		    model.upload.pendingFiles.push(file);
		    model.upload.fileObjs_ac.addItem({name:file.name, file:file, bytes:"-", total:"-", progress:"0 %"});
		    
	        file.addEventListener(Event.OPEN, openHandler);
	        file.addEventListener(Event.COMPLETE, completeHandler);
	        file.addEventListener(IOErrorEvent.IO_ERROR, ioErrorHandler);
	        file.addEventListener(ProgressEvent.PROGRESS, progressHandler);
	        file.addEventListener(SecurityErrorEvent.SECURITY_ERROR, securityErrorHandler);
			
			var data:URLVariables = new URLVariables();
  	        data.catalog = model.upload.catalog;
  	        data.folder = model.upload.folder;
  	        data.user = model.user.username;
  	        data.password = model.user.password;
  	        
  	        uploadURL.data = data;
  	        
	        file.upload(uploadURL);
	    }
	 
	    private function selectHandler(event:Event):void {
	        trace("selectHandler: " + fileList.length + " files");
	        model.upload.pendingFiles = new Array();
   	        model.upload.fileObjs_ac = new ArrayCollection();
   	        model.upload.done = 0;
	        var file:FileReference;
	        for (var i:uint = 0; i < fileList.length; i++) {
	            file = FileReference(fileList[i]);
	            addPendingFile(file);
	        }
	    }
	 
	    private function cancelHandler(event:Event):void {
	        trace("cancelHandler: name=");
	    }
	 
	    private function openHandler(event:Event):void {
	        var file:FileReference = FileReference(event.target);
	        trace("openHandler: name=" + file.name);
	    }
	 
	    private function progressHandler(event:ProgressEvent):void {
	        var file:FileReference = FileReference(event.target);
	        setProgress(event);
	        trace("progressHandler: name=" + file.name + " bytesLoaded=" + event.bytesLoaded + " bytesTotal=" + event.bytesTotal+";:"+Math.ceil(100*event.bytesLoaded/event.bytesTotal)+"%"+":"+model.upload.fileObjs_ac.length);
	    }
	    
	    private function setProgress(event:ProgressEvent):void
	    {
	    	 var file:FileReference = FileReference(event.target);
	    	 for(var i:Number = 0;i<model.upload.fileObjs_ac.source.length;i++)
	        {
	        	trace(file.name +"=== "+model.upload.fileObjs_ac.source[i].file+";"+(file === model.upload.fileObjs_ac.source[i].file));
	        	if(file === model.upload.fileObjs_ac.source[i].file)
	        	{
	        		var item:Object = new Object();
	        		item.name = file.name;	  
	        		item.file = file;      		
	        		item.bytes = event.bytesLoaded;
	        		item.total = event.bytesTotal;
	        		item.progress = Math.ceil(100*event.bytesLoaded/event.bytesTotal)+"%";
	        		model.upload.fileObjs_ac.setItemAt(item, i);
	        	}
	        }
	    }
	 
	    private function completeHandler(event:Event):void {
	        var file:FileReference = FileReference(event.target);
	        trace("completeHandler: name=" + model.upload.done +"=="+ model.upload.pendingFiles.length);
	        model.upload.done ++;
	        if(model.upload.done == model.upload.pendingFiles.length){
				dispatchEvent(new Event(LIST_COMPLETE));
	        }
	    }
	 
	    private function httpErrorHandler(event:Event):void {
	        var file:FileReference = FileReference(event.target);
	        trace("httpErrorHandler: name=" + file.name);
	    }
	 
	    private function ioErrorHandler(event:Event):void {
	        var file:FileReference = FileReference(event.target);
	        trace("ioErrorHandler: name=" + file.name);
	    }
	 
	    private function securityErrorHandler(event:Event):void {
	        var file:FileReference = FileReference(event.target);
	        trace("securityErrorHandler: name=" + file.name + " event=" + event.toString());
	    }
	}
}