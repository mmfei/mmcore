<?php
namespace Mm\Libs;
/**
 * ActiveRecord类，完成ActiveRecord基础操作
 * @author mmfei
 */
abstract class MmActiveRecord extends ArrayObject
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
				$this->_data[$pk] = $lastInsertId;
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
				$this->_data = array();
				return $this->deleteByPk($id);
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
//		if(property_exists($this,$name))
//			return $this->$name;
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
		$this->checkFields($data);
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
		$this->checkFields($data);
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
		$this->checkFields($data);
		return MmDatabase::insertBy($this->_getTable(), $data , $lastInsertId);
	}
	/**
	 * 插入数据
	 * @param array $data
	 * @param integer $lastInsertId	更新后的主键
	 * @return integer
	 */
	public function insertTMulti(array $data , &$lastInsertId = null)
	{
		//$this->checkFields($data);
		return MmDatabase::insertTableMulti($this->_getTable(), $data , $lastInsertId);
	}
	
	/**
	 * 插入数据 -- 忽略主键
	 * @param array $data
	 * @param integer $lastInsertId	更新后的主键
	 * @return integer
	 */
	public function insertIgnore(array $data , &$lastInsertId = null)
	{
		$this->checkFields($data);
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
	 * 获取全表数据
	 * 
	 * @param string $key
	 * @param array $arrSelect
	 * @param array $arrOrder
	 * @return array
	 */
	public function getAll($key = null , array $arrSelect = null , array $arrOrder = null)
	{
		return MmDatabase::getAll($this->_getTable() , $key, $arrSelect , $arrOrder);
	}
	/**
	 * 检测写入
	 * @param array $data
	 * @return void
	 */
	private function checkFields($data)
	{
		$arr = array_diff(array_keys($data), array_keys($this->rules()));
		if($arr)
			throw new Exception('Fields ['.join(',',$arr).'] are not exists in table '.$this->_getTable().'!');
	}
}