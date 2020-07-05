<?php
namespace Mm\Libs\Ext;
/**
 * memcached实例
 * @author mmfei
 */
class MmMemcached
{
	/**
	 * 获取memcache实例
	 * @param array | string $arrServerList 服务器或者是host 	array(array(host,port,weigh,),...)
	 * @param array $arrOptionList 配置
	 * @return Memcached
	 */
	public static function getMemcached($arrServerList , array $arrOptionList = array())
	{
		$memcached = new Memcached();
		$memcached->addServers($arrServerList);
		$memcached->setOptions($arrOptionList);
		return $memcached;
	}
	/**
	 * 获取memcached实例
	 * @param string $host
	 * @param integer $port
	 * @param integer $weight
	 * @param array $arrOptionList 配置
	 * @return Memcached
	 */
	public static function getMemcachedByHost($host , $port = 11211 , $weight = 10 , array $arrOptionList = array())
	{
		return self::getMemcached(array($host,$port,$weight,) , $arrOptionList);
	}
}