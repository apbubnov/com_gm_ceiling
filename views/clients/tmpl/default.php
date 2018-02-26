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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$user_group = $user->groups;
$dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
$dop_num = $dop_num_model->getData($userId)->dop_number;
$_SESSION['user_group'] = $user_group;
$_SESSION['dop_num'] = $dop_num;
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'clientform.xml');
$canEdit    = $user->authorise('core.edit', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'clientform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gm_ceiling');
$canChange  = $user->authorise('core.edit.state', 'com_gm_ceiling');
$canDelete  = $user->authorise('core.delete', 'com_gm_ceiling');

$jinput = JFactory::getApplication()->input;
$type = $jinput->getString('type', NULL);
$status_model = Gm_ceilingHelpersGm_ceiling::getModel('statuses');
$status = $status_model->getData();
?>

<?php parent::getButtonBack();?>

<h2 class = "center">Клиенты</h2>

<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clients&type='.$type); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid toolbar">
		<div class="span3">
			<?php if ($canCreate) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientform&id=0&type='.$type, false, 2); ?>" class="btn btn-success"><i class="icon-plus"></i>
					Добавить клиента
				</a>
			<?php endif; ?>
		</div>
        <div id="search" style="display: none; width: 40px; height: 40px;"><i class="fa fa-search"></i></div>
		<div class="span9">
			<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
		</div>
        <select id="select_status" ><option value='' selected>Выберите статус</option>
            <?php foreach($status as $item): ?>
            <?php if(($item->id > 0 && $item->id <= 5 ) || $item->id == 10 || $item->id == 12 ) { ?>
                <option value="<?php echo $item->id; ?>"><?php echo $item->title; ?></option>
            <?php } ?>
            <?php endforeach;?>
        </select>
	</div>
	<table class="table table-striped table_cashbox one-touch-view" id="clientList">
		<thead>
			<tr>
				<th class='' >
					<?php //echo JHtml::_('grid.sort',  'Создан', 'a.created', $listDirn, $listOrder); ?>
                    Создан
				</th>
				<th class=''>
					<?php //echo JHtml::_('grid.sort',  'COM_GM_CEILING_CLIENTS_CLIENT_NAME', 'a.client_name', $listDirn, $listOrder); ?>
                    Клиент
				</th>
				<th class=''>
					<?php //echo JHtml::_('grid.sort',  'COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS', 'a.client_contacts', $listDirn, $listOrder); ?>
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
        <!-- по сути этот кусок кода не нужен, т.к. таблицу формирует jQ...-->
<!--		--><?php //foreach ($this->items as $i => $item) : ?>
<!--			--><?php //$canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>
<!--			--><?php //if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
<!--				--><?php //$canEdit = JFactory::getUser()->id == $item->created_by; ?>
<!--			--><?php //endif; ?>
<!--			--><?php //if($item->id !== $user->associated_client): ?>
<!--			<tr class="row--><?php //echo $i % 2; ?><!-- inform" data-href="--><?php //echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&id='.(int) $item->id); ?><!--">-->
<!--				<td class="one-touch created">-->
<!--					--><?php
//						if($item->created == "0000-00-00 00:00:00") {
//							echo "-";
//						} else {
//							$jdate = new JDate($item->created);
//							$created = $jdate->format("d.m.Y H:i");
//							echo $created;
//						}
//					?>
<!--                    -->
<!--				</td>-->
<!--				<td class="one-touch name">--><?php //echo $this->escape($item->client_name); ?><!--<br>--><?php //echo $item->client_contacts; ?><!--</td>-->
<!--                <td class="one-touch address"> --><?php //print_r($item); ?><!-- </td>-->
<!--			</tr>-->
<!--			--><?php //endif; endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

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

	jQuery(document).ready(function () {
	   // if(jQuery("#filter_search").val() == '') {
            jQuery("#select_status").change();
        //}
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem() {

		if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}

    var $ = jQuery;

    // вызовем событие resize
    $(window).resize();

    jQuery("#select_status").change(function ()
    {
        var status = jQuery("#select_status").val();
        var search = jQuery("#filter_search").val();
        jQuery.ajax({
            type: "POST",
            url: "/index.php?option=com_gm_ceiling&task=filterProjectForStatus",
            data: {
                status: status,
                search: search
            },
            dataType: "json",
            async: true,
            cache: false,
            success: function (data) {
                console.log(data);
                var list = $("#clientList tbody");
                list.empty();
                var text='';
                for(i=0;i<data.length;i++){
                    var tr = $("#TrClone").clone();

                    tr.show();
                    tr.find(".created").text(data[i].created);
                    if (data[i].client_contacts != null)
                    {
                        tr.find(".name").text(data[i].client_contacts + ' ' + data[i].client_name);
                    }
                    else
                    {
                        tr.find(".name").text(data[i].client_name);
                    }
                    if (data[i].address != null)
                    {
                        tr.find(".address").text(data[i].address);
                    }
                    else
                    {
                        tr.find(".address").text('-');
                    }
                    if (data[i].status != null)
                    {
                        tr.find(".status").text(data[i].status);
                    }
                    else
                    {
                        tr.find(".status").text('-');
                    }
                    
                    tr.attr("data-href", "/index.php?option=com_gm_ceiling&view=clientcard&id="+data[i].client_id);
                    list.append(tr);
                }
                OpenPage();
            },
            timeout: 50000,
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера"
                });
            }
        });
    });
    function OpenPage() {
        var e = jQuery("[data-href]");
        jQuery.each(e, function (i, v) {
            jQuery(v).click(function () {
                document.location.href = this.dataset.href;
            });
        });
    }
    var tmp = 1;
    jQuery("#search").click(function () {
        if(tmp == 1) { jQuery(".span9").show(); tmp = 0;}
        else { jQuery(".span9").hide(); tmp = 1;}
    })
</script>

