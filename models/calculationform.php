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

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

use Joomla\Utilities\ArrayHelper;

/**
 * Gm_ceiling model.
 *
 * @since  1.6
 */
class Gm_ceilingModelCalculationForm extends JModelForm
{
    private $item = null;

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return void
     *
     * @since  1.6
     */
    protected function populateState()
    {
        try
        {
            $app = JFactory::getApplication('com_gm_ceiling');

            // Load state from the request userState on edit or from the passed variable on default
            if (JFactory::getApplication()->input->get('layout') == 'edit') {
                $id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.calculation.id');
            } else {
                $id = JFactory::getApplication()->input->get('id');
                JFactory::getApplication()->setUserState('com_gm_ceiling.edit.calculation.id', $id);
            }

            $this->setState('calculation.id', $id);

            // Load the parameters.
            $params = $app->getParams();
            $params_array = $params->toArray();

            if (isset($params_array['item_id'])) {
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
     * Method to get an ojbect.
     *
     * @param   integer $id The id of the object to get.
     *
     * @return Object|boolean Object on success, false on failure.
     *
     * @throws Exception
     */
    public function &getData($id = null)
    {
        try
        {
            if ($this->item === null) {
                $this->item = false;

                if (empty($id)) {
                    $id = $this->getState('calculation.id');
                }

                // Get a level row instance.
                $table = $this->getTable();

                // Attempt to load the row.
                if ($table !== false && $table->load($id)) {
                    $user = JFactory::getUser();
                    $id = $table->id;
                    $canEdit = $user->authorise('core.edit', 'com_gm_ceiling') || $user->authorise('core.create', 'com_gm_ceiling');

                    if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')) {
                        $canEdit = $user->id == $table->created_by;
                    }

                    if (!$canEdit) {
                        //throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 500);
                    }

                    // Check published state.
                    if ($published = $this->getState('filter.published')) {
                        if ($table->state != $published) {
                            return $this->item;
                        }
                    }

                    // Convert the JTable to a clean JObject.
                    $properties = $table->getProperties(1);
                    $this->item = ArrayHelper::toObject($properties, 'JObject');
                }
            }


            //$this->item->types = $this->types();
            if ($this->item->id && !empty($this->item->n3)) {
                $this->item->n13 = $this->n13_load($this->item->id);
                $this->item->n14 = $this->n14_load($this->item->id);
                $this->item->n14_all = $this->getListBypass();
                $this->item->n15 = $this->n15_load($this->item->id);
                $this->item->n15_all = $this->getListCornice();
                $this->item->n22 = $this->n22_load($this->item->id);
                $this->item->n23 = $this->n23_load($this->item->id);
                $this->item->n23_all = $this->getListDiffuz();
                $this->item->n26 = $this->n26_load($this->item->id);
                $this->item->n26_all = $this->getListEcola();
                $this->item->n26_lamp = $this->getListEcolaLamp();
                $this->item->n29 = $this->n29_load($this->item->id);
                $this->item->n45 = $this->n45_load($this->item->id);
                $this->item->n29_all = $this->getListProfil();
                $this->item->n19 = $this->n19_load($this->item->id);
            }

            return $this->item;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function types()
    {
        try
        {
            $db = JFactory::getDbo();

            $query = $db->getQuery(true);
            $query->select('type.title AS title, type.id AS id')->from('`#__gm_ceiling_type` AS type')->where('type.parent IS NULL');
            $db->setQuery($query);
            $temp = $db->loadObjectList();
            $result = [];
            foreach ($temp as $item) $result[$item->id] = $item;

            foreach ($result as $key => $item) {

                $query = $db->getQuery(true);
                $query->select('type.title AS title, type.id AS id')->from('`#__gm_ceiling_type` AS type')->where('type.parent = ' . $item->id);
                $db->setQuery($query);
                $temp = $db->loadObjectList();
                $types = [];
                foreach ($temp as $item1) $types[$item1->id] = $item;

                foreach ($types as $key1 => $value1) {
                    $query = $db->getQuery(true);
                    $query->select('options.component_id AS components_option, options.default_comp_option_id AS default_option')
                        ->from('`#__gm_ceiling_type_option` AS options')
                        ->where('options.type_id = ' . $value1->id);
                    $db->setQuery($query);
                    $options = $db->loadObjectList();

                    foreach ($options as $key2 => $value2) {
                        if (!empty($value2->components_option)) {
                            $query = $db->getQuery(true);
                            $query->select('options.title AS title, options.id AS id')
                                ->from('`#__gm_ceiling_components` AS components')
                                ->join('RIGHT', '`#__gm_ceiling_components_option` AS options ON options.component_id = components.id')
                                ->where('components.id = ' . $value2->components_option);
                            $db->setQuery($query);
                            $options[$key2]->components_option = $db->loadObjectList();
                        }
                    }

                    foreach ($options as $key2 => $value2) {
                        if (!empty($value2->default_option)) {
                            $query = $db->getQuery(true);
                            $query->select('options.title AS title, options.id AS id')
                                ->from('`#__gm_ceiling_components` AS components')
                                ->join('RIGHT', '`#__gm_ceiling_components_option` AS options ON options.component_id = components.id')
                                ->where('options.id = ' . $value2->default_option);
                            $db->setQuery($query);
                            $options[$key2]->default_option = $db->loadObjectList();
                        }
                    }

                    $types[$key1]->options = $options;
                }

                $result[$key]->id = $types;
            }
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function component_option($component_id)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('components_option.*')
                ->from('`#__gm_ceiling_components_option` AS components_option')
                ->where('components_option.component_id' . ' = ' . $component_id);

            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function components_list_n13_n22($type, $ring)
    {
        try
        {
            $list = [];

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('options.component_id AS com_id, options.default_comp_option_id AS def, options.count AS count')
                ->from('`#__gm_ceiling_type` AS type')
                ->join('LEFT', '`#__gm_ceiling_type_option` AS options ON options.type_id = type.id')
                ->where('type.id = ' . $type);

            $db->setQuery($query);
            $result = $db->loadObjectList();
            
            foreach ($result AS $key => $value) {
                if ($key == 0) {
                    $list[$key] = ['id' => $ring, 'count' => $value->count];
                    $query = $db->getQuery(true);
                    $query->select('com_opt.id AS id, com_opt.title AS title, com_opt.price AS price')
                        ->from('`#__gm_ceiling_components_option` AS com_opt')
                        ->where('com_opt.id = ' . $ring);
                    $db->setQuery($query);
                    $ring = ($db->loadObjectList())[0]->title;
                } else if ($key == 1) {
                    $query = $db->getQuery(true);
                    $query->select('com_opt.id AS id, com_opt.title AS title,  com_opt.price AS price')
                        ->from('`#__gm_ceiling_components_option` AS com_opt')
                        ->where('com_opt.component_id = ' . $value->com_id)
                        ->order('com_opt.price');
                    $db->setQuery($query);
                    $com_opt = $db->loadObjectList();
                    $ring = explode("*", $ring);
                    $ring_size = $ring[1];
                    if (empty($ring[1]) && !empty($ring[0])){
                        $ring_size = $ring[0];
                    }
                    foreach ($com_opt as $item) {
                        $rings = explode("-", $item->title);
                        if (floatval($ring_size)>=  floatval($rings[0]) && floatval($ring_size) <= floatval($rings[1])) {
                            $list[$key] = ['id' => $item->id, 'count' => $value->count];
                            break;
                        }
                    }
                    if (empty($list[$key])) $list[$key] = ['id' => $value->def, 'count' => $value->count];
                } else $list[$key] = ['id' => $value->def, 'count' => $value->count];
            }
            return $list;
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
            /*
                    foreach ($result AS $key => $value)
                    {
                        $type_id = $value->type_id;
                        $query = $db->getQuery(true);
                        $query -> select('*, components.id AS comp_id')
                            ->from('`#__gm_ceiling_type` AS type')
                            ->join('LEFT', '`#__gm_ceiling_type_option` AS options ON options.type_id = type.id')
                            ->join('LEFT', '`#__gm_ceiling_components` AS components ON options.component_id = components.id')
                            ->join('LEFT', '`#__gm_ceiling_components_option` AS com_option ON options.default_comp_option_id = com_option.id')
                            ->where('type.id = '.$type_id);
                        $db->setQuery($query);
                        $result[$key]->type_options = $db->loadObjectList();

                        foreach ($result[$key]->type_options AS $optKey => $optValue) if ($optValue->comp_id)
                        {
                            $query = $db->getQuery(true);
                            $query -> select('*')
                                ->from('`#__gm_ceiling_components_option`')
                                ->where('component_id = '.$optValue->comp_id);
                            $db->setQuery($query);
                            $result[$key]->option[$optKey]->comp_options = $db->loadObjectList();
                        }
                    }*/
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function n13($id)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('fixtures.*')
                ->from('`#__gm_ceiling_fixtures` AS fixtures')
                ->where('fixtures.calculation_id = ' . $id);


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

    public function getListBypass()
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('components_option.*')
                ->from('`#__gm_ceiling_components_option` AS components_option')
                ->where('components_option.component_id = 24');


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
                ->select('type.title AS type_title, type.id AS type_id')
                ->join('LEFT', '`#__gm_ceiling_type` AS type ON type.id = cornice.n15_type')
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

    public function getListCornice()
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('components_option.*')
                ->from('`#__gm_ceiling_components_option` AS components_option')
                ->where('components_option.component_id = 51');


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


    public function n22($id)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('hoods.*')
                ->from('`#__gm_ceiling_hoods` AS hoods')
                ->where('(hoods.n22_type = 7 or hoods.n22_type = 8) and  hoods.calculation_id = ' . $id);

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

    public function getListDiffuz()
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('components_option.*')
                ->from('`#__gm_ceiling_components_option` AS components_option')
                ->where('components_option.component_id = 22');


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
    public function n19_load($id)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('wires.*')
                ->select('components_option.title AS wire_title')
                ->from('`#__gm_ceiling_wires` AS wires')
                ->leftJoin('`#__gm_ceiling_components_option` AS components_option ON components_option.id = wires.wire_id')
                ->where('wires.calc_id = ' . $id);

            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getListEcola()
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('components_option.*')
                ->from('`#__gm_ceiling_components_option` AS components_option')
                ->where('components_option.component_id = 19 AND ( components_option.title LIKE(\'%Эcola%\') OR components_option.title LIKE(\'%Экола%\') )');


            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getListEcolaLamp()
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('components_option.*')
                ->from('`#__gm_ceiling_components_option` AS components_option')
                ->where('components_option.component_id = 20 AND ( components_option.title LIKE(\'%Эcola%\') OR components_option.title LIKE(\'%Экола%\') )');


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

    public function n45_load($id)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('light.count as n45_count,light.type as n45_type,components_option.title AS component_title')
                ->from('`#__gm_ceiling_calc_light_lines` AS light')

                ->join('LEFT', '`#__gm_ceiling_components_option` AS components_option ON components_option.id = light.type')
                ->where('light.calc_id' . ' = ' . $id);

            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getListProfil()
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('components_option.*')
                ->from('`#__gm_ceiling_components_option` AS components_option')
                ->where('components_option.component_id = 14');


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
     * Method to get the table
     *
     * @param   string $type Name of the JTable class
     * @param   string $prefix Optional prefix for the table class name
     * @param   array $config Optional configuration array for JTable object
     *
     * @return  JTable|boolean JTable if found, boolean false on failure
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
     * Get an item by alias
     *
     * @param   string $alias Alias string
     *
     * @return int Element id
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
     * @param   integer $id The id of the row to check out.
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
            $id = (!empty($id)) ? $id : (int)$this->getState('calculation.id');

            if ($id) {
                // Initialise the table
                $table = $this->getTable();

                // Attempt to check the row in.
                if (method_exists($table, 'checkin')) {
                    if (!$table->checkin($id)) {
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
     * @param   integer $id The id of the row to check out.
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
            $id = (!empty($id)) ? $id : (int)$this->getState('calculation.id');

            if ($id) {
                // Initialise the table
                $table = $this->getTable();

                // Get the current user object.
                $user = JFactory::getUser();

                // Attempt to check the row out.
                if (method_exists($table, 'checkout')) {
                    if (!$table->checkout($user->get('id'), $id)) {
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
     * Method to get the profile form.
     *
     * The base form is loaded from XML
     *
     * @param   array $data An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return    JForm    A JForm object on success, false on failure
     *
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        try
        {
            // Get the form.
            $form = $this->loadForm('com_gm_ceiling.calculation', 'calculationform', array(
                    'control' => 'jform',
                    'load_data' => $loadData
                )
            );

            if (empty($form)) {
                return false;
            }

            return $form;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     *
     * @since    1.6
     */
    protected function loadFormData()
    {
        try
        {
            $data = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.calculation.data', array());

            if (empty($data)) {
                $data = $this->getData();
            }


            return $data;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to save the form data.
     *
     * @param   array $data The form data
     *
     * @return bool
     *
     * @throws Exception
     * @since 1.6
     */
    public function add_client($data)
    {
        try
        {
            $date_created = date("Y-m-d");
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query
                ->select(' users.id AS id')
                ->from('`#__users` AS users')
                ->where('users.email =' . $db->quote($data['send_email']));
            $db->setQuery($query);
            $id_user = $db->loadObject();
            if (empty($id_user->id)) {

                $query = $db->getQuery(true);
                $query
                    ->select(' dop_contacts.client_id AS client_id')
                    ->from('`#__gm_ceiling_clients_dop_contacts` AS dop_contacts')
                    ->where('dop_contacts.contact =' . $db->quote($data['send_email']));
                $db->setQuery($query);
                $id = $db->loadObject()->client_id;
                if (empty($id)) {
                    $query = $db->getQuery(true);
                    $query
                        ->select(' client.id AS id')
                        ->from('`#__gm_ceiling_clients` AS client')
                        ->where('client.client_name =' . $db->quote($data['send_email']));
                    $db->setQuery($query);
                    $id = $db->loadObject()->id;
                }
            }


            if (empty($id) && empty($id_user->id)) {
                $query = $db->getQuery(true);
                $columns = array('client_name', 'type_id', 'dealer_id', 'created');
                $query
                    ->insert($db->quoteName('#__gm_ceiling_clients'))
                    ->columns($db->quoteName($columns))
                    ->values(
                        $db->quote($data['send_email']) . ', '
                        . '1' . ', '
                        . '1' . ', '
                        . $db->quote($date_created));
                $db->setQuery($query);
                $db->execute();
                $query = $db->getQuery(true);
                $query
                    ->select(' client.id AS id')
                    ->from('`#__gm_ceiling_clients` AS client')
                    ->where('client.client_name =' . $db->quote($data['send_email']));
                $db->setQuery($query);
                $id = $db->loadObject()->id;

                $query = $db->getQuery(true);
                $columns = array('client_id', 'type_id', 'contact');
                $query
                    ->insert($db->quoteName('#__gm_ceiling_clients_dop_contacts'))
                    ->columns($db->quoteName($columns))
                    ->values(
                        $id . ', '
                        . '1' . ', '
                        . $db->quote($data['send_email']));
                $db->setQuery($query);
                $db->execute();
            }
            if(!empty($id)) {
                $query = $db->getQuery(true);
                $columns = array('client_id', 'date_time', 'text');
                $query
                    ->insert($db->quoteName('#__gm_ceiling_client_history'))
                    ->columns($db->quoteName($columns))
                    ->values("$id , NOW(), 'Клиент запросил смету на почту'");
                $db->setQuery($query);
                $db->execute();
            }
            return $id;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function add_project($data, $id_client)
    {
        try
        {
            $date_created = date("Y-m-d");
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query
                ->select(' project.id AS project_id')
                ->from('`#__gm_ceiling_projects` AS project')
                ->where('project.client_id =' . $id_client);
            $db->setQuery($query);
            $id = $db->loadObject();
            if (empty($id->project_id)) {

                $query = $db->getQuery(true);
                $columns = array('state', 'checked_out_time', 'created_by', 'modified_by', 'client_id', 'project_status'
                , 'project_verdict', 'created', 'gm_canvases_margin', 'gm_components_margin', 'gm_mounting_margin',
                    'dealer_canvases_margin', 'dealer_components_margin', 'dealer_mounting_margin', 'project_discount', 'api_phone_id');
                $query
                    ->insert($db->quoteName('#__gm_ceiling_projects'))
                    ->columns($db->quoteName($columns))
                    ->values(
                        '1' . ', '
                        . $db->quote($date_created) . ', '
                        . $data['dealer_id'] . ', '
                        . $data['dealer_id'] . ', '
                        . $id_client . ', '
                        . '0' . ', '
                        . '0' . ', '
                        . $db->quote($date_created) . ', '
                        . '0' . ', '
                        . '0' . ', '
                        . '0' . ', '
                        . '50' . ', '
                        . '50' . ', '
                        . '50' . ', '
                        . '20' . ', '
                        . $data['rek']);
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query
                    ->select(' project.id AS project_id')
                    ->from('`#__gm_ceiling_projects` AS project')
                    ->where('project.client_id =' . $id_client);
                $db->setQuery($query);
                $id = $db->loadObject();
            }
            return $id->project_id;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function save($data, $del_flag)
    {
        try
        {
            /*n3,n4,n5,n5_shrink,n9,n10,n31,shrink_percent,calc_data,cut_data,original_sketch,offctu_square
            сохраняются на контроллере skecth (controllers/sketch.php)*/
            $date_created = date("Y-m-d H:i:s");
            $oldColumns = ["n6","n7","n8","n11","n12","n16","n17","n18","n20","n20_1","n21","n22_1","n24","n27","n28","remove_n28","n13_inside_ring","n13_double",
                "n30","n32","n33","n33_2","n34","n34_2","n35","n36","n37","n38","n39","n40","n41","n42","n43","n44","n46","niche","height","components_sum","canvases_sum","mounting_sum","dealer_components_sum",
                "dealer_canvases_sum","dop_krepezh","extra_components","extra_mounting","components_stock","need_mount","need_metiz","need_cuts",
                "color","discount","scaffolding","n12_cancel_install"];
            $columns = [
                "calculation_title","details","manager_note"
            ];

            if(isset($data['n3'])){
                $columns = array_merge($columns,$oldColumns);
            }
            $calculationId = $data['id'];

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_ceiling_calculations`');
            foreach ($columns as $column){
                $value = "'".$data[$column]."'" ;
                $query->set("$column = $value");
            }
            $query->where('id = ' . $data['id']);
            $db->setQuery($query);
            $db->execute();
            if(is_string($data['n13'])){
                $n13 = json_decode($data['n13']);
            }
            if(is_string($data['n19'])){
                $n19 = json_decode($data['n19']);
            }
            if(is_string($data['n26'])){
                $n26 = json_decode($data['n26']);
            }
            if(is_string($data['n22'])){
                $n22 = json_decode($data['n22']);
            }
            if(is_string($data['n14'])){
                $n14 = json_decode($data['n14']);
            }
            if(is_string($data['n23'])){
                $n23 = json_decode($data['n23']);
            }
            if(is_string($data['n15'])){
                $n15 = json_decode($data['n15']);
            }
            if(is_string($data['n29'])){
                $n29 = json_decode($data['n29']);
            }
            if(is_string($data['n45'])){
                $n45 = json_decode($data['n45']);
            }


            if (!empty($n29)) {
                foreach ($n29 as $key => $value) $n29[$key][0] = str_replace(",",".", $value[0]);
            }
            if ($del_flag) {
                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__gm_ceiling_fixtures'));
                $query->where('calculation_id = ' . $data['id']);
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__gm_ceiling_pipes'));
                $query->where('calculation_id = ' . $data['id']);
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__gm_ceiling_cornice'));
                $query->where('calculation_id = ' . $data['id']);
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__gm_ceiling_hoods'));
                $query->where('calculation_id = ' . $data['id']);
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__gm_ceiling_diffusers'));
                $query->where('calculation_id = ' . $data['id']);
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__gm_ceiling_ecola'));
                $query->where('calculation_id = ' . $data['id']);
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__gm_ceiling_profil'));
                $query->where('calculation_id = ' . $data['id']);
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__gm_ceiling_wires'));
                $query->where('calc_id = ' . $data['id']);
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__gm_ceiling_calc_light_lines'));
                $query->where('calc_id = ' . $data['id']);
                $db->setQuery($query);
                $db->execute();
            }

            if (!empty($n13)) {
                $query = $db->getQuery(true);
                $query
                    ->insert('`#__gm_ceiling_fixtures`')
                    ->columns('`calculation_id`, `n13_count`, `n13_type`, `n13_size`');

                foreach ($n13 as $value) {
                    if (!empty($value[0]))
                        $query->values($calculationId . ', ' . $value[0] . ', ' . $value[1] . ', ' . $value[2]);
                }
                $db->setQuery($query);
                $db->execute();
            }

            if (!empty($n14)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_pipes`')
                    ->columns('calculation_id, n14_count, n14_size');
                foreach ($n14 as $value) {
                    if (!empty($value[0]))
                        $query->values($data['id'] . ', ' . $value[0] . ', ' . $value[1]);
                }
                $db->setQuery($query);
                $db->execute();
            }
            if (!empty($n15)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_cornice`')
                    ->columns('calculation_id, n15_count, n15_type, n15_size');
                foreach ($n15 as $value) {
                    if (!empty($value[0]))
                        $query->values($data['id'] . ', ' . $value[0] . ', ' . $value[1] . ', ' . $value[2]);
                }
                $db->setQuery($query);
                $db->execute();
            }

            if(!empty($n19)){
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_wires`')
                    ->columns('calc_id, wire_id, count');
                foreach ($n19 as $value) {
                    if (!empty($value[1])&&!empty($value[0]))
                        $query->values($data['id'] . ', ' . $value[1] . ', ' . $value[0]);
                }
                $db->setQuery($query);
                $db->execute();
            }
            if (!empty($n22)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_hoods`')
                    ->columns('calculation_id, n22_count, n22_type, n22_size');
                foreach ($n22 as $value) {
                    if (!empty($value[0]))
                        $query->values($data['id'] . ', ' . $value[0] . ', ' . $value[1] . ', ' . $value[2]);
                }
                $db->setQuery($query);
                $db->execute();
            }
            if (!empty($n23)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_diffusers`')
                    ->columns('calculation_id, n23_count, n23_size');
                foreach ($n23 as $value) {
                    if (!empty($value[0]))
                        $query->values($data['id'] . ', ' . $value[0] . ', ' . $value[1]);
                }
                $db->setQuery($query);
                $db->execute();
            }
            if (!empty($n26)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_ecola`')
                    ->columns('calculation_id, n26_count, n26_illuminator, n26_lamp');
                foreach ($n26 as $value) {
                    if (!empty($value[0]))
                        $query->values($data['id'] . ', ' . $value[0] . ', ' . $value[1] . ', ' . $value[2]);
                }
                $db->setQuery($query);
                $db->execute();
            }
            if (!empty($n29)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_profil`')
                    ->columns('calculation_id, n29_count, n29_type');
                foreach ($n29 as $value) {
                    if (!empty($value[0]))
                        $query->values($data['id'] . ', ' . $value[0] . ', ' . $value[1] );
                }
                $db->setQuery($query);
                $db->execute();
            }
            if (!empty($n45)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_calc_light_lines`')
                    ->columns('calc_id, count, type');
                foreach ($n45 as $value) {
                    if (!empty($value[0]))
                        $query->values($data['id'] . ', ' . $value[0] . ', ' . $value[1] );
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
    function updateCanvasSum($calcId,$canvases_sum){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_ceiling_calculations`')
                ->set("canvases_sum = $canvases_sum")
                ->where("id = $calcId");
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /**
     * Method to delete data
     *
     * @param   array $data Data to be deleted
     *
     * @return bool|int If success returns the id of the deleted item, if not false
     *
     * @throws Exception
     */
    public function delete($data)
    {
        try
        {
            $id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('calculation.id');

            if (JFactory::getUser()->authorise('core.delete', 'com_gm_ceiling') !== true) {
                throw new Exception(403, JText::_('JERROR_ALERTNOAUTHOR'));
            }

            $table = $this->getTable();

            if ($table->delete($data['id']) === true) {
                return $id;
            } else {
                return false;
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Check if data can be saved
     *
     * @return bool
     */
    public function getCanSave()
    {
        try
        {
            $table = $this->getTable();

            return $table !== false;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getFields($dealer_id) {
        try {
            if (empty($dealer_id)) {
                throw new Exception('Empty dealer_id!'); 
            }
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`id`,`title`,`order`')
                ->from('`rgzbn_gm_ceiling_categories_main_groups`')
                ->where("`dealer_id` = $dealer_id")
                ->order('`order`');
            $db->setQuery($query);
            $result = $db->loadObjectList();
            $ids = '';
            $last_key = count($result) - 1;
            foreach ($result as $key => $category) {
                if ($key !== $last_key) {
                    $ids .= $category->id.',';
                } else {
                    $ids .= $category->id;
                }
                $result[$key]->main_groups = [];
            }

            $query = $db->getQuery(true);
            $query->select('`id`, `title`, `order`,`category_id`');
            $query->from('`#__gm_ceiling_fields_main_groups`');
            $query->where("`dealer_id` = $dealer_id and category_id IN ($ids)");
            $query->order('`order`');
            $db->setQuery($query);
            $main_groups = $db->loadObjectList('id');

            $ids = implode(',',array_keys($main_groups));
            foreach ($main_groups as $key => $main_group) {
                $main_group->groups = [];
                foreach ($result as $key2 => $category) {
                    if($category->id == $main_group->category_id){
                        $result[$key2]->main_groups[] = $main_group;
                    }
                }
            }

            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_fields_groups`');
            $query->where("`main_group_id` in ($ids)");
            $query->order('`order`');
            $db->setQuery($query);
            $groups = $db->loadObjectList();

            $ids = '';
            $last_key = count($groups) - 1;
            foreach ($groups as $key => $group) {
                if ($key !== $last_key) {
                    $ids .= $group->id.',';
                } else {
                    $ids .= $group->id;
                }
                $group->fields = [];
                foreach ($result as $key2 => $category) {
                    foreach ($category->main_groups as $key3 => $main_group) {
                        if ($main_group->id === $group->main_group_id) {
                            $result[$key2]->main_groups[$key3]->groups[] = $group;
                            break;
                        }
                    }
                }

            }

            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_fields`');
            $query->where("`group_id` in ($ids)");
            $query->order('`order`');
            $db->setQuery($query);
            $fields = $db->loadObjectList();

            $ids = '';
            $categories = '';
            $last_key = count($fields) - 1;
            foreach ($fields as $key => $field) {
                if (!empty($field->goods_category_id)) {
                    $categories .=  $field->goods_category_id.',';
                }
                if ($key !== $last_key) {
                    $ids .= $field->id.',';
                } else {
                    $ids .= $field->id;
                }
                $field->goods = [];
                $field->jobs = [];
                foreach ($result as $key1 => $category){
                    foreach ($category->main_groups as $key2 => $main_group) {
                        foreach ($main_group->groups as $key3 => $group) {
                            if ($group->id === $field->group_id) {
                                $result[$key1]->main_groups[$key2]->groups[$key3]->fields[] = $field;
                                break 2;
                            }
                        }
                    }
                }
            }

            $categories = substr($categories, 0, -1);

            $query = $db->getQuery(true);
            $query->select('`g`.`id`, `g`.`name`, `g`.`category_id`, `g`.`color`, `g`.`hex`,`g`.`is_default`');
            $query->select('CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"job_id":"\',`jfg`.`child_job_id`,\'","count":"\',`jfg`.`count`,\'"}\') SEPARATOR \',\'),\']\') AS jobs');
            $query->select('CONCAT(\'[\',GROUP_CONCAT(DISTINCT CONCAT(\'{"id":"\',`sjm`.`job_id`,\'","name":"\',j.name,\'","count":"\',IFNULL(`sjm`.`count`,0),\'"}\') SEPARATOR \',\'),\']\') AS spec_jobs');
            $query->from('`#__goods_components` as `g`');
            $query->leftJoin('`rgzbn_gm_ceiling_jobs_from_goods_map` AS `jfg` ON `g`.`id` =  `jfg`.`parent_goods_id`');
            $query->leftJoin('`rgzbn_gm_ceiling_goods_special_jobs_map` AS `sjm` ON `g`.`id` = `sjm`.`parent_goods_id`');
            $query->leftJoin('`rgzbn_gm_ceiling_jobs` AS j ON j.id = sjm.job_id');
            $query->where("`category_id` in ($categories) AND `visibility` = 1");
            $query->group('`g`.`id`');
            $query->order('`category_id`, `is_default` DESC,`id`');
            $db->setQuery($query);
            //throw new Exception($query);
            $goods = $db->loadObjectList();
            $goods_jobs_map = [];

            foreach ($goods as $key => $item) {

                if (!empty($item->jobs)) {
                    $goods_jobs_map[$item->id] = json_decode($item->jobs);
                }
                if (!empty($item->spec_jobs)) {
                    $item->spec_jobs = json_decode($item->spec_jobs);
                } else {
                    unset($item->spec_jobs);
                }
                unset($item->jobs);
                $item->child_goods = [];
                foreach ($result as $key1 => $category){
                    foreach ($category->main_groups as $key2 => $main_group) {
                        foreach ($main_group->groups as $key3 => $group) {
                            foreach ($group->fields as $key4 => $field) {
                                if ($field->goods_category_id === $item->category_id) {
                                    $result[$key1]->main_groups[$key2]->groups[$key3]->fields[$key4]->goods[] = clone $item;
                                }
                            }
                        }
                    }
                }
            }

            $query = $db->getQuery(true);
            $query->select('`j`.`id`, `j`.`name`,`m`.`type`,`m`.`field_id`');
            $query->from('`#__gm_ceiling_fields_jobs_map` as `m`');
            $query->innerJoin('`#__gm_ceiling_jobs` as `j` on `m`.`job_id` = `j`.`id`');
            $query->where("`field_id` in ($ids)");
            $query->order('`job_id`');
            $db->setQuery($query);
            
            $jobs = $db->loadObjectList();

            foreach ($jobs as $key => $job) {
                foreach ($result as $key1 => $category) {
                    foreach ($category->main_groups as $key2 => $main_group) {
                        foreach ($main_group->groups as $key3 => $group) {
                            foreach ($group->fields as $key4 => $field) {
                                if ($field->id === $job->field_id) {
                                    if ($job->type == 0) {
                                        $result[$key1]->main_groups[$key2]->groups[$key3]->fields[$key4]->jobs[] = clone $job;
                                    } else {
                                        $result[$key1]->main_groups[$key2]->groups[$key3]->fields[$key4]->manual_jobs[] = clone $job;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $query = $db->getQuery(true);
            $query
                ->select('`field_id`,`goods_id` as `id`')
                ->from('`rgzbn_gm_ceiling_fields_goods_map`')
                ->where("`field_id` in ($ids)");
            $db->setQuery($query);
            $goods = $db->loadObjectList();
            if(!empty($goods)) {
                foreach ($goods as $key => $goods_item) {
                    foreach ($result as $key1 => $category) {
                        foreach ($category->main_groups as $key2 => $main_group) {
                            foreach ($main_group->groups as $key3 => $group) {
                                foreach ($group->fields as $key4 => $field) {
                                    if ($field->id === $goods_item->field_id) {
                                        $result[$key1]->main_groups[$key2]->groups[$key3]->fields[$key4]->default_goods[] = clone $goods_item;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $query = $db->getQuery(true);
            $query = "
                SELECT  DISTINCT
                        `g`.`id`,
                        `g`.`name`,
                        `g`.`category_id`,
                        `gg`.`parent_goods_id`,
                        `gg`.`job_id`
                  FROM  `#__gm_ceiling_goods_from_goods_map` AS `gg`
                        LEFT JOIN   (
                                        SELECT  *
                                          FROM  `#__gm_ceiling_goods_from_goods_map`
                                          WHERE `dealer_id` = $dealer_id
                                    ) AS `ggd`
                                ON  `gg`.`parent_goods_id` = `ggd`.`parent_goods_id`
                        INNER JOIN  `#__gm_stock_goods` AS `g`
                                ON  `gg`.`child_goods_id` = `g`.`id`
                  WHERE (`gg`.`dealer_id` = 1 AND `ggd`.`dealer_id` IS NULL) OR
                        (`gg`.`dealer_id` = $dealer_id AND `ggd`.`dealer_id` = $dealer_id)
            ";
            //throw new Exception($query);
            $db->setQuery($query);

            $child_goods = $db->loadObjectList();
            foreach($result as $key1 => $category) {
                foreach ($category as $key => $main_group) {
                    foreach ($main_group->groups as $key2 => $group) {
                        foreach ($group->fields as $key3 => $field) {
                            if (empty($field->goods)) {
                                continue;
                            }
                            foreach ($field->jobs as $key5 => $job) {
                                foreach ($child_goods as $key4 => $map_item) {
                                    if ($job->id == $map_item->job_id) {
                                        foreach ($field->goods as $key6 => $item) {
                                            if ($item->id == $map_item->parent_goods_id) {
                                                $result[$key1]->main_groups[$key]->groups[$key2]->fields[$key3]->goods[$key6]->child_goods[] = $map_item;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $result['goods_jobs_map'] = $goods_jobs_map;
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getComponentsInCategories($dealer_id = null) {
        try {
            $temp_result = array();
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`g`.*, `gc`.`category`')

                ->from('`#__gm_stock_goods` as `g`')
                ->innerJoin('`#__gm_stock_goods_categories` as `gc` on `g`.`category_id` = `gc`.`id`')
                ->where('`visibility` <> 3')
                ->order('`id`');
            if(!empty($dealer_id)){
                $query
                    ->select('ROUND(
                                    CASE
                                      WHEN `gdp`.`operation_id` = 1 THEN
                                        `gdp`.`value`
                                      WHEN `gdp`.`operation_id` = 2 THEN
                                        `g`.`price` + `gdp`.`value`
                                      WHEN `gdp`.`operation_id` = 3 THEN
                                        `g`.`price` - `gdp`.`value`
                                      WHEN `gdp`.`operation_id` = 4 THEN
                                        `g`.`price` + `gdp`.`value` / 100 * `g`.`price`
                                      WHEN `gdp`.`operation_id` = 5 THEN
                                        `g`.`price` - `gdp`.`value` / 100 * `g`.`price`
                                      ELSE
                                        `g`.`price`
                                    END, 2
                                  ) AS `dealer_price`')
                    ->leftJoin("`rgzbn_gm_ceiling_goods_dealer_price` AS `gdp` ON `gdp`.`goods_id` = `g`.`id` AND `gdp`.`dealer_id` = $dealer_id");
            }
            else{
                $query->where('`category_id` <> 1');
            }
            $db->setQuery($query);
            //throw new Exception($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                if (empty($temp_result[$value->category_id])) {
                    $temp_result[$value->category_id] = (object) array(
                        'category_id' => $value->category_id,
                        'category_name' => $value->category,
                        'goods' => array()
                    );
                }
                $temp_result[$value->category_id]->goods[] = $value;
            }

            foreach ($temp_result as $value) {
                $result[] = $value;
            }

            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addGoodsInCalculation($calc_id, $goods, $from_sketch) {
        try {
            $values = [];
            foreach ($goods as $value) {
                if(is_array($value)) {
                    if(!empty($value['id'])){
                        $values[] = $calc_id . ',' . $value['id'] . ',' . $value['count'];
                    }
                }
                if(is_object($value)){
                    if(!empty($value->id)) {
                        $values[] = $calc_id . ',' . $value->id . ',' . $value->count;
                    }
                }
            }

            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query = "
                DELETE  `cgm`
                  FROM  `#__gm_ceiling_calcs_goods_map` as `cgm`
                        INNER JOIN  `#__gm_stock_goods` as `g`
                                ON  `cgm`.`goods_id` = `g`.`id`
            ";
            if ($from_sketch) {
                $query .= "WHERE `cgm`.`calc_id` = $calc_id AND `g`.`category_id` = 1";
            } else {
                $query .= "WHERE `cgm`.`calc_id` = $calc_id AND `g`.`category_id` <> 1";
            }
            
            $db->setQuery($query);
            $db->execute();

            if (empty($values)) {
                return true;
            }

            $query = $db->getQuery(true);
            $query
                ->insert('`#__gm_ceiling_calcs_goods_map`')
                ->columns('`calc_id`, `goods_id`, `count`')
                ->values($values);
            $db->setQuery($query);
            $db->execute();
            $result = $db->getAffectedRows();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addJobsInCalculation($calc_id, $jobs, $from_sketch) {
        try {
            $values = [];
            foreach ($jobs as $value) {
                if(is_array($value)) {
                    if (is_numeric($value['id']) && is_numeric($value['count'])) {
                        $values[] = $calc_id . ',' . $value['id'] . ',' . $value['count'];
                    }
                }
                if(is_object($value)){
                    if (is_numeric($value->id) && is_numeric($value->count)) {
                        $values[] = $calc_id . ',' . $value->id . ',' . $value->count;
                    }
                }
            }

            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query = "
                DELETE  `cjm`
                  FROM  `#__gm_ceiling_calcs_jobs_map` as `cjm`
                        INNER JOIN  `#__gm_ceiling_jobs` as `j`
                                ON  `cjm`.`job_id` = `j`.`id`
            ";
            if ($from_sketch) {
                $query .= "WHERE `cjm`.`calc_id` = $calc_id AND `j`.`is_factory_work` = 1";
            } else {
                $query .= "WHERE `cjm`.`calc_id` = $calc_id AND `j`.`is_factory_work` = 0";
            }
                
            $db->setQuery($query);
            $db->execute();

            if (empty($values)) {
                return true;
            }

            $query = $db->getQuery(true);
            $query
                ->insert('`#__gm_ceiling_calcs_jobs_map`')
                ->columns('`calc_id`, `job_id`, `count`')
                ->values($values);
            $db->setQuery($query);
            $db->execute();
            $result = $db->getAffectedRows();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getGoodsPricesInCalculation($calc_id, $dealer_id) {
        try {
            /*$dealer_info = Gm_ceilingHelpersGm_ceiling::getDealerInfo($dealer_id);
            if (empty($dealer_info)) {
                $canvases_margin = 0;
                $components_margin = 0;
            } else {
                $canvases_margin = $dealer_info->dealer_canvases_margin;
                $components_margin = $dealer_info->dealer_components_margin;
            }*/
            $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $margin = $calculationModel->getProjectMargin($calc_id);
            $canvases_margin = $margin->dealer_canvases_margin;
            $components_margin = $margin->dealer_components_margin;
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query = "
SELECT  `gf`.`goods_id`,
        `g`.`name`,
        `g`.`category_id`,
        `g`.`original_price`,
        CASE
          WHEN `g`.`category_id` = 1 THEN
            ROUND(`g`.`original_price`* 100 / (100 - $canvases_margin), 2)
          ELSE
            ROUND(`g`.`original_price`* 100 / (100 - $components_margin), 2)
        END AS `original_price_with_margin`,
        `g`.`multiplicity`,
        `g`.`dealer_price`,
        CASE
          WHEN `g`.`category_id` = 1 THEN
            ROUND(`g`.`dealer_price`* 100 / (100 - $canvases_margin), 2)
          ELSE
            ROUND(`g`.`dealer_price`* 100 / (100 - $components_margin), 2)
        END AS `dealer_price_with_margin`,
        `g`.`unit_id`,
        CEIL(SUM(`gf`.`goods_count_all`) / `g`.`multiplicity`) * `g`.`multiplicity` AS `final_count`,
        ROUND(CEIL(SUM(`gf`.`goods_count_all`) / `g`.`multiplicity`) * `g`.`multiplicity` * `g`.`dealer_price`, 2) AS `price_sum`,
        CASE
          WHEN `g`.`category_id` = 1 THEN
            ROUND(CEIL(SUM(`gf`.`goods_count_all`) / `g`.`multiplicity`) * `g`.`multiplicity` * `g`.`dealer_price` * 100 / (100 - $canvases_margin), 2)
          ELSE
            ROUND(CEIL(SUM(`gf`.`goods_count_all`) / `g`.`multiplicity`) * `g`.`multiplicity` * `g`.`dealer_price` * 100 / (100 - $components_margin), 2)
        END AS `price_sum_with_margin`
  FROM  (
      SELECT  `cgm`.`goods_id`,
              `cgm`.`count` AS `goods_count_all`
        FROM  `rgzbn_gm_ceiling_calcs_goods_map` AS `cgm`
        WHERE `cgm`.`calc_id` = $calc_id
    
  ) AS `gf`
        INNER JOIN  (
          SELECT  `g`.`id`,
                  `g`.`name`,
                  `g`.`unit_id`,
                  `g`.`multiplicity`,
                  `g`.`category_id`,
                  `g`.`price` AS `original_price`,
                  ROUND(
                    CASE
                      WHEN `dp`.`operation_id` = 1 THEN
                        `dp`.`value`
                      WHEN `dp`.`operation_id` = 2 THEN
                        `g`.`price` + `dp`.`value`
                      WHEN `dp`.`operation_id` = 3 THEN
                        `g`.`price` - `dp`.`value`
                      WHEN `dp`.`operation_id` = 4 THEN
                        `g`.`price` + `dp`.`value` / 100 * `g`.`price`
                      WHEN `dp`.`operation_id` = 5 THEN
                        `g`.`price` - `dp`.`value` / 100 * `g`.`price`
                      ELSE
                        `g`.`price`
                    END, 2
                  ) AS `dealer_price`
            FROM  `rgzbn_gm_stock_goods` AS `g`
                  LEFT  JOIN  `rgzbn_gm_ceiling_goods_dealer_price` AS `dp`
                          ON  `g`.`id` = `dp`.`goods_id` AND
                              `dp`.`dealer_id` = $dealer_id
        ) AS `g`
              ON  `gf`.`goods_id` = `g`.`id`
GROUP BY `goods_id`
ORDER BY `goods_id`
            ";
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getMountingServicePricesInCalculation($calc_id, $dealer_id = 1) {
        try {
            $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $margin = $calculationModel->getProjectMargin($calc_id);
            $mounting_margin = $margin->dealer_mounting_margin;
            /*$dealer_info = Gm_ceilingHelpersGm_ceiling::getDealerInfo($dealer_id);
            if (empty($dealer_info)) {
                $mounting_margin = 0;
            } else {
                $mounting_margin = $dealer_info->dealer_mounting_margin;
            }*/
            
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query = "
            SELECT  `jf`.`job_id`,
                `j`.`name`,
                `j`.`mount_type_id`,
                SUM(`jf`.`job_count_all`) AS `final_count`,
                `sp`.`price`,
                ROUND(`sp`.`price` * 100 / (100-$mounting_margin),2) as `price_with_margin`,
                ROUND(SUM(`jf`.`job_count_all`) * `sp`.`price`, 2) AS `price_sum`,
                ROUND(SUM(`jf`.`job_count_all`) * `sp`.`price` * 100 / (100 - $mounting_margin), 2) AS `price_sum_with_margin`
    FROM    (
                    SELECT  `cjm`.`job_id`,
                             `cjm`.`count` as `job_count_all`
                            FROM    `rgzbn_gm_ceiling_calcs_jobs_map` AS `cjm`
                            WHERE   `cjm`.`calc_id` = $calc_id
                    
                ) AS `jf`
                INNER JOIN  `rgzbn_gm_ceiling_jobs` AS `j`
                                ON  `jf`.`job_id` = `j`.`id`
                LEFT JOIN `rgzbn_gm_ceiling_jobs_service_price` as `sp`
                                ON `sp`.`job_id` =  `jf`.`job_id` and `sp`.`dealer_id` = $dealer_id
    WHERE   `j`.`guild_only` = 0 AND
            `j`.`is_factory_work` = 0
    GROUP BY    `job_id`
    ORDER BY    `job_id`
            ";
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getJobsPricesInCalculation($calc_id, $dealer_id) {
        try {
            $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $margin = $calculationModel->getProjectMargin($calc_id);
            $mounting_margin = $margin->dealer_mounting_margin;
            /*$dealer_info = Gm_ceilingHelpersGm_ceiling::getDealerInfo($dealer_id);
            if (empty($dealer_info)) {
                $mounting_margin = 0;
            } else {
                $mounting_margin = $dealer_info->dealer_mounting_margin;
            }
*/
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query = "SELECT    `jf`.`job_id`,
                `j`.`name`,
                `j`.`mount_type_id`,
                SUM(`jf`.`job_count_all`) AS `final_count`,
                IFNULL(`jdp`.`price`, 0) AS `price`,
                ROUND(`jdp`.`price` * 100 / (100-$mounting_margin),2) as `price_with_margin`,
                ROUND(SUM(`jf`.`job_count_all`) * IFNULL(`jdp`.`price`, 0), 2) AS `price_sum`,
                ROUND(SUM(`jf`.`job_count_all`) * IFNULL(`jdp`.`price`, 0) * 100 / (100 - $mounting_margin), 2) AS `price_sum_with_margin`
    FROM    (
                SELECT 
                        `cjm`.`job_id`,
                        `cjm`.`count` AS `job_count_all`
                    FROM    `rgzbn_gm_ceiling_calcs_jobs_map` AS `cjm`
                    WHERE   `cjm`.`calc_id` = $calc_id
                    
                ) AS `jf`
                INNER JOIN  `rgzbn_gm_ceiling_jobs` AS `j`
                                ON  `jf`.`job_id` = `j`.`id`
                LEFT    JOIN    `rgzbn_gm_ceiling_jobs_dealer_price` AS `jdp`
                                ON  `jf`.`job_id` = `jdp`.`job_id` AND
                                    `jdp`.`dealer_id` = $dealer_id
    WHERE   `j`.`guild_only` = 0 AND
            `j`.`is_factory_work` = 0
    GROUP BY    `job_id`
    ORDER BY    `job_id`
            ";
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getFactoryWorksPricesInCalculation($calc_id) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('
                    `cjm`.`job_id`,
                    `j`.`name`,
                    `cjm`.`count`,
                    `j`.`price`,
                    `cjm`.`count` * `j`.`price` as `price_sum`
                ')
                ->from('`#__gm_ceiling_calcs_jobs_map` as `cjm`')
                ->innerJoin('`#__gm_ceiling_jobs` as `j` on `cjm`.`job_id` = `j`.`id`')
                ->where("`cjm`.`calc_id` = $calc_id and `j`.`guild_only` = 0 and `j`.`is_factory_work` = 1");
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getNainGroupsData(){
        try{
            $db = $this->getDbo();
            $query = "SET SESSION group_concat_max_len = 1000000;";
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $subquery = $db->getQuery(true);

            $subquery
                ->select('fg.id,fg.title,fg.main_group_id')
                ->select('CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"id":"\',f.id,\'","title":"\',f.title,\'","goods_category":"\',IFNULL(f.goods_category_id,\'\'),\'","input_type":"\',f.input_type,\'","duplicate":"\',f.duplicate,
\'","parent":"\',IFNULL(f.parent,\'\'),\'","order":"\',f.order,\'"}\') SEPARATOR \',\'),\']\') AS `fields`')
                ->from('`rgzbn_gm_ceiling_fields_groups` AS fg')
                ->leftjoin('`rgzbn_gm_ceiling_fields` AS f ON f.group_id = fg.id')
                ->group('fg.id');


            $query
                ->select('mg.id,mg.title,mg.order')
                ->select('CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"group_id":"\',fd.id,\'","title":"\',fd.title,\'","fields":\',fd.fields,\'}\')),\']\') AS `data`')
                ->from('`rgzbn_gm_ceiling_fields_main_groups` AS mg')
                ->innerJoin("($subquery) AS fd ON fd.main_group_id = mg.id ")
                ->group('mg.id');
            $db->setQuery($query);
            //throw new Exception($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getGoodsFromJobs($jobs){
        try{
            $jobs_ids = implode(',',array_keys($jobs));
            $goods = [];
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('gfj.parent_job_id AS job_id,gfj.child_goods_id AS goods_id,gfj.count')
                ->from('`rgzbn_gm_ceiling_goods_from_jobs_map` AS gfj')
                ->leftJoin('`rgzbn_gm_stock_goods` AS g ON g.id = gfj.child_goods_id')
                ->where("parent_job_id in ($jobs_ids)");
            $db->setQuery($query);
            $items = $db->loadObjectList();

            if(!empty($items)){
                foreach ($items as $item){
                    if(array_key_exists($item->goods_id,$goods)){
                        $goods[$item->goods_id]->count += $jobs[$item->job_id]*$item->count;
                    }
                    else{
                        $goods[$item->goods_id] = (object)["id"=>$item->goods_id,"count"=>$jobs[$item->job_id]*$item->count];
                    }
                }
            }
            return $goods;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getJobsFromGoodsMap(){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('parent_goods_id,CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"job_id":"\',jfg.child_job_id,\'","count":"\',jfg.count,\'"}\') SEPARATOR \',\'),\']\') as jobs')
                ->from('`rgzbn_gm_ceiling_jobs_from_goods_map` AS jfg')
                ->group('jfg.parent_goods_id');
            $db->setQuery($query);
            $result = $db->loadAssocList('parent_goods_id','jobs');
            foreach ($result as $key=>$value){
                $items[$key]=json_decode($value);
            }
            return (object)$items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
