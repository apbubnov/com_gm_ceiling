<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$jinput = JFactory::getApplication()->input;
$user = JFactory::getUser();
$userId = (empty($jinput->getInt('id'))) ? $user->get('id') : $jinput->getInt('id');
$userType = JFactory::getUser($userId)->dealer_type;
//$model_dealer_info = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
//$margin = $model_dealer_info->getData();
$model_mount = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$mount = $model_mount->getDataAll($userId);
$gm_mount = json_encode($model_mount->getDataAll(1));

if(!$user->getDealerInfo()->update_check) {
	$user->setDealerInfo(["update_check" => true]);
}

$model_prices = Gm_ceilingHelpersGm_ceiling::getModel('prices');
$dealer_jobs = $model_prices->getJobsDealer($userId);
$gm_price = json_encode($model_prices->getJobsDealer(1));

$model_dealer_info = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
$dealer_info = $model_dealer_info->getDealerInfo($userId);
$style = "";
if($userType == 7){
    $style = "style=\"display:none;\"";
}
?>


<style>
  body {
    color: #414099;
  }
  .caption1 {
    text-align: center;
    padding: 15px 0;
    margin-bottom: 0;
    color: #414099;
  }
  .caption2 {
    text-align: center;
    height: auto;
    padding: 10px 0;
    border: 0;
    margin-bottom: 0;
    color: #414099;
  }
  input[type="text"] {
    padding: .5rem .75rem;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: .25rem;
  }
  .control-label {
    margin-top: 7px;
    margin-bottom: 0;
  }
</style>


<?php if($userType != 7) { ?>
    <div class="row">
        <div class="col-md-6"></div>
        <div style="text-align: right;" class="col-md-3">
            <a href="/index.php?option=com_users&view=profile&layout=edit" class="btn btn-large btn-primary">Изменить личные данные</a>
        </div>
        <div class="col-md-3">
            <a href="/index.php?option=com_gm_ceiling&view=dealerprofile&type=integration" class="btn btn-large btn-primary">Интеграция</a>
        </div>
    </div>
<?php } ?>
<!--<div class="row">
    <h5 class="caption2">Настройка просчетной страницы</h5>
    <div class="row">
        <div class="col-md-3">
            <button class="btn btn-primary" id="create_new_calcform">Создать свою</button>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary" id="edit_calc_form">Дублировать существующую и внести изменения</button>
        </div>
    </div>
</div>-->
<div id="dealer_form" action="/index.php?option=com_gm_ceiling&task=dealer.updatedata" method="post"  class="form-validate form-horizontal" enctype="multipart/form-data">
    <input type="hidden" name="jform[dealer_id]" id="jform_dealer_id" value="<?php echo $userId?>">
    <div <?=$style?>
        <h1 class="center">Редактирование маржинальности</h1>
        <h5 class="caption2">Укажите, какой процент прибыли от заказа Вы желаете получать</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="control-group">
                    <div class="control-label">
                        <label id="-lbl" for="jform_dealer_canvases_margin" class="hasTooltip required" >от полотен</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="jform[dejform_dealer_canvases_marginaler_canvases_margin]" id="jform_dealer_canvases_margin" class="required" style="width:100%;" size="3" required aria-required="true" value = "<?=$dealer_info->dealer_canvases_margin;?>" />
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_dealer_components_margin-lbl" for="jform_dealer_components_margin">от комплектующих</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="jform[dealer_components_margin]" id="jform_dealer_components_margin" value = "<?=$dealer_info->dealer_components_margin;?>"  class="required"style="width:100%;" size="3" required aria-required="true" />
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_dealer_mounting_margin-lbl" for="jform_dealer_mounting_margin">от монтажа</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="jform[dealer_mounting_margin]" id="jform_dealer_mounting_margin" value = "<?=$dealer_info->dealer_mounting_margin;?>"  class="required" style="width:100%;"size="3" required aria-required="true" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  <h3 class="caption1">Редактирование прайса монтажа</h3>
  <div>
    <button id = "fill_default" class="btn btn-primary" type = "button" >Заполнить по умолчанию</button>
    <button id = "reset_ap" class="btn btn-primary" type = "button" >Сбросить</button>
  </div>
  <br>
  <br>

  <?php 
  //print_r($dealer_info);
  ?>  
  <?php foreach ($dealer_jobs as $value) { ?>
    <div class="row" style="margin-top: 5px;">
      <div class="col-md-2"></div>
      <div class="col-md-5 control-label" > <label><?= $value->name;?></label> </div>
      <div class="col-md-3 left" > <input type="text" class="required input" style="width:100%;" size="3" required aria-required="true" value="<?= $value->price;?>" data-id="<?= $value->dp_id;?>" id="<?= $value->id;?>"/>
      </div>
      <div class="col-md-2"></div>
    </div>
  <?php } ?>
</div>

