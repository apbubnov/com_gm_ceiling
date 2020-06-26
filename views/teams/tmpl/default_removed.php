<?php
    $user = JFactory::getUser();
    $teamsModel = Gm_ceilingHelpersGm_ceiling::getModel('teams');
    $removedBrigades = $teamsModel->getRemoved($user->dealer_id);
// календарь
$month1 = date("n");
$year1 = date("Y");
if ($month1 == 12) {
    $month2 = 1;
    $year2 = $year1;
    $year2++;
} else {
    $month2 = $month1;
    $month2++;
    $year2 = $year1;
}
$FlagCalendar = [1, $user->dealer_id];
$allBrigades = [];
foreach ($removedBrigades as $value){
    $brigades = json_decode($value->mounters);
    $allBrigades = array_merge($allBrigades,$brigades);
    $city = (!empty($value->name) ? $value->name : 'Город не указан');
    $calendars .= "<div class='row brigade_city' data-id='$value->city_id' style='text-align: left'>";
    $calendars .= "<div class='col-md-10' style='font-size: 22pt'>$city <i class=\"fas fa-angle-down\"></i></div>";
    $calendars .= '</div>';
    $calendars .= '<div class="city_brigades" data-city_id="'.$value->city_id.'" style="display:none;">';
    for($i=0;$i<count($brigades);$i++){
        if($i % 3 == 0){
            $calendars.= '<div class="row" style="margin-bottom: 15px;">';
        }
        $calendars .= '<div class="col-md-4" >';
        $calendars .= '<div class="row center" style="width: 98%">';
        $calendars .= '<div class="col-md-10"><a href="/index.php?option=com_gm_ceiling&view=team&id='.$brigades[$i]->id.'class="site-tar">'.$brigades[$i]->name.'</a></div><div class="col-md-2"><button class="btn btn-primary btn-sm return_brigade" type="button" data-id="'.$brigades[$i]->id.'"><i class="fas fa-undo"></i></button></div>';
        $calendars .= '</div>';
        $calendars .= '<div class="row" style="width: 98%">';
        if(count($brigades[$i]->include_mounters) > 1) {
            $calendars .= '<div class="col-md-12">' . $brigades[$i]->include_mounters . '</div>';
        }
        $calendars .= '</div>';
        $calendars .= '<div class="row center firstMonth" data-brigade_id="'.$brigades[$i]->id.'" style="width: 98%">';
        $calendars .= Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($brigades[$i]->id, $month1, $year1, $FlagCalendar);
        $calendars .= '</div>';
        $calendars .= '<div class="row center secondMonth" data-brigade_id="'.$brigades[$i]->id.'" style="width: 98%">';
        $calendars .= Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($brigades[$i]->id, $month2, $year2, $FlagCalendar);
        $calendars .= '</div>';
        $calendars .= '</div>';
        if(($i+1) % 3 == 0 || $i+1 == count($brigades)){
            $calendars .= '</div>';
        }

    }
    $calendars .= '</div>';
}
?>
<link rel="stylesheet" href="components/com_gm_ceiling/views/teams/tmpl/css/style.css" type="text/css" />
<?=parent::getButtonBack();?>
<div id="preloader" style="display: none;" class="PRELOADER_GM PRELOADER_GM_OPACITY">
    <div class="PRELOADER_BLOCK"></div>
    <img src="/images/GM_R_HD.png" class="PRELOADER_IMG">
</div>
<h2 class="center">Удаленные бригады</h2>
<div class="container" id="calendars-container">
    <?php echo $calendars; ?>
</div>
<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>

    <div class="modal_window" id="mw_mounts">
        <input type="hidden" id="selected_brigade">
        <table id="table-mounting"></table>
    </div>
