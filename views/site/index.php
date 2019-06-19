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
		<?php echo$form->field($model, 'range')->dropDownList(['week' => 'До конца недели', 'month' => 'До конца месяца', 'year' => 'До конца года'])->label('Интервал'); ?>
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
	    <legend>Доля трех самых популярных браузеров</legend>
		<div id="chart-total" style="width: 100%; height: 260px;"></div>
	  </div>
  </div>

  <div class="body-content">
	<legend>Запрсы к сайту</legend>
	<table id="table" class="table table-border sortable">
	  <thead>
	    <tr>
		  <th class="datesort">Дата</th>
		  <th class="recwestsort">Число запрсов за день</th>
		  <th class="urltsort">Популярный запрс</th>
		  <th class="broustsort">Популярный браузер</th>
		</tr>
	  </thead>
	  <tbody id="tbody">

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
			  if (typeof json['chart']['urls'] == 'undefined') { return false; }
			  
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
            		ticks: json['chart']['xaxis']
				}
			}
			
			$.plot('#chart-request', [json['chart']['urls'], json['chart']['populars']], option);
			$.plot('#chart-total', [json['chart']['brous'], json['chart']['totals']], option);
			$('#tbody').html(json['body']);
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
   
  function sort_rows(id, data, dir, type) {
	var tbl=document.getElementById(id);
	var tbodies=tbl.getElementsByTagName('tbody');
	var tmp_trs=tbodies[0].getElementsByTagName('tr');
	
	var all_trs=new Array();
	var tmp;

	for (var i=0; i<tmp_trs.length; i++) {
		tmp=tmp_trs[i].getAttribute('data-'+data);
		if (tmp) {
			tmp_trs[i].sort_value=type(tmp);
			all_trs.push(tmp_trs[i]);
		}
	}
	 

	all_trs.sort(function(a,b) {
		if (a.sort_value==b.sort_value) {
			return 0;
		}
		else {
			return (a.sort_value>b.sort_value?1:-1);
		}
	});
	 
	if (dir) {
		all_trs.reverse();
	}
	 
	var current_row;
	var last_row=null;
	for (i=all_trs.length-1; i>0; i--) {
		all_trs[i].parentNode.insertBefore(all_trs[i],last_row);
		last_row=all_trs[i];
	}
  }
  $(".datesort").on('click', function(){
	if($('th').hasClass('desc')){
		$('th').removeClass('desc').removeClass('asc')
		$(this).addClass('asc');
		sort_rows('table','date',true,String);
	} else {
		$('th').removeClass('desc').removeClass('asc')
		sort_rows('table','date',false,String);
		$(this).addClass('desc');
	}
  })
  
  $(".recwestsort").on('click', function(){
	if($('th').hasClass('desc')){
		$('th').removeClass('desc').removeClass('asc')
		$(this).addClass('asc');
		sort_rows('table','requests',false,String);
	} else {
		$('th').removeClass('desc').removeClass('asc')
		sort_rows('table','requests',true,Number);
		$(this).addClass('desc');
	}
  })
  
  $(".urltsort").on('click', function(){
	if($('th').hasClass('desc')){
		$('th').removeClass('desc').removeClass('asc')
		$(this).addClass('asc');
		sort_rows('table','url',false,String);
	} else {
		$('th').removeClass('desc').removeClass('asc')
		sort_rows('table','url',true,String);
		$(this).addClass('desc');
	}
  })
  
  $(".broustsort").on('click', function(){
	if($('th').hasClass('desc')){
		$('th').removeClass('desc').removeClass('asc')
		$(this).addClass('asc');
		sort_rows('table','brous',false,String);
	} else {
		$('th').removeClass('desc').removeClass('asc')
		sort_rows('table','brous',true,String);
		$(this).addClass('desc');
	}
  })
	
JS;
$this->registerJs($indexjs, yii\web\View::POS_READY);
$this->registerJsFile('/js/jquery/flot/jquery.flot.js',   ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('/js/jquery/flot/jquery.flot.resize.min.js',   ['depends' => [\yii\web\JqueryAsset::className()]]);

?>
