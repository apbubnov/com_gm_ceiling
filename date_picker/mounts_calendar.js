function init_mount_calendar(elem_id, input_time, input_calculator, modal_window, dop_mw)
{
	var cont = document.getElementById(elem_id), calendar, data_array, mounters, selectTime, selectMounter,
	mw_elem = document.getElementById(modal_window),
	mw_stages = '<div class="mw_stages" style="position:fixed;left:0px;right:0px;margin:60px auto;width:300px;height:280px;display:none;"></div>';

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
	            		noty({
	                        timeout: 3000,
	                        theme: 'relax',
	                        layout: 'topCenter',
	                        maxVisible: 5,
	                        type: "warning",
	                        text: "Для назначения монтажа на эту дату необходимо вернуться в прошлое"
	                    });
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
		            	html += '<center><table class="mounts_grafik"><tbody><tr><th></th><th>09:00</th><th>10:00</th><th>11:00</th><th>12:00</th><th>13:00</th><th>14:00</th><th>15:00</th><th>16:00</th><th>17:00</th><th>18:00</th><th>19:00</th><th>20:00</th></tr>';
		            	for (var key in mounters) {
			    			var c = mounters[key].id;
			    			html += '<tr><th>'+mounters[key].name+'</th>';
			    			if (data_array[y] == undefined || data_array[y][m] == undefined || data_array[y][m][d] == undefined || data_array[y][m][d][c] == undefined) {
			    				for (var h = 9; h < 21; h++) {
			    					var time = y+'-'+m+'-'+d+' '+h+':00:00';
			    					var _class;
			    					if (selectTime == time && selectMounter == c) {
			    						_class = 'select-day';
			    					} else {
			    						_class = 'free-day';
			    					}
			    					html += '<td class="'+_class+'" data-time="'+time+'" data-mounter="'+c+'"></td>';
			    				}
			    			}
			    			else {
			    				for (var h = 9; h < 21; h++) {
			    					var time = y+'-'+m+'-'+d+' '+h+':00:00';
			    					var _class, p_id = false, p_info = false;
			    					if (selectTime == time && selectMounter == c) {
			    						_class = 'select-day';
			    					} else {
			    						var ymdch = data_array[y][m][d][c][h];
			    						if (ymdch) {
				    						_class = 'busy-day';
				    						if (ymdch.id != null && ymdch.info != null) {
				    							p_id = ymdch.id;
				    							p_info = ymdch.info;
				    						}
					    				}
					    				else {
					    					_class = 'free-day';
					    				}
			    					}
			    					if (p_id && p_info) {
					    				html += '<td class="'+_class+'" data-time="'+time+'" data-mounter="'+c+'" data-pid="'+p_id+'" data-info="'+p_info+'"></td>';
					    			}
					    			else {
					    				html += '<td class="'+_class+'" data-time="'+time+'" data-mounter="'+c+'"></td>';
					    			}
				    			}
			    			}
			    			
			    			html += '</tr>';
			    		}
			    		html += '</tbody></table><label class="p_date"></label><br><label class="p_id"></label><br><label class="p_info"></label><p><button type="button" class="btn btn-primary hide_calendar">Ок</button></p></center>';
			    		html += mw_stages;
			    		mw_elem.innerHTML = html;
		            	mw_elem.style.display = 'block';

		            	jQuery('#'+modal_window+' .free-day').click(function free_click(){
		            		mw_elem.getElementsByClassName('mw_stages')[0].style.display = 'block';
		            		var selected_td = mw_elem.getElementsByClassName('select-day');
		            		for (var i = selected_td.length; i--;) {
		            			selected_td[i].onclick = free_click;
		            			selected_td[i].classList.add('free-day');
		            			selected_td[i].classList.remove('select-day');
		            		}
		            		this.classList.add('select-day');
		            		selectTime = this.getAttribute('data-time');
		            		selectMounter = this.getAttribute('data-mounter');
		            		document.getElementById(input_time).value = selectTime;
		            		document.getElementById(input_calculator).value = selectMounter;
		            		mw_elem.getElementsByClassName('p_date')[0].innerHTML = this.getAttribute('data-time');
		            		mw_elem.getElementsByClassName('p_id')[0].innerHTML = 'Свободно';
		            		mw_elem.getElementsByClassName('p_info')[0].innerHTML = '';
		            		//this.classList.remove('free-day');
		            	});

		            	jQuery('#'+modal_window+' .busy-day').click(function(){
		            		var p_id, p_info;
		            		if (this.hasAttribute('data-pid') && this.hasAttribute('data-info')) {
		            			p_date = this.getAttribute('data-time');
		            			p_id = this.getAttribute('data-pid');
		            			p_info = this.getAttribute('data-info');
		            		}
		            		else {
		            			p_date = this.getAttribute('data-time');
		            			p_id = 'Выходной';
		            			p_info = '';
		            		}
		            		mw_elem.getElementsByClassName('p_date')[0].innerHTML = p_date;
		            		mw_elem.getElementsByClassName('p_id')[0].innerHTML = p_id;
		            		mw_elem.getElementsByClassName('p_info')[0].innerHTML = p_info;
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
                url: "index.php?option=com_gm_ceiling&task=getArrayForMountsCalendar",
                success: function(data) {
                	data_array = data.data;
                	mounters = data.mounters;
                    console.log(data_array, mounters);
                    cont.onclick = clicks_on_calendar;
                    draw_calendar();
                },
                dataType: "json",
                timeout: 10000,
                error: function() {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'topCenter',
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
	            		draw_calendar();
	            		return;
	            	}
	            	target = target.parentNode;
	            }
		    }

		    function draw_calendar() {
		    	var y = calendar.monthData.year, m = calendar.monthData.month, count, tds, maxCount;
		    	maxCount = 12 * mounters.length;
		    	tds = cont.getElementsByClassName('nice-normal');
		    	if (data_array[y] == undefined) {
		    		data_array[y] = [];
		    	}
		    	for (var d in data_array[y][m]) {
		    		count = 0;
		    		for (var key in mounters) {
		    			var c = mounters[key].id;
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