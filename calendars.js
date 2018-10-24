// календарь монтажников
// листание календаря
jQuery("#button-next").click(function () {
    month1 = <?php echo $month1; ?>;
    year1 = <?php echo $year1; ?>;
    month2 = <?php echo $month2; ?>;
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
    update_calendar(month1, year1,"#calendar1");
    update_calendar(month2, year2,"#calendar2");
});
jQuery("#button-prev").click(function () {
    month1 = <?php echo $month1; ?>;
    year1 = <?php echo $year1; ?>;
    month2 = <?php echo $month2; ?>;
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
    update_calendar(month1, year1,"#calendar1");
    update_calendar(month2, year2,"#calendar2");
});
function update_calendar(month, year,type) {
    var flag = (type == "#g_calendar" ) ? 3 : 2;
    jQuery.ajax({
        type: 'POST',
        url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
        data: {
            id: <?php echo $userId; ?>,
            id_dealer: <?php if ($user->dealer_type == 1 && $user->dealer_mounters == 1) { echo 1; } else { echo $user->dealer_id; } ?>,
            flag: flag,
            month: month,
            year: year,
        },
        success: function (msg) {
            jQuery(type).empty();
            jQuery(type).append(msg);
            Today(day, NowMonth, NowYear);
        },
        dataType: "text",
        timeout: 10000,
        error: function () {
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
//----------------------------------------
