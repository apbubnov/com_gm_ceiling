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
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$model = $this->getModel();

$userId = $user->get('id');
$groups = $user->get('groups');


$app = JFactory::getApplication();
$type = $app->input->get('document', '', 'string');
$number = $app->input->get('number', 0, 'int');
$jcookie = $app->input->cookie;

$href = "http://" . $_SERVER['SERVER_NAME'] . '/files/stock/' . $type . '/' . $number . ".pdf";
?>
<?= parent::getPreloader(); ?>
<style>
    iframe {
        width: 100%;
        height: calc(100vh - 100px);
    }
    .buttons {
        width: 100%;
        height: 50px;
    }
</style>
<div class="buttons">
    <a href="<?=$href;?>" download="<?=$type." №".$number;?>">
    <button type="button" class="save btn btn-primary">Сохранить</button>
    </a>
    <button type="button" class="print btn btn-primary" onclick="jQuery('iframe')[0].contentWindow.print();">Распечатать</button>
    <button type="button" class="pageClose btn btn-primary" onclick="window.close();">Закрыть</button>
</div>
<iframe src="<?="http://" . $_SERVER['SERVER_NAME'] . '/files/stock/' . $type . '/' . $number . ".pdf";?>"></iframe>