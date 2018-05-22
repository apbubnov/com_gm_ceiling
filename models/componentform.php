<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     aleksander <nigga@hotmail.ru>
 * @copyright  2017 aleksander
 * @license    GNU General Public License версии 2 или более поздней; Смотрите LICENSE.txt
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
class Gm_ceilingModelComponentForm extends JModelForm
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
                $id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.component.id');
            } else {
                $id = JFactory::getApplication()->input->get('id');
                JFactory::getApplication()->setUserState('com_gm_ceiling.edit.component.id', $id);
            }

            $this->setState('component.id', $id);

            // Load the parameters.
            $params = $app->getParams();
            $params_array = $params->toArray();

            if (isset($params_array['item_id'])) {
                $this->setState('component.id', $params_array['item_id']);
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
            $this->item = false;

            $app = JFactory::getApplication();
            $id = $app->input->get('id', null, 'string');

            if (isset($id)) {
                $id = $this->getState('component.id');
                $this->setState('component.id', null);
            } else return null;

            $modelComponents = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $this->item = ($modelComponents->getComponents(array('id_option' => $id)))[0];

            $this->item->option = (object)array();
            $this->item->option->title = $this->item->titleOption;
            $this->item->option->id = $this->item->idOption;
            $this->item->option->price = $this->item->price;
            $this->item->option->purchasing_price = $this->item->purchasing_price;

            return $this->item;
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
    public function getTable($type = 'Component', $prefix = 'Gm_ceilingTable', $config = array())
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
            $properties = $table->getProperties();

            if (!in_array('alias', $properties)) {
                return null;
            }

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
            $id = (!empty($id)) ? $id : (int)$this->getState('component.id');

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
            $id = (!empty($id)) ? $id : (int)$this->getState('component.id');

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
            $form = $this->loadForm('com_gm_ceiling.component', 'componentform', array(
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
            $data = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.component.data', array());

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

    public function save($data)
    {
        try
        {
            $db = $this->getDbo();
            $componentId = $data['componentId'];
            if (empty($componentId)) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_components`')
                    ->columns('title, unit')
                    ->values($db->quote($data['componentTitle']) . ', ' . $db->quote($data['componentUnit']));
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query->from('`#__gm_ceiling_components` AS components')
                    ->select('MAX(components.id) AS id');
                $db->setQuery($query);
                $componentId = ($db->loadObjectList())[0]->id;
            }

            $user = JFactory::getUser();
            $query = $db->getQuery(true);
            $query->insert('`#__gm_ceiling_components_option`')
                ->columns('component_id, title, price, count, date, user_accepted_id');
            foreach ($data['optionTitle'] as $key => $value) {
                $query->values($componentId . ', ' . $db->quote($data['optionTitle'][$key]) . ', ' . $data['optionPrice'][$key] . ', ' . $data['optionCount'][$key] . ', ' . $db->quote(date("Y-m-d H:i:s")) . ', ' . $user->id);
            }
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function edit($data)
    {
        try
        {
            $db = $this->getDbo();

            $data['option']['price'] = str_replace(",", ".", $data['option']['price']);
            $data['option']['purchasing_price'] = str_replace(",", ".", $data['option']['purchasing_price']);

            $messageError = null;
            if ($data['id'] != "" && $data['title'] != "") {
                $query = $db->getQuery(true);
                $query->update('`#__gm_ceiling_components` AS components')
                    ->set('components.title = ' . $db->quote($data['title']))
                    ->set('components.unit = ' . $db->quote($data['unit']))
                    ->where('components.id = ' . $data['id']);
                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $messageError = "Изменение не удалось, попробуйте позже!";
            }

            if (empty($messageError) && $data['option']['id'] != "") {
                $query = $db->getQuery(true);
                $query->update('`#__gm_ceiling_components_option` AS options')
                    ->set('options.component_id = ' . $db->quote($data['id']))
                    ->where('options.id = ' . $data['option']['id']);

                if ($data['option']['title'] != "") $query->set('options.title = ' . $db->quote($data['option']['title']));
                if ($data['option']['price'] != "") $query->set('options.price = ' . $db->quote($data['option']['price']));

                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $messageError = "Изменение не удалось, попробуйте позже!";
            }
    /*
            if (empty($messageError) && $data['option']['purchasing_price'] != "") {
                $user = JFactory::getUser();

                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__gm_ceiling_analytics_components'))
                    ->columns('component_id, option_id, count, price, date_update, user_id, note')
                    ->values($db->quote($data['option']['id']) . ', ' . $db->quote(0) . ', ' . $db->quote($data['option']['purchasing_price']) . ', ' .
                        $db->quote($data['date']) . ', ' . $db->quote($user->dealer_id) . ', ' . $db->quote($user->id) . ', ' .
                        $db->quote("Изменение цены"));
                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $messageError = "Изменение не удалось, попробуйте позже!";
            }
    */
            return $messageError;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to delete data
     *
     * @param   int $pk Item primary key
     *
     * @return  int  The id of the deleted item
     *
     * @throws Exception
     *
     * @since 1.6
     */
    public function delete($pk)
    {
        try
        {
            $modelComponents = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $filter = array(
                'select' => array(
                    'componentId' => 'options.component_id',
                    'fullTitle' => 'CONCAT(components.title, \' \', options.title)',
                    'count' => 'options.count'
                ),
                'where' => array(
                    '=' => array(
                        'options.id' => $pk
                    )
                ),
                'group' => array('options.id')
            );
            $result = ($modelComponents->getComponents($filter))[0];
            $componentId = $result->componentId;
            $fullTitle = $result->fullTitle;
            $count = $result->count;

            $filter = array(
                'select' => array(
                    'optionId' => 'options.id'
                ),
                'where' => array(
                    '=' => array(
                        'components.id' => $componentId
                    )
                ),
                'group' => array('options.id')
            );
            $optionsId = $modelComponents->getComponents($filter);

            $errorMessage = null;
            $date = date("Y-m-d H:i:s");

            $db = $this->getDbo();
            if (empty($errorMessage)) {
                $user = JFactory::getUser();

                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__gm_ceiling_analytics_components'))
                    ->columns('component_option_id, count, price, date_update, dealer_id, user_id, note')
                    ->values('NULL, ' . $db->quote($count) . ', ' . $db->quote('0') . ', ' .
                        $db->quote($date) . ', ' . $db->quote($user->dealer_id) . ', ' . $db->quote($user->id) . ', ' .
                        $db->quote("Удаление " . $fullTitle . " - " . $pk));
                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $messageError = "Удаление произошло неудачно, попробуйте позже!";
            }
            if (empty($errorMessage)) {
                $query = $db->getQuery(true);
                $query->delete('`#__gm_ceiling_components_option`');
                $query->where('id = ' . $pk);
                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $errorMessage = "Удаление произошло неудачно, попробуйте позже!";
            }

            if (count($optionsId) <= 1 && empty($errorMessage)) {
                $query = $db->getQuery(true);
                $query->delete('`#__gm_ceiling_components`');
                $query->where('id = ' . $componentId);
                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $errorMessage = "Удаление произошло неудачно, попробуйте позже!";
            }

            return $errorMessage;
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

    public function receipt($data)
    {
        try
        {
            $errorMessage = null;

            $user = JFactory::getUser();
            $user_id = $user->get('id');
            $dealer_id = $user->get('dealer_id');

            $db = $this->getDbo();

            foreach ($data as $val) {
                $query = $db->getQuery(true);
                $query->select('components.id AS id')
                    ->from('`#__gm_ceiling_components` AS components')
                    ->where('components.title = ' . $db->quote($val->Type))
                    ->where('components.unit = ' . $db->quote($val->Unit));
                $db->setQuery($query);
                $result = $db->loadObject();

                if (empty($result)) {
                    $query = $db->getQuery(true);
                    $query->insert($db->quoteName('#__gm_ceiling_components'))
                        ->columns('title, unit')
                        ->values($db->quote($val->Type) . ', ' . $db->quote($val->Unit));
                    $db->setQuery($query);
                    $db->execute();
                    $result = (object) array('id' => $db->insertid());
                }

                foreach ($val->Options as $opt) {
                    $query = $db->getQuery(true);
                    $query->select('options.id AS id, options.count AS count')
                        ->from('`#__gm_ceiling_components_option` AS options')
                        ->where('options.component_id = ' . $db->quote($result->id))
                        ->where('options.title = ' . $db->quote($opt->Name));
                    $db->setQuery($query);
                    $option = $db->loadObject();

                    if (empty($option)) {
                        $query = $db->getQuery(true);
                        $query->insert($db->quoteName('#__gm_ceiling_components_option'))
                            ->columns('`component_id`, `title`, `count`, `count_sale`')
                            ->values($db->quote($result->id) . ', ' . $db->quote($opt->Name)
                                . ', ' . $db->quote($opt->Count) . ', ' . $db->quote($opt->CountSale));
                        $db->setQuery($query);
                        $db->execute();
                        $option = (object) array("id" => $db->insertid(), "count" => $option->Count);
                    } else {
                        $query = $db->getQuery(true);
                        $query->update('`#__gm_ceiling_components_option` AS options')
                            ->set('options.count = ' . $db->quote(floatval($option->count) + floatval($opt->Count)))
                            ->where('options.id = ' . $db->quote($option->id));
                        $db->setQuery($query);
                        $db->execute();
                    }

                    foreach ($opt->Goods as $good) {
                        $query = $db->getQuery(true);
                        $query->insert($db->quoteName('#__gm_ceiling_components_goods'))
                            ->columns('`option_id`, `stock`, `barcode`, `article`, `count`')
                            ->values($db->quote($option->id) . ', ' . $db->quote($good->Stock)
                                . ', ' . $db->quote($good->Barcode) . ', ' . $db->quote($good->Article)
                                . ', ' . $db->quote($good->Count));
                        $db->setQuery($query);
                        $db->execute();
                        $good->id = $db->insertid();

                        $query = $db->getQuery(true);
                        $query->insert($db->quoteName('#__gm_ceiling_analytics_components'))
                            ->columns('`component_id`, `option_id`, `good_id`, `barcode`, `article`, `count`, `price`, `stock`, `date_update`, `user_id`, `counterparty_id`, `status`')
                            ->values($db->quote($result->id) . ', ' . $db->quote($option->id) . ', ' . $db->quote($good->id)
                                . ', ' . $db->quote($good->Barcode) . ', ' . $db->quote($good->Article) . ', ' . $db->quote($good->Count)
                                . ', ' . $db->quote($good->Price) . ', ' . $db->quote($good->Stock) . ', ' . $db->quote($good->Date)
                                . ', ' . $db->quote($user_id) . ', ' . $db->quote($good->Counterparty) . ', ' . $db->quote(1));
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
            }

            return $errorMessage;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function inventory($data, $date)
    {
        try
        {
            $errorMessage = null;
            try {
                $db = $this->getDbo();
                $user = JFactory::getUser();
                foreach ($data as $item) {
                    $item = (object)$item;
                    $item->count = floatval(str_replace(",", ".", $item->count));

                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_components_goods` AS goods')
                        ->join('LEFT', '`#__gm_ceiling_components_option` AS opt ON opt.id = goods.option_id')
                        ->select('goods.*, opt.component_id as component_id')
                        ->where("goods.id = " . $db->quote($item->id));
                    $db->setQuery($query);
                    $result = $db->loadObject();

                    if (!empty($result) && $item->count != 0) {
                        $new_count = floatval($result->count) + $item->count;
                        $new_count = ($new_count <= 0) ? 0 : $new_count;

                        $query = $db->getQuery(true);
                        $query->insert($db->quoteName('#__gm_ceiling_analytics_components'))
                            ->columns('`component_id`, `option_id`, `good_id`, `barcode`, `article`, `count`, `stock`, `date_update`, `user_id`, `status`')
                            ->values($db->quote($result->component_id) . ", " . $db->quote($result->option_id) . ", " . $db->quote($result->id) . ", " .
                                $db->quote($result->barcode) . ", " . $db->quote($result->article) . ", " .
                                $db->quote($item->count) . ", " . $db->quote($result->stock) . ", " . $db->quote($date) . ", " .
                                $db->quote($user->id) . ", " . $db->quote(3));
                        $db->setQuery($query);
                        $test = $db->execute();

                        if (!empty($test)) {
                            $query = $db->getQuery(true);
                            if ($new_count == 0) {
                                $query->delete($db->quoteName('#__gm_ceiling_components_goods'))
                                    ->where('id = ' . $result->id);
                            } else {
                                $query->update($db->quoteName('#__gm_ceiling_components_goods'))
                                    ->set('count = ' . $db->quote($new_count))
                                    ->where('id = ' . $db->quote($result->id));
                            }
                            $db->setQuery($query);
                            $db->execute();
                        }
                    }
                }

            } catch (Exception $ex) {
                $errorMessage = $ex->getMessage();
            }

            return (object)array("error" => $errorMessage);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function TestRealization($data, $customer)
    {
        $db = $this->getDbo();

        $stock = $customer->stock;

        foreach ($data as $id => $v)
        {
            $query = $db->getQuery(true);
            $query->select("G.id, G.count, G.stock, G.barcode, G.article")
                ->from("`#__gm_ceiling_components_option` AS O")
                ->join("LEFT", "#__gm_ceiling_components_goods AS G ON G.option_id = O.id")
                ->where("O.id = '$v->id'")
                ->order("G.id DESC");
            if ($stock == 2) $query->where("G.stock = '$stock'");
            $db->setQuery($query);
            $Goods = $db->loadObjectList();

            $sum = 0;
            foreach ($Goods as $index => $good) {
                $Goods[$index]->count = floatval($good->count);
                $Goods[$index]->countOld = floatval($good->count);
                $Goods[$index]->stock2 = $stock;
                $Goods[$index]->off = false;
                $sum += floatval($good->count);
            }

            if (floatval($v->count) > $sum)
                throw new Exception("Не хватает компонентов!<br>$v->type $v->name<br>Нужно: $v->count $v->unit - На складе: $sum $v->unit");

            $map = array();
            foreach ($Goods as $index => $good)
            {
                if (empty($map[$good->count])) $map[$good->count] = $index;
                else $map[$good->count.".".$index] = $index;
            }
            ksort($map);

            $count = floatval($v->count);
            foreach ($map as $index) {
                if ($count == 0) break;

                $countTemp = floatval($Goods[$index]->count);
                $countR = ($countTemp - $count <= 0)?$countTemp:$count;
                $Goods[$index]->count = $countTemp - $countR;
                $Goods[$index]->realization = $countR;
                $Goods[$index]->off = true;
                $count -= $countR;
            }

            if ($count > 0)
                throw new Exception("Не хватает компонентов!<br>$v->type $v->name");

            foreach ($Goods as $index => $good)
                if ($good->off == false) unset($Goods[$index]);

            $data[$id]->goods = $Goods;
        }

        return $data;
    }

    public function Realization($data, $info)
    {
        try {
        if (count($data) <= 0) return "";

        $db = $this->getDbo();
        $query = [];

        $customer = $info->customer;
        $dealer_id = $customer->dealer->id;
        $client_id = ($customer->client->id == null)?"NULL":"'".$customer->client->id."'";
        $counterparty_id = ($customer->dealer->counterparty == null)?"NULL":"'".$customer->dealer->counterparty."'";
        $stock = $customer->stock;

        $query_update = null;
        $query_delete = null;
        $query_analytic = null;
        $query_transfer = null;

        $query_analytic = $db->getQuery(true);
        $query_analytic->insert($db->quoteName('#__gm_ceiling_analytics_components'))
            ->columns("`component_id`, `option_id`, `good_id`, `barcode`, `article`, `count`, `price`, `stock`, `date_update`, `client_id`, `dealer_id`, `user_id`, `counterparty_id`, `status`");

        $query_transfer = $db->getQuery(true);
        $query_transfer->insert($db->quoteName('#__gm_ceiling_components_transfer'))
            ->columns("`option_id`, `count`, `in`, `out`, `date`");
        $count_transefer = 0;

        foreach ($data as $index => $component) {
            foreach ($component->goods as $good) {
                $price = ceil(floatval($component->price) * (floatval($good->countOld) - floatval($good->count)) * 100) / 100;

                if ($good->stock != $good->stock2)
                {
                    $count_transefer += 1;
                    $query_transfer->values("'$component->id', '$good->count', '$good->stock2', '$good->stock', '$info->date'");
                }

                if ($good->count == 0) {
                    $query_delete = $db->getQuery(true);
                    $query_delete->delete($db->quoteName('#__gm_ceiling_components_goods'))
                        ->where("id = '$good->id'");

                    $query_analytic->values("'$component->component_id', '$component->id', NULL, '$good->barcode', '$good->article', '-$good->realization',".
                        " '$price', '$stock', '$info->date', $client_id, '$dealer_id', '$info->user', $counterparty_id, '2'");
                } else {
                    $query_update = $db->getQuery(true);
                    $query_update->update($db->quoteName('#__gm_ceiling_components_goods'))
                        ->where("id = '$good->id'")
                        ->set("count = '$good->count'");

                    $query_analytic->values("'$component->component_id', '$component->id', '$good->id', '$good->barcode', '$good->article', '-$good->realization',".
                        " '$price', '$stock', '$info->date', $client_id, '$dealer_id', '$info->user', $counterparty_id, '2'");
                }

                if (!empty($query_update)) $query[] = $query_update;
                if (!empty($query_delete)) $query[] = $query_delete;
            }
        }

        $query[] = $query_analytic;
        if ($count_transefer > 0) $query[] = $query_transfer;

        return $query;
        } catch (Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
