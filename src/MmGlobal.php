<?php
namespace Mm;

$dir = dirname(__FILE__);
defined('MM_ROOT') || define('MM_ROOT' , $dir.'/..'); //网站根目录,默认为core的上一级目录
defined('MM_CORE_ROOT') || define('MM_CORE_ROOT' , $dir); //框架目录
defined('MM_STATIC_DIR') || define('MM_STATIC_DIR' , MM_ROOT."/static"); //静态文件目录
defined('MM_STATIC_PATH') || define('MM_STATIC_PATH' , "/static"); //静态文件目录
defined('CHARSET_PAGE') || define('CHARSET_PAGE' , 'utf-8');//PHP页面编码
defined('MM_VERSION') || define('MM_VERSION' , '0.1');//core的开发版本
defined('MM_APP_ROOT') || define('MM_APP_ROOT' , $dir.'/../../app');//默认项目的目录
defined('MM_APP_PATH') || define('MM_APP_PATH' , '/app');//默认网站的app项目的目录，基于url
defined('MM_APP_MODELS_DIR') || define('MM_APP_MODELS_DIR' , MM_APP_ROOT.'/modules');//模块目录
defined('MM_APP_DAL_DIR') || define('MM_APP_DAL_DIR' , MM_APP_ROOT.'/dal');//dal目录
defined('MM_APP_ACTION_DIR') || define('MM_APP_ACTION_DIR' , MM_APP_ROOT.'/actions');//actions目录
defined('MM_APP_DATA_DIR') || define('MM_APP_DATA_DIR' , MM_APP_ROOT.'/data');//data目录

defined('MM_DEBUG') || define('MM_DEBUG' , 0);//框架调试开关
defined('TABLE_PRE') || define('TABLE_PRE' , 'mm_');//表名前缀
defined('TPL_PATH') || define('TPL_PATH' , MM_APP_PATH.'/tpl');//模板目录

defined('APP_NAME') || define('APP_NAME' , 'Default');//应用名称

defined('IS_AUTO_ADDSLASHES') || define('IS_AUTO_ADDSLASHES' , 0);//是否自动转义参数
if(IS_AUTO_ADDSLASHES && !get_magic_quotes_gpc())
{
	foreach($_POST as $i => $v)
		if(!is_array($v))
			$_POST[$i] = addslashes($v);
		
	foreach($_GET as $i => $v)
		if(!is_array($v))
			$_GET[$i] = addslashes($v);
		
	foreach($_COOKIE as $i => $v)
		if(!is_array($v))
			$_COOKIE[$i] = addslashes($v);
}

/**
 * 错误捕捉函数
 * @param integer $errNo
 * @param string $errStr
 * @param stirng $errFile
 * @param integer $errLine
 * @return void
 */
function mmErrorHandler($errNo, $errStr, $errFile, $errLine)
{
    if(!(error_reporting() & $errNo))
    {
        return;
    }

    switch ($errNo) {
	    case E_USER_ERROR:
	        echo "<b>用户设定错误</b> [$errNo] $errStr<br />\n";
	        echo " $errFile 行号： $errLine";
	        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
	        echo "<br />\n";
	        exit(1);
	        break;
	    case E_USER_WARNING:
	        echo "<b>警告</b> [$errNo] $errStr<br />\n";
	        echo " $errFile 行号： $errLine";
	        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
	        echo "<br />\n";
	        break;
	
	    case E_USER_NOTICE:
	        echo "<b>提醒</b> [$errNo] $errStr<br />\n";
	        echo " $errFile 行号： $errLine";
	        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
	        echo "<br />\n";
	        break;
	    default:
	        echo "未知错误: [$errNo] $errStr<br />\n";
	        echo " $errFile 行号： $errLine";
	        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
	        echo "<br />\n";
	        break;
    }
    return true;
}
/**
 * 异常处理函数
 * @param string $exception
 * @return void
 */
function mmExceptionHandler($exception) {
  echo "Exception: " , $exception->getMessage(), "\n";
}
