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

<h2 class = "center">Поиск</h2>

	<div class="row-fluid toolbar">
		<input type="text" id="search_text">
        <button class="primary"><i class="fa fa-search"></i></button>
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
                    Статус
                </th>
                <th>
                    Тип
                </th>
			</tr>
		</thead>

		<tbody>
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

    });
</script>

