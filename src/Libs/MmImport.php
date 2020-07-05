<?php
namespace Mm\Libs;
/**
 * 加载类 - 负责自动加载框架类和应用的类
 * @author mmfei
 */
class MmImport
{
	static $map = array();
	static $loadedMap = array();
	/**
	 * 配置内容(真实存档的内容)
	 * @return array
	 */
	public static function getConfig()
	{
		return self::$map;
	}
	/**
	 * 获取加载的所有文件
	 * ＠return array
	 */
	public static function getLoadedFile()
	{
		return self::$loadedMap;
	}
	/**
	 * 增加类映射
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public static function addMap($key , $value)
	{
		self::$map[$key] = array('loaded'=>false,'file'=>$value);
	}
	
	/**
	 * 增加类映射
	 * @param array $array
	 * @param string $value
	 * @return void
	 */
	public static function addMapByArray(array $array)
	{
		foreach($array as $key => $value)
			self::addMap($key, $value);
	}
	/**
	 * 加载类
	 * @param string $class
	 * @return boolean
	 */
	public static function includeClassFile($class)
	{
		if(isset(self::$map[$class]))
		{
			if(self::$map[$class]['loaded'] != true)
			{
				if(file_exists(self::$map[$class]['file']))
				{
					include(self::$map[$class]['file']);
					self::$map[$class]['loaded'] = true;
					self::$loadedMap[$class] = self::$map[$class]['file'];
				}
			}
			return true;
		}
	}
	/**
	 * 加载类
	 * @param string $file
	 * @return boolean
	 */
	public static function includeFile($file)
	{
		if(file_exists($file))
		{
			$class = $file;
			foreach(self::$map as $class1 => $a)
			{
				if($a['file'] == $file)
				{
					$class = $class1;
					break;
				}
			}
			if(isset(self::$map[$class]))
			{
				if(self::$map[$class]['loaded'])
					return true;
			}
			else
				self::$map[$class] = array(
					'file' => $file,		
				);
			
			include(self::$map[$class]['file']);
			self::$map[$class]['loaded'] = true;
			self::$loadedMap[$class] = self::$map[$class]['file'];
			return true;
		}
	}
	
	public static function autoLoad($className)
	{		
		static $needRegisterAutoload = true;
		if(self::includeClassFile($className)) return;
		$arr = array(
			MM_APP_ROOT .'/'.$className.'.php',
			MM_CORE_ROOT .'/'.$className.'.php',
			MM_CORE_ROOT .'/libs/'.$className.'.php',
			MM_CORE_ROOT .'/libs/exceptions/'.$className.'.php',
			MM_CORE_ROOT .'/libs/ext/'.$className.'.php',
			MM_CORE_ROOT .'/libs/plugins/'.$className.'/'.ucfirst($className).'.php',
			MM_APP_ACTION_DIR .'/'.$className.'.php',
			MM_APP_DAL_DIR .'/'.$className.'.php',
			MM_APP_MODELS_DIR .'/'.$className.'.php',
		);
		$config = Mm::getByKey('includePath');
		
		if(is_array($config))
			foreach($config as $s)
				$arr[] = $s.'/'.$className.'.php';
		foreach($arr as $filename)
		{
			if(file_exists($filename))
			{
				self::addMap($className, $filename);
				self::includeClassFile($className);
				return;
			}
		}
		
		if($needRegisterAutoload && function_exists('__autoload'))
		{
			$needRegisterAutoload = false;
			spl_autoload_register('__autoload');
		}
	}
}