<?php
/**
 * @package    Com_Gm_ceiling
 * @author     apbubnov <al.p.bubnov@gmail.com>
 * @copyright  2018 apbubnov
 */
// No direct access
defined('_JEXEC') or die;
/*_____________блок для всех моделей/models block________________*/
$canvases_model = Gm_ceilingHelpersGm_ceiling::getModel("canvases");
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
$calculationformModel = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');

$components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
$canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
$user = JFactory::getUser();
$dealer = JFactory::getUser($this->item->dealer_id);
$calculations = $calculationsModel->new_getProjectItems($this->item->id);
$json_mount = $this->item->mount_data;
$stages = [];
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
    /*foreach ($calculations as $calc) {
        foreach ($stages as $key => $value) {
            foreach ($value as $val) {
                Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($calc->id,$key,$val->stage,$val->time,null,1);
            }

        }
    }*/
    Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
}
$transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id);
$client_sum_transport = $transport['client_sum'];
$self_sum_transport = $transport['mounter_sum'];//идет в монтаж
$self_calc_data = [];
$self_canvases_sum = 0;
$self_components_sum = 0;
$self_mounting_sum = 0;
$self_gm_mounting_sum = 0;
$project_self_total = 0;
$project_total = 0;
$project_total_discount = 0;
$total_square = 0;
$total_perimeter = 0;
//$calculation_total_discount = 0;
if (!empty($this->item->calcs_mounting_sum)) {
    $use_service = true ;
    $service_mount = get_object_vars(json_decode($this->item->calcs_mounting_sum));
}
else{
    foreach ($calculations as $calculation) {
        if ($calculation->need_mount == 2) {
            $use_service = true;
            break;
        }
    }
}

$calculations = $calculationsModel->new_getProjectItems($this->item->id);

if(!empty($service_mount)){
    $self_sum_transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id,"service")['mounter_sum'];
}
foreach ($calculations as $calculation) {
    if(!empty($calculation->n3)){
        $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/, $this->item->dealer_canvases_margin);
        $calculation->dealer_components_sum = double_margin($calculation->components_sum, 0 /*$this->item->gm_components_margin*/, $this->item->dealer_components_margin);
        $calculation->dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0 /*$this->item->gm_mounting_margin*/, $this->item->dealer_mounting_margin);
        $calculation->dealer_self_canvases_sum = $calculation->canvases_sum;
        //$self_canvases_sum +=$calculation->dealer_self_canvases_sum;
        $calculation->dealer_self_components_sum = margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/);
        //$self_components_sum += $calculation->dealer_self_components_sum;
        //$self_mounting_sum += $calculation->dealer_self_gm_mounting_sum;
        $calculation->calculation_total = $calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum;
        $calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $calculation->discount) / 100);
        $calculation->n13 = $calculationformModel->n13_load($calculation->id);
        $calculation->n14 = $calculationformModel->n14_load($calculation->id);
        $calculation->n15 = $calculationformModel->n15_load($calculation->id);
        $calculation->n22 = $calculationformModel->n22_load($calculation->id);
        $calculation->n23 = $calculationformModel->n23_load($calculation->id);
        $calculation->n26 = $calculationformModel->n26_load($calculation->id);
        $calculation->n29 = $calculationformModel->n29_load($calculation->id);
        $calculation->n19 = $calculationformModel->n19_load($calculation->id);
        $calculation->n45 = $calculationformModel->n45_load($calculation->id);

        $mount_data = Gm_ceilingHelpersGm_ceiling::calculate_mount(0,$calculation->id,null,"serviceSelf");
        $calculation->gm_self_mounting_sum = $mount_data['total_gm_mounting'];
        $calculation->dealer_self_mounting_sum = $mount_data['total_dealer_mounting'];

    }
    else{
        /*иначе она с новой структурой*/
        $total_gm_sum = 0;
        $total_dealer_sum = 0;
        $extraMount = (array) json_decode($calculation->extra_mounting);
        foreach ($extraMount as $key => $value) {
            $total_gm_sum += $value->price;
            if($use_service){
                if(!empty($value->service_price)){
                    $total_dealer_sum += $value->service_price;
                }
                else{
                    $total_dealer_sum += $value->price + $value->price*0.2;
                }
            }
        }

        if($use_service){
            $all_jobs = $calculationformModel->getMountingServicePricesInCalculation($calculation->id, $this->item->dealer_id);
        }
        else{
            $all_jobs = $calculationformModel->getJobsPricesInCalculation($calculation->id, $this->item->dealer_id);
        }
        //throw new Exception(print_r($all_jobs,true));
        foreach ($all_jobs as $job){
            $total_dealer_sum += $job->price_sum;
        }
        $all_gm_jobs = $calculationformModel->getJobsPricesInCalculation($calculation->id, 1);
        foreach ($all_gm_jobs as $job){
            $total_gm_sum += $job->price_sum;
        }

        $calculation->dealer_self_mounting_sum = $total_dealer_sum;
        $calculation->gm_self_mounting_sum = $total_gm_sum;
        $calculation->dealer_canvases_sum = $calculation->canvases_sum_with_margin;
        $calculation->dealer_components_sum = $calculation->components_sum_with_margin;
        $calculation->dealer_gm_mounting_sum = $calculation->mounting_sum_with_margin;
        $calculation->dealer_self_canvases_sum = $calculation->canvases_sum;
        $calculation->dealer_self_components_sum = $calculation->components_sum;
        $calculation->calculation_total = $calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum;
        $calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $calculation->discount) / 100);
    }



    $self_canvases_sum +=$calculation->dealer_self_canvases_sum;
    $self_components_sum += $calculation->dealer_self_components_sum;
    $self_mounting_sum += $calculation->dealer_self_mounting_sum;
    $self_gm_mounting_sum += $calculation->gm_self_mounting_sum;
    $total_square +=  $calculation->n4;
    $total_perimeter += $calculation->n5;
    $project_total += $calculation->calculation_total;
    $project_total_discount += $calculation->calculation_total_discount;
    $self_calc_data[$calculation->id] = [
        "canv_data" => $calculation->dealer_self_canvases_sum,
        "comp_data" => $calculation->dealer_self_components_sum,
        "mount_data" => $calculation->dealer_self_mounting_sum,
        "gm_mount_data" => $calculation->gm_self_mounting_sum,
        "square" => $calculation->n4,
        "perimeter" => $calculation->n5,
        "sum" => $calculation->calculation_total,
        "sum_discount" => $calculation->calculation_total_discount
    ];
    //$calculation_total = $calculation->calculation_total;
    //$calculation_total_discount =  $calculation->calculation_total_discount;
}
$self_calc_data = json_encode($self_calc_data); //массив с себестоимотью по каждой калькуляции
$project_self_total = $self_sum_transport + $self_components_sum + $self_canvases_sum + $self_mounting_sum; //общая себестоимость проекта

