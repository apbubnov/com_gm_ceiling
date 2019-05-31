<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 24.05.2019
 * Time: 11:46
 */
$model = Gm_ceilingHelpersGm_ceiling::getModel('mounterscommon');
$items = $model->getData();
?>
<h3>Сводная таблица по монтажным бригадам </h3>
<table class="table table_cashbox">
    <thead>
        <tr>
            <th class="center">
                Монтажник
            </th>
            <th class="center">
                Объект
            </th>
            <th class="center">
                В работе
            </th>
            <th class="center">
                Закрыто
            </th>
            <th class="center">
                Выплачено
            </th>
            <th class="center">
                Остаток
            </th>
            <th class="center">
                Закрыть
            </th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item) { ?>
        <tr>
            <td>
                <?php echo $item->mounter_name;?>
            </td>
            <td>
                <?php echo !empty($item->builder_name) ? $item->builder_name : "-";?>
            </td>
            <td>
                <?php echo !empty($item->taken) ? $item->taken : "0";;?>
            </td>
            <td>
                <?php echo !empty($item->closed) ? $item->closed : "0" ;?>
            </td>
            <td>
                <?php echo !empty($item->payed) ? $item->payed : "0";?>
            </td>
            <td>
                <?php echo $item->closed + $item->payed;?>
            </td>
            <td>
                <input class="input-gm close_sum">
                <button class="btn btn-primary btn-sm .save_sum">Save</button>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>