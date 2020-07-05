<?php
namespace Mm\Libs;
/**
 * MMHtml绘制类
 * 
 * @author mmfei<wlfkongl@163.com>
 */
class MmHtml
{
	/**
	 * 绘制表格
	 * @param array $arrTh	标题  eg :
	 * 								array(
	 * 									array('title'=>'标题名称','tpl'=> '模板文本','tagAttrs'=>array(),),//{字段名}:表示对应字段的数值
	 * 									array('title'=>'标题名称','tpl'=> '模板文本','tagAttrs'=>array(),),//{字段名}:表示对应字段的数值
	 * 									array('title'=>'标题名称','tpl'=> '模板文本','tagAttrs'=>array(),),//{字段名}:表示对应字段的数值
	 * 									...
	 * 								),
	 * @param array $data	数据  eg : array(array('Key列关键字' => '数据',...),...)
	 * @param boolean $isForm 是否需要表单
	 * @param string $formUrl 表单提交目标
	 * @param string $submitText 表单提交文字
	 * @param array $arrHiddenList	隐藏表单[name => value]
	 */
	public function DataShow(array $arrTh , array $data , $isForm = false , $formUrl = '' , $submitText = '确定' , $arrHiddenList = array())
	{
		$th = '';
		$count = 0;
		if($isForm)
		{
			$append = '';
			if($arrHiddenList)
			{
				foreach($arrHiddenList as $k => $v)
				{
					$append.="<input type='hidden' name='{$k}' value='{$v}' />";
				}
			}
			$col = count($arrTh);
			$table = "<form name='form1111' action='{$formUrl}' method='post'>
						<table class='table dataShow'>
							<thead>{__th__}</thead>
							<tbody>
								{__body__}
								<tr style='background-color: rgb(255, 255, 255); '>
									<td colspan='{$col}' style='text-align:center;'>
										<input type='submit' value='{$submitText}'>
									</td>
								</tr>
							</tbody>
						</table>
						{$append}
					</form>";
		}
		else
		{
			$table = "<table class='table dataShow'>
						<thead>{__th__}</thead>
						<tbody>
							{__body__}
						</tbody>
					</table>";
		}
		$body = '';
		$arrts = array();
		foreach($arrTh as $key => $arr)
		{
			$value = is_array($arr) && isset($arr['title']) ? $arr['title'] : $arr;
			
			if(is_array($arr))
			{
				if(isset($arr['tagAttrs']))
				{
					foreach($arr['tagAttrs'] as $kk => $vv)
					{
						isset($arrts[$value]) ? $arrts[$value] .= " {$kk} = '{$vv}'" : $arrts[$value] = " {$kk} = '{$vv}'";
					}
				}
			}
			$attrsText = isset($arrts[$value]) ? $arrts[$value] : '';
			$th.="<th{$attrsText}>{$value}</th>";
		}
		$arrReplace = $a = array();
		foreach($data as $arr)
		{
			$a = $arr;
			break;
		}
		foreach($a as $k => $v)
		{
			$arrReplace[$k] = "{{$k}}";
		}
		unset($a);
		foreach(array_values($data) as $key => $arr)
		{
			if($key && $key % 10 == 0)
				$body.='{__th__}';
			foreach($arr as $k1=>$v1)
				if(is_array($v1) || is_object($v1))
					$arr[$k1] = json_encode($v1);
			$body.="<tr>";
			$arrValues = array();
			foreach($arrReplace as $k => $v)
			{
				$arrValues[$k] = isset($arr[$k]) ? $arr[$k] : '';
			}
			foreach($arrTh as $k => $v)
			{
				$attrsText = is_array($v) && isset($arrts[$v['title']]) ? $arrts[$v['title']] : '';
				$tpl = is_array($v) && isset($v['tpl']) ? $v['tpl'] : $v;
				
				$body.="<td{$attrsText}>".str_replace($arrReplace, $arrValues, $tpl)."</td>";
			}
			$body.="</tr>";
		}
		$table = str_replace(array('{__body__}','{__th__}',), array($body,$th,), $table);
		return self::AppendFooter($table)->InitDefaultCss()->InitDefaultJs()->AppendCss('table.dataShow{width:auto;}');
	}
	
