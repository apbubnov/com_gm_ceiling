<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 03.07.2019
 * Time: 14:52
 */
$jinput = JFactory::getApplication()->input;
$dealerId = $jinput->get('dealer_id',null,'INT');

$user = JFactory::getUser();
$user->groups = $user->get('groups');

$managerGM = in_array(16, $user->groups) || in_array(15, $userDealer->groups);
$pricesModel = Gm_ceilingHelpersGm_ceiling::getModel('prices');
$stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
$goods = $stockModel->getAllGoods();
?>
<table id="goods" class="table table-stripped table_cashbox">
    <thead>
        <th>Наименование</th>
        <th>Цена</th>
        <th>Новое наименование</th>
        <th>Новая цена</th>
        <th></th>
    </thead>
    <tbody>
    <?php foreach ($goods as $good){?>
        <tr data-id = <?= $good->id?>>
            <td class="old_name"><?= $good->name;?></td>
            <td class="old_price"><?= $good->price;?></td>
            <td><input class="form-control new_name"></td>
            <td><input class="form-control new_price"></td>
            <td><button class="btn btn-primary save_btn">Сохранить</button> </td>
        </tr>
    <?php }?>
    </tbody>
</table>

<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('.save_btn').click(function(){
            var tr = jQuery(this).closest('tr'),
                goods_id = tr.data('id'),
                new_name = tr.find('.new_name').val(),
                new_price = tr.find('.new_price').val();
            jQuery.ajax({
                type: 'POST',
                url: '/index.php?option=com_gm_ceiling&task=stock.updateGoods',
                data: {
                    goodsId: goods_id,
                    name: new_name,
                    price: new_price,
                },
                dataType: "json",
                timeout: 5000,
                success: function (data) {
                   if(!empty(new_name)){
                       tr.find('.old_name').text(new_name);
                   }
                    if(!empty(new_price)){
                        tr.find('.old_price').text(new_price);
                    }
                    tr.find('.new_name').val('');
                    tr.find('.new_price').val('');
                },
                error: function (error) {
                    console.log(error);
                }
            });

        });
    });
</script>