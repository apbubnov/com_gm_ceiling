<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 29.11.2019
 * Time: 14:10
 */
$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');

$street = preg_split("/,.дом([\S\s]*)/", $this->item->project_info)[0];

preg_match("/,.дом:.([\d\w\/\s]{1,4})/", $this->item->project_info,$house);
$house = $house[1];
preg_match("/.корпус:.([\d\W\s]{1,4}),|.корпус:.([\d\W\s]{1,4}),{0}/", $this->item->project_info,$bdq);
$bdq = $bdq[1];
preg_match("/,.квартира:.([\d\s]{1,4})/", $this->item->project_info,$apartment);
$apartment = $apartment[1];
preg_match("/,.подъезд:.([\d\s]{1,4})/", $this->item->project_info,$porch);
$porch = $porch[1];
preg_match("/,.этаж:.([\d\s]{1,4})/", $this->item->project_info,$floor);
$floor = $floor[1];
preg_match("/,.код:.([\d\S\s]{1,10})/", $this->item->project_info,$code);
$code = $code[1];

$json_mount = $this->item->mount_data;
$wasDelete = false;
$this->item->mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));

foreach ($this->item->mount_data as $key=>$value) {
    if(empty($value->mounter)){
        $wasDelete = true;
        unset($this->item->mount_data[$key]);
    }
}
if($wasDelete){
    if(!empty($this->item->mount_data)) {
        $json_mount = json_encode(htmlspecialchars($this->item->mount_data));
    }
    else{
        $json_mount = [];
    }
}
$stages = [];
if(!empty($this->item->mount_data)){
    $mount_types = $projects_mounts_model->get_mount_types();
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
                Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($calc->id,$key,$val->stage,$val->time);
            }

        }
    }

}
?>
<style>
    .act_btn{
        width:210px;
        margin-bottom: 10px;
    }
</style>
<form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=calculator&subtype=calendar" method="post" enctype="multipart/form-data">
    <input id="mount" name="mount" type='hidden' value='<?php echo $json_mount ?>'>
    <input name="project_id" id = "project_id"  value="<?php echo $this->item->id; ?>" type="hidden">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <b>Номер:</b>
                    </div>
                    <div class="col-md-6">
                        <?=$this->item->id;?>
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                        <b><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></b>
                    </div>
                    <div class="col-xs-8 col-md-8">
                        <input name="new_address" id="jform_address" class="inputactive" value="<?php echo $street ?>" placeholder="Адрес" type="text" >
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                        <b>Дом / Корпус</b>
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_house" id="jform_house" value="<?php echo $house ?>" class="inputactive" placeholder="Дом"  aria-required="true" type="text">
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_bdq" id="jform_bdq"  value="<?php echo $bdq ?>" class="inputactive"   placeholder="Корпус" aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                        <b>Квартира / Подъезд</b>
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_apartment" id="jform_apartment" value="<?php echo $apartment ?>" class="inputactive" placeholder="Квартира"  aria-required="true" type="text">
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_porch" id="jform_porch"  value="<?php echo $porch ?>" class="inputactive"    placeholder="Подъезд"  aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                        <b>Этаж / Код домофона</b>
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_floor" id="jform_floor"  value="<?php echo $floor ?>" class="inputactive"  placeholder="Этаж" aria-required="true" type="text">
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_code" id="jform_code"  value="<?php echo $code ?>" class="inputactive"   placeholder="Код" aria-required="true" type="text">
                    </div>
                </div>
                <div class="row center" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <button class="btn btn-primary" id="save_project_info" type="button">Сохранить адрес</button>
                    </div>
                </div>
                <?php if(!empty($this->item->mount_data)):?>
                    <div class="row">
                        <div class="col-md-12" style="text-align: center;">Монтаж</div>
                    </div>
                    <?php foreach ($this->item->mount_data as $value) { ?>
                        <div class="row">
                            <div class="col-md-4"><?php echo $value->time;?></div>
                            <div class="col-md-4"><?php echo $value->stage_name;?></div>
                            <div class="col-md-4"><?php echo JFactory::getUser($value->mounter)->name;?></div>
                        </div>
                    <?php }?>
                <?php endif;?>
                <div class="row">
                    <div class="col-md-5">
                        <b>Изменить скидку</b>
                    </div>
                    <div class="col-md-4">
                        <input name="new_discount" id="jform_new_discount" value = "<?=$this->item->project_discount?>" placeholder="%" min="0" max='20' type="number" style="width: 100%;">
                    </div>
                    <div class="col-md-3">
                        <button type="button" id="update_discount" class="btn btn-primary">Сохранить</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>
            </div>
        </div>
        <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
        <?php if (!in_array($this->item->project_status,VERDICT_STATUSES)) { ?>
            <div class="project_activation" id="project_activation">
                <div class="row center">
                    <div class="col-md-6">
                        <h4>Назначить дату монтажа</h4>
                        <div id="calendar_mount" align="center"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <h4> Ввести примечания</h4>
                                <div id ="comments_divs">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <b><label for = "jform_production_note">Примечание в производство</label></b>
                                        </div>
                                        <div class="col-md-6">
                                            <textarea name="production_note" class="input-gm" id="jform_production_note" placeholder="Примечание в производство" aria-invalid="false"></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <b><label for = "jform_mount_note">Примечание к монтажу</label></b>
                                        </div>
                                        <div class="col-md-6">
                                            <textarea name="mount_note" id="jform_mount_note" class="input-gm" placeholder="Примечание к монтажу" aria-invalid="false"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row center">
                <div class="col-md-6" style="padding-top: 25px;">
                    <button class="validate btn btn-primary save_bnt" id="save" type="button" from="form-client">Сохранить и запустить <br> в производство ГМ</button>
                </div>
                <div class="col-md-6" style="padding-top: 25px;">
                    <button class="validate btn btn-primary save_bnt" id="save_exit" type="submit" from="form-client">Сохранить и выйти</button>
                </div>
            </div>
        </div>
    <?php } ?>
