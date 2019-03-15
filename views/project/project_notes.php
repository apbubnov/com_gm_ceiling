<?php
$notes = Gm_ceilingHelpersGm_ceiling::getProjectNotes($this->item->id);
?>
<div class="col-md-12">
    <?php foreach ($notes as $note){ ?>
    <div class="row" style="padding-bottom: 10px;">
        <div class="col-md-6"><b><?php echo $note->description;?></b></div>
        <div class="col-md-6"><?php echo $note->value;?></div>
    </div>
    <?php }?>
</div>
