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

$user       = JFactory::getUser();
$userId     = $user->get('id');

/* циферки на кнопки */
$model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
// график замеров
$answer1 = $model->getDataByStatus("GaugingsGraph");
//--------------------------------------

?>

<h2 class="center">Замерщик</h2>
<div class="start_page">
	<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
		<div class="container-for-circl">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=calendar', false); ?>"><i class="fa fa-calendar" aria-hidden="true"></i> График замеров</a>
			<?php if ($answer1[0]->count != 0) { ?>
				<div class="circl-digits"><?php echo $answer1[0]->count; ?></div>
			<?php } ?>
		</div>
	</div>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=projects', false); ?>"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> Запущенные</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=gaugers&type=chief', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Замерщики</a>
	</p>
</div>