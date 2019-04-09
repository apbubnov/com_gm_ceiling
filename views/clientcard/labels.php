<?php
	$model_clients = Gm_ceilingHelpersGm_ceiling::getModel('clients');
    $labels = $model_clients->getClientsLabels($user->dealer_id);
?>
<div class="row">
    <div class="col-md-2 col-xs-0"></div>
    <div class="col-md-2 col-xs-4"><label style="font-size: 16pt;">Ярлык: </label></div>
    <div class="col-md-5 col-xs-5">
        <select class="wide cust-select" id="select_client_label">
            <?php if (empty($this->item->label_id)) { ?>
                <option value="" selected disabled>Выберите ярлык</option>
            <?php } ?>
            <?php foreach($labels as $label):
                if ($label->id == $this->item->label_id) {
                    $current_label = $label;
                }
            ?>
                <option value="<?= $label->id; ?>" <?= $label->id == $this->item->label_id ? 'selected' : ''; ?>><?= $label->title; ?></option>
            <?php endforeach; ?>
        </select>
        <div class="nice-select wide" tabindex="0" style="--rcolor: #<?= !empty($current_label->id) ? $current_label->color_code : 'ffffff'; ?>;">
            <span class="current">
                <?php if (empty($this->item->label_id)) { ?>
                    Выберите ярлык
                <?php } else {
                    echo $current_label->title;
                } ?>
            </span>
            <ul class="list">
                <?php foreach($labels as $label): ?>
                    <li class="option" data-value="<?= $label->id; ?>" data-color="#<?= $label->color_code; ?>" style="--rcolor:#<?= $label->color_code; ?>"><?= $label->title; ?></li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-3 col-xs-3" style="padding: 0px;">
        <button class="btn btn-primary" id="btn_save_client_label" type="button">Ок</button>
    </div>
</div>
<script type="text/javascript">
    var client_id = '<?php echo $this->item->id;?>';
	document.getElementById('btn_save_client_label').onclick = function() {
        var label_id = document.getElementById('select_client_label').value;
        if (empty(label_id)) {
            var n = noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'topCenter',
                maxVisible: 5,
                type: "warning",
                text: "Выберите ярлык"
            });
            return;
        }
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=clients.saveClientLabel",
            data: {
                client_id: client_id,
                label_id: label_id
            },
            dataType: "json",
            async: false,
            success: function(data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "success",
                    text: "На клиента назначен ярлык"
                });
            },
            error: function(data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера"
                });
            }
        });
    };
    jQuery('#select_client_label').niceSelect();
    jQuery("#select_client_label").change(function() {
        var color = (jQuery(".option.selected").data("color"));
        jQuery('.nice-select.wide')[0].style.setProperty('--rcolor', color);
    });
</script>
