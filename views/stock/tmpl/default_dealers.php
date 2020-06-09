<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
$modelDealers = Gm_ceilingHelpersGm_ceiling::getModel('clients');
$dealers = $modelDealers->getDealersByFilter(null,'Воронеж',null,null,null,null,0, null);

$debtModel = Gm_ceilingHelpersGm_ceiling::getModel('mountersdebt');
$operations = $debtModel->getTypes();
$optionHtml = '';
foreach ($operations as $operation){
    $optionHtml .= "<option value=\"$operation->id\">$operation->title</option>";
}
?>

<div class="row" style="margin-bottom: 15px;">
    <div class="col-md-6"></div>
    <div class="col-md-6">
        <div class="col-md-6">
            <input class="form-control" id="seach">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary" id="find">Найти</button>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary" id="clear_search">Очистить поиск</button>
        </div>
    </div>
</div>
<div class="row">
    <table class="table table_cashbox" id="dealers_table">
        <thead>
            <th>
                Имя
            </th>
            <th>
                Контакты
            </th>
            <th>
                Остаток
            </th>
            <th>
                Внести оплату
            </th>
        </thead>
        <tbody>
        <?php foreach($dealers as $dealer){ ?>
            <tr data-id="<?=$dealer->id;?>">
                <td class="td_click">
                    <?= $dealer->client_name;?>
                </td>
                <td class="td_click">
                    <?= $dealer->client_contacts;?>
                </td>
                <td class="rest td_click">
                    <?= $dealer->rest;?>
                </td>
                <td>
                    <div class="row" style="margin-bottom: 5px;">
                        <div class="col-md-4">
                            <input class="form-control sum" placeholder="Сумма">
                        </div>
                        <div class="col-md-4">
                            <select class="form-control operation_type">
                                <?php foreach($operations as $operation){?>
                                    <option value="<?=$operation->id?>"><?=$operation->title;?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary save">Сохранить</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <input class="form-control comment" placeholder="Комментарий">
                        </div>
                    </div>
                </td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    var optionHtml = '<?=$optionHtml?>';
    jQuery(document).ready(function(){
       /* if(!empty(jQuery('#seach').val())){
            getDealers({client: jQuery('#seach').val()});
        }*/
        jQuery('body').on('click','.save',function() {
            var tr = jQuery(this).closest('tr'),
                id = tr.data('id'),
                type = tr.find('.operation_type').val(),
                sum =tr.find('.sum').val(),
                comment = tr.find('.comment').val(),
                rest_td = tr.find('.rest'),
                rest_val = rest_td.text();
            console.log(rest_td);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=client.addRecToStateOfAccount",
                data: {
                    id: id,
                    operation: type,
                    sum: sum,
                    comment: comment
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    if(type == 1){
                        rest_td.text((parseFloat(rest_val)+ +sum).toFixed(2));
                    }
                    if(type == 2){
                        rest_td.text((rest_val - sum).toFixed(2));
                    }
                    tr.find('.sum').val('');
                    tr.find('.comment').val('');
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Сохранено!"
                    });
                },
                error: function (data) {
                    console.log(data);
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка отправки"
                    });
                }
            });
        });

        jQuery('body').on('click','.td_click',function(){
            location.href = '/index.php?option=com_gm_ceiling&view=clientaccount&id='+jQuery(this).closest('tr').data('id');
        });

        function getDealers(data) {
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=dealer.getFilteredData",
                data: data,
                success: function (data) {
                    jQuery('#dealers_table > tbody').empty();
                    jQuery.each(data, function (n, elem) {
                        jQuery('#dealers_table > tbody').append('<tr data-id="' + elem.id + '">' +
                            '<td class="td_click">' + elem.client_name + '</td>' +
                            '<td class="td_click">' + elem.client_contacts + '</td>' +
                            '<td class="rest td_click">' + elem.rest + '</td>' +
                            '<td>' +
                            '<div class="row" style="margin-bottom: 5px;">' +
                            '<div class="col-md-4">' +
                            '<input class="form-control sum" placeholder="Сумма">' +
                            '</div>' +
                            '<div class="col-md-4">' +
                            '<select class="form-control operation_type">' +
                            optionHtml +
                            '</select>' +
                            '</div>' +
                            '<div class="col-md-4">' +
                            '<button class="btn btn-primary save">Сохранить</button>' +
                            '</div>' +
                            '</div>' +
                            '<div class="row">' +
                            '<div class="col-md-12">' +
                            '<input class="form-control comment" placeholder="Комментарий">' +
                            '</div>' +
                            '</div>' +
                            '</td>' +
                            '</tr>')
                    });
                },
                dataType: "json",
                async: false,
                timeout: 10000,
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка. Сервер не отвечает"
                    });
                }
            });
        }

        jQuery('#find').click(function(){
            var data = {client:jQuery('#seach').val()};
            getDealers(data);
        });

        jQuery('#clear_search').click(function () {
           var data = { filter_city: 'Воронеж'};
           jQuery('#seach').val('');
           getDealers(data);
        });
    });
</script>