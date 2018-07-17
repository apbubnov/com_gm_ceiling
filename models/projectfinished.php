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
class Gm_ceilingModelProjectfinished extends JModelItem {

	function GetData() {
		try
		{
			$user    = JFactory::getUser();

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('projects.read_by_chief, projects.id, mp.mount_end as project_mounting_end, users.name, projects.mounter_note, projects.gm_mounter_note, projects.project_info')
				->from('#__gm_ceiling_projects as projects')
				->innerJoin("`#__gm_ceiling_projects_mounts` as mp on p.id = mp.project_id")
				->innerJoin('#__users as users ON users.id = mp.mounter_id')
				->where("users.dealer_id = '$user->dealer_id' and projects.project_status = '11'")
				->order("mp.mount_end DESC");
			$db->setQuery($query);

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function ChangeStatusOfRead($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->update('#__gm_ceiling_projects')
				->set("read_by_chief = 1")
				->where("id = '$id'");
			$db->setQuery($query);
			$db->execute();

		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}


	/* function getMounterBrigade($brigade_id) {
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
					$whereAll .= '(project_mounter = '.$value;
				} else {
					$whereAll .= ' or project_mounter = '.$value;				
				}
			}
			$whereAll .= ") and project_mounting_date between '$date1 00:00:00' and '$date2 23:59:59'";
			
			$query->select('project_mounter, project_mounting_date, read_by_mounter, project_status')
				->from('#__gm_ceiling_projects')
				->where("$whereAll")
				->order('project_mounting_date');
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
			if ($user->dealer_id == 1) {
				$note = "gm_calculator_note";
			} else {
				$note = "dealer_calculator_note";
			}

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);

			$query2->select("SUM(calculations.n5)")
				->from("#__gm_ceiling_calculations as calculations")
				->where("calculations.project_id = projects.id");

			$query3->select("SUM(calculations.mounting_sum)")
				->from("#__gm_ceiling_calculations as calculations")
				->where("calculations.project_id = projects.id");

			$query->select("DISTINCT projects.id, projects.project_mounting_date, projects.project_info, ($query2) as perimeter, ($query3) as salary, projects.$note as note, projects.read_by_mounter, projects.project_status")
				->from('#__gm_ceiling_projects as projects')
				->innerJoin('#__gm_ceiling_calculations as calculations ON calculations.project_id = projects.id')
				->where("project_mounter = '$id' and projects.project_mounting_date between '$date 00:00:00' and '$date 23:59:59'")
				->order('projects.project_mounting_date');
			$db->setQuery($query);

			$items = $db->loadObjectList();
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
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			$query4 = $db->getQuery(true);

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
			} else {
				$query->insert('#__gm_ceiling_day_off')
					->columns("id_user, date_from, date_to")
					->values("'$id', '$date1', '$date2'");
				$db->setQuery($query);
				$db->execute();
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
	} */
}

