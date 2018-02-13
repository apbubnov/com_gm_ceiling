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
$dop_num = $dop_num_model->getData($userId)->dop_number;
$_SESSION['user_group'] = $user_group;
$_SESSION['dop_num'] = $dop_num;

?>

<?=parent::getButtonBack();?>

<h2 class="center">Поиск</h2>

	<div class="row-fluid toolbar">
		<input type="text" id="search_text">
        <button class="btn-primary" id="btn_search"><i class="fa fa-search"></i></button>
	</div>
	<table class="table table-striped table_cashbox one-touch-view" id="clientList">
		<thead>
			<tr class="row">
				<th>
                    Создан
				</th>
				<th>
                    Имя/Телефон
				</th>
				<th>
                    Адрес
				</th>
                <th>
                    Тип
                </th>
			</tr>
		</thead>

		<tbody id="tbody_search">
		</tbody>
	</table>

<style>
    @media (max-width: 1024px) {
        table, table *  {
            font-size: 10px !important;
            padding: .1rem !important;
            width: auto !important;
            margin: 0 !important;
        }

        table {
            margin: 0 -30px !important;
            width: calc(100% + 60px) !important;
            max-width: none !important;
        }
    }
</style>

<script type="text/javascript">
	jQuery(document).ready(function(){
        jQuery('#btn_search').click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=clients.searchClients",
                data: {
                    search_text: document.getElementById('search_text').value
                },
                success: function(data){
                    console.log(data);
                    var tbody = document.getElementById('tbody_search');
                    tbody.innerHTML = '';
                    var html = '';
                    var d_type = '';
                    for(var i in data)
                    {
                        if (data[i].dealer_type == 3)
                        {
                            html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=designer&id=' + data[i].id + '">';
                            d_type = 'Отделочник';
                        }
                        else if (data[i].dealer_type == 1 || data[i].dealer_type == 0)
                        {
                            html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id=' + data[i].id + '">';
                            d_type = 'Дилер';
                        }
                        else if (data[i].dealer_type == null)
                        {
                            html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&id=' + data[i].id + '">';
                            d_type = 'Клиент';
                        }
                        if (data[i].project_info == null)
                        {
                            data[i].project_info = '-';
                        }
                        if (data[i].client_contacts == null)
                        {
                            data[i].client_contacts = '-';
                        }
                        html += '<td>' + data[i].created + '</td>';
                        html += '<td>' + data[i].client_name + '<br>' + data[i].client_contacts + '</td>';
                        html += '<td>' + data[i].project_info + '</td>';
                        html += '<td>' + d_type + '</td></tr>';
                    }
                    tbody.innerHTML = html;
                    html = '';
                },
                dataType: "json",
                async: false,
                timeout: 20000,
                error: function(data){
                    console.log(data);
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

