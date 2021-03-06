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
				$calcFormModel = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
				$this->_item->dealer_id = $db->loadObject()->dealer_id;
                $this->_item->n13 = $calcFormModel->n13_load($this->_item->id);
                $this->_item->n14 = $calcFormModel->n14_load($this->_item->id);
                $this->_item->n15 = $calcFormModel->n15_load($this->_item->id);
                $this->_item->n22 = $calcFormModel->n22_load($this->_item->id);
                $this->_item->n23 = $calcFormModel->n23_load($this->_item->id);
                $this->_item->n26 = $calcFormModel->n26_load($this->_item->id);
                $this->_item->n29 = $calcFormModel->n29_load($this->_item->id);
                $this->_item->n45 = $calcFormModel->n45_load($this->_item->id);
                $this->_item->n19 = $calcFormModel->n19_load($this->_item->id);
				//throw new Exception("Error Processing Request", 1);
			return $this->_item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function getBaseCalculationDataById($id){
	    try{
            if(!empty($id)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->select('c.*,cut.canvas_area')
                    ->from('`#__gm_ceiling_calculations` as c')
                    ->leftJoin('`#__gm_ceiling_cuttings` as `cut` on `cut`.`id` = `c`.`id`')
                    ->where("c.id = $id");
                $db->setQuery($query);
                $item = $db->loadObject();
                return $item;
            }
            else{
                throw new Exception("Empty calculation id!");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	public function new_getData($id){
		try{
			if(!empty($id)){
			    //$calculationFormModel = Gm_ceilingHelpersGm_ceiling::getModel('CalculationForm');
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
				$item = $this->getBaseCalculationDataById($id);
				$item->goods = $this->getGoodsFromCalculation($id);
				$item->jobs = $this->getJobsFromCalculation($id);
				$query
					->select('client.dealer_id')
					->from('`#__gm_ceiling_clients` as client')
					->join('LEFT','`#__gm_ceiling_projects` AS proj ON proj.client_id = client.id')
					->join('LEFT','`#__gm_ceiling_calculations` AS calc ON calc.project_id = proj.id')
					->where('calc.id  = ' . $id);
				$db->setQuery($query);
				$item->dealer_id = $db->loadObject()->dealer_id;
				/*$allGoods = $calculationFormModel->getGoodsPricesInCalculation($id,$item->dealer_id);
                if(!empty($calculation->cancel_metiz)){
                    $item->allGoods = Gm_ceilingHelpersGm_ceiling::deleteMetizFromGoods($allGoods);
                }
                else{
                    $item->allGoods = $allGoods;
                }
                $item->allJobs = $calculationFormModel->getJobsPricesInCalculation($id,$item->dealer_id);*/
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

    public function create_calculation($proj_id,$calculationTitle = null){
        try
        {
            $data['project_id'] = $proj_id;
            $table = $this->getTable();
            if(empty($calculationTitle)){
                $db = JFactory::getDBO();
                $query = 'SELECT `id`, `calculation_title` FROM `#__gm_ceiling_calculations` WHERE `project_id` = ' . $proj_id . ' AND `calculation_title` LIKE  \'%Потолок%\'';
                $db->setQuery($query);
                $calculations = $db->loadObjectList();
                $indexes = []; $index = 1;
                foreach ($calculations as $calculation) {
                    $indexes[] = intval(str_replace("Потолок ", "", $calculation->calculation_title));
                    if (in_array($index, $indexes)) $index += 1;
                }
                $data['calculation_title'] = "Потолок $index";
            }
            else{
                $data['calculation_title'] = $calculationTitle;
            }
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

    public function duplicate_calculation($data)
    {
        try
        {
            //throw new Exception(print_r($data,true));
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

    public function update($id, $filter) {
    	$db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->update('`#__gm_ceiling_calculations`');
        $query->set($filter);
        $query->where("`id` = $id");
        $db->setQuery($query);
        $db->execute();
        return true;
    }

    function duplicate($data){
	    try{
            $model_calcform = Gm_ceilingHelpersGm_ceiling::getModel('CalculationForm');
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            //throw new Exception(print_r($data,true));
            $dealer_id = $data['dealer_id'];
            $mountData = $data['mountData'];
            $canvasesArea = $data['canvas_area'];
            $goods = $data['goods'];
            $jobs = $data['jobs'];

            unset($data['dealer_id']);
            unset($data['mountData']);
            unset($data['canvas_area']);
            unset($data['goods']);
            unset($data['jobs']);

            foreach($data as $key=>$value){
                if(empty($value) && $value !== 0){
                    unset($data[$key]);
                }
            }

            //throw new Exception(print_r($data,true));
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

            $model_calcform->addGoodsInCalculation($calculationId, $goods, false); // Добавление компонентов
            $model_calcform->addJobsInCalculation($calculationId, $jobs, false); // Добавление работ

            if(!empty($mountData)){
                $query = $db->getQuery(true);
                $query
                    ->insert('`rgzbn_gm_ceiling_calcs_mount`')
                    ->columns('`calculation_id`, `stage_id`, `sum`');
                foreach ($mountData as $mountItem) {
                    $query->values($calculationId . ', ' . $mountItem->stage_id . ', ' . $mountItem->sum);
                }
                $db->setQuery($query);
                $db->execute();
            }


            if(!empty($canvasesArea)){
                $query = $db->getQuery(true);
                $query->insert('`rgzbn_gm_ceiling_cuttings`')
                    ->columns('id,ready,data,canvas_area');

                $query->values($calculationId . ', ' . $canvasesArea->ready . ', \'' . $canvasesArea->data . '\','.$canvasesArea->canvas_area);

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

    function updateSum($calcId,$sum){
	    try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_ceiling_calculations`')
                ->set("canvases_sum = $sum")
                ->set('components_sum = 0')
                ->where("id = $calcId");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function save_ready_time($calc_id,$ready_time){
	    try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->update('`#__gm_ceiling_calculations`');
            if($ready_time != "by_call"){
                $query->set("`run_by_call` = (NULL)");
                $query->set("`run_date` = '$ready_time'");
            }
            else{
                $query->set("`run_by_call` = 1");
                $query->set("`run_date` = (NULL)");
            }
            $query->where("id = $calc_id");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getCalcIndex($project_id){
	    try{
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query
                ->select("`id`, `calculation_title`")
                ->from('`#__gm_ceiling_calculations`')
                ->where("`project_id` = $project_id AND `calculation_title` LIKE '%Потолок%'");
            $db->setQuery($query);
            $calculations = $db->loadObjectList();
            $indexes = []; $index = 1;
            foreach ($calculations as $calculation) {
                $indexes[] = intval(str_replace("Потолок ", "", $calculation->calculation_title));
                if (in_array($index, $indexes)) $index += 1;
            }
            return $index;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveComment($calcId, $comment) {
	    try {
            $db = JFactory::getDBO();
            $comment = $db->escape($comment);
            $query = $db->getQuery(true);
                $query
                    ->update('`#__gm_ceiling_calculations`')
                    ->set("`comment`='$comment'")
                    ->where("`id` = $calcId");
            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getGoodsFromCalculation($calc_id){
	    try{
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query
                ->select('`g`.`id`,`g`.`category_id`,`g`.`name`,`cgm`.`count`')
                ->from('`rgzbn_gm_ceiling_calcs_goods_map` as `cgm`')
                ->innerJoin('`rgzbn_gm_stock_goods` as `g` on `g`.`id` = `cgm`.`goods_id`')
                ->where("`cgm`.`calc_id` = $calc_id");
            $db->setQuery($query);
            $items = $db->loadObjectList();
	        return $items;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getJobsFromCalculation($calc_id){
        try{
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query
                ->select('`j`.`id`,`j`.`is_factory_work`,`j`.`guild_only`,`j`.`name`,`cjm`.`count`')
                ->from('`rgzbn_gm_ceiling_calcs_jobs_map` as `cjm`')
                ->innerJoin('`rgzbn_gm_ceiling_jobs` as `j` on `j`.`id` = `cjm`.`job_id`')
                ->where("`cjm`.`calc_id` = $calc_id");
            $db->setQuery($query);
            //throw new Exception($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getProjectMargin($calcId){
	    try{
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query
                ->select('`p`.`dealer_canvases_margin`,`p`.`dealer_components_margin`,`p`.`dealer_mounting_margin`')
                ->from('`rgzbn_gm_ceiling_calculations` as c')
                ->leftJoin('`rgzbn_gm_ceiling_projects` as p on c.project_id = p.id')
                ->where("c.id = $calcId");
            $db->setQuery($query);
            $result = $db->loadObject();
            return $result;
        }

        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveDetails($title,$comment,$manager_note,$calc_id){
	    try{
            if((!empty($title) || !empty($comment) || !empty($manager_note)) && !empty($calc_id)){
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->update('`#__gm_ceiling_calculations`');
                if(!empty($title)){
                    $query->set("`calculation_title`='$title'");
                }
                if(!empty($comment)){
                    $query->set("`details`='$comment'");
                }
                if(!empty($manager_note)){
                    $query->set("`manager_note` = '$manager_note'");
                }
                $query->where("`id`=$calc_id");
                $db->setQuery($query);
                $db->execute();
            }
            return true;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getByProjectId($projectId){
	    try{

        } catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
