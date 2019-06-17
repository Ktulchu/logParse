<?php
use yii\helpers\Url;
/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">
  <legend>Фильтр</legend>
  <div class="row">
	<div class="col-md-4">
	  
	</div>
	<div class="col-md-4">

	</div>
	<div class="col-md-4">

	</div>
  </div>
	
  <div class="row">
	  <div class="col-md-6">
	    <legend style="position:relative">Число запросов 
		  <a href="#" class="dropdown-toggle pull-right" data-toggle="dropdown"><i class="fa fa-calendar"></i> <i class="caret"></i></a>
		  <ul id="range" class="dropdown-menu dropdown-menu-right">
			<li><a href="day">День</a></li>
			<li class="active"><a href="week">Неделя</a></li>
			<li><a href="month">Месяц</a></li>
			<li><a href="year">Год</a></li>
		  </ul>
		</legend>
		<div id="chart-sale" style="width: 100%; height: 260px;"></div>
	  </div>
	  <div class="col-md-6">
	    <legend>ТОП 3 запросов</legend>
	  </div>
  </div>

  <div class="body-content">
	<legend>Запрсы к сайту</legend>
	<table class="table">
	  <thead>
	    <tr>
		  <td>Дата</td>
		  <td>Число запрсов за день</td>
		  <td>Популярный запрс</td>
		  <td>Популярный браузер</td>
		</tr>
	  </thead>
	  <tbody>
	    <?php foreach($total as $key => $col) : ?>
		  <tr>
		    <td><?php echo $key; ?></td>
			<td><?php echo $col['total_requests']; ?></td>
			<td><?php echo $col['total_url']; ?></td>
			<td><?php echo $col['total_brous']; ?></td>
		  </tr>
		<?php endforeach; ?>
	  </tbody>
	</table>
  </div>

</div>

<?php
$url = Url::to(['index']);
$indexjs = <<< JS
  $('#range a').on('click', function(e) {
	  e.preventDefault();
	  $(this).parent().parent().find('li').removeClass('active');
	  $(this).parent().addClass('active');
	  $.ajax({
		  type: 'get',
		  url: '$url?range=' + $(this).attr('href'),
		  dataType: 'json',
		  success: function(json) {
			  if (typeof json['urls'] == 'undefined') { return false; }
			  
			  var option = {	
				shadowSize: 0,
				colors: ['#9FD5F1', '#1065D2'],
				bars: { 
					show: true,
					fill: true,
					lineWidth: 1
				},
				grid: {
					backgroundColor: '#FFFFFF',
					hoverable: true
				},
				points: {
					show: false
				},
				xaxis: {
					show: true,
            		ticks: json['xaxis']
				}
			}
			
			$.plot('#chart-sale', [json['urls'], json['populars']], option);
			
		  },
			error: function(xhr, ajaxOptions, thrownError) {
           alert(thrownError);
        }
	  });
  });
  
  $('#range .active a').trigger('click');
JS;
$this->registerJs($indexjs, yii\web\View::POS_READY);
$this->registerJsFile('/js/jquery/flot/jquery.flot.js',   ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('/js/jquery/flot/jquery.flot.resize.min.js',   ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
