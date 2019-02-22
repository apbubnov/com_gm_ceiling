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
    $date = date("Y")."-".date("n")."-".date("d");
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
$recoil_map_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
$data = $recoil_map_model->getData($userId);
$total_sum = 0;// общая сумма потолка
$contributed = 0;//Внесенная сумма
$rest = 0;//Сумма долга или Остаток
foreach ($data as $item) {
    if($item->sum < 0) $total_sum+=$item->sum;
    else $contributed+=$item->sum;
}
$rest = -($total_sum) - $contributed;

?>

<?php if(!$dealerInfo->update_check):?>
<!-- <style>
    #toProfile {
        position: relative;
        width: 52px;
        height: 38px;
        display: inline-block;
    }
    #toProfile .pix {
        position: absolute;
        right: -6px;
        top: -6px;
        width: 12px;
        height: 12px;
        border-radius: 6px;
        background-color: rgb(255,0,0);
        box-shadow: 1px 1px 1px 0 rgba(0,0,0,.5), inset 1px 1px 1px 0 rgba(255,255,255,.5), inset -.5px -.5px 1px 0 rgba(0,0,0,.5);
    }
    #toProfile .message {
        position: absolute;
        left: 58px;
        top: 0;
        border-radius: .25rem;
        height: 38px;
        line-height: 38px;
        width: auto;
        padding: 0 10px;
        background-color: rgb(65,64,153);
        color: rgb(255,255,255);
        display: none;
    }
    #toProfile:hover .message {
        display: inline-block;
    }
    #toProfile .message:before {
        position: absolute;
        left: -8px;
        content: "◀";
        font-size: 12px;
        color: rgb(65,64,153);
    }
</style> -->
<?endif;?>

<div class="form-group" style = "text-align: center;">
    <h2 style = "display:inline-block; text-align: center;"><?php echo $user->name; ?></h2> <?php if($user->dealer_type!=2 ){
        if($userId == 1 || $userId == 2 || ($userId != 1 && $user->dealer_id != 1)) { ?>
        </br><a class="btn btn-primary btn-acct" href="/index.php?option=com_gm_ceiling&view=dealerprofile&type=score"> <?=$rest?-round($rest, 2):0;?> руб. </a>
        <div id="modal_window_container" class="modal_window_container" >
            <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
            </button>
            <div id="modal_window_acct" class="modal_window" style="padding-top: 2em; border: 2px solid #414099;">
                <p>Общая сумма за комплектующие и материалы: <?php echo $total_sum?-$total_sum:0;?></p>
                <p>Внесенная сумма: <?php echo $contributed?$contributed:0;?></p>
                <p>Сумма долга: <?php echo ($rest>0)?$rest:0;?></p>
                <p>На счете: <?php echo $rest?-$rest:0;?></p>
            </div>
        </div>
        <?php } ?>
  <?php  }?>
</div>

