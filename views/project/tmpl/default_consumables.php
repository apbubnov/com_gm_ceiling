<?php

$jinput = JFactory::getApplication()->input;
$projectId = $jinput->getInt('id');
if(empty($projectId)){
    exit('Пустой id проекта!');
}

$projectModel = self::getModel('Project');
$calculations = $projectModel->getCalculations($projectId);

$stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
$units = $stockModel->getGoodsUnitsAssoc();

$all_goods = [];
$all_jobs = [];
$extra_components = [];
$components_data = [];
foreach ($calculations as $calc) {
    foreach ($calc->goods as $goods){
        if (array_key_exists($goods->goods_id, $all_goods)) {
            $all_goods[$goods->goods_id]->price_sum += $goods->price_sum;
            $all_goods[$goods->goods_id]->price_sum_with_margin += $goods->price_sum_with_margin;
            $all_goods[$goods->goods_id]->final_count += $goods->final_count;
        } else {
            $all_goods[$goods->goods_id] = $goods;
        }

    }
    foreach ($calc->jobs as $job){
        if (array_key_exists($job->job_id, $all_jobs)) {
            $all_jobs[$job->job_id]->price_sum += $job->price_sum;
            $all_jobs[$job->job_id]->price_sum_with_margin += $job->price_sum_with_margin;
            $all_jobs[$job->job_id]->final_count += $job->final_count;
        } else {
            $all_jobs[$job->job_id] = $job;
        }

    }
}
$goods_total = 0;
$jobs_total = 0;

$projectsMountsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
$mountTypes = $projectsMountsModel->get_mount_types();

$address = Gm_ceilingHelpersGm_ceiling::parseProjectInfo($this->item->project_info);
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
?>
<style>
    tr.line-through td{
        text-decoration:line-through;
        color:grey;
    }
</style>
<input id="mount" name="mount" type='hidden' value='<?php echo $json_mount ?>'>
<input name="project_id" id = "project_id"  value="<?php echo $this->item->id; ?>" type="hidden">
<input name="project_info" id="project_info" type="hidden">
<h3> Список товаров</h3>
<table class="table table-stripped" id="table_goods">
    <thead>
        <th class="center"></th>
        <th class="center">
            Наименование
        </th>
        <th class="center">
            Кол-во
        </th>
        <th class="center">
            Ед.изм-я
        </th>
        <th class="center">
            Цена за ед.
        </th>
        <th class="center">
            Стоимость
        </th>
    </thead>
    <tbody>
        <?php foreach($all_goods as $goods){?>
            <tr>
                <td>
                    <input type="checkbox" id="<?= "g_$goods->goods_id"?>" data-id = "<?= $goods->goods_id?>" class="inp-cbx g_include" checked style="display: none">
                    <label for="<?= "g_$goods->goods_id"?>" class="cbx">
                        <span>
                            <svg width="12px" height="10px" viewBox="0 0 12 10">
                                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                            </svg>
                        </span>
                        <span></span>
                    </label>
                </td>
                <td>
                    <?= $goods->name;?>
                </td>
                <td class="center">
                    <?= $goods->final_count;?>
                </td>
                <td class="center">
                    <?= $units[$goods->unit_id]->unit;?>
                </td>
                <td class="center">
                    <?= $goods->dealer_price_with_margin;?>
                </td>
                <td class="right goods_sum">
                    <?php
                        $goods_total += $goods->price_sum_with_margin;
                        echo $goods->price_sum_with_margin;
                    ?>
                </td>
            </tr>
        <?php }?>
    </tbody>
    <tfoot>
        <td colspan="5" class="right">
            <b>Итого:</b>
        </td>
        <td class="right goods_total">
            <b><?= $goods_total;?></b>
        </td>
    </tfoot>
