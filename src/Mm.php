<?php
namespace Mm;
/**
 * 框架入口类
 * @author mmfei
 */
class Mm
{
	/**
	 * 脚本开始执行时间
	 * @var integer
	 */
	static $startTime = 0;
	/**
	 * app配置
	 * @var array
	 */
	static $config = null;
	/**
	 * 应用入口
	 * @return void
	 */
	public static function run()
	{
		try
		{
			self::runFirst();
			$appName = APP_NAME;
			$app = new $appName();
			/* @var $app MmAppBase */
			$app->run();
			self::runAfter();
		}
		catch(Exception $e)
		{
			self::runAfter();
			throw $e;
		}
	}
	public static function runFirst(){
		self::runBefore();
		self::_initConfig();
	}
	/**
	 * 执行代码 - 不初始化app
	 * @return void
	 */
	public static function runWithoutApp()
	{
		self::runBefore();
		self::_initConfig();
		$appName = APP_NAME;
	}
	/**
	 * 结束应用
	 * @param string $string
	 * @return void
	 */
	public static function stop($string = '')
	{
		if($string)
			echo $string;
		self::runAfter();
		die();
	}
	/**
	 * 设置app配置
	 * @param array $arrConfig
	 * @return void
	 */
	public static function setConfig(array $arrConfig = null)
	{
		self::$config = $arrConfig;
	}
	/**
	 * 获取app配置key对应的数据
	 * @param string $key		支持/划分,只支持三层
	 * @return mixed
	 */
	public static function getByKey($key)
	{
		$arr = explode('/', $key);
		$count = count($arr);
		switch($count)
		{
			case 1:
				return isset(self::$config[$key]) ? self::$config[$key] : null;
				break;
			case 2:
				return isset(self::$config[$arr[0]][$arr[1]]) ? self::$config[$arr[0]][$arr[1]] : null;
				break;
			case 3:
				return isset(self::$config[$arr[0]][$arr[1]][$arr[2]]) ? self::$config[$arr[0]][$arr[1]][$arr[2]] : null;
				break;
			case 4:
				return isset(self::$config[$arr[0]][$arr[1]][$arr[2]][$arr[3]]) ? self::$config[$arr[0]][$arr[1]][$arr[2]][$arr[3]] : null;
				break;
			default:
				throw new Exception('Arguments Error!');
				$config = isset(self::$config[$arr[0]][$arr[1]][$arr[2]][$arr[3]]) ? self::$config[$arr[0]][$arr[1]][$arr[2]][$arr[3]] : null;
				if($config)
				{
					$arr = array_slice($arr, 3);
					
					//TO DO
				}
		}
	}
	public static function initConfig()
	{
		self::_initConfig();
	}
	/**
	 * 初始化配置
	 * @return void
	 */
	private static function _initConfig()
	{
		static $defaultConfig = null;
		if(!is_null($defaultConfig))
			return ;
		$mmRootDir = dirname(__FILE__).'/..';
		//使用默认配置
		$defaultConfig = array(
			'run' => array(
				'class'=>'AdminLogin',
				'function'=>'Login',
			),
//			'handler' => array(//错误捕捉函数
//				'error' => 'MmErrorHandler',//错误捕捉函数
//				'exception' => 'MmExceptionHandler',//异常捕捉函数
//			),
			'errorReportLevel' => 0,//错误提示级别
			'define' => array(
				'MM_ROOT_DIR' => $mmRootDir,
				'MM_APP_ROOT' => './app',
				'CHARSET_PAGE' => 'utf-8',
			),
			'app' => 'DefaultApp',
			'actions' => array(
				'*' => 1,//所有接口是否都开放
			),
//			'db' => array(
//			),
		);
		
		foreach($defaultConfig as $k => $a)
		{
			if(is_array($a))
			{
				foreach($a as $kk => $aa)
				{
					if(is_array($aa))
					{
						foreach($aa as $kkk => $v)
							if(!isset(self::$config[$k][$kk]))
								self::$config[$k][$kk][$kkk] = $v;
					}
					elseif(!isset(self::$config[$k][$kk]))
					{
						self::$config[$k][$kk] = $aa;
					}
				}
			}
			elseif(!isset(self::$config[$k]))
			{
				self::$config[$k] = $a;
			}
		}
		self::_CheckConfig(self::$config);
		
		$config = self::$config;
		$appName = $config['app'];
		defined('APP_NAME') || define('APP_NAME' , $appName);
		
		$define = isset($config['define']) ? $config['define'] : array();
		
		foreach($define as $k => $v)
			defined($k) || define($k , $v);

		header('content-type:text/html;charset='.CHARSET_PAGE);
		
		self::_IncludeDo();
		if(isset(self::$config['errorReportLevel'])) error_reporting(self::$config['errorReportLevel']);
		if(isset(self::$config['handler']['error'])) set_error_handler(self::$config['handler']['error']);
		if(isset(self::$config['handler']['exception'])) set_exception_handler(self::$config['handler']['exception']);
		
		$timezone = isset(self::$config['timezone']) ? self::$config['timezone'] : "Etc/GMT-8";
		date_default_timezone_set($timezone);
	}
	/**
	 * 加载必要类文件
	 * @return void
	 */
	private static function _includeDo()
	{
		$arrIncludeMap = include MM_CORE_ROOT .'/MmAutoLoadConfig.php'; 
		include MM_CORE_ROOT.'/libs/MmImport.php';
		\Mm\Libs\MmImport::addMapByArray($arrIncludeMap);
		foreach($arrIncludeMap as $k => $v)
		{
			\Mm\Libs\MmImport::includeFile($v);
		}
		$arrIncludeFile = self::getByKey('autoIncludeFile');
		if($arrIncludeFile && is_array($arrIncludeFile))
			foreach($arrIncludeFile as $key => $file)
			\Mm\Libs\MmImport::includeFile($file);
		spl_autoload_register(array('MmImport','autoLoad',));
	}
	/**
	 * 检查配置
	 * @param array $config
	 * @throws Exception
	 */
	private static function _checkConfig($config)
	{
		$exceptionMessage = '';
		if(!isset($config['app']))
			$exceptionMessage = "没有指定应用入口";

		if($exceptionMessage)
			throw new Exception($exceptionMessage);
	}
	/**
	 * app运行之前调用的代码
	 * @return void
	 */
	private static function runBefore()
	{
		self::$startTime = microtime(true);
		ob_start();
	}
	/**
	 * app运行之后调用的代码
	 * @return void
	 */
	public static function runAfter()
	{
		if(MM_DEBUG)
		{
			$arr = self::getByKey('debugClass');
			$class = $function = '';

			if($arr)
			{
				$class = isset($arr['class']) ? $arr['class'] : '';
				$function = isset($arr['show']) ? $arr['show'] : '';
			}

			if($class && $function)
			{
				$config = self::getByKey('switch');
				$default = isset($config['*']) ? $config['*'] : false;
				$traceList = self::getTrace();
				if(isset($config['sql']) && $config['sql'])
					$traceList['sql'] = MmDatabase::$sql;
				if(isset($config['includeFile']) && $config['includeFile'])
					$traceList['includeFile'] = MmImport::$loadedMap;
				if(isset($config['traceSystemVar']) && $config['traceSystemVar'])
					foreach($config['traceSystemVar'] as $key => $open)
						if($open)
						{
							$var = '_'.strtoupper($key);
							$traceList[$key] = eval("return $$var;");
						}
				if(isset($config['time']) && $config['time'])
					$traceList['time'] = round(microtime(true) - self::$startTime , 4);
				if(isset($config['traceDefine']) && $config['traceDefine']) {$a = get_defined_constants(1); $traceList['define'] = $a['user'];};
				isset($config['trace']) && $config['trace'] && call_user_func_array(array($class,$function),$traceList);
				isset($config['backTrace']) && $config['backTrace'] && call_user_func_array(array($class , $function),array(debug_backtrace()));
			}
		}
		$content = ob_get_contents();
		$length = strlen($content);
		Header("Content-Length: {$length}\r\n");
		ob_flush();
	}
	/**
	 * 增加调试信息
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function addTrace($key , $value)
	{
		if(!MM_DEBUG) return array();
		static $class = null;
		static $function = '';
		
		if(is_null($class))
		{
			$arr = self::getByKey('debugClass');
			$class = isset($arr['class']) ? $arr['class'] : '';
			if($class)
				$function = isset($arr['in']) ? $arr['in'] : '';
			if($function)
				return call_user_func_array(array($class , $function),array($key , $value));
		}
		elseif($class)
			return call_user_func_array(array($class , $function),array($key , $value));
		else 
			return ;
	}
	/**
	 * 获取调试堆栈
	 * @return array
	 */
	public static function getTrace()
	{
		if(!MM_DEBUG) return;
		static $class = null;
		static $function = '';
		
		if(is_null($class))
		{
			$arr = self::getByKey('debugClass');
			$class = isset($arr['class']) ? $arr['class'] : '';
			if($class)
				$function = isset($arr['out']) ? $arr['out'] : '';
			if($function)
				return call_user_func_array(array($class , $function),array());
		}
		elseif($class)
			return call_user_func_array(array($class , $function),array());
		else 
			return ;//没设置debugSql
	}
}