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
class Gm_ceilingModelStock extends JModelList
{
    public function __construct($config = array())
    {
        try
        {
            if (empty($config['filter_fields'])) {
                $config['filter_fields'] = array(
                    'group_id', 'component.id',
                    'group_title', 'component.title',
                    'group_unit', 'component.unit'
                );
            }

            parent::__construct($config);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getAllListComponents($filter = array())
    {
        try
        {
            $query = $this->getListQuery();

            $db = JFactory::getDbo();
            $db->setQuery($query);

            return $db->loadObjectList();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    protected function getListQuery()
    {
        try
        {
            // Создаем запрос
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->from("`#__gm_ceiling_components` AS component");
            $query->select("component.id AS group_id");
            $query->select("component.title AS group_title");
            $query->select("component.unit AS group_unit");

            $query->join("RIGHT", "`#__gm_ceiling_components_option` AS com_option ON com_option.component_id = component.id");
            $query->select("com_option.id AS option_id");
            $query->select("com_option.title AS option_title");
            $query->select("com_option.price AS option_price");
            $query->select("com_option.count AS option_count");
            //$query->select("com_option.date AS option_date");
            //$query->select("com_option.user_accepted_id AS option_user");
            $query->select("CONCAT(component.title, ' ', com_option.title) AS full_title");

            $query->order("group_id");

            return $query;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function searchClients($search = null, $type = 1, $several = true)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select("c.id as client, c.client_name as client_name, c.dealer_id as dealer, u.name as dealer_name")
                ->from("`#__gm_ceiling_clients` as c")
                ->join("LEFT", "`#__gm_ceiling_clients_type` as ct ON ct.id = c.type_id")
                ->join("LEFT", "`#__users` as u ON c.dealer_id = u.id")
                ->where("c.client_name LIKE ('%" . $search . "%')")
                ->where("(c.type_id = " . $type . (($type == 1) ? " OR c.type_id IS NULL)" : ")"));

            $db->setQuery($query);
            $result = $db->loadObjectList();

            foreach ($result as $k => $v) {
                $query = $db->getQuery(true);

                $query
                    ->select("cc.phone as phone")
                    ->from("`#__gm_ceiling_clients_contacts` as cc")
                    ->where("cc.client_id = " . $v->client);

                $db->setQuery($query);
                $phones = $db->loadObjectList();

                $result[$k]->client_phone = array();
                foreach ($phones as $phone)
                    $result[$k]->client_phone[] = $phone->phone;

                $dealer = $this->searchDealers($v->client, false);
                $result[$k]->dealer_phone = $dealer->dealer_phone;
            }

            return ($several) ? $result : $result[0];
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function searchDealers($search = null, $several = true)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select("u.id as dealer, u.name as dealer_name")
                ->from("`#__users` as u")
                ->join("LEFT", "`#__user_usergroup_map` as ug ON u.id = ug.user_id")
                ->where("ug.group_id = 14")
                ->where("u.name LIKE ('%" . $search . "%')");

            $db->setQuery($query);
            $result = $db->loadObjectList();

            foreach ($result as $k => $v) {
                $query = $db->getQuery(true);

                $query
                    ->select("p.phone as phone")
                    ->from("`#__gm_ceiling_phone` as p")
                    ->where("p.users_id = " . $v->dealer);

                $db->setQuery($query);
                $phones = $db->loadObjectList();

                $result[$k]->dealer_phone = array();
                foreach ($phones as $phone)
                    $result[$k]->dealer_phone[] = $phone->phone;
            }

            return ($several) ? $result : $result[0];
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getInformationStock($id)
    {
        try
        {
            if (intval($id) < 1) return null;

            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('*')
                ->from("`#__gm_ceiling_stocks`")
                ->where("id = " . $db->quote($id));

            $db->setQuery($query);
            return $db->loadObject();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function newDocument($stock, $type, $date)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('MAX(number) as number')
                ->from("`#__gm_ceiling_documents`")
                ->where("type = ".$db->quote($type)."AND stock = ".$db->quote($stock));

            $db->setQuery($query);
            $number = intval($db->loadObject()->number) + 1;

            $href = "http://" . $_SERVER['SERVER_NAME'] . '/files/stock/' . $type . '/' . $number . ".pdf";
            $path = $_SERVER['DOCUMENT_ROOT'] . '/files/stock/' . $type . '/' . $number . ".pdf";

            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->insert($db->quoteName('#__gm_ceiling_documents'))
                ->columns('number, type, href, stock, date')
                ->values($db->quote($number) . "," . $db->quote($type) . "," .
                    $db->quote($href) . "," . $db->quote($stock) . "," . $db->quote($date));

            $db->setQuery($query);
            $res = $db->execute();
            return (empty($res)) ? null : $number;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getStocks()
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('id, name')
                ->from("`#__gm_ceiling_stocks`");
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getHistoryComponent($id, $date = null)
    {
        try
        {
            if (empty($id)) return (object) array();
            if (empty($date))
            {
                $end = date("Y.m.d H:i:s");
                $start = date("Y.m.d H:i:s", mktime(date("H"), date("i"), date("s"), date("m")-1, date("d"),   date("Y")));
                $date = (object) array("start" => $start, "end" => $end);
            }

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('analytic.*, status.title as status, stock.name as stock_name')
                ->from("`#__gm_ceiling_analytics_components` as analytic")
                ->join("LEFT", "`#__gm_ceiling_analytics_status` as status ON analytic.status = status.id")
                ->join("LEFT", "`#__gm_ceiling_stocks` as stock ON analytic.stock = stock.id")
                ->where("analytic.option_id = ".$db->quote($id))
                ->order("analytic.date_update DESC");

            if (!empty($date->start)) $query->where("analytic.date_update >= ".$db->quote($date->start));
            if (!empty($date->end)) $query->where("analytic.date_update <= ".$db->quote($date->end));
            $db->setQuery($query);
            $history = $db->loadObjectList();

            $client = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            foreach ($history as $h) {
                $query = $db->getQuery(true);
                $query->select('SUM(count) as count')
                    ->from("`#__gm_ceiling_analytics_components`")
                    ->where("option_id = ".$db->quote($h->option_id))
                    ->where("barcode = ".$db->quote($h->barcode))
                    ->where("date_update <= ".$db->quote($h->date_update));
                $db->setQuery($query);
                $h->count_now = $db->loadObject()->count;
                $h->price = ($h->price > 0)?$h->price:0;

                $h->client = (!empty($h->client_id))?$client->getItem($h->client_id)->client_name:"-";
                $h->dealer = (!empty($h->dealer_id))?JFactory::getUser($h->dealer_id)->name:"-";
                $h->stock = JFactory::getUser($h->user_id)->name;
                $h->date_update = date("H:i d.m.Y", strtotime($h->date_update));
            }

            $query = $db->getQuery(true);
            $query
                ->select('CONCAT(components.title, \' \', options.title) as name, components.unit as unit')
                ->from('`#__gm_ceiling_components_option` AS options')
                ->join('LEFT', '`#__gm_ceiling_components` AS components  ON components.id = options.component_id ')
                ->where("options.id = ".$db->quote($id));
            $db->setQuery($query);
            $info = $db->loadObject();

            return (object) array('info' => $info, 'history' => $history);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getHistoryCanvas($id, $date = null)
    {
        try
        {
            if (empty($id)) return (object) array();
            if (empty($date))
            {
                $end = date("Y.m.d H:i:s");
                $start = date("Y.m.d H:i:s", strtotime($end." -1 month"));
                $date = (object) array("start" => $start, "end" => $end);
            }

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('canvas.*, status.title as status, stock.name as stock_name')
                ->from("`#__gm_ceiling_analytics_canvases` as canvas")
                ->join("LEFT","`#__gm_ceiling_analytics_status` as status ON status.id = canvas.status")
                ->join("LEFT", "`#__gm_ceiling_stocks` as stock ON canvas.stock = stock.id")
                ->where("canvas_id = ".$db->quote($id))
                ->order("date_update DESC");

            if (!empty($date->start)) $query->where("date_update >= ".$db->quote($date->start));
            if (!empty($date->end)) $query->where("date_update <= ".$db->quote($date->end));

            $db->setQuery($query);
            $history = $db->loadObjectList();

            $client = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            foreach ($history as $h) {
                $query = $db->getQuery(true);
                $query->select('SUM(quad) as quad')
                    ->from("`#__gm_ceiling_analytics_canvases` as canvas")
                    ->where("canvas_id = ".$db->quote($h->canvas_id))
                    ->where("barcode = ".$db->quote($h->barcode))
                    ->where("date_update <= ".$db->quote($h->date_update));
                $db->setQuery($query);
                $h->quad_now = round(floatval($db->loadObject()->quad),2);
                $h->price = ($h->price > 0)?$h->price:0;

                $h->client = (!empty($h->client_id))?$client->getItem($h->client_id)->client_name:"-";
                $h->dealer = (!empty($h->dealer_id))?JFactory::getUser($h->dealer_id)->name:"-";
                $h->stock = JFactory::getUser($h->user_id)->name;
                $h->date_update = date("H:i d.m.Y", strtotime($h->date_update));
            }

            $query = $db->getQuery(true);
            $query
                ->select('CONCAT(c_mnfct.name, \' \', canvas.width, \' - \', c_mnfct.country) as name, \'м²\' as unit')
                ->from('`#__gm_ceiling_canvases` AS canvas')
                ->leftJoin('`#__gm_ceiling_canvases_manufacturers` as c_mnfct on canvas.manufacturer_id = c_mnfct.id' )
                ->where("canvas.id = ".$db->quote($id));
            $db->setQuery($query);
            $info = $db->loadObject();

            return (object) array('info' => $info, 'history' => $history);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCustomer($filter = null)
    {
        try
        {
            $user = JFactory::getUser();
            $db = $this->getDbo();
            $isDealer = false;

            $filter['where']['IS'] = array();
            foreach ($filter['where']['like'] as $k => $v)
            {
                if ($v == "'%%'") unset($filter['where']['like'][$k]);
                else if ($v == "'%Нет%'")
                {
                    unset($filter['where']['like'][$k]);
                    $filter['where']['IS']['('.$k] = "NULL OR ".$k." = '')";
                }
            }
             if($filter['where']['like']['customer.type'] == "'%Дилер%'"){
                    $isDealer = true;
                }
            
            /*$q = $db->getQuery(true);
            $q  ->from('`#__gm_ceiling_clients` AS client')
                ->join('LEFT','`#__gm_ceiling_clients_contacts` AS contact ON contact.client_id = client.id ')
                ->join('LEFT','`#__gm_ceiling_clients_dop_contacts` AS dop ON dop.client_id = client.id ')
                ->join('LEFT','`#__gm_ceiling_clients_type` AS type ON type.id = client.type_id')
                ->join('LEFT','`#__users` AS dealer ON dealer.id = client.dealer_id ')
                ->join('LEFT','`#__gm_ceiling_dealer_info` as info ON info.dealer_id = dealer.id')
                ->where('(dop.type_id = 1 or dop.type_id IS NULL) and client.dealer_id IS NOT NULL')
                ->select("CONVERT(type.title USING utf8) as type, CONVERT(client.client_name USING utf8) as name, CONVERT(dop.contact USING utf8) as email, CONVERT(contact.phone USING utf8) as phone")
                ->select("CONVERT(CONCAT('{\"type\":\"', type.id, '\", \"client\":{\"id\":\"', client.id ,'\", \"name\":\"', (CASE WHEN client.client_name IS NULL THEN '' ELSE client.client_name END), '\", \"email\":\"', (CASE WHEN dop.contact  IS NULL THEN '' ELSE dop.contact END), '\", \"phone\":\"', (CASE WHEN contact.phone IS NULL THEN '' ELSE contact.phone END), '\"}, \"dealer\":{\"id\":\"', dealer.id ,'\", \"name\":\"', dealer.name, '\", \"email\":\"', (CASE WHEN dealer.email IS NULL THEN '' ELSE dealer.email END), '\", \"phone\":\"', (CASE WHEN dealer.username IS NULL THEN '' ELSE dealer.username END), '\"}}') USING utf8) AS JSON")
                ->select("CONVERT(CONCAT('{\"component\":\"', info.dealer_components_margin, '\", \"canvas\":\"', info.dealer_canvases_margin ,'\"}') USING utf8) as margin")
                ->group("client.id");
            $query_1 = "(" . (string) $q . ")";


            $q = $db->getQuery(true);
            $q  ->from('`#__users` AS dealer')
                ->join('LEFT','`#__gm_ceiling_dealer_info` as info ON info.dealer_id = dealer.id')
                ->join('LEFT','`#__user_usergroup_map` as map ON map.user_id = dealer.id')
                ->where("map.group_id = '14'")
                ->select("CONVERT('Дилер' USING utf8) as `type`, CONVERT(dealer.name USING utf8) as `name`, CONVERT(dealer.email USING utf8) as email, CONVERT(dealer.username USING utf8) as phone")
                ->select("CONVERT(CONCAT('{\"type\":\"4\", \"client\":null, \"dealer\":{\"id\":\"', dealer.id ,'\", \"name\":\"', dealer.name, '\", \"email\":\"', (CASE WHEN dealer.email IS NULL THEN '' ELSE dealer.email END), '\", \"phone\":\"', (CASE WHEN dealer.username IS NULL THEN '' ELSE dealer.username END), '\"}}') USING utf8) AS JSON")
                ->select("CONVERT(CONCAT('{\"component\":\"', info.dealer_components_margin, '\", \"canvas\":\"', info.dealer_canvases_margin ,'\"}') USING utf8) as margin")
                ->group("dealer.id");
            $query_2 = "(" . (string) $q . ")";
*/
            $q = $db->getQuery(true);
            $q  ->from('`#__gm_ceiling_clients` AS client')
                ->join('LEFT','`#__gm_ceiling_clients_contacts` AS contact ON contact.client_id = client.id ')
                ->join('LEFT','`#__gm_ceiling_clients_dop_contacts` AS dop ON dop.client_id = client.id ')
                ->join('LEFT','`#__gm_ceiling_clients_type` AS type ON type.id = client.type_id')
                ->join('LEFT','`#__users` AS dealer ON dealer.id = client.dealer_id ')
                ->join('LEFT','`#__gm_ceiling_dealer_info` as info ON info.dealer_id = dealer.id')
                ->where('(dop.type_id = 1 or dop.type_id IS NULL) and client.dealer_id IS NOT NULL')
                ->select("IF(client.id = dealer.associated_client,\"Дилер\",CONVERT(type.title USING utf8)) as `type`, CONVERT(client.client_name USING utf8) as name, CONVERT(dop.contact USING utf8) as email, CONVERT(contact.phone USING utf8) as phone")
                ->select("CONVERT(CONCAT('{\"type\":\"', IF(client.id = dealer.associated_client,'4',type.id), '\", \"client\":{\"id\":\"', client.id ,'\", \"name\":\"', IF(client.client_name IS NULL, '', client.client_name), '\", \"email\":\"', IF(dop.contact  IS NULL, '', dop.contact), '\", \"phone\":\"', IF(contact.phone IS NULL, '', contact.phone), '\"}, \"dealer\":{\"id\":\"', dealer.id ,'\", \"name\":\"', dealer.name, '\", \"email\":\"', IF(dealer.email IS NULL, '', dealer.email ), '\", \"phone\":\"', IF(dealer.username IS NULL, '', dealer.username), '\"}}') USING utf8) AS JSON")
                ->select("CONVERT(CONCAT('{\"component\":\"', info.dealer_components_margin, '\", \"canvas\":\"', info.dealer_canvases_margin ,'\"}') USING utf8) as margin")
                ->group("client.id");
                if(!$isDealer){

                    $q->where("client.dealer_id = $user->dealer_id");
                }
            $query = $db->getQuery(true);
            //$query->from("( " . $query_1 . " UNION " . $query_2 . " ) AS customer");
            $query->from("($q) AS customer");
            if ($filter['select'])
                foreach ($filter['select'] as $key => $value)
                {
                    $query->select("(CASE WHEN ".$value." IS NULL || ".$value." = '' THEN 'Нет' ELSE ".$value." END)"." AS ".$key);
                }
            else $query->select('*');

            if($filter['where'])
                foreach ($filter['where'] as $key => $value)
                    foreach ($value as $title => $item)
                        $query->where($title.' '.$key.' '.$item.' ');
            if ($filter['group'])
                foreach ($filter['group'] as $value)
                    $query->group($value);

            if ($filter['order'])
                foreach ($filter['order'] as $value)
                    $query->order($value);


            print_r($query,true);
            $db->setQuery($query);
            $result = $db->loadObjectList();

            foreach ($result as $i => $v)
            {
                $JSON = json_decode($v->JSON);
                if ($v->JSON == "") break;
                else if (empty($JSON)) {
                    $result[$i]->error = json_encode(array("Не возможно распарсить JSON! Обратитесь к администратору! Имя дилера : ".$v->Name));
                    continue;
                }

                if ($JSON->dealer == null)
                {
                    unset($result[$i]);
                    continue;
                }

                $query = $db->getQuery(true);
                $query->from("`#__gm_ceiling_counterparty` AS CP")
                    ->select("CP.id as id, CP.close_contract as date_contract")
                    ->where("CP.user_id = ".$db->quote($JSON->dealer->id));
                $db->setQuery($query);
                $r = $db->loadObject();
                if (empty($r)){
                    $result[$i]->error = json_encode(array("У дилера нет контрагента, добавьте или выберите ГМ!"));
                }
                else{
                    if($r->date_contract < date("YYYY-MM-DD")){
                        $result[$i]->error = json_encode(array("У дилера закончился срок договора!"));
                    }
                }
            }

            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function setQuery($queryArray) {
        try
        {
            $queryText = "";
            foreach ($queryArray as $query)
            {
                $queryLine = (string) $query;
                $queryLine = str_replace(array("\r","\n"),"", $queryLine);
                $alphabet = array("SELECT","FROM","WHERE","GROUP BY","ORDER BY","UNION","UPDATE","SET","DELETE","ASK","DESC","NULL","IS","INSERT","VALUES","(",")");
                foreach ($alphabet as $a) $queryLine = str_replace($a, " ".$a." ", $queryLine);
                while(strpos($queryLine, "  ")) $queryLine = str_replace("  ", " ", $queryLine);
                $queryLine = substr($queryLine, 1);
                $queryText .= $queryLine.";\n";
            }
           
            $fileOld = file("components/com_gm_ceiling/views/stock/log/sql_log.txt");
            $fileNew = [];
            if (sizeof($fileOld) > 1000)
            {
                $fp=fopen("components/com_gm_ceiling/views/stock/log/sql_log.txt","w");
                for ($i = 500; $i < count($fileOld); $i++) $fileNew[] = $fileOld[$i];
                fputs($fp,implode("",$fileNew));
                fclose($fp);
            }

            $Text = "------------------------------------------------------------------------------------------------------\n";
            $date = date("d.m.Y H:i:s");
            $Text .= "Транзакция: ".$date."\n";
            $Text .= $queryText."\n";
            $db = $this->getDbo();
            $result = [];
            $queries = $db->splitSql($queryText);
            foreach( $queries AS $sql ) {
                $db->setQuery($sql);
                array_push($result,$db->execute());
            }
            //$db->setQuery($queryText);
            //$result = $db->execute();
            $Text .= (empty($result))?"Ответ: Неудачно!\n":"Ответ: Удачно!\n";
            $files = "components/com_gm_ceiling/views/stock/log/";
            file_put_contents($files.'sql_log.txt', $Text, FILE_APPEND);

            return (!empty($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function updateCountGoods()
    {
        try {
            $db = $this->getDbo();

            $query = "UPDATE `#__gm_ceiling_canvases` AS C " .
                "SET `count`= (SELECT COUNT(R.id) FROM `#__gm_ceiling_canvases_all` AS R WHERE R.id_canvas = C.id);";

            $query .= "UPDATE `#__gm_ceiling_components_option` AS O " .
                "SET `count`= (SELECT SUM(G.count) FROM `#__gm_ceiling_components_goods` AS G WHERE G.option_id = O.id);";
            $result = [];
            $queries = $db->splitSql($query);
            foreach( $queries AS $sql ) {
                $db->setQuery($sql);
                array_push($result,$db->execute());
            }
            //$db->setQuery($query);
            //$result = $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getAllProviders(){
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_counterparty` AS CP")
                ->select("*")
                ->where("CP.close_contract > ".$db->quote(date("Y.m.d")));
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function NextStatusProject($id)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_projects` AS P")
                ->select("P.project_status AS status")
                ->where("P.id = ".$db->quote($id));
            $db->setQuery($query);
            $status = floatval($db->loadObject()->status);

            if ($status == 5) $status = 7;
            else if ($status == 6) $status = 19;
            else if ($status == 19) $status = 8;

            $query = $db->getQuery(true);
            $query->update("`#__gm_ceiling_projects`")
                ->set("project_status = '$status'")
                ->where("id = ".$db->quote($id));
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function AddProject($client) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->insert('`#__gm_ceiling_projects`')
                ->columns('`client_id`, `project_status`, `created`')
                ->values("$client->id, 8, NOW()");

            $db->setQuery($query);
            $db->execute();
            $id = $db->insertid();
            return $id;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getRealisedComponents($id){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('g.id AS goods_id,g.category_id,g.name,s.sale_price AS dealer_price,SUM(s.count) AS final_count,s.sale_price*s.count AS price_sum')
                ->select('CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"inventory_id":"\',s.inventory_id,\'","i_count":"\',i.count,\'","r_count":"\',s.count,\'"}\') ORDER BY s.inventory_id DESC SEPARATOR \',\'),\']\') AS inventories')
                ->from('`rgzbn_gm_stock_sales` AS s ')
                ->leftJoin('`rgzbn_gm_stock_inventory` AS i ON i.id = s.inventory_id')
                ->leftJoin('`rgzbn_gm_stock_goods` AS g ON i.goods_id = g.id')
                ->where("s.project_id = $id")
                ->group('g.id');
            $db->setQuery($query);
            $items = $db->loadObjectList('goods_id');
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getRealizedCanvases($id){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("ac.canvas_id as id,abs(quad) as quad")
                ->from("`#__gm_ceiling_analytics_canvases` as ac")
                ->where("ac.project_id = $id");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getPropCanvasWidths() {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_stock_prop_canvas_widths`')
                ->order('`width`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $result[] = (object) array('id' => $value->width, 'value' => $value->width);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getPropColors() {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_stock_prop_colors`')
                ->order('`color`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $result[] = (object) array('id' => $value->color, 'value' => $value->color, 'hex' => $value->hex);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getPropManufacturers() {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_stock_prop_manufacturers`')
                ->order('`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $result[] = (object) array('id' => $value->id, 'value' => $value->manufacturer);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getPropTextures() {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_stock_prop_textures`')
                ->order('`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $result[] = (object) array('id' => $value->id, 'value' => $value->texture);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addPropColor($color_id, $hex) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`#__gm_stock_prop_colors`')
                ->columns('`color`, `hex`')
                ->values("$color_id, '$hex'");
            $db->setQuery($query);
            $db->execute();
            return $db->insertid();
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addPropManufacturer($manufacturer_title) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`#__gm_stock_prop_manufacturers`')
                ->columns('`manufacturer`')
                ->values("'$manufacturer_title'");
            $db->setQuery($query);
            $db->execute();
            return $db->insertid();
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addPropTexture($texture_title) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`#__gm_stock_prop_textures`')
                ->columns('`texture`')
                ->values("'$texture_title'");
            $db->setQuery($query);
            $db->execute();
            return $db->insertid();
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delPropColor($id) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`#__gm_stock_prop_colors`')
                ->where("`color` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delPropManufacturer($id) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`#__gm_stock_prop_manufacturers`')
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delPropTexture($id) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`#__gm_stock_prop_textures`')
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function editPropColor($id, $value) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_stock_prop_colors`')
                ->set("`hex` = '$value'")
                ->where("`color` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function editPropManufacturer($id, $value) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_stock_prop_manufacturers`')
                ->set("`manufacturer` = '$value'")
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function editPropTexture($id, $value) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_stock_prop_textures`')
                ->set("`texture` = '$value'")
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getGoodsCategories() {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_stock_goods_categories`')
                ->order('`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $result[] = (object) array('id' => $value->id, 'value' => $value->category);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getGoods($goods_id = null) {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`go`.`id`, 
                    `go`.`category_id`, 
                    `go`.`name`, 
                    `go`.`unit_id`, 
                    `go`.`price`, 
                    `stoc`.`name` as `stock_name`, 
                    `stoc`.`id` as `stock_id`, 
                    `inv`.`count`')
                ->from('`#__gm_stock_goods` as `go`')
                ->leftJoin('`#__gm_stock_inventory` as `inv` on `go`.`id` = `inv`.`goods_id`')
                ->leftJoin('`#__gm_stock_stocks` as `stoc` on `inv`.`stock_id` = `stoc`.`id`')
                ->order('`go`.`id`');

                if (!empty($goods_id)) {
                    $query->where("`go`.`id`=$goods_id");
                }

            $db->setQuery($query);
            $items = $db->loadObjectList();

            $stocks_count = array();      
            $stocks = array();                
            $items_stocks = $items[0]->id;
            foreach ($items as $value) {
                if ($value->id != $items_stocks) {
                    $stocks_count[] = $stocks;
                    $stocks = null;
                }
                if ($value->stock_id != null) {
                    $stocks[] = (object) array('id' => $value->stock_id,'name' => $value->stock_name,'count' => $value->count);
                } 
                $items_stocks = $value->id;
            }

            $items_stocks = 0;
            $i = 0;
            foreach ($items as $value) {

                if ($value->id != $items_stocks) {
                    $result[] = (object) array('id' => $value->id, 'name' => $value->name, 'category_id' => $value->category_id, 'unit_id' => $value->unit_id, 'price' => $value->price, 'stocks_count' => $stocks_count[$i]);
                    $items_stocks = $value->id;
                    $i++;
                }

            }

            return $result;      
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getOperations() {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`op`.`id`,
                          `op`.`title`')
                ->from('`#__gm_ceiling_operations` as `op`')
                ->order('`op`.`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();

            return $items;     
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }


    public function getGoodsUnits(){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`u`.`id`,
                          `u`.`unit`')
                ->from('`rgzbn_gm_stock_units` as `u`')
                ->order('`u`.`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();

            return $items;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCounterparty($id = null){
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_counterparty` AS CP")
                ->select("*");

            if (!empty($id)) {
                $query->where("CP.id = ".$db->quote($id));
                $db->setQuery($query);
                $items = $db->loadObject();
            } else {
                $db->setQuery($query);
                $items = $db->loadObjectList();
            }
    
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function saveDataInventory($array_reception, $stock_id, $id_counterparty){
        try {
            $user = JFactory::getUser();
            $db = $this->getDbo();
            $reception_values = array();
            foreach ($array_reception as $value) {
                $query = $db->getQuery(true);
                $query
                    ->insert('`rgzbn_gm_stock_inventory`')
                    ->columns('`goods_id`, `count`, `stock_id`')
                    ->values($value['id'].','.$value['count'].','.$stock_id);
                $db->setQuery($query);
                $db->execute();
                $inventory_values[] = $db->insertid().','.$value['cost'].','.$value['count'].',NOW(),'.$user->id.','.$id_counterparty;
            }

            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_stock_reception`')
                ->columns('`inventory_id`, `cost_price`, `count`, `date_time`, `created_by`, `provider_id`')
                ->values($inventory_values);
            $db->setQuery($query);
            $db->execute();

            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function addGoods($category,$goodsName,$goodsUnit,$goodsMultiplicity,$goodsPrice){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_stock_goods`')
                ->columns('`name`,`category_id`,`unit_id`,`multiplicity`,`price`,`created_by`')
                ->values("'$goodsName',$category,$goodsUnit,$goodsMultiplicity,$goodsPrice,".JFactory::getUser()->id);
            $db->setQuery($query);
            $db->execute();
            $result = $db->insertId();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function addProps($tableName,$columnName,$goodsId,$propValue){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert("`$tableName`")
                ->columns("`good_id`,`$columnName`")
                ->values("$goodsId,$propValue");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getGoodsInCategories($dealer_id = null) {
        try {
            $temp_result = array();
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`g`.`id` as `goods_id`,`g`.`name`,`g`.`category_id`,`g`.`price` as `original_price`,`g`.`multiplicity`,`gc`.`category`')
                ->from('`#__gm_stock_goods` as `g`')
                ->innerJoin('`#__gm_stock_goods_categories` as `gc` on `g`.`category_id` = `gc`.`id`')
                ->where('`visibility` <> 3')
                ->order('`g`.`id`');
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

    function getGoodsFromInvetory($ids){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`gi`.`goods_id`,SUM(`gi`.`count`) AS total_count,CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"id":"\',`gi`.`id`,\'","count":"\',`gi`.`count`,\'"}\') ORDER BY `gi`.`id` ASC SEPARATOR \',\'),\']\') AS detailed_count')
                ->from('`rgzbn_gm_stock_inventory` AS `gi`')
                ->where("`gi`.`goods_id` IN ($ids)")
                ->group('`gi`.`goods_id`');
            $db->setQuery($query);
            //throw new Exception($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function makeRealisation($realisationArr,$inventoryArr){
       try{
           if(!empty($realisationArr) && !empty($inventoryArr)) {

               $columns = implode(',', array_keys(get_object_vars($realisationArr[0])));
               $values_arr = [];
               foreach ($realisationArr as $item) {
                   $values_arr[] = implode(',', array_values(get_object_vars($item)));
               }

               $db = $this->getDbo();
               $query = $db->getQuery(true);
               $query
                   ->insert('`rgzbn_gm_stock_sales`')
                   ->columns("$columns")
                   ->values($values_arr);
               $db->setQuery($query);
               $db->execute();
               foreach ($inventoryArr as $inventory) {
                   $query = $db->getQuery(true);
                   $query
                       ->update('`rgzbn_gm_stock_inventory`')
                       ->set("`count` = $inventory->count")
                       ->where("`id` = $inventory->id");
                   $db->setQuery($query);
                   $db->execute();
               }
           }
       }
       catch(Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
       }
    }
     function makeReturn($returnArr,$realisattionUpdateArray,$inventoryUpdateArr,$projectId){
        try{
            $values = [];
            foreach ($returnArr as $returnItem){
                $values[]="$returnItem->inventory_id,$returnItem->count,$projectId";
            }
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_stock_returns`')
                ->columns("inventory_id,count,project_id")
                ->values($values);
            $db->setQuery($query);
            $db->execute();

            foreach ($realisattionUpdateArray as $r_update) {
                $query = $db->getQuery(true);
                $query
                    ->update('`rgzbn_gm_stock_sales`')
                    ->set("`count` = $r_update->count")
                    ->where("`id` = $r_update->inventory_id and project_id = $projectid");
                $db->setQuery($query);
                $db->execute();
            }

            foreach ($inventoryUpdateArr as $inventory) {
                $query = $db->getQuery(true);
                $query
                    ->update('`rgzbn_gm_stock_inventory`')
                    ->set("`count` = $inventory->count")
                    ->where("`id` = $inventory->inventory_id");
                $db->setQuery($query);
                $db->execute();
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function addJobToGoods($goodsId,$jobId,$dealerId){
        try{
            $user = JFactory::getUser();
            if(!empty($user->id)) {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query
                    ->insert('`rgzbn_gm_ceiling_jobs_from_goods_map`')
                    ->columns('`parent_goods_id`,`child_job_id`,`count`,`dealer_id`')
                    ->values("$goodsId,$jobId,1,$user->dealer_id");
                $db->setQuery($query);
                $db->execute();

                return true;
            }
            else{
                throw new Exception("Empty user!");
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }
}