</table>
<h3> Список монтажных работ</h3>
<table class="table table-stripped" id="table_jobs">
    <thead>
    <th class="center"></th>
    <th class="center">
        Наименование
    </th>
    <th class="center">
        Кол-во
    </th>
    <th class="center">
        Цена за ед.
    </th>
    <th class="center">
        Стоимость
    </th>
    </thead>
    <tbody>
        <?php foreach($all_jobs as $job){?>
            <tr>
                <td>
                    <input type="checkbox" id="<?= "j_$job->job_id"?>" data-id = "<?= $job->job_id?>" class="inp-cbx j_include" checked style="display: none">
                    <label for="<?= "j_$job->job_id"?>" class="cbx">
                            <span>
                                <svg width="12px" height="10px" viewBox="0 0 12 10">
                                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                </svg>
                            </span>
                        <span></span>
                    </label>
                </td>
                <td>
                    <?= $job->name;?>
                </td>
                <td class="center">
                    <?= $job->final_count;?>
                </td>
                <td class="center">
                    <?= $job->price_with_margin;?>
                </td>
                <td class="right job_sum">
                    <?php
                        $jobs_total += $job->price_sum_with_margin;
                        echo $job->price_sum_with_margin;
                    ?>
                </td>
            </tr>
        <?php }?>
    </tbody>
    <tfoot>
    <td colspan="4" class="right">
        <b>Итого:</b>
    </td>
    <td class="right jobs_total">
        <b><?= $jobs_total;?></b>
    </td>
    </tfoot>
</table>
<?php if(!in_array($this->item->project_status,VERDICT_STATUSES)){ ?>
<div class="row center">
    <div class="col-md-12">
        <button class="btn btn-primary" id="choose_date">Заказать</button>
    </div>
</div>
<?php }?>
<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="mw_mounts_calendar" class="modal_window"></div>
    <div class="modal_window" id="mw_run">
        <h4>Запустить проект в работу</h4>
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4" style="text-align: right;">
                        <b>Улица</b>
                    </div>
                    <div class="col-xs-8 col-md-8">
                        <input name="new_address" id="jform_address" class="form-control" value="<?php echo $address->street ?>" placeholder="Адрес" type="text" >
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4"  style="text-align: right;">
                        <b>Дом / Корпус</b>
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_house" id="jform_house" value="<?php echo $address->house ?>" class="form-control" placeholder="Дом"  aria-required="true" type="text">
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_bdq" id="jform_bdq"  value="<?php echo $address->bdq ?>" class="form-control"   placeholder="Корпус" aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4"  style="text-align: right;">
                        <b>Квартира / Подъезд</b>
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_apartment" id="jform_apartment" value="<?php echo $address->apartment ?>" class="form-control" placeholder="Квартира"  aria-required="true" type="text">
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_porch" id="jform_porch"  value="<?php echo $address->porch ?>" class="form-control"    placeholder="Подъезд"  aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4"  style="text-align: right;">
                        <b>Этаж / Код домофона</b>
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_floor" id="jform_floor"  value="<?php echo $address->floor ?>" class="form-control"  placeholder="Этаж" aria-required="true" type="text">
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_code" id="jform_code"  value="<?php echo $address->code ?>" class="form-control"   placeholder="Код" aria-required="true" type="text">
                    </div>
                </div>
                <div class="row left">
                    <span id="selected_mount"></span>
                </div>
            </div>

            <div class="col-md-6 col-sm-12">
                <div class="col-md-12">
                    <h4>Выберите желаемую дату монтажа</h4>
                    <div id="calendar_mount" align="center"></div>
                </div>
            </div>
        </div>
        <div class="row center">
            <button class="btn btn-primary" id="save"> Заказать</button>
        </div>
    </div>
</div>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>

