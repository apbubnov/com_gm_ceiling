<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     CEH4TOP <CEH4TOP@gmail.com>
 * @copyright  2017 CEH4TOP
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$userGroup = $user->groups;
if (!(array_search('23', $userGroup) || array_search('18', $userGroup))) header('Location: '.$_SERVER['REDIRECT_URL']);
?>

<style>
    body {
        background-color: #E6E6FA;
    }
</style>

<?= parent::getPreloader(); ?>
<h2><?=$user->name;?></h2>

<div class="start_page">
    <h3>Цех</h3>
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=guild&type=schedule', false, 2); ?>"><i class="fa fa-bars" aria-hidden="true"></i> Расписание</a>
    </p>
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=guild&type=projects', false, 2); ?>"><i class="fa fa-bars" aria-hidden="true"></i> Раскрои</a>
    </p>
    <p class="center">
        <?= parent::getButtonBack(); ?>
    </p>
    В разработке
</div>