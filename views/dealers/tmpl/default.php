<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2017 al.p.bubnov@gmail.com>
 * @copyright  2017 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$user       = JFactory::getUser();
$userId     = $user->get('id');

$users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
$result_users = $users_model->getDealers();
$recoil_map_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');

?>
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
    <h2 class="center">Дилеры</h2>
    <div style="display:inline-block; width: 48%; text-align: left;">
        <button type="button" id="new_dealer" class="btn btn-primary">Создать дилера</button>
    </div>
    <div style="display:inline-block; width: 48%; text-align: left;">
        <input type="text" id="name_find_dealer">
        <button type="button" id="find_dealer" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i></button>
    </div>
    <br>
    <table class="table table-striped one-touch-view" id="callbacksList">
        <thead>
        <tr>
            <th>
               Имя
            </th>
            <th>
               Телефоны
            </th>
            <th>
               Дата регистрации
            </th>
            <th>
                Взнос
            </th>
        </tr>
        </thead>
        <tbody id="tbody_dealers">
        	<?php
        		foreach ($result_users as $key => $value)
        		{
                    $data = $recoil_map_model->getData($value->id);
                    $sum[$value->id] = 0;
                    $dealers[$value->id] = $value;
                    foreach ($data as $item)
                    {
                        $sum[$value->id] +=  $item->sum;
                    }
        	?>
                <tr class="row<?php echo $i % 2; ?>" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id='.(int) $value->associated_client); ?>">
		            <td>
		               <?php echo $value->name; ?>
		            </td>
                    <td>
                       <?php echo $value->client_contacts; ?>
                    </td>
		            <td>
		               <?php
                            if($value->created == "0000-00-00 00:00:00") {
                                echo "-";
                            } else {
                                $jdate = new JDate($value->created);
                                $created = $jdate->format("d.m.Y H:i");
                                echo $created;
                            }
                       ?>
		            </td>
                    <td>
                        <button class="btn btn-primary btn-done" user_id="<?= $value->id; ?>" type="button"> Внести сумму </button>
                    </td>
		        </tr>
        	<?php
        		}
        	?>
        </tbody>
    </table>
    <div class="modal_window_container" id="mv_container">
        <button type="button" class="close_btn" id="close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div class="modal_window" id="modal_window_create">
                <p><strong>Создание нового дилера</strong></p>
                <p>ФИО:</p>
                <p><input type="text" id="fio_dealer"></p>
                <p>Номер телефона:</p>
                <p><input type="text" id="dealer_contacts"></p>
                <p><button type="submit" id="save_dealer" class="btn btn-primary">ОК</button></p>
        </div>
        <div class="modal_window" id="modal_window_sum">
            <p><strong id="dealer_name"></strong></p>
            <p id="dealer_invoice"></p>
            <p>Сумма взноса:</p>
            <p><input type="text" id="pay_sum"></p>
            <input type="hidden" id="hidden_user_id">
            <p><button type="submit" id="save_pay" class="btn btn-primary">ОК</button></p>
        </div>
    </div>

