<?php
/**
 * @package    Com_Gm_ceiling
 * @author     apbubnov <al.p.bubnov@gmail.com>
 * @copyright  2018 apbubnov
 */
// No direct access
defined('_JEXEC') or die;
/*_____________блок для всех моделей/models block________________*/ 
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$calculationform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
$reserve_model = Gm_ceilingHelpersGm_ceiling::getModel('reservecalculation');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$clients_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
$components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
$canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
$model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
$repeat_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');

$dealer = JFactory::getUser($this->item->dealer_id);
$calculations = $calculationsModel->new_getProjectItems($this->item->id);
$json_mount = $this->item->mount_data;
if(!empty($this->item->mount_data)){

    $mount_types = $projects_mounts_model->get_mount_types(); 
    $this->item->mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
    foreach ($this->item->mount_data as $value) {
        $value->stage_name = $mount_types[$value->stage];
        if(!array_key_exists($value->mounter,$stages)){
            $stages[$value->mounter] = array((object)array("stage"=>$value->stage,"time"=>$value->time));
        }
        else{
            array_push($stages[$value->mounter],(object)array("stage"=>$value->stage,"time"=>$value->time));
        }
    }
    foreach ($calculations as $calc) {
        foreach ($stages as $key => $value) {
           foreach ($value as $val) {
              Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($calc->id,$key,$val->stage,$val->time,null,1);
           }

        }
    }
    Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
}
$transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id);
$client_sum_transport = $transport['client_sum'];
$self_sum_transport = $transport['mounter_sum'];//идет в монтаж
$self_calc_data = [];
$self_canvases_sum = 0;
$self_components_sum = 0;
$self_mounting_sum = 0;
$project_self_total = 0;
$project_total = 0;
$project_total_discount = 0;
$total_square = 0;
$total_perimeter = 0;
$calculation_total_discount = 0;
if (!empty($this->item->calcs_mounting_sum)) {
    $service_mount = get_object_vars(json_decode($this->item->calcs_mounting_sum));
}
$calculations = $calculationsModel->new_getProjectItems($this->item->id);
if(!empty($service_mount)){
    $self_sum_transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id,"service")['mounter_sum'];
}
foreach ($calculations as $calculation) {
    $mount_data = Gm_ceilingHelpersGm_ceiling::calculate_mount(0,$calculation->id,null,"serviceSelf");
    $calculation->dealer_self_gm_mounting_sum = $mount_data['total_gm_mounting'];
    $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/, $this->item->dealer_canvases_margin);
    $calculation->dealer_components_sum = double_margin($calculation->components_sum, 0 /*$this->item->gm_components_margin*/, $this->item->dealer_components_margin);
    $calculation->dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0 /*$this->item->gm_mounting_margin*/, $this->item->dealer_mounting_margin);
    $calculation->dealer_self_canvases_sum = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
    $self_canvases_sum +=$calculation->dealer_self_canvases_sum;
    $calculation->dealer_self_components_sum = margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/);
    $self_components_sum += $calculation->dealer_self_components_sum;

    $self_mounting_sum += $calculation->dealer_self_gm_mounting_sum;
    $calculation->calculation_total = $calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum;
    $calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $calculation->discount) / 100);
    $calculation->n13 = $calculationform_model->n13_load($calculation->id);
    $calculation->n14 = $calculationform_model->n14_load($calculation->id);
    $calculation->n15 = $calculationform_model->n15_load($calculation->id);
    $calculation->n22 = $calculationform_model->n22_load($calculation->id);
    $calculation->n23 = $calculationform_model->n23_load($calculation->id);
    $calculation->n26 = $calculationform_model->n26_load($calculation->id);
    $calculation->n29 = $calculationform_model->n29_load($calculation->id);
    $total_square +=  $calculation->n4;
    $total_perimeter += $calculation->n5;
    $project_total += $calculation->calculation_total;
    $project_total_discount += $calculation->calculation_total_discount;
    $self_calc_data[$calculation->id] = array(
        "canv_data" => $calculation->dealer_self_canvases_sum,
        "comp_data" => $calculation->dealer_self_components_sum,
        "mount_data" => $calculation->dealer_self_gm_mounting_sum,
        "square" => $calculation->n4,
        "perimeter" => $calculation->n5,
        "sum" => $calculation->calculation_total,
        "sum_discount" => $calculation->calculation_total_discount
    );
    $calculation_total = $calculation->calculation_total;
    $calculation_total_discount =  $calculation->calculation_total_discount;
}
$self_calc_data = json_encode($self_calc_data);//массив с себестоимотью по каждой калькуляции
$project_self_total = $self_sum_transport + $self_components_sum + $self_canvases_sum + $self_mounting_sum; //общая себестоимость проекта
$mount_transport = $mountModel->getDataAll($this->item->dealer_id);
$min_project_sum = (empty($mount_transport->min_sum)) ? 0 : $mount_transport->min_sum;
$min_components_sum = (empty($mount_transport->min_components_sum)) ? 0 : $mount_transport->min_components_sum;

