<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_gm_ceiling', JPATH_SITE);
$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');

$jinput = JFactory::getApplication()->input;

?>

<style>
    body {
        color: #414099;
    }
    input, button {
        width: 100%;
    }
</style>

<?= parent::getButtonBack(); ?>
<div class="client-edit front-end-edit">
    <div style="margin-top: 15px;">
        <?php if (!empty($this->item->id)): ?>
            <h2 class="center">Изменить клиента</h2>
        <?php else: ?>
            <h2 class="center">Добавление нового клиента</h2>
        <?php endif; ?>
    </div>
    <div class="row" style="margin-top: 15px;">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <form id="form-client" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>
                <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>"/>
                <input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>"/>
                <input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>"/>
                <input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>"/>
                <?php if (empty($this->item->created_by)): ?>
                    <input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>"/>
                <?php else: ?>
                    <input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>"/>
                <?php endif; ?>
                <?php if (empty($this->item->modified_by)): ?>
                    <input type="hidden" name="jform[modified_by]" value="<?php echo JFactory::getUser()->id; ?>"/>
                <?php else: ?>
                    <input type="hidden" name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>"/>
                <?php endif; ?>
                <?php echo $this->form->renderField('client_name'); ?>
                <div style="margin-top: 10px;"></div>
                <?php echo $this->form->renderField('client_contacts'); ?>
                <div class="control-group" style="margin-top: 25px;">
                    <div class="controls">
                        <?php if ($this->canSave): ?>
                            <button type="submit" class="validate btn btn-primary">Сохранить клиента</button>
                        <?php endif; ?>
                    </div>
                </div>
                <input type="hidden" name="option" value="com_gm_ceiling"/>
                <input type="hidden" name="task" value="clientform.save"/>
                <?php echo JHtml::_('form.token'); ?>
            </form>
        </div>
        <div class="col-md-4"></div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#jform_client_contacts').mask('+7(999) 999-9999');
    });
</script>