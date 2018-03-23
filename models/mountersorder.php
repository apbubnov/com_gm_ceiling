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
			
			$query->select('calculations.id, calculations.calculation_title, calculations.n1, calculations.details')
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function GetDates($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('project_mounting_start, project_mounting_end, project_status')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
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

	function MountingStart($id, $date) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			$query4 = $db->getQuery(true);
			$query5 = $db->getQuery(true);

			$query->update('#__gm_ceiling_projects')
			->set('project_mounting_start = \''.$date.'\'')
			->set('project_status = 16')
			->where('id = '.$id);
			$db->setQuery($query);
			$db->execute();

			$query3->select('client_id')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
			$db->setQuery($query3);
			$items = $db->loadObjectList();
			$client = $items[0]->client_id;
	
			$note = "Монтаж по проекту №$id начат.";
			$query4->insert('#__gm_ceiling_client_history')
			->columns('client_id, date_time, text')
			->values("'$client', '$date', '$note'");
			$db->setQuery($query4);
			$db->execute();

			// запись в project_history
			$query5->insert('#__gm_ceiling_projects_history')
			->columns('project_id, new_status, date_of_change')
			->values("'$id', '16', '$date'");
			$db->setQuery($query5);
			$db->execute();

			$query2->select('project_status')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
			$db->setQuery($query2);

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

	function MountingComplited($id, $date, $note2, $note) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			$query4 = $db->getQuery(true);
			$query5 = $db->getQuery(true);
			$query6 = $db->getQuery(true);

			$query->update('#__gm_ceiling_projects')
			->set('project_mounting_end = \''.$date.'\'')
			->set('project_status = 11')
			->where('id = '.$id);
			$db->setQuery($query);
			$db->execute();

			if (strlen($note2) != 0) {
				$query2->select('client_id')
				->from('#__gm_ceiling_projects')
				->where('id = '.$id);
				$db->setQuery($query2);
				$items = $db->loadObjectList();
				$client = $items[0]->client_id;
		
				$query3->insert('#__gm_ceiling_client_history')
				->columns('client_id, date_time, text')
				->values("'$client', '$date', '$note2'");
				$db->setQuery($query3);
				$db->execute();
			}

			// запись в project_history
			$query5->insert('#__gm_ceiling_projects_history')
			->columns('project_id, new_status, date_of_change')
			->values("'$id', '11', '$date'");
			$db->setQuery($query5);
			$db->execute();

			// запись в note
			$user = JFactory::getUser();
			if ($user->dealer_id == 1) {
				$ForWho = "gm_mounter_note";
			} else {
				$ForWho = "mounter_note";
			}
			$query6->update('#__gm_ceiling_projects')
			->set("$ForWho = '$note'")
			->where("id = '$id'");
			$db->setQuery($query6);
			$db->execute();

			$query4->select('project_status')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
			$db->setQuery($query4);

			$items2 = $db->loadObjectList();
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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

			$query->select('users.email')
			->from('#__users as users')
			->innerJoin('#__user_usergroup_map as map ON users.id = map.user_id')		
			->where('users.dealer_id = '.$user->dealer_id.' and map.group_id = '.$groupe);
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

	function DataOrder($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);

			$query2->select("users.name")
			->from('#__users as users')
			->where("users.id = projects.project_mounter");

			$query->select("projects.project_info, projects.project_mounting_date, projects.project_mounter, ($query2) as project_mounter_name")
			->from('#__gm_ceiling_projects as projects')
			->where("projects.id = $id");
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

}