$project_total_discount_transport = $project_total_discount + $client_sum_transportt;

$del_flag = 0;
$project_total = $project_total + $client_sum_transport;
$project_total_discount = $project_total_discount  + $client_sum_transport;
/*________________________________________________________________*/
?>
<?= parent::getButtonBack(); ?>
<form id = "mount_form" action="/index.php?option=com_gm_ceiling&task=project.saveService" method="post" enctype="multipart/form-data">
	<input id="project_id" name = "project_id" type="hidden" value="<?php echo $this->item->id?>">
	<input id="mount" name="mount" type='hidden' value='<?php echo $json_mount ?>'>
	<input id="dealer_id" name="dealer_id" type="hidden" value="<?php echo $this->item->dealer_id?>">
</form>
<?php if ($this->item) : ?>
	<div class="container">
        <div class="row">
            <div class="col-xl item_fields">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
            </div>
        </div>
        <div class="row">
        	<div class="col-md-6">
        		<table class="table_info" style="border: 1px solid #414099;border-radius: 15px">
                        <tr>
                            <th>
                                Дилер
                            </th>
                            <td colspan=2>
                                <a href="/index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id=<?=$dealer->associated_client;?>">
                                    <?php echo $dealer->name ?>
                                </a>
                            </td colspan=2>
                        </tr>
                        <tr>
                            <th>
                                <?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
                            </th>
                            <td colspan=2>
                                <?php echo $dealer->username;?>
                            </td>
                        </tr>
                        <tr>
                            <th>Почта</th>
                            <td colspan=2>
                                <?php
                                    echo "<a href='mailto:$dealer->email'>$dealer->email</a>";
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?>
                            </th>
                            <td colspan=2>
                                <a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>">
                                    <?=$this->item->project_info;?>
                                </a>
                            </td> 
                        </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table_info" style="border: 1px solid #414099;border-radius: 15px">
                    <tr>
                        <th colspan="3" style="text-align: center">Монтаж</th>
                    </tr>
                    <?php if(!empty($this->item->mount_data)):?>
                        <?php foreach ($this->item->mount_data as $value) { ?>
                            <tr>
                                 <th>
                                    <?php echo $value->stage_name;?>
                                </th>
                                 <td>
                                    <?php echo $value->time;?>
                                </td>
                                <td>
                                    <?php echo JFactory::getUser($value->mounter)->name;?>
                                </td>
                           </tr>
                            <?php }?>
                    <?php endif;?>
                </table>
            </div>
        </div>
        <!-- расчеты для проекта -->
        <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="row center">
                    <h4>Назначить дату монтажа</h4>
                    <div id="calendar_mount" align="center"></div>
                    <button class="btn btn-primary" type = "button" id = "save">Сохранить</button>
                </div>
            </div>
        </div>

    <div class="modal_window_container" id="mw_container">
        <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div class="modal_window" id="modal_window_mounts_calendar"></div>
        <div id="mw_mounts_calendar" class="modal_window"></div>
	</div>
<?php endif;?>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
<script type="text/javascript">
    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);
    var $ = jQuery;
    var min_project_sum = <?php echo  $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;
    var self_data = JSON.parse('<?php echo $self_calc_data;?>');
    var project_id = "<?php echo $this->item->id; ?>";

    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var div = jQuery("#mw_mounts_calendar");

            if (!div.is(e.target)
                && div.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close_mw").hide();
                jQuery("#mw_container").hide();
                div.hide();
            }
        });
    jQuery(document).ready(function(){
    	jQuery("#save").click(function(){
    		jQuery("#mount_form").submit();
    	});
    });
</script>
