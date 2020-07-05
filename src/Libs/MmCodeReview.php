<?php
namespace Mm\Libs;
class MmCodeReview
{
	public static function run()
	{
		return self::checkProject();
		$rootDir = MM_ROOT;
		$html = Html::GetHtml();
		$html->AppendBody('<h1>项目检测:</h1>');
		$fileList = array();
		foreach(new DirectoryIterator($rootDir) as $o)
		{
			if($o->isFile() && preg_match("/\.php$/",$o->getFilename()))
			{
				$filename = $rootDir.'/'.$o->getFilename();
				$fileList[] = $filename;
				self::_file($filename);
			}
		}
		MmDebug::printData($fileList);
		
		$html->InitDefaultCss()->InitDefaultJs()->AppendCss('.main{text-align:left;}')->Show();
	}
	private static function _file($filename)
	{
		$content = file_get_contents($filename);
		if($content)
		{
			Html::GetHtml()->AppendBody("<h2>文件:{$filename}</h2>");
			self::_define($content);
			self::_class($content);
			self::_function($content);
		}
	}
	private static function _define($content)
	{
		$s = '';
		preg_match_all('/define\s?\([\'"](.*)[\'"]\s?,\s?.+\);/' , $content , $arrMatch);
		foreach($arrMatch[1] as $defineName)
		{
			$s.=<<<EOT

	<dd>{$defineName}</dd>						
EOT;
		}
		if($s)
			$s = "<dl><dt>常量</dt>\n{$s}\n</dl>";
		Html::GetHtml()->AppendBody($s);
	}
	private static function _class($content)
	{
		$s = '';
		preg_match_all('/\/\*\*\s*\n((.*\*(.*)\n)*)\s*\*\/(\n\s*)*(final)>\s*(abstract)?\s*class\s([\w\d_]+)\s*(extends\s([^\s]+))?[\s]*(implements\s([^\s]+))?[\s\n]*{/' , $content , $arrMatch);
		$start = 1;
		foreach($arrMatch[$start+1] as $index => $arr)
		{
			$text=$arrMatch[$start+4][$index];
			if($arrMatch[$start+6][$index])
				$text.= ' 继承:'.$arrMatch[$start+6][$index];
			if($arrMatch[$start+8][$index])
				$text.= ' 实现接口:'.$arrMatch[$start+8][$index];
			$text.=self::_classComment($arrMatch[$start][$index]);
			$s.=<<<EOT

	<dd>{$text}</dd>
EOT;
		}
		if($s)
			$s = "<dl><dt>类</dt>\n{$s}\n</dl>";
		if($content)
			$s.=self::_classFunction($content);
		Html::GetHtml()->AppendBody($s);
	}
	private static function _classComment($comment)
	{
		$s = self::_bindComment($comment);
//		$s = preg_replace(array("/\n/","/\\t/",),array("\\n",'\\t'),$s);
		return $s;
	}
	private static function _bindComment($comment)
	{
		$s = preg_replace(
			array(
				"/\n[ \t]*\*/",
				"/\t/",
				"/^\s*\*/",
				"/\n/",
			),
			array(
				"<br/>",
				"&nbsp;&nbsp;&nbsp;&nbsp;",
				"",
				"<br/>",
			),
			$comment
		);
		
		$attr = array(
			'@package' => '包',
			'@param' => '参数',
			'@author' => '作者',
			'@return' => '返回值',
		);
		foreach($attr as $k => $v)
			if(preg_match("/{$k}/", $s)) $s = preg_replace("/{$k}\s/","{$v} : ",$s);
		if($s)
			$s = preg_replace("/^<br\/>/","",$s);
//			$s = "<h3>注释</h3>".preg_replace("/^<br\/>/","",$s);
		return $s;
	}
	private static function _classFunction($content)
	{
		preg_match_all(
			"/(\/\*\*\s*\n((.*\*(.*)\n)*)\s*\*\/.*):?\n\s*(public|protected|private)?\s*(static)?\s*(abstract)?\s*function\s([\w\d_]+)\s*\([^\)]*\)\s*{/",
			$content,
			$arrFunction
		);
		$s = '';
		MmDebug::printData($arrFunction);
		foreach($arrFunction[8] as $k => $functionName)
		{
			$s .= '<h3>'.$functionName.'</h3>';
			$s .= self::_bindComment($arrFunction[2][$k]);
		}
		return $s;
	}
	private static function _function($contentNoClass)
	{
		
	}
	/**
	 * 检测项目目录 , db
	 */
	public static function checkProject()
	{
		$file = isset($_GET['file']) ? $_GET['file'] : '';
		if($file)
			return self::_readFile($file);
		$arrList = array(
			'dir' => array(
				MM_ROOT,
				MM_CORE_ROOT,
				MM_APP_ROOT,
				MM_APP_PATH,
				MM_APP_MODELS_DIR,
				MM_APP_DAL_DIR,
				MM_APP_ACTION_DIR,
				TPL_PATH,
				APP_NAME,
			),
			'file' => array(
//				MM_ROOT.'/index.php',
			),
		);
		
		//scan dir
//		self::_checkDir(MM_APP_ROOT);
		self::_checkDir(MM_ROOT);
	}
	/**
	 * 检测文件
	 */
	private static function _checkFile($file)
	{
		if(!preg_match("/\.php$/", $file)) return;
		$commentPattern = "\/\*\*\s*\n(:?(:?.*\*(.*)\n)*)\s*\*\/[\s\n]*";
		$arr = array(
//			'define'=>'\s*define\s*\(\s*([\w\d_]+)\s*,\s*.*\s*\)\s*;\s*\n',
			'class'=>'[^\n]*class\s+([\w\d_]+)',
//			'const'=>'\s*const\s+[\w\d_]+\s*=\s*.*\s*;\s*\n',
			'function'=>'[^\n]*function\s+([\w\d_]+)\s*\(.*\)',
		);
		$content = file_get_contents($file);
		$content = preg_replace("/\/\/[^\n]*/",'',$content);
		$contentNum = '';
		
		$arrReturn = array();
		foreach($arr as $k => $pattern)
		{
			$pattern = '/('.$commentPattern.')?('.$pattern.')/';
//			$pattern = '/('.$commentPattern.')/';
//			MmDebug::printData($pattern);
			preg_match_all($pattern, $content, $matches);
//			MmDebug::printData($matches);
			
			foreach($matches[1] as $i => $v)
				if(empty($v))
					$arrReturn[$k][] = $matches[5][$i];
		}
		
		if($arrReturn)
		{
			$file1 = urlencode($file);
			$s = "<h1>文件:{$file}  <a href='/index.php?action=cv&file={$file1}' target='_blank;'>源码</a></h1>";
			foreach($arrReturn as $type => $arr)
			{
				if($arr)
				{
					$s.="<dl style='color:red;'><dt>{$type}</dt>";
					foreach($arr as $code)
					{
						$code1 = urlencode($code);
						$s.="<dd>$code <a href='/index.php?action=cv&file={$file1}&name={$code1}#_mmfeiflag_' target='_blank;'>源码</a></dd>";
					}
					$s.="</dl>";
				}
			}
			echo $s;
		}
		else
			echo "<div style='color:green;'>正常文件:$file</div>";
	}
	private static function _readFile($file)
	{
		$name = isset($_GET['name']) ? $_GET['name'] : '';
		if($file)
		{
			$name1 = urldecode($name);
			$file = urldecode($file);
//			MmDebug::printData($name1);
//			MmDebug::printData($name);
			if(file_exists($file))
			{
				$content = file_get_contents($file);
				$content = str_replace("{$name1}", "{$name1}_mmfeiflag_", $content);
				$content = highlight_string($content,true);
				echo str_replace(array('_mmfeiflag_',), array('<a href="_mmfeiflag_"></a>'), $content);
			}
		}
	}
	/**
	 * 检测目录
	 * @param string $path
	 */
	private static function _checkDir($path)
	{
		$arrIgnore = array(
//			'\.$',
//			MM_CORE_ROOT,
			MM_APP_PATH.'/data/',
			MM_APP_PATH.'/ext/',
		);
		$ig = join('|',$arrIgnore);
		$ig = str_replace("/", "\/", $ig);
		foreach(new DirectoryIterator($path) as $dir)
		{
			if($dir->isDot() || preg_match("/$ig/",$dir->getPathname())) continue;
			if($dir->isFile())
				self::_checkFile($dir->getPathname());
			else
				self::_checkDir($path.'/'.$dir->getFilename());
		}
		return true;
	}
}