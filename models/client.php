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

use Joomla\Utilities\ArrayHelper;
/**
 * Gm_ceiling model.
 *
 * @since  1.6
 */
class Gm_ceilingModelClient extends JModelItem
{
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
	protected function populateState()
	{
		try
		{
			$app = JFactory::getApplication('com_gm_ceiling');

			// Load state from the request userState on edit or from the passed variable on default
			if (JFactory::getApplication()->input->get('layout') == 'edit')
			{
				$id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.client.id');
			}
			else
			{
				$id = JFactory::getApplication()->input->get('id');
				JFactory::getApplication()->setUserState('com_gm_ceiling.edit.client.id', $id);
			}

			$this->setState('client.id', $id);

			// Load the parameters.
			$params       = $app->getParams();
			$params_array = $params->toArray();

			if (isset($params_array['item_id']))
			{
				$this->setState('client.id', $params_array['item_id']);
			}

			$this->setState('params', $params);
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		try
		{
			if ($this->_item === null)
			{
				$this->_item = false;

				if (empty($id))
				{
					$id = $this->getState('client.id');
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
	/*
			if (isset($this->_item->created_by) )
			{
				$this->_item->created_by_name = JFactory::getUser($this->_item->created_by)->name;
			}if (isset($this->_item->modified_by) )
			{
				$this->_item->modified_by_name = JFactory::getUser($this->_item->modified_by)->name;
			}*/
			return $this->_item;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getClientById($id)
	{
		try
		{
			$db    = JFactory::getDbo();
			$id = $db->escape($id, true);
			$query = $db->getQuery(true);
			$query
				->select("*")
				->from("`#__gm_ceiling_clients`")
				->where("`id` = $id");
			$db->setQuery($query);
			$db->execute();
			$items = $db->loadObject();
			return $items;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function create($dealer_id){
		try{
			$date = date('Y-m-d H:i:s');
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->insert("`#__gm_ceiling_clients`")
				->columns('`client_name`, `dealer_id`, `manager_id`, `created`')
				->values("' ', $dealer_id, $dealer_id, '$date'");
			$db->setQuery($query);
			$db->execute();
			$last_id = $db->insertid();

			$query = $db->getQuery(true);
			$query
				->update("`#__gm_ceiling_clients`")
				->set("`client_name` = '$last_id'")
				->where("`id` = $last_id");
			$db->setQuery($query);
			$db->execute();
			return $last_id;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function updateClient($id,$data = null,$dealer_id = null){
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update("`#__gm_ceiling_clients`");
			if (!empty($data))
			{
				$query->set("`client_name` = '$data'");
			}
			if (!empty($dealer_id))
			{
				$query->set("`dealer_id` = $dealer_id");
			}
			$query->where("id = $id");
			
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function updateClientManager($id,$manager_id){
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update("`#__gm_ceiling_clients`");
			$query->set("manager_id = $manager_id");
			$query->where("id = $id");
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function updateClientSex($id,$sex){
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update("`#__gm_ceiling_clients`");
			$query->set("sex = $sex");
			$query->where("id = $id");
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    
    public function checkIsDealer($phone){
        try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
            $query
                ->select('*')
                ->from("`#__users`")
                ->where("username = $phone");
			$db->setQuery($query);
            $items = $db->loadObjectList();
            return count($items);
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
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
	public function getTable($type = 'Client', $prefix = 'Gm_ceilingTable', $config = array())
	{
		try
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_gm_ceiling/tables');

			return JTable::getInstance($type, $prefix, $config);
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Get the id of an item by alias
	 *
	 * @param   string  $alias  Item alias
	 *
	 * @return  mixed
	 */
	public function getItemIdByAlias($alias)
	{
		try
		{
			$table = $this->getTable();

			$table->load(array('alias' => $alias));

			return $table->id;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
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
	public function checkin($id = null)
	{
		try
		{
			// Get the id.
			$id = (!empty($id)) ? $id : (int) $this->getState('client.id');

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
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
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
	public function checkout($id = null)
	{
		try
		{
			// Get the user id.
			$id = (!empty($id)) ? $id : (int) $this->getState('client.id');

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
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Get the name of a category by id
	 *
	 * @param   int  $id  Category id
	 *
	 * @return  Object|null	Object if success, null in case of failure
	 */
	public function getCategoryName($id)
	{
		try
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
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Publish the element
	 *
	 * @param   int  $id     Item id
	 * @param   int  $state  Publish state
	 *
	 * @return  boolean
	 */
	public function publish($id, $state)
	{
		try
		{
			$table = $this->getTable();
			$table->load($id);
			//$table->state = $state;

			return $table->store();
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to delete an item
	 *
	 * @param   int  $id  Element id
	 *
	 * @return  bool
	 */
	public function delete($id)
	{
		try
		{
			
			$table = $this->getTable();

			return $table->delete($id);
			
			/*$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->delete('#__gm_ceiling_clients')
				->where('id = ' . $id);
			$db->setQuery($query);
			$db->execute($query);
			return true;*/
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

    public function add_client_timer_spec( $id_client, $rek)
    {
    	try
    	{
	        $date = date("Y-m-d H:i:s");
	        $db = $this->getDbo();

			$query = $db->getQuery(true);
	        $query ->select('*')
	            ->from('#__gm_ceiling_client_timer_spec')
				->where('client_id = '. $id_client);
			$db->setQuery($query);
			$client = $db->loadObject();
			if(empty($client->id))
			{
				$query = $db->getQuery(true);
	        $query
	            ->insert ($db->quoteName('#__gm_ceiling_client_timer_spec'))
	            ->columns ('client_id, date, rek')
	            ->values(
	                $id_client . ', '
	                . $db->quote($date). ', '
	                .$db->quote($rek));
	        $db->setQuery($query);
	        $s = $db->execute();

	        $query = $db->getQuery(true);
	        $query ->select('id')
	            ->from('#__gm_ceiling_client_timer_spec')
	            ->where('client_id = '. $id_client .' AND `date` = '. $db->quote($date));
				
	        $db->setQuery($query);
			$client_timer_spec = $db->loadObject();

	            $query = $db->getQuery(true);
	            $columns = array('client_id', 'date_time', 'text');
	            $query
	                ->insert($db->quoteName('#__gm_ceiling_client_history'))
	                ->columns($db->quoteName($columns))
	                ->values("$id_client , NOW(), 'Клиенту отправлено письмо с 10% скидкой'");
	            $db->setQuery($query);
	            $db->execute();

	        return $client_timer_spec;
			}
			else return 0;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getClientInfoApi($id)
    {
    	try
    	{
	        $db = $this->getDbo();
	        $query = $db->getQuery(true);
	        $query ->select('client_timer_spec.date as date, phones.number as number')
	            ->from('#__gm_ceiling_client_timer_spec AS client_timer_spec')
	            ->join('LEFT', '#__gm_ceiling_api_phones AS phones ON client_timer_spec.rek = phones.id')
	            ->where('client_timer_spec.id = '. $id);
	        $db->setQuery($query);
	        $data = $db->loadObject();
	        $data->date = strtotime($data->date);
	        return $data;
	    }
	    catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function updateClientNew($id, $fio, $phone,$adress,  $project_calculation_date)
    {
    	try
    	{
	        $db = $this->getDbo();
	        $query = $db->getQuery(true);
	        $query ->select('*')
	            ->from('#__gm_ceiling_client_timer_spec')
	            ->where('id = '. $id);
	        $db->setQuery($query);
	        $client_id =  $db->loadObject()->client_id;

	        $query = $db->getQuery(true);
	        $query->update('`#__gm_ceiling_clients`')
	            ->set('client_name = ' . $db->quote($fio))
	            ->set('type_id =  1')
	            ->set('manager_id =  1')
	            ->where('id = ' .$client_id );
	        $db->setQuery($query);
	        $db->execute();


	        $query = $db->getQuery(true);
	        $query
	            ->insert ($db->quoteName('#__gm_ceiling_clients_contacts'))
	            ->columns ('client_id, phone')
	            ->values(
	                $db->quote($client_id) . ', '
	                .$db->quote($phone));
	        $db->setQuery($query);
	        $db->execute();

	        $query = $db->getQuery(true);
	        $query
	            ->select(' project.id AS project_id')
	            ->from('`#__gm_ceiling_projects` AS project')
	            ->where('project.client_id =' . $client_id);
	        $db->setQuery($query);
	        $project_id = $db->loadObject();

	        $query = $db->getQuery(true);
	        $query->update('`#__gm_ceiling_projects`')
	            ->set('	project_info = ' . $db->quote($adress))
	            ->set('project_calculation_date = ' .$db->quote($project_calculation_date))
	            ->where('id = ' .$project_id->project_id );
	        $db->setQuery($query);
	        $db->execute();


	        $query = $db->getQuery(true);
	        $columns = array('client_id', 'date_time', 'text');
	        $query
	            ->insert($db->quoteName('#__gm_ceiling_client_history'))
	            ->columns($db->quoteName($columns))
	            ->values("$client_id , NOW(), 'Клиент подтвердил ФИО и телефон для 10% скидки'");
	        $db->setQuery($query);
	        $db->execute();

	        return 1;
	    }
	    catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function getClientBirthday($id)
    {
    	try
    	{
	        $db = $this->getDbo();
	        $query = $db->getQuery(true);
	        $query ->select('birthday')
	            ->from('#__gm_ceiling_clients')
	            ->where('id = '. $id);
	        $db->setQuery($query);
	        $data = $db->loadObject();
	        return $data;
	    }
	    catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function addBirthday($id_client, $birthday)
    {
    	try
    	{
	        $db = $this->getDbo();
	        $query = $db->getQuery(true);
			$query->update('`#__gm_ceiling_clients`')
				->set('	birthday = ' . $db->quote($birthday))
				->where('id = ' .$id_client);
			$db->setQuery($query);
			$db->execute();
			return 1;
	    }
	    catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getDealer($client_id)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query ->select('dealer_id')
                ->from('#__gm_ceiling_clients')
                ->where('id = '. $client_id);
            $db->setQuery($query);
            $data = $db->loadObject();

            $query = $db->getQuery(true);
            $query ->select('name')
                ->from('#__users')
                ->where('id = '. $data->dealer_id);
            $db->setQuery($query);
            $result = $db->loadObject()->name;
            return $result;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }
	
}
