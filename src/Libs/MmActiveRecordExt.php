<?php
namespace Mm\Libs;
/**
 * ActiveRecord扩展类
 * @author mmfei
 */
abstract class MmActiveRecordExt extends MmActiveRecord
{
	/**
	 * 获取多行表数据
	 * 
	 * @param array $arrWhere				条件 array('Col1'=>'Condition','Col2'=>'Condition',),
	 * @param array $arrOrder				排序 array('Col1 Desc','Col2 Asc'),
	 * @param array $arrFields				需要的字段 array('Col1' , 'Col2',),
	 * @param string $key					索引字段名称
	 * @param integer $page					分页,第几页
	 * @param integer $pageSize				每页显示多少条
	 * @param integer $count				符合条件的记录总数
	 * @return array(
	 * 		key => array(),
	 * 		key => array(),
	 * 		key => array(),
	 * 		...
	 * )
	 */
	public function getListBy(array $arrWhere , array $arrOrder = null , $arrFields = null , $key = null , $page = null , $pageSize = null , &$count = 0)
	{
		$argvCount = func_num_args();
		if($argvCount > 7)
			return MmDatabase::getListBy($this->_getTable() , $key , $arrFields ,$arrWhere , $arrOrder , $page , $pageSize , $count);
		if($argvCount > 5)
			return MmDatabase::getListBy($this->_getTable() , $key , $arrFields ,$arrWhere , $arrOrder , $page , $pageSize);
		return MmDatabase::getListBy($this->_getTable() , $key , $arrFields ,$arrWhere , $arrOrder);
	}
	/**
	 * 获取多行表数据
	 * 
	 * @param string $strWhere				条件
	 * @param array $arrOrder				排序 array('Col1 Desc','Col2 Asc'),
	 * @param array $arrFields				需要的字段 array('Col1' , 'Col2',),
	 * @param string $key					索引字段名称
	 * @param integer $page					分页,第几页
	 * @param integer $pageSize				每页显示多少条
	 * @param integer $count				符合条件的记录总数
	 * @return array(
	 * 		key => array(),
	 * 		key => array(),
	 * 		key => array(),
	 * 		...
	 * )
	 */
	public function getListByWhere($strWhere , array $arrOrder = null , $arrFields = null , $key = null , $page = null , $pageSize = null , &$count = 0)
	{
		$argvCount = func_num_args();
		if($argvCount > 7)
			return MmDatabase::getListByWhere($this->_getTable() , $key , $arrFields ,$strWhere , $arrOrder , $page , $pageSize , $count);
		if($argvCount > 5)
			return MmDatabase::getListByWhere($this->_getTable() , $key , $arrFields ,$strWhere , $arrOrder , $page , $pageSize);
		return MmDatabase::getListByWhere($this->_getTable() , $key , $arrFields ,$strWhere , $arrOrder);
	}
}
