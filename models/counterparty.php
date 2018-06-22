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
class Gm_ceilingModelCounterparty extends JModelList
{
    public function __construct($config = array())
    {
        try {
            if (empty($config['filter_fields'])) {
                $config['filter_fields'] = array();
            }

            parent::__construct($config);
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    protected function getListQuery()
    {
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->from("`#__gm_ceiling_counterparty` AS counterparty")
                ->select("counterparty.*");

            return $query;
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCounterparty($filter = null)
    {
        try {

            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $filter['where']['IS'] = array();
            if(isset($filter['where']['like']) && !empty($filter['where']['like'])) {
                foreach ($filter['where']['like'] as $k => $v) {
                    if ($v == "'%%'") unset($filter['where']['like'][$k]);
                    else if ($v == "'%Нет%'") {
                        unset($filter['where']['like'][$k]);
                        $filter['where']['IS'][$k] = "NULL OR " . $k . " = ''";
                    }
                }
            }

            $query->from("`#__gm_ceiling_counterparty` AS counterparty");

            if (isset($filter['select']['Stock'])) {
                $query->from("`#__gm_ceiling_stocks` AS stock");
                foreach ($filter['select'] as $key => $value)
                    $query->select($value . " AS " . $key);
            } else if (isset($filter['select']))
                foreach ($filter['select'] as $key => $value)
                    $query->select($value . " AS " . $key);
            else if (isset($filter['counterparty_id']))
                $query->select('counterparty.*');
            else if (isset($filter['user_id']))
                $query->select('counterparty.id')
                    ->where("counterparty.user_id = " . $db->quote($filter['user_id']));
            else $query->select('*');

            if (isset($filter['where']))
                foreach ($filter['where'] as $key => $value)
                    foreach ($value as $title => $item)
                        $query->where($title . ' ' . $key . ' ' . $item . ' ');

            if (isset($filter['group']))
                foreach ($filter['group'] as $value)
                    $query->group($value);

            if (isset($filter['order']))
                foreach ($filter['order'] as $value)
                    $query->order($value);

            $db->setQuery($query);
            $result = $db->loadObjectList();

            return $result;
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function SetCounterparty($data)
    {
        try {
            $stock = $data->Stock;
            $data = (array)$data;
            unset($data['page'], $data['Stock']);
            $data = (object)$data;

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select("p.id as id")
                ->from("#__gm_ceiling_counterparty as p")
                ->where("p.name = " . $db->quote($data->Name) . " || " .
                    "p.full_name = " . $db->quote($data->FullName) . " || " .
                    "p.contacts_phone = " . $db->quote($data->ContactsPhone) . " || " .
                    "p.contacts_email = " . $db->quote($data->ContactsEmail));
            $db->setQuery($query);
            $counterparty = $db->loadObject();

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select("p.id as id")
                ->from("#__gm_ceiling_stocks as p")
                ->where("p.name = " . $db->quote($stock));
            $db->setQuery($query);
            $stock = $db->loadObject();

            if (empty($counterparty)) {
                $colums = "`name`, `full_name`, `tin`, `cpr`, `ogrn`, `legal_address`, `mailing_address`, `ceo`, `bank_name`, `pay_account`, `cor_account`, `bic`, `contacts_phone`, `contacts_email`, `close_contract`";
                $values = [];
                foreach ($data as $v) $values[] = $db->quote($v);
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_counterparty'))
                    ->columns($colums)
                    ->values(implode(',', $values));
                $db->setQuery($query);
                $test = $db->execute();
                if (!empty($test)) $counterparty = (object)array("id" => $db->insertid());
                else throw new Exception("Ошибка данных!");
            } else {
                $colums = ['name', 'full_name', 'tin', 'cpr', 'ogrn', 'legal_address', 'mailing_address', 'ceo', 'bank_name', 'pay_account', 'cor_account', 'bic', 'contacts_phone', 'contacts_email', 'close_contract'];
                $values = [];
                $vi = 0;
                foreach ($data as $v) $values[] = $db->quoteName($colums[$vi++]) . " = " . $db->quote($v);
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query
                    ->update($db->quoteName('#__gm_ceiling_counterparty'))
                    ->set(implode(',', $values))
                    ->where("id = " . $db->quote($counterparty->id));
                $db->setQuery($query);
                $test = $db->execute();
                if (empty($test)) throw new Exception("Ошибка изменения данных!");
            }

            return (object)array("stock_id" => $stock->id, "counterparty_id" => $counterparty->id);
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addCounterpartyForDealer($dealer_id,$name,$phone,$email){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $columns = "`user_id`, `name`, `full_name`, `contacts_phone`, `contacts_email`, `close_contract`";
            $query
                ->insert($db->quoteName('#__gm_ceiling_counterparty'))
                ->columns($columns)
                ->values("$dealer_id, '$name', '$name $phone','$phone','$email','2199-12-31'");
            $db->setQuery($query);
            $result = $db->execute();
            if(empty($result))  return false; else return true;
        }
        catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}