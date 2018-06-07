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
class Gm_ceilingModelProject extends JModelItem
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
				$id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.project.id');
			}
			else
			{
				$id = JFactory::getApplication()->input->get('id');
				JFactory::getApplication()->setUserState('com_gm_ceiling.edit.project.id', $id);
			}

			$this->setState('project.id', $id);

			// Load the parameters.
			$params       = $app->getParams();
			$params_array = $params->toArray();

			if (isset($params_array['item_id']))
			{
				//$this->setState('project.id', $params_array['item_id']);
			}

			$this->setState('params', $params);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
					$id = $this->getState('project.id');
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
			//throw new Exception($this->_item->project_mounting_date , 1);

			if (isset($this->_item->created_by) )
			{
				$this->_item->created_by_name = JFactory::getUser($this->_item->created_by)->name;
			}
			if (isset($this->_item->modified_by) )
			{
				$this->_item->modified_by_name = JFactory::getUser($this->_item->modified_by)->name;
			}
					$this->_item->project_mounting_daypart = $this->_item->project_mounting_daypart;
					$this->_item->project_mounting_date = $this->_item->project_mounting_date;	
				if (isset($this->_item->client_id) && $this->_item->client_id != '') {
					$this->_item->_client_id = $this->_item->client_id;
					if (is_object($this->_item->client_id)){
						$this->_item->client_id = \Joomla\Utilities\ArrayHelper::fromObject($this->_item->client_id);
					}
					$values = (is_array($this->_item->client_id)) ? $this->_item->client_id : explode(',',$this->_item->client_id);

					$textValue = array();
					foreach ($values as $value)
					{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query
							->select('`#__gm_ceiling_clients`.`id`')
							->select('`#__gm_ceiling_clients`.`client_name`')
							->select('`#__gm_ceiling_clients`.`dealer_id`')
							->from($db->quoteName('#__gm_ceiling_clients'))
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
						$db->setQuery($query);
						$results = $db->loadObject();
						if ($results) {
							$textValue[] = $results->client_name;
							$textValue2[] = $results->dealer_id;
							$textValue3[] = $results->id;
						}
					}
					$this->_item->id_client_num = !empty(intval($this->_item->_client_id))?$this->_item->_client_id:$this->_item->id_client;
                    $this->_item->client_id = !empty($textValue) ? implode(', ', $textValue) : $this->_item->client_id;
                    $this->_item->dealer_id = !empty($textValue2) ? implode(', ', $textValue2) : $this->_item->dealer_id;
                    $this->_item->id_client = !empty($textValue3) ? implode(', ',$textValue3) : $this->_item->client_id;
				}

				if (isset($this->_item->project_mounter) && $this->_item->project_mounter != '') {
					if (is_object($this->_item->project_mounter)){
						$this->_item->project_mounter = \Joomla\Utilities\ArrayHelper::fromObject($this->_item->project_mounter);
					}
					$values = (is_array($this->_item->project_mounter)) ? $this->_item->project_mounter : explode(',',$this->_item->project_mounter);

					$textValue = array();
					foreach ($values as $value)
					{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query
							->select('`#__gm_ceiling_groups_2483036`.`name`')
							->from($db->quoteName('#__gm_ceiling_groups', '#__gm_ceiling_groups_2483036'))
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
						$db->setQuery($query);
						$results = $db->loadObject();
						if ($results) {
							$textValue[] = $results->team_title;
						}
					}

					$this->_item->project_mounter = !empty($textValue) ? implode(', ', $textValue) : $this->_item->project_mounter;

				}
				
			return $this->_item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getCalculationIdById($project_id)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->from('`#__gm_ceiling_calculations`')
                ->select('id')
                ->where("project_id = '$project_id'");
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getClientPhone($client_id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select(' client_contact.phone AS client_contacts')
	            ->from('`#__gm_ceiling_clients_contacts` AS client_contact')
	            ->select('client.id AS client_id')
	            ->join('LEFT', '`#__gm_ceiling_clients` AS client ON client.id = client_contact.client_id')
	            ->where('client.client_name =\''. $client_id.'\'');

	        $db->setQuery($query);
	        $result = $db->loadObject();
	        return $result;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getClientPhones($client_id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select('phone')
	            ->from('`#__gm_ceiling_clients_contacts` ')
	            ->where('client_id =\''. $client_id.'\'');

	        $db->setQuery($query);
	        $result = $db->loadObjectList();
	        return $result;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getMount($project_id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select(' projects.project_mounter AS project_mounter')
	            ->from('`#__gm_ceiling_projects` AS projects')
	            ->select('users.name AS name, users.id AS id')
	            ->join('LEFT', '`#__users` AS users ON users.id = projects.project_mounter')
	            ->where('projects.id =\''. $project_id.'\'');

	        $db->setQuery($query);
	        $result = $db->loadObject();
	        return $result;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getGauger($project_id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select('projects.project_calculator, users.name')
	            ->from('`#__gm_ceiling_projects` AS projects')
	            ->join('LEFT', '`#__users` AS users ON users.id = projects.project_calculator')
	            ->where("projects.id = '$project_id'");

	        $db->setQuery($query);
	        $result = $db->loadObject();
	        return $result;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getMounterBrigade($brigade_id) 
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('mounters.name')
				->from('#__gm_ceiling_mounters as mounters')
				->innerJoin('#__gm_ceiling_mounters_map as map ON map.id_mounter = mounters.id')
				->where("map.id_brigade = ". $brigade_id);
				//print_r($brigade_id); exit;
			$db->setQuery($query);

			$items = $db->loadObjectList();
		
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function getProjectsByClientID($id){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('id')
				->select('project_status')
				->select('api_phone_id')
				->select('client_id')
				->from('`#__gm_ceiling_projects`')
				->where("client_id = $id");
			$db->setQuery($query);
			$result = $db->loadObjectList();
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
	public function getTable($type = 'Project', $prefix = 'Gm_ceilingTable', $config = array())
	{
		try
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_gm_ceiling/tables');

			return JTable::getInstance($type, $prefix, $config);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
			$id = (!empty($id)) ? $id : (int) $this->getState('project.id');

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
			$id = (!empty($id)) ? $id : (int) $this->getState('project.id');

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Запуск в производство
	 *
	 * @param   int  $id     Item id
	 * @param   int  $state  Publish state
	 *
	 * @return  boolean
	 */
	/*public function activate($project_id)
	{
		$table = $this->getTable();
		$table->load($project_id);
		$table->project_status = 1;
		$return = $table->store();
		JFactory::getApplication()->enqueueMessage($return, 'error');

		return $return;
	}*/
	
	public function check($project_id, $type, $check, $new_value)
	{
		try
		{
			if($project_id > 0 && ($check == 0 || $check == 1)){
				$table = $this->getTable();
				$table->load($project_id);
				if($type == 0) {
					$table->project_check = $check;
				} elseif($type == 1) {
					$table->sum_check = $check;
					$table->new_project_sum = $new_value;
				} elseif($type == 2) {
					$table->cost_check = $check;
					$table->new_material_sum = $new_value;
				} elseif($type == 3) {
					$table->mounting_check = $check;
					$table->new_mount_sum = $new_value;
				} elseif($type == 4) {
					$table->spend_check = $check;
					$table->new_extra_spend = $new_value;
				} else {
					return 1;
				}
				$return = $table->store();
			}

			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function return_project($id)
	{
		try
		{
			$table = $this->getTable();
			$table->load($id);
			$table->project_status = 1;
            $return = $table->store();
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->save($id,1);
			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function activate($data, $status)
	{
		try
		{

			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->update('`#__gm_ceiling_projects`')
				->set('project_verdict = ' . $db->quote($data->project_verdict))
				->set('project_note = ' . $db->quote($data->project_note))
				->set('gm_calculator_note = ' . $db->quote($data->gm_calculator_note))
				->set('dealer_calculator_note = ' . $db->quote($data->dealer_calculator_note))
				->set('gm_manager_note = ' . $db->quote($data->gm_manager_note))
				->set('dealer_manager_note = ' . $db->quote($data->dealer_manager_note))
				->set('gm_chief_note = ' . $db->quote($data->gm_chief_note))
				->set('dealer_chief_note = ' . $db->quote($data->dealer_chief_note))
				->set('project_sum = ' . $db->quote($data->project_sum))
				->set('project_mounting_date = ' . $db->quote($data->project_mounting_date))
				->set('project_status = ' . $db->quote($status));
			if (empty($data->project_mounter)) $query->set('project_mounter = NULL');
			else $query->set('project_mounter = ' . $db->quote($data->project_mounter));
			if ($status == 3) {
				$query->set("project_mounting_date = '0000-00-00 00:00:00'");
				$query->set('project_mounter = NULL');
			}
			$query->where('id = ' . $data->id);
			$db->setQuery($query);
			$return = $db->execute();
				
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->save($data->id,$status);
			//JFactory::getApplication()->enqueueMessage($return, 'error');

			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

    public function activate_mount($id,$data)
    {
    	try
    	{
	        $db = $this->getDbo();
	        $query = $db->getQuery(true);
	        $query->update('`#__gm_ceiling_projects` AS p')
	            ->set('p.project_mounting_date = ' . $db->quote($data['project_mounting_from']))
	            ->set('p.project_mounter = ' . $db->quote($data['project_mounting']))
	            ->where('p.id = ' . $id);
	        $db->setQuery($query);
	        $result = $db->execute();

	        return $result;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	public function change_status($id, $project_status)
	{
		try
		{
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				if($project_status == 1) $table->project_verdict = "0";
				$table->project_status = $project_status;
			}
			$return = $table->store();
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->save($id,$project_status);
			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function change_discount($id, $discount)
	{
		try
		{
			$db = $this->getDbo();
	        $query = $db->getQuery(true);
				 $query->update('`#__gm_ceiling_calculations` AS c')
	            ->set('c.discount = ' . $db->quote($discount))
	            ->where('c.project_id = '.$id);
	        $db->setQuery($query);
	        $result = $db->execute();
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getDiscount($id)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query ->select("discount")
				->from('`#__gm_ceiling_calculations`')
	            ->where('project_id = ' . $id);
	        $db->setQuery($query);
			return $db->loadObjectList();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function update_spend($id, $extra_spend)
	{
		try
		{
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				$table->extra_spend = $extra_spend;
			}
			$return = $table->store();

			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function update_penalty($id, $penalty)
	{
		try
		{
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				$table->penalty = $penalty;
			}
			$return = $table->store();

			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function update_bonus($id, $bonus)
	{
		try
		{
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				$table->bonus = $bonus;
			}
			$return = $table->store();

			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function update_client($id,$client_id)
	{
		try
		{
			/*$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				$table->client_id = $client_id;
			}
			$return = $table->store();

			return $return;*/
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update("`#__gm_ceiling_projects`")
				->set("`client_id` = $client_id")
	            ->where("`id` = $id");
	        $db->setQuery($query);
	        $db->execute();
			return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function update_address($id, $address)
	{
		try
		{
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				$table->project_info = $address;
			}
			$return = $table->store();

			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function update_date_time($id, $date_time)
	{
		try
		{
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				$table->project_calculation_date = $date_time;
			}
			$return = $table->store();

			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function update_date_gauger($id, $gauger)
	{
		try
		{
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				$table->project_calculator = $gauger;
			}
			$return = $table->store();

			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function GetNameGauger($id)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query ->select("name")
				->from('`#__users`')
				->where("id = '$id'");
			$db->setQuery($query);
			$item =  $db->loadObject();

			return $item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function update_project_after_call($id,$client_id,$date,$address,$gmmanager_comment,$manager_comment,$status,$api_id=null,$manager_id, $gauger,$d_can_m=null,$d_com_m=null,$d_mou_m=null,$gm_can_m=null,$gm_com_m=null,$gm_mou_m=null){
		try
		{
			$who_calculate = 0;
			$user = JFactory::getUser();
			$user_group = $user->groups;
			if (in_array("15", $user_group)||in_array("16", $user_group)||in_array("17", $user_group)
			||in_array("18", $user_group)||in_array("19", $user_group)||in_array("20", $user_group)||in_array("23", $user_group||
			$user->dealer_id = 1 )) {
				$who_calculate = 1;
			}
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				$table->client_id = $client_id;
				$table->project_info = $address;
				$table->project_calculation_date = $date;
				$table->gm_manager_note = $gmmanager_comment;
				$table->dealer_manager_note = $manager_comment;
				$table->project_status = $status;
				if(!empty($api_id)) $table->api_phone_id = $api_id;
				$table->read_by_manager = $manager_id;
				
                $table->who_calculate = $who_calculate;
                if(!empty($gauger)){
                    $table->project_calculator = $gauger;
                }
                if (!is_null($d_can_m) && !is_null($d_com_m) && !is_null($d_mou_m) &&
                	!is_null($gm_can_m) && !is_null($gm_com_m) && !is_null($gm_mou_m))
                {
                	$table->dealer_canvases_margin = $d_can_m;
                	$table->dealer_components_margin = $d_com_m;
                	$table->dealer_mounting_margin = $d_mou_m;
                	$table->gm_canvases_margin = $gm_can_m;
                	$table->gm_components_margin = $gm_com_m;
                	$table->gm_mounting_margin = $gm_mou_m;
                }
			}
			$return = $table->store();
			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function update_sb_order_id($id, $orderId)
	{
		try
		{
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				$table->sb_order_id = $orderId;
			}
			$return = $table->store();

			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function approve($data)
	{
		try
		{
			$user = JFactory::getUser();
			$table = $this->getTable();
			if($data->id > 0) {
				$table->load($data->id);
				$table->gm_chief_note = $data->gm_chief_note;
				$table->dealer_chief_note = $data->dealer_chief_note;
				if ($data->project_mounting_date != "00-00-0000 00:00:00") {
					$table->project_mounting_date = $data->project_mounting_date;
					if ($user->dealer_type == 1 && $data->project_status == 4) {
						$table->project_status = 5;
					}
					$table->project_mounter = $data->project_mounter;
				}
				if ($data->project_calculation_date != "00-00-0000 00:00:00") {
					$table->project_calculation_date = $data->project_calculation_date;
					$table->project_calculator = $data->project_calculator;
				}
			}
			$return = $table->store();

			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
    public function approvemanager($id,$ready_date = null,$quickly=null)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query->update('`#__gm_ceiling_projects` AS projects');
	        $data = $this->getData($id);
            $user = JFactory::getUser($data->dealer_id);
            $is_components = $data->id_client == $user->associated_client;
	        if ($is_components)
                $query->set('projects.project_status = 19');
            else
                $query->set('projects.project_status = 10');
           
            if(!empty($ready_date)&&!empty($quickly)){
                $query->set("projects.ready_time = '$ready_date'");
                $query->set("projects.quickly = $quickly");
            }

            $query->where('projects.id = ' . $id);
            
	        $db->setQuery($query);
	        $return = $db->execute();

			$query = $db->getQuery(true);
			$query ->select("id, client_id, project_info, project_mounting_date, project_note, 	gm_calculator_note")
				->from('`#__gm_ceiling_projects`')
	            ->where('id = ' . $id);
            $db->setQuery($query);
            $item =  $db->loadObject();
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->save($id,10);
			return $item;

	      //  print_r($return); exit;
		//        $table = $this->getTable();
		//        if($data->id > 0) {
		//            $table->load($data->id);
		//            $table->project_status = 10;
		//        }
		//        $return = $table->store();
		}
		catch(Exception $e)
		{
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
    }

	public function return($id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query->update('`#__gm_ceiling_projects` AS projects')
	            ->set('projects.project_status = 1')
                ->set('projects.project_verdict = 0')
	            ->where('projects.id = ' . $id);
	        $db->setQuery($query);
	        $return = $db->execute();

			$query = $db->getQuery(true);
			$query ->select("id, client_id, project_info, project_mounting_date, project_note, 	gm_calculator_note")
				->from('`#__gm_ceiling_projects`')
	            ->where('id = ' . $id);
            $db->setQuery($query);
            $item =  $db->loadObject();
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->save($id,1);
			return $item;

			}
			catch(Exception $e)
			{
				Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
			}
    }
	
	/*
		// добавление истории клиента при изменении даты и/или МБ/замерщика
		// флаг = 1 изменилась только дата монтажа
		// флаг = 2 изменилась МБ
		// флаг = 3 изменилась только дата замера
		// флаг = 4 изменился замерщик
	*/
	public function AddComment($flag, $data) {
		try
    	{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$currentDate = date("Y-m-d H:i:s");
			
			if ($flag == 1) {
				// коммент о переносе даты
				$date = substr($data->project_mounting_date, 8, 2)."-".substr($data->project_mounting_date, 5, 2)."-".substr($data->project_mounting_date, 0, 4)." ".substr($data->project_mounting_date, 11, 5);
				$text = "У проекта №$data->id дата монтажа перенесена на $date";
				$query->insert('#__gm_ceiling_client_history')
					->columns('client_id, date_time, text')
					->values('"'.$data->id_client.'", "'.$currentDate.'", "'.$text.'"');
				$db->setQuery($query);
				$db->execute();

				// получение дат
				$date = substr($data->old_date, 0, 10);
				$date1 = new DateTime($date);
				$date1->modify("-1 day");
				$olddate = $date1->format("Y-m-d");

				$date = substr($data->project_mounting_date, 0, 10);
				$date2 = new DateTime($date);
				$date2->modify("-1 day");
				$newdate = $date2->format("Y-m-d");				
					
				// перенос даты перезвона
				$query3 = $db->getQuery(true);
				$query3->update('#__gm_ceiling_callback')
					->set("date_time = '$newdate 09:00:00'")
					->where("client_id = '$data->id_client' and date_time like '$olddate%'");
				$db->setQuery($query3);
				$db->execute();
			} else if ($flag == 2) {
				$query2 = $db->getQuery(true);
				$query2->select('name')
					->from('#__users')
					->where("id = $data->project_mounter");
				$db->setQuery($query2);
				$brigade = $db->loadObjectList();			

				$text = "У проекта №$data->id монтажная бригада заменена на ".$brigade[0]->name;
				$query->insert('#__gm_ceiling_client_history')
					->columns('client_id, date_time, text')
					->values('"'.$data->id_client.'", "'.$currentDate.'", "'.$text.'"');
				$db->setQuery($query);
				$db->execute();
			} else if ($flag == 3) {
				// коммент о переносе даты
				$date = substr($data->project_calculation_date, 8, 2)."-".substr($data->project_calculation_date, 5, 2)."-".substr($data->project_calculation_date, 0, 4)." ".substr($data->project_calculation_date, 11, 5);
				$text = "У проекта №$data->id дата замера перенесена на $date";
				$query->insert('#__gm_ceiling_client_history')
					->columns('client_id, date_time, text')
					->values('"'.$data->id_client.'", "'.$currentDate.'", "'.$text.'"');
				$db->setQuery($query);
				$db->execute();
			} else if ($flag == 4) {
				$query2 = $db->getQuery(true);
				$query2->select('name')
					->from('#__users')
					->where("id = $data->project_calculator");
				$db->setQuery($query2);
				$gauger = $db->loadObject();			

				$text = "У проекта №$data->id был изменен замерщик на ".$gauger->name;
				$query->insert('#__gm_ceiling_client_history')
					->columns('client_id, date_time, text')
					->values('"'.$data->id_client.'", "'.$currentDate.'", "'.$text.'"');
				$db->setQuery($query);
				$db->execute();
			}

			// статус монтажа изменить на непросмотренный
			if ($flag == 1 || $flag == 2) {
				$query4 = $db->getQuery(true);
				$query4->update('#__gm_ceiling_projects')
					->set("read_by_mounter = '0'")
					->where("id = '$data->id'");
				$db->setQuery($query4);
				$db->execute();
			}
		}
		catch(Exception $e)
		{
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}

	public function AddCommentManager($flag, $data, $client) {
		try
    	{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$currentDate = date("Y-m-d H:i:s");
			
			if ($flag == 1) {
				// коммент о переносе даты
				$date = substr($data["datatime"], 8, 2)."-".substr($data["datatime"], 5, 2)."-".substr($data["datatime"], 0, 4)." ".substr($data->project_mounting_date, 11, 5);
				$text = "Дата монтажа перенесена на $date";
				$query->insert('#__gm_ceiling_client_history')
				->columns('client_id, date_time, text')
				->values('"'.$client.'", "'.$currentDate.'", "'.$text.'"');
				$db->setQuery($query);
				$db->execute();

				// получение дат
				$date = substr($data["olddatetime"], 0, 10);
				$date1 = new DateTime($date);
				$date1->modify("-1 day");
				$olddate = $date1->format("Y-m-d");

				$date = substr($data["datatime"], 0, 10);
				$date2 = new DateTime($date);
				$date2->modify("-1 day");
				$newdate = $date2->format("Y-m-d");
					
				// перенос даты перезвона
				$query3 = $db->getQuery(true);
				$query3->update('#__gm_ceiling_callback')
				->set("date_time = '$newdate 09:00:00'")
				->where("client_id = '$client' and date_time like '$olddate%'");
				$db->setQuery($query3);
				$db->execute();
			}
			if ($flag == 2) {
				$query2 = $db->getQuery(true);
				$query2->select('name')
					->from('#__users')
					->where("id = $data");
				$db->setQuery($query2);
				$brigade = $db->loadObjectList();			

				$text = "Монтажная бригада заменена на ".$brigade[0]->name;
				$query->insert('#__gm_ceiling_client_history')
				->columns('client_id, date_time, text')
				->values('"'.$client.'", "'.$currentDate.'", "'.$text.'"');
				$db->setQuery($query);
				$db->execute();
			}
			
		}
		catch(Exception $e)
		{
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}

	public function FindAllbrigades($id) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('users.id, users.name')
                ->from('#__users as users')
                ->innerJoin('#__user_usergroup_map as usergroup_map ON users.id = usergroup_map.user_id')
                ->where("users.dealer_id = $id AND usergroup_map.group_id = 11");
            $db->setQuery($query);

            $items = $db->loadObjectList();
            return $items;
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function FindAllMounters($where) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            
            $query->select(' map.id_brigade, mounters.name')
                ->from('#__gm_ceiling_mounters as mounters')
                ->innerJoin('#__gm_ceiling_mounters_map as map ON mounters.id = map.id_mounter');
            if (!empty($where)) $query->where("map.id_brigade in ($where)");
            $db->setQuery($query);
            
            $items = $db->loadObjectList();
            return $items;
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function FindBusyMounters($date1, $date2) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query2 = $db->getQuery(true);

            $query2->select("SUM(calculations.n5)")
                ->from("#__gm_ceiling_calculations AS calculations")
                ->where("calculations.project_id = projects.id");

            $query->select("projects.project_mounter, projects.project_mounting_date, projects.project_info, ($query2) as n5")
                ->from('#__gm_ceiling_projects as projects')
                ->where("projects.project_mounting_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:59'")
                ->order('projects.id');
            $db->setQuery($query);

            $items = $db->loadObjectList();
    		return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function UpdateDateMountBrigade($id, $datatime, $mounter) {
        try
        {
            $db = $this->getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			
            $query->update('#__gm_ceiling_projects')
				->set("project_mounting_date = '$datatime'")
				->set("project_mounter = '$mounter'")
				->set("read_by_mounter = '0'")
				->where("id = '$id'");
			$db->setQuery($query);
			$db->execute();

			$query2->select("project_mounter, project_mounting_date")
				->from('#__gm_ceiling_projects')
				->where("id = '$id'");
			$db->setQuery($query2);
			$items = $db->loadObjectList();
			
    		return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function WhatStatusProject($id) {
		try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('project_status')
                ->from('#__gm_ceiling_projects')
                ->where("id = $id");
            $db->setQuery($query);

            $items = $db->loadObjectList();
            return $items;
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function done($project_id, $new_value, $mouting_sum, $material_sum, $check, $mouting_sum_itog)
	{
		try
		{
			$table = $this->getTable();
			if($project_id > 0) {
				$table->load($project_id);
				if($check == 1) $table->project_status = 12;
				$table->new_project_sum = $new_value;
	            $table->new_mount_sum = $mouting_sum;
	            $table->new_material_sum = $material_sum;
				$table->closed = date("Y-m-d");
                $table->new_project_mounting = $mouting_sum_itog;
				$table->new_material_sum = $material_sum;
				$table->check_mount_done = $check;
				
				// сюда нужно сделать запись в БД новх полей, подобрать для них переменные и проверку на check,
                // чтобы оставить статус если чек =0
			}
			$return = $table->store();
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->save($project_id,12);
			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function deactivate($copy_data, $status)
	{
		try
		{
			$table = $this->getTable();
			$table->load(0);
			$table->project_verdict = $copy_data->project_verdict;
			$table->project_note = $copy_data->project_note;
			$table->gm_calculator_note = $copy_data->gm_calculator_note;
			$table->dealer_calculator_note = $copy_data->dealer_calculator_note;
			$table->gm_manager_note = $copy_data->gm_manager_note;
			$table->gm_chief_note = $copy_data->gm_chief_note;
			$table->dealer_manager_note = $copy_data->dealer_manager_note;
			$table->dealer_chief_note = $copy_data->dealer_chief_note;
			$table->project_mounting_date = $copy_data->project_mounting_date;
			$table->project_status = $status;
            $return = $table->store();
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->save($copy_data->id,$status);
			//JFactory::getApplication()->enqueueMessage($return, 'error');
			echo $return;
			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function discount($id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query ->select("projects.project_discount")
				->from('`#__gm_ceiling_projects` AS projects')
	            ->where('projects.id = ' . $id);
	        $db->setQuery($query);

			return $db->loadObject();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
	public function isOwner($id, $project_id)
	{
		try
		{
			$table = $this->getTable();
			$table->load($id);
			
			$user = JFactory::getUser($id);
			if($user->dealer_id == $table->dealer_id) {
				return 1;
			} else {
				return 0;
			}
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
			$table->state = $state;

			return $table->store();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function deleteEmptyOrBadProjectsByClientId($client_id)
	{
		try
		{
			$db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query ->delete("`#__gm_ceiling_projects`")
	            ->where("`client_id` = $client_id AND (`project_status` = 0 OR `project_status` = 2 OR `project_status` = 15)");
	        $db->setQuery($query);
	        $db->execute();
			return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function deleteProjectsByClientId($client_id)
	{
		try
		{
			$db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query ->delete("`#__gm_ceiling_projects`")
	            ->where("`client_id` = $client_id");
	        $db->setQuery($query);
	        $db->execute();
			return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function deleteAdvtProjectsByClientId($client_id)
	{
		try
		{
			$db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query ->delete("`#__gm_ceiling_projects`")
	            ->where("`client_id` = $client_id AND `project_status` != 21");
	        $db->setQuery($query);
	        $db->execute();
			return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getNewData($id)
	{
		try
		{
			$db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query ->select('*')
				->from("`#__gm_ceiling_projects`")
	            ->where("`id` = $id");
	        $db->setQuery($query);
	       return $db->loadObject();
	   }
	   catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function newStatus($id, $status)
    {
    	try
    	{
	        $db = $this->getDbo();
	        $query = $db->getQuery(true);
	        $query ->update('`#__gm_ceiling_projects`')
	            ->set("project_status = ".$status)
	            ->where("id = $id");
	        $db->setQuery($query);
	        $res = $db->execute();
	        if (empty($res)) throw new Exception("Error in model/project/newStatus! An error occurred while changing the entity in the database.", 5);
	        return true;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

	public function update_transport($project_id,$transport,$distance,$col){
		try
    	{
	        $db = $this->getDbo();
	        $query = $db->getQuery(true);
	        $query ->update('`#__gm_ceiling_projects`')
	            ->set('transport = '.$db->quote($transport))
				->set('distance = '.$db->quote($distance))
				->set('distance_col = '.$db->quote($col))
	            ->where('id =' .$project_id);
	        $db->setQuery($query);
	        $res = $db->execute();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function transport($data)
    {
    	try
    	{
	        $db = $this->getDbo();
	        $query = $db->getQuery(true);
	        $query ->update('`#__gm_ceiling_projects`')
	            ->set('transport = '.$db->quote($data->transport))
				->set('distance = '.$db->quote($data->distance))
				->set('distance_col = '.$db->quote($data->distance_col))
	            ->where('id =' .$data->id);
	        $db->setQuery($query);
	        $res = $db->execute();
			
			$query = $db->getQuery(true);
			$query
				->select('a.client_id, c.dealer_id')
				->from('#__gm_ceiling_projects AS a')
				->join("LEFT","`#__gm_ceiling_clients` as c on c.id = a.client_id")
				->where('a.id = ' . $data->id);
			$db->setQuery($query);
			$results = $db->loadObject();
			
			if(empty($results->dealer_id)) $results->dealer_id = 1;
			$query = $db->getQuery(true);
			$query
				->select('m.*')
				->from('#__gm_ceiling_mount AS m')
				->where('m.user_id = ' . $results->dealer_id);
			$db->setQuery($query);
			$mount = $db->loadObject();
			return $mount;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }


    public function getProjectForStock($id)
    {
    	try
    	{
	        if (empty($id)) return null;

	        $db = $this->getDbo();

	        $project = null;
	        $calculations = null;
	        $components = array();
	        $canvases = array();
	        $customer = (object)["page" => "Customer", "type" => "4"];
	        $margin = (object) [];

	        $query = $db->getQuery(true);
	        $query->select('p.project_discount as discount, p.project_status as status, p.dealer_canvases_margin as DCanM, p.dealer_components_margin as DComM')
	            ->select('c.id as client, c.client_name as client_name, cc.phone as client_phone, d.contact as client_email')
	            ->select('u.id as dealer, u.name as dealer_name, u.username as dealer_phone, u.email as dealer_email')
	            ->from("`#__gm_ceiling_projects` as p")
	            ->join("LEFT","`#__gm_ceiling_clients` as c on c.id = p.client_id")
	            ->join("LEFT","`#__gm_ceiling_clients_contacts` as cc on cc.client_id = c.id")
	            ->join("LEFT","`#__users` as u on u.id = c.dealer_id")
                ->join("LEFT","#__gm_ceiling_clients_dop_contacts as d ON d.client_id = c.id and d.type_id = '1' or d.type_id is NULL")
	            ->where("p.id = " . $db->quote($id))
	            ->group("p.id");
	        $db->setQuery($query);
	        $project = $db->loadObject();

	        $customer->client = (object) ["id" => $project->client, "name" => $project->client_name, "phone" => $project->client_phone, "email" => $project->client_email];
	        $customer->dealer = (object) ["id" => $project->dealer, "name" => $project->dealer_name, "phone" => $project->dealer_phone, "email" => $project->dealer_email];
	        $customer->Name = $project->client_name;
	        $customer->Phone = $project->client_phone;
	        $customer->Email = $project->client_email;
	        $customer->Status = $project->status;

	        $margin->component = intval($project->DComM);
	        if ($margin->component == 100) $margin->component = 99;
            $margin->canvas = intval($project->DCanM);
            if ($margin->canvas == 100) $margin->canvas = 99;
            $customer->Margin = $margin;

            $discount = $project->discount;
            if (empty($discount)) $discount = 0;
            $customer->discount = $discount;
            $customer->status = $project->status;

	        $query = $db->getQuery(true);
	        $query->select('c.id as id, c.n3 as cid, c.n5 as quad, c.offcut_square as square')
	            ->from("`#__gm_ceiling_calculations` as c")
	            ->where("project_id = " . $db->quote($id));
	        $db->setQuery($query);
	        $calculations = $db->loadObjectList();

	        foreach ($calculations as $calculation) {
	            $canvases[] = (object) array("id" => $calculation->cid, "quad" => $calculation->quad, "discount" => $discount);
	            if (floatval($calculation->square) > 0) $canvases[] = (object) array("id" => $calculation->cid, "quad" => $calculation->square, "discount" => 50);

	            $from_db = 1; $save = 0; $ajax = 0; $pdf = 0; $print_components = 1;
	            $componentsTemp = Gm_ceilingHelpersGm_ceiling::calculate($from_db, $calculation->id, $save, $ajax, $pdf, $print_components);
	            $componentsTemp = (json_decode($componentsTemp))->comp_arr;
	            foreach ($componentsTemp as $v)
	            {
	                if ($v->quantity != "0" && $v->id > 0)
	                {
	                    $component = (object) array();
	                    $component->id = $v->id;
	                    $component->title = $v->title;
	                    $component->count = floatval($v->quantity);

	                    if (empty($components[$v->id])) $components[$v->id] = $component;
	                    else $components[$v->id]->count += $component->count;
	                }
	            }
	        }

	        $componentsTemp = array();
	        foreach ($components as $component) {

	            $query = $db->getQuery(true);
	            $query->select('c.title AS Type, o.title AS Name, c.unit AS Unit, o.count_sale AS CountUnit, o.price AS Price')
	                ->from("`#__gm_ceiling_components_option` as o")
	                ->join("LEFT", "`#__gm_ceiling_components` as c ON c.id = o.component_id")
	                ->where("o.id = " . $db->quote($component->id));
	            $db->setQuery($query);
	            $CT = $db->loadObject();

	            $CT->page = "Component";
	            $CT->Price = floatval($CT->Price) * ((100 - intval($discount)) / 100);
	            $CT->PriceM = (($CT->Price * 100)/(100 - floatval($margin->component)));
	            $CT->Count = floatval($component->count);
	            $CT->CountUnit = floatval($CT->CountUnit);
                if ((($CT->Count*100) % ($CT->CountUnit*100)) != 0)
                    $CT->Count = (floor($CT->Count / $CT->CountUnit) + 1) * $CT->CountUnit;
                $CT->Itog = $CT->PriceM * $CT->Count;
                $CT->page = "Component";

                $componentsTemp[] = $CT;
	        }
	        $components = $componentsTemp;

	        $canvasesTemp = array();
	        foreach ($canvases as $canvas) {
	            $query = $db->getQuery(true);
	            $query->select('c.name as Name, c.country as Country, c.width as Width, c.price as Price')
	                ->select('t.texture_title as Texture, cc.title as Color')
	                ->from("`#__canvases` as c")
	                ->join("LEFT", "`#__gm_ceiling_textures` as t on c.texture_id = t.id")
	                ->join("LEFT", "`#__gm_ceiling_colors` as cc on c.color_id = c.id")
	                ->where("c.id = " . $db->quote($canvas->id));
	            $db->setQuery($query);
	            $CT = $db->loadObject();

	            if ($CT->Color == "") $CT->Color = "Нет";
	            $CT->Quad = floatval($canvas->quad);
	            $CT->Price = floatval($CT->Price) * ((100 - intval($canvas->discount)) / 100);
	            $CT->PriceM = ((floatval($CT->Price) * 100)/(100 - floatval($margin->canvas)));
	            $CT->Itog = $CT->PriceM * $CT->Quad;
                $CT->page = "Canvas";

                $canvasesTemp[] = $CT;
	        }
	        $canvases = $canvasesTemp;

	        $data = (object) array("goods" => array_merge($canvases, $components), "customer" => $customer);

	        return $data;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getProjectForGuild($id)
    {
	    try {
            if (empty($id)) return null;

            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query->select('p.project_status as status')
                ->from("`#__gm_ceiling_projects` as p")
                ->where("id = " . $db->quote($id));
            $db->setQuery($query);
            $project = $db->loadObject();

            $query = $db->getQuery(true);
            $query->select('c.id as id, c.calculation_title as title, c.n4 as perimeter, c.n5 as quad, c.n9 as angles, c.calc_data, c.cut_data, c.offcut_square as square')
                ->select("s.name as name, s.country as country, s.width as width, t.texture_title as texture, r.title as color")
                ->from("`#__gm_ceiling_calculations` as c")
                ->join("Left", "`#__gm_ceiling_canvases` as s ON s.id = c.n3")
                ->join("Left", "`#__gm_ceiling_textures` as t ON t.id = s.texture_id")
                ->join("Left", "`#__gm_ceiling_colors` as r ON r.id = s.color_id")
                ->where("project_id = " . $db->quote($id));
            $db->setQuery($query);
            $calculations = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*")
                ->from("`#__gm_ceiling_guild_works`");
            $db->setQuery($query);
            $works = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*")
                ->from("`#__gm_ceiling_guild_ceilings`")
                ->where("project_id = '$id'");
            $db->setQuery($query);
            $ceilings = $db->loadObject();

            if (!empty($ceilings))
            {
                $ceilings->completed = json_decode($ceilings->completed);
                $ceilings->true = [];
                foreach ($ceilings->completed as $key => $item)
                    if ($item) $ceilings->true[] = $key;
            } else $ceilings = (object) ["true" => array()];


            $calculationsTemp = [];
            foreach ($calculations as $calc) {
                if (in_array($calc->id, $ceilings->true))
                {
                    $calc->title .= " ( Готово )";
                    $calc->status = true;
                } else $calc->status = false;


                $calc->canvas_name = $calc->name . " " .$calc->country . " " .$calc->width . " " .$calc->texture . ((empty($calc->color))?"":" ".$calc->color);
                $calc->quad = floatval($calc->quad);
                $calc->perimeter = floatval($calc->perimeter);
                $calc->square = floatval($calc->square);
                $calc->percent = round((($calc->quad + $calc->square)*$calc->square)/100.0, 2);
                $splitCalcData = preg_split("/;/", $calc->calc_data);
                $split = preg_split("/Полотно\d{1,5}:/", $calc->cut_data);
                unset($split[0]);
                preg_match_all("/Полотно\d{1,5}:/", $calc->cut_data, $preg);
                $cut_data = [];
                foreach ($split as $key => $value)
                    $cut_data[] = (object) ["title" => $preg[0][$key - 1], "data" => $value];
                $calc->cut_data = $cut_data;
                $calc->calc_data = implode("; ", $splitCalcData);

                $calc->works = [];
                $calc->sumWork = 0.0;
                foreach ($works as $work) {
                    $workTemp = (object) [];
                    $workTemp->name = $work->name;
                    $workTemp->unit = $work->unit;
                    $flag = false;
                    switch (intval($work->id))
                    {
                        case 1:
                            $workTemp->count = floatval($calc->quad);
                            $workTemp->sum = (floatval($calc->quad) - floatval($work->free)) * $work->price;
                            break;
                        case 2:
                            $workTemp->count = floatval($calc->angles);
                            $workTemp->sum = (floatval($calc->angles) - floatval($work->free)) * $work->price;
                            break;
                        case 5:
                            $workTemp->count = floatval($calc->perimeter);
                            $workTemp->sum = (floatval($calc->perimeter) - floatval($work->free)) * $work->price;
                            break;
                        default:
                            $flag = true;
                            break;
                    }

                    if (!$flag)
                    {
                        $calc->works[] = $workTemp;
                        $calc->sumWork += $workTemp->sum;
                    }
                }

                $calculationsTemp[$calc->id] = $calc;
            }

            return (object) ["calculations" => $calculationsTemp, "status" => $project->status];
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function refusing($id)
	{
		try
		{
			$db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query ->select('client_id ')
				->from("`#__gm_ceiling_projects`")
	            ->where("`id` = ".$id);
	        $db->setQuery($query);
	        $client_id = $db->loadObject()->client_id;

	        $query = $db->getQuery(true);
	        $query->update('#__gm_ceiling_projects');
				$query->set('project_status = 22');
				$query->where('id = '.$id);
				$db->setQuery($query);
				$db->execute();
	        
	      return $client_id;
	  }
	  catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function new_getProjectItems($project_id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select('*')
	            ->from('#__gm_ceiling_projects')
	            ->where('id = '.$project_id);
	        $db->setQuery($query);
	        $results = $db->loadObject();
	        return $results;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getMarginProject($project_id, $calculate_id)
    {
    	try
    	{
	        if (empty($project_id) && empty($calculate_id))
	            return null;

	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query->select('`gm_canvases_margin`, `gm_components_margin`, `gm_mounting_margin`, `dealer_canvases_margin`, `dealer_components_margin`, `dealer_mounting_margin`')
	            ->from('`#__gm_ceiling_projects`');

	        if (empty($project_id))
	            $query->join('`#__gm_ceiling_calculation` as calc ON calc.project_id = id')
	                ->where("calc.id = '$calculate_id''");
	        else
	            $query->where("id = '$project_id'");

	        $db->setQuery($query);
	        return $db->loadObject();
	    }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function save($data)
    {
    	try
    	{
	        $table = $this->getTable();
	        $data['change_time'] = date("Y-m-d H:i:s");
			//по хорошему нужно смотреть больше про JTable методы bind и на моделях возможно переписывать многое
			return $table->save($data);
	    }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
