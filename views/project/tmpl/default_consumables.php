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
$all_jobs =[];
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
?>
<style>
    tr.line-through td{
        text-decoration:line-through;
        color:grey;
    }
</style>
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
                    <input type="checkbox" id="<?= "g_$goods->goods_id"?>" data-goods_id = "<?= $goods->goods_id?>" class="inp-cbx g_include" checked style="display: none">
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
                <td class="center" style="max-width: 30%">
                    <div class="row">
                        <div class="col-md-2 col-xs-1">
                            <button class="btn btn-primary"><i class="far fa-minus-square"></i></button>
                        </div>
                        <div class="col-md-8 col-xs-10">
                            <input class="form-control" value="<?= $goods->final_count;?>">
                        </div>
                        <div class="col-md-2 col-xs-1">
                            <button class="btn btn-primary"><i class="far fa-plus-square"></i></button>
                        </div>
                    </div>

                </td>
                <td class="center">
                    <?= $units[$goods->unit_id]->unit;?>
                </td>
                <td class="center">
                    <?= $goods->dealer_price_with_margin;?>
                </td>
                <td class="right">
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
        <td class="right">
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
                    <input type="checkbox" id="<?= "j_$job->job_id"?>" data-goods_id = "<?= $job->job_id?>" class="inp-cbx j_include" checked style="display: none">
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
                <td class="right">
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
    <td class="right">
        <b><?= $jobs_total;?></b>
    </td>
    </tfoot>
</table>
<script type="text/javascript">
    jQuery(document).ready(function () {
        document.querySelector('footer').style = 'display: none';
        jQuery('.g_include').change(function () {
            var checkbox = jQuery(this);
            if(!checkbox.is(':checked')){
                checkbox.closest('tr').addClass('line-through');
            }
            if(checkbox.is(':checked')){
                checkbox.closest('tr').removeClass('line-through');
            }

        });
        jQuery('.j_include').change(function () {
            var checkbox = jQuery(this);
            if(!checkbox.is(':checked')){
                checkbox.closest('tr').addClass('line-through');
            }
            if(checkbox.is(':checked')){
                checkbox.closest('tr').removeClass('line-through');
            }
        });
    })

</script>