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
class Gm_ceilingModelCalculation extends JModelItem
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
				$id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.calculation.id');
			}
			else
			{
				$id = JFactory::getApplication()->input->get('id');
				JFactory::getApplication()->setUserState('com_gm_ceiling.edit.calculation.id', $id);
			}

			$this->setState('calculation.id', $id);

			// Load the parameters.
			$params       = $app->getParams();
			$params_array = $params->toArray();

			if (isset($params_array['item_id']))
			{
				$this->setState('calculation.id', $params_array['item_id']);
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
		    //throw new Exception($id);
			if ($this->_item === null)
			{
				$this->_item = false;

				if (empty($id))
				{
					$id = $this->getState('calculation.id');
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
			}

			if (isset($this->_item->modified_by) )
			{
				$this->_item->modified_by_name = JFactory::getUser($this->_item->modified_by)->name;
			}

				if (isset($this->_item->project_id) && $this->_item->project_id != '') {
					if (is_object($this->_item->project_id)){
						$this->_item->project_id = \Joomla\Utilities\ArrayHelper::fromObject($this->_item->project_id);
					}
					$values = (is_array($this->_item->project_id)) ? $this->_item->project_id : explode(',',$this->_item->project_id);

					$textValue = array();
					foreach ($values as $value)
					{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query
							->select('`#__gm_ceiling_projects_2463298`.`id`')
							->from($db->quoteName('#__gm_ceiling_projects', '#__gm_ceiling_projects_2463298'))
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
						$db->setQuery($query);
						$results = $db->loadObject();
						if ($results) {
							$textValue[] = $results->id;
						}
					}

				$this->_item->project_id = !empty($textValue) ? implode(', ', $textValue) : $this->_item->project_id;

				}

				if (isset($this->_item->n1) && $this->_item->n1 != '') {
					if (is_object($this->_item->n1)){
						$this->_item->n1 = \Joomla\Utilities\ArrayHelper::fromObject($this->_item->n1);
					}
					$values = (is_array($this->_item->n1)) ? $this->_item->n1 : explode(',',$this->_item->n1);

					$textValue = array();
					foreach ($values as $value)
					{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query
							->select('`#__gm_ceiling_textures_2460779`.`texture_title`')
							->from($db->quoteName('#__gm_ceiling_textures', '#__gm_ceiling_textures_2460779'))
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
						$db->setQuery($query);
						$results = $db->loadObject();
						if ($results) {
							$textValue[] = $results->texture_title;
							
						}
					}

				//KM_CHANGED START
				$this->_item->n1_id = $this->_item->n1;
				//KM_CHANGED END
				$this->_item->n1 = !empty($textValue) ? implode(', ', $textValue) : $this->_item->n1;

				}

				if (isset($this->_item->n2) && $this->_item->n2 != '') {
					if (is_object($this->_item->n2)){
						$this->_item->n2 = \Joomla\Utilities\ArrayHelper::fromObject($this->_item->n2);
					}
					$values = (is_array($this->_item->n2)) ? $this->_item->n2 : explode(',',$this->_item->n2);

					$textValue = array();
					foreach ($values as $value)
					{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query
							->select('`#__gm_ceiling_textures_2460779`.`texture_title`')
							->from($db->quoteName('#__gm_ceiling_textures', '#__gm_ceiling_textures_2460779'))
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
						$db->setQuery($query);
						$results = $db->loadObject();
						if ($results) {
							$textValue[] = $results->texture_title;
							
							
						}
					}
				//KM_CHANGED START
				$this->_item->n2_id = $this->_item->n2;
				//KM_CHANGED END
				$this->_item->n2 = !empty($textValue) ? implode(', ', $textValue) : $this->_item->n2;

				}

				if (isset($this->_item->n3) && $this->_item->n3 != '') {
					if (is_object($this->_item->n3)){
						$this->_item->n3 = \Joomla\Utilities\ArrayHelper::fromObject($this->_item->n3);
					}
					$values = (is_array($this->_item->n3)) ? $this->_item->n3 : explode(',',$this->_item->n3);

					$textValue = array();
					foreach ($values as $value)
					{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query
							->select('*')
							->from($db->quoteName('#__gm_ceiling_canvases', '#__gm_ceiling_canvases_2463047'))
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
						$db->setQuery($query);
						$results = $db->loadObject();
						if ($results) {
							$textValue[] = $results->name." ".$results->country." ".$results->width;						
						}
					}
				//KM_CHANGED START
				$this->_item->n3_id = $this->_item->n3;
				//KM_CHANGED END
				$this->_item->n3 = !empty($textValue) ? implode(', ', $textValue) : $this->_item->n3;

				}
				//throw new Exception($this->_item->n13, 1);


				$query = $db->getQuery(true);
				$query
					->select('client.dealer_id')
					->from('`#__gm_ceiling_clients` as client')
					->join('LEFT','`#__gm_ceiling_projects` AS proj ON proj.client_id = client.id')
					->join('LEFT','`#__gm_ceiling_calculations` AS calc ON calc.project_id = proj.id')
					->where('calc.id  = ' . $this->_item->id);
				$db->setQuery($query);
				$this->_item->dealer_id = $db->loadObject()->dealer_id;

                $this->_item->n13 = $this->n13_load($this->_item->id);
				$this->_item->n14 = $this->n14_load($this->_item->id);
	            $this->_item->n15 = $this->n15_load($this->_item->id);
	            $this->_item->n22 = $this->n22_load($this->_item->id);
	            $this->_item->n23 = $this->n23_load($this->_item->id);
	            $this->_item->n26 = $this->n26_load($this->_item->id);
				$this->_item->n29 = $this->n29_load($this->_item->id);

			return $this->_item;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function new_getData($id){
		try{
			if(!empty($id)){
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query
					->select('*')
					->from('`#__gm_ceiling_calculations` as c')
					->where("c.id = $id");
				$db->setQuery($query);
				$item = $db->loadObject();
				$item->n13 = $this->n13_load($item->id);
				$item->n14 = $this->n14_load($item->id);
	            $item->n15 = $this->n15_load($item->id);
	            $item->n22 = $this->n22_load($item->id);
	            $item->n23 = $this->n23_load($item->id);
	            $item->n26 = $this->n26_load($item->id);
				$item->n29 = $this->n29_load($item->id);
				$query
					->select('client.dealer_id')
					->from('`#__gm_ceiling_clients` as client')
					->join('LEFT','`#__gm_ceiling_projects` AS proj ON proj.client_id = client.id')
					->join('LEFT','`#__gm_ceiling_calculations` AS calc ON calc.project_id = proj.id')
					->where('calc.id  = ' . $item->id);
				$db->setQuery($query);
				$item->dealer_id = $db->loadObject()->dealer_id;
				return $item;
			}
			else{
				throw new Exception("Пустой id калькуляции");
			}
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function n13_load($id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);

	        $query->select('fixtures.*, components_option.id AS option_id, components_option.component_id AS component_id')
	            ->from('`#__gm_ceiling_fixtures` AS fixtures')
	            ->select('components_option.title AS component_title')
	            ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = fixtures.n13_size')
	            ->select('type.title AS type_title, type.id AS type_id')
	            ->join('LEFT', '`#__gm_ceiling_type` AS type ON type.id = fixtures.n13_type')
	            ->where('fixtures.calculation_id' . ' = ' . $id);

	        $db->setQuery($query);
	        $result = $db->loadObjectList();
	        return $result;
	    }
	    catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function n14_load($id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);

	        $query->select('pipes.*')
	            ->from('`#__gm_ceiling_pipes` AS pipes')
	            ->select('components_option.title AS component_title')
	            ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = pipes.n14_size')
	            ->where('pipes.calculation_id = ' . $id);

	        $db->setQuery($query);
	        $result = $db->loadObjectList();
	        return $result;
	    }
	    catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function n15_load($id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);

	        $query->select('cornice.*')
	            ->from('`#__gm_ceiling_cornice` AS cornice')
	            ->select('components_option.title AS component_title')
	            ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = cornice.n15_size')
	            ->where('cornice.calculation_id = ' . $id);

	        $db->setQuery($query);
	        $result = $db->loadObjectList();
	        return $result;
	    }
	    catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function n22_load($id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);

	        $query->select('hoods.*')
	            ->from('`#__gm_ceiling_hoods` AS hoods')
	            ->select('components_option.title AS component_title')
	            ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = hoods.n22_size')
	            ->select('type.title AS type_title, type.id AS type_id')
	            ->join('LEFT', '`#__gm_ceiling_type` AS type ON type.id = hoods.n22_type')
	            ->where('hoods.calculation_id = ' . $id);

	        $db->setQuery($query);
	        $result = $db->loadObjectList();
	        return $result;
	    }
	    catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function n23_load($id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);

	        $query->select('diffusers.*')
	            ->from('`#__gm_ceiling_diffusers` AS diffusers')
	            ->select('components_option.title AS component_title')
	            ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = diffusers.n23_size')
	            ->where('diffusers.calculation_id = ' . $id);

	        $db->setQuery($query);
	        $result = $db->loadObjectList();
	        return $result;
	    }
	    catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function n26_load($id)
    {
    	try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);

	        $query->select('ecola.*')
	            ->from('`#__gm_ceiling_ecola` AS ecola')
	            ->select('components_option.title AS component_title_illum')
	            ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = ecola.n26_illuminator')
	            ->select('components_option_lamp.title AS component_title')
	            ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option_lamp ON components_option_lamp.id = ecola.n26_lamp')
	            ->where('ecola.calculation_id = ' . $id);

	        $db->setQuery($query);
	        $result = $db->loadObjectList();
	        return $result;
	    }
	    catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function n29_load($id)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('profil.*')
                ->from('`#__gm_ceiling_profil` AS profil')
                ->select('components_option.title AS component_title')
                ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = profil.n29_profil')
                ->select('type.title AS type_title')
                ->join('LEFT', '`#__gm_ceiling_type` AS type ON type.id = profil.n29_type')
                ->where('profil.calculation_id' . ' = ' . $id);

            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
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
	public function getTable($type = 'Calculation', $prefix = 'Gm_ceilingTable', $config = array())
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
	
	public function changeProjectId($calculation_id, $project_id)
	{
		try
		{
			$table = $this->getTable();
			$table->load($calculation_id);
			$table->project_id = $project_id;
			$return = $table->store();

			return $return;
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
			$id = (!empty($id)) ? $id : (int) $this->getState('calculation.id');

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
			$id = (!empty($id)) ? $id : (int) $this->getState('calculation.id');

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
			$table->state = $state;

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
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getDataById($id)
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('*')
				->from('`#__gm_ceiling_calculations`')
				->where('id = ' . $id);
			$db->setQuery($query);

			return $db->loadObject();
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

    public function getImage($id, $type)
    {
        try
        {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("$type")
                ->from('`#__gm_ceiling_calculations`')
                ->where('id = ' . $id);
            $db->setQuery($query);

            return $db->loadObject();
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function create_calculation($proj_id)
    {
        try
        {
            $data['project_id'] = $proj_id;
            $table = $this->getTable();
			if ($table->save($data) === true)
			{
				return $table->id;
			}
			else
			{
				return false;
			}
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function save($id,$project_id){
    	try
        {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`#__gm_ceiling_calculations`')
                ->columns('`id`,`project_id`')
                ->values("$id, $project_id");
            $db->setQuery($query);
            $db->execute();
            return $last_id = $db->insertid();

        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function update_calculation($data)
    {
        try
        {
			$table = $this->getTable();
			//по хорошему нужно смотреть больше про JTable методы bind и на моделях возможно переписывать многое
			if ($table->save($data) === true)
			{
				return true;
			}
			else
			{
				return false;
			}
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
