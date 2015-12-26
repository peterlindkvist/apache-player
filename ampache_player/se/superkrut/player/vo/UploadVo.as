package se.superkrut.player.vo
{
	import mx.collections.ArrayCollection;
	
	[Bindable]
	public class UploadVo
	{
		public var pendingFiles:Array;
		public var fileObjs_ac:ArrayCollection;
		public var done:int;
		public var folder:String;
		public var catalog:String;
	}
}