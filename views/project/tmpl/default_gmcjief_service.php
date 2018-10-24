<?php
/**
 * @package    Com_Gm_ceiling
 * @author     apbubnov <al.p.bubnov@gmail.com>
 * @copyright  2018 apbubnov
 */
// No direct access
defined('_JEXEC') or die;
?>
<?= parent::getButtonBack(); ?>
<?php if ($this->item) : ?>
	<div class="container">
        <div class="row">
            <div class="col-xl item_fields">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
            </div>
        </div>
    </div>
<?php endif;?>