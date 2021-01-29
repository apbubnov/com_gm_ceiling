<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user       = JFactory::getUser();
$userId     = $user->get('id');

?>
<style>
    .row{
        margin-bottom: 1em !important;
    }
</style>
<?=parent::getButtonBack();?>
<h2 class="center">Менеджер</h2>

<div class="start_page">
    <div class="row center">
        <button class="btn btn-large btn-primary" id="precalc_btn" ><i class="fas fa-edit" aria-hidden="true"></i>Рассчитать</button>
    </div>
	<div class="row center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=manager&subtype=refused', false); ?>"><i class="fa fa-times" aria-hidden="true"></i> Отказы</a>
	</div>
	<div class="row center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=clients', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Клиенты</a>
	</div>
	<div class="row center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>">
            <div style="position:relative;">
                <div>
                    <i class="fa fa-phone-square" aria-hidden="true"></i> Перезвоны
                </div>
                <div class="circl-digits" id="ZvonkiDiv" style="display: none;"></div>
            </div>
        </a>
	</div>
    <div class="row center">
	    <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=chiefmainpage', false); ?>"><i class="fa fa-gavel" aria-hidden="true"></i> Монтажи</a>
    </div>
	<div class="row center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=addproject&type=manager', false); ?>"><i class="fas fa-pen" aria-hidden="true"></i> Запись на замер</a>
	</div>
	<div class="row center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=prices', false); ?>"><i class="fa fa-rub" aria-hidden="true"></i> Прайсы</a>
	</div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        localStorage.clear();
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=printZvonkiOnGmMainPage",
            async: true,
            success: function (data) {
                if (data != null) {
                    if (data[0].count != 0) {
                        document.getElementById('ZvonkiDiv').innerHTML = data[0].count;
                        document.getElementById('ZvonkiDiv').style.display = 'block';
                    }
                }
            },
            dataType: "json",
            timeout: 30000,
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Сервер не отвечает."
                });
            }
        });

        jQuery("#precalc_btn").click(function () {
            user_id = "<?php echo $userId;?>";
            create_new_client(user_id);
        });
    });

    function create_precalculation(proj_id)
    {
        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=calculation.create_calculation",
            data: {
                proj_id: proj_id
            },
            success: function(data){
                console.log(data);
                location.href = '/index.php?option=com_gm_ceiling&view=calculationform&type=calculator&subtype=precalc&calc_id='+data+'&precalculation=1';
            },
            error: function(data){
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера."
                });
            }
        });
    }

    function create_project(client_id){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=create_empty_project",
            data: {
                client_id: client_id
            },
            success: function (data) {
                create_precalculation(data);
            },
            dataType: "text",
            timeout: 10000,
            error: function (data) {
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при создании заказа. Сервер не отвечает"
                });
            }
        });
    }
    function create_new_client(id){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=client.create",
            data: {
                user_id: id
            },
            success: function (data) {
                create_project(data);
            },
            dataType: "text",
            timeout: 10000,
            async: false,
            error: function (data) {
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при создании. Сервер не отвечает"
                });
            }
        });
    }
</script>