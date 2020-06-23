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

$user = JFactory::getUser();
$userId = $user->get('id');
$dealerInfo = $user->getDealerInfo();
$user_group = $user->groups;
$_SESSION['user_group'] = $user_group;
$_SESSION['dealer_type'] = $user->dealer_type;

$model = Gm_ceilingHelpersGm_ceiling::getModel('clients');

$userPhone = mb_ereg_replace('[^\d]', '', $userPhone);

$clientId = $model->getItemsByOwnerID($userId, $userPhone);

/* циферки на кнопки */
$model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
// замеры
$sumcalculator = $model->getDataByStatus("GaugingsGraph");
// менеджер
if ($user->dealer_id == 1) {
    $managers = $model->getDataByStatus("FindManagers");
    $managersid = "";
    foreach ($managers as $value) {
        if ($managersid == "") {
            $managersid = "'".$value->id."'";
        } else {
            $managersid .= ", '".$value->id."'";
        }
    }
    // менеджеры проекты запущенные и в производстве
    $answer1 = $model->getDataByStatus("RunInProduction", $managersid);
    // заявки с сайта
    $answer2 = $model->getDataByStatus("ZayavkiSSaita");
    // звонки
    $date = date('Y-m-d');
    $answer3 = $model->getDataByStatus("Zvonki", $date);
    // кол-во
    $sumManager = $answer1[0]->count + $answer2[0]->count + $answer3[0]->count;
}
//НМС /монтажи
$countMounting = $model->getDataByStatus("Mountings");
// незапущенные монтажи
$answer1 = $model->getDataByStatus("UnComplitedMountings");
$allMount = $countMounting[0]->count + $answer1[0]->count;
//--------------------------------------
$stateOfAccountModel =  Gm_ceilingHelpersGm_ceiling::getModel('client_state_of_account');
$rest = $stateOfAccountModel->getStateOfAccount($user->associated_client)->sum;
if(empty($rest)){
    $rest = 0;
}

$managerTitle =  ($userId == 1 || $userId == 2 || $userId == 827)
    ? '<i class="far fa-clock" aria-hidden="true"></i> ГМ Менеджер'
    : '<i class="far fa-clock" aria-hidden="true"></i>Менеджер';
?>
<style>
    .margin_bottom{
        margin-bottom: 15px;
    }
    .btn_width{
        width: 300px !important;
    }
    .row{
        margin-bottom: 1em !important;
    }
</style>
<div class="form-group" style = "text-align: center;">
    <h2 style = "display:inline-block; text-align: center;"><?php echo $user->name; ?></h2>
    <?php if($user->dealer_type!=2 ){ ?>
        </br><a class="btn btn-primary btn-acct" href="/index.php?option=com_gm_ceiling&view=dealerprofile&type=score"> <?=$rest;?> руб. </a>
        <div id="mw_container" class="modal_window_container" >
            <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
            </button>
            <div id="mw_analytic" class="modal_window">
                <div class="row margin_bottom">
                    <a class="btn btn-primary btn_width"  href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=analytics', false); ?>"><i class="fa fa-calculator" aria-hidden="true"></i> Основная и дневная аналитика</a>
                </div>
                <div class="row margin_bottom">
                    <a class="btn btn-primary btn_width"  href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=analytics&type=gaugers', false); ?>"><i class="fas fa-chart-line"></i> Аналитика замерщиков</a>
                </div>
                <div class="row margin_bottom">
                    <a class = "btn btn-primary btn_width" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=calls_analitic',false);?>">Аналитика менеджеров</a>
                </div>
                <?php if($user->dealer_id == 1 && ($user->dealer_type == 0 || $user->dealer_type == 1)){ ?>
                    <div class="row margin_bottom">
                        <a class = "btn btn-primary btn_width" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=analytic_dealers',false)?>"><i class="fas fa-chart-area"></i> Аналитика дилеров</a>
                    </div>
                    <div class="row margin_bottom">
                        <a class="btn btn-primary btn_width" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=analytics&type=visitors',false)?>">Посетители сайта</a>
                    </div>
                <?php }?>
            </div>
        </div>

  <?php  }?>
</div>

