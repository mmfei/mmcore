<?php
namespace Mm\Libs;
/**
 * 接口基类
 * @author mmfei
 */
class MmApiBase
{
	public function __call($func , $args)
	{
		echo "Api Deny ! No Api ! ";
	}
}