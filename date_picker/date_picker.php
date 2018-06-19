<link rel="stylesheet" href="/components/com_gm_ceiling/date_picker/nice-date-picker.css">
<script src="/components/com_gm_ceiling/date_picker/nice-date-picker.js"></script>
<label>Дата: </label><br>
<div id="calendar-wrapper"></div>
<p><label>Время: </label><br><input type="time"></p>
<script>
    new niceDatePicker({
        dom:document.getElementById('calendar-wrapper'),
        mode:'en',
        onClickDate:function(date){
            console.log(date);
        }
    });
</script>