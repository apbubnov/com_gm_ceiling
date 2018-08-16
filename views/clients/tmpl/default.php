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

$dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');

$dop_num = $dop_num_model->getData($userId)->dop_number;
$clients = $clients_model->getClientsAndProjects();
foreach ($clients as $key => $value) {
    $clients[$key]->created = date("d.m.Y H:i", strtotime($value->created));
}

$_SESSION['user_group'] = $user_group;
$_SESSION['dop_num'] = $dop_num;

$jinput = JFactory::getApplication()->input;
$type = $jinput->getString('type', NULL);
$status_model = Gm_ceilingHelpersGm_ceiling::getModel('statuses');
$status = $status_model->getData();

echo parent::getPreloaderNotJS();
?>

<h2 class = "center">Клиенты</h2>

<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clients&type='.$type); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid toolbar">
		<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientform&id=0&type='.$type, false, 2); ?>" class="btn btn-primary">
			Добавить клиента
		</a>
        <select id="select_status">
            <option value='' selected>Выберите статус</option>
            <?php foreach($status as $item): ?>
                <?php if(($item->id > 0 && $item->id <= 5 ) || $item->id == 10 || $item->id == 12) { ?>
                    <option value="<?php echo $item->id; ?>"><?php echo $item->title; ?></option>
                <?php } ?>
            <?php endforeach;?>
        </select>
        <input type="text" id="search_text">
        <button type="button" class="btn btn-primary" id="search_btn"><b class="fa fa-search"></b></button>
	</div>
	<table class="small_table table-striped table_cashbox one-touch-view g_table" id="clientList">
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
                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                </th>
			</tr>
            <tr class="row" id="TrClone" data-href="" style="display: none">
                <td class="one-touch created"></td>
                <td class="one-touch name"></td>
                <td class="one-touch address"></td>
                <td class="one-touch status"></td>
                <td class="one-touch delete"></td>
            </tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</form>
<center><button type="button" class="btn" id="show_btn">Показать еще</button></center>
<script type="text/javascript">

jQuery(document).ready(function(){
    var clients_data = JSON.parse('<?php echo json_encode($clients); ?>');
    var elem_select_status = document.getElementById('select_status');
    var elem_search = document.getElementById('search_text');

    //console.log(clients_data);
    var wheel_count_clients = null, last_tr = null;

    var $ = jQuery;
    var list = $("#clientList tbody");

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

    var elem_show = document.getElementById('show_btn');

    elem_show.onclick = function(){
        if (clients_data.length > wheel_count_clients + 1)
        {
            print_clients(wheel_count_clients + 1, clients_data.length);
        }
    }

    /*function check_bottom_tr(){
        if (clients_data.length > wheel_count_clients + 1 && inWindow(last_tr).length > 0)
        {
            print_clients(wheel_count_clients + 1, clients_data.length);
        }
    }
    
    function inWindow(s){
        var scrollTop = $(window).scrollTop();
        var windowHeight = $(window).height();
        var currentEls = $(s);
        var result = [];
        currentEls.each(function(){
            var el = $(this);
            var offset = el.offset();
            if(scrollTop <= offset.top && (el.height() + offset.top) < (scrollTop + windowHeight))
                result.push(this);
        });
        return $(result);
    }*/

    show_clients();

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
        var search = elem_search.value;
        var search_reg = new RegExp(search, "ig");
        for(var i = begin, cl_i, iter = 0; i < clients_data.length; i++)
        {
            cl_i = clients_data[i];
            if ((search_reg.test(cl_i.client_name) || search_reg.test(cl_i.address) ||
                search_reg.test(cl_i.client_contacts) || search_reg.test(cl_i.id)) && 
                (status === "" || status === cl_i.status_id))
            {
                var tr = $("#TrClone").clone();

                tr.show();
                tr.find(".created").text(cl_i.created);
                if (cl_i.client_contacts != null)
                {
                    tr.find(".name").text(cl_i.client_contacts + ' ' + cl_i.client_name);
                }
                else
                {
                    tr.find(".name").text(cl_i.client_name);
                }
                if (clients_data[i].address != null)
                {
                    tr.find(".address").text(cl_i.address);
                }
                else
                {
                    tr.find(".address").text('-');
                }
                if (cl_i.status != null)
                {
                    tr.find(".status").text(cl_i.status);
                }
                else
                {
                    tr.find(".status").text('-');
                }
                tr.find(".delete").append('<button class = "btn btn-danger btn-sm" data-cl_id =' + cl_i.client_id +' type = "button"><i class="fa fa-trash-o" aria-hidden="true"></i></button>');
                tr.attr("data-href", "/index.php?option=com_gm_ceiling&view=clientcard&id="+cl_i.client_id);
                list.append(tr);
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
        OpenPage();
    }
    
    function OpenPage() {
        var e = jQuery("[data-href]");
        jQuery.each(e, function (i, v) {
            jQuery(v).click(function () {
                document.location.href = this.dataset.href;
            });
        });
    }
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
                                jQuery('.btn-danger[data-cl_id ='+id+']').closest('.row').remove();
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

