<?php
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$user = JFactory::getUser();

$model_clients = Gm_ceilingHelpersGm_ceiling::getModel('clients');
$labels = $model_clients->getClientsLabels($user->dealer_id);
?>

<style type="text/css">
    .color-div {
        width: 60px;
        height: 30px;
    }
</style>

<div class="container">
	<!-- модальное окно -->
	<div class="modal_window_container" id="mw_container">
	    <button type="button" id="btn_close" class="btn-close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
	    <div id="mw_add_label" class="modal_window">
	        <p>Название ярлыка</p>
	        <p><input type="text" id="new_label_title" placeholder="Введите название" required></p>
	        <p>Цвет ярлыка</p>
	        <p><input type="text" id="new_label_color" placeholder="Нажмите для выбора цвета" readonly required></p>
	        <p>
	            <button type="button" id="btn_save_label" class="btn btn-primary">Сохранить</button>
	            <button type="button" id="btn_cancel" class="btn btn-danger">Отмена</button>
	        </p>
            <hr>
	    </div>
	</div>
	<div class="row">
		<div class="col-md-5 col-xs-12">
			<a class="btn btn-primary" href="/index.php?option=com_gm_ceiling&view=clients" id="back">
				<i class="fa fa-arrow-left" aria-hidden="true"></i> Назад
			</a>
		</div>
		<div class="col-md-5 col-xs-6">
			<h2>Ярлыки</h2>
		</div>
		<div class="col-md-2 col-xs-6">
			<button type="button" class="btn btn-primary" id="btn_add_label"><i class="fa fa-plus" aria-hidden="true"></i> Создать</button>
		</div>
	</div>
	<table class="table">
		<thead>
			<tr><th>Название</th><th>Цвет</th><th></th><th></th></tr>
		</thead>
		<tbody id="tbody_labels">
			<?php foreach ($labels as $label) { ?>
                <tr>
                    <td><?= $label->title; ?></td>
                    <td><div class="color-div" style="background-color: #<?= $label->color_code; ?>;"></div></td>
                    <td>
                        <button class="btn btn-sm btn-primary btn_edit_label" data-id="<?= $label->id; ?>" data-title="<?= $label->title; ?>" data-color="<?= $label->color_code; ?>">
                            <i class="fa fa-edit"></i>
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger btn_delete_label" data-id="<?= $label->id; ?>">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php } ?>
		</tbody>
	</table>
</div>

<link rel="stylesheet" media="screen" type="text/css" href="/components/com_gm_ceiling/views/colors/colorPicker/css/colorpicker.css"/>
<script type="text/javascript" src="/components/com_gm_ceiling/views/colors/colorPicker/js/colorpicker.js"></script>
<script type="text/javascript">

var newLabelId = 0;

jQuery(document).ready(function() {
	jQuery(document).mouseup(function(e) {
	    if ( (jQuery("#btn_cancel").is(e.target) || jQuery("#btn_cancel").has(e.target).length > 0) ||
	    	 (jQuery("#btn_close").is(e.target) || jQuery("#btn_close").has(e.target).length > 0) ) {
	            jQuery("#btn_close").hide();
				jQuery("#mw_container").hide();
				jQuery("#mw_add_label").hide();
			}
	});
	jQuery("#btn_add_label").click(function() {
        newLabelId = 0;
        jQuery('#new_label_title').val('');
        jQuery('#new_label_color').val('');
        jQuery("#btn_close").show();
        jQuery("#mw_container").show();
        jQuery("#mw_add_label").show("slow");
    });

    jQuery('.btn_edit_label').click(editClick);
    jQuery('.btn_delete_label').click(deleteClick);

    function editClick() {
        newLabelId = jQuery(this).data('id')-0;
        jQuery('#new_label_title').val(jQuery(this).data('title'));
        jQuery('#new_label_color').val(jQuery(this).data('color'));
        jQuery("#btn_close").show();
        jQuery("#mw_container").show();
        jQuery("#mw_add_label").show("slow");
    }

    function deleteClick() {
        thisBtn = this;
        labelId = jQuery(this).data('id')-0;
        noty({
            theme: 'relax',
            layout: 'center',
            timeout: false,
            type: "info",
            text: "Удалить ярлык?",
            buttons: [
                {
                    addClass: 'btn btn-primary', text: 'Да', onClick: function($noty) {
                        jQuery.ajax({
                            url: "index.php?option=com_gm_ceiling&task=clients.deleteLabel",
                            data: {
                                label_id: labelId
                            },
                            dataType: "json",
                            async: false,
                            success: function(data) {
                                thisBtn.closest('tr').remove();
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
                        $noty.close();
                    }
                },
                {
                    addClass: 'btn btn-danger', text: 'Отмена', onClick: function($noty) {
                        $noty.close();
                    }
                }
            ]
        });
    }

    jQuery('#new_label_color').ColorPicker({
        onShow: function (colpkr) {
            jQuery(colpkr).fadeIn(500);
            return false;
        },
        onHide: function (colpkr) {
            jQuery(colpkr).fadeOut(500);
            return false;
        },
        onChange: function (hsb, hex, rgb) {
            jQuery('#new_label_color').val(hex);
        }
    });

    jQuery('#btn_save_label').click(function() {
        saveLabel(newLabelId);
    });

    function saveLabel(labelId) {
        var label_title = jQuery('#new_label_title').val();
        var label_color = jQuery('#new_label_color').val();
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=clients.saveClientLabel",
            async: false,
            data: {
                label_id: labelId,
                title: label_title,
                color_code: label_color
            },
            success: function(data){
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Ярлык сохранен"
                });
                if (empty(labelId)) {
                    var tr, td, btn;
                    tr = document.getElementById('tbody_labels').insertRow();

                    td = tr.insertCell();
                    td.innerHTML = label_title;

                    td = tr.insertCell();
                    td.innerHTML = '<div class="color-div" style="background-color: #'+label_color+';"></div>';

                    td = tr.insertCell();
                    btn = document.createElement('button');
                    td.appendChild(btn);
                    btn.classList.add('btn');
                    btn.classList.add('btn-sm');
                    btn.classList.add('btn-primary');
                    btn.classList.add('btn_edit_label');
                    btn.setAttribute('data-id', data.insertId);
                    btn.setAttribute('data-title', label_title);
                    btn.setAttribute('data-color', label_color);
                    btn.innerHTML = '<i class="fa fa-edit"></i>';
                    btn.onclick = editClick;
                    
                    td = tr.insertCell();
                    btn = document.createElement('button');
                    td.appendChild(btn);
                    btn.classList.add('btn');
                    btn.classList.add('btn-sm');
                    btn.classList.add('btn-danger');
                    btn.classList.add('btn_delete_label');
                    btn.setAttribute('data-id', data.insertId);
                    btn.innerHTML = '<i class="fa fa-trash"></i>';
                    btn.onclick = deleteClick;

                    jQuery("#btn_close").hide();
                    jQuery("#mw_container").hide();
                    jQuery("#mw_add_label").hide();
                } else {
                    location.reload();
                }
            },
            dataType: "json",
            timeout: 20000,
            error: function(data){
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сохранения ярлыка"
                });
            }                   
        });
    }

});

</script>
