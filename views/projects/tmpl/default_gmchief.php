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

$user = JFactory::getUser();
$userId = $user->get('id');
$groups = $user->get('groups');

$user       = JFactory::getUser();
$userId     = $user->get('id');

foreach ($this->items as $i => $item){
    if(!empty($item->project_mounter)){
        $mounters_array = explode(',',$item->project_mounter);
        $mounter = '';
        foreach ($mounters_array as $value) {
            $mounter .= JFactory::getUser($value)->name.'; ';
        }
        $item->project_mounter = $mounter;
    }
    $item->project_info = addslashes($item->project_info);
}
$projects = quotemeta(json_encode($this->items));
$done_statuses = [11,16,17,24,25,26,27,28,29];
?>
<?=parent::getButtonBack();?>
<form action="">
	<input id="jform_project_id" type="hidden" value="jform[project_id]" />
	<input id="jform_project_status" type="hidden" value="jform[project_status]" />
</form>
<?php if ($user->dealer_type != 2): ?><h2 class="center">Монтажи</h2><?php else: ?><h2 class="center">Заказы</h2><?php endif; ?>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div class="row right">
        <div class="col-md-7">
        </div>
        <div class="col-md-4">
            <input class="form-control" id="search_text" placeholder="Поиск">
        </div>
        <div class="col-md-1">
            <button class="btn btn-primary" id="search_btn" type="button"><i class="fas fa-search"></i> Найти</button>
        </div>
    </div>
    <?php if (count($this->items) > 0): ?>
	<table class="table table-striped" id="projectList">
		<thead>
			<tr>
				<th><i class="fas fa-check-double"></i></th>
				<th class='center'>
					Номер договора
				</th>
				<th class='center'>
					Дата монтажа
				</th>
				<th class='center'>
					Адрес
				</th>
				<th class='center'>
					Клиент
				</th>
				<th class='center'>
					Дилер
				</th>
				<th class='center'>
					Квадратура
				</th>
				<th class='center'>
					Бригада
				</th>
                <th class='center'>
                    Статус
                </th>
			</tr>
		</thead>
		<tbody>
			<?php   foreach ($this->items as $i => $item) :?>
                <?php
                    if($item->project_status == 30){
                        $style = 'style = "background: linear-gradient( to right, white 50%, red 100%);"';
                    }
                    else{
                        $style = "";
                    }
                ?>
				<tr data-project_id = "<?=$item->id; ?>">
                    <td>
                        <?php if ($item->project_status == 11): ?>
                            <button class="btn btn-primary btn-sm btn-done" data-project_id="<?= $item->id; ?>" type="button"><i class="fa fa-check"></i></button>
                        <?php endif; ?>
                        <?php if ($item->project_status == 12): ?>
                            <i class="fas fa-check-double"></i>
                        <?php endif; ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $item->id; ?>
                    </td>
                    <td class="center one-touch">
                        <?= $item->project_mounting_date; ?>
                    </td>
					<td class="center one-touch">
						<?php echo $this->escape($item->project_info); ?>
					</td>
					<td class="center one-touch">
                        <?= "$item->client_name<br>$item->client_contacts;" ?>

					</td>
					<td class="center one-touch">
						<?php echo $item->dealer_name; ?>
					</td>
					<td class="center one-touch">
						<?= $item->quadrature; ?>
					</td>
                    <td class="center one-touch"><?= $item->project_mounter; ?></td>
                    <td class="center one-touch" <?php echo $style; ?>>
                        <?= $item->status;?>
                    </td>
				</tr>
			<?php endforeach; ?>
		</tbody>
    </table>
	<?php echo JHtml::_('form.token'); ?>
    <?php else: ?>
        <p class="center">
        <h3>У вас еще нет заказов!</h3>
        </p>
        <button id="new_order_btn" class="btn btn-primary" type="button">Сделайте заказ прямо сейчас</button>
    <?php endif; ?>
</form>

