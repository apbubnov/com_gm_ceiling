<?php
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$user = JFactory::getUser();

$model_clients = Gm_ceilingHelpersGm_ceiling::getModel('clients');
$labels = $model_clients->getClientsLabels($user->id);
?>

<div class="container">
	<!-- модальное окно -->
	<div class="modal_window_container" id="mw_container">
	    <button type="button" id="btn_close" class="btn-close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
	    <div id="mw_add_label" class="modal_window">
	        <p>Название ярлыка</p>
	        <p><input type="text" id="new_label_title" placeholder="Введите название" required></p>
	        <p>Цвет ярлыка</p>
	        <p><input type="text" id="new_label_color" readonly required></p>
	        <p>
	            <button type="button" id="btn_save_label" class="btn btn-primary">Сохранить</button>
	            <button type="button" id="btn_cancel" class="btn btn-danger">Отмена</button>
	        </p>
	    </div>
	</div>
	<h2>
		<div class="col-md-5 col-xs-12">
			<a class="btn btn-primary" href="/index.php?option=com_gm_ceiling&view=clients" id="back">
				<i class="fa fa-arrow-left" aria-hidden="true"></i> Назад
			</a>
		</div>
		<div class="col-md-5 col-xs-6">
			<label>Ярлыки</label>
		</div>
		<div class="col-md-2 col-xs-6">
			<button type="button" class="btn btn-primary" id="btn_add_label"><i class="fa fa-plus" aria-hidden="true"></i> Создать</button>
		</div>
	</h2>
	<table class="table">
		<thead>
			<tr><th>Название</th><th>Цвет</th></tr>
		</thead>
		<tbody>
			
		</tbody>
	</table>
</div>

<link rel="stylesheet" media="screen" type="text/css" href="/components/com_gm_ceiling/views/colors/colorPicker/css/colorpicker.css"/>
<script type="text/javascript" src="/components/com_gm_ceiling/views/colors/colorPicker/js/colorpicker.js"></script>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(document).mouseup(function(e) { // событие клика по веб-документу
	    var div1 = jQuery("#mw_add_label");
	    var div2 = jQuery(".colorpicker");
	    if ( (!div1.is(e.target) && div1.has(e.target).length === 0 &&
	    	  !div2.is(e.target) && div2.has(e.target).length === 0) ||
	    	 (jQuery("#btn_cancel").is(e.target) || jQuery("#btn_cancel").has(e.target).length > 0) ) {
	            jQuery("#btn_close").hide();
				jQuery("#mw_container").hide();
				jQuery("#mw_add_label").hide();
			}
	});
	jQuery("#btn_add_label").click(function () {
        jQuery("#btn_close").show();
        jQuery("#mw_container").show();
        jQuery("#mw_add_label").show("slow");
    });

    jQuery('#new_label_color').ColorPicker({
        color: '#000000',
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
    	jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=clients.saveClientLabel",
            async: false,
            data: {
                title: jQuery('#new_label_title').val(),
                color_code: jQuery('#new_label_color').val()
            },
            success: function(data){
                console.log(data);
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
    });

});

</script>
