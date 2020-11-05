<?php
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$jinput = JFactory::getApplication()->input;
$user = JFactory::getUser();
$groups = $user->groups;

$model_mount = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$servicePrice = $model_mount->getServicePrice();
$modelPrices = Gm_ceilingHelpersGm_ceiling::getModel('prices');
$mountPrice = $modelPrices->getJobsDealer($user->dealer_id);
$dealerInfo = Gm_ceilingHelpersGm_ceiling::getDealerInfo($user->dealer_id);
$mountingMargin = $dealerInfo->dealer_mounting_margin;
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
<div class="row">
    <div class="col-md-6">
        <h3>Прайс монтажа для клиента по прайсу ГМ</h3>
        <div>
            <?php foreach ($mountPrice as $value) { ?>
                <div class="row" style="margin-top: 5px;">
                    <div class="col-md-2"></div>
                    <div class="col-md-5 control-label"><label><?= $value->name; ?></label></div>
                    <div class="col-md-3 left">
                        <input type="text" class="required input" readonly style="width:100%;" size="3"
                               required aria-required="true" value="<?=($value->price*100)/(100-$mountingMargin); ?>"
                               data-id="<?= $value->id; ?>" data-job_id="<?= $value->id; ?>"/>
                    </div>
                    <div class="col-md-2"></div>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="col-md-6">
        <h3>Прайс монтажа для клиента по прайсу Монтажной службы</h3>
        <div>
            <?php foreach ($servicePrice as $value) { ?>
                <div class="row" style="margin-top: 5px;">
                    <div class="col-md-2"></div>
                    <div class="col-md-5 control-label"><label><?= $value->name; ?></label></div>
                    <div class="col-md-3 left">
                        <input type="text" class="required input" readonly style="width:100%;" size="3"
                               required aria-required="true" value="<?=($value->price*100)/(100-$mountingMargin); ?>"
                               data-id="<?= $value->id; ?>" data-job_id="<?= $value->id; ?>"/>
                    </div>
                    <div class="col-md-2"></div>
                </div>
            <?php } ?>
        </div>

    </div>
</div>