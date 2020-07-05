<?php
namespace Mm;

/**
 * 框架url处理类
 * 		目前支持URL：
 * 			/index.php/ActionClass/ActionFunction/Param1/Param2?get1=val1&get2=val2
 *
 * @author mmfei
 */
class MmUrl
{
	static $url = '';
	static $class = '';
	static $function = '';
	static $arrParam = array();
	static $arrPath = array();
	/**
	 * 参数处理
	 * @return void
	 */
	public static function filter()
	{
		if(self::$url) return ;
		$url =  isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		if(empty($url)) return ;
//		$url = preg_replace("/\/[^\/]*$/", "/?", $url);
		$arr = parse_url($url);
		if($arr)
		{
			self::$url = trim($arr['path'],'/');
			$arrParams = explode('/' , self::$url);
			if($arrParams) {
				$isHasPhp = strpos($arrParams[0],".php");
				if($isHasPhp !== false && $isHasPhp >= 0){
					array_shift($arrParams);//delete filename
				}
			}
			self::$arrPath = $arrParams;
			$c = count($arrParams);
			if($c > 1)
			{
				self::$class = $arrParams[0];
				self::$function = $arrParams[1];
			}
			elseif($c == 1)
			{
				self::$class = $arrParams[0];
//				self::$function = 'index';
			}
			if(isset($arr['query']))
			{
				parse_str($arr['query'],self::$arrParam);
				if(self::$arrParam)
					$_GET = array_merge($_GET , self::$arrParam);
			}
		}
	}
}