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
$groups = $user->groups;

$chief = (in_array(23, $groups));
$employee = (in_array(18, $groups));
?>
<? if (!($chief || $employee)): ?>
    <h1>К сожалению данный кабинет вам не доступен!</h1>
    <p>Что бы получить доступ, обратитесь к IT отделу. Через <span>5</span> секунды вы вернетесь на предыдущую страницу!
    </p>
    <div style="display: none;"><?= parent::getButtonBack(); ?></div>
    <script type="text/javascript">
        var $ = jQuery;
        $(function () {
            $(".PRELOADER_GM").hide();
            setTimeout(function () {
                $("#BackPage").click();
            }, 5000);
            setInterval(function () {
                var span = $("p span"),
                    text = span.text();
                span.text(parseInt(text) - 1);
            }, 1000);
        });
    </script>
<?else:?>
<style>
    body {
        background-color: #E6E6FA;
    }
</style>

<?= parent::getPreloader(); ?>
<h2><?=$user->name;?></h2>

<div class="start_page">
    <h3>Цех</h3>
    <?if($chief):?>
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=guild&type=schedule', false, 2); ?>"><i class="fa fa-bars" aria-hidden="true"></i> Расписание</a>
    </p>
    <?endif;?>
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=guild&type=projects', false, 2); ?>"><i class="fa fa-bars" aria-hidden="true"></i> Раскрои</a>
    </p>
    <p class="center">
        <?= parent::getButtonBack(); ?>
    </p>
</div>
<?endif;?>