<?php
namespace Mm\Libs\Ext;
/**
 * memcache实例
 * @author mmfei
 */
class MmMemcache
{
	/**
	 * 缓存变量
	 * @var Memcache
	 */
	static $memcache = null;
	/**
	 * 获取memcache实例
	 * @param array | string $arrServerList 服务器或者是host
	 * @return Memcache
	 */
	public static function getMemcache($arrServerList)
	{
		if(self::$memcache) return self::$memcache;
		
		$m = new Memcache();
		foreach($arrServerList as $arr)
		{
			if(isset($arr['host']))
				$m->addServer($arr['host'], isset($arr['port'])?$arr['port']:11211);
			else
				$m->addServer($arr,11211);
		}
		
		self::$memcache = $m;
		return self::$memcache;
	}
	/**
	 * 获取memcache实例
	 * @param string $host
	 * @param integer $port
	 * @return Memcache
	 */
	public static function getMemcacheByHost($host , $port = 11211)
	{
		return self::getMemcache(array('host'=>$host,'port'=>$port));
	}
	/**
	 * 关闭链接
	 * @return void
	 */
	public static function close()
	{
		if(self::$memcache)
			self::$memcache->close();
	}
}