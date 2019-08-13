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

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}

?>
<?=parent::getButtonBack();?>
<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>
	<?php $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations'); ?>
	<?php $calculations = $model->getProjectItems($this->item->id); ?>

	<div class="container">
	  <div class="row">
		<div class="item_fields">
			<h4>Информация по проекту № <?php echo $this->item->id ?></h4>
			<form id="form-client" action="/index.php?option=com_gm_ceiling" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
				<table class="table">
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
						<td>
                            <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00"){ ?> -
                            <?php } else {?>
							<?php $jdate = new JDate($this->item->project_calculation_date); ?>
							<?php echo $jdate->format('d.m.Y'); }?>
						</td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
						<td><?php echo $this->item->project_info; ?></td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
						<td><?php echo $this->item->client_id; ?></td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                        <? $contacts = $model->getClientPhone($this->item->client_id); ?>
                        <td><?php  foreach ($contacts as $phone) { echo $phone->client_contacts; echo "<br>"; }?></td>
					</tr>
				</table>
			</form>
		</div>
		<div class="">
			<h4>Информация для менеджера</h4>
            <table class="table">
                <?php
                $mount_model = Gm_ceilingHelpersGm_ceiling::getModel('mount');
                $mount = $mount_model->getDataAll();
                ?>
                <?php foreach($calculations as $calculation) { ?>
                    <tr>
                        <th><?php echo $calculation->calculation_title; ?></th>
                        <td>
                            <?php if( $calculation->n9 >= 4) $sum = $calculation->canvases_sum + $mount->mp21 * $calculation->n10 + $mount->mp22 * $calculation->n11 + $mount->mp20 * ($calculation->n9 - 4);
                            else $sum = $calculation->canvases_sum + $mount->mp21 * $calculation->n10 + $mount->mp22 * $calculation->n11;
                            ?>
                            <?php echo $calculation->canvases_sum;?> руб.
                        </td>
                        <td>
                            <?php $path = "/costsheets/" . md5($calculation->id . "manager") . ".pdf"; ?>
                            <?php if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>

                    </tr>
                <?php } ?>
            </table>
			<h4>Расходные материалы</h4>
            <table class="table">
                <?php $total_components_sum = 0;  $sum = 0;
                $total_perimeter = 0;
                //получаем прайс комплектующих
                $components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
                $components_list = $components_model->getFilteredItems();
                foreach($components_list as $i => $component) {
                    $components[$component->id] = $component;
                }
                foreach($calculations as $calculation) {
                    $total_perimeter+=$calculation->n5;
                }

                ?>

                <?php
                $sum = 0; $baget = 0;
                ?>
                <?php foreach($calculations as $calculation) { ?>
                    <?php $total_components_sum += $calculation->components_sum; ?>
                    <?php if( $calculation->n9 >= 4) $sum += $mount->mp21 * $calculation->n10 + $mount->mp22 * $calculation->n11 + $mount->mp20 * ($calculation->n9 - 4);
                    else $sum +=  $mount->mp21 * $calculation->n10 + $mount->mp22 * $calculation->n11;
                    ?>
                    <?php $baget = $calculation->n5 + $calculation->dop_krepezh / 2.0;
                    $baget_count = intval( $baget / 2.5 );
                    if (floatval( $baget / 2.5 ) > $baget_count) {
                        $baget_count++;
                    }
                    $baget = $baget_count * 2.5;
                    $baget2 += $components[11]->price * $baget;

                }

                foreach($calculations as $calculation) {
                    $new_baget += $calculation->n5 + $calculation->dop_krepezh / 2.0;
                }
                $baget_count = intval( $new_baget / 2.5 );
                if (floatval( $new_baget / 2.5 ) > $baget_count) {
                    $baget_count++;
                }
                $new_baget = $baget_count * 2.5;
                $itog = $components[11]->price * $new_baget;


                ?>
                <tr>
                    <th>Общая себестоимость расходников</th>
                    <td>

                        <?php echo  $total_components_sum  - $baget2 + $itog; ?> руб.
                    </td>
                    <td>
                        <?php $path = "/costsheets/" . md5($this->item->id . "consumables") . ".pdf"; ?>
                        <?php if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) { ?>
                            <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                        <?php } else { ?>
                            -
                        <?php } ?>
                    </td>
                </tr>

            </table>
			<h4>Наряды на монтаж</h4>
            <table class="table">
                <?php foreach($calculations as $calculation) { ?>
                    <tr>
                        <th><?php echo $calculation->calculation_title; ?></th>
                        <td>

                            <?php echo $calculation->mounting_sum ; ?> руб.
                        </td>
                        <td>
                            <?php $path = "/costsheets/" . md5($calculation->id . "mount_single") . ".pdf"; ?>
                            <?php if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
		</div>
	  </div>
	</div>
<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>

	
	<?php
else:
	echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