<div class="start_page">
    <?php if ($userId == 1 || $userId == 2 || $userId == 827): ?>
        <h3>Гильдия мастеров</h3>
    <?php elseif ($user->delaer_type == 1 || $user->dealer_type == 0): ?>
        <h3>Дилер</h3>
    <?php endif; ?>
    <?php if ($user->dealer_type == 0) { ?>
        <div style=" margin-left: calc(50% - 100px); padding-bottom: 1em;">
		    <div class="container-for-circl">
                <a class="btn btn-large btn-warning" href="<?php
                if ($userId == 1 || $userId == 2 || $userId == 827)
                    echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage', false);
                else echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=managermainpage', false);
                ?>">
                    <?php if ($userId == 1 || $userId == 2 || $userId == 827): ?>
                    <i class="fa fa-clock-o" aria-hidden="true"></i> ГМ Менеджер</a>
                <?php else: ?>
                    <i class="fa fa-clock-o" aria-hidden="true"></i>Менеджер</a>
                <?php endif; ?>
                <?php if ($sumManager != 0) { ?>
                    <div class="circl-digits"><?php echo $sumManager; ?></div>
                <?php } ?>
            </div>
        </div>
        <div style=" margin-left: calc(50% - 100px); padding-bottom: 1em;">
		    <div class="container-for-circl">
                <a class="btn btn-large btn-success" href="<?php
                if ($userId == 1 || $userId == 2 || $userId == 827 ) {
                    echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=gmcalculatormainpage', false); }
                else { echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=calculatormainpage', false); }
                ?>">
                    <?php if ($userId == 1 || $userId == 2 || $userId == 827): ?>
                    <i class="fa fa-calculator" aria-hidden="true"></i> ГМ Замерщик</a>
                <?php else: ?>
                    <i class="fa fa-calculator" aria-hidden="true"></i> Замерщик</a>
                <?php endif; ?>
                <?php if ($sumcalculator[0]->count != 0) { ?>
                    <div class="circl-digits"><?php echo $sumcalculator[0]->count; ?></div>
                <?php } ?>
            </div>
        </div>
        <div style=" margin-left: calc(50% - 100px); padding-bottom: 1em;">
		    <div class="container-for-circl">
                <a class="btn btn-large btn-primary" href="<?php
                if ($userId == 1 || $userId == 2){
                    echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=gmchiefmainpage', false);
                }
                    
                else echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=chiefmainpage', false);
                ?>">
                    <?php if ($userId == 1 || $userId == 2 || $userId == 827): ?>
                    <i class="fa fa-user" aria-hidden="true"></i> ГМ Начальник МС</a>
                <?php else: ?>
                    <i class="fa fa-user" aria-hidden="true"></i> Начальник МС</a>
                <?php endif; ?>
                <?php if ($countMounting[0]->count != 0) { ?>
                    <div class="circl-digits"><?php echo $countMounting[0]->count; ?></div>
                <?php } ?>
            </div>
        </div>
        <p class="center">
            <a class="btn btn-large btn-danger"
               href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=dealer&subtype=buh', false); ?>"><i
                        class="fa fa-list-alt" aria-hidden="true"></i> Бухгалтерия</a>
        </p>
        <p class="center">
            <a class="btn btn-large btn-danger"
               href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=dealer&subtype', false); ?>"><i
                        class="fa fa-list-alt" aria-hidden="true"></i> Договоры</a>
        </p>
        <?php if($userId = 2 || $userId = 1 || $userId == 827 ){?>
            <p class="center">
                <a class="btn btn-large btn-primary"
                href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=analytics', false); ?>"><i
                            class="fa fa-list-alt" aria-hidden="true"></i> Аналитика</a>
            </p>
            <p>
                <a class="btn btn-large btn-primary"
                href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=api_phones', false); ?>"><i
                            class="fa fa-mobile" aria-hidden="true"></i> Телефоны</a>
            </p>
        <?php }?>
    <?php } elseif ($user->dealer_type == 1) { ?>
        <p class="center">
            <button class="btn btn-large btn-primary" id="precalc_btn" ><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Рассчитать</button>
        </p>

        <div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
            <div class="container-for-circl">
                <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>"><i class="fa fa-phone-square" aria-hidden="true"></i> Перезвоны</a>
                <div class="circl-digits" id="ZvonkiDiv" style="display: none;"></div>
            </div>
        </div>
        <p class="center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=incoming_calls', false); ?>"><i class="fa fa-phone-square" aria-hidden="true"></i> Входящие звонки</a>
        </p>
        <div style=" margin-left: calc(50% - 100px); padding-bottom: 1em;">
            <div class="container-for-circl">
                <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=calendar', false); ?>"><i class="fa fa-calculator" aria-hidden="true"></i> Замеры</a>
                <?php if ($sumcalculator[0]->count != 0) { ?>
                    <div class="circl-digits"><?php echo $sumcalculator[0]->count; ?></div>
                <?php } ?>
            </div>
        </div>
        <p class="center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=clients', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Клиенты</a>
        </p>
        <div style=" margin-left: calc(50% - 100px); padding-bottom: 1em;">
            <div class="container-for-circl">
                <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=chiefmainpage', false); ?>"><i class="fa fa-gavel" aria-hidden="true"></i> Монтажи</a>
                <?php if ($allMount != 0) { ?>
                    <div class="circl-digits"><?php echo $allMount; ?></div>
                <?php } ?>
            </div>
        </div>
         <p class="center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=analytics', false); ?>"><i class="fa fa-bar-chart" aria-hidden="true"></i> Аналитика</a>
        </p>
        <p class="center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=prices', false); ?>"><i class="fa fa-list-alt" aria-hidden="true"></i> Прайсы</a>
        </p>
         
    <?php } elseif ($user->dealer_type == 2) { ?>
        <p class="center">
            <button class="btn btn-large btn-primary" id="create_order_btn"><i class="fa fa-list-alt"
                                                                               aria-hidden="true"></i> Создать заказ
            </button>
        </p>
        <p class="center">
            <button class="btn btn-large btn-primary" id="prev_orders_btn"><i class="fa fa-list-alt"
                                                                              aria-hidden="true"></i> Ранее заказанные
            </button>
        </p>
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
                    location.href = '/index.php?option=com_gm_ceiling&view=calculationform2&type=calculator&subtype=precalc&calc_id='+data+'&precalculation=1';
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
        var div = jQuery(".modal_window"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery(".close_btn").hide();
            jQuery(".modal_window_container").hide();
            jQuery(".modal_window").hide();
        }
    });
</script>