$mount_transport = $mountModel->getDataAll($this->item->dealer_id);
$min_project_sum = (empty($mount_transport->min_sum)) ? 0 : $mount_transport->min_sum;
$min_components_sum = (empty($mount_transport->min_components_sum)) ? 0 : $mount_transport->min_components_sum;

$project_total_discount_transport = $project_total_discount + $client_sum_transport;

$project_total = $project_total + $client_sum_transport;
$project_total_discount = $project_total_discount  + $client_sum_transport;
$final_sum = (!empty(floatval($this->item->new_project_sum))) ? $this->item->new_project_sum : $project_total_discount;
$pdf_names_mount = [];
$pdf_names = [];

if ($this->item->id_client!=1) {
    $phone = $calculationsModel->getClientPhones($this->item->id_client);
} else  {
    $phone = [];
}
$mount_notes = Gm_ceilingHelpersGm_ceiling::getProjectNotes($this->item->id,5);
$mount_note = "";
foreach ($mount_notes as $m_note) {
    if($m_note->author == JFactory::getUser()->id){

        $mount_note = $m_note->value;
    }
}
$final_sum = (!empty(floatval($this->item->new_project_sum))) ? $this->item->new_project_sum : $project_total_discount;
$kol = 0;
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
    .div_imgs {
        overflow-x: auto;
        white-space: nowrap;
    }
    .uploaded_calc_img {
        display: inline-block;
        max-width: 200px;
        padding: 2px 10px;
        cursor: pointer;
    }
    .uploaded_calc_img:hover {
        background: gray;
    }
    .big_uploaded_img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    #modal_window_img {
        width: 800px !important;
        height: 600px !important;
        margin: auto !important;
    }
    #modal_window_img {
        width: 360px !important;
        height: 400px !important;
        margin: auto !important;
    }
    #btn_del_img {
        display: none;
        position: fixed;
        top: 20px;
        left: 30px;
        cursor: pointer;
    }
    #btn_close_img {
        right: 0px;
    }

    @media screen and (min-width: 768px) {
        #btn_close_img {
            right: 30px;
        }
        #modal_window_img {
            width: 800px !important;
            height: 600px !important;
            margin: auto !important;
        }
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
        <div class="col-md-6" style="border: 1px solid #414099;border-radius: 15px">
            <table class="table_info" >
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
                <tr>
                    <th>Клиент</th>
                    <td colspan=2>
                        <?php echo $this->item->client_id; ?>
                    </td>
                </tr>
                <tr>
                    <th>Контакты</th>
                    <td colspan="2">
                        <?php
                        foreach ($phone AS $contact) {
                            echo "<a href='tel:+$contact->client_contacts'>$contact->client_contacts</a>";
                            echo "<br>";
                        }
                        ?>
                    </td>

                </tr>
            </table>
            <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-3 no_padding"><b>Ввести примечание к монтажу:</b></div>
                    <div class="col-md-6 col-xs-9 no_padding">
                        <textarea class="inputactive" id="mount_note" style="width: 98%;"><?=$mount_note?></textarea>
                    </div>
                    <div class="col-md-3 col-xs-3 no_padding">
                        <button type="button" class="btn btn-primary" id="btn_add_mount_note">Ок</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6" >
            <div style="border: 1px solid #414099;border-radius: 15px">
                <table class="table_info" >
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
                        <tr>
                            <td colspan="3">

                            </td>
                        </tr>
                    <?php endif;?>
                </table>
                <?php if(!empty($this->item->mount_data)):?>
                    <div class="row center">
                        <h4>Перенести дату монтажа</h4>
                        <div id="change_mount_div" align="center"></div>
                        <input type="hidden" id="change_mount"  value='<?php echo $json_mount ?>'>
                        <button class="btn btn-primary" type = "button" id = "save_changes">Сохранить</button>
                    </div>
                <?php endif;?>
            </div>


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
                                        echo "<b>Транспорт по городу</b>";
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
                                        <span>МС </span><span class = "gm_sum"><?php echo round($self_gm_mounting_sum+$self_sum_transport, 0); ?></span>
                                    </div>
                                    <div class="col-md-6">
                                        <span>МД </span><span class = "sum"><?php echo round($self_mounting_sum+$self_sum_transport, 0); ?></span>
                                    </div>

                                </td>
                                <td id="calcs_total"><div id="calcs_total_border"><?php echo round($project_self_total  , 0); ?></div></td>
                            </tr>
                            <tr style="background-color: rgba(0,0,0,0.15);">
                                <?php if ($kol > 0) { ?>
                                    <th>Итого/ - %:</th>
                                    <th id="project_total"><span class="sum">
                                    <?php echo round($project_total, 0); ?></span> р. /
                                    </th>
                                    <th id="project_total_discount">
                                        <?php
                                        //---------------  Если сумма проекта меньше 3500, то делаем сумму проекта 3500  -----------------------
                                        $old_price = $project_total_discount;
                                        if ($dealer_canvases_sum == 0 && $project_total_discount < $min_components_sum) {
                                            $project_total_discount = $min_components_sum;
                                        } elseif ($dealer_gm_mounting_sum_11 == 0 && $project_total_discount < $min_components_sum) {
                                            $project_total_discount = $min_components_sum;
                                        } elseif ($project_total_discount <  $min_project_sum && $project_total_discount > 0) {
                                            $project_total_discount =  $min_project_sum;
                                        }
                                        ?>
                                        <span class="sum"><?= round($project_total_discount, 0);?></span> р.
                                        <?php if($old_price != $project_total_discount): ?>
                                            <span class="dop" style="font-size: 9px;" > * минимальная сумма заказа <?php echo $min_project_sum;?>. </span>
                                        <?php endif; ?>
                                    </th>
                                <?php } else { ?>
                                    <th>Итого</th>
                                    <td colspan="3" id="project_total">
                                        <?php
                                        //---------------  Если сумма проекта меньше 3500, то делаем сумму проекта 3500  -----------------------
                                        $old_price = $project_total;
                                        if ($dealer_canvases_sum == 0 && $project_total < $min_components_sum) {
                                            $project_total = $min_components_sum;
                                        } elseif ($dealer_gm_mounting_sum_11 == 0 && $project_total < $min_components_sum) {
                                            $project_total = $min_components_sum;
                                        } elseif ($project_total <  $min_project_sum && $project_total > 0) {
                                            $project_total =  $min_project_sum;
                                        }
                                        ?>
                                        <b><span class="sum"><?= round($project_total, 0);?> </span>р.</b>
                                        <?php if($old_price != $project_total): ?>
                                            <span class="dop" style="font-size: 9px;" > * минимальная сумма заказа <?php echo $min_project_sum;?>. </span>
                                        <?endif;?>
                                    </td>
                                <?php } ?>
                            </tr>
                            <tr style="background-color: rgba(0,0,0,0.15);">
                                <th>
                                    Финальная сумма
                                </th>
                                <td colspan="3">
                                    <input class="input-gm final_sum" value="<?=$this->item->new_project_sum?>">
                                </td>

                            </tr>
                            <tr style="background-color: rgba(0,0,0,0.15);">
                                <th colspan="4">
                                    Предоплата
                                </th>
                            </tr>
                            <tr>
                                <td>
                                    Всего внесено
                                </td>
                                <td colspan="3">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <span id="prepayment_total" style="vertical-align: middle;"><?php echo !empty($this->item->prepayment_total) ? $this->item->prepayment_total : 0 ;?></span> руб.
                                        </div>
                                        <div class="col-md-3">
                                            <button id="show_detailed_prepayment" type="button" class="btn btn-primary" style="padding-right: 6px;padding-left: 6px;">Посмотреть детально</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr id="detailed_tr" style="display: none;">
                                <td id="detailed_td" colspan="4"></td>
                            </tr>
                            <tr>
                                <th>Остаток</th>
                                <td><b><span class="project_rest"><?=round($final_sum-$this->item->prepayment_total,0);?></span></b></td>
                                <td><b>за минусом з\п бригады: <?=round(($final_sum-$this->item->prepayment_total)-($self_mounting_sum+$self_sum_transport),0)  ?></b></td>
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
                    foreach ($calculations as $k => $calculation){
                        $allGoods = $calculationformModel->getGoodsPricesInCalculation($calculation->id,$this->item->dealer_id);
                        if(!empty($calculation->cancel_metiz)){
                            $calculation->goods = Gm_ceilingHelpersGm_ceiling::deleteMetizFromGoods($allGoods);
                        }
                        else{
                            $calculation->goods = $allGoods;
                        }
                        $calculation->jobs = $calculationformModel->getJobsPricesInCalculation($calculation->id,$this->item->dealer_id);
                        $calculation->factory_jobs = $calculationformModel->getFactoryWorksPricesInCalculation($calculation->id);
                        $mounters = json_decode($calculation->mounting_sum);
                        $filename = "/calculation_images/" . md5("calculation_sketch" . $calculation->id) . ".svg";
                        $canvas = null;
                        foreach($calculation->goods as $goods){
                            if($goods->category_id == 1){
                                $canvas = $goods;
                                break;
                            }
                        }
                        $detailed_canvas = '';
                        if (!empty($canvas)) {
                            $filter = "id = " . $canvas->goods_id;
                            $detailed_canvas = $canvas_model->getFilteredItemsCanvas($filter);
                        }
                        $color = $detailed_canvas[0]->color;
                        $hex = $detailed_canvas[0]->hex;
                        ?>
                        <div class="tab-pane" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
                            <div class="other_tabs">
                                <?php if($needShow){
                                    $type_url = ''; $subtype_url = '';
                                    if (!empty($type)){
                                        $type_url = "&type=$type";
                                    }
                                    if (!empty($subtype)){
                                        $subtype_url = "&subtype=$subtype";
                                    }
                                    if(!empty($calculation->n3)){
                                        $button_url = "index.php?option=com_gm_ceiling&view=calculationform2$type_url$subtype_url&calc_id=$calculation->id";
                                    }
                                    else{
                                        $button_url = "index.php?option=com_gm_ceiling&view=calculationform$type_url$subtype_url&calc_id=$calculation->id";
                                    }
                                    ?>

                                <?php } ?>
                                <div class="row" style="margin-bottom: 10px;">
                                    <div class="col-md-12">
                                        <?php if($needShow){ ?>
                                            <a class="btn btn-primary change_calc" href="<?php echo $button_url; ?>" data-calc_id="<?php echo $calculation->id; ?>">Изменить расчет</a>
                                        <?php }?>
                                        <input type="file" class="img_file" data-calc-id="<?= $calculation->id; ?>" data-img-type="before" style="display: none;" multiple accept="image/*">
                                        <button type="button" class="btn btn-primary btn_img_file"><i class="fa fa-camera" aria-hidden="true"></i></button>
                                        <input type="hidden" id="input_delete_uploaded_calc_img">
                                    </div>
                                </div>
                                <?php
                                $dir_before = 'uploaded_calc_images/'.$calculation->id.'/before';
                                $dir_after = 'uploaded_calc_images/'.$calculation->id.'/after';
                                $dir_defect = 'uploaded_calc_images/'.$calculation->id.'/defect';
                                $files = [];
                                $temp = [];
                                if (is_dir($dir_before)) {
                                    $temp = scandir($dir_before);
                                    foreach ($temp as $key => $value) {
                                        if (strlen($value) === 32) {
                                            $temp[$key] = $dir_before.'/'.$value;
                                        } else {
                                            unset($temp[$key]);
                                        }
                                    }
                                    $files = array_merge($files, $temp);
                                }
                                if (is_dir($dir_after)) {
                                    $temp = scandir($dir_after);
                                    foreach ($temp as $key => $value) {
                                        if (strlen($value) === 32) {
                                            $temp[$key] = $dir_after.'/'.$value;
                                        } else {
                                            unset($temp[$key]);
                                        }
                                    }
                                    $files = array_merge($files, $temp);
                                }
                                if (is_dir($dir_defect)) {
                                    $temp = scandir($dir_defect);
                                    foreach ($temp as $key => $value) {
                                        if (strlen($value) === 32) {
                                            $temp[$key] = $dir_defect.'/'.$value;
                                        } else {
                                            unset($temp[$key]);
                                        }
                                    }
                                    $files = array_merge($files, $temp);
                                }

                                if (empty($files)) {
                                    $col1 = 0;
                                    $col2 = 5;
                                } else {
                                    $col1 = 8;
                                    $col2 = 4;
                                }
                                ?>
                                <div class="row">
                                    <div class="col-md-<?=$col1?>">
                                        <div class="row div_imgs">
                                            <?php
                                            foreach ($files as $value) {
                                                echo '<img src="'.$value.'" data-path="'.str_replace('uploaded_calc_images/', '', $value).'" class="uploaded_calc_img">';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-<?=$col2;?>">
                                        <textarea class="inputactive" name="calc_comment" rows="5" ><?=$calculation->comment?></textarea>
                                        <button class="btn btn-primary" type="button" name="add_calc_comment" data-calc_id = "<?php echo $calculation->id;?>" style="width:100%;">Сохранить комментарий<i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php if (!empty($filename)) { ?>
                                            <div class="sketch_image_block" style="margin-top: 15px;">
                                                <h4>Чертеж </h4>
                                                <div class="section_content">
                                                    <img alt = 'Чертеж' class="sketch_image" style="width: 100%;max-height: 1000px;object-fit: contain" src="<?php echo $filename.'?t='.time(); ?>"/>
                                                </div>
                                            </div>
                                        <?php }
                                        $filename = ''; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-xs-12 wtf_padding">
                                                <?php if (!empty($detailed_canvas)){?>
                                                    <h4>Материал</h4>
                                                    <table class="table_info2">
                                                        <tr>
                                                            <td colspan="2">
                                                                <?php echo $detailed_canvas[0]->name?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style='width:20%'>Цвет:</td>
                                                            <td style='width:80%'>
                                                                <div class="col-md-3"><?=$color;?></div>
                                                                <div class="col-md-9" style="background-color:<?="#".$hex;?>;color:<?="#".$hex;?>"><?=$color;?></div>
                                                            </td>
                                                        </tr>

                                                    </table>
                                                <?php } ?>
                                                <?php if (!empty($calculation->n3)){
                                                    $canvas = $canvas_model->getFilteredItemsCanvas("`a`.`id` = $calculation->n3",'old');?>
                                                    <h4>Материал</h4>
                                                    <table class="table_info2">
                                                        <tr>
                                                            <td>
                                                                <?php echo $canvas[0]->texture_title.' '.$canvas[0]->name.' '.$canvas[0]->width;?>
                                                            </td>
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
                                                    <?php if ($calculation->n4 > 0) { ?>
                                                        <tr>
                                                            <td>Площадь, м<sup>2</sup>:</td>
                                                            <td><?php echo $calculation->n4; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n5 > 0) { ?>
                                                        <tr>
                                                            <td>Периметр, м:</td>
                                                            <td><?php echo $calculation->n5; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n9> 0) { ?>
                                                        <tr>
                                                            <td>Углы, шт.:</td>
                                                            <td><?php echo $calculation->n9; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </table>
                                                <?php if(!empty($calculation->goods)){?>
                                                    <h4 style="margin: 10px 0;cursor: pointer;" class="calc_goods"><i class="fas fa-angle-down"></i> Комплектующие</h4>
                                                    <table class="table_info2 table_goods" style="display:none;">
                                                        <thead>
                                                        <th>Название</th>
                                                        <th>Количество</th>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        foreach ($calculation->goods as $goods){
                                                            if($goods->category_id != 1){
                                                                echo "<tr><td>$goods->name</td><td>$goods->final_count</td></tr>";
                                                            }
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                <?php }?>
                                                <?php if(!empty($calculation->jobs)){?>
                                                    <h4 style="margin: 10px 0;cursor: pointer;" class="mount_jobs"><i class="fas fa-angle-down"></i> Монтажные работы</h4>
                                                    <table class="table_info2 table_jobs" style="display:none;">
                                                        <thead>
                                                        <th>Название</th>
                                                        <th>Количество</th>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach ($calculation->jobs as $job){
                                                            if(!$job->guild_only && !$job->is_factory_work){
                                                                echo "<tr><td>$job->name</td><td>".round($job->final_count,2)."</td></tr>";
                                                            }
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                <?php }?>
                                                <?php if(!empty($calculation->factory_jobs)){?>
                                                    <h4 style="margin: 10px 0;cursor: pointer;" class="factory_jobs"><i class="fas fa-angle-down"></i> Работы цеха</h4>
                                                    <table class="table_info2 table_factory_jobs" style="display:none;">
                                                        <thead>
                                                        <th>Название</th>
                                                        <th>Количество</th>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach ($calculation->factory_jobs as $job){
                                                            echo "<tr><td>$job->name</td><td>$job->count</td></tr>";
                                                        }?>
                                                        </tbody>
                                                    </table>
                                                <?php }?>
                                                <?php $extra_mounting = (array) json_decode($calculation->extra_mounting);?>
                                                <?php if (!empty($extra_mounting) ) { ?>
                                                    <h4 style="margin: 10px 0;cursor: pointer;" class="additional_jobs" s><i class="fas fa-angle-down"></i> Дополнительные работы</h4>
                                                    <table class="table_info2 additional_jobs_table" style="display:none;width:100%">
                                                        <thead>
                                                        <th>Название</th>
                                                        <th>Цена</th>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        foreach($extra_mounting as $dop) {
                                                            echo "<tr><td>$dop->title</td><td>$dop->price</td></tr>";
                                                        }
                                                        ?>
                                                        </tbody>

                                                    </table>
                                                <?php } ?>
                                                <?php $extra_components = (array) json_decode($calculation->extra_components);?>
                                                <?php if (!empty($extra_components) ) { ?>
                                                    <h4 style="margin: 10px 0;cursor: pointer;" class="additional_goods"><i class="fas fa-angle-down"></i> Дополнительные комплектующие</h4>
                                                    <table class="table_info2 additional_goods_table" style="display:none;width:100%;">
                                                        <thead>
                                                        <th>Название</th>
                                                        <th>Цена</th>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        foreach($extra_components as $dop) {
                                                            echo "<tr><td>$dop->title</td><td>$dop->price</td></tr>";
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                <?php } ?>
                                                <!-- Под старую структуру -->
                                                <?php if(!empty($calculation->n28)){
                                                    switch($calculation->n28) {
                                                        case 0:
                                                            $profil = "Отсутствует";
                                                            break;
                                                        case 1:
                                                            $profil = "Потолочный Al";
                                                            break;
                                                        case 2:
                                                            $profil = "Стеновой Al";
                                                            break;
                                                        case 3:
                                                            $profil = "Стеновой ПВХ";
                                                            break;
                                                        case 4:
                                                            $profil = "KRAAB";
                                                            break;
                                                    }
                                                    ?>
                                                    <h4 style="margin: 10px 0;">Профиль</h4>
                                                    <table class="table_info2">
                                                        <tr>
                                                            <td><?php echo $profil;?></td>
                                                        </tr>
                                                    </table>
                                                <?php }?>
                                                <?php if(!empty(floatval($calculation->remove_n28)) || !empty(floatval($calculation->n41))){?>
                                                    <h4 style="margin: 10px 0;">Демонтаж</h4>
                                                    <table class="table_info2">
                                                        <?php if(!empty(floatval($calculation->remove_n28))){?>
                                                            <tr>
                                                                <th>Демонтаж профиля, м:</th>
                                                                <td><?php echo $calculation->remove_n28;?></td>
                                                            </tr>
                                                        <?php }?>
                                                        <?php if(!empty(floatval($calculation->n41))){?>
                                                            <tr>
                                                                <th>Демонтаж потолка:</th>
                                                                <td>нужен</td>
                                                            </tr>
                                                        <?php }?>
                                                    </table>
                                                <?php } ?>
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
                                                <?php if ($calculation->n26) {?>
                                                    <h4 style="margin: 10px 0;">Светильники Гильдии Мастеров</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach ($calculation->n26 as $key => $n26_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n26_item->n26_count . " шт - <b>Тип:</b>  " . $n26_item->component_title_illum . " -  <b>Лампа:</b> " . $n26_item->component_title . "</td></tr>";
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
                                                <?php if ($calculation->n27> 0) { ?>
                                                    <h4 style="margin: 10px 0;">Шторный карниз</h4>
                                                    <?php if ($calculation->n16) {
                                                        switch($calculation->niche){
                                                            case 1:
                                                                $niche_title = "Открытая ниша";
                                                                break;
                                                            case 2:
                                                                $niche_title = "Закрытая ниша";
                                                                break;
                                                            case 3:
                                                                $niche_title = "Ниша с пластиком 100мм";
                                                                break;
                                                            case 4:
                                                                $niche_title = "Ниша с пластиком 150мм";
                                                                break;
                                                            case 5:
                                                                $niche_title = "Ниша с пластиком 200мм";
                                                                break;
                                                        }
                                                        ?>
                                                        <table class="table_info2">
                                                            <tr>
                                                                <td><?php echo $niche_title?></td>
                                                                <td><?php echo $calculation->n27; ?> м.</td>
                                                            </tr>
                                                        </table>
                                                    <?php } else { ?>
                                                        <table class="table_info2">
                                                            <tr>
                                                                <td><?php echo "Обычный шторный карниз"?></td>
                                                                <td><?php echo $calculation->n27; ?> м.</td>
                                                            </tr>
                                                        </table>
                                                        <?php
                                                    }
                                                } ?>

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

                                                <?php if ($calculation->n22 || $calculation->n22 || $calculation->n42) { ?>
                                                    <h4 style="margin: 10px 0;">Вентиляция</h4>
                                                    <?php if($calculation->n22) {?>
                                                        <table class="table_info2">
                                                            <?php
                                                            foreach ($calculation->n22 as $key => $n22_item) {
                                                                echo "<tr><td><b>Количество:</b> " . $n22_item->n22_count . " шт - <b>Тип:</b>   " . $n22_item->type_title . " - <b>Размер:</b> " . $n22_item->component_title . "</td></tr>";
                                                            }
                                                            ?>
                                                        </table>
                                                    <?php }?>
                                                    <?php if($calculation->n22_1) {?>
                                                        <table class="table_info2">
                                                            <tr>
                                                                <th>Пластиковый короб, м</th>
                                                                <td><?php echo $calculation->n22_1;?></td>
                                                            </tr>
                                                        </table>
                                                    <?php }?>
                                                    <?php if($calculation->n42) {?>
                                                        <table class="table_info2">
                                                            <tr>
                                                                <th>Вытяжка(наклейка кольца), шт</th>
                                                                <td><?php echo $calculation->n42;?></td>
                                                            </tr>
                                                        </table>
                                                    <?php }?>
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
                                                <?php if (!empty($calculation->n45)) { ?>
                                                    <h4 style="margin: 10px 0;">Световые линии</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach ($calculation->n45 as $key => $n45_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n45_item->n45_count . " м - <b>Тип:</b>  " . $n45_item->component_title . "</td></tr>";
                                                        }
                                                        ?>
                                                    </table>
                                                <?php } ?>
                                                <?php if ($calculation->n19) { ?>
                                                    <h4 style="margin: 10px 0;">Провода</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach ($calculation->n19 as $key => $n19_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n19_item->count . " м - <b>Тип:</b>   " . $n19_item->wire_title."</td></tr>";
                                                        }
                                                        ?>
                                                    </table>
                                                <?php } ?>
                                                <h4 style="margin: 10px 0;">Прочее</h4>
                                                <table class="table_info2">
                                                    <?php if ($calculation->n10> 0) { ?>
                                                        <tr>
                                                            <td> Криволинейный участок, м:</td>
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
                                                    <?php if ($calculation->n18> 0) { ?>
                                                        <tr>
                                                            <td> Усиление стен, м:</td>
                                                            <td><?php echo $calculation->n18;?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n20> 0) { ?>
                                                        <tr>
                                                            <td>Разделитель, м:</td>
                                                            <td><?php echo $calculation->n20; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n20_1> 0) { ?>
                                                        <tr>
                                                            <td>Отбойник, м:</td>
                                                            <td><?php echo $calculation->n20_1; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n21> 0) { ?>
                                                        <tr>
                                                            <td>Пожарная сигнализация, шт:</td>
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
                                                    <?php if ($calculation->n22_1> 0) { ?>
                                                        <tr>
                                                            <td>Пластиковый короб:</td>
                                                            <td><?php echo $calculation->n22_1; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n33> 0) { ?>
                                                        <tr>
                                                            <td>Лючок:</td>
                                                            <td><?php echo $calculation->n33; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n33_2> 0) { ?>
                                                        <tr>
                                                            <td>Большой люк:</td>
                                                            <td><?php echo $calculation->n33_2; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n34> 0) { ?>
                                                        <tr>
                                                            <td>Диодная лента:</td>
                                                            <td><?php echo $calculation->n34; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n34_2> 0) { ?>
                                                        <tr>
                                                            <td>Блок питания диод.ленты:</td>
                                                            <td><?php echo $calculation->n34_2; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n35> 0) { ?>
                                                        <tr>
                                                            <td>Контурный профиль:</td>
                                                            <td><?php echo $calculation->n35; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n36> 0) { ?>
                                                        <tr>
                                                            <td>Перегарпунка, м:</td>
                                                            <td><?php echo $calculation->n36; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n37) { ?>
                                                        <tr>
                                                            <td>Фотопечать, м<sup>2</sup>:</td>
                                                            <td><?php echo json_decode($calculation->n37)->square; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n38) { ?>
                                                        <tr>
                                                            <td>Ремонт потолка, шт:</td>
                                                            <td><?php echo $calculation->n38; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if (!empty(floatval($calculation->n39))) { ?>
                                                        <tr>
                                                            <td>Лента на шторный карниз, м:</td>
                                                            <td><?php echo $calculation->n39; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n40) { ?>
                                                        <tr>
                                                            <td>Закругления на шторный карниз, шт:</td>
                                                            <td><?php echo $calculation->n40; ?></td>
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
                                                <!-- //////////////// -->
                                            </div>
                                            <?php if($needShow){?>
                                                <button class="btn btn-danger delete_calc" data-calculation_id = "<?php echo $calculation->id;?>" style="margin:10px;" type="button" > Удалить потолок </button>
                                            <?php } ?>
                                        </div>
                                    </div>
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
    <div class="modal_window_container" id="img_modal_container">
        <button type="button" class="close_btn" id="btn_close_img"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <button type="button" class="close_btn" id="btn_del_img"><i class="fa fa-trash" aria-hidden="true"></i> Удалить изображение</button>
        <div class="modal_window" id="modal_window_img" style="border: 2px solid black; border-radius: 4px;"></div>
    </div>

    <?php endif;?>
    <script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
    <script type="text/javascript">
        init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);
        init_mount_calendar('change_mount_div','change_mount','mw_mounts_calendar',['close_mw','mw_container']);
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

            jQuery("#save_changes").click(function () {
                console.log(project_id,jQuery("#change_mount").val());
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=project.updateMountDate",
                    data:{
                        project_id:project_id,
                        mount_data:jQuery("#change_mount").val(),
                    },
                    success: function(data) {
                        location.reload();
                    },
                    dataType: "json",
                    timeout: 10000,
                    error: function(error) {
                        console.log(error);
                        noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "error",
                            text: "error"
                        });
                    }
                });
            });

            jQuery("#btn_add_mount_note").click(function(){
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=project.addNote",
                    data: {
                        project_id: project_id,
                        note:jQuery("#mount_note").val(),
                        type:5
                    },
                    success: function(data) {
                        location.reload();
                    },
                    dataType: "json",
                    timeout: 10000,
                    error: function(data) {
                        var n = noty({
                            theme: 'relax',
                            timeout: 2000,
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при добавлении примечания"
                        });
                    }
                });
            });
        });
    </script>