<script type="text/javascript">
	jQuery(document).ready(function () {
        var projects = JSON.parse('<?php echo $projects;?>'),
            done_statuses = JSON.parse('<?= json_encode($done_statuses)?>');
        jQuery('body').on('click',".btn-done",function(e){
			var button = jQuery( this ),
                subject = "Отметка стоимости договора <br> № " + button.data("project_id"),
                text = "",
                project = projects.find(function (item) {
                    return item.id == button.data('project_id');
                }),
                project_sum = !empty(project.new_project_sum) ? project.new_project_sum : project.project_sum,
                components_sum = !empty(project.self_price) ? project.self_price : +project.canvases_sum + +project.components_sum,
                mounting_sum = +project.mounting_sum+ +project.transport_cost;
            text += "<div class='dop_info_block' style='font-size:15px;'>";
            text += "<div class='center'>Укажите новую стоимость договора</div><div class='center'><input id='input_check' class='noty_input' style='margin-top: 5px;' value='" + project_sum + "'/></div></br>";
            text += "<div class='center'>Укажите новую стоимость расходных материалов</div><div class='center'><input id='input_material' class='noty_input' style='margin-top: 5px;' value='" + components_sum + "'/></div></br>";
            text += "<div class='center'>Укажите новую стоимость монтажных работ</div><div class='center'><input id='input_mounting' class='noty_input' style='margin-top: 5px;' value='" + mounting_sum + "'/></div>";
            text += "</div>";
            modal({
                type: 'primary',
                title: subject,
                text: text,
                size: 'small',
                buttons: [{
                    text: 'Выполнено', //Button Text
                    val: 0, //Button Value
                    eKey: true, //Enter Keypress
                    onClick: function(dialog) {
                        var input_value = jQuery("#input_check").val(),
                            input_mounting = jQuery("#input_mounting").val(),
                            input_material = jQuery("#input_material").val(),
                            profit = parseFloat(input_value) - (parseFloat(input_mounting)+parseFloat(input_mounting));

                        jQuery.ajax({
                            type: 'POST',
                            url: "index.php?option=com_gm_ceiling&task=project.done",
                            data: {
                                project_id : button.data("project_id"),
                                new_value : input_value,
                                mouting_sum : input_mounting,
                                material_sum : input_material,
                                check: 1
                            },
                            success: function(data){
                                button.closest("td").html("<i class='fa fa-check' aria-hidden='true'></i> Закрыт!");
                                var n = noty({
                                    theme: 'relax',
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "success",
                                    text: data
                                });
                            },
                            dataType: "text",
                            timeout: 10000,
                            error: function(data){
                                console.log(data);
                                var n = noty({
                                    theme: 'relax',
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "error",
                                    text: "Ошибка при попытке сохранить отметку. Сервер не отвечает"
                                });
                            }
                        });
                        return 1;
                    }
                },
                    {
                        addClass: 'btn', text: 'Отмена', onClick: function($noty) {
                            $noty.close();
                        }
                    }
                ],
                callback: null,
                autoclose: false,
                center: true,
                closeClick: true,
                closable: true,
                theme: 'xenon',
                animate: true,
                background: 'rgba(0,0,0,0.35)',
                zIndex: 1050,
                buttonText: {
                    ok: 'Поставить',
                    cancel: 'Снять'
                },
                template: '<div class="modal-box"><div class="modal-inner"><div class="modal-title"><a class="modal-close-btn"></a></div><div class="modal-text"></div><div class="modal-buttons"></div></div></div>',
                _classes: {
                    box: '.modal-box',
                    boxInner: ".modal-inner",
                    title: '.modal-title',
                    content: '.modal-text',
                    buttons: '.modal-buttons',
                    closebtn: '.modal-close-btn'
                }

            });
			return false;
		});

		jQuery("#search_btn").click(function () {
		    var style,check_btn = '';
		    jQuery("#projectList > tbody").empty();
		    var search_str = jQuery("#search_text").val().toLowerCase(),
                regExp = new RegExp(search_str);
		    jQuery.each(projects,function (index,project) {
                if(regExp.test(project.id.toLowerCase())||
                    regExp.test(project.client_name.toLowerCase()) ||
                    regExp.test(project.project_info.toLowerCase()) ||
                    regExp.test(project.dealer_name.toLowerCase())){
                    if(project.project_status == 30){
                        style = 'style = "background: linear-gradient( to right, white 50%, red 100%);"';
                    }
                    else{
                        style = "";
                    }
                    if(done_statuses.indexOf(parseInt(project.project_status)) != -1 || project.project_status == 12) {
                        if (done_statuses.indexOf(parseInt(project.project_status)) != -1) {
                            check_btn = '<button class="btn btn-primary btn-sm btn-done" data-project_id="' + project.id + '" type="button"><i class="fa fa-check"></i></button>';
                        }
                        if (project.project_status == 12) {
                            check_btn = '<i class="fas fa-check-double"></i>';
                        }
                    }
                    else{
                        check_btn = '';
                    }
                    jQuery("#projectList > tbody").append('<tr data-project_id = "'+project.id+'"></tr>');
                    jQuery("#projectList > tbody > tr:last").append(
                        '<td class="center one-touch">'+check_btn+'</td>' +
                        '<td class="center one-touch">'+project.id+'</td>' +
                        '<td class="center one-touch">'+project.project_mounting_date+'</td>' +
                        '<td class="center one-touch">'+project.project_info+'</td>' +
                        '<td class="center one-touch">'+project.client_contacts+'</td>' +
                        '<td class="center one-touch">'+project.client_name+'</td>' +
                        '<td class="center one-touch">'+project.dealer_name+'</td>' +
                        '<td class="center one-touch">'+project.quadrature+'</td>' +
                        '<td class="center one-touch">'+project.project_mounter+'</td>' +
                        '<td class="center one-touch"'+style+'>'+project.status+'</td>');
                }
            });
        });
        jQuery('body').on('click', "#projectList > tbody > tr", function () {
		    var projectId = jQuery(this).data('project_id');
            location.href = '/index.php?option=com_gm_ceiling&view=projectform&type=gmchief&id='+projectId;
        })
	});
</script>