<script type="text/javascript">
    var project_id = '<?= $this->item->id?>';
    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',[]);
    jQuery(document).mouseup(function (e){// событие клика по веб-документу
        var div1 = jQuery('#mw_run'),
            div2 = jQuery("#mw_mounts_calendar");
        if (!div1.is(e.target) // если клик был не по нашему блоку
            && div1.has(e.target).length === 0
            && !div2.is(e.target)
            && div2.has(e.target).length === 0
        ) { // и не по его дочерним элементам
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            div1.hide();
            div2.hide();
        }
    });

    jQuery(document).ready(function () {
        var mountStages = JSON.parse('<?= json_encode($mountTypes)?>');
        document.querySelector('footer').style = 'display: none';
        jQuery('.g_include').change(function () {
            var checkbox = jQuery(this),
            goodsSum = parseFloat(checkbox.closest('tr').find('.goods_sum').text()),
            goodsTotal = jQuery('.goods_total'),
            goodsTotalVal = parseFloat(goodsTotal.text());
            if(!checkbox.is(':checked')){
                checkbox.closest('tr').addClass('line-through');
                goodsTotal.html('<b>'+ (goodsTotalVal - goodsSum) +'</b>');
            }
            if(checkbox.is(':checked')){
                checkbox.closest('tr').removeClass('line-through');
                goodsTotal.html('<b>'+ (goodsTotalVal + goodsSum) +'</b>')
            }

        });
        jQuery('.j_include').change(function () {
            var checkbox = jQuery(this),
                jobSum = parseFloat(checkbox.closest('tr').find('.job_sum').text()),
                jobsTotal = jQuery('.jobs_total'),
                jobsTotalVal = parseFloat(jobsTotal.text());
            if(!checkbox.is(':checked')){
                checkbox.closest('tr').addClass('line-through');
                jobsTotal.html('<b>'+ (jobsTotalVal - jobSum) +'</b>');
            }
            if(checkbox.is(':checked')){
                checkbox.closest('tr').removeClass('line-through');
                jobsTotal.html('<b>'+ (jobsTotalVal + jobSum) +'</b>')
            }
        });
        jQuery('#choose_date').click(function () {
            jQuery('#mw_container').show();
            jQuery('#close_mw').show();
            jQuery('#mw_run').show();
        });

        jQuery('#calendar_mount').on('click','td',function(){
            jQuery('#mw_run').hide();
        });
        jQuery('#mw_mounts_calendar').on('click','.hide_calendar',function () {
            jQuery('#selected_mount').empty();
            var mountData = JSON.parse(jQuery('#mount').val()),
                mountText = '';
            for(var i=0;i<mountData.length;i++){
                mountText += '<b>Вы выбрали: </b>'+mountStages[mountData[i].stage]+' на '+formatDate(new Date(mountData[i].time),true) +'<br/>';
            }

            jQuery('#selected_mount').append(mountText);
            jQuery('#mw_run').show();

        });

        jQuery('#save').click(function(){
            var street = jQuery('#jform_address').val(),
                house = jQuery('#jform_house').val(),
                bdq = jQuery('#jform_bdq').val(),
                apartment = jQuery('#jform_apartment').val(),
                porch = jQuery('#jform_porch').val(),
                floor = jQuery('#jform_floor').val(),
                code = jQuery('#jform_code').val(),
                address = '',
                notCheckedGoods = jQuery('.g_include:not(:checked)'),
                notCheckedJobs =  jQuery('.j_include:not(:checked)'),
                goodsTotal = parseFloat(jQuery('.goods_total').text()),
                jobsTotal = parseFloat(jQuery('.jobs_total').text()),
                sendObject = {
                    project_id: project_id,
                    project_info: address,
                    status:5,
                    mount_data: jQuery('#mount').val(),
                    del_goods: [],
                    del_jobs: [],
                    project_sum: goodsTotal + jobsTotal
                };
            sendObject.del_goods = getIds(notCheckedGoods);
            sendObject.del_jobs = getIds(notCheckedJobs);
            if (!empty(house)) address = street + ", дом: " + house;
            if (!empty(bdq)) address += ", корпус: " + bdq;
            if (!empty(apartment)) address += ", квартира: " + apartment;
            if (!empty(porch)) address += ", подъезд: " + porch;
            if (!empty(floor)) address += ", этаж: " + floor;
            if (!empty(code)) address += ", код: " + code;
            sendObject.project_info = address;
            //console.log(sendObject);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=project.runByClient",
                data: sendObject,
                dataType: "json",
                async: true,
                success: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Ваш проект запущен в работу. В ближайшее время с вами свяжется наш менеджер"
                    });
                    jQuery('#mw_container').hide();
                    jQuery('#close_mw').hide();
                    jQuery('#mw_run').hide();
                },
                error: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Произошла ошибка!"
                    });
                }
            });
        });

        function getIds(arr){
            var result = [];
            for(var i = 0;i<arr.length;i++){
                result.push(jQuery(arr[i]).data('id'));
            }
            return result;
        }
    });



</script>