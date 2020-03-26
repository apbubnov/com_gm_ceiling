<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$user       = JFactory::getUser();
$userId     = $user->get('id');
$user_group = $user->groups;

$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');

$clients = $clients_model->getClientsAndProjects();
foreach ($clients as $key => $value) {
    $clients[$key]->created = date("d.m.Y H:i", strtotime($value->created));
}

$jinput = JFactory::getApplication()->input;
$status_model = Gm_ceilingHelpersGm_ceiling::getModel('statuses');
$status = $status_model->getData();

$labels = $clients_model->getClientsLabels($user->dealer_id);

echo parent::getPreloaderNotJS();
?>

<style type="text/css">
    .table {
        border-collapse: separate;
        border-spacing: 0 0.5em;
    }
    th {
        text-align: center;
    }
</style>

<div class="row">
    <div class="col-md-2 col-xs-6">
        <a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientform&id=0', false, 2); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Клиент</a>
    </div>
    <div class="col-md-3 col-xs-6">
        <a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clients&type=labels', false, 2); ?>" class="btn btn-primary"><i class="fa fa-tags"></i> Ярлыки</a>
    </div>
    <div class="col-md-0 col-xs-3"></div>
    <div class="col-md-7 col-xs-9">
        <h2>Клиенты</h2>
    </div>
</div>


<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clients'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row" style="margin-bottom: 10px;">
        <div class="col-md-4 col-xs-6">
            <select id="select_status" class="form-control">
                <option value='' selected>Статусы</option>
                <?php foreach($status as $item): ?>
                    <?php if(($item->id > 0 && $item->id <= 5 ) || $item->id == 10 || $item->id == 12) { ?>
                        <option value="<?php echo $item->id; ?>"><?php echo $item->title; ?></option>
                    <?php } ?>
                <?php endforeach;?>
            </select>
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
        <div class="col-md-3 col-xs-9">
            <input type="text" id="search_text" class="form-control">
        </div>
        <div class="col-md-1 col-xs-3" style="padding: 0px;">
            <button type="button" class="btn btn-primary" id="search_btn"><b class="fa fa-search"></b></button>
        </div>
	</div>
	<table class="table one-touch-view g_table" id="clientList">
		<thead>
			<tr>
				<th>
                    Создан
				</th>
				<th>
                    Клиент
				</th>
				<th>
                    Адрес
				</th>
                <th>
                    Статус
                </th>
                <th>
                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                </th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</form>
<center><button type="button" class="btn" id="show_btn">Показать еще</button></center>
<script type="text/javascript">

