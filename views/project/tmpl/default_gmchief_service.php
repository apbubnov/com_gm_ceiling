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
if(!empty($this->item->calcs_mounting_sum)){
    $dealer_moung_sum = json_decode($this->item->calcs_mounting_sum);
    foreach ($dealer_moung_sum as $key=>$sum){
        $total_dealer_mount +=$sum;
    }
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
<style>
    .center-left {
        width: 100%;
        text-align: center;
        margin-bottom: 15px;
    }
    .calculation_sum {
        width: 100%;
        margin-bottom: 25px;
    }
    .calculation_sum td {
        padding: 0 5px;
    }
    #table1 {
        width: 100%;
        font-size: 13px;
    }
    #table1 button, #table1 a, #table1 input {
        font-size: 13px;
        max-width: 150px;
    }
    #table1 td, #table1 th {
        padding: 10px 5px;
    }
    #table1 tr
    {
        border-bottom: 1px solid #414099;
        border-top: 1px solid #414099;
    }
    #table1 td
    {
        cursor: default;
    }
    .wtf_padding {
        padding: 0;
    }
    .no_yes_padding {
        padding: 0;
    }
    #calendar1, #calendar2 {
        display: inline-block;
        width: 100%;
        padding: 0;
    }
    #container_calendars {
        width: 100%;
    }
    #button-prev, #button-next {
        padding: 0;
    }
    #calcs_total_border {
        display: inline-block;
        width: auto;
        padding: 3px 7px;
        border: 2px solid #414099;
    }
    @media screen and (min-width: 768px) {
        .center-left {
            text-align: left;
        }
        #table1 {
            width: 100%;
            max-width: 3000px;
            font-size: 1em;
        }
        #table1 td, #table1 th {
            padding: 15px;
        }
        #table1 button, #table1 a, #table1 input {
            font-size: 1em;
            width: auto;
            max-width: 200px;
        }
        .wtf_padding {
            padding: 15px;
        }
        .no_yes_padding {
            padding: 15px;
        }
        #calendar1, #calendar2 {
            width: calc(50% - 25px);
        }
        #calendar2 {
            margin-left: 30px;
        }
    }
