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
$dealerId = $user->dealer_id;
$groups = $user->groups;
$isNMS = in_array('17', $groups) || in_array('12',$groups ) || in_array('14',$groups);

$model_mount = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$servicePrice = $model_mount->getServicePrice($dealerId);
$model_prices = Gm_ceilingHelpersGm_ceiling::getModel('prices');
$gm_price = $model_prices->getJobsDealer($dealerId);
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
        border: 1px solid rgba(0, 0, 0, .15);
        border-radius: .25rem;
    }

    .control-label {
        margin-top: 7px;
        margin-bottom: 0;
    }
</style>
<div class="row" style="margin-bottom: 1em;">
    <?=parent::getButtonBack();?>
</div>
<?php if ($isNMS || ($user->dealer_id == 1 && $user->dealer_type == 0)): ?>
    <div class="row">
        <div class="col-md-6">
            <button class="btn btn-primary generate_pdf" data-type="service">Открыть PDF-файл c прайсом МС</button>
        </div>
        <div class="col-md-6">
            <button class="btn btn-primary generate_pdf" data-type="mounter"> Открыть PDF-файл с прайсом монтажа ГМ</button>
        </div>
    </div>
    <div class="col-md-6">
        <h3>Редактирование прайса Монтажной Службы</h3>

        <div>
            <?php foreach ($servicePrice as $value) { ?>
                <div class="row" style="margin-top: 5px;">
                    <div class="col-md-2"></div>
                    <div class="col-md-5 control-label">
                        <label><?= $value->name; ?></label>
                    </div>
                    <div class="col-md-3 left">
                        <input type="text" class="required input" style="width:100%;" size="3"
                                                      required aria-required="true" value="<?= $value->price; ?>"
                                                      data-id="<?= $value->sp_id; ?>" data-job_id="<?= $value->id; ?>"/>
                    </div>
                    <div class="col-md-2"></div>
                </div>
            <?php } ?>
        </div>
        <?php if($isNMS){ ?>
            <div class="col-md-12" style="margin-top:15px;">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <button type="button" id="btn_save" class="btn btn-primary" style="width:100%;"> Сохранить</button>
                </div>
                <div class="col-md-4"></div>
            </div>
        <?php }?>
    </div>
    <div class="col-md-6">
        <h3>Редактирование прайса для монтажных бригад</h3>
        <div>
            <?php foreach ($gm_price as $value) { ?>
                <div class="row" style="margin-top: 5px;">
                    <div class="col-md-2"></div>
                    <div class="col-md-5 control-label"><label><?= $value->name; ?></label></div>
                    <div class="col-md-3 left">
                        <input type="text" class="required gm_input" style="width:100%;" size="3"
                               required aria-required="true" value="<?= $value->price; ?>"
                               data-id="<?= $value->id; ?>" data-job_id="<?= $value->id; ?>"/>
                    </div>
                    <div class="col-md-2"></div>
                </div>
            <?php } ?>
        </div>
        <?php if($isNMS){ ?>
            <div class="col-md-12" style="margin-top:15px;">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <button type="button" id="btn_save_gm" class="btn btn-primary" style="width:100%;"> Сохранить</button>
                </div>
                <div class="col-md-4"></div>
            </div>
        <?php }?>
    </div>
    <script>
        jQuery(document).ready(function () {
            document.getElementById('btn_save').onclick = function () {
                var dataToSave = collectDataTable('.input');
                saveData(dataToSave);
            };

            jQuery('#btn_save_gm').click(function () {
                var dataToSave = collectDataTable('.gm_input');
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=dealer.updatedata",
                    data: {
                        array: dataToSave,
                        dealer_id: 1
                    },
                    success: function (data) {
                        console.log(data);
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Данные сохранены!"
                        });
                    },
                    dataType: "json",
                    async: false,
                    timeout: 10000,
                    error: function (data) {
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

            });

            jQuery('.generate_pdf').click(function () {
                var pdf_type = jQuery(this).data('type');
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=prices.generatePricePDF",
                    data: {
                        pdf_type: pdf_type
                    },
                    success: function (data) {
                        window.open(data);
                    },
                    dataType: "json",
                    async: false,
                    timeout: 10000,
                    error: function (data) {
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
            });
        });

        function collectDataTable(class_name) {
            var data = [];
            jQuery.each(jQuery(class_name), function (index, value) {
                data.push({
                    job_id: jQuery(value).data('job_id'),
                    sp_id: jQuery(value).data('id'),
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
                success: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Прайс изменен!"
                    });
                },
                dataType: "json",
                async: false,
                timeout: 10000,
                error: function (data) {
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
<?php else: echo '<h4>У вас нет доступа к данной странице!</h4>';
endif; ?>