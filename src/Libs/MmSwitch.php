<?php
namespace Mm\Libs;
class MmSwitch
{
	private static $arrConfig = null;
	/**
	 * 获取开关配置
	 * @param string $configKey	预留参数
	 * @return array
	 */
	private static function getSwitchConfig($configKey = '')
	{
		$appName = APP_NAME;
		self::$arrConfig = call_user_func_array(array($appName,getByKey),array('switch'));
		if(is_null(self::$arrConfig)) self::$arrConfig = array('*' => false,);
	}
	/**
	 * 是否打开
	 * @param string $key	开关名称
	 * @return boolean
	 */
	public static function isOpen($key = '*')
	{
		return isset(self::$arrConfig[$key]) ? self::$arrConfig[$key] : (self::$arrConfig['*'] ? self::$arrConfig['*'] : false);
	}
}