</div>
<script>
    var notes=[],
        month_old1 = 0,
        year_old1 = 0,
        month_old2 = 0,
        year_old2 = 0,
        calendar,
        today = new Date(),
        NowYear = today.getFullYear(),
        NowMonth = today.getMonth(),
        day = today.getDate();

    jQuery(document).mouseup(function (e) { // событие клика по веб-документу
        var div = jQuery("#mw_mounts");// тут указываем ID элемента
        if (!div.is(e.target) && div.has(e.target).length == 0) { // не по элементу и не по его дочерним элементам
            jQuery("#mw_container").hide();
            jQuery("#close_mw").hide();
            jQuery("#mw_mounts").hide();
            jQuery("#table-mounting").empty();
        }
    });
    jQuery(document).ready(function () {
        jQuery('.brigade_city').click(function () {
            var city_id = jQuery(this).data('id');
            jQuery('.city_brigades[data-city_id="' + city_id + '"]').toggle();
            var i = jQuery(this).find('i');

            if (i.hasClass('fa-angle-down')) {
                i.removeClass("fa-angle-down").addClass("fa-angle-up");
            } else if (i.hasClass('fa-angle-up')) {
                i.removeClass("fa-angle-up").addClass("fa-angle-down");
            }
        });

        jQuery('.return_brigade').click(function () {
            var user_id = jQuery(this).data('id');
            alert(user_id);
            noty({
                layout: 'topCenter',
                type: 'default',
                modal: true,
                text: 'Вы действительно хотите вернуть бригаду?',
                killer: true,
                buttons: [
                    {
                        addClass: 'btn btn-primary', text: 'Да', onClick: function ($noty) {
                            jQuery.ajax({
                                url: "index.php?option=com_gm_ceiling&task=teams.returnBrigade",
                                data: {
                                    user_id: user_id
                                },
                                dataType: "json",
                                async: true,
                                success: function(data) {
                                    location.reload();
                                },
                                error: function(data) {
                                    console.log(data);
                                    var n = noty({
                                        timeout: 2000,
                                        theme: 'relax',
                                        layout: 'center',
                                        maxVisible: 5,
                                        type: "error",
                                        text: "Ошибка сервера"
                                    });
                                }
                            });
                            $noty.close();
                        }
                    },
                    {
                        addClass: 'btn', text: 'Нет', onClick: function ($noty) {
                            $noty.close();
                        }
                    }
                ]
            });
        });

        // нажатие на день, чтобы посмотреть проекты на день
        jQuery("#calendars-container").on("click", ".day-not-read, .day-read, .day-in-work, .day-underfulfilled, .day-complite, .old-project", function() {
            ChoosenDay = this.id;
            kind = "no-empty";
            WhatDay(ChoosenDay);
            ListOfWork(kind, d, m, y);
            jQuery('#selected_brigade').val(ChoosenDay.match("I(.*)I")[1]);
            jQuery("#mw_mounts").show('slow');
            jQuery("#close_mw").show();
            jQuery("#mw_container").show();
        });

        jQuery('#calendars-container').on('click','.prev', function () {
            var brigadeId = jQuery(this).closest('div[data-brigade_id]').data('brigade_id'),
                month1 = <?php echo $month1; ?>,
                year1 = <?php echo $year1; ?>,
                month2 = <?php echo $month2; ?>,
                year2 = <?php echo $year2; ?>;
            if (month_old1 != 0) {
                month1 = month_old1;
                year1 = year_old1;
                month2 = month_old2;
                year2 = year_old2;
            }
            if (month1 == 1) {
                month1 = 12;
                year1--;
            } else {
                month1--;
            }
            if (month2 == 1) {
                month2 = 12;
                year2--;
            } else {
                month2--;
            }
            month_old1 = month1;
            year_old1 = year1;
            month_old2 = month2;
            year_old2 = year2;
            jQuery("#preloader").show();
            updateCalendar(brigadeId,month1,year1);
            jQuery('.firstMonth[data-brigade_id='+brigadeId+']').empty();
            jQuery('.firstMonth[data-brigade_id='+brigadeId+']').append(calendar);
            updateCalendar(brigadeId,month2,year2);
            jQuery('.secondMonth[data-brigade_id='+brigadeId+']').empty();
            jQuery('.secondMonth[data-brigade_id='+brigadeId+']').append(calendar);
        });

        jQuery('#calendars-container').on('click','.next', function () {
            var brigadeId = jQuery(this).closest('div[data-brigade_id]').data('brigade_id'),
                month1 = <?php echo $month1; ?>,
                year1 = <?php echo $year1; ?>,
                month2 = <?php echo $month2; ?>,
                year2 = <?php echo $year2; ?>;

            if (month_old1 != 0) {
                month1 = month_old1;
                year1 = year_old1;
                month2 = month_old2;
                year2 = year_old2;
            }
            if (month1 == 12) {
                month1 = 1;
                year1++;
            } else {
                month1++;
            }
            if (month2 == 12) {
                month2 = 1;
                year2++;
            } else {
                month2++;
            }
            month_old1 = month1;
            year_old1 = year1;
            month_old2 = month2;
            year_old2 = year2;

            updateCalendar(brigadeId,month1,year1);
            jQuery('.firstMonth[data-brigade_id='+brigadeId+']').empty();
            jQuery('.firstMonth[data-brigade_id='+brigadeId+']').append(calendar);
            updateCalendar(brigadeId,month2,year2);
            jQuery('.secondMonth[data-brigade_id='+brigadeId+']').empty();
            jQuery('.secondMonth[data-brigade_id='+brigadeId+']').append(calendar);
        });

        jQuery("#table-mounting").on('click','.clickabel',function () {
            var id = jQuery(this).closest('tr').data('id');
            location.replace("/index.php?option=com_gm_ceiling&view=projectform&type=gmchief&id="+id);
        });
        jQuery("#table-mounting").on('click','.clear_mount',function () {
            var projectId = jQuery(this).closest('tr').data('id'),
                brigadeId = jQuery('#selected_brigade').val();
            noty({
                layout: 'topCenter',
                type: 'default',
                modal: true,
                text: 'Вы действительно хотите убрать бригаду с проекта?',
                killer: true,
                buttons: [
                    {
                        addClass: 'btn btn-primary', text: 'Да', onClick: function ($noty) {
                            jQuery.ajax({
                                url: "index.php?option=com_gm_ceiling&task=project.removeProjectMountByBrigade",
                                data: {
                                    project_id: projectId,
                                    brigade_id: brigadeId
                                },
                                dataType: "json",
                                async: true,
                                success: function(data) {
                                    noty({
                                        layout: 'topCenter',
                                        type: 'default',
                                        modal: true,
                                        text: 'Перейти в проект №'+projectId+'?',
                                        killer: true,
                                        buttons: [
                                            {
                                                addClass: 'btn btn-primary', text: 'Да', onClick: function ($noty) {
                                                    location.href = "/index.php?option=com_gm_ceiling&view=projectform&type=gmchief&id="+projectId;
                                                }
                                            },
                                            {
                                                addClass: 'btn', text: 'Нет', onClick: function ($noty) {
                                                    $noty.close();
                                                }
                                            }
                                        ]
                                    });
                                },
                                error: function(data) {
                                    console.log(data);
                                    noty({
                                        timeout: 2000,
                                        theme: 'relax',
                                        layout: 'center',
                                        maxVisible: 5,
                                        type: "error",
                                        text: "Ошибка сервера"
                                    });
                                }
                            });
                            $noty.close();
                        }
                    },
                    {
                        addClass: 'btn', text: 'Нет', onClick: function ($noty) {
                            $noty.close();
                        }
                    }
                ]
            });
        });
    });


    // функция вывода работ (таблицы) дня при нажатии на день
    function ListOfWork(kind, d, m, y) {
        date = y+"-"+m+"-"+d;
        date_to_modal_window = d+"."+m+"."+y;
        idBrigade = ChoosenDay.match("I(.*)I")[1];
        jQuery("#table-mounting").empty();
        var table = "";
        if (kind == "empty") {
            table = '<tr id="caption-data"><td colspan=2>'+d+'.'+m+'.'+y+'</td></tr><tr><td colspan=2>В данный момент на этот день монтажей нет</td></tr>';
            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=teams.GetMounting",
                dataType: 'json',
                data: {
                    date: date,
                    id: idBrigade,
                },
                success: function(data) {
                    console.log(data);
                    Array.from(data).forEach(function(element) {
                        table += '<tr><td style="width: 25%;">'+element.project_mounting_date+'</td><td style="width: 75%;">'+element.project_info+'</td></tr>';
                    });
                    jQuery("#table-mounting").append(table);
                }
            });
        } else {
            table += '<tr id="caption-data"><td colspan="8">'+d+'.'+m+'.'+y+'</td></tr><tr id="caption-tr"><td>№</td><td>Время</td><td>Адрес</td><td>Периметр</td><td>З/П</td><td>Остаток</td><td>Снять бригаду</td></tr>';
            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=teams.GetMounting",
                dataType: 'json',
                data: {
                    date: date,
                    id: idBrigade,
                },
                success: function(data) {
                    Array.from(data).forEach(function(element) {
                        if (element.project_mounting_date.length < 6) {
                            console.log(element);
                            perimeter = +element.perimeter;
                            table += '<tr data-id="'+element.id+'"><td class="clickabel">'+element.id+'</td>' +
                                '<td class="clickabel">'+element.project_mounting_date+'</td>' +
                                '<td class="clickabel">'+element.project_info+'</td>' +
                                '<td class="clickabel">'+perimeter.toFixed(2)+'</td>' +
                                '<td class="clickabel">'+element.salary+'</td>' +
                                '<td class="clickabel">'+element.project_rest+'</td>' +
                                '<td><button class="btn btn-primary clear_mount">Убрать бригаду</button> </td>' +
                                '</tr>';
                        } else {
                            table += '<tr><td>'+element.project_mounting_date+'</td><td colspan=5>'+element.project_info+'</td></tr>';
                        }
                    });
                    jQuery("#table-mounting").append(table);
                }
            });
        }
    }
    function updateCalendar(id,month,year){
        jQuery.ajax({
            async: false,
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
            data: {
                id: id,
                id_dealer: '<?php echo $user->dealer_id; ?>',
                flag: 1,
                month: month,
                year: year,
            },
            success: function (msg) {
                jQuery("#preloader").hide();
                calendar = msg;
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                jQuery("#preloader").hide();
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при попытке обновить календарь. Сервер не отвечает"
                });
            }
        });
    }
    function WhatDay(id) {
        var nov_reg1 = "D(.*)D";
        d = id.match(nov_reg1)[1];
        var nov_reg2 = "M(.*)M";
        m = id.match(nov_reg2)[1];
        var nov_reg3 = "Y(.*)Y";
        y = id.match(nov_reg3)[1];
        if (d.length == 1) {
            d = "0"+d;
        }
        if (m.length == 1) {
            m = "0"+m;
        }
    }
</script>