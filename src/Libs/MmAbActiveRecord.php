<?php
namespace Mm\Libs;
/**
 * ActiveRecord抽象类，完成ActiveRecord基础操作
 * @author mmfei
 */
abstract class MmAbActiveRecord extends ArrayObject
{
	/**
	 * 获取主键 , 目前只支持单主键
	 * @return string
	 */
	abstract function _getPrimaryKey();
	/**
	 * 列表规则
	 */
	abstract function listRules();
//	public function listRules()
//	{
//		return array(
//			'Columns' => array('Col'=>'Template','Col2',...),
//			Template => array('Head'=>'','Row'=>'',),
//			'Order' => array('',),
//		);
//	}
	/**
	 * 字段属性规则,每个字段都必须定义
	 */
	abstract function rules();
//	public function rules()
//	{
//		return array(
// 			'ColumnName' => array(
//				'Label'=>'名称',
//				'Type'=>'(Text|Label|Password|Checkbox|Select|Radio|Html|TextArea|File|Date)',
//				'Range'=>array('RangValue' => 'Text',...,),
//				'IsRangeMulti' => false,
//				'Table'=>array('TableName'=>'表名','EqualColumn'=>'数值对应的列','NameColumn'=>'显示的列',),
//			),
//			...
//		);
//	}
	/**
	 * 类名
	 */
	abstract function _getClass();
	/**
	 * 获取表
	 */
	abstract function _getTable();
//	{
//		return get_class($this);
//	}
	/**
	 * 数据
	 * @var array
	 */
	private $_data = array();
	/**
	 * 保存数据
	 */
	public function save()
	{
		$rules = $this->rules();
		if($this->_data)
		{
			$pk = $this->_getPrimaryKey();
			$table = $this->_getTable();
			
			if(isset($this->_data[$pk]))
			{
				$data = $this->_data;
				$id = $data[$pk];
				unset($data[$pk]);
				$return = $this->updateByPk($id, $data);
				if($return) return $return;
				$data[$pk] = $id;
				return $this->insertIgnore($data , $return);
			}
			else
			{
				$lastInsertId = 0;
				$this->insert($this->_data , $lastInsertId);
				return $lastInsertId;
			}
		}
		return 0;
	}
	/**
	 * 删除数据
	 * @return integer
	 */
	public function delete()
	{
		$rules = $this->rules();
		if($this->_data)
		{
			$pk = $this->_getPrimaryKey();
			$table = $this->_getTable();
			
			if(isset($this->_data[$pk]))
			{
				$data = $this->_data;
				$id = $data[$pk];
				unset($data[$pk]);
				return $this->deleteByPk($id, $data);
			}
		}
		return 0;
	}
	/**
	 * 初始化
	 * 
	 * @param array $arr
	 */
	public function __construct(array $arr = array())
	{
		$this->_data = $arr;
		return parent::__construct($arr);
	}
	public function __set($name, $value) 
	{
		$rules = $this->rules();
		if(property_exists($this,$name))
			$this->$name = $value;
		elseif(isset($rules[$name]))
			$this->_data[$name] = $value;
		else
			return false;
		return true;
	}
	public function __get($name) 
	{
		if(isset($this->_data[$name]))
			return $this->_data[$name];

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);
		return null;
	}
	public function __isset($name) 
	{
		return isset($this->_data[$name]);
	}
	public function __unset($name) 
	{
		unset($this->_data[$name]);
	}
	/**
	 * 根据主键删除[目前只支持单个子增主键]
	 * @param integer $id
	 * @return integer
	 */
	public function deleteByPk($id)
	{
		return MmDatabase::deleteBy($this->_getTable() , array($this->_getPrimaryKey() => $id,));
	}
	/**
	 * 根据条件删除
	 * @param array $arrWhere
	 * @return integer
	 */
	public function deleteBy(array $arrWhere)
	{
		return MmDatabase::deleteBy($this->_getTable() , $arrWhere);
	}
	/**
	 * 根据条件更新
	 * @param array $arrWhere
	 * @param array $data
	 * @return integer
	 */
	public function updateBy(array $arrWhere , array $data)
	{
		return MmDatabase::updateBy($this->_getTable(), $data , $arrWhere);
	}
	/**
	 * 根据主键更新
	 * @param integer $id
	 * @param array $data
	 * @return integer
	 */
	public function updateByPk($id , $data)
	{
		return MmDatabase::updateBy($this->_getTable(), $data , array($this->_getPrimaryKey() => $id,));
	}
	/**
	 * 插入数据
	 * @param array $data
	 * @param integer $lastInsertId	更新后的主键
	 * @return integer
	 */
	public function insert(array $data , &$lastInsertId = null)
	{
		return MmDatabase::insertBy($this->_getTable(), $data , $lastInsertId);
	}
	/**
	 * 插入数据 -- 忽略主键
	 * @param array $data
	 * @param integer $lastInsertId	更新后的主键
	 * @return integer
	 */
	public function insertIgnore(array $data , &$lastInsertId = null)
	{
		return MmDatabase::insertIgnoreBy($this->_getTable(), $data , $lastInsertId);
	}
	/**
	 * 获取一行数据
	 * @param array $arrWhere
	 * @return array
	 */
	public function getRowByPk($id)
	{
		return MmDatabase::getRowBy($this->_getTable() , null , array($this->_getPrimaryKey() => $id ,));
	}
	/**
	 * 获取一行数据
	 * @param array $arrWhere
	 * @return array
	 */
	public function getRowBy(array $arrWhere)
	{
		return MmDatabase::getRowBy($this->_getTable() , null , $arrWhere);
	}
	/**
	 * 获取表数据
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
}