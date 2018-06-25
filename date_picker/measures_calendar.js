function init_measure_calendar(elem_id, input_time, input_calculator, modal_window, dop_mw)
{
	var cont = document.getElementById(elem_id), calendar, data_array, gaugers, selectTime, selectCalculator,
	mw_elem = document.getElementById(modal_window);

	setTimeout(include_script, 10, 'components/com_gm_ceiling/date_picker/nice-date-picker.js');
	setTimeout(include_style, 10, 'components/com_gm_ceiling/date_picker/calendars.css');
	setTimeout(init, 200);

	function init() {
		try {
	    	calendar = new niceDatePicker({
		        dom: cont,
		        mode: 'en',
		        onClickDate: function(date) {
		        	var elem = jQuery('#'+elem_id+' .nice-normal[data-date="'+date+'"]')[0], date_sp = date.split('-'),
		        	html = '', y = date_sp[0]-0, m = date_sp[1]-0, d = date_sp[2]-0, current_day, today;
		            console.log(date);
		            draw_calendar();
		            today = new Date();
	            	current_day = add_zeros_in_date(today.getFullYear()+'-'+(today.getMonth() + 1)+'-'+today.getDate());
	            	if (add_zeros_in_date(date) < current_day) {
		            	return;
		            }
		            
		            elem.classList.remove('nice-busy');
		            if (elem.classList.contains('nice-busy-all')) {
		            	setTimeout(function(){elem.classList.remove('nice-select');}, 500);
		            }
		            else {
		            	if (Array.isArray(dop_mw)) {
		            		for (var i in dop_mw) {
		            			document.getElementById(dop_mw[i]).style.display = 'block';
		            		}
		            	}
		            	else {
		            		document.getElementById(dop_mw).style.display = 'block';
		            	}
		            	html += '<center><table class="measures_grafik"><tbody><tr><th></th><th>09:00</th><th>10:00</th><th>11:00</th><th>12:00</th><th>13:00</th><th>14:00</th><th>15:00</th><th>16:00</th><th>17:00</th><th>18:00</th><th>19:00</th><th>20:00</th></tr>';
		            	for (var key in gaugers) {
			    			var c = gaugers[key].id;
			    			html += '<tr><th>'+gaugers[key].name+'</th>';
			    			if (data_array[y] == undefined || data_array[y][m] == undefined || data_array[y][m][d] == undefined) {
			    				for (var h = 9; h < 21; h++) {
			    					var time = y+'-'+m+'-'+d+' '+h+':00:00';
			    					var _class;
			    					if (selectTime == time) {
			    						_class = 'select-day';
			    					} else {
			    						_class = 'free-day';
			    					}
			    					html += '<td class="'+_class+'" data-time="'+time+'" data-calculator="'+c+'"></td>';
			    				}
			    			}
			    			else {
			    				for (var h = 9; h < 21; h++) {
			    					var time = y+'-'+m+'-'+d+' '+h+':00:00';
			    					var _class;
			    					if (selectTime == time) {
			    						_class = 'select-day';
			    					} else {
			    						if (data_array[y][m][d][c][h]) {
				    						_class = 'busy-day';
					    				}
					    				else {
					    					_class = 'free-day';
					    				}
			    					}
				    				html += '<td class="'+_class+'" data-time="'+time+'" data-calculator="'+c+'"></td>';
				    			}
			    			}
			    			
			    			html += '</tr>';
			    		}
			    		html += '</tbody></table><br><p><button type="button" class="btn btn-primary hide_calendar">Ок</button></p></center>';
			    		mw_elem.innerHTML = html;
		            	mw_elem.style.display = 'block';
		            	jQuery('#'+modal_window+' .free-day').click(function(){
		            		var selected_td = mw_elem.getElementsByClassName('select-day');
		            		for (var i = selected_td.length; i--;) {
		            			selected_td[i].classList.remove('select-day');
		            		}
		            		this.classList.add('select-day');
		            		selectTime = this.getAttribute('data-time');
		            		selectCalculator = this.getAttribute('data-calculator');
		            		document.getElementById(input_time).value = selectTime;
		            		document.getElementById(input_calculator).value = selectCalculator;
		            		this.classList.remove('free-day');
		            	});
		            	jQuery('#'+modal_window+' .hide_calendar').click(function(){
		            		if (Array.isArray(dop_mw)) {
			            		for (var i in dop_mw) {
			            			document.getElementById(dop_mw[i]).style.display = 'none';
			            		}
			            	}
			            	else {
			            		document.getElementById(dop_mw).style.display = 'none';
			            	}
			            	mw_elem.style.display = 'none';
		            	});
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
		    	if (data_array[y] == undefined) {
		    		data_array[y] = [];
		    	}
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