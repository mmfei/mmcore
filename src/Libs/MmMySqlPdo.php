<?php
namespace Mm\Libs;
class MmMySqlPdo
{
	static $sql = array();
	static $error = array();
	static $affectRowCount = 0;
	static $lastInsertId = 0;
	private static $db = null;
	/**
	 * 获取数据配置
	 * @return PDO
	 */
	protected static function getInstance()
	{
		if(isset(self::$db)) return self::$db;
		$mmConfig = Mm::getByKey('db');

		if(!isset($mmConfig['host']))
		{
			Mm::stop('数据库配置错误');
		}
		try
		{
			$options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
			$dsn = "mysql:host={$mmConfig['host']};dbname={$mmConfig['dbName']};charset={$mmConfig['charset']}";
			self::$db = new PDO($dsn, $mmConfig['user'], $mmConfig['pass'], $options);
		}
		catch(Exception $e)
		{
			throw $e;
			Mm::stop('Mysql connection faile!');
		}
		return self::$db;
	}
	/**
	 * 保存sql
	 * 
	 * @param string $sql
	 * @param array $parameters
	 * @return string
	 */
	private static function saveSql($sql , $parameters)
	{
		if($parameters)
			self::$sql[] = str_replace(array_keys($parameters), array_values($parameters), $sql);
		else
			self::$sql[] = $sql;
		return $sql;
	}
	/**
	 * 查询
	 * 
	 * @param string $sql				查询语句
	 * @parma string $key				返回数组下标的字段名
	 * @param array $parameters			替换参数
	 * @return array(
	 * 		array(...),#如果$formatToObject为真,这里返回的是对象
	 * 		array(...),#如果$formatToObject为真,这里返回的是对象
	 * 		array(...),#如果$formatToObject为真,这里返回的是对象
	 * 		...
	 * )
	 */
	public static function query($sql , $key = null , array $parameters = array())
	{
		$data = array();
		self::saveSql($sql, $parameters);

		$stmt = self::getInstance()->prepare($sql);
		if(!$stmt)
		{
        	self::$error[] = array(
        		'sql' 		=>	$sql,
        		'message'	=>	'Sql Error',
        	);
        	return array();
		}
		if($stmt->errorCode())
		{
			throw new Exception($stmt->errorInfo() , $stmt->errorCode() , NULL);
		}
        $stmt->execute($parameters);
        self::$affectRowCount = $stmt->rowCount();
        self::$lastInsertId = self::getInstance()->lastInsertId();
		$rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(self::getInstance()->errorCode() != '00000')
        {
       		$err = self::getInstance()->errorInfo();
        	throw new Exception($err[2], $err[0].$err[1]);
        }
        
 		$stmt = null;
		if($key && $rs)
		{
			foreach($rs as $row)
			{
				if(isset($row[$key]))
					$data[$row[$key]] = $row;
				else 
					$data[] = $row;
			}
		}
		else 
		{
			$data = $rs;
		}
	
        return $data;
	}
	/**
	 * 执行sql
	 * 
	 * @param string $sql
	 * @param array $parameters			替换参数
	 * @return integer
	 */
	public static function execute($sql , array $parameters = array())
	{
		self::saveSql($sql, $parameters);
		$stmt = self::getInstance()->prepare($sql);
		if(!$stmt)
		{
        	self::$error[] = array(
        		'sql' 		=>	$sql,
        		'message'	=>	'Sql Error',
        	);
        	return 0;
		}
		$stmt->execute($parameters);
        self::$affectRowCount = $stmt->rowCount();
        self::$lastInsertId = self::getInstance()->lastInsertId();
        if(self::getInstance()->errorCode() != '00000')
        {
       		$err = self::getInstance()->errorInfo();
        	throw new Exception($err[2], $err[0].$err[1]);
        }
		$stmt = null;
        return self::$affectRowCount;
	}
	/**
	 * 获取影响行数
	 * @return integer
	 */
	public static function getAffectedRow()
	{
		return self::$affectRowCount;
	}
	/**
	 * 获取上次插入的自增列ID
	 * @return integer
	 */
	public static function getInsertId()
	{
		return self::$lastInsertId;
	}
	/**
	 * 分析查询的语句
	 * @param string $sql
	 * @return array
	 */
//	public static function explain($sql = null)
//	{
//		$return = array();
//		if($sql)
//		{
//			$return[$sql] = self::query('Explain '.$sql);
//		}
//		else
//		{
//			foreach(self::$sql as $sql)
//			{
//				$return[$sql] = self::query('Explain '.$sql);
//			}
//		}
//		return $return;
//	}
}