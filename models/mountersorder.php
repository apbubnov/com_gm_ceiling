<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');


/**
 * Gm_ceiling model.
 *
 * @since  1.6
 */
class Gm_ceilingModelMountersorder extends JModelItem {

	function getData() {}

	public function GetCalculation($project) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('calculations.id, calculations.calculation_title, calculations.details')
				->from('#__gm_ceiling_projects as projects')
				->innerJoin('#__gm_ceiling_calculations as calculations ON calculations.project_id = projects.id')
				->where("projects.id = '$project'")
				->order('calculations.id');
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function GetDates($proj_id, $stage) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('m.mount_start as project_mounting_start, m.mount_end as project_mounting_end, p.project_status')
			->from('#__gm_ceiling_projects as p')
			->innerJoin('#__gm_ceiling_projects_mounts as m ON m.project_id = p.id')
			->where("p.id = $proj_id and m.type = $stage");
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function MountingStart($id, $date, $stage) {
		try
		{
			$stage_map_status = array(
			    "1"=>16,
			    "2"=>27,
			    "3"=>28,
			    "4"=>29
			);
			$db = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->update('#__gm_ceiling_projects')
			->set("project_status = $stage_map_status[$stage]")
			->where('id = '.$id);
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->update('#__gm_ceiling_projects_mounts')
			->set('mount_start = \''.$date.'\'')
			->where("project_id = $id AND type = $stage");
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->select('client_id')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
			$db->setQuery($query);
			$items = $db->loadObjectList();
			$client = $items[0]->client_id;
	
			$note = "Монтаж по проекту №$id начат.";
			$query = $db->getQuery(true);
			$query->insert('#__gm_ceiling_client_history')
			->columns('client_id, date_time, text')
			->values("'$client', '$date', '$note'");
			$db->setQuery($query);
			$db->execute();

			// запись в project_history
			$query = $db->getQuery(true);
			$query->insert('#__gm_ceiling_projects_history')
			->columns('project_id, new_status, date_of_change')
			->values("'$id', '16', '$date'");
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->select('project_status')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
			$db->setQuery($query);

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function MountingComplited($id, $date, $note2, $note, $status, $stage) {
		try
		{
			$db = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->update('#__gm_ceiling_projects')
			->set("project_status = $status")
			->where('id = '.$id);
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->update('#__gm_ceiling_projects_mounts')
			->set('mount_end = \''.$date.'\'')
			->where("project_id = $id AND type = $stage");
			$db->setQuery($query);
			$db->execute();

			if (strlen($note2) != 0) {
				$query = $db->getQuery(true);
				$query->select('client_id')
				->from('#__gm_ceiling_projects')
				->where('id = '.$id);
				$db->setQuery($query);
				$items = $db->loadObjectList();
				$client = $items[0]->client_id;
		
				$query = $db->getQuery(true);
				$query->insert('#__gm_ceiling_client_history')
				->columns('client_id, date_time, text')
				->values("'$client', '$date', '$note2'");
				$db->setQuery($query);
				$db->execute();
			}

			// запись в project_history
			$query = $db->getQuery(true);
			$query->insert('#__gm_ceiling_projects_history')
			->columns('project_id, new_status, date_of_change')
			->values("'$id', $status, '$date'");
			$db->setQuery($query);
			$db->execute();

			// запись в note
			$contrProject = Gm_ceilingHelpersGm_ceiling::getController('project');
			$contrProject->addNote($id, $note, 6);

			$query = $db->getQuery(true);
			$query->select('project_status')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
			$db->setQuery($query);

			$items2 = $db->loadObjectList();
			return $items2;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function MountingUnderfulfilled($id, $date, $note) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			$query4 = $db->getQuery(true);
			$query5 = $db->getQuery(true);

			$query->update('#__gm_ceiling_projects')
			->set('project_status = 17')
			->where('id = '.$id);
			$db->setQuery($query);
			$db->execute();

			if (strlen($note) != 0) {
				$query2->select('client_id')
				->from('#__gm_ceiling_projects')
				->where('id = '.$id);
				$db->setQuery($query2);
				$items = $db->loadObjectList();
				$client = $items[0]->client_id;
		
				$query3->insert('#__gm_ceiling_client_history')
				->columns('client_id, date_time, text')
				->values("'$client', '$date', '$note'");
				$db->setQuery($query3);
				$db->execute();
			}

			// запись в project_history
			$query5->insert('#__gm_ceiling_projects_history')
			->columns('project_id, new_status, date_of_change')
			->values("'$id', '17', '$date'");
			$db->setQuery($query5);
			$db->execute();

			$query4->select('project_status')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
			$db->setQuery($query4);
			
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	function AllNMSEmails() {
		try
		{
			$user       = JFactory::getUser();
			$userId     = $user->get('id');
			if ($user->dealer_id == 1) {
				$groupe = 17;
			} else {
				$groupe = 12;
			}
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
                ->select('DISTINCT users.email')
                ->from('#__users as users')
                ->leftJoin('`rgzbn_users_dealer_id_map` as dm on dm.user_id = users.id')
                ->innerJoin('#__user_usergroup_map as map ON users.id = map.user_id')
                ->where("(users.dealer_id = $user->dealer_id and map.group_id = $groupe) OR (dm.dealer_id = $user->dealer_id AND dm.group_id = $groupe)");
			$db->setQuery($query);

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function DataOrder($id, $stage) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);

			$query2
                ->select("users.name")
			    ->from('#__users as users')
    			->where("users.id = m.mounter_id");

			$query
                ->select("p.project_info, m.date_time as project_mounting_date, m.mounter_id as project_mounter, ($query2) as project_mounter_name")
			    ->from('#__gm_ceiling_projects as p')
			    ->innerJoin('#__gm_ceiling_projects_mounts as m ON m.project_id = p.id')
		    	->where("p.id = $id AND m.type = $stage");
			$db->setQuery($query);

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function NamesMounters($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select("mounters.name")
			->from('#__gm_ceiling_mounters as mounters')
			->innerJoin('#__gm_ceiling_mounters_map as map ON mounters.id = map.id_mounter')
			->where("map.id_brigade = $id");
			$db->setQuery($query);
			$items = $db->loadObjectList();
			
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

}
