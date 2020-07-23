<?php
$stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
$providers = $stockModel->getCounterparty();
?>
<style>
    .row{
        margin-bottom: 1em;
    }
</style>
<div class="row">
    <div class="col-md-8">
        <h3>Список поставщиков</h3>
    </div>
    <div class="col-md-4" style="text-align: right">
        <button class="btn btn-primary" id="new_provider"><i class="fas fa-user-plus"></i> Добавить</button>
    </div>
</div>

<table class="table table_cashbox" id="provider_list">
    <thead>
        <th>Поставщик</th>
        <th>Полное название</th>
        <th>Контактный телефон</th>
        <th>Контактный адрес эл.почты</th>
    </thead>
    <tbody>
        <?php foreach ($providers as $provider){?>
            <tr data-id="<?=$provider->id;?>">
                <td>
                    <?= $provider->name;?>
                </td>
                <td>
                    <?= $provider->full_name?>
                </td>
                <td>
                    <?= $provider->contacts_phone?>
                </td>
                <td>
                    <?= $provider->contacts_email?>
                </td>
            </tr>
        <?php }?>
    </tbody>
</table>
<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="mw_close">
        <i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
    </button>
    <div class="modal_window" id="mw_provider_info">
        <input type="hidden" id="provider_id">
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="name">Название</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите название" id="name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="full_name">Полное название</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите полное название" id="full_name">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="tin">ИНН</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите ИНН" id="tin">
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="cpr">КПП</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите КПП" id="cpr">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="ogrn">ОГРН</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите ОГРН" id="ogrn">
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="legal_address">Юридический адрес</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите юр. адрес" id="legal_address">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="mail_address">Почтовый адрес</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите почт. адрес" id="mail_address">
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="ceo">Ген. директор</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите ген. директор" id="ceo">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="bank_name">Наименование банка</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите наим. банка" id="bank_name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="pay_account">Расчетный счет</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите расчетный счет" id="pay_account">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="cor_account">Кор. счет</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите кор. счет" id="cor_account">
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="bic">БИК</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите БИК" id="bic">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="phone">Номер телефона</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите номер телефона" id="phone">
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="email">Адрес эл.почты</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" placeholder="Введите эл. почту" id="email">
                </div>
            </div>
        </div>
        <div class="row center">
            <div class="col-md-12">
                <button class="btn btn-primary" id="save_provider">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var providers = JSON.parse('<?= quotemeta(json_encode($providers))?>');
    jQuery(document).mouseup(function (e) {
        var div1 = jQuery("#mw_provider_info");
        if (!div1.is(e.target) && div1.has(e.target).length === 0) {
            jQuery("#mw_close").hide();
            jQuery("#mw_container").hide();
            div1.hide();
        }
    });
    jQuery(document).ready(function () {
        jQuery('body').on('click','tr',function(){
            var id = jQuery(this).data('id');
            jQuery('#provider_id').val(id);
            jQuery('#mw_container').show();
            jQuery('#mw_provider_info').show();
            jQuery('#mw_close').show();
            fillProviderData(id);
        });

        jQuery('#new_provider').click(function () {
            jQuery('#provider_id').val('');
            jQuery('#name').val('');
            jQuery('#full_name').val('');
            jQuery('#tin').val('');
            jQuery('#cpr').val('');
            jQuery('#ogrn').val('');
            jQuery('#legal_address').val('');
            jQuery('#mail_address').val('');
            jQuery('#ceo').val('');
            jQuery('#bank_name').val('');
            jQuery('#pay_account').val('');
            jQuery('#cor_account').val('');
            jQuery('#bic').val('');
            jQuery('#phone').val('');
            jQuery('#email').val('');
            jQuery('#mw_container').show();
            jQuery('#mw_provider_info').show();
            jQuery('#mw_close').show();
        });

        jQuery('#save_provider').click(function () {
            var data = {
                id: jQuery('#provider_id').val(),
                name: jQuery('#name').val(),
                full_name: jQuery('#full_name').val(),
                tin: jQuery('#tin').val(),
                cpr: jQuery('#cpr').val(),
                ogrn: jQuery('#ogrn').val(),
                legal_address: jQuery('#legal_address').val(),
                mailing_address: jQuery('#mail_address').val(),
                ceo: jQuery('#ceo').val(),
                bank_name: jQuery('#bank_name').val(),
                pay_account: jQuery('#pay_account').val(),
                cor_account: jQuery('#cor_account').val(),
                bic: jQuery('#bic').val(),
                contacts_phone: jQuery('#phone').val(),
                contacts_email:jQuery('#email').val()
            };
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=stock.addProvider",
                data: {
                    provider: JSON.stringify(data)
                },
                dataType: "json",
                async: true,
                success: function (response) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Данные сохранены!"
                    });
                    if(empty(data.id)){
                        data.id = response;
                        providers.push(data);
                        jQuery('#provider_list > tbody').append('<tr data-id="'+data.id+'">' +
                            '<td>'+data.name+'</td>' +
                            '<td>'+data.full_name+'</td>' +
                            '<td>'+data.contacts_phone+'</td>' +
                            '<td>'+data.contacts_email+'</td>' +
                            '</tr>');
                        jQuery('#mw_container').hide();
                        jQuery('#mw_provider_info').hide();
                        jQuery('#mw_close').hide();
                    }
                    else{
                        jQuery('#mw_container').hide();
                        jQuery('#mw_provider_info').hide();
                        jQuery('#mw_close').hide();
                        setTimeout(function () {
                            location.reload();
                        },2000);
                    }

                },
                error: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка добавления!"
                    });
                }
            });
        });
    });
    function fillProviderData(id) {
        var provider;
        jQuery.each(providers,function (i,p) {
            if(p.id == id){
                provider = p;
                return;
            }
        });
        if(!empty(provider)){
            jQuery('#name').val(provider.name);
            jQuery('#full_name').val(provider.full_name);
            jQuery('#tin').val(provider.tin);
            jQuery('#cpr').val(provider.cpr);
            jQuery('#ogrn').val(provider.ogrn);
            jQuery('#legal_address').val(provider.legal_address);
            jQuery('#mail_address').val(provider.mailing_address);
            jQuery('#ceo').val(provider.ceo);
            jQuery('#bank_name').val(provider.bank_name);
            jQuery('#pay_account').val(provider.pay_account);
            jQuery('#cor_account').val(provider.cor_account);
            jQuery('#bic').val(provider.bic);
            jQuery('#phone').val(provider.contacts_phone);
            jQuery('#email').val(provider.contacts_email);
        }

    }
</script>