<div class="start_page">
    <?php if ($userId == 1 || $userId == 2 || $userId == 827): ?>
        <h3>Гильдия мастеров</h3>
    <?php elseif ($user->delaer_type == 1 || $user->dealer_type == 0): ?>
        <h3>Дилер</h3>
    <?php endif; ?>
    <?php if ($user->dealer_type == 0) { ?>
        <div class="row center">
            <a class="btn btn-large btn-warning" href="<?php
                if ($userId == 1 || $userId == 2 || $userId == 827)
                    echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage', false);
                else echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=managermainpage', false);
                ?>">
                <div style="position: relative">
                    <div>
                        <?=$managerTitle?>
                    </div>
                    <?php if ($sumManager != 0) { ?>
                        <div class="circl-digits"><?php echo $sumManager; ?></div>
                    <?php } ?>
                </div>
            </a>
        </div>

        <div class="row center">
            <a class="btn btn-large btn-success" href="<?php
                if ($userId == 1 || $userId == 2 || $userId == 827 ) {
                    echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=gmcalculatormainpage', false); }
                else { echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=calculatormainpage', false); }
                ?>">
                <div style="position: relative;">
                    <div>
                        <?php if ($userId == 1 || $userId == 2 || $userId == 827): ?>
                            <i class="fa fa-calculator" aria-hidden="true"></i> ГМ Замерщик
                        <?php else: ?>
                            <i class="fa fa-calculator" aria-hidden="true"></i> Замерщик
                        <?php endif; ?>
                    </div>
                    <?php if ($sumcalculator[0]->count != 0) { ?>
                        <div class="circl-digits"><?php echo $sumcalculator[0]->count; ?></div>
                    <?php } ?>
                </div>
            </a>
        </div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php
            if ($userId == 1 || $userId == 2){
                echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=gmchiefmainpage', false);
            }

            else echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=chiefmainpage', false);
            ?>">

                <div style="position: relative;">
                    <div>
                        <?php if ($userId == 1 || $userId == 2 || $userId == 827): ?>
                            <i class="fa fa-user" aria-hidden="true"></i> ГМ Начальник МС
                        <?php else: ?>
                            <i class="fa fa-user" aria-hidden="true"></i> Начальник МС
                        <?php endif; ?>
                    </div>
                    <?php if ($countMounting[0]->count != 0) { ?>
                        <div class="circl-digits"><?php echo $countMounting[0]->count; ?></div>
                    <?php } ?>
                </div>
            </a>
        </div>
        <div class="row center">
            <a class="btn btn-large btn-danger"
               href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&&view=projects&type=buh', false); ?>"><i
                        class="fa fa-list-alt" aria-hidden="true"></i> Бухгалтерия</a>
        </div>
        <div class="row center">
            <a class="btn btn-large btn-danger"
               href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=accountant&subtype=close', false); ?>"><i
                        class="fa fa-list-alt" aria-hidden="true"></i> Договоры</a>
        </div>
        <?php if($userId == 2 || $userId == 1 || $userId == 827 ){?>
            <div class="row center">
                <a class="btn btn-large btn-primary"
                href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=analytics', false); ?>">
                    <i class="fas fa-chart-line"></i> Аналитика</a>
            </div>
            <div class="row center">
                <a class="btn btn-large btn-primary"
                href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=api_phones', false); ?>"><i
                            class="fa fa-mobile" aria-hidden="true"></i> Телефоны</a>
            </div>
            <div class="row center">
                <a class="btn btn-large btn-primary"
                   href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock', false); ?>"><i
                            class="fa fa-mobile" aria-hidden="true"></i> Склад</a>
            </div>
        <?php }?>
    <?php } elseif ($user->dealer_type == 1) { ?>
        <div class="row center">
            <button class="btn btn-large btn-primary" id="precalc_btn" ><i class="fas fa-edit" aria-hidden="true"></i>Рассчитать</button>
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
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=calendar', false); ?>">
                <div style="position:relative;">
                    <div>
                        <i class="fa fa-calculator" aria-hidden="true"></i> Замеры
                    </div>
                    <?php if ($sumcalculator[0]->count != 0) { ?>
                        <div class="circl-digits"><?php echo $sumcalculator[0]->count; ?></div>
                    <?php } ?>
                </div>
            </a>
        </div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=clients', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Клиенты</a>
        </div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=chiefmainpage', false); ?>">
                <div style="position: relative">
                    <div>
                        <i class="fa fa-gavel" aria-hidden="true"></i> Договоры/Монтажи
                    </div>
                    <?php if ($allMount != 0) { ?>
                        <div class="circl-digits"><?php echo $allMount; ?></div>
                    <?php } ?>
                </div>
            </a>
        </div>
        <div class="row center">
            <button class="btn btn-large btn-primary" id="show_analytic"><i class="fas fa-chart-line"></i> Аналитика</button>
        </div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=search', false); ?>"><i class="fa fa-search"></i> Поиск</a>
        </div>
         
    <?php } elseif ($user->dealer_type == 2) { ?>
        <div class="row center">
            <button class="btn btn-large btn-primary" id="create_order_btn">
                <i class="fa fa-list-alt" aria-hidden="true"></i> Создать заказ
            </button>
        </div>
        <div class="row center">
            <button class="btn btn-large btn-primary" id="prev_orders_btn">
                <i class="fa fa-list-alt" aria-hidden="true"></i> Ранее заказанные
            </button>
        </div>
    <?php } ?>
</div>

<script>

    jQuery(document).ready(function () {

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

        jQuery("#show_additional").click(function () {
            jQuery("#montages_btn").toggle();
            jQuery("#accounting_btn").toggle();
            jQuery("#contracts_btn").toggle();
            jQuery("#clients_btn").toggle();
            jQuery("#refused_btn").toggle();

        });
        jQuery("#msrmnt_btn").click(function () {
            jQuery("#new_msrmnt_btn").toggle();
            jQuery("#exist_msrmnt_btn").toggle();
        });
        jQuery("#prices_btn").click(function () {
            jQuery("#canvases_price_btn").toggle();
            jQuery("#components_price_btn").toggle();
            jQuery("#mounting_price_btn").toggle();
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
        jQuery("#precalc_btn").click(function () {
            user_id = "<?php echo $userId;?>";
            create_new_client(user_id);
        });
        jQuery("#toProfile").click(function(){
		    location.href = "index.php?option=com_gm_ceiling&view=dealerprofile&type=edit";
	    });
        jQuery("#new_msrmnt_btn").click(function () {
            location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=addproject&type=calculator', false); ?>";
        });
        jQuery("#exist_msrmnt_btn").click(function () {
            location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=calendar', false); ?>";
        });
        jQuery("#montages_btn").click(function () {
            location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=chief', false); ?>";
        });
        jQuery("#contracts_btn").click(function () {
            location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=dealer', false); ?>";
        });
        jQuery("#accounting_btn").click(function () {
            location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=dealer&subtype=buh', false); ?>";
        });
        jQuery("#clients_btn").click(function () {
            location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=clients', false); ?>";
        });
        jQuery("#refused_btn").click(function () {
            location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=refused', false); ?>";
        });
        jQuery("#canvases_price_btn").click(function () {
            location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=canvases', false, 2); ?>";
        });
        jQuery("#components_price_btn").click(function () {
            location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=components', false, 2); ?>";
        });
        jQuery("#mounting_price_btn").click(function () {
            location.href = "<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=mount', false, 2); ?>";
        });

        jQuery("#prev_orders_btn").click(function () {
            location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=chief', false); ?>";
        });

        jQuery('#show_analytic').click(function () {
            jQuery('#mw_container').show();
            jQuery('#close').show();
            jQuery("#mw_analytic").show();
        });
    })
    jQuery("#create_order_btn").click(function () {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=create_empty_project",
            data: {
                client_id: "<?php echo $clientId?>",
                owner: "<?php echo $userId?>"
            },
            success: function (data) {
                url = '/index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=' + data;
                console.log(data);
                location.href = url;// "<?php echo JRoute::_(url1, false); ?>";
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
    });

    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#mw_analytic"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery(".close_btn").hide();
            jQuery("#mw_container").hide();
            jQuery(".modal_window").hide();
        }
    });
</script>