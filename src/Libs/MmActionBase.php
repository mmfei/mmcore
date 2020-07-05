<?php
namespace Mm\Libs;
/**
 * 接口基类
 * @author mmfei
 */
class MmActionBase
{
	public function __call($func , $args)
	{
		echo "Access Deny ! No Actions ! ";
	}
	/**
	 * 获取post 或者 get的参数
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function pg($key , $default = null)
	{
		return isset($_POST[$key]) ? $_POST[$key] : (isset($_POST[$key]) ? $_POST[$key] : $default);
	}
}