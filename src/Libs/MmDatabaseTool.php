<?php
namespace Mm\Libs;
/**
 * 数据库工具
 * @author mmfei<wlfkongl@163.com>
 */
class MmDatabaseTool extends MmDatabase
{
	/**
	 * 获得表结构
	 * @param string $table
	 * @return array
	 */
	public static function GetTableCols($table)
	{
		static $arrTables = array();
		if(isset($arrTables[$table])) return $arrTables[$table];
		$sql = 'Show Full Fields From `'.$table.'`';
		$arr = self::Query($sql);
		foreach($arr as $a)
			$arrTables[$table][$a['Field']] = $a;
		return $arrTables[$table];
	}
	/**
	 * 获取所有表
	 * @return array(
	 * 		tableName => tableName,
	 * 		tableName => tableName,
	 * 		tableName => tableName,
	 * 		...
	 * )
	 */
	public static function GetTables()
	{
		static $tables = array();
		if($tables) return $tables;
		$sql = 'Show tables';
		$arr = self::Query($sql);
		foreach($arr as $a)
		{
			$t = array_pop($a);
			$tables[$t] = $t;
		}
		return $tables;
	}
}