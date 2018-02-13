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
$user_group = $user->groups;
$dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
$dop_num = $dop_num_model->getData($userId)->dop_number;
$_SESSION['user_group'] = $user_group;
$_SESSION['dop_num'] = $dop_num;
?>

<style>
	.columns-tar {
		display: inline-block;
		float: left;
		width: 100%;
		text-align: center;
	}

	@media screen and (min-width: 992px) {
		.columns-tar{
			width: calc(100% / 3 - 5px);
		}
	}
</style>

<h2 class="center">Менеджер ГМ</h2>

<div class="start_page">
	<div class="columns-tar">
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=clients&type=manager', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Клиенты</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=dealers', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Дилеры</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=recoil', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Откатники</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=designers', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Отделочники</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=cashbox', false); ?>"><i class="fa fa-usd" aria-hidden="true"></i> Касса</a>
		</p>
		<!-- <p>
			<a class="btn btn-large btn-primary" href="<?php //echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=clientorders', false); ?>"><i class="fa fa-shopping-cart" aria-hidden="true"></i> Клиентские заказы</a>
		</p> -->
	</div>
	<div class="columns-tar">
		<div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager', false); ?>"><i class="fa fa-clock-o" aria-hidden="true"></i> В производстве </a>
					<div class="circl-digits" id="InProductionDiv" style="display: none;"></div>
			</div>
		</div>
		<div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=runprojects', false); ?>"><i class="fa fa-cogs" aria-hidden="true"></i> Запущенные </a>
					<div class="circl-digits" id="ZapushennieDiv" style="display: none;"></div>
			</div>
		</div>
		<div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=requestfrompromo', false); ?>"><i class="fa fa-bookmark" aria-hidden="true"></i></i> Заявки с сайта </a>
					<div class="circl-digits" id="ZayavkiSSaitaDiv" style="display: none;"></div>
			</div>
		</div>
		<div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>"><i class="fa fa-phone-square" aria-hidden="true"></i> Звонки </a>
					<div class="circl-digits" id="ZvonkiDiv" style="display: none;"></div>
			</div>
		</div>
		<div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=missed_calls', false); ?>"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Пропущенные</a>
					<div class="circl-digits" id="MissedCallsDiv" style="display: none;"></div>
			</div>
		</div>
	</div>
	<div class="columns-tar">
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=archive', false); ?>"><i class="fa fa-archive" aria-hidden="true"></i> Архив</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=refused', false); ?>"><i class="fa fa-times" aria-hidden="true"></i> Отказы</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=reservecalculation&type=gmmanager&subtype=activatedprojects', false); ?>"><i class="fa fa-pencil" aria-hidden="true"></i> Запись на замер</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=prices', false); ?>"><i class="fa fa-rub" aria-hidden="true"></i> Прайсы</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=colors', false); ?>"><i class="fa fa-eyedropper" aria-hidden="true"></i> Цвета полотен</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=search', false); ?>"><i class="fa fa-search"></i> Поиск</a>
		</p>
	</div>
</div>

<script type="text/javascript">
	jQuery('document').ready(function(){
		//в производстве
		jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=printInProductionOnGmMainPage",
            async: true,
            success: function(data){
                if (data != null)
                {
                	if (data[0].count != 0)
                	{
                		document.getElementById('InProductionDiv').innerHTML = data[0].count;
                		document.getElementById('InProductionDiv').style.display = 'block';
                	}
                }
            },
            dataType: "json",
            timeout: 30000,
            error: function(data){
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
		//запущенные
		jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=printZapushennieOnGmMainPage",
            async: true,
            success: function(data){
                if (data != null)
                {
                	if (data[0].count != 0)
                	{
                		document.getElementById('ZapushennieDiv').innerHTML = data[0].count;
                		document.getElementById('ZapushennieDiv').style.display = 'block';
                	}
                }
            },
            dataType: "json",
            timeout: 30000,
            error: function(data){
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
		//заявки с сайта
		jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=printZayavkiSSaitaOnGmMainPage",
            async: true,
            success: function(data){
                if (data != null)
                {
                	if (data[0].count != 0)
                	{
                		document.getElementById('ZayavkiSSaitaDiv').innerHTML = data[0].count;
                		document.getElementById('ZayavkiSSaitaDiv').style.display = 'block';
                	}
                }
            },
            dataType: "json",
            timeout: 30000,
            error: function(data){
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
		//звонки
		jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=printZvonkiOnGmMainPage",
            async: true,
            success: function(data){
                if (data != null)
                {
                	if (data[0].count != 0)
                	{
                		document.getElementById('ZvonkiDiv').innerHTML = data[0].count;
                		document.getElementById('ZvonkiDiv').style.display = 'block';
                	}
                }
            },
            dataType: "json",
            timeout: 30000,
            error: function(data){
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
        //пропущенные
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=printMissedCallsOnGmMainPage",
            async: true,
            success: function(data){
                if (data != 0)
                {
                	document.getElementById('MissedCallsDiv').innerHTML = data;
                	document.getElementById('MissedCallsDiv').style.display = 'block';
                }
            },
            dataType: "json",
            timeout: 30000,
            error: function(data){
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
	});
</script>
