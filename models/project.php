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
				if (isset($this->_item->client_id) && $this->_item->client_id != '') {
					$this->_item->_client_id = $this->_item->client_id;
					if (is_object($this->_item->client_id)){
						$this->_item->client_id = \Joomla\Utilities\ArrayHelper::fromObject($this->_item->client_id);
					}

					$clientId = $this->_item->client_id;

					$clientModel = Gm_ceilingHelpersGm_ceiling::getModel('client');
					$client = $clientModel->getClientById($clientId);

                    if (!empty($client)) {
                        $this->_item->id_client_num = $client->id;
                        $this->_item->client_id = $client->client_name;
                        $this->_item->dealer_id = $client->dealer_id;
                        $this->_item->id_client = $client->id;
                    }
				}

				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query
					->select("m.type as stage,m.date_time as time,m.mounter_id as mounter")
					->from('`#__gm_ceiling_projects_mounts` as m')
					->where("m.project_id =". $this->_item->id);
				$db->setQuery($query);
				
				$mount_array = $db->loadObjectList();
				$this->_item->mount_data = htmlspecialchars(json_encode((!empty($mount_array)) ? $mount_array : array()),ENT_QUOTES);

                $query = $db->getQuery(true);
                $query
                    ->select("SUM(prepayment_sum) as total")
                    ->from('`rgzbn_gm_ceiling_projects_prepayment`')
                    ->where("project_id =". $this->_item->id);
                $db->setQuery($query);
                $prepayment_total = $db->loadObject();
                if(!empty($prepayment_total)){
                    $this->_item->prepayment_total = $prepayment_total->total;
                }

				//throw new Exception(print_r($this->_item,true));
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
	            ->select(' pm.mounter_id AS project_mounter')
	            ->from('`#__gm_ceiling_projects_mounts` AS pm')
	            ->select('users.name AS name, users.id AS id')
	            ->join('LEFT', '`#__users` AS users ON users.id = pm.mounter_id')
	            ->where('pm.project_id =\''. $project_id.'\'');

	        $db->setQuery($query);
	        $result = $db->loadObjectList();
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
			/*if($project_id > 0 && ($check == 0 || $check == 1)){
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

			return $return;*/
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
				->set('project_sum = ' . $db->quote($data->project_sum))
				//->set('project_mounting_date = ' . $db->quote($data->project_mounting_date))
				->set('project_status = ' . $db->quote($status));
			/*if (empty($data->project_mounter)) $query->set('project_mounter = NULL');
			else $query->set('project_mounter = ' . $db->quote($data->project_mounter));*/
			/*if ($status == 3) {
				$query->set("project_mounting_date = '0000-00-00 00:00:00'");
				$query->set('project_mounter = NULL');
			}*/
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

	public function change_status($id, $project_status)
	{
		try
		{
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
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

	         $query = $db->getQuery(true);
				 $query->update('`#__gm_ceiling_projects` AS p')
	            ->set('p.project_discount = ' . $db->quote($discount))
	            ->where('p.id = '.$id);
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
				if(!empty($address)){
					$table->project_info = $address;
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
			$query
                ->select("name")
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

	public function update_project_after_call($id,$client_id,$date,$address,$status,$api_id=null,$manager_id, $gauger,$d_can_m=null,$d_com_m=null,$d_mou_m=null,$gm_can_m=null,$gm_com_m=null,$gm_mou_m=null){
		try
		{
			$user = JFactory::getUser();
			$user_group = $user->groups;
			/*if (in_array("15", $user_group)||in_array("16", $user_group)||in_array("17", $user_group)
			||in_array("18", $user_group)||in_array("19", $user_group)||in_array("20", $user_group)||in_array("23", $user_group||
			$user->dealer_id = 1 )) {

			}*/
			$table = $this->getTable();
			if($id > 0) {
				$table->load($id);
				$table->client_id = $client_id;
				$table->project_info = $address;
				$table->project_calculation_date = $date;
				$table->project_status = $status;
				if(!empty($api_id)) $table->api_phone_id = $api_id;
				$table->read_by_manager = $manager_id;
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
				$table->project_status = $data->project_status;
				/*if ($data->project_mounting_date != "00-00-0000 00:00:00") {
					$table->project_mounting_date = $data->project_mounting_date;
					if ($user->dealer_type == 1 && $data->project_status == 4) {
						$table->project_status = 5;
					}
					$table->project_mounter = $data->project_mounter;
				}*/
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
    public function approvemanager($id)
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

            $query->where('projects.id = ' . $id);
            
	        $db->setQuery($query);
	        $db->execute();

			$query = $db->getQuery(true);
			$query ->select("p.id, p.client_id, p.project_info, GROUP_CONCAT(distinct mp.date_time SEPARATOR ';') as project_mounting_date")
				->from('`#__gm_ceiling_projects` as p')
				->innerJoin("`#__gm_ceiling_projects_mounts` as mp on mp.project_id = p.id")
	            ->where('p.id = ' . $id)
	            ->order("p.id");
            $db->setQuery($query);
            $item =  $db->loadObject();
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->save($id,10);
			return $item;

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
    	try {
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query->update('`#__gm_ceiling_projects` AS projects')
	            ->set('projects.project_status = 1')
	            ->where('projects.id = ' . $id);
	        $db->setQuery($query);
	        $return = $db->execute();

			$query = $db->getQuery(true);
			$query ->select("id, client_id, project_info")
				->from('`#__gm_ceiling_projects`')
	            ->where('id = ' . $id);
            $db->setQuery($query);
            $item =  $db->loadObject();
			return $item;
		}
		catch(Exception $e) {
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
				$query2
                    ->select('name')
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

            $query
                ->select('users.id, users.name')
                ->from('#__users as users')
                ->leftJoin('`rgzbn_users_dealer_id_map` as dm on dm.user_id = users.id')
                ->innerJoin('#__user_usergroup_map as usergroup_map ON users.id = usergroup_map.user_id')
                ->where("(users.dealer_id = $id AND usergroup_map.group_id = 11) OR (dm.dealer_id = $id and dm.group_id = 11)");
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

            $query->select("mp.mounter_id  as project_mounter, mp.date_time as project_mounting_date, projects.project_info, ($query2) as n5")
                ->from('#__gm_ceiling_projects as projects')
                ->innerJoin("`#__gm_ceiling_projects_mounts` as mp on p.id = mp.project_id")
                ->where("mp.date_time BETWEEN '$date1 00:00:00' AND '$date2 23:59:59'")
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
/*	public function delete($id)
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
	}*/

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

            /*$discount = $project->discount;
            if (empty($discount)) */$discount = 0;
            $customer->discount = $discount;
            $customer->status = $project->status;

            $stock_model = Gm_ceilingHelpersGm_ceiling::getModel("stock");
            $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $all_goods = [];
            //получаем списанные товары
            $realisedComponents =  $stock_model->getRealisedComponents($id);
            //$all_goods = $stock_model->getRealisedComponents($id);
            //считаем все компоненты

            $model_calcform = Gm_ceilingHelpersGm_ceiling::getModel('calculationForm');
            $calculations = $calculationsModel->new_getProjectItems($id);
            $calc_goods = [];
            foreach ($calculations as $calculation) {
                $calc_goods[$calculation->id] = $model_calcform->getGoodsPricesInCalculation($calculation->id,  $customer->dealer->id); // Получение компонентов
            }
            //throw new Exception(print_r($calc_goods,true));
            foreach ($calc_goods as $goods_array){
                foreach($goods_array as $goods){
                    if(array_key_exists($goods->goods_id,$all_goods)){
                        $all_goods[$goods->goods_id]->final_count += $goods->final_count;
                        $all_goods[$goods->goods_id]->price_sum += $goods->price_sum;
                        $all_goods[$goods->goods_id]->price_sum_with_margin += $goods->price_sum_with_margin;

                    }
                    else{
                        $all_goods[$goods->goods_id] = $goods;
                    }
                }
            }
			foreach ($realisedComponents as $key=>$value){
			    if(!empty($all_goods[$key])){
			        if($value->final_count == $all_goods[$key]->final_count){
			            $all_goods[$key] = $value;
                    }
                }
            }

			//throw new Exception(print_r($all_goods,true));
	        $data = (object) array("goods" => $all_goods, "customer" => $customer);

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
    	    //throw new Exception(print_r($data,true));
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

    function getMaterialsForEstimate($project_id){
    	try
    	{
    		$projectsMaterialsModel = Gm_ceilingHelpersGm_ceiling::getModel('Projects_materials');
    		$data = $projectsMaterialsModel->getData($project_id);
    		$data = json_decode($data[0]->data);
    		$materials['canvases'] = $data->canvases;
            $materials['components'] = $data->components;

    		if(empty($materials['canvases'])&&empty($materials['Components'])){
	            $components_data = array();
	            $calculations_model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
	            $calculations = $calculations_model->new_getProjectItems($project_id);
	            foreach($calculations as $calc){
	                $components_data [] = Gm_ceilingHelpersGm_ceiling::calculate_components($calc->id,null,0);
	                $canvases_data [] = Gm_ceilingHelpersGm_ceiling::calculate_canvases($calc->id);
	            }
	            $components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
	            $components_list = $components_model->getFilteredItems();
	            foreach ($components_list as $i => $component) {
	                $components[$component->id] = $component;
	            }
                $materials = [];
	            $materials['canvases'] = [];
	            $materials['components'] = [];
	            foreach ($canvases_data as $key=>$value){
                    if (array_key_exists($value['id'], $materials['canvases'])) {
                        $materials['canvases'][$component['id']]['total_price'] += $value['self_total'];
                        $materials['components'][$component['id']]['quantity'] +=$value['quantity'];
                    }
                    else {
                        $canvas = [];
                        $canvas['id'] = $value['id'];
                        $canvas['title'] = $value['manufacturer'] . ' ' . $value['sub_title'] . ' ширина ' . ($value['width'] / 100);
                        $canvas['unit'] = 'м<sup>2</sup>';
                        $canvas['quantity'] = $value['quantity'];
                        $canvas['price'] = $value['self_price'];
                        $canvas['total_price'] = $value['self_total'];
                        $materials['canvases'][$value['id']] = $canvas;
                        //array_push($materials['canvases'], $canvas);
                    }
                }
	            foreach ($components_data as $component_array) {
	                foreach ($component_array as $key1 => $component) {
	                    if ($component['stack'] == 0) {
                            if (array_key_exists($component['id'], $materials['components'])) {
                                $materials['components'][$component['id']]['total_price'] += $component['self_total'];
                                $materials['components'][$component['id']]['quantity'] = Gm_ceilingHelpersGm_ceiling::rounding($materials['components'][$component['id']]['quantity'] + $component['quantity'], $components[$component['id']]->count_sale);
                            } else {
                                $comp = [];
                                $comp['id'] = $component['id'];
                                $comp['title'] = $component['title'];
                                $comp['unit'] = $component['unit'];
                                $comp['quantity'] = $component['quantity'];
                                $comp['price'] = $component['self_price'];
                                $comp['total_price'] = $component['self_total'];
                                $materials['components'][$component['id']] = $comp;
                            }
                            $materials['components'][$component['id']]['count_sale'] = $components[$component['id']]->count_sale;
                        }
	                }
	            }
	           
	            foreach ($components_data as $component_array) {
	                foreach ($component_array as $key => $component) {
	                    if ($component['stack'] == 1) {
                            $comp = [];
                            $comp['id'] = $component['id'];
                            $comp['title'] = $component['title'];
                            $comp['unit'] = $component['unit'];
                            $comp['quantity'] = $component['quantity'];
                            $comp['price'] = $component['self_price'];
                            $comp['total_price'] = $component['self_total'];
	                        $materials['components'][] = $comp;
	                    }
	                }
	            }
    		}
    		return $materials;

	    } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveNote($project_id, $note,$type = 1) {
    	try {
    	    if(empty($type)){
    	        $type = 1;
            }
    		$user_id = JFactory::getUser()->id;
    		if (empty($project_id) && empty($user_id) && empty($note)) {
    			return false;
    		}
    		$db = JFactory::getDbo();

    		$query = $db->getQuery(true);
	        $query
	            ->select('`id`,`note`')
                ->from('`#__gm_ceiling_projects_notes`')
                ->where("`project_id` = $project_id AND `user_id` = $user_id");
            if(!empty($type)){
                $query->where("`type` = $type");
            }
	        $db->setQuery($query);
	        $dbNote = $db->loadObject();
            $query = $db->getQuery(true);

            if(!empty($dbNote)){
                $query
                    ->update('`#__gm_ceiling_projects_notes`')
                    ->set("`note` = '$note'")
                    ->where("`id` = $dbNote->id");
            }
            else{
                $query
                    ->insert('`#__gm_ceiling_projects_notes`')
                    ->columns('`project_id`, `user_id`, `note`,`type`')
                    ->values("$project_id, $user_id, '$note',$type");

            }
            $db->setQuery($query);
            $db->execute();
	        return true;
    	} catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getProjectNotes($project_id,$type) {
    	try {
    		if (empty($project_id)) {
    			return false;
    		}
    		$db = JFactory::getDbo();

	        $query = $db->getQuery(true);
	        $query
	        	->select('`project_id`, `user_id`, `note`,`type`')
	            ->from('`#__gm_ceiling_projects_notes`')
	            ->where("`project_id` = $project_id");
	        if(!empty($type)){
	            $query->where("`type`= $type");
            }
	        $db->setQuery($query);
	        $result = $db->loadObjectList();
	        return $result;
    	} catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveFinalSum($id,$project_sum){
	    try{
	        if(!empty($id)) {
	            $project_sum = floatval($project_sum);
	            if(!empty($project_sum)) {
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);
                    $query
                        ->update('#__gm_ceiling_projects')
                        ->set("new_project_sum = $project_sum")
                        ->where("id = $id");
                    $db->setQuery($query);
                    $db->execute();
                }
                return true;
            }
	        else{
	            return false;
	        }

        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getDataForNotify($id){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $calcSubquery = $db->getQuery(true);
            $calcSubquery
                ->select('project_id,sum(n5) as perimeter,Group_CONCAT(id) as calcs_id')
                ->from('`rgzbn_gm_ceiling_calculations`')
                ->where("project_id = $id");
            $query
                ->select('p.id,p.project_status,p.project_info,p.project_calculator,p.project_sum,p.new_project_sum,c.client_name,c.dealer_id')
                ->select('DATE_FORMAT(p.project_calculation_date,\'%d.%m.%Y %H:%i\') AS measure_date')
                ->select('GROUP_CONCAT(cc.phone SEPARATOR \';\') AS client_contacts')
                ->select('CONCAT(\'[\',GROUP_CONCAT( CONCAT(\'{"mounter":"\',pm.mounter_id,\'","type_id":"\',pm.type,\'","type_name":"\',t.title,\'","date_time":"\',pm.date_time,\'"}\') SEPARATOR \',\'),\']\') AS mount')
                ->select('calc.calcs_id,calc.perimeter')
                ->from('`rgzbn_gm_ceiling_projects` AS p')
                ->innerJoin('`rgzbn_gm_ceiling_clients` AS c ON p.client_id = c.id')
                ->leftJoin("($calcSubquery) as calc on calc.project_id = p.id")
                ->leftJoin('`rgzbn_gm_ceiling_projects_mounts` AS pm ON p.id = pm.project_id')
                ->leftJoin('`rgzbn_gm_ceiling_clients_contacts` AS cc ON c.id = cc.client_id')
                ->leftJoin('`rgzbn_gm_ceiling_mounts_types` AS t ON t.id = pm.type')
                ->where("p.id = $id");
            $db->setQuery($query);
            $item = $db->loadObject();
            $item->notes = Gm_ceilingHelpersGm_ceiling::getProjectNotes($id);
            return $item;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getBaseData($id){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('p.id,p.project_info,p.client_id,c.dealer_id')
                ->from('`rgzbn_gm_ceiling_projects` as p')
                ->innerJoin('`rgzbn_gm_ceiling_clients` as c on c.id = p.client_id')
                ->where("p.id = $id");
            $db->setQuery($query);
            $project = $db->loadObject();
            return $project;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function clearProject($id){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`rgzbn_gm_ceiling_calculations`')
                ->where("project_id = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function deleteGoods($projectId,$goodsIds){
	    try{
            $db = JFactory::getDbo();

            $query = "
                DELETE `rgzbn_gm_ceiling_calcs_goods_map`
                FROM `rgzbn_gm_ceiling_calcs_goods_map`
                INNER JOIN `rgzbn_gm_ceiling_calculations` ON `rgzbn_gm_ceiling_calculations`.`id` = `rgzbn_gm_ceiling_calcs_goods_map`.`calc_id`
                WHERE `rgzbn_gm_ceiling_calculations`.`project_id` = $projectId AND  `rgzbn_gm_ceiling_calcs_goods_map`.`goods_id` IN ($goodsIds)";
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function deleteJobs($projectId,$jobsIds){
        try{
            $db = JFactory::getDbo();
            $query = "
                DELETE `rgzbn_gm_ceiling_calcs_jobs_map`
                FROM `rgzbn_gm_ceiling_calcs_jobs_map`
                INNER JOIN `rgzbn_gm_ceiling_calculations` ON `rgzbn_gm_ceiling_calculations`.`id` = `rgzbn_gm_ceiling_calcs_jobs_map`.`calc_id`
                WHERE `rgzbn_gm_ceiling_calculations`.`project_id` = $projectId AND  `rgzbn_gm_ceiling_calcs_jobs_map`.`job_id` IN ($jobsIds)";
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function removeAdvt($id){
    	try{
    		$db = JFactory::getDbo();
    		$query = $db->getQuery(true);
    		$query
    			->update('rgzbn_gm_ceiling_projects')
    			->set('api_phone_id = NULL')
    			->where("id=$id");
    		$db->setQuery($query);
    		$db->execute();
    		return $db->getAffectedRows();
    	}
    	catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /*4API*/
    public function create($client_id){
        try{
            $clientModel = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $client = $clientModel->get($client_id);
            $info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
            $margin = $info_model->getDataById($client->dealer_id);
            $projectData = [
                "client_id"=> $client_id,
                "project_status" => 0,
                "created" => date("Y.m.d"),
                "project_discount" => 0,
                "dealer_canvases_margin" => $margin->dealer_canvases_margin,
                "dealer_components_margin" => $margin->dealer_components_margin,
                "dealer_mounting_margin" => $margin->dealer_mounting_margin
            ];
            $projectForm = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
            $projectId = $projectForm->save($projectData);
            return $projectId;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function get($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`rgzbn_gm_ceiling_projects`')
                ->where("id = $id and deleted_by_user <> 1");
            $db->setQuery($query);
            $item = $db->loadObject();
            return $item;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCalculations($id){
        try{
            $project = $this->getBaseData($id);
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id,calculation_title,project_id,n4,n5,n9,canvases_sum,components_sum,mounting_sum,
                canvases_sum_with_margin,components_sum_with_margin,mounting_sum_with_margin')
                ->from('`rgzbn_gm_ceiling_calculations`')
                ->where("project_id = $id");
            $db->setQuery($query);
            $calculations = $db->loadObjectList();
            $calculationformModel = Gm_ceilingHelpersGm_ceiling::getModel('CalculationForm');
            foreach ($calculations as $calculation) {
                $allGoods = $calculationformModel->getGoodsPricesInCalculation($calculation->id,$project->dealer_id);
                if(!empty($calculation->cancel_metiz)){
                    $calculation->goods = Gm_ceilingHelpersGm_ceiling::deleteMetizFromGoods($allGoods);
                }
                else{
                    $calculation->goods = $allGoods;
                }
                $calculation->jobs = $calculationformModel->getJobsPricesInCalculation($calculation->id,$project->dealer_id);
                $calculation->factory_jobs = $calculationformModel->getFactoryWorksPricesInCalculation($calculation->id);
            }
            return $calculations;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getMountingSum($projectId,$dealerId){
        try{
            $project = $this->getNewData($projectId);
            $mountMargin = $project->dealer_mounting_margin;
            $calculations = $this->getCalculationIdById($projectId);
            $ids = [];
            foreach ($calculations as $item){
                array_push($ids,$item->id);
            }
            $calculatuionsIds = implode(',',$ids);
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $subQuery = $db->getQuery(true);
            $subQuery
                ->select('DISTINCT `cjm`.`job_id`, SUM(`cjm`.`count`) AS `job_count_all`')
                ->from('`rgzbn_gm_ceiling_calcs_jobs_map` AS `cjm`')
                ->where(" `cjm`.`calc_id` IN($calculatuionsIds)")
                ->group('`cjm`.`job_id`');
            $query
                ->select('SUM(ROUND(`jf`.`job_count_all` * IFNULL(`jdp`.`price`, 0), 2)) AS `price_sum`')
                ->select("SUM(ROUND(`jf`.`job_count_all` * IFNULL(`jdp`.`price`, 0) * 100 / (100 - $mountMargin), 2)) AS `price_sum_with_margin`")
                ->from("($subQuery) as `jf`")
                ->innerJoin('`rgzbn_gm_ceiling_jobs` AS `j` ON  `jf`.`job_id` = `j`.`id`')
                ->leftJoin("`rgzbn_gm_ceiling_jobs_dealer_price` AS `jdp` ON  `jf`.`job_id` = `jdp`.`job_id` AND `jdp`.`dealer_id` = $dealerId")
                ->where('`j`.`guild_only` = 0 AND `j`.`is_factory_work` = 0');
            $db->setQuery($query);
            $result = $db->loadObject();
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getByClientId($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`rgzbn_gm_ceiling_projects`')
                ->where("client_id = $id and deleted_by_user <> 1");
            $db->setQuery($query);
            $item = $db->loadObjectList();
            return $item;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function update($project){
        try{
            $projectData = [];
            if(!empty($project)){
                foreach ($project as $key=>$value){
                    if(!empty($value)){
                        $projectData[$key] = $value;
                    }
                }
            }
            if(!empty($projectData)){
                $this->save($projectData);
            }
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function delete($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`rgzbn_gm_ceiling_projects`')
                ->set('deleted_by_user = 1')
                ->where("id = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /*END*/
}
