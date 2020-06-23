<?php
$user       = JFactory::getUser();
$userId     = $user->get('id');
$user_group = $user->groups;

$clientcardModel = Gm_ceilingHelpersGm_ceiling::getModel('clientcard');
$historyModel = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
$projectsMountsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
$projectsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects');

$mountTypes = $projectsMountsModel->get_mount_types();
unset($mountTypes[1]);
foreach ($mountTypes as $key=>$value){
    $mountTypes[$key] = array("title"=>$value,"status"=>$key+25);
}
$history = $historyModel->getDataByClientId($this->item->id);

$app = JFactory::getApplication();
$jinput = $app->input;
$phoneto = $jinput->get('phoneto', '', 'STRING');
$phonefrom = $jinput->get('phonefrom', '', 'STRING');
$call_id = $jinput->get('call_id', 0, 'INT');
$client = $client_model->getClientById($this->item->id);
$clients_items = $clients_model->getDealersClientsListQuery($client->dealer_id, $this->item->id);
/*для дублирования отдельного потолка*/
$projectsWithCalcIds = $projectsModel->getProjectsWithCalcIds($client->dealer_id);
$dealer = JFactory::getUser($client->dealer_id);
if ($dealer->associated_client != $this->item->id)
{
    throw new Exception("this is not dealer", 403);
}


if(!empty($client->manager_id)){
    $manager_name = JFactory::getUser($client->manager_id)->name;
}
else{
    $manager_name = "-";
}
$client_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
$client_phones = $client_phones_model->getItemsByClientId($this->item->id);
$client_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
$dop_contacts = $client_dop_contacts_model->getContact($this->item->id);
$all_builders = $clients_model->getDesignersByClientName('', 7);
$options = '';
foreach($all_builders as $builder){
    if($builder->dealer_id != $client->dealer_id){
        $options .= "<option value = $builder->dealer_id>$builder->client_name</option>";
    }
}
//$mounterSalaryModel = Gm_ceilingHelpersGm_ceiling::getModel('mounterssalary');
//$mounterSalaryModel->recalcClosedSum($dealer->id);
$calcsComponentModel = Gm_ceilingHelpersGm_ceiling::getModel('Calcs_components');
$common_goods = $calcsComponentModel->getAllComponentsOnBuildersObject($dealer->id);
?>

