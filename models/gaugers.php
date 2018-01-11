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
			
			$query->insert('#__gm_ceiling_day_off')
			->columns("id_user, date_from, date_to")
			->values("'$id', '$date1', '$date2'");
			$db->setQuery($query);
			$db->execute();
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

			$query2->select("name")
				->from('#__users as users')
				->where("id = $id_gauger");
			
			$query->select("project_calculation_date, project_info, ($query2) as project_calculator")
				->from('#__gm_ceiling_projects as projects')
				->where("project_calculator = $id_gauger and project_calculation_date between '$date 00:00:00' and '$date 23:59:59'")
				->order('project_calculation_date');
			$db->setQuery($query);

			throw new Exception($query);
			
			
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

}

