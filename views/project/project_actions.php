<?php
$status = $this->item->project_status;
$status_attr = "data-status = \"$status\"";
?>
<style>
    .act_btn {
        width: 210px;
        margin-bottom: 10px;
    }

    .save_bnt {
        width: 250px;
        height: 60px;
    }

    .btn_edit {
        position: absolute;
        top: 0px;
        right: 0px;
    }

    .row {
        margin-bottom: 5px;
    }

    .manuf_div {
        height: 80px;
        border: 2px solid grey;
        border-radius: 5px;
        margin-bottom: 5px;
    }

    .manuf_div.selected {
        border: 2px solid #414099;
        background-color: #d3d3f9;
    }

    .text-left {
        text-align: left;
    }
</style>
<input name="ref_note_type" id="ref_note_type" value="" type="hidden"></input>
<div class="container">
    <div class="row center">
        <div class="col-md-6" style="padding-top: 25px;" align="center">
            <div class="col-md-12" style="margin-bottom: 1em;">
                <button class="btn btn-primary act_btn" type="button" id="accept_project">
                    Договор/производство
                </button>
            </div>
            <div class="col-md-12">
                <?php if (in_array($this->item->project_status, [0, 1])) { ?>
                    <button class="btn btn-primary act_btn" id="rec_to_mesure" type="button">
                        <?= $this->item->project_status == 0 ? 'Записать на замер' : 'Перенести замер'; ?>
                    </button>
                <?php } elseif (in_array($this->item->project_status, [2, 3, 15])) { ?>
                    <button class="btn btn-primary act_btn" id="return_project" type="button">
                        Вернуть в работу
                    </button>
                <?php } ?>
            </div>
        </div>
        <div class="col-md-6" style="padding-top: 25px; margin-bottom: 1em;" align="center">
            <div class="col-md-12" style="margin-bottom: 1em;">
                <button id="ref_btn" class="btn btn-danger act_btn" type="button">Отказ</button>

                <div id=refuse_block align="left" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="col-md-12">
                                <input id='refuse_measure' type='radio' class="radio" name='slider-refuse'
                                       data-status='2' data-note_type="7">
                                <label for='refuse_measure'>от замера</label>
                            </div>
                            <div class="col-md-12">
                                <input id='refuse_deal' type='radio' class="radio" name='slider-refuse'
                                       data-status='3' data-note_type="3">
                                <label for='refuse_deal'>от договора</label>
                            </div>
                            <div class="col-md-12">
                                <input id='refuse_coop' type='radio' class="radio" name='slider-refuse'
                                       data-status='15' data-note_type="8">
                                <label for='refuse_coop'>от сотрудничества</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="ref_note_block" >
                            <textarea name="ref_note" class="form-control" id="ref_note" placeholder="Примечание"
                                      aria-invalid="false" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="row center">
                        <div class="col-md-12">
                            <button class="btn btn-primary" id="move_to_ref_btn" type="button">Перевести в отказ</button>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-md-12">
                <button id="save_btn" class="btn btn-primary act_btn" type="button">Сохранить</button>
            </div>
        </div>
    </div>

    <div class="project_activation" id="project_activation" style="display: none;">
        <div class="row center">
            <div class="col-md-6">
                <h4>Назначить дату монтажа</h4>
                <div id="calendar_mount" align="center"></div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <h4>Назначить дату готовности полотен</h4>
                    <div class="row" style="padding-bottom: 5px;">
                        <div class="col-md-3 text-left">
                            <b>Все потолки</b>
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" id="all_calcs" name="runByCallAll" class="inp-cbx"
                                   style="display: none">
                            <label for="all_calcs" class="cbx">
                                            <span>
                                                <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                </svg>
                                            </span>
                                <span>По звонку</span>
                            </label>
                        </div>
                        <div class="col-md-6 left">
                            <input type="datetime-local" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}"
                                   name="date_all_canvas_ready" class="input-gm">
                        </div>
                    </div>
                    <?php
                    if(empty($calculations)){
                        $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
                        $calculations = $calculationsModel->new_getProjectItems($this->item->id);
                    }
                    foreach ($calculations as $calculation) {
                        $byCall = '';
                        $runDate = '';
                        if($calculation->run_by_call){
                            $byCall = 'checked';
                        }
                        if(!empty($calculation->run_date)){
                            $date=date_create($calculation->run_date);
                            $runDate = date_format($date,"Y-m-d").'T'.date_format($date,"h:i");
                        }
                        ?>
                        <div class="row" style="padding-bottom: 5px;">
                            <div class="col-md-3 text-left">
                                <?php echo $calculation->calculation_title; ?>
                            </div>
                            <div class="col-md-3">
                                <input type="checkbox" data-calc_id="<?php echo $calculation->id ?>"
                                       id="<?php echo "cid" . $calculation->id ?>" name="runByCall" class="inp-cbx"
                                       style="display: none" <?=$byCall;?>>
                                <label for="<?php echo "cid" . $calculation->id ?>" class="cbx">
                                        <span>
                                            <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                            </svg>
                                        </span>
                                    <span>По звонку</span>
                                </label>
                            </div>
                            <div class="col-md-6 left">
                                <input type="datetime-local"
                                       data-calc_id="<?php echo $calculation->id ?>" name="date_canvas_ready"
                                       class="input-gm" value="<?=$runDate?>">
                            </div>

                        </div>
                    <?php } ?>
                    <div class="row">
                        <button class="btn btn-primary" id="btn_ready_date_вave" type="button">Сохранить дату</button>
                    </div>
                </div>
                <div class="row">
                    <h4> Ввести примечания</h4>
                    <div id="comments_divs">
                        <div class="row">
                            <div class="col-md-6 text-left">
                                <b><label for="jform_production_note">Примечание в производство</label></b>
                            </div>
                            <div class="col-md-6">
                                    <textarea name="production_note" class="input-gm" id="jform_production_note"
                                              placeholder="Примечание в производство" aria-invalid="false"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 text-left">
                                <b><label for="jform_mount_note">Примечание к монтажу</label></b>
                            </div>
                            <div class="col-md-6">
                                    <textarea name="mount_note" id="jform_mount_note" class="input-gm"
                                              placeholder="Примечание к монтажу" aria-invalid="false"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 text-left">
                                <b><label id="jform_ref_note-lbl" for="jform_refuse_note" class="">Примечание к
                                        незапускаемым потолкам(если есть)</label></b>
                            </div>
                            <div class="col-md-6">
                                    <textarea name="refuse_note" id="jform_refuse_note" class="input-gm"
                                              placeholder="Примечание к незапускаемым потолкам"
                                              aria-invalid="false"></textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <hr>
        <div class="row center">
            <div class="col-md-4" style="padding-top: 25px;">
                <button class="validate btn btn-primary save_bnt" id="save" type="button" from="form-client">Сохранить и
                    запустить <br> в производство ГМ
                </button>
            </div>
            <div class="col-md-4" style="padding-top: 25px;">
                <button class="validate btn btn-primary save_bnt" id="save_email" type="button" from="form-client">
                    Сохранить и запустить <br> в производство по email
                </button>
            </div>
            <div class="col-md-4" style="padding-top: 25px;">
                <button class="validate btn btn-primary save_bnt" id="save_exit" type="submit" from="form-client">
                    Сохранить в договоры<br> и выйти
                </button>
            </div>
        </div>
    </div>