	/**
	 * 绘制表格
	 * @param array $arrTh	标题  eg : 
	 * 								array('Key列关键字' => '标题名称',...) 
	 * 								或者 
	 * 								array(
	 * 									'Key列关键字' => array('name'=>'标题名称','tpl'=>'模板文本',)
	 * 									'Key列关键字' => array('name'=>'标题名称','tpl'=>'模板文本',)
	 * 									'Key列关键字' => array('name'=>'标题名称','tpl'=>'模板文本',)
	 * 									,...
	 * 								)
	 * @param array $data	数据  eg : array(array('Key列关键字' => '数据',...),...)
	 */
	public function DataToTable(array $arrTh , array $data)
	{
		$th = '';
		$count = 0;
		$table = "<table class='table'>
					<thead>{__th__}</thead>
					<tbody>
						{__body__}
					</tbody>
				</table>";
		$body = '';
		foreach($arrTh as $key => $value)
		{
			if(is_array($value))
			{
				$value = isset($value['title']) ? $value['title'] : array_pop($value);
				$th.="<th>{$value}</th>";
			}
			else
			{
				$th.="<th>{$value}</th>";
			}
		}
		$arrReplace = $a = array();
		foreach($data as $arr)
		{
			$a = $arr;
			break;
		}
		foreach($a as $k => $v)
		{
			$arrReplace[] = "{{$k}}";
		}
		unset($a);
		foreach($data as $key => $arr)
		{
			$body.="<tr>";
			foreach($arrTh as $k => $v)
			{
				if(is_array($v))
				{
					$tpl = isset($v['tpl']) ? $v['tpl'] : array_pop(array_pop($v));
					$body.="<td>".str_replace($arrReplace, $arr, $tpl)."</td>";
				}
				else 
				{
					$body.="<td>{$arr[$k]}</td>";
				}
			}
			$body.="</tr>";
		}
		$table = str_replace(array('{__th__}','{__body__}'), array($th , $body), $table);
		return self::AppendFooter($table)->InitDefaultCss()->InitDefaultJs();
	}
	/**
	 * 初始化html对象，清空html内容
	 * 
	 * @param string $content		body的内容
	 * @param string $title			html的标题
	 * @param string $javascript	javascript脚本内容
	 * @param string $css			css内容
	 * @return void
	 */
	public function __construct($content = '', $title = '', $javascript = '', $css = '')
	{
		$GLOBALS['mmHtml']['Css'] 			= $css;
		$GLOBALS['mmHtml']['Javascript'] 	= $javascript;
		$GLOBALS['mmHtml']['JavascriptFile']= array();
		$GLOBALS['mmHtml']['Body'] 			= $content;
		$GLOBALS['mmHtml']['Title'] 		= $title;
		$GLOBALS['mmHtml']['Form']			= array();
		$GLOBALS['mmHtml']['Meta']			= '';
		$GLOBALS['mmHtml']['Footer']		= '';
	}
	/**
	 * 获取当前html对象，不清空html内容
	 * 
	 * @return MmHtml
	 */
	public static function GetHtml()
	{
		if(isset($GLOBALS['mmHtml']))
		{
			$mmHtml = $GLOBALS['mmHtml'];
			$html = new MmHtml();
			$GLOBALS['mmHtml'] = $mmHtml;
			return $html;
		}
		return new MmHtml();
	}
	/**
	 * 获取GET的数据
	 * 
	 * @param string $s			需要获取的get变量 $_GET[$s];
	 * @param string $defualt	如果get变量不存在则返回指定数值
	 * @return string
	 */
	public static function Get($s , $defualt = null)
	{
		return isset($_GET[$s]) ? $_GET[$s] : $defualt;
	}
	/**
	 * 获取POST的数据
	 * 
	 * @param string $s			需要获取的post变量 $_POST[$s];
	 * @param string $defualt	如果post变量不存在则返回指定数值
	 * @return string
	 */
	public static function Post($s , $defualt = null)
	{
		return isset($_POST[$s]) ? $_POST[$s] : $defualt;
	}
	/**
	 * 获取POST | GET的数据 [POST优先]
	 * 
	 * @param string $s			需要获取的post | get 变量 $POST[$s] $_GET[$s];
	 * @param string $defualt	如果post | get变量不存在则返回指定数值
	 * @return string
	 */
	public static function PG($s , $defualt = null)
	{
		return isset($_POST[$s]) ? $_POST[$s] : (isset($_GET[$s]) ? $_GET[$s] : $defualt);
	}
	/**
	 * 追加css内容
	 * 
	 * @param string $s	css 内容
	 * @return MmHtml
	 */
	public function AppendCss($s)
	{
		isset($GLOBALS['mmHtml']['Css']) ? '' : $GLOBALS['mmHtml']['Css'] = '' ;
		$GLOBALS['mmHtml']['Css'].=$s;
		return $this;
	}
	/**
	 * 覆盖css内容，会覆盖以前的css内容
	 * 
	 * @param string $s	css 内容
	 * @return MmHtml
	 */
	public function Css($s)
	{
		isset($GLOBALS['mmHtml']['Css']) ? '' : $GLOBALS['mmHtml']['Css'] = '' ;
		$GLOBALS['mmHtml']['Css']=$s;
		return $this;
	}
	/**
	 * 追加js内容
	 * 
	 * @param string $s	js 内容
	 * @return MmHtml
	 */
	public function AppendJavascript($s)
	{
		isset($GLOBALS['mmHtml']['Javascript']) ? '' : $GLOBALS['mmHtml']['Javascript'] = '' ;
		$GLOBALS['mmHtml']['Javascript'].=$s;
		return $this;
	}
	/**
	 * 增加一个js文件
	 * 
	 * @param string $src	js文件路径
	 * @return MmHtml
	 */
	public function AppendJavascriptFile($src)
	{
		isset($GLOBALS['mmHtml']['JavascriptFile']) ? '' : $GLOBALS['mmHtml']['JavascriptFile'] = array() ;
		$GLOBALS['mmHtml']['JavascriptFile'][] = $src;
	}
	/**
	 * 覆盖js内容
	 * 
	 * @param string $s	js 内容
	 * @return MmHtml
	 */
	public function Javascript($s)
	{
		$GLOBALS['mmHtml']['Javascript']=$s;
		return $this;
	}
	
