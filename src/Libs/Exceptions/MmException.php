<?php
namespace Mm\Libs\Exceptions;
/**
 * 异常基类
 * @author mmfei
 *
 */
class MmException extends Exception
{
	/**
	 * 异常
	 * @param string $message
	 * @param integer $code
	 * @return Exception
	 */
	public function __construct($message, $code = 0)
	{
		return parent::__construct($message, $code);
	}
}