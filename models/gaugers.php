<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelGaugers extends JModelItem {
	
	/*public function getAllItems() {
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('*');
		$query->from('`#__gm_ceiling_groups`');
		$db->setQuery($query);
		$items = $db->loadAssocList();
		return $items;
	}*/

	function getDealerGaugers($dealerId) {
		try
		{
			$db = JFactory::getDbo();
			if ($dealerId == 1) {
				$type = 22;
			} else {
				$type = 21;
			}
			$query = $db->getQuery(true);
			$query->select('`u`.`id`, `u`.`name`')
				->from('`#__users` AS `u`')
				->innerJoin('`#__user_usergroup_map` AS `g` ON `g`.`user_id` = `u`.`id`')
				->where("`u`.`dealer_id` = $dealerId AND `g`.`group_id` = $type");
			$db->setQuery($query);
			$items = $db->loadObjectList();

			if (empty($items)) {
				$query = $db->getQuery(true);
				$query->select('`id`, `name`')
					->from('`#__users`')
					->where("`id` = $dealerId");
				$db->setQuery($query);
				$items = $db->loadObjectList();
			}
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function GetAllGaugingOfGaugers($id, $date1, $date2) { //masID
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$whereAll = "";
			//foreach ($masID as $value) {
				//if ($whereAll == "") {
					//$whereAll .= "(project_calculator = '$value'";
				//} else {
					//$whereAll .= " or project_calculator = '$value'";				
				//}
			//}
			$whereAll = "project_calculator = '$id'";
			$whereAll .= " and project_calculation_date between '$date1 00:00:00' and '$date2 23:59:59'"; //)
			
			$query->select('project_calculator, project_calculation_date')
				->from('#__gm_ceiling_projects')
				->where("$whereAll")
				->order('project_calculation_date');
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function SaveDayOff($id, $date1, $date2) {
		try
		{
			$server_name = $_SERVER['SERVER_NAME'];
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			$query4 = $db->getQuery(true);
			$query5 = $db->getQuery(true);

			$query5->select("email")
				->from("#__users")
				->where("id = '$id'");
			$db->setQuery($query5);
			$items5 = $db->loadObject();

			$query2->select("id_user")
				->from("#__gm_ceiling_day_off")
				->where("id_user = '$id' and date_from between '".substr($date1, 0, 10)." 00:00:00' and '".substr($date1, 0, 10)." 23:59:59'");
				$db->setQuery($query2);
			$items = $db->loadObject();

			if ($items->id_user != null) {
				$query3->update("#__gm_ceiling_day_off")
					->set("date_from = '$date1'")
					->set("date_to = '$date2'")
					->where("id_user = '$id' and date_from between '".substr($date1, 0, 10)." 00:00:00' and '".substr($date1, 0, 10)." 23:59:59'");
				$db->setQuery($query3);
				$db->execute();

				// письмо
				$mailer = JFactory::getMailer();
				$config = JFactory::getConfig();
				$sender = array(
					$config->get('mailfrom'),
					$config->get('fromname')
				);
				$mailer->setSender($sender);
				$mailer->addRecipient($items5->email);
				$body = "Здравствуйте, изменилось время выходных часов ".substr($date1, 0, 10)." числа: с ".substr($date1, 11, 5)." до ".substr($date2, 11, 5)." \n";
				$body .= "\n";
				$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://$server_name/\">http://$server_name</a>";		
				$mailer->setSubject('Выходные часы');
				$mailer->setBody($body);
				$send = $mailer->Send();
			} else {
				$query->insert('#__gm_ceiling_day_off')
					->columns("id_user, date_from, date_to")
					->values("'$id', '$date1', '$date2'");
				$db->setQuery($query);
				$db->execute();

				// письмо
				$mailer = JFactory::getMailer();
				$config = JFactory::getConfig();
				$sender = array(
					$config->get('mailfrom'),
					$config->get('fromname')
				);
				$mailer->setSender($sender);
				$mailer->addRecipient($items5->email);
				$body = "Здравствуйте, у Вас появились выходные часы ".substr($date1, 0, 10)." числа с ".substr($date1, 11, 5)." до ".substr($date2, 11, 5)." \n";
				$body .= "\n";
				$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://$server_name/\">http://$server_name</a>";		
				$mailer->setSubject('Выходные часы');
				$mailer->setBody($body);
				$send = $mailer->Send();
			}

			$query4->select("id_user")
				->from("#__gm_ceiling_day_off")
				->where("id_user = '$id' and date_from = '$date1' and date_to = '$date2'");
			$db->setQuery($query4);
			$items2 = $db->loadObject();
			
			return $items2;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function GetGaugersWorkDayOff($id_gauger, $date) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);

			$query->select("projects.id, projects.project_calculation_date, projects.project_info, projects.project_status")
				->from('#__gm_ceiling_projects as projects')
				->where("projects.project_calculator = $id_gauger and projects.project_calculation_date between '$date 00:00:00' and '$date 23:59:59'")
				->order('projects.project_calculation_date');
			$db->setQuery($query);
			$items = $db->loadObjectList();
			
			$query2->select("id_user, date_from, date_to")
				->from('#__gm_ceiling_day_off')
				->where("id_user = '$id_gauger' and date_from between '$date 00:00:00' and '$date 23:59:59'");
			$db->setQuery($query2);
			$items2 = $db->loadObject();

			// объединение с выходным днем
			$index = 0;
			$was_break = false;
            //поиск индекса для вставки и замена даты на просто время
            for ($i=0; $i < count($items); $i++) {
                if (strtotime($items[$i]->project_calculation_date) >= strtotime($items2->date_from)) {
					$index = $i;
					$was_break = true;
                    break;
                }
			}
			($index == 0 && !$was_break) ? $index = count($items) : 0;
            for ($i=0; $i < count($items); $i++) {
                $items[$i]->project_calculation_day_off = "";
            }
            //создание нового массива
            if (!empty($items2)) {
                $day = array(
                    'id'=>$items2->id_user,
                    'project_calculation_date'=>$items2->date_from,
                    'project_info'=>NULL,
                    'project_status'=>NULL,
                    'project_calculation_day_off'=>$items2->date_to
                );
                $day = array((object)$day);
                array_splice($items,$index,0,$day);
			}
						
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function GetAllDayOff($id, $date1, $date2) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('date_from, date_to')
				->from('#__gm_ceiling_day_off')
				->where("id_user = '$id' and date_from between '$date1 00:00:00' and '$date2 23:59:59'")
				->order('date_from');
			$db->setQuery($query);
			$items = $db->loadObjectList();

			return $items;
		}
		catch(Exception $e)
		{
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}

	public function FindFreeDay($id, $date) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select("*")
				->from("#__gm_ceiling_day_off")
				->where("(id_user = '$id') and (date_from between '$date 00:00:00' and '$date 23:59:59')");
			$db->setQuery($query);

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function DeleteFreeDay($id, $date) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);

			$query3->select("email")
				->from("#__users")
				->where("id = '$id'");
			$db->setQuery($query3);
			$items3 = $db->loadObject();

			$query->delete("#__gm_ceiling_day_off")
				->where("id_user = '$id' and date_from between '$date 00:00:00' and '$date 23:59:59'");
			$db->setQuery($query);
			$db->execute();

			// письмо
			$mailer = JFactory::getMailer();
			$config = JFactory::getConfig();
			$sender = array(
				$config->get('mailfrom'),
				$config->get('fromname')
			);
			$mailer->setSender($sender);
			$mailer->addRecipient($items3->email);
			$body = "Здравствуйте, выходные часы $date числа были удалены \n";
			$body .= "\n";
			$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://$server_name/\">http://$server_name</a>";		
			$mailer->setSubject('Выходные часы');
			$mailer->setBody($body);
			$send = $mailer->Send();

			$query2->select("id_user")
				->from("#__gm_ceiling_day_off")
				->where("id_user = '$id' and date_from = '$date1 00:00:00' and date_to = '$date2 23:59:59'");
			$db->setQuery($query2);
			$items2 = $db->loadObject();

			return $items2;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function GetGaugingForSaveDayOff($id, $date1, $date2) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select("count(id) as count")
				->from("#__gm_ceiling_projects")
				->where("project_calculator = '$id' and project_calculation_date between '$date1' and '$date2'");
			$db->setQuery($query);
			$items = $db->loadObject();
			
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getFreeGaugers($date_time){
		try
		{
			$all_gaugers = $this->getDatas(1);
			$gaugers_id = [];
			foreach ($all_gaugers as $gauger) {
				array_push($gaugers_id,$gauger->id);
			}
			$measure_date = explode(" ",$date_time)[0];
			$measure_time = explode(" ",$date_time)[1];
			$times = [
				"09:00:00" => $gaugers_id,
				"10:00:00" => $gaugers_id,
				"11:00:00" => $gaugers_id,
				"12:00:00" => $gaugers_id,
				"13:00:00" => $gaugers_id,
				"14:00:00" => $gaugers_id,
				"15:00:00" => $gaugers_id,
				"16:00:00" => $gaugers_id,
				"17:00:00" => $gaugers_id,
				"18:00:00" => $gaugers_id,
				"19:00:00" => $gaugers_id,
				"20:00:00" => $gaugers_id
			];
			$days_off =[];
	        foreach ($all_gaugers as $gauger) {
	        	array_push($days_off,$this->FindFreeDay($gauger->id,$measure_date));
			}
			foreach ($days_off as $value) {
				foreach ($value as $val) {
					$time_from = explode(" ",$val->date_from)[1];
					$time_to = explode(" ",$val->date_to)[1];
	    			if($measure_time>=$time_from && $measure_time<=$time_to){
	    				foreach ($times[$measure_time] as $key => $u_id) {
	    					if($u_id == $val->id_user){
	    						array_splice($times[$measure_time],$key,1);
	    					}
	    				}
	    			}
		    		
	    		}
			}
			foreach ($all_gaugers as $gauger) {        	
	            $measures_times = $this->GetAllGaugingOfGaugers($gauger->id,$measure_date,$measure_date);
	            foreach($measures_times as $time){
	                $time = explode(" ",$time->project_calculation_date)[1];
	               	if($time == $measure_time){
	               		foreach ($times[$measure_time] as $key => $value) {
	               			if($value == $gauger->id){
	    						array_splice($times[$measure_time],$key,1);
	    					}
	               		}
					}
	            }    
	        }
	        return $times[$measure_time];
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getFreeGaugingTimes($date){
		try
		{
			$result = [];
	        $all_gaugers = $this->getDatas(1);
	        $gaugers_count = count($all_gaugers);
	        $times = [
	            "09:00:00" => $gaugers_count,
	            "10:00:00" => $gaugers_count,
	            "11:00:00" => $gaugers_count,
	            "12:00:00" => $gaugers_count,
	            "13:00:00" => $gaugers_count,
	            "14:00:00" => $gaugers_count,
	            "15:00:00" => $gaugers_count,
	            "16:00:00" => $gaugers_count,
	            "17:00:00" => $gaugers_count,
	            "18:00:00" => $gaugers_count,
	            "19:00:00" => $gaugers_count,
	            "20:00:00" => $gaugers_count
	        ];
	        $days_off =[];
	        foreach ($all_gaugers as $gauger) {
	        	array_push($days_off,$this->FindFreeDay($gauger->id,$date));
			}
			foreach ($days_off as $value) {
				foreach ($value as $val) {
					$time_from = explode(" ",$val->date_from)[1];
					$time_to = explode(" ",$val->date_to)[1];
					foreach($times as $time => $value){
		    			if($time>=$time_from && $time<=$time_to){
		    				if($times[$time]>0){
				                $times[$time]--;
			                }
		    			}
		    		}
	    		}
			}
	        foreach ($all_gaugers as $gauger) {        	
	            $measures_times = $this->GetAllGaugingOfGaugers($gauger->id,$date,$date);
	            foreach($measures_times as $time){
	                $time = explode(" ",$time->project_calculation_date)[1];
	                if($times[$time]>0){
		                $times[$time]--;
	                }
	            }
	            
	        }
	        foreach ($times as $key => $value) {
	            if($value != 0){
	                array_push($result,$key);
	            }
	            
	        }
	        return $result;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

}

