<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelCalculations extends JModelList {
    /**
     * Constructor.
     *
     * @param   array $config An optional associative array of configuration settings.
     *
     * @see        JController
     * @since      1.6
     */
    public function __construct($config = array())
    {
        try
        {
            if (empty($config['filter_fields'])) {
                    $config['filter_fields'] = array(
                        'id', 'a.id',
                        'ordering', 'a.ordering',
                        'state', 'a.state',
                        'created_by', 'a.created_by',
                        'modified_by', 'a.modified_by',
                        'calculation_title', 'a.calculation_title',
                        'project_id', 'a.project_id',
                        'components_sum', 'a.components_sum',
                        'mounting_sum', 'a.mounting_sum',
                        'gm_mounting_sum', 'a.gm_mounting_sum',
                        'dealer_mounting_sum', 'a.dealer_mounting_sum',
                        'transport', 'a.transport',
                        'n1', 'a.n1',
                        'n2', 'a.n2',
                        'n3', 'a.n3',
                        'n4', 'a.n4',
                        'n5', 'a.n5',
                        'n6', 'a.n6',
                        'n7', 'a.n7',
                        'n8', 'a.n8',
                        'n9', 'a.n9',
                        'n10', 'a.n10',
                        'n11', 'a.n11',
                        'n12', 'a.n12',
                        'n16', 'a.n16',
                        'n17', 'a.n17',
                        'n18', 'a.n18',
                        'n19', 'a.n19',
                        'n20', 'a.n20',
                        'n21', 'a.n21',
                        'n24', 'a.n24',
                        'n25', 'a.n25',
                        'dop_krepezh', 'a.dop_krepezh',
                        'extra_components', 'a.extra_components',
                        'extra_mounting', 'a.extra_mounting',
                        'n13_count', 'a.n13_count',
                        'n13_type', 'a.n13_type',
                        'n13_size', 'a.n13_size',
                        'n14_count', 'a.n14_count',
                        'n14_type', 'a.n14_type',
                        'n15_count', 'a.n15_count',
                        'n15_type', 'a.n15_type',
                        'n15_size', 'a.n15_size',
                        'n23_count', 'a.n23_count',
                        'n23_size', 'a.n23_size',
                        'n26_count', 'a.n26_count',
                        'n26_illuminator', 'a.n26_illuminator',
                        'n26_lamp', 'a.n26_lamp',
                        'n22_count', 'a.n22_count',
                        'n22_type', 'a.n22_type',
                        'n22_size', 'a.n22_size'
        
        
                    );
                }
        
                parent::__construct($config);
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
     * @param   string $ordering Elements order
     * @param   string $direction Order direction
     *
     * @return void
     *
     * @throws Exception
     *
     * @since    1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        try
        {
            // Initialise variables.
            $app = JFactory::getApplication();

            $list = $app->getUserState($this->context . '.list');

            if (empty($list['ordering'])) {
                $list['ordering'] = 'id';
            }

            if (empty($list['direction'])) {
                $list['direction'] = 'desc';
            }

            if (isset($list['ordering'])) {
                $this->setState('list.ordering', $list['ordering']);
            }

            if (isset($list['direction'])) {
                $this->setState('list.direction', $list['direction']);
            }

            // List state information.
            parent::populateState($ordering, $direction);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return   JDatabaseQuery
     *
     * @since    1.6
     */
    protected function getListQuery()
    {
        try
        {
            // Create a new query object.
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            // Select the required fields from the table.
            $query
                ->select(
                    $this->getState(
                        'list.select', 'DISTINCT a.*'
                    )
                );

            $query->from('`#__gm_ceiling_calculations` AS a');

            // Join over the users for the checked out user.
            $query->select('uc.name AS editor');
            $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

            // Join over the created by field 'created_by'
            $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

            // Join over the created by field 'modified_by'
            $query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');
            // Join over the foreign key 'project_id'
            $query->select('`#__gm_ceiling_projects_2463298`.`id` AS projects_fk_value_2463298, 
    						`#__gm_ceiling_projects_2463298`.`gm_canvases_margin` AS gm_canvases_margin, 
    						`#__gm_ceiling_projects_2463298`.`gm_components_margin` AS gm_components_margin, 
    						`#__gm_ceiling_projects_2463298`.`gm_mounting_margin` AS gm_mounting_margin,
    						`#__gm_ceiling_projects_2463298`.`dealer_canvases_margin` AS dealer_canvases_margin,
    						`#__gm_ceiling_projects_2463298`.`dealer_components_margin` AS dealer_components_margin,
    						`#__gm_ceiling_projects_2463298`.`dealer_mounting_margin` AS dealer_mounting_margin');
            $query->join('LEFT', '#__gm_ceiling_projects AS #__gm_ceiling_projects_2463298 ON #__gm_ceiling_projects_2463298.`id` = a.`project_id`');
            // Join over the foreign key 'n1'
            $query->select('`#__gm_ceiling_textures_2460779`.`texture_title` AS textures_fk_value_2460779');
            $query->join('LEFT', '#__gm_ceiling_textures AS #__gm_ceiling_textures_2460779 ON #__gm_ceiling_textures_2460779.`id` = a.`n1` ');
            // Join over the foreign key 'n2'
            $query->select('`#__gm_ceiling_textures_2460778`.`texture_title` AS textures_fk_value_2460779');
            $query->join('LEFT', '#__gm_ceiling_textures AS #__gm_ceiling_textures_2460778 ON #__gm_ceiling_textures_2460778.`id` = a.`n2` ');

            // Join over the foreign key 'n3'
            $query->select('CONCAT(`#__gm_ceiling_canvases_2463047`.`name`, \' \',`#__gm_ceiling_canvases_2463047`.`country`,\' \',`#__gm_ceiling_canvases_2463047`.`width` ) AS canvases_fk_value_2463047');
            $query->join('LEFT', '#__gm_ceiling_canvases AS #__gm_ceiling_canvases_2463047 ON #__gm_ceiling_canvases_2463047.`id` = a.`n3` ');

            /*
                    $query->select('fixtures.n13_count AS n13_count, fixtures.n13_type AS n13_type, fixtures.n13_size AS n13_size');
                    $query->join('LEFT', '#__gm_ceiling_fixtures AS fixtures ON fixtures.calculation_id = a.id');

                    $query->select('cornice.n15_count AS n15_count, cornice.n15_type AS n15_type, cornice.n15_size AS n15_size');
                    $query->join('LEFT', '#__gm_ceiling_cornice AS cornice ON cornice.calculation_id = a.id');

                    $query->select('diffusers.n23_count AS n23_count, diffusers.n23_size AS n23_size');
                    $query->join('LEFT', '#__gm_ceiling_diffusers AS diffusers ON diffusers.calculation_id = a.id');

                    $query->select('ecola.n26_count AS n26_count, ecola.n26_illuminator AS n26_illuminator, ecola.n26_lamp AS n26_lamp');
                    $query->join('LEFT', '#__gm_ceiling_ecola AS ecola ON ecola.calculation_id = a.id');

                    $query->select('hoods.n22_count AS n22_count, hoods.n22_type AS n22_type, hoods.n22_size AS n22_size');
                    $query->join('LEFT', '#__gm_ceiling_hoods AS hoods ON hoods.calculation_id = a.id');

                    $query->select('pipes.n14_count AS n14_count, pipes.n14_size AS n14_size');
                    $query->join('LEFT', '#__gm_ceiling_pipes AS pipes ON pipes.calculation_id = a.id');
            */
            $query->where('a.state = 1');
            //$query->where('a.project_id <= 0');


            // Filter by search in title
            $search = $this->getState('filter.search');

            if (!empty($search)) {
                if (stripos($search, 'id:') === 0) {
                    $query->where('a.id = ' . (int)substr($search, 3));
                } else {
                    $search = $db->Quote('%' . $db->escape($search, true) . '%');
                    $query->where('( a.calculation_title LIKE ' . $search . '  OR #__gm_ceiling_projects_2463298.id LIKE ' . $search . '  OR  a.components_sum LIKE ' . $search . '  OR  a.mounting_sum LIKE ' . $search . '  OR  a.gm_mounting_sum LIKE ' . $search . '  OR  a.dealer_mounting_sum LIKE ' . $search . '  OR  a.n4 LIKE ' . $search . '  OR  a.n5 LIKE ' . $search . ' )');
                }
            }

            //KM_CHANGED START

            //Filtering project_id
            $filter_project_id = $this->state->get("filter.project_id");

            if ($filter_project_id) {
                $query->where("a.`project_id` = '" . $db->escape($filter_project_id) . "'");
            }/* else {
    			$query->where("a.`project_id` = NULL ");
    		}*/

            //KM_CHANGED END

            // Filtering n1
            $filter_n1 = $this->state->get("filter.n1");
            if ($filter_n1) {
                $query->where("a.n1 = '" . $db->escape($filter_n1) . "'");
            }

            // Filtering n2
            $filter_n2 = $this->state->get("filter.n2");
            if ($filter_n2) {
                $query->where("a.n2 = '" . $db->escape($filter_n2) . "'");
            }

            // Filtering n3
            $filter_n3 = $this->state->get("filter.n3");
            if ($filter_n3) {
                $query->where("a.n3 = '" . $db->escape($filter_n3) . "'");
            }
            // Add the list ordering clause.
            $orderCol = $this->state->get('list.ordering');
            $orderDirn = $this->state->get('list.direction');

            //KM_CHANGED START
            if ($orderCol && $orderDirn) {
                $query->order($db->escape($orderCol . ' ' . $orderDirn));
            } else {
                $query->order($db->escape('id desc'));
            }
            //KM_CHANGED END
            return $query;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to get an array of data items
     *
     * @return  mixed An array of data on success, false on failure.
     */
    public function getItems()
    {
        try
        {
            $items = parent::getItems();
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (isset($item->project_id) && $item->project_id != '') {
                        if (is_object($item->project_id)) {
                            $item->project_id = \Joomla\Utilities\ArrayHelper::fromObject($item->project_id);
                        }

                        $values = (is_array($item->project_id)) ? $item->project_id : explode(',', $item->project_id);
                        $textValue = array();

                        foreach ($values as $value) {
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

                        $item->project_id = !empty($textValue) ? implode(', ', $textValue) : $item->project_id;
                    }
                    if (isset($item->n1) && $item->n1 != '') {
                        if (is_object($item->n1)) {
                            $item->n1 = \Joomla\Utilities\ArrayHelper::fromObject($item->n1);
                        }

                        $values = (is_array($item->n1)) ? $item->n1 : explode(',', $item->n1);
                        $textValue = array();

                        foreach ($values as $value) {
                            $db = JFactory::getDbo();
                            $query = $db->getQuery(true);
                            $query
                                ->select('`#__gm_ceiling_projects_2463298`.`texture_title`')
                                ->from($db->quoteName('#__gm_ceiling_textures', '#__gm_ceiling_projects_2463298'))
                                ->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
                            $db->setQuery($query);
                            $results = $db->loadObject();

                            if ($results) {
                                $textValue[] = $results->texture_title;
                            }
                        }

                        $item->n1 = !empty($textValue) ? implode(', ', $textValue) : $item->n1;
                    }
                    if (isset($item->n2) && $item->n2 != '') {
                        if (is_object($item->n2)) {
                            $item->n2 = \Joomla\Utilities\ArrayHelper::fromObject($item->n2);
                        }

                        $values = (is_array($item->n2)) ? $item->n2 : explode(',', $item->n2);
                        $textValue = array();

                        foreach ($values as $value) {
                            $db = JFactory::getDbo();
                            $query = $db->getQuery(true);
                            $query
                                ->select('`#__gm_ceiling_projects_2463298`.`texture_title`')
                                ->from($db->quoteName('#__gm_ceiling_textures', '#__gm_ceiling_projects_2463298'))
                                ->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
                            $db->setQuery($query);
                            $results = $db->loadObject();

                            if ($results) {
                                $textValue[] = $results->texture_title;
                            }
                        }

                        $item->n2 = !empty($textValue) ? implode(', ', $textValue) : $item->n2;
                    }
                    if (isset($item->n3) && $item->n3 != '') {
                        if (is_object($item->n3)) {
                            $item->n3 = \Joomla\Utilities\ArrayHelper::fromObject($item->n3);
                        }

                        $values = (is_array($item->n3)) ? $item->n3 : explode(',', $item->n3);
                        $textValue = array();

                        foreach ($values as $value) {
                            $db = JFactory::getDbo();
                            $query = $db->getQuery(true);
                            $query
                                ->select('CONCAT(`#__gm_ceiling_projects_2463298`.`name`, \' \',`#__gm_ceiling_projects_2463298`.`country`,\' \',`#__gm_ceiling_projects_2463298`.`width` ) AS canvas')
                                ->from($db->quoteName('#__gm_ceiling_canvases', '#__gm_ceiling_projects_2463298'))
                                ->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
                            $db->setQuery($query);
                            $results = $db->loadObject();

                            if ($results) {
                                $textValue[] = $results->canvas;
                            }
                        }

                        $item->n3 = !empty($textValue) ? implode(', ', $textValue) : $item->n3;
                    }


                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);
                    $query
                        ->select('fixtures.*')
                        ->from('`#__gm_ceiling_fixtures` AS fixtures')
                        ->select('components_option.title AS component_title')
                        ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = fixtures.n13_size')
                        ->select('type.title AS type_title')
                        ->join('LEFT', '`#__gm_ceiling_type` AS type ON type.id = fixtures.n13_type')
                        ->where('fixtures.calculation_id' . ' = ' . $item->id);

                    $db->setQuery($query);
                    $item->n13 = $db->loadObjectList();

                    $query = $db->getQuery(true);
                    $query
                        ->select('pipes.*')
                        ->from('`#__gm_ceiling_pipes` AS pipes')
                        ->select('components_option.title AS component_title')
                        ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = pipes.n14_size')
                        ->where('pipes.calculation_id' . ' = ' . $item->id);

                    $db->setQuery($query);
                    $item->n14 = $db->loadObjectList();

                    $query = $db->getQuery(true);
                    $query
                        ->select('cornice.*')
                        ->from('`#__gm_ceiling_cornice` AS cornice')
                        ->select('components_option.title AS component_title')
                        ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = cornice.n15_size')
                        ->select('type.title AS type_title')
                        ->join('LEFT', '`#__gm_ceiling_type` AS type ON type.id = cornice.n15_type')
                        ->where('cornice.calculation_id' . ' = ' . $item->id);

                    $db->setQuery($query);
                    $item->n15 = $db->loadObjectList();

                    $query = $db->getQuery(true);
                    $query
                        ->select('hoods.*')
                        ->from('`#__gm_ceiling_hoods` AS hoods')
                        ->select('components_option.title AS component_title')
                        ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = hoods.n22_size')
                        ->select('type.title AS type_title')
                        ->join('LEFT', '`#__gm_ceiling_type` AS type ON type.id = hoods.n22_type')
                        ->where('hoods.calculation_id' . ' = ' . $item->id);

                    $db->setQuery($query);
                    $item->n22 = $db->loadObjectList();

                    $query = $db->getQuery(true);
                    $query
                        ->select('diffusers.*')
                        ->from('`#__gm_ceiling_diffusers` AS diffusers')
                        ->select('components_option.title AS component_title')
                        ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = diffusers.n23_size')
                        ->where('diffusers.calculation_id' . ' = ' . $item->id);

                    $db->setQuery($query);
                    $item->n23 = $db->loadObjectList();

                    $query = $db->getQuery(true);
                    $query
                        ->select('ecola.*')
                        ->from('`#__gm_ceiling_ecola` AS ecola')
                        ->select('components_option_illuminator.title AS component_title_illuminator')
                        ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option_illuminator ON components_option_illuminator.id = ecola.n26_illuminator')
                        ->select('components_option_lamp.title AS component_title_lamp')
                        ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option_lamp ON components_option_lamp.id = ecola.n26_lamp')
                        ->where('ecola.calculation_id' . ' = ' . $item->id);

                    $db->setQuery($query);
                    $item->n26 = $db->loadObjectList();

                    $query = $db->getQuery(true);
                    $query
                        ->select('profil.*')
                        ->from('`#__gm_ceiling_profil` AS profil')
                        ->select('components_option.title AS component_title')
                        ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = profil.n29_profil')
                        ->select('type.title AS type_title')
                        ->join('LEFT', '`#__gm_ceiling_type` AS type ON type.id = profil.n29_type')
                        ->where('profil.calculation_id' . ' = ' . $item->id);

                    $db->setQuery($query);
                    $item->n29 = $db->loadObjectList();

                }
            }
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function new_getProjectItems($project_id)
    {
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('#__gm_ceiling_calculations')
                ->where('project_id = '.$project_id);
            $db->setQuery($query);
            $results = $db->loadObjectList();
            return $results;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
       
    }

    public function updateComponents_sum($id)
    {
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__gm_ceiling_calculations'));
            $query->set("`components_sum` = 0")
                ->where('id = '.$id);
            //print_r((string)$query); exit;
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    //KM_CHANGED START

    public function getProjectItems($project_id)
    {
        try
        {
            //
            //		$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $this->setState('filter.state', 1);
            $this->setState('filter.project_id', $project_id);
            $items = $this->getItems();
            {

                foreach ($items as $item)
                    if (isset($item->project_id) && $item->project_id != '') {
                        if (is_object($item->project_id)) {
                            $item->project_id = \Joomla\Utilities\ArrayHelper::fromObject($item->project_id);
                        }

                        $values = (is_array($item->project_id)) ? $item->project_id : explode(',', $item->project_id);
                        $textValue = array();

                        foreach ($values as $value) {
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

                        $item->project_id = !empty($textValue) ? implode(', ', $textValue) : $item->project_id;

                    }
                if (isset($item->n1) && $item->n1 != '') {
                    if (is_object($item->n1)) {
                        $item->n1 = \Joomla\Utilities\ArrayHelper::fromObject($item->n1);
                    }

                    $values = (is_array($item->n1)) ? $item->n1 : explode(',', $item->n1);
                    $textValue = array();

                    foreach ($values as $value) {
                        $db = JFactory::getDbo();
                        $query = $db->getQuery(true);
                        $query
                            ->select('`#__gm_ceiling_projects_2463298`.`texture_title`')
                            ->from($db->quoteName('#__gm_ceiling_textures', '#__gm_ceiling_projects_2463298'))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
                        $db->setQuery($query);
                        $results = $db->loadObject();

                        if ($results) {
                            $textValue[] = $results->type_title;
                        }
                    }

                    $item->n1 = !empty($textValue) ? implode(', ', $textValue) : $item->n1;
                }
                if (isset($item->n2) && $item->n2 != '') {
                    if (is_object($item->n2)) {
                        $item->n2 = \Joomla\Utilities\ArrayHelper::fromObject($item->n2);
                    }

                    $values = (is_array($item->n2)) ? $item->n2 : explode(',', $item->n2);
                    $textValue = array();

                    foreach ($values as $value) {
                        $db = JFactory::getDbo();
                        $query = $db->getQuery(true);
                        $query
                            ->select('`#__gm_ceiling_projects_2463298`.`texture_title`')
                            ->from($db->quoteName('#__gm_ceiling_textures', '#__gm_ceiling_projects_2463298'))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
                        $db->setQuery($query);
                        $results = $db->loadObject();

                        if ($results) {
                            $textValue[] = $results->texture_title;
                        }
                    }

                    $item->n2 = !empty($textValue) ? implode(', ', $textValue) : $item->n2;
                }
                if (isset($item->n3) && $item->n3 != '') {
                    if (is_object($item->n3)) {
                        $item->n3 = \Joomla\Utilities\ArrayHelper::fromObject($item->n3);
                    }

                    $values = (is_array($item->n3)) ? $item->n3 : explode(',', $item->n3);
                    $textValue = array();

                    foreach ($values as $value) {
                        $db = JFactory::getDbo();
                        $query = $db->getQuery(true);
                        $query
                            ->select('CONCAT(`#__gm_ceiling_projects_2463298`.`name`, \' \',`#__gm_ceiling_projects_2463298`.`country`,\' \',`#__gm_ceiling_projects_2463298`.`width` )')
                            ->from($db->quoteName('#__gm_ceiling_canvases', '#__gm_ceiling_projects_2463298'))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
                        $db->setQuery($query);
                        $results = $db->loadObject();

                        if ($results) {
                            $textValue[] = $results->canvas_title;
                        }
                    }
                    $item->n3 = !empty($textValue) ? implode(', ', $textValue) : $item->n3;
                }

            }
            return $items;
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
                ->where('client.client_name =\'' . $client_id . '\'');

            $db->setQuery($query);
            $result = $db->loadObjectList();
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
                ->select(' client_contact.phone AS client_contacts')
                ->from('`#__gm_ceiling_clients_contacts` AS client_contact')
                ->where('client_contact.client_id =\'' . $client_id . '\'');

            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getProjectQuadrature($project_id = 0)
    {
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $model->setState('filter.state', 1);
            $model->setState('filter.project_id', $project_id);

            $items = $model->getItems();
            $quadrature = 0;
            if (!empty($items)) {
                foreach ($items as $item) {
                    $quadrature += $item->n4;
                }
            }

            return $quadrature;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    //инфа по проекту для почты
    public function InfoForMail($project_id) {
        try
        {
            $db = JFactory::getDbo();
    		$query = $db->getQuery(true);

    		$query->select('n5, mounting_sum')
    			->from('#__gm_ceiling_calculations')
    			->where("project_id = '$project_id'");
    		$db->setQuery($query);
    		
    		$items = $db->loadObjectList();
    		return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    //KM_CHANGED END

    /**
     * Overrides the default function to check Date fields format, identified by
     * "_dateformat" suffix, and erases the field if it's not correct.
     *
     * @return void
     */
    protected function loadFormData()
    {
        try
        {
            $app = JFactory::getApplication();
            $filters = $app->getUserState($this->context . '.filter', array());
            $error_dateformat = false;

            foreach ($filters as $key => $value) {
                if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null) {
                    $filters[$key] = '';
                    $error_dateformat = true;
                }
            }

            if ($error_dateformat) {
                $app->enqueueMessage(JText::_("COM_GM_CEILING_SEARCH_FILTER_DATE_FORMAT"), "warning");
                $app->setUserState($this->context . '.filter', $filters);
            }

            return parent::loadFormData();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
     *
     * @param   string $date Date to be checked
     *
     * @return bool
     */
    private function isValidDate($date)
    {
        try
        {
            $date = str_replace('/', '-', $date);
            return (date_create($date)) ? JFactory::getDate($date)->format("Y-m-d") : null;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function searchId()
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from('`#__gm_ceiling_calculations` AS calc')
                ->select('max(calc.id) AS id');
            $db->setQuery($query);
            $calculationId = ($db->loadObject())->id;
            return $calculationId + 1;
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

            if ($where != null) {
                $query->select(' map.id_brigade, mounters.name')
                    ->from('#__gm_ceiling_mounters as mounters')
                    ->innerJoin('#__gm_ceiling_mounters_map as map ON mounters.id = map.id_mounter')
                    ->where("map.id_brigade in ($where)");
                $db->setQuery($query);
                $items = $db->loadObjectList();
                return $items;
            }  
            
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function FindBusyMounters($date1, $date2, $dealer) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query2 = $db->getQuery(true);
            $query3 = $db->getQuery(true);

            $query2->select("SUM(calculations.n5)")
                ->from("#__gm_ceiling_calculations AS calculations")
                ->where("calculations.project_id = projects.id");

            $query->select("pm.mounter_id as project_mounter, pm.date_time as project_mounting_date, projects.project_info, ($query2) as n5")
                ->from('#__gm_ceiling_projects as projects')
                ->innerJoin("`#__gm_ceiling_projects_mounts` as pm on projects.id = pm.project_id")
                ->innerJoin("#__gm_ceiling_clients as clients ON projects.client_id = clients.id")
                ->where("pm.date_time  BETWEEN '$date1 00:00:00' AND '$date2 23:59:59' and clients.dealer_id = '$dealer'")
                ->order('projects.id');
            $db->setQuery($query);
            $items = $db->loadObjectList();

            $query3->select('day_off.id_user, day_off.date_from, day_off.date_to')
                ->from('#__gm_ceiling_day_off as day_off')
                ->LeftJoin("#__user_usergroup_map as users ON day_off.id_user = users.user_id")
                ->where("day_off.date_from between '$date1 00:00:00' and '$date2 23:59:59' and group_id = 11");
            $db->setQuery($query3);
            $items3 = $db->loadObject();
            
            // объединение с выходным днем
            $index = 0;
            $was_break = false;
            //поиск индекса для вставки и замена даты на просто время
            for ($i=0; $i < count($items); $i++) {
                if (strtotime($items[$i]->project_mounting_date) >= strtotime($items3->date_from)) {
                    $index = $i;
                    $was_break = true;
                    break;
                }
            }
            ($index == 0 && !$was_break) ? $index = count($items) : 0;
            for ($i=0; $i < count($items); $i++) {
                $items[$i]->project_mounting_day_off = "";
            }

            //создание нового массива
            if (!empty($items3)) {
                $day = array(
                    'project_mounter'=>$items3->id_user,
                    'project_mounting_date'=>$items3->date_from,
                    'project_info'=>"Выходные часы",
                    'n5'=>NULL,
                    'project_mounting_day_off'=>$items3->date_to
                );
                $day = array((object)$day);
                array_splice($items,$index,0,$day);
            }
            
    		return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function FindBusyMounters2($date1, $date2, $dealer) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query2 = $db->getQuery(true);
            $query3 = $db->getQuery(true);

            $query2->select("SUM(calculations.n5)")
                ->from("#__gm_ceiling_calculations AS calculations")
                ->where("calculations.project_id = projects.id");

            $query->select("pm.mounter_id as project_mounter, pm.date_time as project_mounting_date, projects.project_info, ($query2) as n5")
                ->from('#__gm_ceiling_projects as projects')
                ->innerJoin("`#__gm_ceiling_projects_mounts` as pm on projects.id = pm.project_id")
                ->innerJoin("#__gm_ceiling_clients as clients ON projects.client_id = clients.id")
                ->where("pm.date_time BETWEEN '$date1 00:00:00' AND '$date2 23:59:59' and clients.dealer_id = '$dealer'")
                ->order('projects.id');
            $db->setQuery($query);
            $items = $db->loadObjectList();

            $query3->select('day_off.id_user, day_off.date_from, day_off.date_to')
                ->from('#__gm_ceiling_day_off as day_off')
                ->LeftJoin("#__user_usergroup_map as users ON day_off.id_user = users.user_id")
                ->where("day_off.date_from between '$date1 00:00:00' and '$date2 23:59:59' and group_id = 11");
            $db->setQuery($query3);
            $items3 = $db->loadObjectList();
            
            // объединение с выходным днем
            for ($i=0; $i < count($items); $i++) {
                $items[$i]->project_mounting_day_off = "";
            }

            //создание нового массива
            if (!empty($items3)) {
                foreach ($items3 as $value) {
                    $day = array(
                        'project_mounter'=>$value->id_user,
                        'project_mounting_date'=>$value->date_from,
                        'project_info'=>"Выходные часы",
                        'n5'=>NULL,
                        'project_mounting_day_off'=>$value->date_to
                    );
                    $day = (object)$day;
                    array_push($items, $day);
                }
            }
            
    		return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function FindAllGauger($id, $gauger) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('users.id, users.name')
                ->from('#__users as users')
                ->innerJoin('#__user_usergroup_map as usergroup_map ON users.id = usergroup_map.user_id')
                ->where("users.dealer_id = '$id' AND usergroup_map.group_id = '$gauger'");
            $db->setQuery($query);

            $items = $db->loadObjectList();
    		return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function FindBusyGauger($date1, $date2, $dealer) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query2 = $db->getQuery(true);
            
            if ($dealer == 1) {
                $who = 1;
                $who2 = 22;
            } else {
                $who = 0;
                $who2 = 21;
            }

            $query->select('projects.project_info, projects.project_calculation_date, projects.project_calculator')
                ->from('#__gm_ceiling_projects as projects')
                ->innerJoin("#__gm_ceiling_clients as clients ON projects.client_id = clients.id")
                ->where("projects.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:59' and projects.who_calculate = '$who' and clients.dealer_id = '$dealer' and projects.project_status NOT IN (2, 3, 9, 15, 22)");
            $db->setQuery($query);
            $items = $db->loadObjectList();

            $query2->select('day_off.id_user, day_off.date_from, day_off.date_to')
                ->from('#__gm_ceiling_day_off as day_off')
                ->LeftJoin("#__user_usergroup_map as map ON day_off.id_user = map.user_id")
                ->LeftJoin("#__users as users ON day_off.id_user = users.id")
                ->where("day_off.date_from between '$date1 00:00:00' and '$date2 23:59:59' and map.group_id = '$who2' and users.dealer_id = '$dealer'" );
            $db->setQuery($query2);
            $items2 = $db->loadObject();
            
            // объединение с выходным днем
            $index = 0;
            $was_break = false;
            //поиск индекса для вставки и замена даты на просто время
            for ($i=0; $i < count($items); $i++) {
                if (strtotime($items[$i]->project_mounting_date) >= strtotime($items2->date_from)) {
                    $index = $i;
                    $was_break = true;
                    break;
                }
            }
            ($index == 0 && !$was_break) ? $index = count($items) : 0;
            for ($i=0; $i < count($items); $i++) {
                $items[$i]->project_calculation_day_off = "";
            }
            //создание нового массива
            if (!empty($items2)) {
                $day = array(
                    'project_calculator'=>$items2->id_user,
                    'project_calculation_date'=>$items2->date_from,
                    'project_info'=>"Выходные часы",
                    'project_calculation_day_off'=>$items2->date_to
                );
                $day = array((object)$day);
                array_splice($items,$index,0,$day);
            }

    		return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function FindBusyGauger2($date1, $date2, $dealer) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query2 = $db->getQuery(true);
            
            if ($dealer == 1) {
                $who = 1;
                $who2 = 22;
            } else {
                $who = 0;
                $who2 = 21;
            }

            $query->select('projects.project_info, projects.project_calculation_date, projects.project_calculator')
                ->from('#__gm_ceiling_projects as projects')
                ->innerJoin("#__gm_ceiling_clients as clients ON projects.client_id = clients.id")
                ->where("projects.project_calculation_date BETWEEN '$date1 00:00:00' AND '$date2 23:59:59' and projects.who_calculate = '$who' and clients.dealer_id = '$dealer' and projects.project_status NOT IN (2, 3, 9, 15, 20, 21, 22)");
            $db->setQuery($query);
            $items = $db->loadObjectList();

            $query2->select('day_off.id_user, day_off.date_from, day_off.date_to')
                ->from('#__gm_ceiling_day_off as day_off')
                ->LeftJoin("#__user_usergroup_map as map ON day_off.id_user = map.user_id")
                ->LeftJoin("#__users as users ON day_off.id_user = users.id")
                ->where("day_off.date_from between '$date1 00:00:00' and '$date2 23:59:59' and map.group_id = '$who2' and users.dealer_id = '$dealer'" );
            $db->setQuery($query2);
            $items2 = $db->loadObjectList();
            
            // объединение с выходным днем
            for ($i=0; $i < count($items); $i++) {
                $items[$i]->project_calculation_day_off = "";
            }
            //создание нового массива
            if (!empty($items2)) {
                foreach ($items2 as $value) {
                    $day = array(
                        'project_calculator'=>$value->id_user,
                        'project_calculation_date'=>$value->date_from,
                        'project_info'=>"Выходные часы",
                        'project_calculation_day_off'=>$value->date_to
                    );
                    $day = (object)$day;
                    array_push($items, $day);
                }
            }

    		return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delete($id) {
        try
        {
         $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__gm_ceiling_calculations'));
            $query->where("id = $id");
            $db->setQuery($query);
            $db->execute();

           /* $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__gm_ceiling_fixtures'));
            $query->where('calculation_id = ' .$id);
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__gm_ceiling_pipes'));
            $query->where('calculation_id = ' . $id);
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__gm_ceiling_cornice'));
            $query->where('calculation_id = ' . $id);
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__gm_ceiling_hoods'));
            $query->where('calculation_id = ' . $id);
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__gm_ceiling_diffusers'));
            $query->where('calculation_id = ' . $id);
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__gm_ceiling_ecola'));
            $query->where('calculation_id = ' . $id);
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__gm_ceiling_profil'));
            $query->where('calculation_id = ' . $id);
            $db->setQuery($query);
            $db->execute();*/

            return 1;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function update_cut_data($id, $cut_data, $width)
    {
        try
        {
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query->select("`n2`,`n3`")
            ->from('#__gm_ceiling_calculations')
            ->where('id = ' . $id);    
            $db->setQuery($query);
            $item = $db->loadObject();

            $query = $db->getQuery(true);
            $query->select("*")
            ->from('#__gm_ceiling_canvases')
            ->where('id = ' . $item->n3);    
            $db->setQuery($query);
            $item_canvas = $db->loadObject();
            

            $query = $db->getQuery(true);
            $query->select("id")
            ->from('#__gm_ceiling_canvases')
            ->where('texture_id = '. $item->n2 . ' and name = '.  $db->quote($item_canvas->name) .' and width = '.  $db->quote($width));    
            if(!empty($item_canvas->color_id))  $query->where('color_id = '. $item_canvas->color_id); 
            $db->setQuery($query);
            $new_n3  = $db->loadObject();

            //throw new Exception($new_n3->id);
            /* Старое */
            $cut_data = $db->escape($cut_data);
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__gm_ceiling_calculations'));
            $query->set("`cut_data` = '$cut_data'");
            $query->set("`n3` = $new_n3->id");
            $query->where('id = ' . $id);
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