jQuery(document).ready(function(){
    var clients_data = JSON.parse('<?php echo quotemeta(json_encode($clients)); ?>'),
        elem_select_status = document.getElementById('select_status'),
        elem_select_label = document.getElementById('select_label'),
        elem_search = document.getElementById('search_text'),
        savedData = JSON.parse(localStorage.getItem('savedData')),
        wheel_count_clients = null, last_tr = null,
        $ = jQuery,
        list = $("#clientList tbody"),
        elem_show = document.getElementById('show_btn');
    localStorage.removeItem('savedData');
    console.log(savedData);
    if(!empty(savedData)){
        if(!empty(savedData.status)){
            elem_select_status.value = savedData.status;
        }
        if(!empty(savedData.label)){
            jQuery('#select_label').val(savedData.label);
            jQuery.each(jQuery('li.option'),function(index,option){
                if(jQuery(option).data('value') == savedData.label){
                    jQuery(option).addClass('selected');
                    var color = (jQuery(".option.selected").data("color"));
                    jQuery('.nice-select.wide')[0].style.setProperty('--rcolor', color);
                    jQuery('.current').text(option.innerText);
                    return;
                }
            });
        }
        if(!empty(savedData.search)){
            jQuery('#search_text').val(savedData.search);
        }
    }
    jQuery('#select_label').niceSelect();
    jQuery("#select_label").change(function() {
        var color = (jQuery(".option.selected").data("color"));
        jQuery('.nice-select.wide')[0].style.setProperty('--rcolor', color);
        show_clients();
    });

    function scrollToTr(trIndex){
        var need_row = jQuery('#clientList > tbody').find('tr').eq(trIndex);
        jQuery('html, body').animate({
            scrollTop: jQuery(need_row).offset().top
        }, 500);
    }

    $(window).resize();

    elem_search.onfocus = function(){
        wheel_count_clients= null;
        last_tr = null;
        list.empty();
    };

    document.onkeydown = function(e){
        if(e.keyCode === 13){
            elem_search.blur();
            show_clients();
            return false;
        }
    };
    
    elem_select_status.onchange = show_clients;
    document.getElementById('search_btn').onclick = show_clients;

    //document.onwheel = check_bottom_tr;
    //document.body.onmousemove = check_bottom_tr;
    //document.body.ontouchmove = check_bottom_tr;



    elem_show.onclick = function(){
        if (clients_data.length > wheel_count_clients + 1)
        {
            print_clients(wheel_count_clients + 1, clients_data.length);
        }
    }
    show_clients();
    if(!empty(savedData)){
        if(savedData.trIndex > 0){
            if(savedData.trIndex <=jQuery('#clientList > tbody > tr').length){
                scrollToTr(savedData.trIndex);
            }
            else{
                var tableLength = jQuery('#clientList > tbody > tr').length;
                console.log(tableLength);
                while(savedData.trIndex >=tableLength){
                    console.log(tableLength);
                    jQuery('#show_btn').trigger('click');
                    tableLength = jQuery('#clientList > tbody > tr').length;
                }
                scrollToTr(savedData.trIndex);
            }
        }
    }

    function show_clients()
    {
        wheel_count_clients = null;
        last_tr = null;
        list.empty();
        print_clients(0);
    }

    function print_clients(begin)
    {
        var status = elem_select_status.value;
        var label = elem_select_label.value;
        var search = elem_search.value;
        var search_reg = new RegExp(search, "ig");
        for (var i = begin, cl_i, iter = 0; i < clients_data.length; i++)
        {
            cl_i = clients_data[i];
            var cl_info = !empty(cl_i.client_contacts)? cl_i.client_contacts+' '+cl_i.client_name : cl_i.client_name,
                cl_address = !empty(cl_i.address) ? cl_i.address : '-',
                cl_status = !empty(cl_i.status) ? cl_i.status : '-';

            if ((search_reg.test(cl_i.client_name) || search_reg.test(cl_i.address) ||
                search_reg.test(cl_i.client_contacts) || search_reg.test(cl_i.id)) && 
                (status === '' || status === cl_i.status_id) &&
                (label === '' || label === cl_i.label_id)){

                jQuery('#clientList > tbody').append('<tr data-client_id='+cl_i.client_id+'></tr>');
                jQuery('#clientList > tbody > tr:last').append( '<td>'+cl_i.created+'</td>' +
                                                                '<td>'+cl_info+'</td>' +
                                                                '<td>'+cl_address+'</td>' +
                                                                '<td>'+cl_status+'</td>' +
                                                                '<td><button class = "btn btn-danger btn-sm" data-cl_id =' + cl_i.client_id +' type = "button"><i class="fas fa-trash-alt" aria-hidden="true"></i></button></td>'
                );
                var tr = jQuery('#clientList > tbody > tr:last');

                if (cl_i.label_color_code !== null) {
                    tr.css('outline', '#'+cl_i.label_color_code+' solid 2px');
                }
                tr.css('margin-top', '10px');
                //list.append(tr);
                wheel_count_clients = i;
                iter++;
                if (iter === 20)
                {
                    elem_show.style.display = 'block';
                    break;
                }
            }
            if (i === clients_data.length - 1)
            {
                elem_show.style.display = 'none';
            }
        }
        if (wheel_count_clients !== null)
        {
            var elems_tr = list[0].getElementsByTagName('tr');
            last_tr = elems_tr[elems_tr.length - 1];
        }

    }

    jQuery('#clientList').on('click','tr',function(){
        var rowIndex = jQuery(this)[0].rowIndex - 2,
            clientId = jQuery(this).data('client_id'),
            statusFilter = jQuery('#select_status').val(),
            labelFilter = jQuery('#select_label').val(),
            textFilter = jQuery('#search_text').val();
        localStorage.setItem('savedData',JSON.stringify({
            trIndex: rowIndex,
            status: statusFilter,
            label: labelFilter,
            search: textFilter
        }));
        location.href ='/index.php?option=com_gm_ceiling&view=clientcard&id='+clientId;
    });

    jQuery(".btn-danger").click(function(e){
        var id = jQuery(this).data('cl_id');
        noty({
            theme: 'relax',
            layout: 'center',
            timeout: false,
            type: "info",
            text: "Вы действительно хотите удалить клиента?",
            buttons:[
                {
                    addClass: 'btn btn-primary', text: 'Удалить', onClick: function($noty) {
                        jQuery.ajax({
                            type: 'POST',
                            url: "index.php?option=com_gm_ceiling&task=client.delete_by_user",
                            async: false,
                            data: {
                                client_id: id
                            },
                            success: function(data){
                                jQuery('.btn-danger[data-cl_id ='+id+']').closest('tr').remove();
                                let remove_id = clients_data.findIndex(function(el,index,arr){return el.client_id == id });
                                clients_data.splice(remove_id,1);
                            },
                            dataType: "json",
                            timeout: 20000,
                            error: function(data){
                                console.log(data);
                                var n = noty({
                                    timeout: 2000,
                                    theme: 'relax',
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "error",
                                    text: "Сервер не отвечает."
                                });
                            }                   
                        });
                        $noty.close();
                    }
                },
                {
                    addClass: 'btn btn-primary', text: 'Отмена', onClick: function($noty) {
                        $noty.close();
                    }
                }
            ]
        });
        
        return false;
    });
    
   
    document.body.onload = function(){
        jQuery(".PRELOADER_GM").hide();
    };
});
</script>

