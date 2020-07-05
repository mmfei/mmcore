<?php
class MmHelper
{
	private static function GetRules()
	{
		// 		'{Field}' => $fieldName,
		// 		'{Type}' => $fieldType,
		// 		'{Comment}' => $Comment,
		// 		'{CommentLess}' => $CommentLess,
		// 		'{isAllowNull}' => $isAllowNull,
		// 		'{defaultValue}' => $defaultValue,
		return array(
				'post&get' => '		${Field} = MmHtml::PG(\'{Field}\' , \'{defaultValue}\');//{CommentLess}',
				'post&get2' => '		${Field} = MmHtml::PG(\'{Field}\');//{CommentLess}',
				'post&get1' => '		${Field} = MmHtml::PG(\'{Field}\' , isset($data[\'{Field}\']) ? $data[\'{Field}\'] : \'{defaultValue}\');//{CommentLess}',
				'表单' => '		MmHtml::GetHtml()->AppendInput($formName , \'{Field}\' , \'{CommentLess}\' , $data[\'{Field}\']);',
				'表单1' => '		MmHtml::GetHtml()->AppendInput($formName , \'{Field}\' , \'{CommentLess}\' , ${Field});',
				'表单2' => '		MmHtml::GetHtml()->AppendInput($formName , \'{Field}\' , \'{CommentLess}\' , MmHtml::PG(\'{Field}\'),\'{defaultValue}\');',
				'标题' => '		<th>{CommentLess}</th>',
				'标题1' => '		<th>{Field}</th>',
				'smarty' => '		<tr><td>{CommentLess}</td><td> <!--{$data.{Field}}--></td></tr>',
				'smarty1' => '		<b>{CommentLess} :</b> <!--{$data.{Field}}--><br />',
				'smarty2' => '		<tr><td>{CommentLess}</td><td><input type="text" value="<!--{$data.{Field}}-->" name="{Field}" /></td></tr>',
				'smarty3' => '		<b>{CommentLess} :</b> <input type="text" value="<!--{$data.{Field}}-->" name="{Field}" /><br />',
				'td' => '		<td>{$data[\'{Field}\']}</td>',
				'data' => '		$data[\'{Field}\'] = ${Field};',
				'data1' => '		\'{Field}\' => ${Field},',
				'string' => '		\'{Field}\',',
				'string1' => '		\'{Field}\',//{CommentLess}',
				'comment' => '		\'{Field}\' => \'{CommentLess}\',',
				'comment1' => '	 * 				\'{Field}\' => \'{CommentLess}\',',
				'dataShowConfig' => '	 array(\'title\' => \'{CommentLess}\', \'tpl\'=>\'{{Field}}\',\'tagAttrs\'=>array(),),',
				'formConfig' => '	 			array(
					\'label\' => \'{CommentLess}\',
					\'list\'  => array(
						array(
							\'name\' => \'{Field}\',
							\'type\' => \'text\',
							\'value\' => ${Field},
						),
					),
				),',
					'formConfig1' => '	 			array(
					\'label\' => \'{CommentLess}\',
					\'list\'  => array(
						array(
							\'name\' => \'{Field}\',
							\'type\' => \'text\',
							\'value\' => MmHtml::PG(\'{Field}\'),
						),
					),
				),',
					'formConfig2' => '	 			array(
					\'label\' => \'{CommentLess}\',
					\'list\'  => array(
						array(
							\'name\' => \'{Field}\',
							\'type\' => \'text\',
							\'value\' => MmHtml::PG(\'{Field}\' , ${Field}),
						),
					),
				),',
		);
	}
	public static function DbToCode()
	{
		$arrRules = self::GetRules();
	
		$arrTableList = MmDatabaseTool::GetTables();
		$formName = 'form1';
		$TableName = MmHtml::PG('TableName');
		$_ttt = MmHtml::PG('_ttt');
		$arrTableValueList = $arrRuleValueList = array();
		foreach($arrTableList as $k => $table)
		{
			$arrTableValueList[$table] = $table;
		}
		foreach($arrRules as $k => $v)
			$arrRuleValueList[$k] = $k;
// 			MmHtml::GetHtml()->AppendInput($formName , '_ttt[]' , '类型' , $k , 'checkbox' , $k , $_ttt , true);
	
		$config = array(
			'title'=>'查看',
			'inputList' => array(
				array(
					'label' =>	'表',
					'list'	=>	array(
						array(
							'type' =>'select',
							'name' =>'TableName',
							'value' => $TableName,
							'valueList' => 	$arrTableValueList,
							'selected' => array($TableName,),
						),
					),
				),
				array(
					'label' =>	'类型',
					'list'	=>	array(
						array(
							'type' =>'checkbox',
							'name' =>'_ttt',
							'value' => $_ttt,
							'valueList' => 	$arrRuleValueList,
							'selected' => $_ttt,
						),
					),
				),
			),		
		);
		$html = MmHtml::GetHtml();
		if($TableName)
		{
			$arrReturns = array();
			$data = MmDatabaseTool::GetTableCols($TableName);
			$ColumnNameList = '';
	
			$strCoust = '';
			foreach($data as $arr)
			{
				$fieldName = $arr['Field'];
				$fieldType = $arr['Type'];
				$Comment = $arr['Comment'];
				$CommentLess = preg_replace("/\(.*\)/",'',$arr['Comment']);
				$isAllowNull = $arr['Null'] == 'NO' ? 'false' : 'true';
				$grnerator = $arr['Extra'] == 'auto_increment' ? 'identity' : '';
				$CurColumn = $arr['Key'] == 'PRI' ? $fieldName : '';
				$defaultValue= !is_null($arr['Default']) ? $arr['Default'] : '';
				$arrReplace = array(
						'{Field}' => $fieldName,
						'{Type}' => $fieldType,
						'{Comment}' => $Comment,
						'{CommentLess}' => $CommentLess,
						'{isAllowNull}' => $isAllowNull,
						'{defaultValue}' => $defaultValue,
				);
				foreach($arrRules as $k => $v)
				{
					if(in_array($k , $_ttt))
						$arrReturns[$k][] = str_replace(array_keys($arrReplace) , $arrReplace , $v);
				}
			}
	
			foreach($arrReturns as $k => $a)
				$html->AppendFooter('<div style="text-align:left;">'.highlight_string("<?php\n".join("\n" , $a),true).'</div>');
		}

		$html->FormByConfig($config)->InitDefaultJs()->InitDefaultCss()->Show();
		
	}
}