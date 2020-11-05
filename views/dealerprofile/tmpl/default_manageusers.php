<?php
    $user = JFactory::getUser();
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
<?= parent::getButtonBack(); ?>
<div class="row">
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
            <th class="center">
                <i class="fas fa-trash-alt"></i>
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
                <td>
                    <button class="btn btn-danger del_user"><i class="fas fa-trash-alt"></i></button>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var SAVE_BTN = '<button class="btn btn-primary save_dop_number"><i class="far fa-save"></i></button>';
        var availableNumbers = JSON.parse('<?=json_encode($availableNumbers)?>'),
            select = '<select class="form-conterol dop_number_select"><option value="">Убрать доп.номер</option>';
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
                        {у
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

        jQuery('.del_user').click(function(){
            noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'topCenter',
                maxVisible: 5,
                type: "warning",
                text: "Данная функция будет реализовано позднее!"
            });
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