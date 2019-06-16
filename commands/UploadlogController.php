<?php

namespace app\commands;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;


class UploadlogController extends Controller
{
	
	public function actionIndex($file = null)
    {
	
		//unkoment to increase memory capacity
		//ini_set('memory_limit', '2048M');

		if(!is_dir(Yii::getAlias('@app') .'/web/uploads/')) {
		   echo "Этой папки нет \n" . Yii::getAlias('@app') . "/web/uploads/ \n"; 
		   return ExitCode::OK;
		} 

		if (!file_exists(Yii::getAlias('@app') .'/web/uploads/'. $file))
		{
			echo Yii::getAlias('@app') .'/web/uploads/'. $file ."\n"; 
			echo "Загррузите файл в /web/uploads/ \n"; 
			return ExitCode::OK;
		}
	
		$file_handle = fopen(Yii::getAlias('@app') .'/web/uploads/'. $file, "r");
		
		$pattern = "/(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\")/";
		
		while (!feof($file_handle))
		{
			$line = fgets($file_handle);
		  
			if(strpos($line, 'assets') ||
				strpos($line, 'upload') ||
				strpos($line, 'resize') ||
				strpos($line, '.js') ||
				strpos($line, '.png') ||
				strpos($line, '.svg') ||
				strpos($line, 'ajax') ||
				strpos($line, '?') || 
				strpos($line, '.txt') || 
				strpos($line, 'adsbot') ||
				strpos($line, 'bingbot') || 
				strpos($line, 'Bot') ||
				strpos($line, 'bot') ||
				strpos($line, 'facebook') ||
				strpos($line, 'Researchscan') ||
				strpos($line, 'python') ||
				strpos($line, 'curl')
			) continue;
		  
			preg_match_all($pattern, $line, $result);
		  		  
			if(!$os = $this->GetOS($result [13][0])) continue;

			$insert[] = [
				'ip' => $result [1][0],
				'date' =>  date('Y-m-d H:i:s',  strtotime(str_replace('/', '-', $result[4][0]) . $result[5][0])),
				'url' => $result [8][0],
				'agent' => $result [13][0],
				'os' => $os,
				'brous' => $this->GetBrows($result [13][0]),
				'architecture' => $this->GetArchitecture($result [13][0]),
			];
		  
		}
		
		fclose($file_handle);
		
		$columns = array('ip', 'date', 'url', 'agent', 'os', 'brous', 'architecture');
		Yii::$app->db->createCommand()->batchInsert('logsparse', $columns, $insert)->execute();
		
		echo "Импорт успешно завершен"; 
		return ExitCode::OK;
    }
	
	private function GetOS($string)
	{
		if(stripos($string,"windows ") != null ||
			stripos($string,"winnt") != null ||
			stripos($string,"win ") != null ||
			preg_match("/win[0-9]{2}/i",$string))
		{
			preg_match("/(windows |winnt|win |win[0-9]{2})[^;)]*/i",$string,$regs);
			return $regs[0];
		}
		
		if(stripos($string,"powerpc") != null ||
			stripos($string,"macintosh") != null ||
			stripos($string,"mac os") != null)
		{
			return "Macintosh";
		}
	
		if(stripos($string,"freebsd") != null ||
			stripos($string,"linux") != null ||
			stripos($string,"unix") != null ||
			stripos($string,"lynx") != null)
		{
			preg_match("/(freebsd|linux|unix|lynx)[^;)-]*/i",$string,$regs);
			return $regs[0];
		}
		
		if(stripos($string,"/bots yabs")) return;

		return 'other';
	}

	private function GetBrows($string)
	{
		if(stripos($string,"Opera") !== false)
		{
			preg_match("/Opera[^\"(\[]*/i",$string,$regs);
			return str_replace("/"," ",$regs[0]);
		}

		if(stripos($string,"OPR") !== false)
		{
			preg_match("/OPR[^\"(\[]*/i",$string,$regs);
			return 'Opera '. str_replace("OPR/"," ",$regs[0]);
		}

		if(stripos($string,"Edge") != null)
		{
			preg_match("/Edge ([^;)]*)/i",$string,$regs);
			return "Microsoft Edge $regs[1]";
		}

		if(stripos($string,"Chrome") != null)
		{
			preg_match("/Chrome ([^;)]*)/i",$string,$regs);
			return "Chrome $regs[1]";
		}

		if(stripos($string,"konqueror") !== false ||
			stripos($string,"safari") !== false)
		{
			preg_match("/(konqueror|safari)[^;)]*/i",$string,$regs);
			return str_replace("/"," ",$regs[0]);
		}

		if(stripos($string,"gecko") !== false)
		{
			preg_match("/rv:([^;)]*)/i",$string,$regs);
			if(stripos($string,"firefox") !== false)
				$str = " (Firefox)";
			elseif(stripos($string,"netscape") !== false)
				$str = " (Netscape)";
			elseif(stripos($string,"YaBrowser") !== false)
				$str = " (Yandex browser)";				
			return "Mozilla$str $regs[1]";
		}

		if(stripos($string,"myie") !== false)
		{
			preg_match("/myie[^;)]*/i",$string,$regs);
			return str_replace("/"," ",$regs[0]);
		}

		if(stripos($string,"MSIE") != null)
		{
			preg_match("/MSIE([^;)]*)/i",$string,$regs);
			return "Internet Explorer $regs[1]";
		}

		return 'other';
	} 

	private function GetArchitecture($string)
	{
		if(stripos($string,"x64") || stripos($string,"WOW64"))
		{
			return 'x64';
		}
		
		if(stripos($string,"Windows NT"))
		{
			return 'x32';
		}
		
		return 'xXX';
	} 

}
