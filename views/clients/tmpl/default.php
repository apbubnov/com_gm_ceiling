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

echo parent::getPreloaderNotJS();

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
	<table class="small_table table-striped table_cashbox one-touch-view" id="clientList">
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
			</tr>
            <tr class="row" id="TrClone" data-href="" style="display: none">
                <td class="one-touch created"></td>
                <td class="one-touch name"></td>
                <td class="one-touch address"></td>
                <td class="one-touch status"></td>
            </tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</form>

<script type="text/javascript">

jQuery(document).ready(function(){
    var clients_data = JSON.parse('<?php echo json_encode($clients); ?>');
    var elem_select_status = document.getElementById('select_status');
    var elem_search = document.getElementById('search_text');

    //console.log(clients_data);

    document.onkeydown = function(e){
        if(e.keyCode === 13){
            show_clients();
            return false;
        }
    };
    
    elem_select_status.onchange = show_clients;
    document.getElementById('search_btn').onclick = show_clients;

    var $ = jQuery;

    // вызовем событие resize
    $(window).resize();

    show_clients();

    function show_clients()
    {
        var status = elem_select_status.value;
        var search = elem_search.value;
        var search_reg = new RegExp(search, "ig");
        var list = $("#clientList tbody");
        list.empty();

        for(var i = 0, cl_i; i < clients_data.length; i++)
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
                
                tr.attr("data-href", "/index.php?option=com_gm_ceiling&view=clientcard&id="+cl_i.client_id);
                list.append(tr);
            }
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

    document.body.onload = function(){
        jQuery(".PRELOADER_GM").hide();
    };
});
</script>