	/**
	 * 追加js正文内容，body标签中间的内容
	 * 
	 * @param string $s	body内容
	 * @return MmHtml
	 */
	public function AppendBody($s)
	{
		isset($GLOBALS['mmHtml']['Body']) ? '' : $GLOBALS['mmHtml']['Body'] = '' ;
		$GLOBALS['mmHtml']['Body'].=$s;
		return $this;
	}
	/**
	 * 追加body底部内容，在绘制完AppendBody后继续绘制的内容<body>设置这里的内容</body>
	 * 
	 * @param string $s	footer内容
	 * @return MmHtml
	 */
	public function AppendFooter($s)
	{
		isset($GLOBALS['mmHtml']['Footer']) ? '' : $GLOBALS['mmHtml']['Footer'] = '' ;
		$GLOBALS['mmHtml']['Footer'].=$s;
		return $this;
	}
	/**
	 * 设置body的内容[不包括Footer内容]<body>[其他内容] 设置这里的内容</body>
	 * 
	 * @param string $s	body内容
	 * @return MmHtml
	 */
	public function Body($s)
	{
		$GLOBALS['mmHtml']['Body']=$s;
		return $this;
	}
	/**
	 * 从配置绘制表单【直接添加到body中】
	 * @param array $config
	 * 			array(
	 * 				'formName' => 'form1',
	 * 				'mothod' => 'post',
	 * 			)
	 * @return MmHtml
	 */
	public function FormByConfig($config)
	{
		$defaultConfig = array(
			'formName' => 'form1',
			'method' => 'post',
			'action' => '',
			'title' => '表单标题',
			'attr' => array(),//额外表单属性 , 如果有file表单 ， 则需要 enctype=>"multipart/form-data"
			'textTop' => '',//顶部文本内容
			'textBottom' => '',//底部文本内容
			'buttonText' => '确定',//按钮文字
			'inputList' => array(//表单元素 , 一个元素代表一行
// 				array(
// 					'label' => '',//表单文本
// 					'list' => array(
// 						array(
// 							'name' => '',//表单名称
// 							'type' => 'text',//表单类型 text|label|hidden|password|radio|select|checkbox|textarea|file
// 							'value' => '',//表单值
// 							'attr' => array(),//表单属性
// 							'selected' => array(),//选中值 , 多选表单有效
// 							'valueList'=>array(//此行表单的元素列表 , 多选表单有效
// 								'value' => 'text',
// 							)
// 						),
// 					),
// 				),
			),
		);
		$newConfig = $defaultConfig;
		$isUpload = false;
		foreach($config as $k => $v)
		{
			if(is_array($v))//$k = inputLit
			{
				foreach($v as $kk => $vv)
				{
					if(is_array($vv))
					{
						foreach($vv as $kkk => $vvv)//$kkk = list
						{
							if(is_array($vvv))
							{
								foreach($vvv as $kkkk => $vvvv)
								{
									if(!$isUpload && isset($vvvv['type']) && $vvvv['type'] == 'file')
									{
										$isUpload = true;
									}
									$newConfig[$k][$kk][$kkk][$kkkk] = $vvvv;
								}
							}
							else
								$newConfig[$k][$kk][$kkk] = $vvv;
						}
					}
					else
					{
						$newConfig[$k][$kk] = $vv;
					}
				}
			}
			else
			{
				$newConfig[$k] = $v;
			}
		}
		$tbody = '';
		foreach($newConfig['inputList'] as $arrRow)
		{
			$inputString = '';
			$appendString = '';
			foreach($arrRow['list'] as $arr)
			{
				if(isset($arr['attr']) && $arr['attr'])
					$attrs1 = self::_attrToStr($arr['attr']);
				else
					$attrs1 = '';
				$arr['valueList'] = isset($arr['valueList']) ? $arr['valueList'] : array();
				$br = (count($arr['valueList']) > 3) ? '<br />' : '';
				if(!isset($arr['type']) || empty($arr['type']))
					$arr['type'] = 'text';
				switch($arr['type'])
				{
					case 'checkbox':
						$arr['name'] .= '[]';
	
						foreach($arr['valueList'] as $key => $value)
						{
							$arr['attr']['id'] = "{$arr['name']}_{$key}";
							$attrs1 = self::_attrToStr($arr['attr']);
							$checked = '';
							if(in_array($key , $arr['selected']))
								$checked = ' checked="checked"';
							$inputString.=<<<EOT
				
							<input type='{$arr['type']}' name='{$arr['name']}' value='{$key}' {$attrs1}{$checked} />
							<label for='{$arr['attr']['id']}'>{$value}</label>
							{$br}
EOT;
						}
						break;
						
					case 'radio':
						foreach($arr['valueList'] as $key => $value)
						{
							$arr['attr']['id'] = "{$arr['name']}_{$key}";
							$attrs1 = self::_attrToStr($arr['attr']);
							$checked = '';
							if(in_array("{$key}" , $arr['selected']))
								$checked = ' checked="checked"';
							$inputString.=<<<EOT
									
						<input type='{$arr['type']}' name='{$arr['name']}' value='{$key}' {$attrs1}{$checked} />
						<label for='{$arr['attr']['id']}'>{$value}</label>
							{$br}
EOT;
							}
						break;
					case 'select':
						$arr['attr']['id'] = "{$arr['name']}_{$arr['value']}";
						$attrs1 = self::_attrToStr($arr['attr']);
						
						$inputString.=<<<EOT
							<select name='{$arr['name']}'  {$attrs1}>
EOT;
						foreach($arr['valueList'] as $key => $value)
						{
							$selected = '';
							if(in_array($key , $arr['selected']))
								$selected = ' selected="selected"';
							$inputString.=<<<EOT
								
							<option value='{$key}'{$selected}>{$value}</option>
EOT;
						}
						$inputString.=<<<EOT
						
						</select>
EOT;
						break;
						case 'textarea':
						
						$inputString.=<<<EOT
	
							<textarea name='{$arr['name']}' {$attrs1}>{$arr['value']}</textarea>
EOT;
						break;
					case 'label':
	
						$inputString.=<<<EOT
	
							<label{$attrs1}>{$arr['value']}</label>
EOT;
						break;
					default:
						$inputString.=<<<EOT
	
						<input type='{$arr['type']}' name='{$arr['name']}' value='{$arr['value']}' {$attrs1} />{$appendString}
EOT;
	
				}
			}
			$tbody.=<<<EOT
			<tr><td class='label'>{$arrRow['label']}</td><td>{$inputString}</td></tr>
EOT;
		}
		if($isUpload)
			$newConfig['attr']['enctype'] = 'multipart/form-data';
		$attrs = self::_attrToStr($newConfig['attr']);
		$form=<<<EOT

			<form name='{$newConfig['formName']}' action='{$newConfig['action']}' method='{$newConfig['method']}' {$attrs}>
				{$newConfig['textTop']}
				<table>
				<thead>
						<th colspan=2>{$newConfig['title']}</th>
					</thead>
					<tbody>
						{$tbody}
						<tr>
							<td colspan=2 style="text-align:center;">
								<input type="submit" value="{$newConfig['buttonText']}" />
							</td>
						</tr>
					</tbody>
				</table>
				{$newConfig['textBottom']}
			</form>
EOT;
	
		if(!isset($GLOBALS['mmHtml']['Title']) || empty($GLOBALS['mmHtml']['Title']))
			self::Title($newConfig['title']);
		return self::AppendBody($form);
	}
	private static function _attrToStr(array $attr)
	{
		$s = '';
		foreach($attr as $k => $v)
			$s.= " {$k} = '$v'";
		return $s;
	}
	/**
	 * 设置网站标题<title>[其他已经设置的标题] 设置这里的内容</title>
	 * 
	 * @param string $s	title内容
	 * @return MmHtml
	 */
	public function AppendTitle($s)
	{
		isset($GLOBALS['mmHtml']['Title']) ? '' : $GLOBALS['mmHtml']['Title'] = '' ;
		$GLOBALS['mmHtml']['Title'].=$s;
		return $this;
	}
	/**
	 * 设置网站标题<title>设置这里的内容</title>
	 * 
	 * @param string $s	title内容
	 * @return MmHtml
	 */
	public function Title($s)
	{
		$GLOBALS['mmHtml']['Title']=$s;
		return $this;
	}

