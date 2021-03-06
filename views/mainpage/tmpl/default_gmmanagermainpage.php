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
    .row{
        margin-bottom: 1em !important;
    }
</style>

<h2 class="center">Менеджер ГМ</h2>

<div class="start_page">
	<div class="columns-tar">
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=clients', false); ?>"><i class="fas fa-users" aria-hidden="true"></i> Клиенты</a>
		</div>
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=dealers', false); ?>"><i class="fas fa-user-tie"></i> Дилеры</a>
		</div>
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=clients&type=partners', false); ?>"><i class="fas fa-user-friends"></i> Клиенты партнеров</a>
		</div>
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=designers', false); ?>"><i class="fas fa-user-injured"></i></i> Отделочники</a>
		</div>
        <!-- <p class="center">
            <a class="btn btn-large btn-primary" href="<?php //echo JRoute::_('/index.php?option=com_gm_ceiling&view=manufacturers', false); ?>"><i class="fa fa-list-alt" aria-hidden="true"></i> Производители</a>
        </p> -->
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=builders', false); ?>"><i class="fa fa-building" aria-hidden="true"></i> Застройщики</a>
		</div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=wininstallers', false); ?>"><i class="fab fa-windows" aria-hidden="true"></i> Оконщики</a>
        </div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=analytic_dealers&type=new', false); ?>">Аналитика по дилерам</a>
        </div>
	</div>
	<div class="columns-tar">
		<div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager', false); ?>">
                <div style="position:relative;">
                    <div>
                        <i class="fas fa-clock" aria-hidden="true"></i> В производстве
                    </div>
                    <div class="circl-digits" id="InProductionDiv" style="display: none;"></div>
                </div>
            </a>
		</div>
		<div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=runprojects', false); ?>">
               <div style="position:relative;">
                   <div>
                       <i class="fa fa-cogs" aria-hidden="true"></i> Запущенные
                   </div>
                   <div class="circl-digits" id="ZapushennieDiv" style="display: none;"></div>
               </div>
            </a>
		</div>
		<div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>">
                <div style="position:relative;">
                    <div>
                        <i class="fa fa-phone-square" aria-hidden="true"></i> Звонки
                    </div>
                    <div class="circl-digits" id="ZvonkiDiv" style="display: none;"></div>
                </div>
            </a>
		</div>
		<div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=missed_calls', false); ?>">
                <div style="position:relative;">
                    <div>
                        <i class="fa fa-exclamation-circle" aria-hidden="true"></i> Пропущенные
                    </div>
                    <div class="circl-digits" id="MissedCallsDiv" style="display: none;"></div>
                </div>
            </a>
		</div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=cashbox', false); ?>"><i class="fas fa-dollar-sign"></i> Касса</a>
        </div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=teams&type=gmchief', false); ?>"><i class="fas fa-hammer"></i> Монтажи</a>
        </div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=measures', false); ?>"><i class="fas fa-ruler"></i> Замеры</a>
        </div>
	</div>
	<div class="columns-tar">
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=archive', false); ?>"><i class="fa fa-archive" aria-hidden="true"></i> Архив</a>
		</div>
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=refused', false); ?>"><i class="fa fa-times" aria-hidden="true"></i> Отказы</a>
		</div>
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=reservecalculation&type=gmmanager&subtype=activatedprojects', false); ?>"><i class="fas fa-pencil-ruler"></i> Запись на замер</a>
		</div>
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=price&type=goods', false); ?>"><i class="fas fa-ruble-sign"></i> Прайс</a>
		</div>
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=colors', false); ?>"><i class="fas fa-eye-dropper"></i> Цвета, фактуры</a>
		</div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=search', false); ?>"><i class="fa fa-search"></i> Поиск</a>
        </div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock', false); ?>"><i class="fas fa-warehouse"></i> На склад</a>
        </div>
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
