<?php


?>
 <div class="modal_window_container" id="mw_container">
 	<button type="button" id="btn_close" class="btn-close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
 	<div id="mw_add_good" class="modal_window">
 		<div class="row">
 			<label for="good_category">
 				Категория:
 			</label>
 			<select class="input-gm" id="good_category">
 				<option>Выберите категорию</option>
 			</select>
 		</div>
 		<div class="row">
 			<label for="good_name">
 				Наименование:
 			</label>
 			<input id=good_name class="input-gm">
 		</div>
 		<div class="row">
 			<label for="good_count">
 				Количество:
 			</label>
 			<input id=good_count class="input-gm">
 		</div>
 	</div>
 </div>
<div class="container">
	<div class="row">
		<h1>Прием товаров</h1>
		<button type="button" class="btn btn-primary" id="add_position">
			<i class="fa fa-plus" aria-hidden="true"></i> Товар
		</button>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).mouseup(function (e){
		var div = jQuery("#mw_add_good");
    if (!div.is(e.target) && div.has(e.target).length === 0) { 
            jQuery("#btn_close").hide();
			jQuery("#mw_container").hide();
			div.hide();
		}
	});
	jQuery(document).ready(function(){
		jQuery("#add_position").click(function(){
			jQuery("#btn_close").show();
			jQuery("#mw_container").show();
			jQuery("#mw_add_good").show('slow');
		});
	});


</script>