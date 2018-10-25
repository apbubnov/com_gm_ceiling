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
    "use strict";
    var data = JSON.parse('<?php echo $data;?>')
    /*
    * type == 0 - вычитание количества
    * type == 1 - прибавка количества
    */
    function changeComponentCount(elem, type){
        let row =  jQuery(elem.closest('.row')),
            count_sale = row.data('count_sale'),
            input_count = row.find('.count'),
            table_row = jQuery(row.closest('tr')),
            component_id = table_row.data('component_id'),
            count;
        if(type == 0 ) {
            count = (input_count.val() >= 0) ? input_count.val() : data[component_id].quantity;
            count -= (count > 0) ? count_sale : 0;
        }
        if(type == 1){
            count = +input_count.val();
            count += +count_sale;
        }
        data[component_id].quantity = count;
        input_count.val(count);

    }

    function changeProjectTotalSum(total_price_old, total_price_new) {
        let total_sum_td = jQuery("#materials > tbody tr:last td:last-child"),
            total_sum = total_sum_td.text();
        total_sum -= total_price_old;
        total_sum += +total_price_new;
        total_sum_td.text(total_sum);
    }
    /*
    * type == 0 - уменьшение стоимости
    * type == 1 - увелечение стоимости
    */
    function changeComponentTotalPrice(elem, type){
        let row =  jQuery(elem.closest('.row')),
            table_row = jQuery(row.closest('tr')),
            count_sale = row.data('count_sale'),
            price = +table_row.find('.price').text(),
            total_price_td = row.closest('tr').find('.total_price'),
            total_price_old = +total_price_td.text(),
            component_id = table_row.data('component_id'),
            total_price = total_price_old;

        if(type ==0){
            total_price -= (total_price>0) ? count_sale*price : 0;
        }
        if(type == 1){
            total_price += count_sale*price;
        }
        data[component_id].self_total = total_price;
        total_price_td.text(total_price.toFixed(2));
        changeProjectTotalSum(total_price_old,total_price);
    }
	jQuery(document).ready(function(){
		var BTN_MINUS_COUNT = '<button  class="btn btn-primary btn-sm minus"><i class="fa fa-minus-square" aria-hidden="true"></i></button>',
            BTN_PLUS_COUNT = '<button class="btn btn-primary btn-sm plus"><i class="fa fa-plus-square" aria-hidden="true"></i></button>',
            BTN_REMOVE = '<button class="btn btn-sm btn-danger remove"><i class="fa fa-trash" aria-hidden="true"></i></button>';
		var total_sum = 0;

		jQuery("#materials > tbody").empty();
		jQuery.each(data,function(index,element){
			jQuery("#materials > tbody").append('<tr data-component_id = "'+element.id+'"></tr>');
			let count = `<div class="row" data-count_sale="${element.count_sale}">
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
			total_sum += element.self_total;
			jQuery("#materials > tbody tr:last").append('<td>'+element.title+'</td><td>'+element.unit+'</td><td class="price">'+element.self_dealer_price+'</td><td>'+count+'</td><td class="total_price">'+element.self_total+'</td><td>'+BTN_REMOVE+'</td>')
		});
        jQuery("#materials > tbody").append('<tr><td colspan="4"><b>Итого:</b></td><td colspan="2">'+total_sum.toFixed(2)+'</td></tr>');

		jQuery(".minus").click(function(){
            changeComponentCount(this,0);
            changeComponentTotalPrice(this,0);
            console.log(data);
        });

        jQuery(".plus").click(function(){
            changeComponentCount(this,1);
            changeComponentTotalPrice(this,1);
        });

        jQuery('.count').keypress(function(eve) {
            if ((eve.which != 46 || jQuery(this).val().indexOf('.') != -1) && (eve.which < 48 || eve.which > 57) || (eve.which == 46 &&
                jQuery(this).caret().start == 0)) {
                eve.preventDefault();
            }
        });

        jQuery('.count').keyup(function () {
           let count = this.value,
               row =  jQuery(this.closest('.row')),
               count_sale = row.data('count_sale');
           if(count%count_sale){
               noty({
                   timeout: 2000,
                   theme: 'relax',
                   layout: 'center',
                   maxVisible: 5,
                   type: "error",
                   text: "Ошибка! Значение должно быть кратно " + count_sale
               });
            }
           else{
               let price = +row.closest('tr').find('.price').text(),
                   total_price_td = row.closest('tr').find('.total_price'),
                   total_price = count*price;
               total_price_td.text(total_price.toFixed(2));
               changeProjectTotalSum(price,total_price);
           }
        });

        jQuery('.remove').click(function(){
            let row = jQuery(this.closest('tr')),
                component_id =  row.data('component_id'),
                total_price = row.find('.total_price').text(),
                total_sum_td = jQuery("#materials > tbody tr:last td:last-child"),
                total_sum = total_sum_td.text();

            total_sum_td.text((total_sum-total_price).toFixed(2));
            delete data[component_id];
            console.log(data);
            row.hide();
        })
	});
</script>