</form>
<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="mw_mounts_calendar" class="modal_window"></div>
</div>
<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
<script type="text/javascript">
    var project_id = '<?= $this->item->id;?>',
        min_project_sum = <?php echo  $min_project_sum;?>,
        min_components_sum = <?php echo $min_components_sum;?>,
        self_data = JSON.parse('<?php echo $self_calc_data;?>');
    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);
    jQuery(document).mouseup(function (e){// событие клика по веб-документу
        var div1 = jQuery("#mw_mounts_calendar");
        if (!div1.is(e.target) // если клик был не по нашему блоку
            && div1.has(e.target).length === 0
           ) { // и не по его дочерним элементам
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            div1.hide();
        }
    });
    jQuery(document).ready(function(){
        jQuery("#update_discount").click(function() {
            save_data_to_session(4);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=project.changeDiscount",
                data: {
                    project_id: project_id,
                    project_total: jQuery("#project_total span.sum")[0].innerText,
                    new_discount: jQuery("#jform_new_discount").val()
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    //console.log(data);
                    location.reload();
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка изменения скидки"
                    });
                }
            });
        });
        jQuery("#jform_new_discount").change(function(){
            if(this.value > 20){
                this.value = 20;
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Максимальная скидка 20%!"
                });
            }
        });

        jQuery("#save_project_info").click(function () {
            var street = jQuery('#jform_address').val(),
                house = jQuery('#jform_house').val(),
                bdq = jQuery('#jform_bdq').val(),
                apartment = jQuery('#jform_apartment').val(),
                porch = jQuery('#jform_porch').val(),
                floor = jQuery('#jform_floor').val(),
                code = jQuery('#jform_code').val(),
                address = '';
            if (!empty(house)) address = street + ", дом: " + house;
            if (!empty(bdq)) address += ", корпус: " + bdq;
            if (!empty(apartment)) address += ", квартира: " + apartment;
            if (!empty(porch)) address += ", подъезд: " + porch;
            if (!empty(floor)) address += ", этаж: " + floor;
            if (!empty(code)) address += ", код: " + code;
            var new_data = {id:project_id,project_info:address};
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=project.change_project_data",
                data: {
                    new_data: JSON.stringify(new_data)
                },
                dataType: "json",
                async: true,
                success: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Адрес сохранен!"
                });
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка изменения скидки"
                    });
                }
            });
        });

        jQuery("#save").click(function(){
            save(5);
        });
        jQuery("#save_exit").click(function(){
            save('<?=$this->item->project_status?>');
        });
    });
    function save(status) {
        var production_note = jQuery('#jform_production_note').val(),
            mount_note = jQuery('#jform_mount_note').val();
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=project.runByClient",
            data: {
                project_id: project_id,
                mount_data: jQuery("#mount").val(),
                mount_note: mount_note,
                production_note: production_note,
                status: status
            },
            dataType: "json",
            async: true,
            success: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Адрес сохранен!"
                });
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка изменения скидки"
                });
            }
        });
    }
</script>