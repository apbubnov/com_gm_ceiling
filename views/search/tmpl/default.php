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
if (!in_array("16", $user_group)){
    die('403 Forbidden');
}
?>

<?=parent::getButtonBack();?>

<h2 class="center">Поиск</h2>

	<div class="row-fluid toolbar">
		<input type="text" id="search_text">
        <button class="btn btn-primary" id="btn_search"><i class="fa fa-search"></i></button>
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
                <th>
                    Контакты
                </th>
                <th>
                    Проекты
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
        jQuery('body').on('click', 'tr', function(e)
        {
            if(jQuery(this).data('href')!=""){
                document.location.href = jQuery(this).data('href');
            } 
        });
        jQuery('#btn_search').click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=clients.searchClients",
                data: {
                    search_text: document.getElementById('search_text').value
                },
                success: function(data){
                    //console.log(data);
                    var tbody = document.getElementById('tbody_search');
                    tbody.innerHTML = '';
                    var html = '';
                    var d_type = '';
                    for(var i = data.length, d_i; i--;)
                    {
                        d_i = data[i];
                        if (d_i.dealer_type == 3)
                        {
                            html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=designer&id=' + d_i.id + '">';
                            d_type = 'Отделочник';
                        }
                        else if (d_i.dealer_type == 5)
                        {
                            html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=designer2&id=' + d_i.id + '">';
                            d_type = 'Дизайнер';
                        }
                        else if (d_i.dealer_type == 6)
                        {
                            html += '<tr data-href="/index.php?option=com_gm_ceiling&view=manufacturers&type=info&id=' + d_i.id + '">';
                            d_type = 'Производитель';
                        }
                        else if (d_i.dealer_type == 7)
                        {
                            html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=builder&id=' + d_i.id + '">';
                            d_type = 'Застройщик';
                        }
                        else if (d_i.dealer_type == 8)
                        {
                            html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=wininstaller&id=' + d_i.id + '">';
                            d_type = 'Оконщик';
                        }
                        else if (d_i.dealer_type == 1 || d_i.dealer_type == 0)
                        {
                            html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id=' + d_i.id + '">';
                            d_type = 'Дилер';
                        }
                        else if (d_i.dealer_type == null || d_i.dealer_type == 2)
                        {
                            html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&id=' + d_i.id + '">';
                            d_type = 'Клиент';
                        }
                        if (d_i.project_info == null)
                        {
                            d_i.project_info = '-';
                        }
                        if (d_i.client_contacts == null)
                        {
                            d_i.client_contacts = '-';
                        }
                        if (d_i.projects_ids == null)
                        {
                            d_i.projects_ids = '-';
                        }
                        if (d_i.client_dop_contacts == null)
                        {
                            d_i.client_dop_contacts = '-';
                        }
                        html += '<td>' + d_i.created + '</td>';
                        html += '<td>' + d_i.client_name + '<br>' + d_i.client_contacts + '</td>';
                        html += '<td>' + d_i.project_info + '</td>';
                        html += '<td>' + d_type + '</td>';
                        html += '<td>' + d_i.client_dop_contacts + '</td>';
                        html += '<td>' + d_i.projects_ids + '</td></tr>';
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

