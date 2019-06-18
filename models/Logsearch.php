<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\base\Model;

/**
 * This is the model class search " Logsparse".
 *
 * @property date $datestart
 * @property string $range
 * @property string $os
 * @property string $brous
 * @property string $architecture 
  * @property output array $total
 */
class Logsearch extends Logsparse
{
	public $datestart;
	public $start;
	public $range;
	public $os = 'all';
	public $brous;
	public $architecture;
	public $total;
	
	
	public function rules()
    {
        return [
            [['range'], 'safe'],
            [['os', 'brous', 'architecture'], 'string', 'max' => 100],
			[['datestart'], 'string', 'max' => 10],
        ];
    }

    public function attributeLabels()
    {
        return [
            'range'        => 'Интервал',
            'os'           => 'OS',
            'brous'        => 'Браузер',
            'architecture' => 'Архитектура',
            'datestart'    => 'Начальная дата',
        ];
    }
	
	
	public function getLog()
	{
		
		if($this->datestart == null)
		{
			$this->datestart = Logsparse::getMinDate();
		}			
		
		if(empty($this->range))
		{
			$this->range = 'week';
		}
		
		if($this->os == 'all') $system = null; else $system = $this->os;

		$dates = Logsparse::find()
			->where('date >= :datestart', [':datestart' => $this->datestart])
			->andWhere('date <= :dateend', [':dateend' => $this->getDateAnd()])
			->andFilterWhere(['architecture' => $this->architecture])
			->distinct(true)
			->indexBy('date')
			->all();
			
		if($dates)	
		{
			foreach($dates as $date)
			{
				
				$brous = Logsparse::find()
					->select(['brous', 'os', 'COUNT(brous) AS pop_brous'])
					->where('date = :date', [':date' => $date->date])
					->andFilterWhere(['architecture' => $this->architecture, 'os' => $system])	
					->groupBy('brous')
					->asArray()
					->indexBy('pop_brous')
					->all();
					
				ksort($brous);
				
				$total_brous_array = array_pop($brous);
					
				$array = Logsparse::find()
					->select(['url', 'os', 'COUNT(url) AS pop_url'])
					->where('date = :date', [':date' => $date->date])
					->andFilterWhere(['architecture' => $this->architecture,  'os' => $system])
					->groupBy('url')
					->asArray()
					->indexBy('pop_url')
					->all();
					
				ksort($array);
				
				$total_brous_url = array_pop($array);
				$total_pop_requests = end (array_keys($array));
				
				$total_pop_requests3 = 0;
				$keys = array_keys($brous);
				for ($i = 0; $i <= 3; $i++) {
					$total_pop_requests3 += $keys[count($keys)-$i];
				}

				$total[str_replace(' 00:00:00', '',  $date->date)] = [
					'total_requests' => array_sum(array_keys($array)),
					'total_url'      => $total_brous_url['url'],
					'total_brous'    => $total_brous_array['brous'],
					'total_poprequests' => end (array_keys($array)),
					'total_toprequests' => $total_pop_requests3,
					'total_topbrous' => array_sum(array_keys($brous)),
				];
				
			}
		}
		else
		{
			$total[str_replace(' 00:00:00', '',  $this->datestart)] = [
				'total_requests' => 0,
				'total_url'      => '',
				'total_brous'    => '',
				'total_poprequests' => 0,
				'total_toprequests' => 0,
			];
		}
		
		return $total;
	}
	
