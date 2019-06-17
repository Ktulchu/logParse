<?php

namespace app\models;
use Yii;
use yii\base\Model;
/**
 * This is the model class search for table " logsparse".
 *
 * @property int $id
 * @property string $ip
 * @property datetime $date
 * @property string $url
 * @property string $agent
 * @property string $os
 * @property string $brous
 * @property string $architecture 
 */
class Logsearch extends Logsparse
{
	public $range;
	public $total;
	
	
	public function getLog()
	{
		$dates = Logsparse::find()->distinct(true)->indexBy('date')->all();
		
		foreach($dates as $date)
		{
			if($date->date != '1970-01-01 03:00:00')
			{
				$brous = Logsparse::find()
					->select(['brous', 'COUNT(brous) AS pop_brous'])
					->where('date = :date', [':date' => $date->date])
					->groupBy('brous')
					->asArray()
					->indexBy('pop_brous')
					->all();
					
				ksort($brous);
				
				$total_brous_array = array_pop($brous);
					
				$array = Logsparse::find()
					->select(['url', 'COUNT(url) AS pop_url'])
					->where('date = :date', [':date' => $date->date])
					->groupBy('url')
					->asArray()
					->indexBy('pop_url')
					->all();
					
				ksort($array);
				
				$total_brous_url = array_pop($array);
				$total_pop_requests = end (array_keys($array));
		
				$total[str_replace(' 00:00:00', '',  $date->date)] = [
					'total_requests' => array_sum(array_keys($array)),
					'total_url'      => $total_brous_url['url'],
					'total_brous'    => $total_brous_array['brous'],
					'total_poprequests' => end (array_keys($array)),
				];
			}
		}
		
		
		return $total;
	}
	
	public function getChart($range = null)
	{
		if (!$range) $range = 'week';
		
		$array = $this->getLog();
		
		$keys = array_keys($array);
		
		switch ($range) {
			default:
			case 'week':
				
				$i = 0;
				foreach ($array as $key => $value) {
					$json['urls']['data'][] = array($i, $value['total_requests']);
					$json['populars']['data'][] = array($i, $value['total_poprequests']);
					$i++;
					if ($i == 7) break;
				}
				if($i < 7)
				{
					while($i < 7)
					{
						$json['urls']['data'][] = array($i, 0);
						$json['populars']['data'][] = array($i, 0);
						$i++;
					}
				}
				
				
				for ($i = 0; $i < 7; $i++) {
					$date = date('Y-m-d', $keys[0] + ($i * 86400));

					$json['xaxis'][] = array(date('w', strtotime($date)), date('D', strtotime($date)));
				}
				break;
			case 'month':
				
				
				
				
				for ($i = 1; $i <= date('t'); $i++) {
					$date = date('Y') . '-' . date('m') . '-' . $i;

					$json['xaxis'][] = array(date('j', strtotime($date)), date('d', strtotime($date)));
				}
				break;
			
			case 'year':
				for ($i = 1; $i <= 12; $i++) {
					$json['xaxis'][] = array($i, date('M', mktime(0, 0, 0, $i)));
				}
				break;
		}
		
		return $json;
	}
	
}
