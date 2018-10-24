<link rel="stylesheet" href="/components/com_gm_ceiling/date_picker/calendars.css">
<script src="/components/com_gm_ceiling/date_picker/nice-date-picker.js"></script>
<div id="calendar-wrapper"></div>
<script>
    new niceDatePicker({
        dom:document.getElementById('calendar-wrapper'),
        mode:'en',
        onClickDate:function(date){
            console.log(date);
        }
    });
</script>