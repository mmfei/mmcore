<?php
namespace Mm\Libs;
/**
 * 客户端信息
 * @author mmfei<wlfkongl@163.com>
 */
class MmClientInfo
{
	static $agent = null;
	static $data = array(
	);
	/**
	 * 获取浏览器
	 * @return string
	*/
	public static function getBrowser()
	{
		return self::getBy('browser','');
	}
	/**
	 * 获取浏览器ID
	 * @return integer
	 */
	public static function getBrowserTypeId()
	{
		return self::getBy('browserTypeId',0);
	}
	/**
	 * 获取语言ID
	 * @return integer
	 */
	public static function getLanguageTypeId()
	{
		return self::getBy('languageTypeId',0);
	}
	/**
	 * 获取语言
	 * @return string
	 */
	public static function getLanguage()
	{
		return self::getBy('language','');
	}
	/**
	 * 获取客户端平台ID
	 * @return integer
	 */
	public static function getPlatformTypeId()
	{
		return self::getBy('platformTypeId',0);
	}
	/**
	 * 获取客户端平台
	 * @return string
	 */
	public static function getPlatform()
	{
		return self::getBy('platform','');
	}
	/**
	 * 获取key
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public static function getBy($key,$default='')
	{
		self::_init();
		return isset(self::$data[$key]) ? self::$data[$key] : $default;
	}
	/**
	 * 记录客户端信息
	 */
	private static function _init()
	{
		if(empty(self::$data))
		{
			self::_getBrowser();
			self::_getPlatform();
			self::_getLanguage();
		}
	}
	/**
	 * 获取客户端信息
	 * @return string
	 */
	private static function getAgent()
	{
		if(is_null(self::$agent))
		{
			self::$agent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
		}
		return self::$agent;
	}
	/**
	 * 获取客户端语言
	 */
	private static function _getLanguage()
	{
		$acceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		if(empty($acceptLanguage)) return ;
		$lang=substr($acceptLanguage,0,4);//只取前4位，这样只判断最优先的语言。如果取前5位，可能出现en,zh的情况，影响判断。
		if(preg_match("/zh-c/i",$lang))
			$language = "简体中文";
		elseif(preg_match("/zh/i",$lang))
		$language ="繁體中文";
		elseif(preg_match("/en/i",$lang))
		$language = "English";
		elseif(preg_match("/fr/i",$lang))
		$language = "French";
		elseif(preg_match("/de/i",$lang))
		$language = "German";
		elseif(preg_match("/jp/i",$lang))
		$language = "Japanese";
		elseif(preg_match("/ko/i",$lang))
		$language = "Korean";
		elseif(preg_match("/es/i",$lang))
		$language = "Spanish";
		elseif(preg_match("/sv/i",$lang))
		$language = "Swedish";
		else
			$language = 'Other';

		$arr = self::getLanguageConfig();
		self::$data['language'] = $language;
		self::$data['languageTypeId'] = isset($arr[$language]) ? $arr[$language] : 0;

	}
	/**
	 * 获取客户端浏览器
	 */
	private static function _getBrowser()
	{
		$agent = self::getAgent();
		$browserConfig = self::getBrowserConfig();
			
		$browserType = ""; // 浏览器
		$browserVersion = ""; // 浏览器的版本
		if (preg_match("/MSIE ([0-9]+)\.([0-9]{1,2})/", $agent, $version ))
		{
			$browserVersion = $version[1];
			$browserType = "Internet Explorer";
			$flag = $browserType.$browserVersion;
		}
		elseif (preg_match("/Opera\/([0-9]{1,2}.[0-9]{1,2})/", $agent, $version ))
		{
			$browserVersion = $version [1];
			$browserType = "Opera";
			$flag = $browserType;
		}
		elseif (preg_match("/Firefox\/([0-9.]{1,5})/", $agent, $version ))
		{
			$browserVersion = $version [1];
			$browserType = "Firefox";
			$flag = $browserType;
		}
		elseif (preg_match("/Chrome\/([0-9.]{1,3})/", $agent, $version ))
		{
			$browserVersion = $version [1];
			$browserType = "Chrome";
			$flag = $browserType;
		}
		elseif (preg_match("/Safari\/([0-9.]{1,3})/", $agent, $version ))
		{
			$browserType = "Safari";
			$flag = $browserType;
			$browserVersion = isset($version [1]) ? $version [1] : '';
		}
		else
		{
			$browserVersion = "";
			$browserType = "Unknown";
			$flag = $browserType;
		}
		$browserTypeId = isset($browserConfig[$flag]) ? $browserConfig[$flag] : 0;
		self::$data['browser'] = $browserType.$browserVersion;
		self::$data['browserTypeId'] = $browserTypeId;
	}
	/**
	 * 获取客户端系统环境
	 */
	private static function _getPlatform()
	{
		$agent = self::getAgent();
		$browserplatform = '';
		if (preg_match("/win/i", $agent ) && strpos ( $agent, '95' ))
		{
			$browserplatform = "Windows 95";
		}
		elseif (preg_match("/win 9x/i", $agent ) && strpos ( $agent, '4.90' ))
		{
			$browserplatform = "Windows ME";
		}
		elseif (preg_match("/win/i", $agent ) && preg_match("/98/", $agent ))
		{
			$browserplatform = "Windows 98";
		}
		elseif (preg_match("/win/i", $agent ) && preg_match("/nt 5.0/i", $agent ))
		{
			$browserplatform = "Windows 2000";
		}
		elseif (preg_match("/win/i", $agent ) && preg_match("/nt 5.1/i", $agent ))
		{
			$browserplatform = "Windows XP";
		}
		elseif (preg_match("/win/i", $agent ) && preg_match("/nt 6.0/i", $agent ))
		{
			$browserplatform = "Windows Vista";
		}
		elseif (preg_match("/win/i", $agent ) && preg_match("/nt 6.1/i", $agent ))
		{
			$browserplatform = "Windows 7";
		}
		elseif (preg_match("/win/i", $agent ) && preg_match("/32/", $agent ))
		{
			$browserplatform = "Windows 32";
		}
		elseif (preg_match("/win/i", $agent ) && preg_match("/nt/i", $agent ))
		{
			$browserplatform = "Windows NT";
		}
		elseif (preg_match("/Mac OS/i", $agent ))
		{
			$browserplatform = "Mac OS";
		}
		elseif (preg_match("/linux/i", $agent ))
		{
			$browserplatform = "Linux";
		}
		elseif (preg_match("/unix/i", $agent ))
		{
			$browserplatform = "Unix";
		}
		elseif (preg_match("/sun/i", $agent ) && preg_match("/os/i", $agent ))
		{
			$browserplatform = "SunOS";
		}
		elseif (preg_match("/ibm/i", $agent ) && preg_match("/os/i", $agent ))
		{
			$browserplatform = "IBM OS/2";
		}
		elseif (preg_match("/Mac/i", $agent ) && preg_match("/PC/i", $agent ))
		{
			$browserplatform = "Macintosh";
		}
		elseif (preg_match("/PowerPC/i", $agent ))
		{
			$browserplatform = "PowerPC";
		}
		elseif (preg_match("/AIX/i", $agent ))
		{
			$browserplatform = "AIX";
		}
		elseif (preg_match("/HPUX/i", $agent ))
		{
			$browserplatform = "HPUX";
		}
		elseif (preg_match("/NetBSD/i", $agent ))
		{
			$browserplatform = "NetBSD";
		}
		elseif (preg_match("/BSD/i", $agent ))
		{
			$browserplatform = "BSD";
		}
		elseif (preg_match("/OSF1/", $agent ))
		{
			$browserplatform = "OSF1";
		}
		elseif (preg_match("/IRIX/", $agent ))
		{
			$browserplatform = "IRIX";
		}
		elseif (preg_match("/FreeBSD/i", $agent ))
		{
			$browserplatform = "FreeBSD";
		}
		if ($browserplatform == '')
		{
			$browserplatform = "Other";
		}
		$arr = self::getPlatformConfig();
		self::$data['platform'] = $browserplatform;
		self::$data['platformTypeId'] = isset($arr[$browserplatform]) ? $arr[$browserplatform] : 0;
	}
	/**
	 * 获取语言版本
	 * @return array
	 */
	public static function getLanguageConfig()
	{
		return array(
			'Other' => 0,
			'简体中文' => 1,
			'繁體中文' => 2,
			'English' => 3,
			'French' => 4,
			'German' => 5,
			'Japanese' => 6,
			'Korean' => 7,
			'Spanish' => 8,
			'Swedish' => 9,
		);
	}
	/**
	 * 获取平台版本
	 * @return array
	 */
	public static function getPlatformConfig()
	{
		return array(
			'Other' => 0,
			'Windows 95' => 1,
			'Windows ME' => 2,
			'Windows 98' => 3,
			'Windows 2000' => 4,
			'Windows XP' => 5,
			'Windows Vista' => 6,
			'Windows 7' => 7,
			'Windows 32' => 8,
			'Windows NT' => 9,
			'Mac OS' => 10,
			'Linux' => 11,
			'Unix' => 12,
			'SunOS' => 13,
			'IBM OS/2' => 14,
			'Macintosh' => 15,
			'PowerPC' => 16,
			'AIX' => 17,
			'HPUX' => 18,
			'NetBSD' => 19,
			'BSD' => 20,
			'OSF1' => 21,
			'IRIX' => 22,
			'FreeBSD' => 23,
		);
	}
	/**
	 * 获取浏览器版本
	 * @return array
	 */
	public static function getBrowserConfig()
	{
		static $arr = array();
		if($arr) return $arr;
		foreach(self::_getBrowserConfig() as $browser => $a)
		{
			if(is_array($a))
			{
				foreach($a as $index => $version)
					$arr[$index] = $browser.$version;
			}
			else
				$arr[$a] = $browser;
		}
		$arr = array_flip($arr);
		return $arr;
	}
	private static function _getBrowserConfig()
	{
		return array(
			'other' => 0,
			'Internet Explorer' => array(
				'1'	=> '4',
				'2'	=> '5',
				'3'	=> '5.5',
				'4'	=> '6',
				'5'	=> '7',
				'6'	=> '8',
				'7'	=> '9',
				'8'	=> '10',
			),
			'NetCaptor' => 9,
			'Netscape' => 10,
			'Lynx' => 11,
			'Opera' => 12,
			'Konqueror' => 13,
			'Mozilla' => 14,
			'Chrome' => 15,
		);
	}
	/**
	 * 获取客户端IP地址
	 *
	 * @return string
	 */
	public static function getClientIp()
	{	
		if (!empty($_SERVER["HTTP_CLIENT_IP"]))
			return $_SERVER["HTTP_CLIENT_IP"];

		if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
			$proxy_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		elseif (($tmp_ip = getenv("HTTP_X_FORWARDED_FOR")))
			$proxy_ip = $tmp_ip;
		else
			$proxy_ip = '';
		
		if ('' !== $proxy_ip)
		{
			if (false === strpos($proxy_ip, ','))
				return $proxy_ip;
			
			foreach (explode(',', $proxy_ip) as $curr_ip)
			{// 处理可能有多级代理的情况
				if (false === stripos($curr_ip, 'unknown'))
					$curr_ip = ltrim($curr_ip);
				else
					continue;
				
				if (0 === strpos($curr_ip, '192.168.'))
					continue;	// 内网IP
				if (0 === strpos($curr_ip, '10.'))
					continue;	// 内网IP
				if (0 === strpos($curr_ip, '172.16.'))
					continue;	// 内网IP
				
				return $curr_ip;
			}
		}

		if(!empty($_SERVER["REMOTE_ADDR"]))
			return $_SERVER["REMOTE_ADDR"];
		elseif(($retvl = getenv("HTTP_CLIENT_IP")))
			return $retvl;
		elseif(($retvl = getenv("REMOTE_ADDR")))
			return $retvl;
		else
			return '0.0.0.0';
	}// end static method getClientIp
	/**
	 * 获取IP - 不穿过代理
	 * @return string
	 */
	public static function getClientIpNoProxy()
	{
		if(!empty($_SERVER["REMOTE_ADDR"]))
			return $_SERVER["REMOTE_ADDR"];
		return '';
	}
}