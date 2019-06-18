<?php
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">
  <legend>Фильтр</legend>
  <?php $form = ActiveForm::begin(['id' => 'filter-form']); ?>
    <div class="row" style="margin-bottom:50px">
	  <div class="col-md-3">
	    <?php echo $form->field($model, 'datestart')->widget(\yii\jui\DatePicker::class, [
			'language' => 'ru',
			'dateFormat' => 'yyyy-MM-dd',
		]) ?>
	  </div>
	  <div class="col-md-3">
		<?php echo$form->field($model, 'range')->dropDownList(['week' => 'С начала недели', 'month' => 'С начала месяца', 'year' => 'С начала года'])->label('Интервал'); ?>
	  </div>
	  <div class="col-md-3">
		<?php echo$form->field($model, 'os')->dropDownList($model->getOs())->label('Операционная система'); ?>
	  </div>
	  <div class="col-md-3">
		<?php echo$form->field($model, 'architecture')->dropDownList($model->getArchitecture())->label('Архитектура'); ?>
	  </div>
    </div>
  <?php ActiveForm::end(); ?>
  <div class="row" style="margin-bottom:50px">
	  <div class="col-md-6">
	    <legend style="position:relative">Число запросов 	  
		</legend>
		<div id="chart-request" style="width: 100%; height: 260px;"></div>
	  </div>
	  <div class="col-md-6">
	    <legend>ТОП 3 запросов</legend>
		<div id="chart-total" style="width: 100%; height: 260px;"></div>
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
  function Aply() {		
	  $.ajax({
		  type: 'get',
		  url: '$url',
		  data: $('#filter-form').serialize(),
		  type: 'POST',
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
			
			$.plot('#chart-request', [json['urls'], json['populars']], option);
			$.plot('#chart-total', [json['urls'], json['totals']], option);
			
		  },
			error: function(xhr, ajaxOptions, thrownError) {
           alert(thrownError);
        }
	  });
  };
  
  $('select, input').on('change', function(){ 
	Aply();
  });
  
  Aply();
JS;
$this->registerJs($indexjs, yii\web\View::POS_READY);
$this->registerJsFile('/js/jquery/flot/jquery.flot.js',   ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('/js/jquery/flot/jquery.flot.resize.min.js',   ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
