<?php
namespace Mm;

/**
 * app基类
 * @author mmfei
 */
class MmAppBase
{
	/**
	 * 应用入口
	 * @return mixed
	 */
	public function run()
	{
		MmUrl::filter();
		$class = MmUrl::$class;
		$function = MmUrl::$function;
		if(empty($class))
		{
			$arr = Mm::getByKey('run');
			$class = isset($arr['class']) && $arr['class'] ? $arr['class'] : '';
			if(empty($class))
				Mm::stop('ActionName is Empty!');

			$function = isset($arr['function']) && $arr['function'] ? $arr['function'] : '';
		}
		$this->_checkActions($class , $function);
		if($function)
			return $this->_runActionClass($class , $function);
		else 
			return $this->_runAction($class);
	}
	/**
	 * 检测接口权限
	 * 
	 * @param string $class
	 * @param string $function
	 * @return void
	 */
	private function _checkActions($class , $function)
	{
		if(!$this->_isAllowActions($class, $function))
			Mm::stop('Access Deny !!!');
	}
	/**
	 * 获取访问接口的权限
	 * 
	 * @param string $class
	 * @param string $function
	 * @return boolean
	 */
	private function _isAllowActions($class , $function)
	{
		$arr = Mm::getByKey('actions');
		$default = $arr['*'];
		if(isset($arr[$class]))
		{
			if(is_array($arr[$class]))
			{
				return isset($arr[$class][$function]) ? $arr[$class][$function] : (isset($arr[$class]['*']) ? $arr[$class]['*'] : $default);
			}
			else
			{
				return $arr[$class];
			}
		}
		
		return $default;
	}
	/**
	 * 执行接口类 
	 * @param string $class
	 * @param string $function
	 * @return mixed
	 */
	protected function _runActionClass($class , $function)
	{
		$func = "action".ucfirst($function);
		$class = ucfirst($class)."Action";
		if(file_exists(MM_APP_ACTION_DIR.'/'.$class.'.php'))
		{
			MmImport::addMap($class, MM_APP_ACTION_DIR.'/'.$class.'.php');
			return call_user_func_array(array($class , $func), MmUrl::$arrParam);
		}
		else
		{
			echo "No Action Found!";
		}
	}
	/**
	 * 执行App接口方法
	 * @param string $action
	 * @return mixed
	 */
	protected function _runAction($action)
	{
		$func = "action".ucfirst($action);
		return call_user_func(array(APP_NAME , $func) , MmUrl::$arrParam);
	}
	public function __call($func , $args)
	{
		echo "Access Deny!";
	}
}