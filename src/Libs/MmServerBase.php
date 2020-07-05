<?php
namespace Mm\Libs;
/**
 * 接口基类
 * @author mmfei
 */
class MmServerBase
{
	public function __call($func , $args)
	{
		echo "Server Deny ! No Server ! ";
	}
}