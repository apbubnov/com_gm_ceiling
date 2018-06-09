<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$user    = JFactory::getUser();
$userId     = $user->get('id');

$id_brigade = $_GET["id"];

$model = Gm_ceilingHelpersGm_ceiling::getModel('Team');
$brigade = $model->GetData($id_brigade);
$mounters = $model->GetMounters($id_brigade);
$AllBrigade = $model->GetAllBrigade($user->dealer_id);
$month = date("n");
$year = date("Y");
$date = $year."-".$month;
$ProjectCurrentMonth = $model->GetProjects($id_brigade, $date);
/* $ProjectCurrentMonth = [];
foreach ($ProjectCurrentMonthAll as $value) {
	if (isset($ProjectCurrentMonth[$value->id])) {
		$ProjectCurrentMonth[$value->id]["mounting_sum"] += $value->mounting_sum;
	} else {
		$ProjectCurrentMonth[$value->id] = ["id" => $value->id, "mounting_sum" => $value->mounting_sum, "new_mount_sum" => $value->new_mount_sum];
	}
} */
$server_name = $_SERVER['SERVER_NAME'];
?>

<link rel="stylesheet" href="components/com_gm_ceiling/views/team/tmpl/css/style.css" type="text/css" />

<?=parent::getButtonBack();?>

<h2 class="center">Бригада: <?php echo $brigade[0]->name; ?></h2>
<h6 class="center">Телефон: <?php echo $brigade[0]->username; ?>; E-mail: <?php echo $brigade[0]->email; ?></h6>

<div id="content-tar">
	<div id="mounters-container">
		<p><h6>Монтажники:</h6></p>
		<table id="mounters">
			<?php 
				$i = 1;
				foreach ($mounters as $value) {
			?>
				<tr>
					<td>Монтажник <?php echo $i; ?>:</td>
					<td><?php echo $value->name; ?></td>
					<td><?php echo $value->phone; ?></td>
					<td><img src="data:image/png;base64,<?php echo base64_encode($value->pasport); ?>" id="image<?php $value->id ?>" class="passport-image" style="cursor: pointer"></td>
					<td><button type="button" class="btn btn-primary move" id="btn<?php echo $value->id; ?>">Переместить в другую бригаду</button></td>
				</tr>
			<?php 
				$i++;
				}
			?>
		</table>
	</div>
	<div id="modal-window-container-tar">
		<button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
		<div id="modal-window-choose-tar">
			<p id="date-modal"></p>
			<p>Выберите в какую монтажную бригаду перместить монтажника:</p>
			<p>
				<select name="brigades" id='brigades'>
					<?php
						foreach ($AllBrigade as $val) { 
							if ($val->id != $id_brigade) {
					?>
						<option value="<?php echo $val->id ?>"><?php echo $val->name ?></option> 
					<?php }} ?>
				</select>
			</p>
			<p><button type="button" id="save-choise-tar" class="btn btn-primary">Переместить</button></p>
		</div>
	</div>
	<div id="big-image-container">
		<div id="modal-window-container2-tar">
			<button id="close2-tar" class="center" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
			<div id="big-image" class="modal-window-big-image">
				<div id="big-image-shadow" class="big-image-shadow">
					<!--<div style="position:relative;">-->
						<img id="image-big" class="image-big" alt="Скан паспорта">
					<!--</div>-->
				</div>
			</div>
		</div>
	</div>
	<div id="projects-container">
		<p><h6>Проекты бригады:</h6></p>
		<div id="button-research">
			<p>Показать проекты с:  
				<input type="date" id="date1" class="input-date">
				до:  
				<input type="date" id="date2" class="input-date">
				<button type="button" id="show" class="btn btn-primary">Показать</button>
			</p>
			<div id="label-wrong-filter">
				<label id="wrong-filter">Введите две даты</label>
			</div>
		</div>
		<table id="projects">
			<tr>
				<td class="caption">Проект №</td>
				<td class="caption">Сумма монтажа, ₽</td>
				<td class="caption">Выплачено, ₽</td>
			</tr>
				<?php 
					$price1 = 0;
					$price2 = 0; 
					foreach ($ProjectCurrentMonth as $value) { 
						$price1 +=  $value->mounting_sum;
						$price2 += $value->new_mount_sum;
				?>
					<tr onclick="location.replace('http://<?php echo $server_name ?>/index.php?option=com_gm_ceiling&view=projectform&type=gmchief&id=<?php echo $value->id; ?>');">
						<td><?php echo $value->id; ?></td>
						<td><?php echo $value->mounting_sum; ?></td>
						<td><?php if ($value->new_mount_sum == null) { echo 0; } else { echo $value->new_mount_sum; } ?></td>
					</tr>
				<?php } ?>
			<tr>
				<td class="caption">Итого</td>
				<td class="caption"><?php echo $price1; ?></td>
				<td class="caption"><?php echo $price2; ?></td>
			</tr>
		</table>
	</div>
</div>

