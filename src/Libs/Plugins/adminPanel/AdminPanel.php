<?php
namespace Mm\Libs\Plugins\adminPanel;
class AdminPanel
{
	/**
	 * 菜单列表
	 * @param array $menuList
	 * 		array(
	 * 			array(
	 * 				'title' => '',
	 * 				'attr' => array(),
	 * 				'list' => array(
	 * 					array(
	 * 						'text'=> '',
	 * 						'attr'=> array(),
	 * 						'url'=>'',
	 * 						'urlAttr'=>array(),
	 * 					),
	 * 					...,
	 * 				),
	 * 			),
	 * 			...
	 * 		)
	 * @return void
	 */
	protected static function adminIndex($menuList , $title = '管理后台')
	{
		$menuHtml = self::getMenuHtml($menuList);
		$html=<<<EOT
			<div id="header">{$title}</div>
			{$menuHtml}
			<div id=''>Welcome!</div>
EOT;
		$css=<<<EOT
			
EOT;
		$js=<<<EOT
		
EOT;
		MmHtml::GetHtml()->AppendBody($html)->AppendCss($css)->AppendJavascript($js)->Show();
	}
	protected static function getMenuHtml($menuList)
	{
		$html = '<div class="mmMenu">';
		foreach($menuList as $k => $arr)
		{
			$title = isset($arr['title']) ? $arr['title'] : $k;
			$strAttr = self::_bindAttr($arr);
			$list = isset($arr['list']) ? $arr['list'] : array();
			$html.="<dl><dt>{$title}</dt>";
			foreach($list as $kk => $aa)
			{
				$text = isset($aa['text'])?$aa['text']:'';
				if(empty($text)) continue;
				$url = isset($aa['url'])?$aa['url']:'';
				$strAttr = self::_bindAttr($aa);
				if(isset($aa['urlAttr']))
					$strLinkAttr = self::_bindAttr($aa['urlAttr']);
				else
					$strLinkAttr = '';
				if($url)
					$html.="<dd{$strAttr}><a href='{$url}'{$strLinkAttr}>{$text}</a></dd>";
				else
					$html.="<dd{$strAttr}>{$text}</dd>";
			}
			$html.="</dl>";
		}
		$html.= '</div>';
		return $html;
	}
	private static function _bindAttr(array $data)
	{
		$attr = isset($data['attr']) ? $data['attr'] : $data;
		$return = '';
		if($attr)
		{
			foreach($attr as $k => $v)
				$return .= " '{$k}' = '$v'";
		}
		return $return;
	}
	/**
	 * 获取css
	 * /index.php/$actionClass/css/cssFileName.css
	 */
	public static function actionCss()
	{
		$args = MmUrl::$arrPath;
		if(isset($args[2]))
		{
			$file = $args[2];
			if($file != '')
			{
				return self::getCss($file);
			}
		}
	}
	/**
	 * 获取js
	 * /index.php/$actionClass/js/FileName.js
	 */
	public static function actionJs()
	{
		$args = MmUrl::$arrParam;
		if($args)
		{
			$file = array_pop($args);
			if($file != '')
			{
				return self::getJavascript($file);
			}
		}
	}
	/**
	 * 获取js
	 * /index.php/$actionClass/image/FileName.Ext
	 */
	public static function actionImage()
	{
		$args = MmUrl::$arrParam;
		if($args)
		{
			$file = array_pop($args);
			if($file != '')
			{
				return self::getJavascript($file);
			}
		}
	}
	///index.php/ActionClass/ActionFunction/Param1/Param2?get1=val1&get2=val2
	protected static function route($actionClass = '')
	{
		// /$actionClass/css/file
		// /$actionClass/js/file
		// /$actionClass/images/file
		// /$actionClass/css/file
	}
	protected static function getCss($file)
	{
		return self::getResources($file , 'css');
	}
	protected static function getJavascript($file)
	{
		return self::getResources($file , 'js');
	}
	protected static function getImage($file)
	{
		return self::getResources($file , 'images');
	}
	protected static function getSwf($file)
	{
		return self::getResources($file , 'swf');
	}
	protected static function getResources($file , $type='images')
	{
		static $dir = '' , $theme = '';
		if(empty($dir))
		{
			$config = self::getConfig();
			$dir = isset($config['adminDir']) ? $config['adminDir'] : MM_CORE_ROOT.'/libs/plugins/adminPanel/themes';
			$theme = isset($config['theme']) ? $config['theme'] : 'default';
			$dir .= '/'.$theme; 
		}
		$file = $dir . '/'.$type.'/'.$file;
		if(file_exists($file))
		{
			echo file_get_contents($file);
			die();
		}
		return '';
	}
	protected static function getConfig()
	{
		static $config = null;
		if($config) return $config;
		$config = Mm::getByKey(__CLASS__);
		if($config) return $config;
		$config = self::_getDefaultConfig();
		return $config;
	}
	private static function _getDefaultConfig()
	{
		$dir = MM_CORE_ROOT.'/libs/plugins/adminPanel/themes';
		$path = str_replace(MM_ROOT, '', $dir);
		return array(
			'adminDir' => $dir,
			'adminPath'=> $path,
			'editorPath' => $path.'/editor',
			'theme' => 'default',
		);
	}
}