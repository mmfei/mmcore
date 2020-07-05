<?php
namespace Mm\Libs;
/**
 * 自动生成工具
 * @author mmfei
 */
class MmGenerator
{
	/**
	 * 代码生成器
	 * @return void
	 */
	public static function createProject()
	{
		$dirName = MmHtml::PG('dirName','app1');
		$configFile = MmHtml::PG('configFile','appConfig.php');
		$dirConfig = array(
		);
		$config = array(
			'inputList' => array(
				array(
					'label' => '文件夹名称',
					'list'	=>	array(
						array(
							'name' => 'dirName',
							'value'=> $dirName,
						)
					),
				),
				array(
					'label' => '配置文件',
					'list'	=>	array(
						array(
							'name' => 'configFile',
							'value'=> $configFile,
						)
					),
				),
			),
		);
		MmHtml::GetHtml()
			->InitDefaultJs()
			->InitDefaultCss()
			->FormByConfig($config)
			->Show();
	}
	/**
	 * 生成ActiveRecord工具
	 * @return void
	 */
	public static function ActiveRecord()
	{
		$tableList = MmDatabaseTool::getTables();
		$arrIsCreate = $arrIsConst = array(1=>'是',0=>'否',);
		$isCreate = MmHtml::PG('isCreate',0);
		$isConst = MmHtml::PG('isConst',0);
		$config = array(
			'title' => 'Active Record Creator',
			'inputList' => array(
				array(
					'label' => 'table',
					'list'  => array(
						array(
							'name' => 'table',
							'value' => '',
							'type' => 'select',
							'valueList' => $tableList,
							'selected' => array(MmHtml::POST('table')),
						),
						array(
							'type' => 'label',
							'value' => '前缀',		
						),
						array(
							'name' => 'pre',
							'value' => MmHtml::PG('pre' , TABLE_PRE),		
						),
					),		
				),
				array(
					'label' => 'is create?',
					'list' => array(
						array(
							'name' => 'isCreate',
							'value' => '',
							'type' => 'radio',
							'valueList' => $arrIsCreate,
							'selected' => array($isCreate),
						),
					),		
				),
				array(
					'label' => 'is const?',
					'list' => array(
						array(
							'name' => 'isConst',
							'value' => '',
							'type' => 'radio',
							'valueList' => $arrIsConst,
							'selected' => array($isConst),
						),
					),		
				),
			),		
		);
		$table = MmHtml::POST('table');
		if($table)
		{
			$realTable = $table;
			$arrColumns = MmDatabaseTool::getTableCols($table);
			if(MmHtml::PG('pre'))
			{
				$pre = MmHtml::PG('pre');
				$table = preg_replace("/^{$pre}/" , '' , $table);
			}
			$class = ucfirst(preg_replace_callback("/_(\w)/", function ($m){return strtoupper($m[0]);}, $table)).'DAL';
			$code = self::_getCreateActiveRecord($class , $table, $arrColumns , $realTable , $isConst);
			if($isCreate == 1)
			{
				$file = MM_APP_DAL_DIR .'/'.$class.'.php';
				if(file_put_contents($file, $code))
				{
					MmHtml::GetHtml()->AppendFooter('生成文件成功！');
				}
			}
			MmHtml::GetHtml()->AppendFooter('<pre style="text-align:left;">'.highlight_string($code,true).'</div>');
		}
		MmHtml::GetHtml()->FormByConfig($config)->InitDefaultCss()->InitDefaultJs()->Show();
	}
	/**
	 * 获得生成ActiveRecord模型的文件内容
	 * 
	 * @param string $class
	 * @param string $tableName
	 * @param string $arrColumns
	 * @param string $realTableName
	 * @param boolean $isNeedConst
	 * @return string
	 */
	private static function _getCreateActiveRecord($class , $tableName , $arrColumns , $realTableName , $isNeedConst)
	{
		$pk = $rules = $vars = $const = '';
		foreach($arrColumns as $col => $arr)
		{
			$append = '';
			if($arr['Extra'] == 'auto_increment')
			{
				$pk = $col;
				$append.=<<<EOT

				'autoIncrement' => '1',
EOT;
			}
			elseif($arr['Key'] == 'PRI' && empty($pk))
			{
				$pk = $col;
			}
			$comment = $arr['Comment'];
			if(preg_match("/\[md5\]/", $comment))
			{
				$append.=<<<EOT
				
				'special' => 'md5',
EOT;
			}
			if(preg_match("/\[json\]/", $comment))
			{
				$arr['Default'] = '[]';//设置默认值为空数组
				$append.=<<<EOT
				
				'special' => 'json',
EOT;
			}
			$comment1 = preg_replace("/\??\(.*\)/", "", $comment);
			$type = $arr['Type'];
			$typeVar = 'integer';	
			if(preg_match("/char/" , $type))
			{
				if(preg_match("/\((\d+)\)/" , $type , $a_))
				{
					$len = (int)$a_[1];
					if($len >= 50)
					{
						$type = 'textarea';
					}
					else
						$type = 'text';
				}
				else
					$type = 'text';
				$typeVar = 'string';
			}
			elseif(preg_match("/int/" , $type))
			{
				$type = 'text';
				$typeVar = 'integer';
			}
			elseif(preg_match("/text/" , $type))
			{
				$type = 'textarea';
				$typeVar = 'string';
			}
//			$vars.=<<<EOT
//
//	/**
//	 * {$comment}
//	 * 
//	 * @var {$typeVar}
//	 */
//	public \${$arr['Field']} = '{$arr['Default']}';
//EOT;
			$valueList = "";
			if(preg_match_all("/\(([^:]+\s*:\s*[^,]+)(,([^:]+\s*:\s*[^,]+))*\)/" , $arr['Comment'] , $arrMatch))
			{
				$sss = $arrMatch[0][0];
				$sss = ltrim($sss,'(');
				$sss = rtrim($sss,')');
				$aa = explode(',', $sss);
// 				MmDebug::PrintData($aa);
				$type = 'radio';
				foreach($aa as $s)
				{
					$arr_ = explode(':' , $s);
					$valueList.="\n\t\t\t\t\t'{$arr_[0]}' => '{$arr_[1]}',";
					if($isNeedConst)
					{
						$const_name = strtoupper(preg_replace("/[A-Z]/", '_\0', $arr['Field'])).'_'.$arr_[0];
						$const.=<<<EOT

	/**
	 * {$comment1} : {$arr_[1]}
	 * @var {$typeVar}
	 */
	const {$const_name} = '{$arr_[0]}';
EOT;
					}
				}
				if($valueList)
					$valueList.="\n\t\t\t\t";
			}
			$tableString = '';
			$tc = '';
			$fieldUc = strtolower(preg_replace("/([A-Z])/" , '_$1' , $arr['Field']));
			if($fieldUc != $tableName."_id" && preg_match("/(.+)Id$/" , $arr['Field'] , $arrMatch))
			{
				if($arrMatch[1] == 'parent')
				{
					$tableName1 = $tableName;
				}
				else
				{
					$tableName1 = $arrMatch[1];
				}
				$tableName2 = preg_replace("/_(\w)/e", 'strtoupper("$1")', $tableName1);
				$tableName2Uc = ucfirst($tableName2);
				$tableString = "\n\t\t\t\t\t'name' => '{$tableName2Uc}DAL',//类名\n\t\t\t\t\t'keyField'=>'{$tableName2}Id',//主键名称\n\t\t\t\t\t'valueField' => '{$tableName2}Name',//记录名称";
			}
			if(empty($tableString))
				$tc = '//';
			else 
				$tableString.="\n\t\t\t\t";
			$range = array();
			$rules.=<<<EOT

			'{$arr['Field']}' => array(
				'label' => '{$comment1}',
				'type' => '{$type}',
				'value' => '{$arr['Default']}',
				'valueList' => array({$valueList}), //数值范围{$append}
{$tc}				'table' => array({$tableString}), //表,字段名首字母大写，name表示类名称
			),
EOT;
		}
		$code=<<<EOT
<?php
/**
 * 数据库操作类
 *
 */
class {$class} extends MmActiveRecordExt
{{$const}{$vars}
	/**
	 * 获取主键 , 目前只支持单主键
	 * @return string
	 */
	public function _getPrimaryKey()
	{
		return '{$pk}';
	}
	/**
	 * 字段属性规则,每个字段都必须定义
	 * @return array
	 */
	public function rules()
	{
		return array({$rules}
		);
// 			'ColumnName' => array(
// 				'Label'=>'名称',
// 				'Type'=>'(Text|Label|Password|Checkbox|Select|Radio|Html|TextArea|File|Date)',
// 				'Range'=>array('RangValue' => 'Text',...,),
// 				'IsRangeMulti' => false,
// 				'Table'=>array('TableName'=>'表名','EqualColumn'=>'数值对应的列','NameColumn'=>'显示的列',),
// 			),
// 			...
	}
	/**
	 * 列表规则
	 * @return array
	 */
	public function listRules()
	{
	
	}
	/**
	 * 类名
	 * @return string
	 */
	public function _getClass()
	{
		return __CLASS__;
	}
	/**
	 * 获取表
	 * @return string
	 */
	public function _getTable()
	{
		return '{$realTableName}';
	}
}
EOT;
		return $code;
	}
}