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
				//throw new Exception("Error Processing Request", 1);
			return $this->_item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
				if (empty($item)) {
					return null;
				}
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getDataForAnalytic($project_id){
		try
    	{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $price_subquery = $db->getQuery(true);
	        $price_subquery
	        	->select('price')
	        	->from('`#__gm_ceiling_analytics_canvases`')
	        	->where('roller_id = ca.id AND `status` = 1');
	        $query
	        	->select("DISTINCT c.id,c.n3,c.n4,c.n5,canv.manufacturer_id,mnf.name,c.n31,c.n5_shrink,c.offcut_square,c.n9,c.components_sum,c.canvases_sum,cut.canvas_area,($price_subquery) as self_price")
	            ->from('`#__gm_ceiling_calculations` AS c')
	            ->innerJoin('`#__gm_ceiling_canvases` AS canv ON c.n3 = canv.id')
	            ->innerJoin('`#__gm_ceiling_canvases_manufacturers` AS `mnf` ON canv.manufacturer_id = mnf.id')
	            ->innerJoin('`#__gm_ceiling_cuttings` AS cut ON c.id = cut.id')
	            ->innerJoin('`#__gm_ceiling_canvases_all` AS ca ON ca.id_canvas = c.n3')
	            ->where("c.project_id = $project_id")
				->group("c.id");
	        $db->setQuery($query);
	        $result = $db->loadObjectList();
	        return $result;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
	public function getTable($type = 'Calculation', $prefix = 'Gm_ceilingTable', $config = array())
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
				return (int)$table->id;
			}
			else
			{
				return false;
			}
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function duplicate($data){
	    try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $n13 = $data['n13'];
            $n14 = $data['n14'];
            $n15 = $data['n15'];
            $n22 = $data['n22'];
            $n23 = $data['n23'];
            $n26 = $data['n26'];
            $n29 = $data['n29'];
            unset($data['n13']);
            unset($data['n14']);
            unset($data['n15']);
            unset($data['n22']);
            unset($data['n23']);
            unset($data['n26']);
            unset($data['n29']);
            unset($data['dealer_id']);

            $columns = array_keys($data);
            $values = array_values($data);
            foreach ($values as $key=>$value){
                $values[$key] = $db->quote($value);
            }
            $values = implode(',',$values);
            $query
                ->insert($db->quoteName('#__gm_ceiling_calculations'))
                ->columns($db->quoteName($columns))
                ->values($values);
            //throw new Exception($query);
            $db->setQuery($query);
            $db->execute();
            $calculationId = $db->insertId();
            if (!empty($n13)) {
                $query = $db->getQuery(true);
                $query
                    ->insert('`#__gm_ceiling_fixtures`')
                    ->columns('`calculation_id`, `n13_count`, `n13_type`, `n13_size`');

                foreach ($n13 as $value) {
                    $query->values($calculationId . ', ' . $value->n13_count . ', ' . $value->n13_type . ', ' . $value->n13_size);
                }
                $db->setQuery($query);
                $db->execute();

            }

            if (!empty($n14)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_pipes`')
                    ->columns('calculation_id, n14_count, n14_size');
                foreach ($n14 as $value) {
                    $query->values($calculationId . ', ' . $value->n14_count . ', ' . $value->n14_size);
                }
                $db->setQuery($query);
                $db->execute();
            }
            if (!empty($n15)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_cornice`')
                    ->columns('calculation_id, n15_count, n15_type, n15_size');
                foreach ($n15 as $value) {
                    $query->values($calculationId . ', ' . $value->n15_count . ', ' . $value->n15_type . ', ' . $value->n15_size);
                }
                $db->setQuery($query);
                $db->execute();
            }
            if (!empty($n22)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_hoods`')
                    ->columns('calculation_id, n22_count, n22_type, n22_size');
                foreach ($n22 as $value) {
                    $query->values($calculationId . ', ' . $value->n22_count . ', ' . $value->n22_type . ', ' . $value->n22_size);
                }
                $db->setQuery($query);
                $db->execute();
            }
            if (!empty($n23)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_diffusers`')
                    ->columns('calculation_id, n23_count, n23_size');
                foreach ($n23 as $value) {
                    $query->values($calculationId . ', ' . $value->n23_count . ', ' . $value->n23_size);
                }
                $db->setQuery($query);
                $db->execute();
            }
            if (!empty($n26)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_ecola`')
                    ->columns('calculation_id, n26_count, n26_illuminator, n26_lamp');
                foreach ($n26 as $value) {
                    $query->values($calculationId . ', ' . $value->n26_count . ', ' . $value->n26_illuminator . ', ' . $value->n26_lamp);
                }
                $db->setQuery($query);
                $db->execute();
            }

            if (!empty($n29)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_profil`')
                    ->columns('calculation_id, n29_count, n29_type');
                foreach ($n29 as $value) {
                    $query->values($calculationId . ', ' . $value->n29_count . ', ' . $value->n29_type);
                }
                $db->setQuery($query);
                $db->execute();
            }
            return $calculationId;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
