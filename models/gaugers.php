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

	function getData($dealerId) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			if ($dealerId == 1) {
				$type = 22;
			} else {
				$type = 21;
			}
			
			$query->select('users.id, users.name')
				->from('#__users as users')
				->innerJoin('#__user_usergroup_map as usergroup ON usergroup.user_id = users.id')
				->where("dealer_id = $dealerId and usergroup.group_id = '$type'");
			$db->setQuery($query);

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function GetAllGaugingOfGaugers($masID, $date1, $date2) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$whereAll = "";
			foreach ($masID as $value) {
				if ($whereAll == "") {
					$whereAll .= "(project_calculator = '$value'";
				} else {
					$whereAll .= " or project_calculator = '$value'";				
				}
			}
			$whereAll .= ") and project_calculation_date between '$date1 00:00:00' and '$date2 23:59:59'";
			
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function SaveDayOff($id, $date1, $date2) {
		try
		{
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
				$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://test1.gm-vrn.ru/\">http://test1.gm-vrn.ru</a>";		
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
				$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://test1.gm-vrn.ru/\">http://test1.gm-vrn.ru</a>";		
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            //поиск индекса для вставки и замена даты на просто время
            for ($i=0; $i < count($items); $i++) {
                if (strtotime($items[$i]->project_calculation_date) >= strtotime($items2->date_from)) {
                    $index = $i;
                    break;
                }
            }
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
			
			var_dump($items);
			
			return $items;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
			$date = date("d.m.Y H:i:s");
			$files = "components/com_gm_ceiling/";
			file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
			throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
			$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://test1.gm-vrn.ru/\">http://test1.gm-vrn.ru</a>";		
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

}