<script type='text/javascript'>

	// количество дней в месяце
	function getDaysInMonth(month,year)  {
		var daysInMonth=[31,28,31,30,31,30,31,31,30,31,30,31];
		if ((month==1)&&(year%4==0)&&((year%100!=0)||(year%400==0))){
			return 29;
		}else{
			return daysInMonth[month];
		}
	}

	//скрыть модальное окно
    jQuery(document).mouseup(function (e) {
		var div = jQuery("#modal-window-choose-tar");
		if (!div.is(e.target)
		    && div.has(e.target).length === 0) {
			jQuery("#close-tar").hide();
			jQuery("#modal-window-container-tar").hide();
			jQuery("#modal-window-choose-tar").hide();
		}
		var div2 = jQuery("#big-image");
		if (!div2.is(e.target)
		    && div2.has(e.target).length === 0) {
			jQuery("#close2-tar").hide();
			jQuery("#modal-window-container2-tar").hide();
			jQuery("#big-image-shadow").hide();
			jQuery("#big-image").hide();
		}
    });

	// перенаправление на страницу проекта
	function ReplaceToOrder(project) {
		location.href="/index.php?option=com_gm_ceiling&view=projectform&type=chief&id="+project;
	}

	jQuery(document).ready(function () {

		// подставка текущей дат в фильтр
		today = new Date();
		NowYear = today.getFullYear();
		NowMonth = today.getMonth();
		CountDay = getDaysInMonth(NowMonth, NowYear);
		NowMonth++;
		if (String(NowMonth).length == 1) {
			NowMonth = "0"+NowMonth;
		}
		jQuery("#date1").val(NowYear+"-"+NowMonth+"-01");
		jQuery("#date2").val(NowYear+"-"+NowMonth+"-"+CountDay);

		// фильтр для вывода проектов
		jQuery("#show").click(function () {
			datetime1 = jQuery("#date1").val()+" 00:00:00";
			datetime2 = jQuery("#date2").val()+" 23:59:59";
			if (datetime1.length > 9 && datetime2.length > 9) {
				jQuery("#label-wrong-filter").hide();
				jQuery.ajax({
					type: 'POST',
					url: "/index.php?option=com_gm_ceiling&task=team.GetProjectsFilter",
					dataType: 'json',
					data: {
						datetime1: datetime1,
						datetime2: datetime2,
						$id: <?php echo $id_brigade; ?>,
					},
					success: function(data) {
						console.log(data);
						jQuery("#projects").empty();
						var table = '<tr><td class="caption">Проект №</td><td class="caption">Сумма монтажа, ₽</td><td class="caption">Выплачено, ₽</td></tr>';
						var price1 = 0;
						var price2 = 0;
						Array.from(data).forEach(function(element) {
							if (element.new_mount_sum == null) {
								element.new_mount_sum = 0;
							}
							table += '<tr onclick="ReplaceToOrder('+element.id+');"><td>'+element.id+'</td><td>'+element.mounting_sum+'</td><td>'+element.new_mount_sum+'</td></tr>';
							price1 += +element.mounting_sum;
							price2 += +element.new_mount_sum;
						});
						table += '<tr><td class="caption">Итого</td><td class="caption">'+price1+'</td><td class="caption">'+price2+'</td></tr>';
						jQuery("#projects").append(table);
					}
				});
			} else {
				jQuery("#label-wrong-filter").show();
			}
		});

		// нажание на "переместить"
		jQuery("#mounters-container").on("click", ".move", function () {
			var id_btn = this.id;
			window.id_mounter = id_btn.substr(3);
			jQuery("#modal-window-container-tar").show();
			jQuery("#modal-window-choose-tar").show("slow");
			jQuery("#close-tar").show();
		});

		//перемещение
		jQuery("#modal-window-container-tar").on("click", "#save-choise-tar", function () {
			brigade = jQuery("#brigades").val();
			jQuery.ajax({
				type: 'POST',
				url: "/index.php?option=com_gm_ceiling&task=team.MoveBrigade",
				dataType: 'json',
				data: {
					id_mounter: id_mounter,
					brigade: brigade,
					current_brigade: <?php echo $id_brigade; ?>,
				},
				success: function(data) {
					console.log(data);
					jQuery("#mounters").empty();
					if (data != null) {
						var table2 = '';
						var i = 1;
						Array.from(data).forEach(function(element) {
							table2 += "<tr><td>Монтажник "+i+":</td>";
							table2 += "<td>"+element.name+"</td>";
							table2 += "<td>"+element.phone+"</td>";
							table2 += '<td><img src="'+element.passport+'" id="image'+element.id+'" class="passport-image" style="cursor: pointer"></td>';
							table2 += '<td><button type="button" class="btn btn-primary move" id="btn'+element.id+'">Переместить в другую бригаду</button></td>';
							table2 += "</tr>";
							i++;
						});
						jQuery("#mounters").append(table2);
					}
				},
				error: function(data) {
					console.log(data);
				}
			});
			jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
			jQuery("#modal-window-choose-tar").hide();
			// сделать перерисовку бригады
		});

		// увеличение картинки
		jQuery("#mounters").on("click", ".passport-image", function() {
			var src = jQuery(this).attr("src");
			jQuery("#modal-window-container2-tar").show();
			jQuery("#close2-tar").show();
			jQuery("#image-big").attr("src", src);
			jQuery("#big-image-shadow").show("slow");
			jQuery("#big-image").show("slow");
		});

	});

</script>