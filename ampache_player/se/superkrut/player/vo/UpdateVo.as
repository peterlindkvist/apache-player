package se.superkrut.player.vo
{
	[Bindable]
	public class UpdateVo
	{
		public var updateTime:int;
		public var additionTime:int;
		public var updateCount:int
		public var additionCount:int;
		public var lastUpdate:int;
		
		public function UpdateVo(updateTime:int, additionTime:int, updateCount:int, additionCount:int)
		{
			this.updateTime = updateTime;
			this.additionTime = additionTime;
			this.updateCount = updateCount;
			this.additionCount = additionCount;	
		}
	}
}