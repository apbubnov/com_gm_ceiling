function init_measure_calendar(elem_id)
{
	function init() {
		try {
	    	new niceDatePicker({
		        dom:document.getElementById(elem_id),
		        mode:'en',
		        onClickDate:function(date){
		            console.log(date);
		        }
		    });
	    } catch(e) {
	    	setTimeout(init, 200);
	    }
    }
	setTimeout(include_script, 1, 'components/com_gm_ceiling/date_picker/nice-date-picker.js');
	setTimeout(include_style, 1, 'components/com_gm_ceiling/date_picker/calendars.css');
	setTimeout(init, 200);
}
function include_script(url) {
    let scripts = document.getElementsByTagName('script');
    let reg_exp = new RegExp(url);
    for(let i = scripts.length;i--;){
        if(reg_exp.test(scripts[i].src)){
            return;
        }
    }
    var script = document.createElement('script'); 
    script.src = url; 
    document.getElementsByTagName('head')[0].appendChild(script);
}
function include_style(url) {
    let styles = document.getElementsByTagName('link');
    let reg_exp = new RegExp(url);
    for(let i = styles.length;i--;){
        if(reg_exp.test(styles[i].src)){
            return;
        }
    }
    var link = document.createElement('link'); 
    link.rel = 'stylesheet'; 
    link.href = url;
    document.getElementsByTagName('head')[0].appendChild(link);
}