</div>
<!--Всплывающие окна-->
<div class="modal_window_container" id="container_mw">
    <button type="button" class="close_btn" id="mw_close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
    </button>
    <div id="mw_measures_calendar" class="modal_window"></div>
    <div id="mw_mounts_calendar" class="modal_window"></div>
    <div id="modal_window_by_email" class="modal_window">
        <h4>Выберите производство</h4>
        <div id="manufacturer_list" class="container">

        </div>
        <div class="row">
            <button class="btn btn-primary runByEmail" type="button">Запустить в выбранное производтсво</button>
        </div>
        <h4>или введите email для отправки</h4>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-md-6">
                        <input class="form-control" id="directly_email">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary runByEmail" type="button"> Отправить</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
    <div id="mw_measure" class="modal_window">
        <div class="row">
            <div class="col-md-4">
                <label><strong>Адрес замера</strong></label>
                <table align="center">
                    <tr>
                        <td>Улица:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_address" id="jform_rec_address" placeholder="Улица"
                                   type="text" class="form-control" value="<?= $address->street; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Дом:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_house" id="jform_rec_house" placeholder="Дом" aria-required="true"
                                   type="text" class="form-control" value="<?= $address->house; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Корпус:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_bdq" id="jform_rec_bdq" placeholder="Корпус" aria-required="true"
                                   type="text" class="form-control" value="<?= $address->bdq; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Квартира:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_apartment" id="jform_rec_apartment" placeholder="Квартира"
                                   aria-required="true" type="text" class="form-control"
                                   value="<?= $address->apartment; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Подъезд:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_porch" id="jform_rec_porch" placeholder="Подъезд" aria-required="true"
                                   type="text" class="form-control" value="<?= $address->porch; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Этаж:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_floor" id="jform_rec_floor" placeholder="Этаж" aria-required="true"
                                   type="text" class="form-control" value="<?= $address->floor; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Код:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_code" id="jform_rec_code" placeholder="Код" aria-required="true"
                                   type="text" class="form-control" value="<?= $address->code; ?>">
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-4">
                <label><strong>Время замера</strong></label>
                <div id="measures_calendar" align="center"></div>
                <input id="measure_info" readonly>
            </div>
            <div class="col-md-4">
                <div class="row" style="margin-bottom: 5px">
                    <div class="col-md-4">
                        <b>Примечание к замеру:</b>
                    </div>
                    <div class="col-md-8">
                        <input type="text" id="measure_note" class="form-control">
                    </div>
                </div>
            </div>

        </div>
        <button id="save_rec" class="btn btn-primary" type="button">Сохранить</button>
    </div>
    <div id="mw_call_add" class="modal_window">
        <h4>Добавить звонок</h4>
        <link rel="stylesheet" href="/components/com_gm_ceiling/date_picker/nice-date-picker.css">
        <script src="/components/com_gm_ceiling/date_picker/nice-date-picker.js"></script>
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <div class="row" style="margin-bottom: 1em;">
                <label><b>Дата: </b></label><br>
                <div id="calendar" align="center"></div>
                <script>
                    new niceDatePicker({
                        dom: document.getElementById('calendar'),
                        mode: 'en',
                        onClickDate: function (date) {
                            document.getElementById('calldate').value = date;
                        }
                    });
                </script>
                <input name="calldate" id="calldate" type="hidden">
            </div>

            <div class="row" style="margin-bottom: 1em;">
                <div class="col-md-6" style="text-align: left;">
                    <label for="calltime" style="vertical-align: middle;">
                        <b>Время: </b>
                    </label>
                </div>
                <div class="col-md-6">
                    <input type="time" id="calltime" class="form-control">
                </div>
            </div>
            <div class="row center" style="margin-bottom: 1em;">
                <div class="col-md-12">
                    <input name="callcomment" id="callcomment" placeholder="Введите примечание" class="form-control">
                </div>
            </div>
            <div class="row center">
                <div class="col-md-12">
                    <input type="checkbox" id="important" class="inp-cbx" style="display: none">
                    <label for="important" class="cbx">
                <span>
                    <svg width="12px" height="10px" viewBox="0 0 12 10">
                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                    </svg>
                </span>
                        <span>Важный звонок</span>
                    </label>
                </div>
            </div>
            <div class="row center" >
                <button class="btn btn-primary" id="add_ref_call" type="button">Сохранить</button>
            </div>
        </div>
        <div class="col-md-4"></div>
    </div>
</div>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js?m"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js?<?=sha1(microtime(1))?>"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/project_actions.js?<?=sha1(microtime(1))?>"></script>

