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


?>
<script type="text/javascript">
	if (jQuery === 'undefined') {
		document.addEventListener("DOMContentLoaded", function (event) {
			jQuery('#form-mounter').submit(function (event) {
				
			});

			
			jQuery('input:hidden.dealer_id').each(function(){
				var name = jQuery(this).attr('name');
				if(name.indexOf('dealer_idhidden')){
					jQuery('#jform_dealer_id option[value="' + jQuery(this).val() + '"]').attr('selected',true);
				}
			});
					jQuery("#jform_dealer_id").trigger("liszt:updated");
		});
	} else {
		jQuery(document).ready(function () {
			jQuery('#form-mounter').submit(function (event) {
				
			});

			
			jQuery('input:hidden.dealer_id').each(function(){
				var name = jQuery(this).attr('name');
				if(name.indexOf('dealer_idhidden')){
					jQuery('#jform_dealer_id option[value="' + jQuery(this).val() + '"]').attr('selected',true);
				}
			});
					jQuery("#jform_dealer_id").trigger("liszt:updated");
		});
	}
</script>

<div class="mounter-edit front-end-edit">
	<?php if (!empty($this->item->id)): ?>
		<h1>Edit <?php echo $this->item->id; ?></h1>
	<?php else: ?>
		<h1>Add</h1>
	<?php endif; ?>

	<form id="form-mounter"
		  action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=mounter.save'); ?>"
		  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
		
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />

	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />

	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />

	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />

	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />

	<?php if(empty($this->item->created_by)): ?>
		<input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>" />
	<?php else: ?>
		<input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" />
	<?php endif; ?>
	<?php if(empty($this->item->modified_by)): ?>
		<input type="hidden" name="jform[modified_by]" value="<?php echo JFactory::getUser()->id; ?>" />
	<?php else: ?>
		<input type="hidden" name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>" />
	<?php endif; ?>
	<?php echo $this->form->renderField('team_title'); ?>

	<?php echo $this->form->renderField('mounter_contacts'); ?>

	<?php echo $this->form->renderField('dealer_id'); ?>

	<?php foreach((array)$this->item->dealer_id as $value): ?>
		<?php if(!is_array($value)): ?>
			<input type="hidden" class="dealer_id" name="jform[dealer_idhidden][<?php echo $value; ?>]" value="<?php echo $value; ?>" />
		<?php endif; ?>
	<?php endforeach; ?>
	<?php echo $this->form->renderField('mounter_margin'); ?>

	<?php echo $this->form->renderField('mp1'); ?>

	<?php echo $this->form->renderField('mp2'); ?>

	<?php echo $this->form->renderField('mp3'); ?>

	<?php echo $this->form->renderField('mp4'); ?>

	<?php echo $this->form->renderField('mp5'); ?>

	<?php echo $this->form->renderField('mp6'); ?>

	<?php echo $this->form->renderField('mp7'); ?>

	<?php echo $this->form->renderField('mp8'); ?>

	<?php echo $this->form->renderField('mp9'); ?>

	<?php echo $this->form->renderField('mp10'); ?>

	<?php echo $this->form->renderField('mp11'); ?>

	<?php echo $this->form->renderField('mp12'); ?>

	<?php echo $this->form->renderField('mp13'); ?>

	<?php echo $this->form->renderField('mp14'); ?>

	<?php echo $this->form->renderField('mp15'); ?>

	<?php echo $this->form->renderField('mp16'); ?>

	<?php echo $this->form->renderField('mp17'); ?>

	<?php echo $this->form->renderField('mt1'); ?>

	<?php echo $this->form->renderField('mt2'); ?>

	<?php echo $this->form->renderField('mt3'); ?>

	<?php echo $this->form->renderField('mt4'); ?>

	<?php echo $this->form->renderField('mt5'); ?>

	<?php echo $this->form->renderField('mt6'); ?>

	<?php echo $this->form->renderField('mt7'); ?>

	<?php echo $this->form->renderField('mt8'); ?>

	<?php echo $this->form->renderField('mt9'); ?>

	<?php echo $this->form->renderField('mt10'); ?>

	<?php echo $this->form->renderField('mt11'); ?>

	<?php echo $this->form->renderField('mt12'); ?>

	<?php echo $this->form->renderField('mt13'); ?>

	<?php echo $this->form->renderField('mt14'); ?>

	<?php echo $this->form->renderField('mt15'); ?>

	<?php echo $this->form->renderField('mt16'); ?>
				<div class="fltlft" <?php if (!JFactory::getUser()->authorise('core.admin','gm_ceiling')): ?> style="display:none;" <?php endif; ?> >
                <?php echo JHtml::_('sliders.start', 'permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
                <?php echo JHtml::_('sliders.panel', JText::_('ACL Configuration'), 'access-rules'); ?>
                <fieldset class="panelform">
                    <?php echo $this->form->getLabel('rules'); ?>
                    <?php echo $this->form->getInput('rules'); ?>
                </fieldset>
                <?php echo JHtml::_('sliders.end'); ?>
            </div>
				<?php if (!JFactory::getUser()->authorise('core.admin','gm_ceiling')): ?>
                <script type="text/javascript">
                    jQuery.noConflict();
                    jQuery('.tab-pane select').each(function(){
                       var option_selected = jQuery(this).find(':selected');
                       var input = document.createElement("input");
                       input.setAttribute("type", "hidden");
                       input.setAttribute("name", jQuery(this).attr('name'));
                       input.setAttribute("value", option_selected.val());
                       document.getElementById("form-mounter").appendChild(input);
                    });
                </script>
             <?php endif; ?>
		<div class="control-group">
			<div class="controls">

				<?php if ($this->canSave): ?>
					<button type="submit" class="validate btn btn-primary">
						<?php echo JText::_('JSUBMIT'); ?>
					</button>
				<?php endif; ?>
				<a class="btn"
				   href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=mounterform.cancel'); ?>"
				   title="<?php echo JText::_('JCANCEL'); ?>">
					<?php echo JText::_('JCANCEL'); ?>
				</a>
			</div>
		</div>

		<input type="hidden" name="option" value="com_gm_ceiling"/>
		<input type="hidden" name="task"
			   value="mounterform.save"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