<div <?= $style;?>>
  <h3 class="caption1">Минимальная сумма заказа</h3>
  <div class="row">
    <div class="col-md-4">
      <div class="control-group">
        <div class="control-label">
          <label id="jform_min_sum-lbl" for="jform_min_sum" >Минимальная сумма</label>
        </div>
        <div class="controls">
          <input type="text" name="jform[min_sum]" id="jform_min_sum" value = "<?=$dealer_info->min_sum;?>" style="width:100%;" size="3" required aria-required="true" />				
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="control-label">
          <label id="jform_min_sum-lbl" for="jform_min_sum" >Сумма транспорта по городу</label>
        </div>
        <div class="controls">
          <input type="text" name="jform[transport]" id="jform_transport" value = "<?=$dealer_info->transport;?>" style="width:100%;" size="3" required aria-required="true" />       
        </div>
    </div>
    <div class="col-md-4">      
        <div class="control-label">
          <label id="jform_min_sum-lbl" for="jform_min_sum" >Сумма транспорта вне города (за 1 км.)</label>
        </div>
        <div class="controls">
          <input type="text" name="jform[distance]" id="jform_distance" value = "<?=$dealer_info->distance;?>" style="width:100%;" size="3" required aria-required="true" />       
        </div>
    </div>
  </div>
</div>
<div  class = "col-md-12" style="margin-top:15px;">
  <div class = "col-md-4"></div>
  <div class = "col-md-4">
    <button type="button" id="btn_save" class="btn btn-primary" style="width:100%;"> Сохранить </button>
  </div>
  <div class = "col-md-4"></div>
</div>	
</form>
<script>
  jQuery(document).ready(function(){

    var gm_price = JSON.parse('<?= $gm_price;?>');

    jQuery("#fill_default").click(function(){
      fill_inputs("fill");
    });

    jQuery("#reset_ap").click(function(){
      fill_inputs("reset");
    });

    function fill_inputs(type){
      switch(type){
        case 'reset':
        jQuery.each(jQuery('.input'), function(index, value){
          value.value = 0;
        });
        break;
        case 'fill':
        var i=0;
        jQuery.each(jQuery('.input'), function(index, value){
          value.value = gm_price[i].price;
          i++;
        });
        break;
      }
    }

    document.getElementById('btn_save').onclick = function() {
      if(document.getElementById('jform_dealer_canvases_margin') && document.getElementById('jform_dealer_components_margin')
        && document.getElementById('jform_dealer_mounting_margin')){
        if (!(/^\d+$/g).test(document.getElementById('jform_dealer_canvases_margin').value) ||
          document.getElementById('jform_dealer_canvases_margin').value < 0 ||
          document.getElementById('jform_dealer_canvases_margin').value > 99) {
          noty({
            theme: 'relax',
            timeout: 3000,
            layout: 'topCenter',
            maxVisible: 5,
            type: "warning",
            text: "Значение маржинальности должно быть в пределе [0..99]"
          });
        document.getElementById('jform_dealer_canvases_margin').focus();
        return;
      }
      if (!(/^\d+$/g).test(document.getElementById('jform_dealer_components_margin').value) ||
        document.getElementById('jform_dealer_components_margin').value < 0 ||
        document.getElementById('jform_dealer_components_margin').value > 99) {
        noty({
          theme: 'relax',
          timeout: 3000,
          layout: 'topCenter',
          maxVisible: 5,
          type: "warning",
          text: "Значение маржинальности должно быть в пределе [0..99]"
        });
      document.getElementById('jform_dealer_components_margin').focus();
      return;
    }
    if (!(/^\d+$/g).test(document.getElementById('jform_dealer_mounting_margin').value) ||
      document.getElementById('jform_dealer_mounting_margin').value < 0 ||
      document.getElementById('jform_dealer_mounting_margin').value > 99) {
      noty({
        theme: 'relax',
        timeout: 3000,
        layout: 'topCenter',
        maxVisible: 5,
        type: "warning",
        text: "Значение маржинальности должно быть в пределе [0..99]"
      });
    document.getElementById('jform_dealer_mounting_margin').focus();
    return;
  }
}
collectDataTable();
};
}); 

  var array = [];
  var data = [];
  function collectDataTable(){
    array = [];
    jQuery.each(jQuery('.input'), function(index, value){
      array.push({
        job_id: value.id,
        price: value.value.replace(',', '.').replace(/[^\d\.]/g, '')
      }); 
    });

    data = [];
    data = {
      canvases_margin: jform_dealer_canvases_margin.value.replace(',', '.').replace(/[^\d\.]/g, ''),
      components_margin: jform_dealer_components_margin.value.replace(',', '.').replace(/[^\d\.]/g, ''),
      mounting_margin: jform_dealer_mounting_margin.value.replace(',', '.').replace(/[^\d\.]/g, ''),
      min_sum: jform_min_sum.value.replace(',', '.').replace(/[^\d\.]/g, ''),
      transport: jform_transport.value.replace(',', '.').replace(/[^\d\.]/g, ''),
      distance: jform_distance.value.replace(',', '.').replace(/[^\d\.]/g, '')
    };
      console.log(data);
      saveData();
    }

    function saveData() {
      jQuery.ajax({
        type: 'POST',
        url: "index.php?option=com_gm_ceiling&task=dealer.updatedata",
        data: {
          array: array,
          dealer_id: <?= $userId ?>,
          data: data
        },
        success: function(data){
          console.log(data);
          var n = noty({
            timeout: 2000,
            theme: 'relax',
            layout: 'center',
            maxVisible: 5,
            type: "success",
            text: "Данные сохранены!"
          });
        },
        dataType:"json",
        async: false,
        timeout: 10000,
        error: function(data){
          var n = noty({
            timeout: 2000,
            theme: 'relax',
            layout: 'center',
            maxVisible: 5,
            type: "error",
            text: "Ошибка!"
          });
        }
      });
    }


  </script>