	/**
	 * 绘制表单
	 *
	 */
	/**
	 * 增加反选功能js , [a#invertCheckbox]
	 * 		a ) 触发此动作的标签必须跟checkbox在一个table内
	 * 		b ) 触发此动作的标签id=invertCheckbox
	 * 
	 * eg :
	 * 		Html::GetHtml()->Form($formMethod, $formName ,'post','','标题<a href="#" id="invertCheckbox">反选</a>');
	 * 		上面的方式会在表单头部增加一个反选按钮，它的功能就是点击后反选此表单所有checkbox
	 * 		
	 * @return MmHtml
	 */
	public function AppendInvertCheckbox()
	{
		$js=<<<EOT

			$(document).ready(function(){
				$('#invertCheckbox').click(function(){
					$('input[type=checkbox]',$(this).parents('table')).each(function(){
						$(this).attr(
							'checked',
							!$(this).attr('checked')
						);
					});
				});
			});
EOT;
		return self::AppendJavascript($js);
	}
	
	/**
	 * 取消选择功能js , [a#cleanCheckbox]
	 * 		a ) 触发此动作的标签必须跟checkbox在一个table内
	 * 		b ) 触发此动作的标签id=cleanCheckbox
	 * 
	 * eg :
	 * 		Html::GetHtml()->Form($formMethod, $formName ,'post','','标题<a href="#" id="cleanCheckbox">取消选择</a>');
	 * 		上面的方式会在表单头部增加一个取消选择按钮，它的功能就是点击后取消选择此表单所有checkbox
	 * 		
	 * @return MmHtml
	 */
	public function AppendCleanCheckbox()
	{
		$js=<<<EOT

			$(document).ready(function(){
				$('#cleanCheckbox').click(function(){
					$('input[type=checkbox]',$(this).parents('table')).each(function(){
						$(this).attr(
							'checked',
							false
						);
					});
				});
			});
EOT;
		return self::AppendJavascript($js);
	}
	/**
	 * 绘制html
	 * 
	 * @param boolean $isReturn	是否返回 ， true : 则直接返回需要打印的数据 , false : 直接打印数据
	 * @return string
	 */
	public function Show($isReturn = false)
	{
		$jqueryUrl = MM_APP_PATH .'/jquery.min.js';
		$jqueryFile = MM_APP_ROOT .'/jquery.min.js';
		if(!file_exists($jqueryFile))
		{
			$jqueryApiUrl = 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js';
			$content = @file_get_contents($jqueryApiUrl);
			if(!$content)
				$jqueryUrl = $jqueryApiUrl;
			else
				file_put_contents($jqueryFile,$content);
		}
		
		$newHtml = self::GetHtml();
		isset($GLOBALS['mmHtml']['Body']) || $GLOBALS['mmHtml']['Body'] = '';
		isset($GLOBALS['mmHtml']['Meta']) || $GLOBALS['mmHtml']['Meta'] = '';
		isset($GLOBALS['mmHtml']['Footer']) || $GLOBALS['mmHtml']['Footer'] = '';
		
		$body = $GLOBALS['mmHtml']['Body'];
		
		$javascriptFileText = '';
		if(isset($GLOBALS['mmHtml']['JavascriptFile']))
		{
			foreach($GLOBALS['mmHtml']['JavascriptFile'] as $src)
			{
				$javascriptFileText.=<<<EOT

				<script type="text/javascript" src="{$src}"></script>
EOT;
			}
		}
		$html=self::getConfigBy(
			__FUNCTION__ , 
			'html' , 
			array(
				'Title'				=>	$GLOBALS['mmHtml']['Title'],
				'Css'				=>	$GLOBALS['mmHtml']['Css'],
				'Meta'				=>	$GLOBALS['mmHtml']['Meta'],
				'Javascript'		=>	$GLOBALS['mmHtml']['Javascript'],
				'JavascriptFile'	=>	$javascriptFileText,
				'Body'				=>	$body,
				'Footer'			=>	$GLOBALS['mmHtml']['Footer'],
				'jqueryUrl' 		=>	$jqueryUrl,
				'CHARSET_PAGE' 		=>	CHARSET_PAGE,
			)
		);
		if($isReturn)
			return $html;
		echo($html);
	}
	
	
	public function InitDefaultCss()
	{
		$css = self::getConfigBy(__FUNCTION__ , 'css');
		self::AppendCss($css);
		return $this;
	}
	public function InitDefaultJs()
	{
		$js = self::getConfigBy(__FUNCTION__ , 'js');
		self::AppendJavascript($js);
		return $this;
	}
	public function InitAjaxSubmit()
	{
		$js =self::getConfigBy(__FUNCTION__ , 'js');
		return self::AppendJavascript($js);
	}
	public function InitAjaxDelete()
	{
		$js =self::getConfigBy(__FUNCTION__ , 'js');
		return self::AppendJavascript($js);
	}
	public function AppendMeta($name  , $content , $nameKey = 'name')
	{
		$GLOBALS['mmHtml']['Meta'] .= <<<EOT

		<meta {$nameKey}="{$name}" content="{$content}" />
EOT;
	}
	public function Clear()
	{
		$GLOBALS['mmHtml'] = array(
			'Css'			=>	'',
			'Meta'			=>	'',
			'Javascript'	=>	'',
			'Body'			=>	'',
			'Footer'		=>	'',
			'Title'			=>	'',
			'Form'			=>	array(),
		);
	}