<script>

    jQuery(document).ready(function()
    {
        jQuery('#dealer_contacts').mask('+7(999) 999-9999');

        var sum = JSON.parse('<?php echo json_encode($sum); ?>');
        var dealers = JSON.parse('<?php echo json_encode($dealers); ?>');
        console.log(sum, dealers);

        jQuery("#new_dealer").click(function(){
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_create").show("slow");
        });

        jQuery(document).click(function(e){
            var target = e.target;
            console.log(e.target.tagName);
            // цикл двигается вверх от target к родителям до table
            while (target.tagName != 'BODY')
            {
                var div = jQuery("#modal_window_create");
                var div2 = jQuery("#modal_window_sum"); // тут указываем ID элемента
                if (div.is(target) || div2.is(target) || div.has(target).length != 0 || div2.has(target).length != 0)
                {
                    console.log(target);
                    if (target.id != undefined)
                    {
                        if (target.id == 'save_dealer')
                        {
                            jQuery.ajax({
                                type: 'POST',
                                url: "index.php?option=com_gm_ceiling&task=dealer.create_dealer",
                                data: {
                                    fio: document.getElementById('fio_dealer').value,
                                    phone: document.getElementById('dealer_contacts').value
                                },
                                success: function(data){
                                    if (data == 'client_found')
                                    {
                                        var n = noty({
                                            timeout: 2000,
                                            theme: 'relax',
                                            layout: 'center',
                                            maxVisible: 5,
                                            type: "error",
                                            text: "Клиент с таким номером существует!"
                                        });
                                    }
                                    else
                                    {
                                        location.reload();
                                    }
                                },
                                dataType: "text",
                                async: false,
                                timeout: 10000,
                                error: function(data){
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
                        if (target.id == 'save_pay')
                        {
                            jQuery.ajax({
                                type: 'POST',
                                url: "index.php?option=com_gm_ceiling&task=dealer.add_in_table_recoil_map_project",
                                data: {
                                    id: document.getElementById('hidden_user_id').value,
                                    sum: document.getElementById('pay_sum').value
                                },
                                success: function(data){
                                    var n = noty({
                                        timeout: 5000,
                                        theme: 'relax',
                                        layout: 'center',
                                        maxVisible: 5,
                                        type: "success",
                                        text: "Сумма успешно добавлена"
                                    });
                                    setInterval(function() { location.reload();}, 1500);
                                },
                                dataType: "text",
                                async: false,
                                timeout: 10000,
                                error: function(data){
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
                            return;
                        }
                    }
                    return;
                }

                if (target.tagName == 'TR')
                {
                    if(jQuery(target).data('href') != undefined){
                        document.location.href = jQuery(target).data('href');
                    }
                    return;
                }

                if (target.className != undefined)
                {
                    if (target.className.indexOf('btn-done') + 1)
                    {
                        var user_id = jQuery(target).attr("user_id");
                        
                        document.getElementById('dealer_name').innerHTML = 'Взнос задолжности. Дилер: ' + dealers[user_id].name;
                        document.getElementById('dealer_invoice').innerHTML = 'На счете: ' + sum[user_id] + ' руб.';
                        document.getElementById('pay_sum').value = (sum[user_id]<0)?Math.abs(sum[user_id]):0;
                        document.getElementById('hidden_user_id').value = user_id;

                        jQuery("#close").show();
                        jQuery("#mv_container").show();
                        jQuery("#modal_window_sum").show("slow");
                        return;
                    }
                }

                if (target.id != undefined)
                {
                    if (target.id == 'close' || target.id == 'mv_container')
                    {
                        jQuery("#close").hide();
                        jQuery("#mv_container").hide();
                        jQuery("#modal_window_create").hide();
                        jQuery("#modal_window_sum").hide();
                        return;
                    }
                }

                target = target.parentNode;
            }
        });

        jQuery("#find_dealer").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=findOldClients",
                data: {
                    fio: document.getElementById('name_find_dealer').value,
                    flag: 'dealers'
                },
                success: function(data){
                    console.log(data);
                    var tbody = document.getElementById('tbody_dealers');
                    tbody.innerHTML = '';
                    var html = '';
                    for(var i in data)
                    {
                        html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id=' + data[i].id + '">';
                        html += '<td>' + data[i].client_name + '</td>';
                        html += '<td>' + data[i].client_contacts + '</td>';
                        html += '<td>' + data[i].created + '</td>';
                        html += '<td><button class="btn btn-primary btn-done" user_id="' + data[i].dealer_id + '" type="button"> Внести сумму </button></td></tr>';
                    }
                    tbody.innerHTML = html;
                    html = '';
                },
                dataType: "json",
                async: false,
                timeout: 20000,
                error: function(data){
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
        });
    });
</script>