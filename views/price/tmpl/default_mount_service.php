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

$jinput = JFactory::getApplication()->input;
$user = JFactory::getUser();



$model_mount = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$servicePrice = $model_mount->getServicePrice();
?>


<style>
    body {
        color: #414099;
    }
    .caption1 {
        text-align: center;
        padding: 15px 0;
        margin-bottom: 0;
        color: #414099;
    }
    .caption2 {
        text-align: center;
        height: auto;
        padding: 10px 0;
        border: 0;
        margin-bottom: 0;
        color: #414099;
    }
    input[type="text"] {
        padding: .5rem .75rem;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: .25rem;
    }
    .control-label {
        margin-top: 7px;
        margin-bottom: 0;
    }
</style>


<h3 class="caption1">Редактирование прайса Монтажной Службы</h3>

<div>
<?php foreach ($servicePrice as $value) { ?>
    <div class="row" style="margin-top: 5px;">
        <div class="col-md-2"></div>
        <div class="col-md-5 control-label" > <label><?= $value->name;?></label> </div>
        <div class="col-md-3 left" > <input type="text" class="required input" style="width:100%;" size="3" required aria-required="true" value="<?= $value->price;?>" data-id="<?= $value->id;?>" id="<?= $value->id;?>"/>
        </div>
        <div class="col-md-2"></div>
    </div>
<?php } ?>
</div>


<div  class = "col-md-12" style="margin-top:15px;">
    <div class = "col-md-4"></div>
    <div class = "col-md-4">
        <button type="button" id="btn_save" class="btn btn-primary" style="width:100%;"> Сохранить </button>
    </div>
    <div class = "col-md-4"></div>
</div>
<script>
    jQuery(document).ready(function(){
        document.getElementById('btn_save').onclick = function() {
            var dataToSave = collectDataTable();
            saveData(dataToSave);
        };
    });

    function collectDataTable(){
        var data = [];
        jQuery.each(jQuery('.input'), function(index, value){
            data.push({
                job_id: value.id,
                price: value.value.replace(',', '.').replace(/[^\d\.]/g, '')
            });
        });
        return data;

    }

    function saveData(data) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=prices.updateServicePrice",
            data: {
                price: JSON.stringify(data)
            },
            success: function(data){
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Прайс изменен!"
                });
            },
            dataType:"json",
            async: false,
            timeout: 10000,
            error: function(data){
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }
        });
    }


</script>