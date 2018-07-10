<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
echo parent::getPreloaderNotJS();
// No direct access
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$id = $app->input->get('id', null, 'int');
$page = $app->input->get('page', null, 'string');
$ready = $app->input->get('ready', null, 'int');

$user = JFactory::getUser();
$userId = $user->get('id');
$userGroup = $user->groups;

$chief = (in_array(23, $groups));
$employee = (in_array(18, $groups));

$model = $this->getModel();
$cutModel = Gm_ceilingHelpersGm_ceiling::getModel("calculation");
$data = (empty($id))?null:$cutModel->getData($id);
?>

<?if(!(empty($id) || empty($data)) && $page != "cut"):
    $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
    $color_filter = "";
    $canvas = $canvases_model->getFilteredItemsCanvas("a.id = $data->n3_id")[0];
    if(!empty($canvas->color_id)){
        $color_filter = "and color_id = $canvas->color_id";
    }
    $canvases = $canvases_model->getFilteredItemsCanvas("texture_id = $canvas->texture_id AND manufacturer_id = $canvas->manufacturer_id and count>0 $color_filter");
    $widths = [];
    foreach ($canvases as $item) {
        $widths[] = (object)array("width" =>$item->width*100,"price" => $item->price);
    }

    usort($widths, function($obj_a,$obj_b){
        return ($obj_a > $obj_b) ? -1 : 1;
    });

    ?>
<form action="/sketch/cut_redactor_2/index.php" id="data_form" method="POST" style="display : none;">
    <input type="hidden" name="walls" id="input_walls" value="<?=$data->original_sketch;?>">
    <input type="hidden" name="calc_id" id="calc_id" value="<?=$data->id;?>">
    <input type="hidden" name="proj_id" id="proj_id" value="<?=$data->project_id;?>">
    <input type="hidden" name="width" id="width" value=<?=json_encode($widths);?>>
    <input type="hidden" name="texture" id="texture" value="<?=$canvas->texture_id;?>">
    <input type="hidden" name="manufacturer" id="manufacturer" value="<?=$canvas->manufacturer_id;?>">
    <input type="hidden" name="color" id="color" value="<?=$canvas->color_id;?>">
    <input type="hidden" name="page" id="page" value="guild">
</form>

<script type="text/javascript">
    var $ = jQuery;
    $(document).ready(function () {
        console.log($("#data_form").serialize());
        $("#data_form").submit();
    });
</script>
<?elseif ($page == "cut"):?>
<script type="text/javascript">
    var $ = jQuery();
    if (<?=$ready;?> === 0)
        top.postMessage('close', '*');
    else
        top.postMessage('update', '*');
</script>
<?endif;?>
