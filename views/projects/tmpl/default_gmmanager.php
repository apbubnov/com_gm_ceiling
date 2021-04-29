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

?>
<button class="btn btn-primary" id="btn_back"><i class="fa fa-arrow-left" aria-hidden="true"></i>Назад</button>
<h2 class = "center">Запущенные в производство</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanager'); ?>" method="post"
      name="adminForm" id="adminForm">
	 <div class="row">
         <div class="col-md-8 col-xs-6 right" >
             <div class="col-md-2" style="line-height: 2em;">
                 Запущено с
             </div>
             <div class="col-md-4">
                 <input class="form-control" id = "run_date_from" type="date">
             </div>
             <div class="col-md-2" style="line-height: 2em;">
                 по
             </div>
             <div class="col-md-4">
                 <input class="form-control" id = "run_date_to" type="date">
             </div>
         </div>
         <div class="col-md-3 col-xs-9">
             <input type="text" id="search_text" class="form-control">
         </div>
         <div class="col-md-1 col-xs-3" style="padding: 0px;">
             <button type="button" class="btn btn-primary" id="search_btn"><b class="fa fa-search"></b></button>
         </div>
     </div>
	<table class="table table-striped one-touch-view g_table" id="projectList">
		<thead>
			<tr>
				<th class='center'>
					Номер договора
				</th>

				<th class='center'>
					Дата и время начала монтажа
				</th>
				<th class='center'>
					Адрес
				</th>
				<th class='center'>
					Примечание
				</th>
				<th class='center'>
					Телефоны
				</th>
				<th class='center'>
					Клиент
				</th>
				<th class="center">
					Дилер
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $item):?>
				<tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&id='.(int) $item->id); ?>">
					<td class="center one-touch">
						<?php echo $item->id; ?>
					</td>
					<td class="center one-touch">
						<?php if(empty($item->project_mounting_date)) {
                            $item->project_mounting_date = "-"?>
							-
						<?php } else { ?>
							<?php echo str_replace(',', '<br>', $item->project_mounting_date)   ?>
							
						<?php } ?>
					</td>
					<td class="center one-touch">
						<?php echo $this->escape($item->project_info); ?>
					</td>
					<td class="center one-touch">
                        <?php
                            $noteStr = "";
                            $project_notes = Gm_ceilingHelpersGm_ceiling::getProjectNotes($item->id,4);
                            foreach ($project_notes as $note){
                                $noteStr .=  $note->description.$note->value.';';
                                echo $note->description.$note->value."<br>";
                            }
                            $item->note = $noteStr;
                        ?>
					</td>
					<td class="center one-touch">
                        <?php if(empty($item->client_contacts)){
                            $item->client_contacts = "-";
                        }?>
						<?php echo $item->client_contacts; ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_name; ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->dealer_name; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>


	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php
    $user       = JFactory::getUser();
    $userId     = $user->get('id');
    $user_group = $user->groups;
?>


<script type="text/javascript">
    var items_json = '<?= quotemeta(json_encode($this->items));?>'.replace(/null/i, "\"\"");
    var items = JSON.parse(items_json);
    console.log(items);
    jQuery(document).keypress(
        function(event){
            if (event.which == '13') {
                event.preventDefault();
            }
        });
	jQuery(document).ready(function () {
		jQuery('#btn_back').click(function(){
                location.href = "/index.php?option=com_gm_ceiling&task=mainpage";
            });
		jQuery('.delete-button').click(deleteItem);

		jQuery("#search_btn").click(function () {
            var search = jQuery("#search_text").val();
            var date_from = jQuery('#run_date_from').val(),
                date_to = jQuery('#run_date_to').val();
            console.log(date_from);
            showFiltered(search,date_from,date_to);
        });

        function OpenPage() {
            var e = jQuery("[data-href]");
            jQuery.each(e, function (i, v) {
                jQuery(v).click(function () {
                    document.location.href = this.dataset.href;
                });
            });
        }
        function showFiltered(searchText,dateFrom,dateTo){
            jQuery("#projectList > tbody").empty();
            var search_reg = new RegExp(searchText, "ig");
            jQuery.each(items,function (index,elem) {
                var status_history = [];
                if(!empty(elem.project_status_history)&&!empty(dateFrom)) {
                    status_history  = JSON.parse(elem.project_status_history.replace(/\\/g, ""));
                }
                var  existDate = true,
                     existText = true;
                if(!empty(searchText)){
                    console.log("text");
                    existText = search_reg.test(elem.client_name)||search_reg.test(elem.dealer_name)||search_reg.test(elem.id)||
                    search_reg.test(elem.project_info);
                }
                console.log(dateFrom);
                if(!empty(dateFrom) || !empty(dateTo)){
                    existDate = status_history.find(function (status) {
                        if(!empty(dateFrom) && !empty(dateTo) && status.status == 5 && status.date >= dateFrom && status.date<=dateTo){
                            return true;
                        }
                        if(!empty(dateFrom) && empty(dateTo) && status.status == 5 && status.date>= dateFrom){
                            return true;
                        }
                        if(empty(dateFrom) && !empty(dateTo) && status.status == 5 && status.date<=dateTo){
                            return true;
                        }
                    });

                    if(existDate === undefined){
                        existDate = false;
                    }
                }

                if(existDate && existText){
                   console.log("go")
                    jQuery("#projectList > tbody").append('<tr></tr>');
                    jQuery("#projectList > tbody > tr:last").attr("data-href", "/index.php?option=com_gm_ceiling&view=project&type=gmmanager&id="+elem.id);
                    jQuery("#projectList > tbody > tr:last").append('<td>'+elem.id+'</td>' +
                        '<td>'+elem.project_mounting_date+'</td><td>'+elem.project_info+'</td><td>'+elem.note+'</td>' +
                        '<td>'+elem.client_contacts+'</td><td>'+elem.client_name+'</td><td>'+elem.dealer_name+'</td>');
                }

            });
            OpenPage();
        }
        jQuery("#search_text").keyup(function (event) {
            if(event.keyCode == 13){
                jQuery("#search_btn").click();
            }
        });

        jQuery("#run_date").keyup(function(){
            var date = jQuery("#run_date").val();
            var search = jQuery("#search_text").val();
            if(date.replaceAll('_',"").length > 9){
                showFiltered(search,format_date(date));
            }
            if(date.replaceAll('_',"").replaceAll('.',"").length == 0){
                showFiltered(search,"");
            }
        });
	});

    function format_date(date) {
       var date_arr = date.split('.'),
           year = date_arr[2],
           month = date_arr[1],
           day = date_arr[0];
        return (!empty(year)&&!empty(month)&&!empty(day)) ? [year, month, day].join('-'):"";
    }
	function deleteItem() {

		if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}
    String.prototype.replaceAll = function(search, replace){
        return this.split(search).join(replace);
    }
</script>
