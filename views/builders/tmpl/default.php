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

$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
$result_clients = $clients_model->getDesignersByClientName('', 3);
?>
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
    <h2 class="center">Застройщики</h2>
    <div style="display:inline-block; width: 48%; text-align: left;">
        <button type="button" id="new_builder" class="btn btn-primary">Создать застройщика</button>
    </div>
    <div style="display:inline-block; width: 48%; text-align: left;">
        <input type="text" id="name_find_builder">
        <button type="button" id="find_builder" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i></button>
    </div>
    <br>
    <table class="table table-striped one-touch-view" id="callbacksList">
        <thead>
        <tr>
            <th>
               Название
            </th>
            <th>
               Телефоны
            </th>
            <th>
               Дата регистрации
            </th>
        </tr>
        </thead>
        <tbody id="tbody_builders">
            <?php
                foreach ($result_clients as $key => $value)
                {
                    if ($value->refused_to_cooperate == 0)
                    {
                        $color = '';
                        if (is_null($value->call_id) && (is_null($value->project_status) || $value->project_status == 0 || $value->project_status == 2))
                        {
                            $color = 'style="background-color: DarkSalmon;"';
                        }
                        else
                        {
                            $color = 'style="background-color: PaleGreen;"';
                        }
            ?>
                    <tr class="row<?php echo $i % 2; ?>" <?= $color ?> data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=designer&id='.(int) $value->id); ?>">
                        <td>
                           <?php echo $value->client_name; ?>
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
                    </tr>
            <?php
                    }
                }
            ?>
        </tbody>
    </table>
    <div id="modal-window-container">
        <button type="button" id="close4-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-1-tar">
                <p><strong>Создание нового застройщика</strong></p>
                <p>Название:</p>
                <p><input type="text" id="fio_builder"></p>
                <p>Номер телефона:</p>
                <p><input type="text" id="builder_contacts"></p>
                <p><button type="submit" id="save_builder" class="btn btn-primary">ОК</button></p>
        </div>
    </div>

<script>
    jQuery(document).ready(function()
    {
        jQuery('#builder_contacts').mask('+7(999) 999-9999');
        jQuery('body').on('click', 'tr', function(e)
        {
            if(jQuery(this).data('href')!=""){
                document.location.href = jQuery(this).data('href');
            } 
        });

        jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var div3 = jQuery("#modal-window-1-tar"); // тут указываем ID элемента
            if (!div3.is(e.target) // если клик был не по нашему блоку
                && div3.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close4-tar").hide();
                jQuery("#modal-window-container").hide();
                jQuery("#modal-window-1-tar").hide();
            }
        });

        jQuery("#new_designer").click(function(){
            jQuery("#close4-tar").show();
            jQuery("#modal-window-container").show();
            jQuery("#modal-window-1-tar").show("slow");
        });

        jQuery("#save_builder").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=dealer.create_designer",
                data: {
                    fio: document.getElementById('fio_builder').value,
                    phone: document.getElementById('builder_contacts').value,
                    designer_type: 7
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
        });

        jQuery("#find_builder").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=findOldClients",
                data: {
                    fio: document.getElementById('name_find_builder').value,
                    flag: 'designers'
                },
                success: function(data){
                    console.log(data);
                    var tbody = document.getElementById('tbody_builders');
                    tbody.innerHTML = '';
                    var html = '';
                    var color;
                    for(var i in data)
                    {
                        color = '';
                        if (data[i].refused_to_cooperate == 1)
                        {
                            color = 'style="background-color: DarkGrey;"';
                        }
                        else if (data[i].call_id == null && (data[i].project_status == null || data[i].project_status == 0 || data[i].project_status == 2))
                        {
                            color = 'style="background-color: DarkSalmon;"';
                        }
                        else
                        {
                            color = 'style="background-color: PaleGreen;"';
                        }
                        html += '<tr ' + color + ' data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=builder&id=' + data[i].id + '">';
                        html += '<td>' + data[i].client_name + '</td>';
                        html += '<td>' + data[i].client_contacts + '</td>';
                        html += '<td>' + data[i].created + '</td></tr>';
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