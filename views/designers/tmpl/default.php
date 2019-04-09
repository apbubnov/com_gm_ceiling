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
$labels = $clients_model->getClientsLabels($user->dealer_id);
?>

    <div class="row">
        <div class="col-md-4 col-xs-6">
            <a class="btn btn-primary"
           href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
           id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
        </div>
        <div class="col-md-8 col-xs-6">
            <h2>Отделочники</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-xs-6">
            <button type="button" id="new_designer" class="btn btn-primary">Создать отделочника</button>
        </div>
        <div class="col-md-4 col-xs-6">
            <select class="wide cust-select" id="select_label">
                <option value="" selected>Ярлыки</option>
                <?php foreach($labels as $label): ?>
                    <option value="<?= $label->id; ?>"><?= $label->title; ?></option>
                <?php endforeach;?>
            </select>
            <div class="nice-select wide" tabindex="0">
                <span class="current">Ярлыки</span>
                <ul class="list">
                    <li class="option" data-value="" data-color="#ffffff" style="--rcolor:#ffffff" data-display="Ярлыки">Ярлыки</li>
                    <?php foreach($labels as $label): ?>
                        <li class="option" data-value="<?= $label->id; ?>" data-color="#<?= $label->color_code; ?>" style="--rcolor:#<?= $label->color_code; ?>"><?= $label->title; ?></li>
                    <?php endforeach;?>
                </ul>
            </div>
        </div>
        <div class="col-md-3 col-xs-6">
            <input type="text" id="name_find_designer" class="form-control">
        </div>
        <div class="col-md-1 col-xs-6" style="padding: 0px;">
            <button type="button" id="find_designer" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i></button>
        </div>
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
        </tr>
        </thead>
        <tbody id="tbody_designers">
        	<?php
        		foreach ($result_clients as $key => $value) {
        	?>
                    <tr class="row<?php echo $i % 2; ?>" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=designer&id='.(int) $value->id); ?>" style="outline: #<?= $value->label_color_code; ?> solid 2px">
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
        	?>
        </tbody>
    </table>
    <div id="modal-window-container">
        <button type="button" id="close4-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-1-tar">
                <p><strong>Создание нового отделочника</strong></p>
                <p>ФИО:</p>
                <p><input type="text" id="fio_designer"></p>
                <p>Номер телефона:</p>
                <p><input type="text" id="designer_contacts"></p>
                <p><button type="submit" id="save_designer" class="btn btn-primary">ОК</button></p>
        </div>
    </div>

<script>
    jQuery(document).ready(function() {
        jQuery('#select_label').niceSelect();
        jQuery("#select_label").change(function() {
            var color = (jQuery(".option.selected").data("color"));
            jQuery('.nice-select.wide')[0].style.setProperty('--rcolor', color);
            show_clients();
        });

        jQuery('#designer_contacts').mask('+7(999) 999-9999');
        jQuery('body').on('click', 'tr', function(e) {
            if (jQuery(this).data('href') != '' && jQuery(this).data('href') != undefined){
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

        jQuery("#save_designer").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=dealer.create_designer",
                data: {
                    fio: document.getElementById('fio_designer').value,
                    phone: document.getElementById('designer_contacts').value,
                    designer_type: 3
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

        jQuery("#find_designer").click(show_clients);

        function show_clients(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=findOldClients",
                data: {
                    fio: document.getElementById('name_find_designer').value,
                    flag: 'designers',
                    label_id: jQuery("#select_label").val()
                },
                success: function(data){
                    console.log(data);
                    var tbody = document.getElementById('tbody_designers');
                    tbody.innerHTML = '';
                    var html = '';
                    var color;
                    for(var i in data) {
                        html += '<tr style="outline: #'+data[i].label_color_code+' solid 2px" data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=designer&id=' + data[i].id + '">';
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
        }

        /*jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=RepeatSendCommercialOffer",
            success: function(data){
                //console.log(data);
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
        });*/
    });
</script>