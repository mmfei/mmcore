<?php
namespace Mm\Libs;
/**
 * 参数验证类
 * @author mmfei
 */
class MmValidate
{
	/**
	 * 判断是否邮箱
	 * @param string $email
	 * @return boolean
	 */
	public static function isEmail($email)
	{
		return preg_match("/^[\w\d_\.]+@[\w\d]+(\.[\w\d]+)$/" , $email);
	}
	/**
	 * 验证是否邮箱
	 * @param string $email
	 * @return boolean
	 */
	public static function validateEmail($email)
	{
		if(self::isEmail($email))
			return true;
		$param = array($email,);
		$string = "%s is not a email!";
		return self::_throw($string, $param);
	}
	/**
	 * 判断是否IP
	 * @param string $ip
	 * @return boolean
	 */
	public static function isIp($ip)
	{
		return preg_match("/^[12]?\d?\d(\.[12]?\d?\d){3}$/" , $ip);
	}
	/**
	 * ip地址
	 * @param string $ip
	 * @return boolean
	 */
	public static function validateIp($ip)
	{
		if(self::isIp($ip))
			return true;
		$param = array($ip,);
		$string = "%s is not a IP!";
		return self::_throw($string, $param);
	}
	/**
	 * 是否超出范围
	 * @param double $value
	 * @param double $min
	 * @param double $max
	 * @return boolean
	 */
	public static function isOutOfRange($value , $min , $max)
	{
		if($value == $min || $value == $max) return true;
		return min($min , $value) == $min && max($max , $value) == $max;
	}
	/**
	 * 检测是否超出了范围
	 * @param double $value
	 * @param double $min
	 * @param double $max
	 * @return boolean
	 */
	public static function validateOutOfRange($value , $min , $max)
	{
		if(self::isOutOfRange($value, $min, $max))
			return true;
		$param = array($value , $min , $max,);
		$string = "%s is out of the range(%s , %s)!";
		return self::_throw($string, $param);
	}
	/**
	 * 参数是否为自然数
	 * @param integer $integer
	 * @return boolean
	 */
	public static function isNatural($integer)
	{
		if($integer !== 0 && !is_numeric($integer) && (int)$integer < 0)
			return false;
		return true;
	}
	/**
	 * 检测是否是自然数
	 * @param integer $integer
	 * @return void
	 */
	public static function validateNatural($integer)
	{
		if(self::isNatural($integer))
			return true;
		return self::_throw("%s is not a natural number!", array($integer));
	}
	/**
	 * 抛出参数验证异常
	 * @param string $string
	 * @param array $param
	 * @throws MmValidateException
	 * @return void
	 */
	private static function _throw($string , $param)
	{
		$message = vsprintf($string , $param);
		throw new MmValidateException($message);
	}
}