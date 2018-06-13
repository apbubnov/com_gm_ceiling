<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
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

$user    = JFactory::getUser();
/*$canCreate  = $user->authorise('core.create', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'colorform.xml');
$canEdit    = $user->authorise('core.edit', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'colorform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gm_ceiling');*/
$canEdit = 1;

?>

<div class="color-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new Exception(JText::_('COM_GM_CEILING_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1>Изменить цвет <?php echo $this->item->title; ?></h1>
		<?php else: ?>
			<h1>Добавить цвет</h1>
		<?php endif;  ?>

		<form id="form-color"
			  action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=color.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />

	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />

	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />

	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />

	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	
				<?php echo $this->form->getInput('created_by'); ?>
				<?php echo $this->form->getInput('modified_by'); ?>
	<?php echo $this->form->renderField('color_title'); ?>


	<?php echo $this->form->renderField('color_canvas');?>

	<?php foreach((array)$this->item->color_canvas as $value): ?>
		<?php if(!is_array($value)): ?>
			<input type="hidden" class="color_canvas" name="jform[color_canvashidden][<?php echo $value; ?>]" value="<?php echo $value; ?>" />
		<?php endif; ?>
	<?php endforeach; ?>
	<?php echo $this->form->renderField('color_picker'); ?>
	<br>
	<table>
		<tr>
			<td><label>Глянец?</label></td>
			<td><input id= "glyanec" type="checkbox" name="glyanec" value="0"> </td>
		</tr>
		<tr>
			<td><label>Сатин?</label></td>
			<td><input id="satin" type="checkbox" name="satin" value="0"> </td>
		</tr>
	</table>
	
	
	<br>
	<button type="button" id = "create_img" class="validate btn btn-primary">Создать картинку</button>
	<br>
	<?php //echo $this->form->renderField('color_file'); ?>
	<br>
	<label>Картинка цвета</label>
	<br>
	<img id="color_file" src="/<?php echo $this->item->color_file; ?>" alt="" />
	
	<?php echo $this->form->renderField('color_hex');
	?>

			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							Сохранить
						</button>
					<?php endif; ?>
					<a class="btn"
					   href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=colorform.cancel'); ?>"
					   title="<?php echo JText::_('JCANCEL'); ?>">
						<?php echo JText::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_gm_ceiling"/>
			<input type="hidden" name="task"
				   value="colorform.save"/>
			<?php echo JHtml::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
<script>
jQuery( document ).ready(function(){
	jQuery('#jform_color_picker').change(function(){
		jQuery('#jform_color_hex').val(jQuery('#jform_color_picker').val().replace('#',''));
	});
	jQuery("#glyanec").change(function () {
		jQuery("#glyanec").prop("checked", this.checked);
		if(jQuery("#glyanec").is(':checked'))
			jQuery("#glyanec").val(1);
			else jQuery("#glyanec").val(0);
		jQuery("#satin").prop("checked", false);
		jQuery("#satin").val(0);
	});
	jQuery("#satin").change(function () {
		jQuery("#satin").prop("checked", this.checked);
		if(jQuery("#satin").is(':checked'))
			jQuery("#satin").val(1);
			else jQuery("#satin").val(0);
		jQuery("#glyanec").prop("checked", false);
		jQuery("#glyanec").val(0);
	});
		jQuery('#create_img').click(function(){
			console.log(color_name, color_code);
			color_name = jQuery('#jform_color_title').val();
			color_code =jQuery('#jform_color_hex').val();
			gl = jQuery('#glyanec').val();
			st = jQuery('#satin').val();
			jQuery.ajax({
				type: 'POST',
				url:"index.php?option=com_gm_ceiling&task=createColorImage",
				data: {
					code: color_code, 
					glyanec : gl,
					satin : st,
					col_name : color_name
				
				},
				success: function(data){
					jQuery("#color_file").attr("src",data);
				},
				dataType: "text",
				timeout: 10000,
				error: function(data){
					console.log(data);
					var n = noty({
						theme: 'relax',
						layout: 'center',
						maxVisible: 5,
						type: "error",
						text: "Ошибка при создании заказа. Сервер не отвечает"
					});
				}					
			});
		});
		
});
</script>