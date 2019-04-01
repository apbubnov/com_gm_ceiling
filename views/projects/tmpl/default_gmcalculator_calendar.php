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
$jinput = JFactory::getApplication()->input;
$type = $jinput->get('type', '', 'STRING');
$subtype = $jinput->get('subtype', '', 'STRING');
?>
<?=parent::getButtonBack();?>

<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=calendar'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row" style="margin-top: 5px">
        <div class="col-md-4 col-xs-6">
            <a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=addproject&type=gmcalculator', false, 2); ?>" class="btn btn-primary btn-md">Добавить<br>замер
            </a>
        </div>
        <div class="col-md-8 col-xs-6 right">
            <b>Выбрать с: </b> <input type="date" id="measure_from" class="input-gm" value="<?= date('Y-m-d');?>">
            <b>по: </b> <input type="date" id="measure_to" class="input-gm" value="<?= date('Y-m-d');?>">
        </div>
    </div>
    <h4 class="center" >График замеров <span id="measure_preiod">на <?= date('d.m.Y')?></span></h4>
    <table class="table table-striped one-touch-view g_table" id="projectList">
        <thead>
        <tr>
            <th class='center' id="project_id">
                №
            </th>
            <th class='center'>
                Время
            </th>
            <th class='center'>
                Адрес
            </th>
            <th>
                Примечание
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
        <?php foreach ($this->items as $i => $item) : ?>
            <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id='.(int) $item->id); ?>">
                <td class="center one-touch">
                    <?php echo $item->id; ?>
                </td>
                <td class="center one-touch">
                    <?php echo $item->calculation_time; ?>
                </td>
                <td class="center one-touch">
                    <?php echo $this->escape($item->project_info); ?>
                </td>
                <td>
                    <?php
                        $project_notes = Gm_ceilingHelpersGm_ceiling::getProjectNotes($item->id,2);
                        foreach ($project_notes as $note){
                            echo $note->description.$note->value."<br>";
                        }
                    ?>
                </td>
                <td class="center one-touch">
                    <?php echo $item->client_name; ?><br><?php echo $item->client_contacts; ?>
                </td>
                <td class="center one-touch">
                    <?php echo $item->dealer_name; ?>
                </td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>
    <?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">

    var $ = jQuery;

    jQuery(document).ready(function () {
        $(window).resize(Resize);
        jQuery('.delete-button').click(deleteItem);
        Resize();

        jQuery("#measure_from").change(function () {
            var dateFrom = this.value,
                dateTo = jQuery("#measure_to").val();
            if(dateFrom<=dateTo){
                getMeasuresByPeriod(dateFrom,dateTo);
            }
            else{
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Начальная дата не может быть больше конечной!"
                });
            }
        });

        jQuery("#measure_to").change(function () {
            var dateFrom = jQuery("#measure_from").val(),
                dateTo = this.value;
            if(dateFrom<=dateTo){
                getMeasuresByPeriod(dateFrom,dateTo);
            }
            else{
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Начальная дата не может быть больше конечной!"
                });
            }
        });
    });

    function getMeasuresByPeriod(dateFrom,dateTo) {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=projects.getMeasures",
            data: {
                dateFrom:dateFrom,
                dateTo:dateTo,
                type: '<?php echo $type;?>',
                subtype: '<?php echo $subtype; ?>'
            },
            dataType: "json",
            async: true,
            success: function (data) {
                console.log(data);
                fillProjectsTable(data);

            },
            error: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка вывода примечаний"
                });
            }
        });
    }

    function OpenPage() {
        var e = jQuery("[data-href]");
        jQuery.each(e, function (i, v) {
            jQuery(v).click(function () {
                document.location.href = this.dataset.href;
            });
        });
    }

    function fillProjectsTable(projects){
        var dateFrom = jQuery("#measure_from").val(),
            dateTo = jQuery("#measure_to").val();
        if(dateFrom != dateTo) {
            jQuery("#project_id").after('<th>Дата</th>');
            jQuery("#measure_preiod").empty();
            jQuery("#measure_preiod")[0].innerHTML = 'c '+changeDateFormat(dateFrom)+' по '+changeDateFormat(dateTo);
        }
        else{
            jQuery("#measure_preiod").empty();
            jQuery("#measure_preiod")[0].innerHTML = 'на '+changeDateFormat(dateFrom);
        }
        jQuery("#projectList > tbody").empty();
        jQuery.each(projects,function (index,elem) {
            jQuery("#projectList > tbody").append('<tr data-href="/index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id='+elem.id+'"></tr>');
            var client_contacts = !empty(elem.client_contacts) ? elem.client_contacts : "";
            var noteStr = "";
            jQuery.each(elem.note,function(n,note){
                noteStr += note.description+note.value;
            });
            jQuery("#projectList > tbody >tr:last").append( '<td>'+elem.id+'</td>' +
                                                            '<td>'+changeDateFormat(elem.calculation_date)+'</td>' +
                                                            '<td>'+elem.calculation_time+'</td>' +
                                                            '<td>'+elem.project_info+'</td>' +
                                                            '<td>'+noteStr+'</td>' +
                                                            '<td>'+elem.client_name+'<br>'+client_contacts+'</td>'+
                                                            '<td>'+elem.dealer_name+'</td>');
        });

        OpenPage();

    }
    function changeDateFormat(date){
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [day,month,year].join('.');
    }
    function deleteItem() {

        if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
            return false;
        }
    }
    
    function Resize() {
        reduceGTable();
    }

    // вызовем событие resize
    $(window).resize();
</script>
