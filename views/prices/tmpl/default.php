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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');

$dealer = JFactory::getUser($user->dealer_id);

?>
<?php if (!in_array("14", $user->groups)) { ?>
    <?=parent::getButtonBack();?>
<?php } ?>


<div class="start_page">

<h2 class="center">Прайсы</h2>
<p class ="center"><a class ="btn btn-large btn-primary" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=canvases', false, 2); ?>" id ="canvases" style="width: 220px;"><i class="fas fa-edit" aria-hidden="true"></i> Прайс полотен</a></p>
<p class ="center"><a class ="btn btn-large btn-primary" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=components', false, 2); ?>" id ="components" style="width: 220px;"><i class="fas fa-edit" aria-hidden="true"></i> Прайс комплектующих</a></p>
<?php if($user->dealer_type!= 2 && !$user->guest){?>
<p class ="center"><a class ="btn btn-large btn-primary" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=mount', false, 2); ?>" id ="mount" style="width: 220px;"><i class="fas fa-edit" aria-hidden="true"></i> Прайс монтажа</a></p>
<?php }?> 
</div>