	/**
	 * 等待跳转
	 *
	 * @param string $url
	 * @param string $title
	 * @param string $content			如果此参数不为null，则覆盖所有内容，需要自己完成对应的内容处理
	 * @param integer $time
	 * @param strint $appendContent
	 * @return void
	 */
	public function WaitingToUrl($url , $title = null , $content = null , $time = 5 , $appendContent = '')
	{
		self::Clear();
		self::AppendMeta("refresh", "{$time};url={$url}" , "http-equiv");
		if(is_null($content))
		{
			$js =self::getConfigBy(__FUNCTION__ , 'js' , array('time'=>$time,'url'=>$url));
			$css=self::getConfigBy(__FUNCTION__ , 'css');
			self::AppendCss($css)->InitDefaultCss();
			self::AppendJavascript($js);
			if(isset($title))
			{
				$css=self::getConfigBy(__FUNCTION__ , 'titleCss');
				self::AppendCss($css);
				$content=self::getConfigBy(__FUNCTION__ , 'titleHtml' , array('title' => $title,'url'=>$url,'appendContent' => $appendContent,));
			}
			else
			{
				$content=self::getConfigBy(__FUNCTION__ , 'html' , array('url'=>$url,));
			}
		}
		if(isset($title))
		{
			self::Title($title);
			
		}
		self::AppendBody($content);
		self::Show();
		Mm::Stop();
		exit(0);
	}
	private $htmlConfig = array();
	/**
	 * 设置配置
	 * @param array $config
	 * @return array
	 */
	public function initHtmlConfig($config = array())
	{
		if(func_num_args() == 0)
		{
			$config = Mm::getByKey(__CLASS__);
			if(is_null($config))
				$config = array();
		}
		$this->htmlConfig = $config;
		return $this;
	}
	/**
	 * 获取配置
	 * @param string $key
	 * @param string $type	css | js | html
	 * @param array $args	all string equal {key} will replace to value string.
	 * @return string
	 */
	private function getConfigBy($key , $type = 'css' , array $args = array())
	{
		$return  = isset($this->htmlConfig[$key][$type]) ? $this->htmlConfig[$key][$type] : self::_getDefaultConfig($key , $type);
		if($args)
		{
			$a = array();
			foreach($args as $k => $v)
				$a[] = "{{$k}}";
			$return = str_replace($a, $args, $return);
		}
		return $return;
	}

