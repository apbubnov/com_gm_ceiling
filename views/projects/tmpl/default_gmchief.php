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
$projects = json_encode($this->items);
?>
<?=parent::getButtonBack();?>
<form action="">
	<input id="jform_project_id" type="hidden" value="jform[project_id]" />
	<input id="jform_project_status" type="hidden" value="jform[project_status]" />
</form>
<?php if ($user->dealer_type != 2): ?><h2 class="center">Монтажи</h2><?php else: ?><h2 class="center">Заказы</h2><?php endif; ?>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief'); ?>" method="post"
      name="adminForm" id="adminForm">
    <?php if (false): ?>
	  <div class="toolbar">
		<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
		</div>
    <?php endif; ?>
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
	<table class="table table-striped  g_table" id="projectList">
		<thead>
			<tr>
				<th></th>
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
					Телефоны
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
                        <?php if ($item->project_status == 10): ?>
                            <button class="btn btn-primary btn-sm btn-done" data-project_id="<?= $item->id; ?>" type="button"><i class="fa fa-check"></i></button>
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
						<?php echo $item->client_contacts; ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_name; ?>
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
	    var projects = JSON.parse('<?php echo $projects;?>');
	    console.log("p",projects);
        jQuery('body').on('click',".btn-done",function(){
			var button = jQuery( this );
			
			noty({
				layout	: 'center',
				type	: 'warning',
				modal	: true,
				text	: 'Вы уверены, что хотите отметить договор выполненным?',
				killer	: true,
				buttons	: [
					{addClass: 'btn btn-success', text: 'Выполнен', onClick: function($noty) {
							jQuery.get(
							  "/index.php?option=com_gm_ceiling&task=project.done",
							  {
							      project_id: button.data("project_id"),
                                  check:1
							  },
							  function(data){
								  if(data == "1") {
									  button.closest("td").html("<i class='fa fa-check' aria-hidden='true'></i> Выполнено");
								  }
							  }
							);
							$noty.close();
						}
					},
					{addClass: 'btn', text: 'Отмена', onClick: function($noty) {
							$noty.close();
						}
					}
				]
			});

		});

		jQuery("#search_btn").click(function () {
		    var style,check_btn;
		    jQuery("#projectList > tbody").empty();
		    var search_str = jQuery("#search_text").val();
		    jQuery.each(projects,function (index,project) {
                if(project.client_name.indexOf(search_str) != -1 ||
                    project.project_info.indexOf(search_str) != -1 ||
                    project.dealer_name.indexOf(search_str) != -1){
                    if(project.project_status == 30){
                        style = 'style = "background: linear-gradient( to right, white 50%, red 100%);"';
                    }
                    else{
                        style = "";
                    }
                    if(project.project_status == 10){
                        check_btn = '<button class="btn btn-primary btn-sm btn-done" data-project_id="'+project.id+'" type="button"><i class="fa fa-check"></i></button>';
                    }
                    else{
                        check_btn = "";
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