	public function getChart()
	{
		
		$array = $json['data'] = $this->getLog();
	
		$keys = array_keys($array);
		
		switch ($this->range) {
			default:
			case 'week':
				$i = 0;
				foreach($array as $day => $value)
				{
					$key =  date('w', strtotime($day));
					$week[$key]['total_requests'] = $value['total_requests'];
					$week[$key]['total_poprequests'] = $value['total_poprequests'];
					$week[$key]['total_toprequests'] = ($value['total_topbrous']) ? $value['total_toprequests'] * 100 / $value['total_topbrous'] : 0;
				}
				$json['urls']['label'] = $json['brous']['label'] = ' Запросов';
				$json['populars']['label'] = ' Популярных';
				$json['totals']['label'] = ' ТОП-3 браузеров';
				while($i < 7)
				{
					if(isset($week[$i]))
					{
						$json['urls']['data'][] = array($i, $week[$i]['total_requests']);
						$json['populars']['data'][] = array($i, $week[$i]['total_poprequests']);
						$json['brous']['data'][] = array($i, 100);
						$json['totals']['data'][] = array($i, $week[$i]['total_toprequests']);
					}
					else
					{
						$json['urls']['data'][] = array($i, 0);
						$json['populars']['data'][] = array($i, 0);
						$json['brous']['data'][] = array($i, 0);
						$json['totals']['data'][] = array($i, 0);
						$i++;
					}
					$i++;
				}
								
				for ($i = 0; $i < 7; $i++) {
					$date = date('Y-m-d', $keys[0] + ($i * 86400));

					$json['xaxis'][] = array(date('w', strtotime($date)), date('D', strtotime($date)));
				}
				
				break;
				
			case 'month':
				$array_date = explode('-', $this->datestart);
				$days = cal_days_in_month(CAL_GREGORIAN, $array_date[1], $array_date[0]);
				
				foreach($array as $day => $value)
				{
					$key =  date('j', strtotime($day));
					$week[$key]['total_requests'] = $value['total_requests'];
					$week[$key]['total_poprequests'] = $value['total_poprequests'];
					$week[$key]['total_toprequests'] = ($value['total_topbrous']) ? $value['total_toprequests'] * 100 / $value['total_topbrous'] : 0;
				}
				
				$i = 0;
				while($i < $days)
				{
					if(isset($week[$i]))
					{
						$json['urls']['data'][] = array($i, $week[$i]['total_requests']);
						$json['populars']['data'][] = array($i, $week[$i]['total_poprequests']);
						$json['brous']['data'][] = array($i, 100);
						$json['totals']['data'][] = array($i, $week[$i]['total_toprequests']);
					}
					else
					{
						$json['urls']['data'][] = array($i, 0);
						$json['populars']['data'][] = array($i, 0);
						$json['brous']['data'][] = array($i, 0);
						$json['totals']['data'][] = array($i, 0);
					}
					$i++;
					
				}
				
				for ($i = 1; $i <= $days; $i++) {
					$date = $array_date[0] . '-' . $array_date[1] . '-' . $i;

					$json['xaxis'][] = array(date('j', strtotime($date)), date('d', strtotime($date)));
				}
				break;
			
			case 'year':
				foreach($array as $day => $value)
				{
					$key =  date('n', strtotime($day));
					$month[$key]['total_requests'] += $value['total_requests'];
					$month[$key]['total_poprequests'] += $value['total_poprequests'];
					$month[$key]['total_toprequests'] = ($value['total_topbrous']) ? $value['total_toprequests'] * 100 / $value['total_topbrous'] : 0;
				}
				$i = 0;
				while($i < 12)
				{
					if(isset($month[$i]))
					{
						$json['urls']['data'][] = array($i, $month[$i]['total_requests']);
						$json['populars']['data'][] = array($i, $month[$i]['total_poprequests']);
						$json['brous']['data'][] = array($i, 100);
						$json['totals']['data'][] = array($i, $month[$i]['total_toprequests']);
					}
					else 
					{
						$json['urls']['data'][] = array($i, 0);
						$json['populars']['data'][] = array($i, 0);
						$json['brous']['data'][] = array($i, 0);
						$json['totals']['data'][] = array($i, 0);
					}
					$i++;
				}

				for ($i = 1; $i <= 12; $i++) {
					$json['xaxis'][] = array($i, date('M', mktime(0, 0, 0, $i)));
				}
				break;
		}
				
		return $json;
	}
	
	public function getOs()
	{
		$models = Logsparse::find()->select(['os'])->distinct(true)->OrderBy('os', SORT_ASC)->all();
		$array = ArrayHelper::map($models, 'os', 'os');
		$array['all'] = 'Все';
		
		return $array;
	}
	
	public function getArchitecture()
	{
		return ['' => 'Все', 'x32' => 'x32', 'x64' => 'x64'];
	}
	
	private function getDateAnd()
	{
		switch ($this->range) {
			default:
			case 'week':
				$array_date = explode('-', $this->datestart);
				$dateend = date('Y-m-d', strtotime($this->datestart) + 86400 *7);
				break;
			case 'month':
				$array_date = explode('-', $this->datestart);				
				$dateend = date('Y-m-d', strtotime($this->datestart) + 86400 *7);
				break;
			case 'year':
				$array_date = explode('-', $this->datestart);
				$days = date('L', strtotime($array_date[0])) ? 366 : 365;
				$dateend = date('Y-m-d', strtotime($this->datestart) + 86400 * $days);
				break;
		}
		return $dateend;
	}
}
