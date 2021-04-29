<?php
$notes = Gm_ceilingHelpersGm_ceiling::getProjectNotes($this->item->id);
$common_note = "";
?>
<div class="container">
    <div class="row center">
        <div class="col-md-12 col-xs-12">
            <b>Примечания</b>
        </div>
    </div>
    <?php foreach ($notes as $note){
        if($note->type==1){
            $common_note = $note->value;
        }?>
        <div class="row" style="padding-bottom: 10px;">
            <div class="col-md-6">
                <b><?php echo $note->description;?></b>
            </div>
            <div class="col-md-6">
                <?php echo $note->value;?>
            </div>
        </div>
    <?php }?>

    <?php if(!$storekeeper){?>
        <div class="row">
            <div class="col-md-4">
                <b>Ввести общее примечание к проекту:</b></div>
            <div class="col-md-6 col-xs-9">
                <textarea class="form-control"  id="textarea_note" style="width: 98%;"><?= $common_note?></textarea>
            </div>
            <div class="col-md-2 col-xs-3" style="text-align: right;">
                <button type="button" class="btn btn-primary" id="btn_add_note">Ок</button>
            </div>
        </div>
    <?php }?>
</div>

