<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 02.12.2019
 * Time: 9:18
 */
echo parent::getButtonBack();
?>
<h2 class="center">Мои заказы</h2>
<div class="row">
    <table class="rwd-table" id="projectList">
        <thead>
        <tr class="row">
            <th class="center">
                №
            </th>
            <th class='center'>
                Дата создания
            </th>
            <th class='center'>
                Адрес
            </th>
            <th>
                Статус
            </th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($this->items as $i => $item):
            ?>
            <tr class="row" style = "cursor: pointer;" data-id="<?= $item->id; ?>">
                <td data-th = "№" class="center one-touch"><?= $item->id; ?></td>
                <td data-th = "Дата создания" class="center one-touch">
                    <?= date('d.m.Y',strtotime($item->created));?>
                </td>
                <td data-th = "Адрес" class="center one-touch">
                    <?= $item->project_info; ?>
                </td>
                <td data-th = "Статус" class="center one-touch">
                    <?=$item->status;?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
</div>

<script type="text/javascript">

    jQuery(document).ready(function(){
        jQuery('tr.row').click(function(){
           var id = jQuery(this).data('id');
           location.href = '/index.php?option=com_gm_ceiling&view=project&type=client&id='+ +id;
        });
    });
</script>
