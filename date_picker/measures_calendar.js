function init_measure_calendar(elem_id)
{
	var cont = document.getElementById(elem_id), calendar, measures;

	setTimeout(include_script, 1, 'components/com_gm_ceiling/date_picker/nice-date-picker.js');
	setTimeout(include_style, 1, 'components/com_gm_ceiling/date_picker/calendars.css');
	setTimeout(init, 200);

	function init() {
		try {
	    	calendar = new niceDatePicker({
		        dom: cont,
		        mode: 'en',
		        onClickDate: function(date) {
		            console.log(date);
		            draw_calendar();
		            jQuery('#'+elem_id+' .nice-normal[data-date="'+date+'"]')[0].classList.remove('nice-busy');
		        }
		    });

		    jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=projects.getMeasuresOfCurrentUser",
                success: function(data) {
                	measures = data;
                    console.log(measures);
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
	            		var date_month = calendar.monthData.year + "-" + calendar.monthData.month;
	            		console.log(add_zeros_in_date(date_month));
	            		draw_calendar();
	            		//cont.getElementsByClassName('nice-normal')[Math.floor(Math.random() * (28))].style.background = 'green';
	            		return;
	            	}
	            	target = target.parentNode;
	            }
		    }

		    function draw_calendar() {
		    	var date, jelems, count, tds;
		    	tds = cont.getElementsByClassName('nice-normal');
		    	for (var i = tds.length; i--;) {
		    		tds[i].setAttribute('data-count', 0);
		    	}
		    	for (var i = measures.length; i--;) {
		    		date = measures[i].project_calculation_date.substring(0, 10);
		    		date = date.replace('-0', '-');
		    		jelems = jQuery('#'+elem_id+' .nice-normal[data-date="'+date+'"]');
		    		if (jelems.length === 1) {
	    				count = jelems[0].getAttribute('data-count')-0 + 1;
	    				jelems[0].setAttribute('data-count', count);
	    				if (count === 12) {
	    					jelems[0].classList.add('nice-busy-all');
	    				}
	    				else {
	    					jelems[0].classList.add('nice-busy');
	    				}
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
	        if(reg_exp.test(styles[i].src)){
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