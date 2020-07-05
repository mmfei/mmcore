<?php
namespace Mm\Libs;
/**
 * 调试类
 * @author mmfei
 */
class MmDebug
{
	private static $trace = array();
	/**
	 * 增加调试信息
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function addTrace($key , $value)
	{
		self::$trace[$key][] = $value;
	}
	/**
	 * 获取调试信息
	 * @return array
	 */
	public static function getTrace()
	{
		return self::$trace;
	}
	/**
	 * 输出调试信息
	 * 
	 * @param string $className	处理类
	 * @param string $function	处理类
	 * @return void
	 */
	public static function showTrace($className = '' , $function = '')
	{
		$trace = self::getTrace();
		if($className)
		{
			$function = $function ? $function : __FUNCTION__;
			return call_user_func_array(array($className , $function),array($trace));
		}
		else
		{
			$arr = Kg::getByKey('debugClass');
			if(isset($arr['class']) && isset($arr['function']))
			{
				$class = $arr['class'];
				$function = $arr['function'];
				return call_user_func_array(array($class , $function),array($trace));
			}
		}
		
		echo "<pre style='text-align:left;' class='kgDebug'>";
		if($trace) print_r($trace);
		else var_dump($trace);
		echo "</pre>";
	}
	/**
	 * 输出数据 -- 依赖 MM_DEBUG
	 * @param mixed $data
	 * @return void
	 */
	public static function printData($data)
	{
		if(!MM_DEBUG) return ;
		echo '<pre style="text-align:left;" class="debug">';
		if($data) echo htmlspecialchars(print_r($data,true));
		else var_dump($data);
		echo '</pre>';
	}
}