<style>
    fieldset {
        margin: 10px;
        border: 2px solid #414099;
        padding: 4px;
        border-radius: 4px;
    }
    legend{
        width: auto;
    }
    .td_div{
        border-bottom: #414099 1px solid;
    }
    hr {
        border: 0;
        height: 2px;
        background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(65,64,153, 0.75), rgba(0, 0, 0, 0));
    }

    .choose_file .form-group{
        padding:1em;margin:1em
    }
    input[type=file]{outline:0;opacity:0;pointer-events:none;user-select:none}
    .label{width:100%;height:38px;border:2px dashed grey;border-radius:5px;display:block;padding:0.25em;transition:border 300ms ease;cursor:pointer;text-align:center;margin-left: auto;
        margin-right: auto}
    .label i{display:block;font-size:28px;}
    .label i,.label .title{color:grey;transition:200ms color}
    .label:hover{border:2px solid #414099}
    .label:hover i,.choose_file .label:hover .title{color:#414099}

    .small_img {
        display: inline-block;
        height: 40px;

    }
    .big_img {
        width:100%;
        height:auto;
    }
    .btn_width{
        width: 320px;
    }
</style>
<button id="back_btn" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</button>
<div class="container">
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-xs-12 col-md-8">
            <div class="col-xs-6 col-md-6" id="FIO-container-tar"><label id = "FIO">Имя: <?php echo $this->item->client_name; ?></label></div>
            <div class="col-xs-3 col-md-2">
                <button type="button" id="edit" value="" class = "btn btn-primary"><i class="fas fa-edit" aria-hidden="true"></i></button>
            </div>
            <div class="col-xs-3 col-md-2">
                <button class = "btn btn-primary" type = "button" id="but_call"><i class="fa fa-phone" aria-hidden="true"></i></button>
            </div>
            <div class="col-xs-3 col-md-2">
                <a href="/index.php?index.php?option=com_gm_ceiling&view=dealerprofile&type=edit&id=<?php echo $dealer->id?>" class = "btn btn-primary" i>Прайс</a>
            </div>
        </div>
        <div class="col-xs-6 col-md-2 left">
            <button type="button" class="btn btn-primary" id="show_info_div">Информация</button>
        </div>
        <div class="col-xs-6 col-md-2 left">
            <button type="button" class="btn btn-primary" id="show_actions_div">Действия</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <!-- <label style="font-size: 18pt;color: #414099;">Менеджер: <?php /*echo $manager_name;*/?></label>-->
        </div>
    </div>
    <div class="row" id="dealer_info_div" style="display: none;">
        <div class="col-md-6">
            <div class="col-md-6">
                <div>
                    <p class = "caption-tar" style="font-size: 18px; color: #414099; text-align: left; margin-bottom: 0px;">Почта: </p>
                </div>
                <? if (!empty($dop_contacts)) { ?>
                    <div>
                        <? foreach ($dop_contacts AS $contact) {?>
                            <p  style="font-size: 20px; color: #414099; text-align: left; margin-bottom: 0px;"><? echo $contact->contact; echo "<br>";?></p> <? }?>
                    </div>
                <? } ?>
                <div>
                    <input type="text" id="new_email" placeholder="Почта" required>
                    <button type="button" id="add_email" class="btn btn-primary">Добавить</button>
                </div>
            </div>
            <div class="col-md-6">
                <div>
                    <p class = "caption-tar" style="font-size: 18px; color: #414099; margin-bottom: 0px;">Телефоны: </p>
                </div>
                <div>
                    <?php foreach($client_phones as $item) { ?>
                        <p  style="font-size: 20px; color: #414099; margin-bottom: 0px;"><? echo $item->phone; ?></p>
                    <?php } ?>
                </div>
                <div>
                    <input type="text" id="new_phone" placeholder="Телефон" required>
                    <button type="button" id="add_phone" class="btn btn-primary">Добавить</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="col-sm-12" id = "calls">
                <p class="caption-tar">История застройщика</p>
                <div id="calls-tar">
                    <table id="table-calls-tar" class="table table-striped one-touch-view" cellspacing="0">
                        <?php foreach($history as $item): ?>
                            <tr>
                                <td>
                                    <?php
                                    $date = new DateTime($item->date_time);
                                    echo $date->Format('d.m.Y H:i');
                                    ?>
                                </td>
                                <td><?php echo $item->text;?></td>
                            </tr>
                        <?php endforeach;?>
                    </table>
                </div>
            </div>
            <div class="col-xs-12" id="add-note-container-tar">
                <label for="comments">Добавить комментарий:</label>
                <br>
                <input id="new_comment" type="text" class="input-text-tar input2" placeholder ="Введите новый комментарий">
                <button class = "btn btn-primary" type = "button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
    <div class="row" id="actions_div" style="display: none;">
        <table class = "actions">
            <?php if ($call_id != 0) { ?>
                <tr>
                    <td>
                        <button id = "broke" type = "button" class = "btn btn-primary">Звонок сорвался, перенести время</button>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td class = "td-left">
                    <button class="btn btn-primary" type="button" id="but_comm">Отправить КП</button>
                </td>
            </tr>
            <tr>
                <td class = "td-left">
                    <button class="btn btn-primary" type="button" id="but_msk_kp">Московский застройщик КП</button>
                </td>
            </tr>
            <tr>
                <td class = "td-left">
                    <button class="btn btn-primary" type="button" id="but_callback">Добавить перезвон</button>
                </td>
            </tr>

        </table>
        <?php include_once('components/com_gm_ceiling/views/clientcard/buttons_calls_history.php'); ?>
    </div>

</div>
<div class="row" style="padding:15px 15px 15px 15px;border: #414099 solid 2px;border-radius: 15px">
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-md-4 center">
            <label for="floor_count">Кол-во этажей</label><br>
            <input type="number" id="floor_count" class="input-gm">
        </div>
        <div class="col-md-4 center">
            <label for="start_number">Начало нумерации</label><br>
            <input type="number" id="start_number" class="input-gm">
        </div>
        <div class="col-md-4 center">
            <label for="apartment_count">Кол-во квартир на этаже</label><br>
            <input type="number" id="apartment_count" class="input-gm">
        </div>
    </div>
    <div class="row center">
        <div class="col-md-12">
            <button class="btn btn-primary" type="button" id="createFloors">Создать</button>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12" id = "cliens_of_dealer">
        <p class="caption-tar">Клиенты застройщика</p>
        <div id="cliens_of_dealer_2">
            <table id="cliens_of_dealer_table" class="table table-striped table_cashbox one-touch-view" cellspacing="0">
                <tbody>
                <?php foreach ($clients_items as $i => $item) : ?>
                    <tr class="row<?php echo $i % 2; ?>" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&id='.(int) $item->id); ?>">
                        <td class="one-touch">
                            <?php
                            if($item->created == "0000-00-00") {
                                echo "-";
                            } else {
                                $jdate = new JDate($item->created);
                                $created = $jdate->format("d.m.Y");
                                echo $created;
                            }
                            ?>

                        </td>
                        <td class="one-touch"><?php echo $item->client_name; ?></td>
                        <td class="one-touch"><?php echo $item->client_contacts; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<hr>
<div class="row">
    <p class="caption-tar">Дублировать замеры</p>
    <form>
        <div class="col-md-6">
            <fieldset>
                <legend>Откуда</legend>
                <select id="copy_from_select" class="input-gm">
                    <option value="">Выбрать этаж</option>
                    <?php foreach ($clients_items as $floor){?>
                        <option value="<?php echo $floor->id?>"><?php echo $floor->client_name?></option>
                    <?php }?>
                </select>
            </fieldset>
        </div>
        <div class="col-md-6">
            <fieldset >
                <legend>Куда</legend>
                <div id="where_duplicate">

                </div>
            </fieldset>
        </div>
    </form>
</div>
<div class="row center">
    <div class="col-md-12">
        <button class="btn btn-primary" id="duplicate">Дублировать</button>
    </div>
</div>
<div class="row">
    <p class="caption-tar">Дублировать отдельные помещения</p>
    <form>
        <div class="col-md-3">
            <fieldset>
                <legend>Откуда</legend>
                <select id="copy_select" class="input-gm">
                    <option value="">Выбрать этаж</option>
                    <?php
                    foreach ($projectsWithCalcIds as $p){?>
                        <option value="<?= $p['project_id']?>"><?= $p['client_name'].' '.$p['project_info']?></option>
                    <?php }?>
                </select>
            </fieldset>
        </div>
        <div class="col-md-3">
            <fieldset>
                <legend>Что</legend>
                <div id="what_duplicate">

                </div>
            </fieldset>
        </div>
        <div class="col-md-6">
            <fieldset >
                <legend>Куда</legend>
                <div id="where_duplicate_calcs">

                </div>
            </fieldset>
        </div>
    </form>
</div>
<div class="row center">
    <div class="col-md-12">
        <button class="btn btn-primary" id="duplicate_selected">Дублировать</button>
    </div>
</div>
<hr>

<div class="row center">
    <p class="caption-tar">Бригады</p>

    <div class="col-md-6">
        <p><b>Добавить бригаду</b></p>
        <label for="new_mounter_name">ФИО/Название</label>
        <input type="text" id="new_mounter_name" class="input-gm">
        <label for="new_mounter_phone">Телефон</label>
        <input type="text" id="new_mounter_phone" class="input-gm">
        <button class="btn btn-primary" id="create_mounter">Cоздать</button>
    </div>
    <div class="col-md-6">
        <button class="btn btn-primary" type="button" id="btn_show_mounters">Просмотреть список бригад </button>
    </div>
</div>
<hr>
<div class="row center" style="margin-bottom: 5px;">
    <div class="col-md-6">
        <p class="caption-tar">Общая информация по объекту</p>
        <div class="row">
            <div class="col-md-12">
                Общий периметр: <span id="common_perimiter"></span> м.
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                Общая площадь: <span id="common_square"></span> м<sup>2</sup>.
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                Общая себестоимость: <span id="common_self_sum"></span> p.
            </div>
        </div>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <button class="btn btn-primary btn_width" id="show_goods"> Просмотреть список комплектующих</button>
            </div>
        </div>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <button class="btn btn-primary btn_width" id="btn_add_dop_costs"> Добавить доп.затраты</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-primary btn_width" id="btn_show_dop_costs"> Просмотреть доп.затраты</button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <p class="caption-tar">Генерация PDF</p>
        <?php foreach ($mountTypes as $k => $mountStage) { ?>
            <input name="stage" id="<?php echo "stage_$k" ?>" class="radio" value="<?php echo $k?>" type="radio">
            <label for="<?php echo "stage_$k" ?>"><?php echo $mountStage['title']?></label>
            <br>
        <?php }?>
        <button type="button" id="generate_pdf" class="btn btn-primary">Сгенерировать PDF</button>
    </div>
</div>
<hr>
<div class="row center">
    <div class="col-md-12">
        <button class="btn btn-primary" type="button" id="btn_recalc">Пересчитать всё</button>
    </div>
</div>
<hr>
<h4>Легенда</h4>
<div class="row" style="margin-bottom: 15px;">
    <div class="col-md-4">
        <div style="height:35px;background:linear-gradient(135deg, white, green 150%);">
            Объем закрыт
        </div>
    </div>
    <div class="col-md-4">
        <div style="height:35px;background:linear-gradient(135deg, white, yellow 150%);">
            Не на все потолки назначены бригады
        </div>
    </div>
    <div class="col-md-4">
        <div style="height:35px;background:linear-gradient(135deg, white, #414099 150%);">
            На все потолки назначены бригады
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <i class="fas fa-house-damage"></i> Дефект
    </div>
    <div class="col-md-4">
        <i class="fas fa-check-double"></i> Дефект исправлен
    </div>
    <div class="col-md-4">
        <i class="far fa-calendar-check"></i> Нужно подтвердить взятие объема
    </div>

</div>
<hr>
<div class="row">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link common_tab" data-toggle="tab" role="tab">
                Общее
            </a>
        </li>
        <?php foreach ($mountTypes as $k => $mountStage) { ?>
            <li class="nav-item">
                <a class="nav-link mount_stage" data-toggle="tab" data-mount_type="<?php echo $k;?>" data-mount_status="<?php echo $mountStage['status'];?>" role="tab">
                    <?php echo $mountStage['title'] ?>
                </a>
            </li>
        <?php } ?>
    </ul>
    <table id = "report_table" class="table table-striped table_cashbox one-touch-view">
        <tbody>

        </tbody>
    </table>
</div>

<div class="row center">
    <div class="col-md-12">
        <button type="button" id="show_salary" class="btn btn-primary">Посмотреть суммы по бригадам</button>
    </div>
    <!--<div class="col-md-6">
        <button type="button" id="show_salary" class="btn btn-primary">Посмотреть суммы по бригадам</button>
    </div>
    <div class="col-md-6">
        <button type="button" id="recalc_salary" class="btn btn-primary">Пересчитать з\п бригадам</button>
    </div>-->
</div>
<div class="row center" style="margin-top:15px">
    <div class="col-md-12">
        <a href="/index.php?option=com_gm_ceiling&view=mounterscommon" class="btn btn-primary"> Посмотреть сводную таблицу</a>
    </div>
</div>
<hr>
<div class="row center">
    <div class="col-md-12">
        <button class="btn btn-primary" type="button" id="btn_close">Закрыть объект</button>
    </div>
</div>
<div id="mv_container" class="modal_window_container">
    <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="modal_window_fio" class="modal_window">
        <h6>Введите новое ФИО клиента</h6>
        <p><input type="text" id="new_fio" placeholder="ФИО" required></p>
        <p><button type="button" id="update_fio" class="btn btn-primary">Сохранить</button>  <button type="button" id="cancel" class="btn btn-primary">Отмена</button></p>
    </div>
    <div id="modal_window_client" class="modal_window">
        <form action="/index.php?option=com_gm_ceiling&task=clientform.save" method="post" enctype="multipart/form-data">
            <p><strong>Создание нового клиента</strong></p>
            <p>ФИО:</p>
            <p><input type="text" id="fio_client" name="jform[client_name]"></p>
            <p>Номер телефона:</p>
            <p><input type="text" id="jform_client_contacts" name="jform[client_contacts]" required></p>
            <input type="hidden" id="jform_dealer_id" name="jform[dealer_id]" value="<?php echo $client->dealer_id; ?>">
            <p><button type="submit" id="save_client" class="btn btn-primary">ОК</button></p>
        </form>
    </div>
    <div id="modal_window_comm" class="modal_window">
        <? if (!empty($dop_contacts)) { ?>
            <div style="margin-top: 10px;">
                <? foreach ($dop_contacts AS $contact) {?>
                    <input type="radio" name='rb_email' value='<? echo $contact->contact; ?>' onclick='rb_email_click(this)'><? echo $contact->contact; ?><br>
                <? }?>
            </div>
        <? } ?>
        <h6 style = "margin-top:10px">Введите почту</h6>
        <p><input type="text" id="email_comm" placeholder="Почта" required></p>
        <p><button type="button" id="send_comm" class="btn btn-primary">Отправить</button>  <button type="button" id="cancel2" class="btn btn-primary">Отмена</button></p>
    </div>
    <div id="modal_window_call" class="modal_window">
        <label>Добавить звонок</label><br>
        <input id="call_date_m" type="datetime-local" placeholder="Дата звонка"><br>
        <input id="call_comment_m" placeholder="Введите примечание"><br>
        <button class="btn btn-primary" id="add_call" type="button"><i class="fas fa-save" aria-hidden="true"></i></button>
    </div>
    <div id="modal_window_select_number" class="modal_window">
        <p>Выберите номер для звонка:</p>
        <select id="select_phones" class = "select_phones">
            <option value='0' disabled selected>Выберите номер</option>
            <?php foreach($client_phones as $item): ?>
                <option value="<?php echo $item->phone; ?>"><?php echo $item->phone; ?></option>
            <?php endforeach;?>
        </select>
    </div>
    <div id="call" class="modal_window">
        <p>Перенести звонок</p>
        <p>Дата звонка</p>
        <p><input name="call_date" id="call_date" type="datetime-local" placeholder="Дата звонка"></p>
        <p>Примечание</p>
        <p><input name="call_comment" id="call_comment" placeholder="Введите примечание"></p>
        <p><button class="btn btn-primary" id="add_call_and_submit" type="button"><i class="fas fa-save" aria-hidden="true"></i></button></p>

    </div>
    <div id="one_mounter_salary" class="modal_window">
        <table id="detailed_salary" class="table_project_analitic">
            <thead>
            <tr class="caption_table">
                <td>
                    <div class="row">
                        <div class="col-md-3">
                            <button id="to_common_salary" class="btn btn-primary">К общему списку</button>
                        </div>
                        <div class="col-md-9">
                            Сумма
                        </div>
                    </div>
                </td>
                <td>
                    Объект
                </td>
                <td>
                    Время
                </td>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div id="mounters_salary" class="modal_window">
        <table id="salary" class="table_project_analitic">
            <thead>
            <tr class="caption_table">
                <td>ФИО</td>
                <td>В работе обагечивание,руб.</td>
                <td>В работе натяжка,руб.</td>
                <td>В работе вставка,руб.</td>
                <td>Итого в работе,руб.</td>
                <td>Сумма закрытых,руб.</td>
                <td>Выплачено,руб.</td>
                <td>Остаток,руб.</td>
                <td>Внести суммы выплаты,руб.</td>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div id="add_mounters" class="modal_window">
        <input type="hidden" id="selected_project">
        <input type="hidden" id="selected_floor">
        <div class="container">
            <div class="row">
                <div class="col-md-4" id="all_calcs_mounter">

                </div>
                <div class="col-md-8">
                    <h4>Назначить бригады попотолочно</h4>
                    <table id="calcsMounters" class="table_project_analitic">
                        <thead>
                        <tr class="caption_table">
                            <td>Комната</td>
                            <td>Монтажники</td>
                            <td>Сумма</td>
                            <td>Подтвердить взятие</td>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="delete_mounter_window" class="modal_window">
        <table id="mountersTbl" class="table_project_analitic">
            <thead>
            <tr class="caption_table">
                <td>Монтажники</td>
                <td>Номер</td>
                <td><i class="fa fa-trash" aria-hidden="true"></i></td>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div class="modal_window" id="mw_dop_costs">
        <div class="row center" >
            <div class="col-md-4">
                <span>Cумма</span>
                <input class="form-control" id="dop_cost_sum">
            </div>
            <div class="col-md-4">
                <span>Комментарий</span>
                <input class="form-control" id="dop_cost_comment">
            </div>
            <div class="col-md-4">
                <span>Прикрепить чек</span>
                <!--<input type="file" class="btn btn-primary" id="check">-->
                    <label class="label">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" id="attach_check" multiple >
                    </label>
            </div>
        </div>
        <div class="row center">
            <button class="btn btn-primary" type="button" id="save_dop_costs">Сохранить</button>
        </div>
    </div>
    <div class="modal_window" id="mw_view_dop_costs">
        <table id="table_dop_costs" class="table_project_analitic">
            <thead>
                <tr class="caption_table">
                    <td>
                        Сумма
                    </td>
                    <td>
                        Комментарий
                    </td>
                    <td>
                        От кого
                    </td>
                    <td>
                        Подтверждено
                    </td>
                    <td>
                        Чек
                    </td>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div class="modal_window" id="mw_common_goods">
        <table class="table table-striped one-touch-view">
            <thead>
                <th class="center">
                    Наименование
                </th>
                <th class="center">
                    Количество
                </th>
            </thead>
            <tbody>
                <?php foreach($common_goods as $goods){ ?>
                    <tr>
                        <td class="center">
                            <?=$goods->name;?>
                        </td>
                        <td class="center">
                            <?=$goods->count.' '.$goods->unit;?>
                        </td>
                    </tr>
                <?php }?>

            </tbody>
        </table>
        <div class="row center">
            <div class="col-md-12">
                <button class="btn btn-primary" id="save_goods_to_pdf">Скачать PDF</button>
            </div>
        </div>
    </div>
</div>
    <script>
        var progressData = [],
            mountersOption = "<option>Выберите</option>",
            mountersForDelete = [],
            checks,
            projectsWithCalcsIds = JSON.parse('<?=addslashes(json_encode($projectsWithCalcIds))?>');
        var EDIT_BUTTON = "<button class='btn btn-primary btn-sm edit_mounter'><i class=\"fas fa-edit\" aria-hidden=\"true\"></i></button>",
            ACCEPT_BUTTON = "<button class='btn btn-primary btn-sm accept_mounter'><i class=\"fa fa-check\" aria-hidden=\"true\"></i></button>",
            CHECK_BUTTON = "<div class='row'><div class='col-md-12'><button name='check_btn' class='btn btn-primary btn-sm sum_btn'><i class=\"fa fa-check\" aria-hidden=\"true\"></i></button></div></div>",
            REFRESH_BUTTON = "<div class='row'><div class='col-md-12'><button name='refresh_btn' class='btn btn-primary btn-sm sum_btn'><i class=\"fas fa-sync\" aria-hidden=\"true\"></i></button></div></div>",
            BUILDERS_SELECT = '<select class="input-gm builders_select" style ="vertical-align: middle;"><?php echo $options ?></select>',
            MOVE_SUM_BTN = '<button class="btn btn-primary btn-sm transfer" style ="vertical-align: middle;"><i class="fas fa-save" aria-hidden="true"></i></buttton>',
            DEFECT_ICON = '<i class="fas fa-house-damage"></i>',
            DEFECT_FIXED_ICON = '<i class="fas fa-check-double"></i>',
            NEED_TAKING_ICON = '<i class="far fa-calendar-check"></i>',
            APPROVE_TAKING_BUTTON = '<button class="btn btn-primary btn-sm approve_btn"><i class="fas fa-check-double"></i></button>';

        function fillDuplicateInFields(value){
            jQuery("#where_duplicate").empty();
            var floors = JSON.parse('<?php echo json_encode($clients_items)?>'),
                checkbox ="";
            jQuery.each(floors,function(index,elem){
                if(value != elem.id) {
                    checkbox = "<input name = \"need_duplicate\"type=\"checkbox\" id=\"" + elem.id + "\" class=\"inp-cbx\" value = \"" + elem.id + "\" style=\"display: none\">\n" +
                        "<label for=\"" + elem.id + "\" class=\"cbx\">\n" +
                        "<span>\n" +
                        "<svg width=\"12px\" height=\"10px\" viewBox=\"0 0 12 10\">\n" +
                        "<polyline points=\"1.5 6 4.5 9 10.5 1\"></polyline>\n" +
                        "</svg>\n" +
                        "</span>\n" +
                        "<span>" + elem.client_name + "</span>\n" +
                        "</label>"
                    jQuery("#where_duplicate").append(checkbox);
                }
            });
        }

        jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var div = jQuery("#modal_window_fio"), // тут указываем ID элемента
                div2 = jQuery("#modal_window_client"),
                div3 = jQuery("#modal_window_comm"),
                div4 = jQuery("#modal_window_call"),
                div5 = jQuery("#call"),
                div6 = jQuery("#modal_window_select_number"),
                div7 = jQuery("#apartment_change"),
                div8 = jQuery("#mounters_salary"),
                div9 = jQuery("#one_mounter_salary"),
                div10 = jQuery("#add_mounters"),
                div11 = jQuery("#delete_mounter_window"),
                div12 = jQuery("#noty_center_layout_container"),
                div13 = jQuery("#mw_dop_costs"),
                div14 = jQuery("#mw_view_dop_costs"),
                div15 = jQuery('#mw_common_goods');
            if (!div.is(e.target) && !div2.is(e.target) && !div3.is(e.target)
                && !div4.is(e.target) && !div5.is(e.target) && !div6.is(e.target)
                && !div7.is(e.target)&& !div8.is(e.target) && !div9.is(e.target) && !div10.is(e.target)
                &&!div11.is(e.target)&&!div12.is(e.target) && !div13.is(e.target) &&!div14.is(e.target) && !div15.is(e.target)
                && div.has(e.target).length === 0 && div2.has(e.target).length === 0 && div3.has(e.target).length === 0
                && div4.has(e.target).length === 0 && div5.has(e.target).length === 0 && div6.has(e.target).length === 0
                && div7.has(e.target).length === 0 && div8.has(e.target).length === 0 && div9.has(e.target).length === 0
                && div10.has(e.target).length === 0 && div11.has(e.target).length === 0&& div12.has(e.target).length === 0
                && div13.has(e.target).length === 0 && div14.has(e.target).length === 0 && div15.has(e.target).length === 0 ) {
                jQuery("#close").hide();
                jQuery("#mv_container").hide();
                div.hide();
                div2.hide();
                div3.hide();
                div4.hide();
                div5.hide();
                div6.hide();
                div7.hide();
                div8.hide();
                div9.hide();
                div10.hide();
                div11.hide();
                div12.hide();
                div13.hide();
                div14.hide();
                div15.hide();
            }
        });

        jQuery("#btn_show_dop_costs").click(function(){
            var builderId = '<?php echo $dealer->id?>';
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=users.getBuildersDopCost",
                data: {
                    builder_id: builderId
                },
                success: function(data){
                   console.log(data);
                   jQuery.each(data,function(index,elem){
                       var images = elem.check.split(','),
                           img = '';
                       for(var i=0;i<images.length;i++){
                           img += '<img class="small_img" src="/additional_builder_costs/'+builderId+'/'+images[i]+'">';
                       }
                       console.log('img',img);
                       jQuery("#table_dop_costs > tbody").append('<tr>' +
                                                                    '<td>'+elem.sum+'</td>'+
                                                                    '<td>'+elem.description+'</td>'+
                                                                    '<td>'+elem.from_name+'</td>'+
                                                                    '<td>'+elem.accepted_name+'</td>'+
                                                                    '<td>'+img+'</td>'+
                                                                 '</tr>');
                   });

                   jQuery("#mv_container").show();
                   jQuery("#close").show();
                   jQuery("#mw_view_dop_costs").show('slow');

                },
                dataType: "json",
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });


        });
        jQuery('body').on('click', '.small_img', function(e)
        {
           jQuery(this).removeClass('small_img');
           jQuery(this).addClass('big_img');
        });
        jQuery('body').on('click', '.big_img', function(e)
        {
            jQuery(this).removeClass('big_img');
            jQuery(this).addClass('small_img');
        });

        jQuery("#btn_add_dop_costs").click(function(){
            jQuery("#mv_container").show();
            jQuery("#close").show();
            jQuery("#mw_dop_costs").show('slow');
        });
        jQuery("#duplicate").click(function () {
            var clients_id = [];
            jQuery.each(jQuery("input[name='need_duplicate']:checked"),function(index,elem){
                clients_id.push(elem.value);
            });
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=projects.duplicate",
                data: {
                    clients: clients_id,
                    idFrom: jQuery("#copy_from_select").val()
                },
                success: function(data){
                    location.reload();
                },
                dataType: "json",
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        });

        jQuery('#btn_close').click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=users.closeBuilderObject",
                data: {
                    builderId: '<?php echo $dealer->id?>'
                },
                success: function(data){
                    location.reload();
                },
                dataType: "json",
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        });
        jQuery("#copy_from_select").change(function(){
            if(this.value){
                fillDuplicateInFields(this.value);
            }
            else {
                jQuery("#where_duplicate").empty();
            }
        });
        jQuery("#generate_pdf").click(function () {
            var stage = jQuery("[name='stage']:checked").val();
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=generateBuilderPDF",
                data: {
                    id:'<?php echo $client->dealer_id; ?>',
                    stage: stage,
                    stageName:jQuery(".mount_stage.active")[0].innerText
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    var win = window.open(data.url, '_blank');
                    win.focus();
                },
                error: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });

        jQuery('#save_goods_to_pdf').click(function () {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=components.saveGoodsToPdf",
                data: {
                    builder:'<?php echo $client->dealer_id; ?>'
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    var win = window.open(data, '_blank');
                    win.focus();
                },
                error: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });

        jQuery("#new_client").click(function(){
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_client").show("slow");
        });

        jQuery("#edit").click(function() {
            jQuery("#mv_container").show();
            jQuery("#modal_window_fio").show("slow");
            jQuery("#close").show();
        });

        jQuery("#show_actions_div").click(function () {
            jQuery("#actions_div").toggle();
        });

        jQuery("#show_info_div").click(function () {
            jQuery("#dealer_info_div").toggle();
        });

        jQuery("#but_comm").click(function (){
            jQuery("#mv_container").show();
            jQuery("#modal_window_comm").show("slow");
            jQuery("#close").show();
        });


        jQuery("#but_msk_kp").click(function (){
            jQuery("#send_comm").attr("c_type","msk");
            jQuery("#mv_container").show();
            jQuery("#modal_window_comm").show("slow");
            jQuery("#close").show();
        });


        jQuery("#but_callback").click(function (){
            jQuery("#mv_container").show();
            jQuery("#modal_window_call").show("slow");
            jQuery("#close").show();
        });

        jQuery("#btn_show_mounters").click(function (){
            jQuery("#mv_container").show();
            jQuery("#delete_mounter_window").show("slow");
            jQuery("#close").show();
            console.log(mountersForDelete);
            jQuery("#mountersTbl > tbody").empty();
            if(!empty(mountersForDelete)){
                mountersForDelete.forEach(function(mounter){
                    jQuery("#mountersTbl > tbody").append('<tr></tr>');
                    jQuery("#mountersTbl > tbody > tr:last").append('<td>'+mounter.name+'</td>' +
                                                                    '<td><a href="tel:\'+'+mounter.phone+'\'">'+mounter.phone+'</a></td>'+
                                                                    '<td><button class="btn btn-danger btn-sm" name="btn_delete_mounter" data-id='+mounter.id+'><i class="fa fa-trash" aria-hidden="true"></i></button></td>');
                });
            }
            jQuery("[name = 'btn_delete_mounter']").click(function () {
                var button = jQuery(this);
                var mounterId = button.data('id');
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=users.deleteUser",
                    data: {
                        user_id : mounterId
                    },
                    dataType: "json",
                    async: false,
                    success: function(data) {
                        button.closest('tr').hide();
                        var index = 0;
                        for(var i = 0;i<mountersForDelete.length;i++){
                            if(mountersForDelete[i].id == mounterId){
                                index = i;
                                break;
                            }
                        }
                        console.log(index);
                        mountersForDelete.splice(index,1);
                        mountersOption = "<option>Выберите</option>";
                        generateMountersOption(mountersForDelete);
                    },
                    error: function(data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });

            });
        });

        jQuery("#to_common_salary").click(function () {
            jQuery("#one_mounter_salary").hide();
            jQuery("#show_salary").click();
        });
        jQuery("#show_salary").click(function (){
            jQuery("#mv_container").show();
            jQuery("#mounters_salary").show("slow");
            jQuery("#close").show();
            var projectsId = [];
            jQuery.each(progressData,function(index,el){
                for(var i = 0;i<el.projects.length;i++){
                    projectsId.push(el.projects[i].id);
                }
            });
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=MountersSalary.getData",
                data: {
                    builder_id: '<?php echo $dealer->id?>'
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    var total_obag = 0,
                        total_natyazh = 0,
                        total_vstav = 0,
                        total_taken= 0,
                        total_closed = 0,
                        total_payed = 0,
                        total_rest = 0;;
                    console.log(data);
                    jQuery("#salary > tbody").empty();
                    jQuery.each(data,function (index,el) {
                        total_obag += +el.obag;
                        total_natyazh += +el.natyazh;
                        total_vstav += +el.vstav;
                        total_taken += +el.taken;
                        total_closed += +el.closed;
                        total_payed += +el.payed;

                        var rest = (+el.closed + +el.payed).toFixed(2),
                            rest_td = (rest < 0)? '<td name ="rest"><div class="row click_tr">'+rest +'</div><div class="row">'+ BUILDERS_SELECT + MOVE_SUM_BTN + '</div></td>' : '<td class="click_tr" name ="rest" >'+rest+'</td>';
                        total_rest += +rest;
                        jQuery("#salary > tbody").append('<tr/>');
                        jQuery("#salary > tbody > tr:last").attr('data-id',el.mounter_id);
                        jQuery("#salary > tbody > tr:last").append(
                            '<td class="click_tr">'+el.name+'</td>' +
                            '<td class="click_tr">'+el.obag+'</td>' +
                            '<td class="click_tr">'+el.natyazh+'</td>' +
                            '<td class="click_tr">'+el.vstav+'</td>' +
                            '<td class="click_tr" name ="taken">'+(+el.taken).toFixed(2)+'</td>' +
                            '<td class="click_tr">'+(+el.closed).toFixed(2)+'</td>' +
                            '<td class="click_tr" name ="paid">'+(+el.payed).toFixed(2)+'</td>' +
                            rest_td +
                            '<td><input class="input-gm" name ="pay_sum" style="vertical-align: middle">' +
                            '<button name="save_pay" style="vertical-align: middle" data-mounter_id = "'+el.mounter_id+'"class="btn btn-primary btn-sm"><i class="fas fa-save" aria-hidden="true"></i></button></td>'

                        );
                    });
                    jQuery('#salary > tbody').append('<tr><td><b>Итого</b></td><td>'+total_obag.toFixed(2)+'</td><td>'+total_natyazh.toFixed(2)+'</td><td>'+total_vstav.toFixed(2)+'</td><td>'+total_taken.toFixed(2)+'</td><td>'+total_closed.toFixed(2)+'</td><td>'+total_payed.toFixed(2)+'</td><td>'+total_rest.toFixed(2)+'</td><td>-</td></tr>');
                },
                error: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
            jQuery("#salary .click_tr").click(function () {
                var mounterId = jQuery(this).closest('tr').data('id');

                console.log(mounterId);
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=MountersSalary.getDataById",
                    data: {
                        mounterId:mounterId,
                        ids:projectsId,
                        builder_id: '<?php echo $dealer->id?>'
                    },
                    dataType: "json",
                    async: false,
                    success: function(data) {
                        var total = 0,note = "";
                        console.log(data);
                        jQuery("#detailed_salary > tbody").empty();
                        jQuery.each(data,function (index,el){
                            total += +el.sum;
                            note = (!empty(el.note)) ? el.note : "Выплата";
                            jQuery("#detailed_salary > tbody").append('<tr/>');
                            jQuery("#detailed_salary > tbody > tr:last").append('<td>'+el.sum+'</td><td>'+note+'</td><td>'+el.datetime+'</td>')
                        });
                        jQuery("#detailed_salary > tbody").append('<tr/>');
                        jQuery("#detailed_salary > tbody > tr:last").append('<td align="right"><b>Итого:<b></td><td>'+total+'</td><td></td>');
                    },
                    error: function(data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
                jQuery("#mv_container").show();
                jQuery("#one_mounter_salary").show();
                jQuery("#close").show();

                jQuery("#mounters_salary").hide();
                jQuery("#close").show();


            });

            jQuery('[name="pay_sum"]').click(function (e) {
                e.preventDefault();
                return false;
            });


            function savePay(mounter_id, paid_sum, paid, oldval,rest,restOld) {
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=MountersSalary.savePay",
                    data: {
                        mounter_id: mounter_id,
                        paid_sum: paid_sum,
                        builder_id: '<?php echo $dealer->id ?>'
                    },
                    dataType: "json",
                    async: false,
                    success: function (data) {
                        paid[0].textContent = "";
                        paid[0].textContent = +oldval + paid_sum;
                        rest[0].textContent = "";
                        rest[0].textContent = +restOld + paid_sum;
                    },
                    error: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            }

            jQuery('[name="save_pay"]').click(function(){
               var button = jQuery(this),
                   paid_sum = button.closest("td").find('[name="pay_sum"]').val(),
                   mounter_id = button.data("mounter_id"),
                   taken_sum = button.closest("tr").find('[name="taken"]')[0].textContent,
                   paid = button.closest('tr').find("[name='paid']"),
                   rest = button.closest('tr').find("[name='rest']"),
                   oldval = paid[0].textContent,
                   restOld = button.closest("tr").find('[name="rest"]')[0].textContent ;
               if(paid_sum>0){
                   paid_sum = -paid_sum;
               }
               if(-(paid_sum+ +oldval)>taken_sum){
                   noty({
                       theme: 'relax',
                       layout: 'center',
                       timeout: false,
                       type: "info",
                       text: "Сумма выплаты больше суммы, взятой монтажником, продолжить?",
                       buttons:[
                           {
                               addClass: 'btn btn-primary', text: 'Продолжить', onClick: function ($noty) {
                                   savePay(mounter_id, paid_sum, paid, oldval,rest,restOld);
                                   button.closest("td").find('[name="pay_sum"]').val("");
                                   $noty.close();
                               }
                           },
                           {
                               addClass: 'btn btn-primary', text: 'Отмена', onClick: function($noty) {
                                   $noty.close();

                               }
                           }
                       ]
                   })
               }
               else{
                   savePay(mounter_id, paid_sum, paid, oldval,rest,restOld);
                   button.closest("td").find('[name="pay_sum"]').val("");
               }
            return false;
            });
        });
        /*jQuery("#recalc_salary").click(function () {

        });*/
        jQuery("#cancel").click(function(){
            jQuery("#close").hide();
            jQuery("#mv_container").hide();
            jQuery("#modal_window_fio").hide();
        });

        jQuery("#cancel2").click(function(){
            jQuery("#close").hide();
            jQuery("#modal_window_container").hide();
            jQuery("#modal_window_comm").hide();
        });

        jQuery("#createFloors").click(function(){
            var floorsCount = jQuery("#floor_count").val(),
                apartmentCount = jQuery("#apartment_count").val(),
                builderId = '<?php echo $client->dealer_id; ?>',
                startNumber = jQuery('#start_number').val();
            if(floorsCount && apartmentCount){
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=clients.createBuilderFloors",
                    data: {
                        floors: floorsCount,
                        apartment: apartmentCount,
                        start: startNumber,
                        builderId: builderId
                    },
                    success: function(data){
                        location.reload();
                    },
                    dataType: "json",
                    timeout: 10000,
                    error: function(data){

                        var n = noty({
                            theme: 'relax',
                            timeout: 2000,
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка!"
                        });
                    }
                });
            }
        });

        jQuery("#update_fio").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=updateClientFIO",
                data: {
                    client_id: "<?php echo $this->item->id;?>",
                    fio: jQuery("#new_fio").val()
                },
                success: function(data){
                    jQuery("#FIO").text(data);
                    jQuery("#new_fio").val("");
                    jQuery("#close").hide();
                    jQuery("#mv_container").hide();
                    jQuery("#modal_window_fio").hide();
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "ФИО обновлено!"
                    });
                },
                dataType: "text",
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        })

        jQuery('body').on('click', '.row_project', function(e)
        {
            if (jQuery(this).data('href') !== undefined)
            {
                document.location.href = jQuery(this).data('href');
            }
        });

        jQuery('body').on('click', '.approve_all', function(e)
        {
            var btn = jQuery(this),
                floor_id = btn.data('floor_id'),
                project_id = btn.data('project_id'),
                project = progressData[floor_id].projects.find(function(obj){return obj.id == project_id}),
                calcs = project.calcs,
                ids = [];
            jQuery.each(calcs,function(index,calc){
                if(calc.calc_status == 3){
                    ids.push(calc.id);
                }
                approveScope(ids);
            });


        });
        jQuery('body').on('click', '.approve_btn', function(e)
        {
            var btn = jQuery(this),
                tr = btn.closest('tr'),
                calc_id = [tr.data('calc_id')];
            approveScope(calc_id);
        });

        jQuery('body').on('click', '.accept_mounter', function(e)
        {
            saveMounter(this);
        });
        jQuery('body').on('click', '.transfer', function()
        {
            var button = jQuery(this),
                rest = button.closest('td').find('.click_tr')[0].innerText,
                old_builder = '<?php echo $client->dealer_id;?>',
                new_builder = button.closest('div').find('.builders_select').val(),
                mounter = button.closest('tr').data('id');
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=mountersSalary.transferRest",
                data: {
                    rest:rest,
                    old_builder:old_builder,
                    new_builder:new_builder,
                    mounter_id : mounter
                },
                success: function(data){
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Успешно!"
                    });
                },
                dataType: "text",
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при создании заказа. Сервер не отвечает"
                    });
                }
            });
        });
        jQuery('body').on('click', '.edit_mounter', function(e)
        {
            jQuery(this.closest('td')).find("[name = 'mounter_div']")[0].innerHTML = "<select class='input-gm' name ='mounter_select'>"+mountersOption+"</select>";
            jQuery(jQuery(this.closest('td')).find("[name = 'btn_div']")[0]).append(ACCEPT_BUTTON);
            this.remove();
        });

        jQuery("#add_new_project").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=create_empty_project",
                data: {
                    client_id:<?php echo $this->item->id;?>

                },
                success: function(data){
                    data = JSON.parse(data);
                    var call_id = <?php echo $call_id; ?>;
                    url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=designer&id=' + data + '&call_id=' + call_id;
                    location.href =url;
                },
                dataType: "text",
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при создании заказа. Сервер не отвечает"
                    });
                }
            });
        });

        function approveScope(ids){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=Calcs_mounts.approve",
                data: {
                    calcsId:ids,
                    stage: jQuery("[name='stage']:checked").val()
                },
                success: function(data){
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Успешно!"
                    });
                },
                dataType: "text",
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при создании заказа. Сервер не отвечает"
                    });
                }
            });
        }
        function rb_email_click(elem)
        {
            jQuery("#email_comm").val(elem.value);
        }

        function drawReportTable(stage){
            getReportData(stage);
            var reportTable = jQuery("#report_table");
            reportTable.empty();
            var temp_sums = [];
            for(var i=0,elem;i<Object.keys(progressData).length;i++) {
                var floor_sum = 0,
                    floor_sq = 0;
                reportTable.append('<tr/>');
                elem = progressData[Object.keys(progressData)[i]];
                jQuery("#report_table > tbody > tr:last").attr("data-id", Object.keys(progressData)[i]);
                jQuery("#report_table > tbody > tr:last").append(
                    '<td>' +
                        '<span>' + elem.name + '</span><br>'+
                        '<span name="total_fl_sq"></span><br>'+
                        '<span name="total_fl_sum"></span>'+
                    '</td>');

                for (var j = 0, td, val, sum,mounter,acceptDoneBtn,button; j < elem.projects.length; j++) {
                    var mountersArr = [],
                    val = parseFloat(elem.projects[j].value);
                    sum = parseFloat(elem.projects[j].sum);
                    floor_sq +=val;
                    floor_sum += sum;
                    if(temp_sums[elem.name]) {
                        temp_sums[elem.name] += val;
                    }
                    else{
                        temp_sums[elem.name] = val;

                    }
                    var mountersStr = '<div class="row" style="font-size:9pt;">';
                    mounter = "<select class='input-gm' name ='mounter_select'>"+mountersOption+"</select>";
                    acceptDoneBtn = "";
                    button = ACCEPT_BUTTON;
                    if(+elem.projects[j].status < stage+29)
                    {
                        acceptDoneBtn = CHECK_BUTTON;

                    }
                    else{
                        acceptDoneBtn = REFRESH_BUTTON;
                    }
                    jQuery.each(elem.projects[j].calcs,function(ind,el){
                        if(el.mounters && el.mounters.length){

                            if (mountersArr.indexOf(el.mounters[0].name) == -1) {
                                mountersArr.push(el.mounters[0].name);
                            }

                        }
                    });
                    for(var z=0;z<mountersArr.length;z++){
                        mountersStr += '<div>'+mountersArr[z]+'<div>';
                    }
                    mountersStr += '</div>';
                    var value = (stage == 3) ? "S=" : "P=";
                    var style;
                    switch(true){
                        case elem.projects[j].calcs_count > elem.projects[j].mounters_count && elem.projects[j].mounters_count != 0:
                            style = 'style = "background:linear-gradient(135deg, white, yellow 100%);"';
                            break;
                        case elem.projects[j].calcs_count == elem.projects[j].mounters_count:
                            if(+elem.projects[j].status >= stage+29){
                                style = 'style = "background:linear-gradient(135deg, white, green 150%);"';
                            }
                            else{
                                style = 'style = "background:linear-gradient(135deg, white, #7B68EE 150%);"';
                            }
                            break;
                        default:
                            style= "";
                            break;
                    }
                    var calculations = elem.projects[j].calcs,
                        isDefect = checkDefect(calculations),
                        needApproveTaking = checkTaking(calculations);
                    var info_div ='';
                    if(isDefect || needApproveTaking){
                        info_div = '<div class="row">';
                        if(needApproveTaking == 1){
                            info_div += '<div class="col-md-6 left">'+NEED_TAKING_ICON+'</div>';
                        }
                        if(isDefect == 1){
                            info_div +="<div class='col-md-6 right'>"+DEFECT_ICON+"</div>";
                        }
                        else if(isDefect == 2){
                            info_div +="<div class='col-md-6 right'>"+DEFECT_FIXED_ICON+"</div>";
                        }
                        info_div += '</div>';
                    }
                    td =  info_div +
                        "<div class='row center project_href' data-project_id = '"+elem.projects[j].id+"'><div class='col-md-12'><b>" + elem.projects[j].title + "</b></div></div>" +
                        "<div class='row center' style='font-size:11pt;font-style:italic;'>" +
                        "<div class='col-md-5'>" +value+ val.toFixed(2) + "</div><div class='col-md-7'>(<span class='sum'>" + sum.toFixed(2) + "</span>) </div>" +
                        "</div>" +
                        "<div class='row center' style='margin-bottom: 5px'>" +
                        "<div class='col-md-12' name='mounter_div'><button name = 'btn_mounters'class='btn btn-primary btn-sm'>Монтажники</buuton></div>" +
                        "</div>"
                        + mountersStr;
                    td+= acceptDoneBtn;
                    jQuery("#report_table > tbody > tr:last").append('<td '+style+'mounter_select data-id="' + elem.projects[j].id + '">' + td + '</td>');
                    jQuery("#report_table > tbody > tr:last").find('[name="total_fl_sq"]')[0].innerHTML = value+(+floor_sq).toFixed(2);
                    jQuery("#report_table > tbody > tr:last").find('[name="total_fl_sum"]')[0].innerHTML = 'Сумма:'+(+floor_sum).toFixed(2);
                }
            }
            jQuery("[name='btn_mounters']").click(function (){
                jQuery("#mv_container").show();
                jQuery("#add_mounters").show("slow");
                jQuery("#close").show();
                jQuery("#all_calcs_mounter").empty();
                jQuery("#save_all_calcs_mounter").empty();
                var td = jQuery(this).closest('td'),
                    floorId = jQuery(this).closest('tr').data('id'),
                    projectId = td.data('id'),
                    project = progressData[floorId].projects.find(function(obj){return obj.id == projectId}),
                    calcs = project.calcs,
                    need_approve = checkTaking(calcs),
                    mounters,tr,
                    trAdd = '<div class="row">' +
                        '<div class="col-md-8" name = "mounter_div"><select class="input-gm" name ="mounter_select">'+mountersOption+'</select></div>' +
                        '<div class="col-md-4" name = "btn_div">'+ACCEPT_BUTTON+'</div>'+
                        '</div>',
                    trApprove = '<h4>Подтвердить ВСЕ запросы</h4>' +
                                '<div class="row">'+
                                    '<button class="btn btn-primary approve_all" data-floor_id ="'+floorId+'"data-project_id = "'+projectId+'">Подтвердить</button>'+
                                '</div>';
                jQuery("#all_calcs_mounter").append("<h4>Назначить бригаду на ВСЕ потолки</h4>");
                if(project.status < stage+29) {
                    jQuery("#all_calcs_mounter").append(trAdd);
                    jQuery("#all_calcs_mounter").append(trApprove);
                }
                else{
                    jQuery("#all_calcs_mounter").append("Этап выполнен, редактирование бригад невозможно!");
                }
                jQuery("#selected_floor").val(floorId);
                jQuery("#selected_project").val(projectId);

                fillMountersTable(project.status,stage,calcs);
            });
            console.log(temp_sums);
        }

        function fillMountersTable(projectStatus,stage,calcs){
            console.log(projectStatus,stage);
            console.log("calcs",calcs);

            var mounters,tr,
                trAdd = '<div class="row">' +
                    '<div class="col-md-8" name = "mounter_div"><select class="input-gm" name ="mounter_select">'+mountersOption+'</select></div>' +
                    '<div class="col-md-4" name = "btn_div">'+ACCEPT_BUTTON+'</div>'+
                    '</div>';
            jQuery("#calcsMounters > tbody").empty();
            jQuery.each(calcs,function(index,elem){
                jQuery("#calcsMounters > tbody").append('<tr/>');
                jQuery("#calcsMounters > tbody > tr:last").attr('data-calc_id',elem.id);
                mounters = elem.mounters;
                tr ='<td>'+elem.title+'</td><td>';
                if(mounters) {
                    mounters.forEach(function (el) {
                        tr += '<div class="row">' +
                            '<div class="col-md-8" name = "mounter_div">' + el.name + '</div>' +
                            '<div class="col-md-4" name ="btn_div">' + EDIT_BUTTON + '</div>' +
                            '</div>';
                    });
                }
                else{
                    tr+=trAdd;
                }
                tr+='</td>';
                tr +='<td>'+elem.sum+'</td>';
                tr += '<td>'+((elem.calc_status == 3) ? APPROVE_TAKING_BUTTON : DEFECT_FIXED_ICON)+'</td>';
                jQuery("#calcsMounters > tbody > tr:last").append(tr);
            });
        }

        function checkDefect(calcs){
            var result = 0;
            jQuery.each(calcs,function(index,elem){
                if(elem.defect_status == 1 || elem.defect_status == 2){
                    result = elem.defect_status;
                    return;
                }
            });
            return result;
        }

        function checkTaking(calcs){
            var result = false;
            jQuery.each(calcs,function(index,elem){
                if(elem.calc_status == 3){
                    result = true
                    return;
                }
            });
            return result;
        }
        function getReportData(stage){
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=clients.getInfoByFloors",
                data: {
                    dealerId: '<?php echo $client->dealer_id; ?>',
                    stage: stage
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    progressData = data;
                },
                error: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных"
                    });
                }
            });
        }

        function getBuilderCommonData(){
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=clients.getBuilderCommonData",
                data: {
                    builderId: '<?php echo $client->dealer_id; ?>'
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    console.log("common",data);
                    fillCommonTab(data);
                },
                error: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных"
                    });
                }
            });
        }
        function getCommonData(){
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=clients.getCommonInfo",
                data: {
                    dealerId: '<?php echo $client->dealer_id; ?>'
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    console.log(data);
                    jQuery("#common_perimiter").text(data.perimeter);
                    jQuery("#common_square").text(data.quadrature);
                    jQuery("#common_self_sum").text(data.self_sum + data.mount_sum);
                },
                error: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных"
                    });
                }
            });
        }
        function generateMountersOption(data){
            jQuery.each(data,function(index,element){
                mountersOption += "<option value='"+element.id+"'>"+element.name+"</option>";
            });
        }
        function getMounters(){
            var option;
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=users.getUserByGroup",
                data: {
                    group: 34
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    mountersForDelete = data;
                    generateMountersOption(data);
                },
                error: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        }

        function saveMounter(element) {
            var td = jQuery(element).closest('td'),
                mounterId = td.find('select').val(),
                mounterName = td.find('select option:selected').text(),
                stage = jQuery('.active.mount_stage').data("mount_type"),
                calcId = jQuery(element).closest('tr').data('calc_id'),
                floorId = jQuery("#selected_floor").val(),
                projectId = jQuery("#selected_project").val(),
                allCallcsMount = false,
                calcsId = [],
                project = progressData[floorId].projects.find(function(obj){
                    return obj.id == projectId
                }),
                calc = project.calcs[calcId];
            if (!calcId) {
                calcsId = Object.keys(project.calcs);
                var mounterSelect = jQuery("#all_calcs_mounter").find('select');
                mounterId = mounterSelect.val();
                mounterName = mounterSelect.find('option:selected').text();
                td = jQuery("#all_calcs_mounter");
                allCallcsMount = true;
            }
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=Calcs_mounts.updateMounter",
                data: {
                    calcId: calcId,
                    calcsId:calcsId,
                    stage: stage,
                    mounterId: mounterId
                },
                dataType: "json",
                async: false,
                success: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Сохранено!"
                    });
                    if(project.status >= stage + 29)
                    {
                        updateMounterSum(calc.title,calc.mounters[0].id,mounterId,projectId,calc.sum);
                    }
                    if(allCallcsMount){
                        var calcs = progressData[floorId].projects.find(function(obj) {return obj.id == projectId}).calcs;
                        jQuery.each(calcs,function(index,elem){
                            elem.mounters = [data];
                        });
                        //поменять данные в таблице
                        fillMountersTable(project.status,stage,calcs);

                    }else{
                        progressData[floorId].projects.find(function(obj) {
                            return obj.id == projectId
                        }).calcs[calcId].mounters = [data];
                    }

                    td.find("[name = 'mounter_div']")[0].innerHTML = "<input type='hidden' name='mounter_id' value =" + mounterId + ">" + mounterName;
                    td.find("[name = 'btn_div']").append(EDIT_BUTTON);
                    element.remove();
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });

        }

        function updateMounterSum(calcTitle,mounterId,new_mounter,project_id,sum){
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=mounterssalary.updateSumMounter",
                data: {
                    calcTitle: calcTitle,
                    mounterId: mounterId,
                    new_mounter: new_mounter,
                    project_id: project_id,
                    sum: sum

                },
                dataType: "json",
                async: false,
                success: function (data) {

                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка обновления!"
                    });
                }
            });
        }
        function saveSum(elem) {
            var floorId = jQuery(elem).closest('tr').data('id'),
                td = jQuery(elem.closest('td')),
                sum = td.find('.sum').text(),
                projectId = td.data('id'),
                stage = jQuery('.active.mount_stage').data("mount_type"),
                project = progressData[floorId].projects.find(function(obj){return obj.id == projectId}),
                calcs = project.calcs,
                data=[],
                mounterExist = true,
                refresh = (jQuery(elem).attr('name') == "refresh_btn") ? 1 : 0;
                jQuery.each(calcs, function (index, elem) {
                    if (elem.mounters) {
                        data.push({id: elem.id, title: elem.title, mounter: elem.mounters[0].id, sum: elem.sum});
                    }
                    else {
                        if(elem.name == "check_sum") {
                            mounterExist = false;
                        }
                    }
                });

            console.log( JSON.stringify(data));
            if(mounterExist){
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=MountersSalary.save",
                    data: {
                        calcs: JSON.stringify(data),
                        projectId: projectId,
                        stage: stage,
                        floorName: progressData[floorId].name,
                        refresh:refresh
                    },
                    dataType: "json",
                    async: false,
                    success: function (data) {
                        if(elem.name == "check_sum") {
                            elem.remove();
                            project.status = stage + 29;
                        }
                        else{
                            var n = noty({
                                timeout: 2000,
                                theme: 'relax',
                                layout: 'center',
                                maxVisible: 5,
                                type: "success",
                                text: "Обновлено!"
                            });
                        }
                    },
                    error: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            }
            else{
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Не выбрана бригада"
                });
            }
        }

        jQuery(document).ready(function ()
        {
            getCommonData();
            jQuery(jQuery("[name='stage']")[0]).attr("checked","true");
            getMounters();
            var firstTab = jQuery(jQuery(".mount_stage")[0]),
                stage = firstTab.data("mount_type"),
                status = firstTab.data("mount_status");
            firstTab.addClass('active');
            drawReportTable(stage);


            jQuery('#attach_check').change(function(){
                checks = this.files;
                console.log(checks);
            });

            jQuery("#btn_show_dop_costs").click(function(){
                jQuery("#mv_container").show();
                jQuery("#close").show();
                jQuery("#mw_view_dop_costs").show('slow');
            });

            jQuery('#show_goods').click(function(){
                jQuery("#mv_container").show();
                jQuery("#close").show();
                jQuery("#mw_common_goods").show('slow');
            });

            // обработка и отправка AJAX запроса при клике на кнопку upload_files
            jQuery('#save_dop_costs').click(function(){
                if( typeof checks == 'undefined' ) return;
                var data = new FormData();
                jQuery.each( checks, function( key, value ){
                    data.append( key, value );
                });
                data.append('checks_upload', 1 );
                data.append('cost_sum',jQuery("#dop_cost_sum").val());
                data.append('cost_comment',jQuery("#dop_cost_comment").val());
                data.append('builder_id','<?php echo $dealer->id?>');
                for (var p of data) {
                    console.log(p);
                }
                jQuery.ajax({
                     url: 'index.php?option=com_gm_ceiling&task=users.saveBuilderDopCosts',
                     type: 'POST',
                     data: data,
                     cache: false,
                     dataType: 'json',
                     processData: false,
                     contentType: false,
                     success: function(respond){
                         console.log('respond',respond);
                     },
                     error: function(respondData){
                         var n = noty({
                             timeout: 2000,
                             theme: 'relax',
                             layout: 'center',
                             maxVisible: 5,
                             type: "error",
                             text: "Ошибка сервера"
                         });
                     }
                 });

            });

            jQuery("#new_mounter_phone").mask('+7(999) 999-9999');

            jQuery("#btn_recalc").click(function(){
                var calcsId =[];
                jQuery.each(progressData,function(ind,el){
                    for(var i=0;i<el.projects.length;i++){
                        jQuery.each(el.projects[i].calcs,function(n,calc){
                            calcsId.push(calc.id);
                        });
                    }
                });
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=calculation.recalcMount",
                    data: {
                        calcs: JSON.stringify(calcsId),
                        dealer_id: '<?php echo $dealer->id?>'
                    },
                    method : 'POST',
                    dataType: "json",
                    async: false,

                    success: function(data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Успешно!"
                        });
                        setTimeout(location.reload(),10000);
                    },
                    error: function(data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            });
            jQuery(document).on('click','.project_href',function(){
                var projectId = jQuery(this).data('project_id');
                document.location.href = '/index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=precalc&id='+projectId;
            });

            jQuery("#create_mounter").click(function () {
                var name = jQuery("#new_mounter_name").val(),
                    phone = jQuery("#new_mounter_phone").val();
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=users.registerMounterForBuilding",
                    data: {
                        name: name,
                        phone: phone
                    },
                    dataType: "json",
                    async: false,
                    success: function(data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Добавлено!"
                        });
                        setTimeout(location.reload(),10000);
                    },
                    error: function(data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            })


            jQuery(".mount_stage").click(function () {
                var stage = jQuery(this).data("mount_type");
                drawReportTable(stage);
                jQuery(".sum_btn").click(function () {
                    saveSum(this);
                });
                jQuery("")
            });

            jQuery(".common_tab").click(function(){
                getBuilderCommonData();
            });

            jQuery(".sum_btn").click(function () {
                saveSum(this);
            });
            document.getElementById('calls-tar').scrollTop = 9999;
            jQuery('#jform_client_contacts').mask('+7(999) 999-9999');
            jQuery('#new_phone').mask('+7(999) 999-9999');

            jQuery("#send_comm").click(function(){
                var user_id = <?php echo $client->dealer_id; ?>;
                var type = null;
                if(jQuery(this).attr("c_type") == "msk"){
                    type = 2;
                }
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=sendCommercialOffer",
                    data: {
                        user_id: user_id,
                        email: jQuery("#email_comm").val(),
                        dealer_type: 7,
                        type:type
                    },
                    dataType: "json",
                    async: false,
                    success: function(data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Коммерческое предложение отправленно"
                        });
                        jQuery("#close").hide();
                        jQuery("#mv_container").hide();
                        jQuery("#modal_window_comm").hide();
                    },
                    error: function(data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            });

            document.getElementById('add_email').onclick = function()
            {
                var client_id = <?php echo $client->id; ?>;
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=addemailtoclient",
                    data: {
                        client_id: client_id,
                        email: document.getElementById('new_email').value
                    },
                    dataType: "json",
                    async: false,
                    success: function(data) {
                        location.reload();
                    },
                    error: function(data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            }

            document.getElementById('add_phone').onclick = function()
            {
                var client_id = <?php echo $client->id; ?>;
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=client.addPhone",
                    data: {
                        client_id: client_id,
                        phone: document.getElementById('new_phone').value
                    },
                    dataType: "json",
                    async: false,
                    success: function(data) {
                        location.reload();
                    },
                    error: function(data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            }
            if(document.getElementById('btn_refuse')) {
                document.getElementById('btn_refuse').onclick = function () {
                    var user_id = <?php echo $dealer->id; ?>;
                    jQuery.ajax({
                        url: "index.php?option=com_gm_ceiling&task=userRefuseToCooperate",
                        data: {
                            user_id: user_id,
                        },
                        dataType: "json",
                        async: false,
                        success: function (data) {
                            var n = noty({
                                timeout: 2000,
                                theme: 'relax',
                                layout: 'center',
                                maxVisible: 5,
                                type: "success",
                                text: "Переведен в отказ от сотрудничества"
                            });
                            setTimeout(function () {
                                location.href = '/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage';
                            }, 1000);
                        },
                        error: function (data) {
                            var n = noty({
                                timeout: 2000,
                                theme: 'relax',
                                layout: 'center',
                                maxVisible: 5,
                                type: "error",
                                text: "Ошибка сервера"
                            });
                        }
                    });
                }
            }

            jQuery('#copy_select').change(function(){
                var proj_id = this.value,
                    calcs = JSON.parse(projectsWithCalcsIds[proj_id].calcs);
                console.log(this.value);
                jQuery("#what_duplicate").empty();
                jQuery.each(calcs,function(index,elem){
                    var checkbox = "<input name = \"what_duplicate\"type=\"checkbox\" id=\"" + elem.calc_id + "\" class=\"inp-cbx\" value = \"" + elem.calc_id + "\" style=\"display: none\">\n" +
                        "<label for=\"" + elem.calc_id + "\" class=\"cbx\">\n" +
                        "<span>\n" +
                        "<svg width=\"12px\" height=\"10px\" viewBox=\"0 0 12 10\">\n" +
                        "<polyline points=\"1.5 6 4.5 9 10.5 1\"></polyline>\n" +
                        "</svg>\n" +
                        "</span>\n" +
                        "<span>" + elem.name + "</span>\n" +
                        "</label>";
                    jQuery("#what_duplicate").append(checkbox);
                });
                jQuery("#where_duplicate_calcs").empty();
                var html = '',
                    row='';
                for(var i=0,index,nextIndex;i<Object.keys(projectsWithCalcsIds).length;i++){
                   index = Object.keys(projectsWithCalcsIds)[i];
                   nextIndex = Object.keys(projectsWithCalcsIds)[i+1];
                    if(empty(row)){
                        row = '<div class="row" style="margin-left:5px;"><p><b>'+projectsWithCalcsIds[index].client_name+'</b></p>';
                    }
                   if(projectsWithCalcsIds[index].project_id != proj_id){
                       var checkbox = "<input name = \"where_duplicate\"type=\"checkbox\" id=\"" + projectsWithCalcsIds[index].project_id + "\" class=\"inp-cbx\" value = \"" + projectsWithCalcsIds[index].project_id + "\" style=\"display: none\">\n" +
                           "<label for=\"" + projectsWithCalcsIds[index].project_id + "\" class=\"cbx\">\n" +
                           "<span>\n" +
                           "<svg width=\"12px\" height=\"10px\" viewBox=\"0 0 12 10\">\n" +
                           "<polyline points=\"1.5 6 4.5 9 10.5 1\"></polyline>\n" +
                           "</svg>\n" +
                           "</span>\n" +
                           "<span>" +projectsWithCalcsIds[index].project_info + "</span>\n" +
                           "</label>";
                       if( !empty(projectsWithCalcsIds[nextIndex]) && projectsWithCalcsIds[nextIndex].client_id == projectsWithCalcsIds[index].client_id){
                           row += checkbox;
                       }
                       else{
                           row += checkbox +'</div>';
                           html += row;
                           row = '';
                       }

                   }
                }

                jQuery('#where_duplicate_calcs').append(html);
            });

            jQuery('#duplicate_selected').click(function(){
                var whatDuplicate = jQuery('[name="what_duplicate"]:checked'),
                    whereDuplicate = jQuery('[name="where_duplicate"]:checked'),
                    calcsIds = [],
                    projectsIds = [];
                jQuery.each(whatDuplicate,function(index,elem){
                    calcsIds.push(elem.value);
                });
                jQuery.each(whereDuplicate,function(index,elem){
                    projectsIds.push(elem.value);
                });
                if(empty(calcsIds)){
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Не выбраны потолки для дублирования"
                    });
                    return;
                }
                if(empty(projectsIds)){
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Не выбраны квартиры для дублирвоания!"
                    });
                    return;
                }
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=calculations.duplicate",
                    data: {
                        projects: projectsIds,
                        calcs: calcsIds
                    },
                    success: function(data) {
                        location.reload();
                    },
                    dataType: "json",
                    timeout: 10000,
                    error: function(data) {
                        var n = noty({
                            theme: 'relax',
                            timeout: 2000,
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при дублировании!"
                        });
                    }
                });
            });

        });


        jQuery("#back_btn").click(function (){
            history.go(-1);
        });

        jQuery("#add_comment").click(function ()
        {
            var comment = jQuery("#new_comment").val();
            var reg_comment = /[\\\<\>\/\'\"\#]/;
            var id_client = <?php echo $this->item->id; ?>;

            if (reg_comment.test(comment) || comment === "")
            {
                alert('Неверный формат примечания!');
                return;
            }

            add_history(id_client, comment);
        });

        jQuery("#but_call").click(function ()
        {
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_select_number").show("slow");
        });

        jQuery("#select_phones").change(function ()
        {
            var id_client = <?php echo $this->item->id; ?>;
            call(jQuery("#select_phones").val());
            add_history(id_client, "Исходящий звонок на " + jQuery("#select_phones").val().replace('+',''));
        });

        jQuery("#broke").click(function(){
            jQuery("#mv_container").show();
            jQuery("#call").show("slow");
            jQuery("#close").show();
        });

        jQuery("#add_call_and_submit").click(function(){
            client_id = <?php echo $this->item->id;?>;
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=changeCallTime",
                data: {
                    id:<?php echo $call_id;?>,
                    date: jQuery("#call_date").val(),
                    comment: jQuery("#call_comment").val()
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    add_history(client_id,"Звонок перенесен");
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Звонок сдвинут"
                    });

                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });
        jQuery("#add_call").click(function(){
            client_id = <?php echo $this->item->id;?>;
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addCall",
                data: {
                    id_client: client_id,
                    date: jQuery("#call_date_m").val(),
                    comment: jQuery("#call_comment_m").val()
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    add_history(client_id,"Добавлен звонок");
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Добавлен звонок"
                    });

                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });

        function add_history(id_client, comment)
        {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addComment",
                data: {
                    comment: comment,
                    id_client: id_client
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Добавленна запись в историю клиента"
                    });
                    setTimeout(function(){location.reload();}, 1000);
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка отправки"
                    });
                }
            });
        }

        function fillCommonTab(data){
            jQuery('#report_table > tbody').empty();
            jQuery.each(data,function(index,elem){
                jQuery('#report_table > tbody').append('<tr></tr>');
                jQuery('#report_table > tbody > tr:last').append('<td>' +
                                                                    '<div class="row"><b>'+elem.client_name+'</b></div>' +
                                                                    '<div class="row"><b>S=</b>'+elem.square+'</div>' +
                                                                    '<div class="row"><b>P=</b>'+elem.perimeter+'</div>' +
                                                                 '</td>');
                var projects = JSON.parse(elem.projects);
                jQuery.each(projects,function(ind,project){
                    jQuery('#report_table > tbody > tr:last').append('<td>' +
                                                                        '<div class="row"><b>'+project.name+'</b></div>' +
                                                                        '<div class="row"> <b>Статус:</b> '+project.status+'</div>' +
                                                                     '</td>');
                });
            });

        }
    </script>