	/**
	 * 获取配置
	 * @param string $key
	 * @param string $type	css | js | html
	 * @return string
	 */
	private function _getDefaultConfig($key , $type)
	{
		$arrConfig = array(
			'InitDefaultCss' => array(
				'css' => '
							body{font-size:14px;text-align:center;}
							.main{
								margin:0 auto;
								text-align:center;
							}
							table , .table{
								border-collapse:collapse;
								margin:0 auto 15px;
								width:600px;
							}
							captain{
								font-size:bold;
								text-align: center;
								width:98%;
								margin:0 auto;
								font-weight:bold;
								font-size:16px;
								height:35px;
								line-height:35px;
								background-color: #FFDEAD;
								border: 1px solid #999999;
								display:block;
							}
							th {
								font-size:bold;
								text-align: center;
								padding: 6px 6px 6px 12px;
								background-color: #EAF5F7;
								border: 1px solid #999999;
							}
							td {
								padding: 6px 6px 6px 12px;
								border:1px solid #ccc;
							} 
							.err , #tips_temp{
								padding: 6px 6px 6px 12px;
								background-color: #FFDEAD;
							}
							#tips{
								color:#CD5C5C;
								font-size:12px;
							}
							textarea{
								width:300px;
								height:50px;
							}',
			),
			'InitDefaultJs' => array(
				'js' => "
						\$(document).ready(function(){
							\$('tr').hover(
								function(){
									\$(this).css({'backgroundColor':'#EAF5F7'});
								},
								function()
								{
									\$(this).css({'backgroundColor':'#fff'});
								}
							);
						});"
			),
			'InitAjaxSubmit' => array(
				'js' => "
					\$(document).ready(function(){
					\$('form.ajaxForm').submit(function(){
						
						\$.ajax({
							type : \$(this).attr('method'),
							url : \$(this).attr('action'),
							cache : false,
							data : \$(this).serialize(),
							error : function(){
								alert('提交失败!');
							},
							success : function(data)
							{
								if(data == true || data == 1 || data == '1')
									alert('提交成功!');
								else
									alert('提交失败'+data);
							}
						});
						return false;
					});	
				});	",
			),
			'InitAjaxDelete' => array(
				'js' => "
					\$(document).ready(function(){
					\$('a.ajaxDelete').click(function(){
						if(confirm('是否删除?'))
						{
							\$(this).attr('id' , '__ajaxDeleteId__');
							\$.ajax({
								url : \$(this).attr('href'),
								cache : false,
								error : function(){
									alert('提交失败!');
								},
								success : function(data)
								{
									if(data == true || data == 1 || data == '1')
									{
										$('a#__ajaxDeleteId__').parents('tr').css({'backgroundColor':'#ffcccc'}).animate({
											'opacity':'0'
										},2000 , function(){\$(this).remove();});
									}
									else
										alert('提交失败'+data);
								}
							});
						}
						return false;
					});	
				});
				",
			),
			'WaitingToUrl' => array(
				'js' => "
					$(document).ready(function(){
						wtLoading();
					});
					function wtLoading()
					{
						unit = 1000;
						count = {time} * 1000 / unit;
						width = $('#percent').width();
						warpWidth = $('#percent').parent().width();
						nowWidth = width + 300 / count;
						
						if(nowWidth >= warpWidth)
						{
							$('#percent').text('100%');
							window.location = '{url}';
						}
						else
						{
							percent = parseInt((nowWidth / warpWidth) * 100);
							$('#percent').text(percent + '%').animate({
								'width':nowWidth
							}, unit);
							setTimeout('wtLoading()',unit);
						}
					}
				",
				'css' => "
					#loading{
						width:302px;
						height:18px;
						line-height:18px;
						border:1px solid #999999;
						margin:0 auto;
					}
					#percent{
						width:1px;
						margin:1px;
						height:16px;
						line-height:16px;
						background-color:#EAF5F7;
						text-align:right;
					}
					#loadingTips{
						
					}
					#loadingTips a{
						text-decoration:none;
						font-weight:bold;
					}
				",
				'html' => '
					<div class="main">
						<div id="loading">
							<div id="percent"></div>
						</div>
						<p id="loadingTips"> 
							如果您的浏览器不支持跳转,
							<a href="{url}">请点这里</a>.
						</p>
						{$appendContent}
					</div>	
				',
				'titleCss' => "
					h1{
						font-size:14px;
						height:30px;
						line-height:30px;
						text-align:center;
						margin:0 auto;
					}
				",
				'titleHtml' => "
					<div class='main'>
						<h1>{title}</h1>
						<div id='loading'>
							<div id='percent'></div>
						</div>
						<p id='loadingTips'> 
							如果您的浏览器不支持跳转,
							<a href='{url}'>请点这里</a>.
						</p>
						{appendContent}
					</div>	
				"
			),
			'Show' => array(
				'html' => '
					<html>
						<head>
							<meta http="equiv-content" content="text/html;charset={CHARSET_PAGE}"/>
							<title>{Title}</title>
							{Meta}
							<style>
								{Css}
							</style>
							<script type="text/javascript" src="{jqueryUrl}"></script>{JavascriptFile}
							<script type="text/javascript" language="javascript">
								{Javascript}
							</script>
						</head>
						<body>
							<div class="main">
							{Body}{Footer}
							</div>
						</body>
					</html>
				',
			),
		);
		return isset($arrConfig[$key][$type]) ? $arrConfig[$key][$type] : '';
	}
}