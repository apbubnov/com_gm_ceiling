<?php
$user = JFactory::getUser();
$dealersKeyModel = Gm_ceilingHelpersGm_ceiling::getModel('Dealers_Key');
$keys = $dealersKeyModel->getData($user->dealer_id);
$usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
$users = $usersModel->getDealerUsers($user->dealer_id);
$usedDopNumbers = [];
foreach ($users as $user) {
    if (!empty($user->dop_number)) {
        array_push($usedDopNumbers, $user->dop_number);
    }
}
include_once( $_SERVER['DOCUMENT_ROOT'].'/telephony_zadarma.php');
$result = TelephonyHelper::makePostRequest('/v1/pbx/internal/',null,true);
$allDopNumber = $result['numbers'];
$availableNumbers = array_diff($allDopNumber,$usedDopNumbers);


?>
<style>
    .row{
        margin-bottom: 1em !important;
    }
</style>
<?= parent::getButtonBack(); ?>
<h3>Интергация с телефонией Zadarma</h3>
<h4>Ключи API</h4>
<div class="row">
    <div class="col-md-6">
        <div class="col-md-4">Key</div>
        <div class="col-md-8">
            <input class="form-control" id="key" value="<?=$keys->key?>">
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">Secret</div>
        <div class="col-md-8">
            <input class="form-control" id="secret" value="<?=$keys->secret?>">
        </div>
    </div>
</div>
<div class="row center">
    <div class="col-md-12">
        <button id="save_keys" class="btn btn-primary">Сохранить ключи</button>
    </div>
</div>
<h4>Назначить внутренние номера пользователям</h4>
<div class="row">
    <div class="col-md-12">
        <table class="table table-stripped table_cashbox" id="table_users">
            <thead>
            <th class="center">
                ФИО
            </th>
            <th class="center">
                Группы
            </th>
            <th class="center">
                Внутр.номер
            </th>
            </thead>
            <?php foreach ($users as $user){?>
                <tr data-id="<?=$user->id?>">
                    <td>
                        <?=$user->name?>
                    </td>
                    <td>
                        <?=$user->groups;?>
                    </td>
                    <td>
                        <?php if(!empty($user->dop_number)){ ?>
                            <div class="col-md-10">
                                <?=$user->dop_number; ?>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary edit_dop_number"><i class="fas fa-edit"></i></button>
                            </div>
                        <?php }
                        else{?>
                            <div class="col-md-10">
                                <select class="form-control dop_number_select">
                                    <option value="">
                                        Выберите внутр.номер
                                    </option>
                                    <?php foreach ($availableNumbers as $number){?>
                                        <option value="<?=$number?>"><?=$number?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary save_dop_number"><i class="far fa-save"></i></button>
                            </div>
                        <?php }?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var SAVE_BTN = '<button class="btn btn-primary save_dop_number"><i class="far fa-save"></i></button>';
        var availableNumbers = JSON.parse('<?=json_encode($availableNumbers)?>'),
            apiKeys = JSON.parse('<?=json_encode($keys)?>'),
            select = '<select class="form-conterol dop_number_select"><option value="">Убрать доп.номер</option>';

        console.log(apiKeys);
        jQuery.each(availableNumbers,function (i,el) {
            select += '<option value="'+el+'">'+el+'</option>';
        });
        select += '</select>';
        jQuery('#table_users').on('click','.save_dop_number',function(){
            var row = jQuery(this).closest('tr'),
                userId = row.data('id'),
                dopNumber = row.find('.dop_number_select option:selected').val();
            if(empty(dopNumber)){
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: false,
                    type: "info",
                    text: "Дополнительный номер пользователя будет снят с данного пользователя. Продолжить?",
                    buttons: [
                        {
                            addClass: 'btn btn-primary', text: 'Да', onClick: function ($noty) {
                                updateDopNumber(userId,dopNumber);
                                $noty.close();
                            }
                        },
                        {
                            addClass: 'btn btn-primary', text: 'Отмена', onClick: function ($noty) {
                                $noty.close();
                            }
                        }
                    ]
                });
            }
            else{
                updateDopNumber(userId,dopNumber);
            }
        });

        jQuery('.edit_dop_number').click(function(){
            var td = jQuery(this).closest('td');
            td.empty();
            td.append('<div class="col-md-10">'+select+'</div><div class="col-md-2">'+SAVE_BTN+'</div>')
        });

        jQuery('#save_keys').click(function () {
            var key = jQuery('#key').val(),
                secret = jQuery('#secret').val(),
                dataToSave;
           if(!empty(key)&&!empty(secret)){
               if(!empty(apiKeys) && apiKeys.key != key && apiKeys.secret != secret){
                   dataToSave = {id: apiKeys.id, key: key, secret: secret};
               }
               else{
                   dataToSave = {key: key, secret: secret}
               }
               jQuery.ajax({
                   url: "index.php?option=com_gm_ceiling&task=dealer.saveApiKeys",
                   data: dataToSave,
                   dataType: "json",
                   async: true,
                   success: function (data) {
                       noty({
                           timeout: 2000,
                           theme: 'relax',
                           layout: 'topCenter',
                           maxVisible: 5,
                           type: "success",
                           text: "Сохранено!"
                       });
                       setTimeout(function(){
                           location.reload();
                       },2000);
                   },
                   error: function (data) {
                       noty({
                           timeout: 2000,
                           theme: 'relax',
                           layout: 'topCenter',
                           maxVisible: 5,
                           type: "error",
                           text: "Ошибка сервера"
                       });
                   }
               });

           }
        });
    });
    function updateDopNumber(userId,dopNum) {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=users.updateDopNumber",
            data: {
                userId: userId,
                number: dopNum
            },
            dataType: "json",
            async: true,
            success: function (data) {
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "success",
                    text: "Сохранено!"
                });
                setTimeout(function(){
                    location.reload();
                },2000);
            },
            error: function (data) {
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера"
                });
            }
        });
    }
</script>