</style>
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
        <div class="row">
            <div class="col-xs-12 no_padding">
                <h4>Расчеты для проекта</h4>
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#summary" role="tab">Общее</a>
                    </li>
                    <?php foreach ($calculations as $k => $calculation) { ?>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#calculation<?php echo $calculation->id; ?>" role="tab">
                                <?php echo $calculation->calculation_title; ?>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if(!in_array($this->item->project_status,VERDICT_STATUSES)){?>
                        <li class="nav-item">
                            <button type="button" class="nav-link" id="add_calc" style="color:white;" <?php echo $hidden?>>
                                Добавить потолок <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                            </button>
                        </li>
                    <?php }?>
                </ul>
                <?php if($user->dealer_type == 1 && count($calculations) <= 0) { ?>
                    <p>У Вас еще нет потолков</p>
                <?php } else { ?>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="summary" role="tabpanel">
                            <table id="table1">
                                <tr style="background-color: rgba(0,0,0,0.15);">
                                    <th colspan="4" class="section_header" id="sh_ceilings">
                                        Потолки <i class="fa fa-sort-desc" aria-hidden="true" style="cursor: pointer;"></i>
                                    </th>
                                </tr>
                                <?php
                                foreach ($calculations as $calculation) { ?>
                                    <tr class="section_ceilings" style="background-color: rgba(0,0,0,0.05);">
                                        <td class="include_calculation" >
                                            <span><i><b><?php echo $calculation->calculation_title; ?></b></i></span>
                                        </td>
                                    </tr>
                                    <tr class="section_ceilings" style="background-color: rgba(0,0,0,0.0);">
                                        <td>S/P :</td>
                                        <td colspan="3">
                                            <?php echo $calculation->n4; ?> м<sup>2</sup> / <?php echo $calculation->n5; ?> м
                                        </td>
                                    </tr>
                                    <?php
                                    if ($calculation->discount > 0) {
                                        $kol++;
                                    }
                                }
                                ?>
                                <tr style="background-color: rgba(0,0,0,0.05);">
                                    <th>Общая S/общий P :</th>
                                    <th id="total_square">
                                        <span class = "sum"><?php echo round($total_square,2);?></span> м<sup>2</sup> /
                                    </th>
                                    <th colspan="2" id="total_perimeter">
                                        <span class = "sum"><?php echo  round($total_perimeter,2); ?></span> м
                                    </th>
                                </tr>
                                <tr style="background-color: rgba(0,0,0,0.15);">
                                    <th colspan="4">Транспортные расходы</th>
                                </tr>
                                <tr>
                                    <td>
                                        <?php if($this->item->transport == 1){
                                            echo "<b>Траноспорт по городу</b>";
                                        }
                                        elseif ($this->item->transport == 2){
                                            echo "Выезд за город";
                                        }
                                        else{
                                            echo "Без транспорта";
                                        }?>
                                    </td>
                                    <td>
                                        <?php echo "<b>Кол-во выездов:</b> ".$this->item->distance_col;?>
                                    </td>
                                    <?php $distance = !empty(floatval($this->item->distance)) ? $this->item->distance : "-";?>
                                    <td>
                                        <?php echo "<b>Кол-во км.:</b> ".$distance;?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Стоимость</b></td>
                                    <td colspan="3" id="transport_sum">
                                        <span class="sum" data-selfval = <?php echo $self_sum_transport ?>><?=$client_sum_transport;?></span> р.
                                    </td>
                                    <!-- <input id="transport_suma" value='<?php //echo $client_sum_transport; ?>' type='hidden'> -->
                                </tr>


                                <tr style="background-color: rgba(0,0,0,0.05);">
                                    <td id="calcs_self_canvases_total"><span>П </span> <span class = "sum"><?php echo round($self_canvases_sum, 0) ?></span></td>
                                    <td id="calcs_self_components_total"><span>К </span><span data-oldval = <?php echo round($self_components_sum, 0) ?> class = "sum"><?php echo round($self_components_sum, 0) ?></span></td>
                                    <td id="calcs_self_mount_total">
                                        <div class="col-md-6">
                                            <span>МС </span><span class = "sum"><?php echo round($self_mounting_sum+$self_sum_transport, 0); ?></span>
                                        </div>
                                        <div class="col-md-6">
                                            <span>МД </span><span class = "sum"><?php echo round($total_dealer_mount+$self_sum_transport, 0); ?></span>
                                        </div>
                                    </td>
                                    <td id="calcs_total"><div id="calcs_total_border"><?php echo round($project_self_total  , 0); ?></div></td>
                                </tr>

                                <tr style="background-color: rgba(0,0,0,0.15);">
                                    <th colspan="2" class="section_header" id="sh_estimate">Сметы и наряды на монтаж <i class="fa fa-sort-desc" aria-hidden="true" style="cursor: pointer;"></i></th>
                                    <th colspan="2" class="section_header">
                                        <button class = "btn btn-primary" type="button" id = "create_pdfs">Перегенерировать</button>
                                    </th>
                                </tr>
                                <tr style="background-color: rgba(0,0,0,0.05);">
                                    <th colspan="4" class="section_estimate" style="display: none;">Сметы:</th>
                                </tr>
                                <?php foreach ($calculations as $calculation) { ?>
                                    <tr class="section_estimate" id="section_estimate_<?= $calculation->id; ?>" style="display:none;">
                                        <?php
                                        $path = "/costsheets/" . md5($calculation->id . "client_single") . ".pdf";
                                        $pdf_names[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "client_single") . ".pdf", "id" => $calculation->id);
                                        ?>
                                        <td>
                                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                                <input name='include_pdf[]' value='<?php echo $path; ?>' data-name='Смета <?php echo $calculation->calculation_title; ?>' type='checkbox' checked="checked" style="cursor: pointer;">
                                            <?php } ?>
                                            <?php echo $calculation->calculation_title; ?>
                                        </td>
                                        <td colspan="3">
                                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                                            <?php } else { ?>
                                                -
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                $json = json_encode($pdf_names);
                                ?>

                                <?php
                                if (is_array($this->item->mount_data)) {
                                    $mount_data = $this->item->mount_data;
                                }
                                else {
                                    $mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
                                }
                                ?>

                                <?php if (($user->dealer_type == 1 && $user->dealer_mounters == 0) || $user->dealer_type != 1) { ?>
                                    <tr style="background-color: rgba(0,0,0,0.05);">
                                        <th class="section_estimate" style="display: none;" colspan="4">Наряды на монтаж:</th>
                                    </tr>
                                    <?php foreach ($calculations as $calculation) { ?>
                                        <tr class="section_estimate" id="section_mount_<?= $calculation->id; ?>" style="display:none;">
                                            <td>
                                                <?php echo $calculation->calculation_title; ?>
                                            </td>
                                            <td colspan="3">
                                                <?php
                                                /*Полный монтаж*/
                                                if (count($mount_data) === 0 || (count($mount_data) === 1 && $mount_data[0]->stage == 1)) {
                                                    $path = "/costsheets/" . md5($calculation->id . "mount_single_gm") . ".pdf";
                                                    $path_service = "/costsheets/" . md5($calculation->id . "mount_single_dealer") . ".pdf";
                                                    if (file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {
                                                        echo '<a href="'.$path.'" class="btn btn-secondary" target="_blank">Наряд бригаде</a>';
                                                    }
                                                    if (file_exists($_SERVER['DOCUMENT_ROOT'].$path_service)) {
                                                        echo '<a href="'.$path_service.'" class="btn btn-secondary" target="_blank">Наряд МС</a>';
                                                    }
                                                }
                                                else {
                                                    foreach ($mount_data as $value) {
                                                        $path = "/costsheets/" . md5($calculation->id.'mount_stage'.$value->stage).'.pdf';
                                                        $path_service = "/costsheets/" . md5($calculation->id.'mount_stage_service'.$value->stage).'.pdf';
                                                        if (file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {
                                                            switch ($value->stage) {
                                                                case 2:
                                                                    echo '<a href="'.$path.'" class="btn btn-secondary" target="_blank">Обагечивание</a>';
                                                                    echo '<a href="'.$path_service.'" class="btn btn-secondary" target="_blank">Обагечивание МС</a>';
                                                                    break;
                                                                case 3:
                                                                    echo '<a href="'.$path.'" class="btn btn-secondary" target="_blank">Натяжка</a>';
                                                                    echo '<a href="'.$path_service.'" class="btn btn-secondary" target="_blank">Натяжка МС</a>';
                                                                    break;
                                                                case 4:
                                                                    echo '<a href="'.$path.'" class="btn btn-secondary" target="_blank">Вставка</a>';
                                                                    echo '<a href="'.$path_service.'" class="btn btn-secondary" target="_blank">Вставка МС</a>';
                                                                    break;
                                                                default:
                                                                    break;
                                                            }
                                                        }
                                                    }
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    $json1 = json_encode($pdf_names_mount);
                                    ?>
                                <?php } ?>
                                <!--------------- Общая смета для клиента -------------->
                                <tr class="section_estimate" style="display: none;">
                                    <?php $path = "/costsheets/" . md5($this->item->id . "client_common") . ".pdf"; ?>
                                    <td>
                                        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                        <input name='include_pdf[]' value='<?php echo $path; ?>' data-name='Общая смета' type='checkbox' checked="checked" style="cursor: pointer;">
                                        <b>Общая смета<b>
                                                <?php } ?>
                                    </td>
                                    <td colspan="3">
                                        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                            <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank" id = "show">Посмотреть</a>
                                        <?php } else { ?>
                                        <span data-href="<?=$path;?>">-
                                            <?php } ?>
                                    </td>
                                </tr>
                                <!-- общий наряд на монтаж-->
                                <?php if (($user->dealer_type == 1 && $user->dealer_mounters == 0) || $user->dealer_type != 1) { ?>
                                    <tr class="section_estimate" style="display: none;">
                                        <?php
                                            $path_service = "/costsheets/" . md5($this->item->id . "mount_common_dealer") . ".pdf";
                                            $path = "/costsheets/" . md5($this->item->id . "mount_common_gm") . ".pdf";
                                        ?>
                                        <td>
                                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                                <input name='include_pdf[]' value='<?php echo $path; ?>' data-name='Общий наряд на монтаж' type='checkbox' checked="checked" style="cursor: pointer;">
                                                <b>Общий наряд на монтаж <b>
                                            <?php } ?>
                                        </td>
                                        <td colspan="3">
                                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Наряд бригаде</a>
                                            <?php }
                                                 if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path_service)) { ?>
                                                <a href="<?php echo $path_service; ?>" class="btn btn-secondary" target="_blank">Наряд МС</a>
                                            <?php }
                                            $pdf_names[] = array("name" => "Подробная смета", "filename" => md5($this->item->id . "mount_common") . ".pdf", "id" => $this->item->id);
                                            $json2 = json_encode($pdf_names);
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                            </table>
                        </div>
                        <?php
                        foreach ($calculations as $k => $calculation) {
                            $mounters = json_decode($calculation->mounting_sum);
                            if (!empty($calculation->n3)) {
                                $filename = "/calculation_images/" . md5("calculation_sketch" . $calculation->id) . ".svg";
                            }
                            ?>
                            <div class="tab-pane" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
                                <div class="other_tabs">
                                    <?php if($this->item->project_status < 5 || $this->item->project_status == 22)
                                    {
                                        $type_url = '';
                                        if (!empty($type))
                                        {
                                            $type_url = "&type=$type";
                                        }

                                        $subtype_url = '';
                                        if (!empty($subtype))
                                        {
                                            $subtype_url = "&subtype=$subtype";
                                        }

                                        $button_url = "index.php?option=com_gm_ceiling&view=calculationform2$type_url$subtype_url&calc_id=$calculation->id";
                                        ?>

                                        <?php if(!in_array($this->item->project_status,VERDICT_STATUSES)){ ?>
                                        <a class="btn btn-primary change_calc" href="<?php echo $button_url; ?>" data-calc_id="<?php echo $calculation->id; ?>" <?php echo $hidden; ?>>Изменить расчет</a>
                                    <?php }?>
                                        <?php
                                    } ?>
                                    <?php if (!empty($filename)) { ?>
                                        <div class="sketch_image_block" style="margin-top: 15px;">
                                            <h4>Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i></h4>
                                            <div class="section_content">
                                                <img class="sketch_image" src="<?php echo $filename.'?t='.time(); ?>"/>
                                            </div>
                                        </div>
                                    <?php }
                                    $filename = ''; ?>
                                    <div class="row">
                                        <div class="col-xs-12 wtf_padding">
                                            <?php
                                            if (!empty($calculation->n3)){
                                                $canvas = $canvas_model->getFilteredItemsCanvas("`a`.`id` = $calculation->n3");
                                                ?>
                                                <h4>Материал</h4>
                                                <table class="table_info2">
                                                    <tr>
                                                        <td>Тип фактуры:</td>
                                                        <td><?php echo $canvas[0]->texture_title; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Производитель, ширина:</td>
                                                        <td><?php echo $canvas[0]->name.' '.$canvas[0]->width; ?></td>
                                                    </tr>
                                                    <?php
                                                    if (!empty($canvas[0]->color_id)) {
                                                        ?>
                                                        <tr>
                                                            <td>Цвет:</td>
                                                            <td>
                                                                <?php echo $canvas[0]->color_title; ?>
                                                                <img src="/<?php echo $canvas[0]->color_file; ?>" alt=""/>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </table>
                                            <?php } ?>
                                            <h4 style="margin: 10px 0;">Размеры помещения</h4>
                                            <table class="table_info2">
                                                <tr>
                                                    <td>Площадь, м<sup>2</sup>:</td>
                                                    <td><?php echo $calculation->n4; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Периметр, м:</td>
                                                    <td><?php echo $calculation->n5; ?></td>
                                                </tr>
                                            </table>

                                            <?php if ($calculation->n6 > 0) { ?>
                                                <h4 style="margin: 10px 0;">Вставка</h4>
                                                <table class="table_info2">
                                                    <tr>
                                                        <?php if ($calculation->n6 == 314) { ?>
                                                            <td>Белая</td>
                                                            <td></td>
                                                            <?php
                                                        } else{
                                                            $color = $components_model->getColorId($calculation->n6);
                                                            ?>
                                                            <td>Цветная:</td>
                                                            <td>
                                                                <?php echo $color->title; ?> <img style='width: 50px; height: 30px;' src="/<?php echo $color->file; ?>"/>
                                                            </td>
                                                        <?php } ?>
                                                    </tr>
                                                </table>
                                            <?php } ?>
                                            <?php if ($calculation->n16) { ?>
                                                <table class="table_info2">
                                                    <tr>
                                                        <td>Скрытый карниз:</td>
                                                        <td><?php echo $calculation->n16; ?></td>
                                                    </tr>
                                                </table>
                                            <?php } ?>
                                            <?php if ($calculation->n12) { ?>
                                                <h4 style="margin: 10px 0;">Установка люстры</h4>
                                                <table class="table_info2">
                                                    <tr>
                                                        <td><?php echo $calculation->n12; ?> шт.</td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            <?php }
                                            ?>
                                            <?php if ($calculation->n13) { ?>
                                                <h4 style="margin: 10px 0;">Установка светильников</h4>
                                                <table class="table_info2">
                                                    <?php
                                                    foreach ($calculation->n13 as $key => $n13_item) {
                                                        echo "<tr><td><b>Количество:</b> " . $n13_item->n13_count . " шт - <b>Тип:</b>  " . $n13_item->type_title . " - <b>Размер:</b> " . $n13_item->component_title . "</td></tr>";
                                                    }
                                                    ?>
                                                </table>
                                            <?php } ?>
                                            <?php if ($calculation->n14) { ?>
                                                <h4 style="margin: 10px 0;">Обвод трубы</h4>
                                                <table class="table_info2">
                                                    <?php
                                                    foreach ($calculation->n14 as $key => $n14_item) {
                                                        echo "<tr><td><b>Количество:</b> " . $n14_item->n14_count . " шт  -  <b>Диаметр:</b>  " . $n14_item->component_title . "</td></tr>";
                                                    }
                                                    ?>
                                                </table>
                                            <?php } ?>
                                            <?php if ($calculation->n15) { ?>
                                                <h4 style="margin: 10px 0;">Шторный карниз Гильдии мастеров</h4>
                                                <table class="table_info2">
                                                    <?php
                                                    foreach ($calculation->n15 as $key => $n15_item) {
                                                        echo "<tr><td><b>Количество:</b> " . $n15_item->n15_count . " шт - <b>Тип:</b>   " . $n15_item->type_title . " <b>Длина:</b> " . $n15_item->component_title . "</td></tr>";
                                                    }
                                                    ?>
                                                </table>
                                            <?php } ?>
                                            <?php if ($calculation->n27> 0) { ?>
                                                <h4 style="margin: 10px 0;">Шторный карниз</h4>
                                                <table class="table_info2">
                                                    <tr>
                                                        <td>
                                                            <?php if ($calculation->n16) echo "Скрытый карниз"; ?>
                                                            <?php if (!$calculation->n16) echo "Обычный карниз"; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $calculation->n27; ?> м.
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php } ?>
                                            <?php if ($calculation->n26) { ?>
                                                <h4 style="margin: 10px 0;">Светильники Эcola</h4>
                                                <table class="table_info2">
                                                    <?php
                                                    foreach ($calculation->n26 as $key => $n26_item) {
                                                        echo "<tr><td><b>Количество:</b> " . $n26_item->n26_count . " шт - <b>Тип:</b>  " . $n26_item->component_title_illuminator . " -  <b>Лампа:</b> " . $n26_item->component_title_lamp . "</td></tr>";
                                                    }
                                                    ?>
                                                </table>
                                            <?php } ?>
                                            <?php if ($calculation->n22) { ?>
                                                <h4 style="margin: 10px 0;">Вентиляция</h4>
                                                <table class="table_info2">
                                                    <?php
                                                    foreach ($calculation->n22 as $key => $n22_item) {
                                                        echo "<tr><td><b>Количество:</b> " . $n22_item->n22_count . " шт - <b>Тип:</b>   " . $n22_item->type_title . " - <b>Размер:</b> " . $n22_item->component_title . "</td></tr>";
                                                    }
                                                    ?>
                                                </table>
                                            <?php } ?>
                                            <?php if ($calculation->n23) { ?>
                                                <h4 style="margin: 10px 0;">Диффузор</h4>
                                                <table class="table_info2">
                                                    <?php
                                                    foreach ($calculation->n23 as $key => $n23_item) {
                                                        echo "<tr><td><b>Количество:</b> " . $n23_item->n23_count . " шт - <b>Размер:</b>  " . $n23_item->component_title . "</td></tr>";
                                                    }
                                                    ?>
                                                </table>
                                            <?php } ?>
                                            <?php if ($calculation->n29) { ?>
                                                <h4 style="margin: 10px 0;">Переход уровня</h4>
                                                <table class="table_info2">
                                                    <?php
                                                    foreach ($calculation->n29 as $key => $n29_item) {
                                                        echo "<tr><td><b>Количество:</b> " . $n29_item->n29_count . " м - <b>Тип:</b>  " . $n29_item->type_title . "</td></tr>";
                                                    }
                                                    ?>
                                                </table>
                                            <?php } ?>
                                            <h4 style="margin: 10px 0;">Прочее</h4>
                                            <table class="table_info2">
                                                <?php if ($calculation->n9> 0) { ?>
                                                    <tr>
                                                        <td>Углы, шт.:</td>
                                                        <td><?php echo $calculation->n9; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n10> 0) { ?>
                                                    <tr>
                                                        <td> Криволинейный вырез, м:</td>
                                                        <td><?php echo $calculation->n10; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n11> 0) { ?>
                                                    <tr>
                                                        <td>Внутренний вырез, м:</td>
                                                        <td><?php echo $calculation->n11; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n7> 0) { ?>
                                                    <tr>
                                                        <td>Крепление в плитку, м:</td>
                                                        <td><?php echo $calculation->n7; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n8> 0) { ?>
                                                    <tr>
                                                        <td>Крепление в керамогранит, м:</td>
                                                        <td><?php echo $calculation->n8; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n17> 0) { ?>
                                                    <tr>
                                                        <td>Закладная брусом, м:</td>
                                                        <td><?php echo $calculation->n17; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n19> 0) { ?>
                                                    <tr>
                                                        <td> Провод, м:</td>
                                                        <td><?php echo $calculation->n19; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n20> 0) { ?>
                                                    <tr>
                                                        <td>Разделитель, м:</td>
                                                        <td><?php echo $calculation->n20; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n21> 0) { ?>
                                                    <tr>
                                                        <td>Пожарная сигнализация, м:</td>
                                                        <td><?php echo $calculation->n21; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->dop_krepezh> 0) { ?>
                                                    <tr>
                                                        <td>Дополнительный крепеж:</td>
                                                        <td><?php echo $calculation->dop_krepezh; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n24> 0) { ?>
                                                    <tr>
                                                        <td>Сложность доступа к месту монтажа, м:</td>
                                                        <td><?php echo $calculation->n24; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n30> 0) { ?>
                                                    <tr>
                                                        <td>Парящий потолок, м:</td>
                                                        <td><?php echo $calculation->n30; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n32> 0) { ?>
                                                    <tr>
                                                        <td>Слив воды, кол-во комнат:</td>
                                                        <td><?php echo $calculation->n32; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </table>
                                            <?php $extra_mounting = (array) json_decode($calculation->extra_mounting);?>
                                            <?php if (!empty($extra_mounting) ) { ?>
                                                <h4 style="margin: 10px 0;">Дополнительные работы</h4>
                                                <table class="table_info2">
                                                    <?php
                                                    foreach($extra_mounting as $dop) {
                                                        echo "<tr><td><b>Название:</b></td><td>" . $dop->title .  "</td></tr>";
                                                    }
                                                    ?>
                                                </table>
                                            <?php } ?>
                                        </div>
                                        <?php if($this->item->project_status < 5 || $this->item->project_status == 22 ){?>
                                            <button class="btn btn-danger delete_calc" data-calculation_id = "<?php echo $calculation->id;?>" style="margin:10px;" type="button" <?php echo $hidden?>> Удалить потолок </button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
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
