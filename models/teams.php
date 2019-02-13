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
class Gm_ceilingModelTeams extends JModelItem {

	function getData() {}

	function getDatas($dealerId) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('users.id, users.name')
				->from('#__users as users')
				->innerJoin('#__user_usergroup_map as usergroup ON usergroup.user_id = users.id')
				->where("users.dealer_id = '$dealerId' and usergroup.group_id = '11'");
			$db->setQuery($query);

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getMounterBrigade($brigade_id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$whereAll = "";
			foreach ($brigade_id as $value) {
				if ($whereAll == "") {
					$whereAll .= "id_brigade = '$value->id'";
				} else {
					$whereAll .= " or id_brigade = '$value->id'";				
				}
			}
			
			$query->select('mounters.id, mounters.name, map.id_brigade')
				->from('#__gm_ceiling_mounters as mounters')
				->innerJoin('#__gm_ceiling_mounters_map as map ON map.id_mounter = mounters.id')
				->where("$whereAll");
			$db->setQuery($query);

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function GetAllMountingOfBrigades($masID, $date1, $date2) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$whereAll = "";
			foreach ($masID as $value) {
				if ($whereAll == "") {
					$whereAll .= '(m.mounter_id = '.$value;
				} else {
					$whereAll .= ' or m.mounter_id = '.$value;				
				}
			}
			$whereAll .= ") and m.date_time between '$date1 00:00:00' and '$date2 23:59:59'";
			
			$query->select('m.mounter_id AS project_mounter, m.date_time AS project_mounting_date, p.read_by_mounter, p.project_status')
				->from('#__gm_ceiling_projects AS p')
				->innerJoin('#__gm_ceiling_projects_mounts AS m ON m.project_id = p.id')
				->where("$whereAll")
				->order('m.date_time');
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
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

	function GetMountingBrigadeDay($id, $date) {
		try
		{
			$user       = JFactory::getUser();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			$query4 = $db->getQuery(true);

			$query2->select("SUM(calculations.n5)")
				->from("#__gm_ceiling_calculations as calculations")
				->where("calculations.project_id = projects.id");

			$query3->select("SUM(calculations.mounting_sum)")
				->from("#__gm_ceiling_calculations as calculations")
				->where("calculations.project_id = projects.id");

		 	$query->select("DISTINCT projects.id, m.date_time AS project_mounting_date, projects.project_info, ($query2) as perimeter, ($query3) as salary, projects.dealer_manager_note,projects.dealer_calculator_note, projects.dealer_chief_note, projects.gm_manager_note,projects.gm_calculator_note, projects.gm_chief_note,projects.read_by_mounter, projects.project_status")
				->from('#__gm_ceiling_projects as projects')
				->innerJoin('#__gm_ceiling_projects_mounts AS m ON m.project_id = projects.id')
				->where("m.mounter_id = '$id' and m.date_time between '$date 00:00:00' and '$date 23:59:59' and projects.project_status IN (5, 6, 7, 8, 10, 16, 11, 12, 17, 19,24,25,26,27,28,29)")
				->order('m.date_time');
			$db->setQuery($query);
			$items = $db->loadObjectList(); 

			$query4->select("date_from, date_to")
				->from('#__gm_ceiling_day_off')
				->where("id_user = '$id' and date_from between '$date 00:00:00' and '$date 23:59:59'");
			$db->setQuery($query4);
			$day_off = $db->loadObject();
			
			$index = 0;
			$was_break = false;
            //поиск индекса для вставки и замена даты на просто время
			for($i=0;$i<count($items);$i++){
                if(strtotime($items[$i]->project_mounting_date)>=strtotime($day_off->date_from)){
					$index = $i;
					$was_break = true;
                    break;
                }
			}
			($index == 0 && !$was_break) ? $index = count($items) : 0;
            for($i=0;$i<count($items);$i++){
                $items[$i]->project_mounting_date = substr($items[$i]->project_mounting_date,11,5);
			}
			
			//создание нового массива
			if (!empty($day_off)) {
				$day = array(
					'id'=>NULL,
					'project_mounting_date' => substr($day_off->date_from,11,5) .' - '. substr($day_off->date_to,11,5),
					'project_info' =>'Выходной',
					'perimeter' => NULL,
					'salary' => NULL,
					$note => NULL,
					'read_by_mounter' => NULL,
					'project_status' => NULL
				);
				$day = array((object)$day);
				array_splice( $items,$index,0,$day);
			}	
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function SaveFreeDay($id, $date1, $date2) {
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

	public function GetMountingForSaveDayOff($id, $date1, $date2) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select("count(p.id) as count")
				->from("#__gm_ceiling_projects AS p")
				->innerJoin('#__gm_ceiling_projects_mounts AS m ON m.project_id = p.id')
				->where("m.mounter_id = '$id' and m.date_time between '$date1' and '$date2'");
			$db->setQuery($query);
			$items = $db->loadObject();
			
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}



	public function getAllItems() {
		try
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->select('*');
			$query->from('`#__gm_ceiling_groups`');
			$db->setQuery($query);
			$items = $db->loadAssocList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}

