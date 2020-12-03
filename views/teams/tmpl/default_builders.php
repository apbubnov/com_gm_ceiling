<?php
$usersModel  = Gm_ceilingHelpersGm_ceiling::getModel('users');
$usersInGroups = $usersModel->getUserInGroups(1,'34,39,40,41,42,43,44');
$userGroups = $usersModel->getGroupsByParentGroup(38);
?>
<style>
    .row{
        margin-bottom: 1em !important;
    }
    .group_title{
        border: #414099 2px solid;
        background: #d3d3f9;
        height: 3em;
        line-height: 2em;
    }

    .group_title span,i{
        vertical-align: middle;
    }

    .mounters{
        display: none;
    }

    .mounter_row{
        border: 1px solid #414099;
        height: 3em;
        line-height: 3em;
        margin: 0 0 5px 0 !important;
        padding: 0 !important;

    }
</style>
<div class="row">
    <div class="col-md-8 col-sm-12">
        <h4>
            Управленеи бригадами
        </h4>
    </div>
    <div class="col-md-4 col-xs-12 right">
        <button class="btn btn-primary" id="btn_add_new">
            <i class="fas fa-user-plus"></i> Добавить
        </button>
    </div>
</div>

<?php foreach($usersInGroups as $key => $group){?>
    <div class="mounter_group">
        <div class="row center  group_title" >
            <div class="col-md-11 col-xs-10">
                <span>
                    <?= $group->title;?>
                </span>
            </div>
            <div class="col-md-1 col-xs-2" style="text-align: right;">
                <i class="fas fa-angle-down"></i>
            </div>

        </div>
        <div class="mounters">
            <?php foreach($group->users as $user){ ?>
                <div class="row mounter_row" data-id="<?= $user->id;?>" data-group_id="<?= $group->id;?>">
                    <div class="col-md-4 col-xs-5">
                        <?= $user->name;?>
                    </div>
                    <div class="col-md-4 col-xs-5 center">
                        <?= $user->phone ?>
                    </div>
                    <div class="col-md-4 col-xs-2" style="text-align: right">
                        <button class="btn btn-sm btn-danger delete">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            <?php }?>
        </div>
    </div>

<?php }?>

<div class="modal_window_container" id="mw_container">
    <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="mw_add">
        <div class="row">
            <h4>
                Добавить новую бригаду
            </h4>
        </div>
        <div class="row">
            <div class="col-md-8 col-xs-12">
                <div class="col-md-4">
                    <div class="col-md-12">
                        <span>ФИО/Название</span>
                    </div>
                    <div class="col-md-12">
                        <input class="form-control" id="brigade_name">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="col-md-12">
                        <span>Номер телефона</span>
                    </div>
                    <div class="col-md-12">
                        <input class="form-control" id="phone">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="col-md-12">
                        <span>E-mail</span>
                    </div>
                    <div class="col-md-12">
                        <input class="form-control" id="email">
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xs-12" style="text-align: left;">
                <span><b>Выберите тип бригады: </b></span>
                <?php foreach ($userGroups as $userGroup) { ?>
                    <div class="col-md-12">
                    <input type="checkbox" id="g_<?=$userGroup->id?>" data-id="<?=$userGroup->id?>" class="inp-cbx groups" style="display: none">
                    <label for="g_<?=$userGroup->id?>" class="cbx">
                        <span>
                            <svg width="12px" height="10px" viewBox="0 0 12 10">
                                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                            </svg>
                        </span>
                        <span><?= $userGroup->title?></span>
                    </label>
                    </div>
                <?php }?>

            </div>
        </div>
        <div class="row center">
            <div class="col=md-12">
                <span style="color:red;display: none;" id="error_text"></span>
            </div>
            <div class="col-md-12">
                <button class="btn btn-primary" id="btn_save_brigade"> Добавить</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#mw_add");
        if (!div.is(e.target) &&
            div.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mw_container").hide();
            div.hide();
        }
    });

    jQuery(document).ready(function(){
        jQuery('#phone').mask('+7 (999) 999-99-99');
        jQuery('#btn_add_new').click(function(){
            jQuery('#mw_container').show();
            jQuery('#close').show();
            jQuery('#mw_add').show();
        });

        jQuery('#btn_save_brigade').click(function(){
            var name = jQuery('#brigade_name').val(),
                phone = jQuery('#phone').val(),
                email = jQuery('#email').val(),
                groupsChbx = jQuery('.groups:checked'),
                groupsId = [];
            jQuery.each(groupsChbx,function (n,el) {
                groupsId.push(jQuery(el).data('id'));
            });
            if(empty(name)){
                jQuery('#brigade_name').css('border-color','red');
                jQuery('#error_text').text('Не заполнено поле ФИО/Название!');
                jQuery('#error_text').show();
                return;
            }
            jQuery('#brigade_name').css('border-color','');
            if(empty(phone)){
                jQuery('#phone').css('border-color','red');
                jQuery('#error_text').text('Не заполнено поле Номер телефона!');
                jQuery('#error_text').show();
                return;
            }
            jQuery('#phone').css('border-color','');
            if(groupsId.length == 0){
                jQuery('#error_text').text('Не выбран тип бригады!');
                jQuery('#error_text').show();
                return;
            }
            jQuery('#error_text').text('');
            jQuery('#error_text').hide();

            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=users.registerMounterForBuilding",
                data: {
                    name: name,
                    phone: phone,
                    email: email,
                    groups: groupsId

                },
                success: function(data) {
                    location.reload();
                },
                dataType: "json",
                timeout: 10000,
                error: function(data) {
                    noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при создании!"
                    });
                }
            });

        });

        jQuery('.group_title').click(function(){
            var mountersDiv = jQuery(this).closest('.mounter_group').find('.mounters'),
                angle = jQuery(this).find('i');
            mountersDiv.toggle()
            if(mountersDiv.is(':visible')){
                angle.removeClass('fa-angle-down');
                angle.addClass('fa-angle-up');
            }
            else{
                angle.removeClass('fa-angle-up ');
                angle.addClass('fa-angle-down');
            }
        });

        jQuery('.delete').click(function(){
            var mounterRow = jQuery(this).closest('.mounter_row'),
                groupId = mounterRow.data('group_id') ,
                userId = mounterRow.data('id');
            noty({
                theme: 'relax',
                layout: 'center',
                timeout: false,
                type: "warning",
                text: "Выбранная бригада будет удалена из данной группы, продолжить?",
                buttons:[
                    {
                        addClass: 'btn btn-primary', text: 'Продолжить', onClick: function ($noty) {
                            deleteMouinterfromGroup(userId,groupId,mounterRow);
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
        });

        function deleteMouinterfromGroup(mounterId,groupId,row){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=users.deleteUserFromGroup",
                data: {
                    id: mounterId,
                    group: groupId
                },
                success: function(data) {
                    noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "succes",
                        text: "Удалено!"
                    });
                    row.remove();
                },
                dataType: "json",
                timeout: 10000,
                error: function(data) {
                    noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при удалении!"
                    });
                }
            });
        }
    });
</script>