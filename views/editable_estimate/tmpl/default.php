<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2017 al.p.bubnov@gmail.com>
 * @copyright  2017 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$user       = JFactory::getUser();
$userId     = $user->get('id');
$project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
$data = json_encode($project_model->getMaterialsForEstimate(221));
?>
<div class="container">
	<div class="row center">
		<div class="col-md-12">
			<table id = "materials" class="table table_cashbox">
				<thead>
					<th width="45%">
						Наименование
					</th>
					<th width="5%">
						Ед.изм-я
					</th>
					<th width="10%">
						Цена
					</th>
					<th width="20%">
						Кол-во
					</th>
					<th width="15%">
						Стоиомсть
					</th>
					<th width="5%">
						<i class="fa fa-trash" aria-hidden="true"></i>
					</th>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	
</div>
<script type="text/javascript">
	jQuery(document).ready(function(){
		var BTN_MINUS_COUNT = '<button  class="btn btn-primary btn-sm minus"><i class="fa fa-minus-square" aria-hidden="true"></i></button>',BTN_PLUS_COUNT = '<button class="btn btn-primary btn-sm plus"><i class="fa fa-plus-square" aria-hidden="true"></i></button>',BTN_REMOVE = '<button class="btn btn-sm btn-danger remove"><i class="fa fa-trash" aria-hidden="true"></i></button>';
		var data = JSON.parse('<?php echo $data;?>');
		console.log(data);
		jQuery("#materials > tbody").empty();
		jQuery.each(data,function(index,element){
			jQuery("#materials > tbody").append('<tr></tr>');
			let count = `<div class="row">
							<div class="col-md-4 left">
								${BTN_MINUS_COUNT}
							</div>
							<div class="col-md-4 center">
								<input class="inputactive count" value = "${element.quantity}">
							</div>
							<div class="col-md-4 right">
								${BTN_PLUS_COUNT}
							</div>
						</div>`;
			jQuery("#materials > tbody tr:last").append('<td>'+element.title+'</td><td>'+element.unit+'</td><td>'+element.self_dealer_price+'</td><td>'+count+'</td><td>'+element.self_total+'</td><td>'+BTN_REMOVE+'</td>')
		});
	});
</script>