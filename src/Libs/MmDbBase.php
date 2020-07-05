<?php
namespace Mm\Libs;
abstract class MmDbBase
{
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
	public function query($sql , $key = null , array $parameters = array())
	{
		$data = array();
		$this->saveSql($sql, $parameters);

		$stmt = $this->pdo->prepare($sql);
		if(!$stmt)
		{
        	$this->error[] = array(
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
        $this->affectRowCount = $stmt->rowCount();
        $this->lastInsertId = $this->pdo->lastInsertId();
		$rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($this->pdo->errorCode() != '00000')
        {
       		$err = $this->pdo->errorInfo();
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
	public function execute($sql , array $parameters = array())
	{
		$this->saveSql($sql, $parameters);
		$stmt = $this->pdo->prepare($sql);
		if(!$stmt)
		{
        	$this->error[] = array(
        		'sql' 		=>	$sql,
        		'message'	=>	'Sql Error',
        	);
        	return 0;
		}
		$stmt->execute($parameters);
        $this->affectRowCount = $stmt->rowCount();
        $this->lastInsertId = $this->pdo->lastInsertId();
        if($this->pdo->errorCode() != '00000')
        {
       		$err = $this->pdo->errorInfo();
        	throw new Exception($err[2], $err[0].$err[1]);
        }
		$stmt = null;
        return $this->affectRowCount;
	}
	/**
	 * 获取影响行数
	 * @return integer
	 */
	public function getAffectedRow()
	{
		return $this->affectRowCount;
	}
	/**
	 * 获取上次插入的自增列ID
	 * @return integer
	 */
	public function getInsertId()
	{
		return $this->lastInsertId;
	}
	/**
	 * 数据库实例
	 * @var PDO
	 */
	public $pdo = null;
	protected $sql = array();
	protected $error = array();
	protected $affectRowCount = 0;
	protected $lastInsertId = 0;
	/**
	 * 根据索引获取数据库配置实例
	 * @param index $serverIndex		指定索引
	 * @param array $serverConfig		配置		弱国此参数没有指定，则表示从系统配置中获取mysqlList
	 * @return MmDb
	 */
	public static function getInstanceByIndex($serverIndex , array $serverConfig = null)
	{
		$arr = $serverConfig ? $serverConfig : Mm::getByKey('mysqlList');
		$config = isset($arr[$serverIndex]) ? $arr[$serverIndex] : array();
		if($config)
		{
			return self::getInstance(
				$config['host'],
				$config['user'],
				$config['pass'],
				$config['dbName'],
				$config['port'],
				$config['charset'],
				$config['tablePre']
			);
		}
		return null;
	}
	/**
	 * 获取数据库实例
	 * 
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $dbName
	 * @param string $post
	 * @param string $charset
	 * @param string $tablePre
	 * @return MmDb
	 */
	public static function getInstance($host , $user , $password , $dbName , $post = 3306, $charset = 'utf8' , $tablePre = '')
	{
		try{
			$db = new MmDb();
			$options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
			$dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
			$db->pdo = new PDO($dsn, $user, $password, $options);
			return $db;
		}
		catch(Exception $e)
		{
			throw $e;
			Mm::stop('Mysql connection faile!');
		}
	}
	/**
	 * 根据hash的关键数值分配到服务器
	 * 
	 * @param string $key
	 * @param array $serverConfig
	 * @return KgDb
	 */
	public static function getInstanceByHashKey($key , array $serverConfig = null)
	{
		$config = self::getServerConfigBy($key , $serverConfig);
		if($config)
		{
			return self::getInstance(
				$config['host'],
				$config['user'],
				$config['pass'],
				$config['dbName'],
				$config['port'],
				$config['charset'],
				$config['tablePre']
			);
		}
		return null;
	}
	/**
	 * 保存sql
	 * 
	 * @param string $sql
	 * @param array $parameters
	 * @return string
	 */
	private function saveSql($sql , $parameters)
	{
		if($parameters)
			$this->sql[] = str_replace(array_keys($parameters), array_values($parameters), $sql);
		else
			$this->sql[] = $sql;
		return $sql;
	}
	/**
     * 按照一致性哈希算法的规则，计算指定的 $str 落在 数据库的哪个索引上
     *
     * @param string $str
     * @return array		返回数组配置
     */
    private static function getServerConfigBy($str , array $serverConfig = null)
    {
    	$arr = $serverConfig ? $serverConfig : Mm::getByKey('mysqlList');
        if (empty($arr)) return array();
        
        if ('' === $str) return array();
        
        $hashRingNumber = count($arr);    // 环形的数量
        if (1 === $hashRingNumber) return $arr;
        
        $scopeNumber = vsprintf('%u', 4294967296 / $hashRingNumber);    // 按 0 ~ 2^32 分成N份，每份的值范围
        
        $computedHashCode = vsprintf('%u', crc32($str));              // 计算 key 的哈希值
        
        $position = ceil($computedHashCode / $scopeNumber);            // 计算 key 的哈希值落在哪个区间内
        
        $arr = array_values($arr);
        return $arr[$position - 1];
    }
	private function __construct(){}
}