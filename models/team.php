<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

use Joomla\Utilities\ArrayHelper;
/**
 * Gm_ceiling model.
 *
 * @since  1.6
 */
class Gm_ceilingModelTeam extends JModelItem
{

	function GetData($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('name, username, email')
				->from('#__users')
				->where("id = '$id'");
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function GetMounters($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('*')
				->from('#__gm_ceiling_mounters as mounters')
				->innerJoin('#__gm_ceiling_mounters_map as map ON mounters.id = map.id_mounter')			
				->where("map.id_brigade = '$id'");
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	function GetAllBrigade($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('`users`.`id` as `id`, `users`.`name` as `name`')
				->from('`#__users` as `users`')
				->innerJoin('`#__user_usergroup_map` as `map` ON `users`.`id` = `map`.`user_id`')			
				->where("`users`.`dealer_id` = '$id' and `map`.`group_id` = 11");
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function GetProjects($id, $date, $date2 = null) {
		try
		{
			if ($date2 == null) {
				$countdays = cal_days_in_month(CAL_GREGORIAN, substr($date, 5), substr($date, 0, 4));
				$date1 = $date."-01 00:00:00";
				$date2 = $date."-".$countdays." 23:59:59";
			} else {
				$date1 = $date;
			}
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);

			$query2->select('sum(calculations.mounting_sum)')
				->from('#__gm_ceiling_calculations as calculations')
				->where("projects.id = calculations.project_id");

			$query->select("projects.id, ($query2) as mounting_sum, projects.new_mount_sum, transport, distance, distance_col")
				->from('#__gm_ceiling_projects as projects')
				->innerJoin("`#__gm_ceiling_projects_mounts` as mp on projects.id = mp.project_id")
				->where("mp.mounter_id = '$id' and mp.date_time between '$date1' and '$date2'");
			$db->setQuery($query);
			$items = $db->loadObjectList();

			$user = JFactory::getUser();

			$query3->select('transport as transport_mp, distance as distance_mp')
				->from('#__gm_ceiling_mount')
				->where("user_id = '$user->dealer_id'");
			$db->setQuery($query3);
			$items3 = $db->loadObject();

			foreach ($items as $value) {
                $calc_transport = 0;
                if ($value->transport == 1) {
                    $calc_transport = $items3->transport_mp * $value->distance_col;
                } else if ($value->transport == 2) {
                    $calc_transport = $items3->distance_mp * $value->distance_col * $value->distance;
                }
                $value->mounting_sum += $calc_transport;
            }

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/* function GetProjectsFilter($id, $datetime1, $datetime2) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);

			$query2->select('sum(calculations.mounting_sum)')
				->from('#__gm_ceiling_calculations as calculations')
				->where("projects.id = calculations.project_id");

			$query->select("DISTINCT projects.id, ($query2) as mounting_sum, projects.new_mount_sum")
				->from('#__gm_ceiling_projects as projects')
				//->innerJoin('#__gm_ceiling_calculations as calculations ON projects.id = calculations.project_id')						
				->where("project_mounter = '$id' and project_mounting_date between '$datetime1' and '$datetime2'");
			$db->setQuery($query);
			$items = $db->loadObjectList();

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	} */

	function MoveBrigade($id_mounter, $id_brigade) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->update('`#__gm_ceiling_mounters_map`')
				->set("id_brigade = $id_brigade")
				->where("id_mounter = $id_mounter");
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function delete_mounter($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->delete('`#__gm_ceiling_mounters`')
				->where("id = $id");
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since    1.6
	 *
	 */
	/*protected function populateState()
	{
		$app = JFactory::getApplication('com_gm_ceiling');

		// Load state from the request userState on edit or from the passed variable on default
		if (JFactory::getApplication()->input->get('layout') == 'edit')
		{
			$id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.team.id');
		}
		else
		{
			$id = JFactory::getApplication()->input->get('id');
			JFactory::getApplication()->setUserState('com_gm_ceiling.edit.team.id', $id);
		}

		$this->setState('team.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			//$this->setState('team.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	/*public function &getData($id = null)
	{
		if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id))
			{
				$id = $this->getState('team.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id))
			{
				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->_item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->_item = ArrayHelper::toObject($properties, 'JObject');
			}
		}

		if (isset($this->_item->created_by) )
		{
			$this->_item->created_by_name = JFactory::getUser($this->_item->created_by)->name;
		}if (isset($this->_item->modified_by) )
		{
			$this->_item->modified_by_name = JFactory::getUser($this->_item->modified_by)->name;
		}

		return $this->_item;
	}

	/**
	 * Get an instance of JTable class
	 *
	 * @param   string  $type    Name of the JTable class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 */
	/*public function getTable($type = 'Team', $prefix = 'Gm_ceilingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_gm_ceiling/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 *
	 * @param   string  $alias  Item alias
	 *
	 * @return  mixed
	 */
	/*public function getItemIdByAlias($alias)
	{
		$table = $this->getTable();

		$table->load(array('alias' => $alias));

		return $table->id;
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	/*public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('team.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	/*public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('team.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = JFactory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Get the name of a category by id
	 *
	 * @param   int  $id  Category id
	 *
	 * @return  Object|null	Object if success, null in case of failure
	 */
	/*public function getCategoryName($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('title')
			->from('#__categories')
			->where('id = ' . $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Publish the element
	 *
	 * @param   int  $id     Item id
	 * @param   int  $state  Publish state
	 *
	 * @return  boolean
	 */
	/*public function publish($id, $state)
	{
		$table = $this->getTable();
		$table->load($id);
		$table->state = $state;

		return $table->store();
	}

	/**
	 * Method to delete an item
	 *
	 * @param   int  $id  Element id
	 *
	 * @return  bool
	 */
	/*public function delete($id)
	{
		$table = $this->getTable();

		return $table->delete($id);
	}*/

	
}
