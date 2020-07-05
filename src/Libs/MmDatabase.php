<?php
namespace Mm\Libs;
/**
 * 数据库链接类
 * @author mmfei
 */
class MmDatabase extends MmMySqlPdo
{
	/**
	 * 获取数据列表
	 * @param string $table				表名
	 * @param string $key				返回数组的索引的字段key名称
	 * @param array $arrSelect			选择列
	 * @param array $arrWhere			条件 , And连接
	 * @param array $arrOrder			排序规则 , 如 : col1 desc
	 * @param integer $page				当前页
	 * @param integer $pageSize			每页显示多少记录
	 * @param integer $count			记录总数
	 * @return array(
	 * 		key => array(),
	 * 		key => array(),
	 * 		key => array(),
	 * 		...
	 * )
	 */
	public static function getListBy($table , $key = null , array $arrSelect = null ,array $arrWhere = null , array $arrOrder = null , $page = null , $pageSize = 10 , &$count = null)
	{
		$arrWhere = self::getWhere($arrWhere);
		$where = $arrWhere ? join(' And ',$arrWhere) : '';
		$argsCount = func_num_args();
		if($argsCount > 7)
			return self::getListByWhere($table , $key , $arrSelect , $where , $arrOrder , $page , $pageSize , $count);
		elseif($argsCount > 5)
			return self::getListByWhere($table , $key , $arrSelect , $where , $arrOrder , $page , $pageSize);
		else
			return self::getListByWhere($table , $key , $arrSelect , $where , $arrOrder);
	}
	/**
	 * 获取数据列表
	 * @param string $table				表名
	 * @param string $key				返回数组的索引的字段key名称
	 * @param array $arrSelect			选择列
	 * @param string $strWhere			条件
	 * @param array $arrOrder			排序规则 , 如 : col1 desc
	 * @param integer $page				当前页
	 * @param integer $pageSize			每页显示多少记录
	 * @param integer $count			记录总数
	 * @return array(
	 * 		key => array(),
	 * 		key => array(),
	 * 		key => array(),
	 * 		...
	 * )
	 */
	public static function getListByWhere($table , $key = null , array $arrSelect = null ,$strWhere = '' , array $arrOrder = null , $page = null , $pageSize = 10 , &$count = null)
	{
		$fields = $arrSelect ? join(',' , $arrSelect) : '*';
		$where = $strWhere ? ' Where '.$strWhere : '';
		$order = $arrOrder ? ' Order By '.join(',',self::getOrder($arrOrder)) : '';
		$argsCount = func_num_args();
		if($argsCount > 5)
		{
			$start = max(($page - 1) * $pageSize , 0);
			$limit = isset($page) ? ' Limit '. $start .' , '.$pageSize : '';
		}
		else
			$limit = '';
		if($argsCount > 7)
		{
			$sql = 'Select count(1) as c From '.$table .$where;
			$row = self::query($sql , $key);
	
			if($row)
			{
				$count = $row[0]['c'];
			}
			else 
			{
				$count = 0;
			}
		}
		else
		{			
			$count = true;
		}
		$data = array();
		if($count)
		{
			$sql = 'Select '.$fields.' From '.$table .$where.$order.$limit;
			$data = self::query($sql , $key);
		}
		return $data;
	}
	/**
	 * 获取全表的数据
	 * @param string $table
	 * @param string $key
	 * @param array $arrSelect
	 * @param array $arrOrder
	 * @return array
	 */
	public static function getAll($table , $key = null , array $arrSelect = null , array $arrOrder = null)
	{
		$fields = isset($arrSelect) ? join(',' , $arrSelect) : '*';
		$order = isset($arrOrder) && $arrOrder ? ' Order By '.join(',',self::getOrder($arrOrder)) : '';
		$sql = 'Select '.$fields.' From '.$table .$order;
		return self::query($sql , $key);
	}
	/**
	 * 根据条件获取一行数据
	 * 
	 * @param string $table		表名
	 * @param array $arrSelect	选择列
	 * @param array $arrWhere	条件
	 * @return array			一条记录
	 */
	public static function getRowBy($table , array $arrSelect = null ,array $arrWhere = null)
	{
		$fields = isset($arrSelect) ? join(',' , $arrSelect) : '*';
		$arrWhere = self::getWhere($arrWhere);
		$where = isset($arrWhere) ? ' Where ' .join(' And ',$arrWhere) : '';
		$sql = 'Select '.$fields.' From '.$table .$where;
		MmDebug::printData($sql);
		$rows = self::query($sql);
		if($rows)
		{
			return $rows[0];
		}
		else 
		{
			return null;
		}
	}
	/**
	 * 根据条件更新
	 * 
	 * @param string $table		表名
	 * @param array $data		更新数据 array(字段名=>需要更新的数值,...)
	 * @param array $arrWhere	更新条件
	 * @return integer			影响行数
	 */
	public static function updateBy($table ,array $data , array $arrWhere = null)
	{
		$set = '';
		$flag = '';
		foreach($data as $key => $value)
		{
			$set.= $flag . '`' .$key . '` = \''.$value.'\'';
			$flag = ' , ';
		}
		$arrWhere = self::getWhere($arrWhere);
		$where = isset($arrWhere) ? ' Where ' .join(' And ',$arrWhere) : '';
		$sql = 'Update '.$table.' Set '.$set.$where;
		return self::execute($sql);
	}
	/**
	 * 累加
	 * 
	 * @param string $table			表名
	 * @param array $data			更新数据 array(字段名=>需要更新的数值,...)
	 * @param array $arrWhere		更新条件
	 * @param boolean $notMinus		是否允许负数
	 * @return integer
	 */
	public static function incrementBy($table , array $data , array $arrWhere = null , $notMinus = false)
	{
		$set = '';
		$flag = '';
		foreach($data as $key => $value)
		{
			if($notMinus)
				$set.= $flag . $key . ' = '.$key.' + '.$value;
			else 
				$set.= $flag . $key . ' = Case When '.$key.' + '.$value.' > 0 Then '.$key.' + '.$value.' Else 0 End';
			$flag = ' , ';
		}
		$arrWhere = self::getWhere($arrWhere);
		$where = isset($arrWhere) ? ' Where ' .join(' And ',$arrWhere) : '';
		$sql = 'Update '.$table.' Set '.$set.$where;
		return self::execute($sql);
	}
	/**
	 * 根据条件删除
	 * @param string $table		表名
	 * @param array $arrWhere	更新条件
	 * @return integer			影响行数
	 */
	public static function deleteBy($table , array $arrWhere = null)
	{
		$arrWhere = self::getWhere($arrWhere);
		$where = isset($arrWhere) ? ' Where ' .join(' And ',$arrWhere) : '';
		$sql = 'Delete From '.$table.$where;
		return self::execute($sql);
	}
	/**
	 * 插入一条记录
	 * @param string $table		表名
	 * @param array $data		更新数据 array(字段名=>需要更新的数值,...)
	 * @param integer $insertId	返回的自增列ID
	 * @return integer			影响行数
	 */
	public static function insertBy($table , array $data , &$insertId = null)
	{
		$fields = '`'.join('`,`' , array_keys($data)).'`';
		$values = $char = '';
		foreach($data as $key => $value)
		{
			$values .= $char . '\''.mysql_escape_string($value).'\'';
			$char = ',';
		}
		$sql = 'Insert Into '.$table.'('.$fields.') values('.$values.')';
		$affect = self::execute($sql);
		if($affect)
		{
			$insertId = self::getInsertId();
			return $affect;
		}
		else
		{
			return 0;
		}
	}
	public function getKeySlashes()
	{
		return '`';
	}// end method getKeySlashes
	/**
	 * 将多行数据批量插入指定的数据表中
	 *
	 * @param string $tableName
	 * @param array $multiColumnAndValue
	 * @param bool $isIgnore
	 * @return bool
	 */
	public static function insertTableMulti($tableName, array $multiColumnAndValue, $isIgnore = false)
	{
		if (empty($multiColumnAndValue))
		{
			throw new \InvalidArgumentException('参数 $multiColumnAndValue 不能为空。');
		}
	
		$firstValues = current($multiColumnAndValue);
		$columns = array_keys($firstValues);
		$keySlashes =self::getKeySlashes();
		$columnList = $keySlashes . implode($keySlashes . ',' . $keySlashes, $columns) . $keySlashes;
	
		$ignore = $isIgnore ? ' IGNORE ' : ' ';
		$sql = "INSERT{$ignore}INTO {$keySlashes}{$tableName}{$keySlashes}({$columnList}) VALUES";
		$tmp = '(' . str_repeat('?, ', count($columns) - 1) . '?)';
		$sql .= str_repeat($tmp. ', ', count($multiColumnAndValue) - 1) . $tmp;
	
		$parameters = array();
		foreach ($multiColumnAndValue as $columnAndValue)
		{
			$parameters = array_merge($parameters, array_values($columnAndValue));
		}
	
		$flag = self::execute($sql,$parameters);
	
		return $flag;
	}// end method insertTableMulti
	/**
	 * 插入一条记录
	 * @param string $table		表名
	 * @param array $data		更新数据 array(字段名=>需要更新的数值,...)
	 * @param integer $insertId	返回的自增列ID
	 * @return integer			影响行数
	 */
	public static function insertIgnoreBy($table , array $data , &$insertId = null)
	{
		$fields = '`'.join('`,`' , array_keys($data)).'`';
		$values = $char = '';
		foreach($data as $key => $value)
		{
			$values .= $char . '\''.mysql_escape_string($value).'\'';
			$char = ',';
		}
		$sql = 'Insert Ignore Into '.$table.'('.$fields.') values('.$values.')';
		$affect = self::execute($sql);
		if($affect)
		{
			$insertId = self::getInsertId();
			return $affect;
		}
		else
		{
			return 0;
		}
	}
	/**
	 * 处理where语句
	 * @param array $arrWhere
	 * @return array
	 */
	private static function getWhere(array $arrWhere = null)
	{
		if($arrWhere)
			foreach($arrWhere as $k => $v)
			{
				if(!is_numeric($k))
				{
					if(is_array($v))
					{
						$arrWhere[$k] = "{$k} In(".join(",",$v).")";
					}
					else 
					{
						if(!is_numeric($v))
							$v = '\''.trim($v,'\'').'\'';
						$arrWhere[$k] = "{$k} = {$v}";
					}
				}
			}
		return $arrWhere;
	}
	/**
	 * 处理order
	 * @param array $arrOrder
	 * @return array
	 */
	private static function getOrder(array $arrOrder = null)
	{
		if($arrOrder)
		{
			$arrOrder1 = array();
			foreach($arrOrder as $key => $value)
			{
				if(is_numeric($key))
					$arrOrder1[] = "$value";
				else
					$arrOrder1[] = "{$key} {$value}";
			}
			return $arrOrder1;
		}
		return $arrOrder;
	}
}