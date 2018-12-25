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


            $this->item->types = $this->types();
            if ($this->item->id) {
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
                $this->item->n29_all = $this->getListProfil();
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
            $result = array();
            foreach ($temp as $item) $result[$item->id] = $item;

            foreach ($result as $key => $item) {

                $query = $db->getQuery(true);
                $query->select('type.title AS title, type.id AS id')->from('`#__gm_ceiling_type` AS type')->where('type.parent = ' . $item->id);
                $db->setQuery($query);
                $temp = $db->loadObjectList();
                $types = array();
                foreach ($temp as $item) $types[$item->id] = $item;

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
            $list = array();

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
                    $list[$key] = array('id' => $ring, 'count' => $value->count);
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
                            $list[$key] = array('id' => $item->id, 'count' => $value->count);
                            break;
                        }
                    }
                    if (empty($list[$key])) $list[$key] = array('id' => $value->def, 'count' => $value->count);
                } else $list[$key] = array('id' => $value->def, 'count' => $value->count);
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
            $columns = [
                "calculation_title","n6","n7","n8","n11","n12","n16","n17","n18","n19","n20","n21","n24","n25","n27","n28",
                "n30","n32","height","components_sum","canvases_sum","mounting_sum","dealer_components_sum",
                "dealer_canvases_sum","dop_krepezh","extra_components","extra_mounting","components_stock","need_mount",
                "color","details","discount","manager_note"
            ];
            $calculationId = $data['id'];

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_ceiling_calculations`')
                ->set('checked_out_time = ' . $db->quote($date_created));
            foreach ($columns as $column){
                if(!empty($data[$column])){
                    $value = (gettype($data[$column]) == "string") ? "'".$data[$column]."'" : $data[$column];
                    $query->set("$column = $value");
                }
            }
            $query->where('id = ' . $data['id']);

            $db->setQuery($query);
            $db->execute();


            /*сохранение комлектующих, которые имеют виды*/
            $n13 = json_decode($data['n13']);
            $n14 = json_decode($data['n14']);
            $n15 = json_decode($data['n15']);
            $n22 = json_decode($data['n22']);
            $n23 = json_decode($data['n23']);
            $n26 = json_decode($data['n26']);
            $n29 = json_decode($data['n29']);
            


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


           return $calculationId;
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

}
