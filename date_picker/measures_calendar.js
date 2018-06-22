function init_measure_calendar(elem_id)
{
	var cont = document.getElementById(elem_id), calendar, data_array, gaugers;

	setTimeout(include_script, 10, 'components/com_gm_ceiling/date_picker/nice-date-picker.js');
	setTimeout(include_style, 10, 'components/com_gm_ceiling/date_picker/calendars.css');
	setTimeout(init, 200);

	function init() {
		try {
	    	calendar = new niceDatePicker({
		        dom: cont,
		        mode: 'en',
		        onClickDate: function(date) {
		        	var elem = jQuery('#'+elem_id+' .nice-normal[data-date="'+date+'"]')[0];
		            console.log(date);
		            draw_calendar();
		            elem.classList.remove('nice-busy');
		            if (elem.classList.contains('nice-busy-all')) {
		            	setTimeout(function(){elem.classList.remove('nice-select');}, 500);
		            }
		        }
		    });

		    jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=getArrayForMeasuresCalendar",
                success: function(data) {
                	data_array = data.data;
                	gaugers = data.gaugers;
                    console.log(data_array, gaugers);
                    cont.onclick = clicks_on_calendar;
                    draw_calendar();
                },
                dataType: "json",
                timeout: 10000,
                error: function() {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных для календаря замеров"
                    });
                }
            });

		    function clicks_on_calendar(e) {
		    	var target = e.target;
	            while (true) {
	            	if (target == null || target.id == elem_id) {
	            		return;
	            	}
	            	if (target.className == 'prev-date-btn' || target.className == 'next-date-btn') {
	            		//var date_month = calendar.monthData.year + "-" + calendar.monthData.month;
	            		//console.log(add_zeros_in_date(date_month));
	            		draw_calendar();
	            		return;
	            	}
	            	target = target.parentNode;
	            }
		    }

		    function draw_calendar() {
		    	var y = calendar.monthData.year, m = calendar.monthData.month, count, tds, maxCount;
		    	maxCount = 12 * gaugers.length;
		    	tds = cont.getElementsByClassName('nice-normal');
		    	for (var d in data_array[y][m]) {
		    		count = 0;
		    		for (var key in gaugers) {
		    			var c = gaugers[key].id;
		    			for (var h in data_array[y][m][d][c]) {
		    				if (data_array[y][m][d][c][h]) {
		    					count++;
		    				}
		    			}
		    		}
		    		if (count > 0 && count < maxCount) {
    					tds[d - 1].classList.add('nice-busy');
    				}
    				else if (count === maxCount) {
    					tds[d - 1].classList.add('nice-busy-all');
    				}
		    	}
		    }
	    } catch(e) {
	    	//console.log(e);
	    	setTimeout(init, 200);
	    }
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
	        if(reg_exp.test(styles[i].href)){
	            return;
	        }
	    }
	    var link = document.createElement('link'); 
	    link.rel = 'stylesheet'; 
	    link.href = url;
	    document.getElementsByTagName('head')[0].appendChild(link);
	}
	function add_zeros_in_date(date) {
		date = date.replace(/(-)([\d]+)/g, function(str,p1,p2) {
            if (p2.length === 1) {
                return '-0'+p2;
            }
            else {
                return str;
            }
        });
        return date;
	}
}