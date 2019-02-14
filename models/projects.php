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
class Gm_ceilingModelProjects extends JModelList
{
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
                    'status', 'a.status',
                    'calculation_date', 'a.calculation_date',
                    'calculation_time', 'a.calculation_time',
                    'mounting_date', 'a.mounting_date',
                    'mounting_time', 'a.mounting_time',
                    'address', 'a.address',
                    'client_name', 'a.client_name',
                    'quadrature', 'a.quadrature',
                    'group_name', 'a.group_name',
                    'project_margin_sum', 'a.project_margin_sum',
                    'count_ceilings', 'a.count_ceilings',
                    'project_status', 'a.project_status'
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

            //if (empty($list['ordering'])) $list['ordering'] = 'id';
            //if (empty($list['direction'])) $list['direction'] = 'desc';

            if (isset($list['ordering'])) $this->setState('list.ordering', $list['ordering']);
            if (isset($list['direction'])) $this->setState('list.direction', $list['direction']);

            // List state information.
            //parent::populateState($ordering, $direction);
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
    protected function getListQuery() {
        try {
            $db = $this->getDbo();
            $subquery = $db->getQuery(true);
            $subquery->select('`p`.`id`,
                            `p`.`created_by`,
                            `p`.`modified_by`,
                            `p`.`client_id`,
                            `cl`.`client_name`,
                            `cl`.`dealer_id`,
                            GROUP_CONCAT(DISTINCT `cl_con`.`phone` SEPARATOR \', \') AS `client_contacts`,
                            `u`.`name` AS `dealer_name`,
                            `p`.`project_info`,
                            `p`.`project_status`,
                            `st`.`title` AS `status`,
                            `p`.`project_calculation_date`,
                            CONCAT(DATE_FORMAT(`p`.`project_calculation_date`, \'%H:%i\'), \'-\',
                                   DATE_FORMAT(DATE_ADD(`p`.`project_calculation_date`, INTERVAL 1 HOUR), \'%H:%i\')
                            ) AS `calculation_time`,
                            `p`.`project_calculator`,
                            GROUP_CONCAT(DISTINCT `pm`.`mounter_id` SEPARATOR \', \') AS `project_mounter`,
                            GROUP_CONCAT(DISTINCT DATE_FORMAT(`pm`.`date_time`, \'%d.%m.%Y %H:%i\')
                                            ORDER BY `pm`.`date_time` DESC SEPARATOR \', \'
                            ) AS `project_mounting_date`,
                            `u2`.`dealer_id` AS `mounter_dealer_id`,
                            GROUP_CONCAT(DISTINCT CONCAT(DATE_FORMAT(`pm`.`mount_start`, \'%d.%m.%Y %H:%i\'), \'-\',
                                                            DATE_FORMAT(`pm`.`mount_end`, \'%d.%m.%Y %H:%i\')
                                                        ) SEPARATOR \', \'
                            ) AS `mounting_time`,
                            `p`.`project_discount`,
                            `p`.`created`,
                            `p`.`closed`,
                            `p`.`paid`,
                            `p`.`project_sum`,
                            `p`.`new_project_sum`,
                            `p`.`calcs_mounting_sum`,
                            `p`.`new_mount_sum`,
                            `p`.`new_material_sum`,
                            (SUM(`calcs`.`components_sum`) + SUM(`calcs`.`canvases_sum`)) AS `self_price`,
                            SUM(`calcs`.`n4`) AS `quadrature`,
                            COUNT(`calcs`.`id`) AS `count_ceilings`,
                            ((SUM(`calcs`.`components_sum`) * 100) /
                                (100 - `p`.`gm_components_margin` - `p`.`dealer_components_margin` +
                                    (`p`.`gm_components_margin` * `p`.`dealer_components_margin`))
                            ) AS `components_margin_sum`,
                            ((SUM(`calcs`.`canvases_sum`) * 100) /
                                (100 - `p`.`gm_canvases_margin` - `p`.`dealer_canvases_margin` +
                                    (`p`.`gm_canvases_margin` * `p`.`dealer_canvases_margin`))
                            ) AS `canvases_margin_sum`,
                            ((SUM(`calcs`.`mounting_sum`) * 100) /
                                (100 - `p`.`dealer_mounting_margin`)
                            ) AS `mounting_margin_sum`,
                            `p`.`gm_canvases_margin`,
                            `p`.`gm_components_margin`,
                            `p`.`gm_mounting_margin`,
                            `p`.`dealer_canvases_margin`,
                            `p`.`dealer_components_margin`,
                            `p`.`dealer_mounting_margin`,
                            `p`.`read_by_manager`,
                            `p`.`read_by_mounter`,
                            `p`.`transport`,
                            `p`.`distance`,
                            `p`.`distance_col`,
                            `p`.`quickly`,
                            `p`.`api_phone_id`
                        ');
            $subquery->from('`#__gm_ceiling_projects` AS `p`');
            $subquery->leftJoin('`#__gm_ceiling_status` AS `st` ON `st`.`id` = `p`.`project_status`');
            $subquery->leftJoin('`#__gm_ceiling_clients` AS `cl` ON `cl`.`id` = `p`.`client_id`');
            $subquery->leftJoin('`#__users` as `u` ON `u`.`id` = `cl`.`dealer_id`');
            $subquery->leftJoin('`#__gm_ceiling_clients_contacts` AS `cl_con` ON `cl_con`.`client_id` = `p`.`client_id`');
            $subquery->leftJoin("`#__gm_ceiling_projects_mounts` AS `pm` ON `p`.`id` = `pm`.`project_id`");
            $subquery->leftJoin('`#__users` as `u2` ON `u2`.`id` = `pm`.`mounter_id`');
            $subquery->leftJoin("`#__gm_ceiling_calculations` AS `calcs` ON `calcs`.`project_id` = `p`.`id`");
            $subquery->where('`p`.`deleted_by_user` = 0');
            $subquery->group('`p`.`id`');

            $query = $db->getQuery(true);
            $query->select('`p`.`id`,
                        `p`.`created_by`,
                        `p`.`dealer_id`,
                        `p`.`client_id`,
                        `p`.`client_name`,
                        `p`.`client_contacts`,
                        `p`.`project_info`,
                        `p`.`project_status`,
                        `p`.`status`,
                        `p`.`project_calculator`,
                        `p`.`project_calculation_date`,
                        `p`.`calculation_time`,
                        (`p`.`components_margin_sum` +
                            `p`.`canvases_margin_sum` +
                            `p`.`mounting_margin_sum`
                        ) AS `project_margin_sum`');
            $query->from('('.$subquery.') AS `p`');


            $user = JFactory::getUser();
            $groups = $user->get('groups');

            $app = JFactory::getApplication();
            $type = $app->input->getString('type', NULL);
            $subtype = $app->input->getString('subtype', NULL);

            switch ($type) {
                // case "managerprojects":
                //     $query->where('`p`.`project_status` = 3');
                //     $query->where('`p`.`dealer_id` = '.$user->dealer_id);
                //     break;
                case 'chiefprojects':
                    $query->where("((`p`.`project_status` = 4
                                    AND `p`.`project_mounting_date` IS NOT NULL
                                    AND `p`.`project_mounting_date` <> '00.00.0000 00:00')
                                    OR (`p`.`project_status` IN (5, 10)
                                        AND (`p`.`project_mounting_date` IS NULL
                                            OR `p`.`project_mounting_date` = '00.00.0000 00:00')))
                                    AND `p`.`dealer_id` = $user->dealer_id
                                ");
                    break;

                case 'gmmanager':

                    if ($subtype == 'runprojects') {
                        $query->select('`p`.`transport`,
                                        `p`.`distance`,
                                        `p`.`distance_col`,
                                        `p`.`new_mount_sum`,
                                        `p`.`project_sum`,
                                        `p`.`new_project_sum`,
                                        `p`.`new_material_sum`,
                                        `p`.`project_mounting_date`
                                ');
                        $query->where('`p`.`project_status` IN (10, 11, 16, 17, 19, 24, 25, 26, 27, 28, 29)');

                    } elseif ($subtype == 'archive') {
                        $query->select('`p`.`new_project_sum`,
                                        `p`.`project_sum`,
                                        `p`.`closed`
                                ');
                        $query->where('`p`.`project_status` = 12');

                    } elseif ($subtype == 'refused') {
                        $query->where('`p`.`project_status` = 22');

                    } else {
                        $query->select('`p`.`project_mounting_date`,
                                        `p`.`dealer_name`
                                    ');
                        $query->where('`p`.`project_status` IN (4, 5)');
                    }
                    break;

                case 'manager':
                    $query->where('dealer_id = '.$user->dealer_id);

                    if ($subtype == 'refused') {
                        $query->where('`p`.`project_status` IN (2, 3, 15)');

                    } else {
                        $query->where('`p`.`project_status` = 5');
                    }
                    break;

                case 'gmcalculator':

                    if ($subtype == 'calendar') {
                        $query->where('`p`.`project_status` = 1');
                        $query->where("`p`.`project_calculator` = $user->id");
                        $query->order('`p`.`project_calculation_date`');

                    } elseif ($subtype == 'projects') {
                        $query->where('`p`.`project_status` BETWEEN 5 AND 15');

                    } elseif ($subtype == 'refused') {
                        $query->where('`p`.`project_status` IN (2, 3)');
                    }
                    break;

                case 'calculator':

                    if (in_array(14, $groups)) {
                        $query->where('(`p`.`dealer_id` = '.$user->id.')');
                    } elseif (in_array(21, $groups) || in_array(12, $groups)) {
                        $query->where('(`p`.`dealer_id` = '.$user->dealer_id.')');
                    }

                    if ($subtype == 'calendar') {
                        $query->where('`p`.`project_status` = 1');
                        $query->order('`p`.`project_calculation_date`');

                    } elseif ($subtype == 'projects') {
                        $query->where('`p`.`project_status` BETWEEN 5 AND 15');

                    } elseif ($subtype == 'refused') {
                        $query->where('`p`.`project_status` IN (2, 3, 15)');
                    }
                    break;

                case 'gmchief':
                    $query->select('`p`.`quadrature`,
                                    `p`.`dealer_name`,
                                    `p`.`project_mounter`,
                                    `p`.`project_mounting_date`,
                                    `p`.`mounter_dealer_id`
                                ');

                    if ($subtype != 'service' && !empty($subtype)) {
                        $query->where('`p`.`dealer_id` = '.$user->dealer_id);
                    }

                    if ($subtype == 'run') {
                        $query->select('`p`.`closed`');
                        $query->where('`p`.`project_status` = 12');

                    } elseif ($subtype == 'gaugings') {
                        $query->where('`p`.`project_status` = 1');
                        $query->where('`p`.`dealer_id` = 1');

                    } elseif ($subtype == 'service') {
                        $query->innerJoin('`#__user_usergroup_map` as `umap` on `umap`.`user_id` IN (`p`.`project_mounter`)');
                        $query->where("`umap`.`group_id` IN (11, 26) AND `p`.`project_status` IN (5, 10, 19,24,25,26,27,28,29)");

                    } else {
                        $query->where('`p`.`project_status` IN (10, 5, 11, 16, 17, 24, 25, 26, 27, 28, 29)');
                        $query->where("`p`.`mounter_dealer_id` = $user->dealer_id");
                    }
                    break;

                case 'chief':
                    $query->select('`p`.`project_mounter`,
                                    `p`.`project_mounting_date`,
                                    `p`.`transport`,
                                    `p`.`distance`,
                                    `p`.`distance_col`,
                                    `p`.`new_mount_sum`,
                                    `p`.`project_sum`,
                                    `p`.`new_project_sum`,
                                    `p`.`new_material_sum`,
                                    `p`.`self_price`,
                                    `p`.`count_ceilings`
                                ');

                    $query->where('`p`.`dealer_id` = '.$user->dealer_id);

                    if ($subtype == 'run') {
                        $query->select('`p`.`closed`');
                        $query->where('`p`.`project_status` = 12');

                    } elseif ($subtype == 'gaugings') {
                        $query->where('`p`.`project_status` = 1');

                    } else {
                        $query->where('`p`.`project_status` in (10, 5, 11, 16, 17, 23, 6, 7, 8, 24, 25, 26, 27, 28, 29)');
                    }
                    break;

                case 'dealer':
                    $query->where('(`p`.`dealer_id` = '.$user->id.' OR `p`.`dealer_id` = '.$user->dealer_id.')');
                    break;
                default:
                    break;
            }


            // $client_id = $this->getState('filter.client_id');
            // if ($client_id) $query->where('a.client_id = ' . $client_id);

            // // Filtering project_status
            // $filter_project_status = $this->state->get("filter.project_status");
            // if ($filter_project_status != '') $query->where("a.project_status = '" . $db->escape($filter_project_status) . "'");

            // // Add the list ordering clause.
            // $orderCol = $this->state->get('list.ordering');
            // $orderDirn = $this->state->get('list.direction');

            // if (!empty($orderCol) && !empty($orderDirn)) $query->order($db->escape($orderCol . ' ' . $orderDirn));
            // else if (($type == "gmcalculator" && $subtype == "calendar") || ($type == "calculator" && $subtype == "calendar"))
            //     $query->order('a.calculation_date DESC');

            $query->order('`p`.`id` DESC');

            //$this->setState('list.limit', null);

            return $query;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    // protected function getListQuery() {
    //     try {
    //         // Create a new query object.
    //         $db = $this->getDbo();
    //         $query = $db->getQuery(true);

    //         // Select the required fields from the table.
    //         $query->select($this->getState('list.select', 'DISTINCT a.*'))
    //             ->select('DATE_FORMAT(a.project_calculation_date, \'%d.%m.%Y\') AS calculation_date')
    //             ->select('CONCAT(DATE_FORMAT(a.project_calculation_date, \'%H:%i\'),\'-\',DATE_FORMAT(DATE_ADD(a.project_calculation_date, INTERVAL 1 HOUR), \'%H:%i\')) AS calculation_time')
    //             ->select("GROUP_CONCAT(DISTINCT pm.mounter_id SEPARATOR ',') AS project_mounter")
    //             ->select("GROUP_CONCAT(DISTINCT DATE_FORMAT(pm.date_time, '%d.%m.%Y %H:%i')  ORDER BY pm.date_time DESC SEPARATOR ',') AS project_mounting_date")
    //             ->select("GROUP_CONCAT(CONCAT(DATE_FORMAT(pm.mount_start, '%d.%m.%Y %H:%i'),'-',DATE_FORMAT(pm.mount_end, '%d.%m.%Y %H:%i')) SEPARATOR ',')AS mounting_time")

    //             /*                ->select('DATE_FORMAT(a.project_mounting_date, \'%d.%m.%Y %H:%i\') AS mounting_date')
    //                             ->select('CONCAT(DATE_FORMAT(a.project_mounting_start, \'%H:%i\'),\'-\',DATE_FORMAT(a.project_mounting_end, \'%H:%i\')) AS mounting_time')*/
    //             ->select('a.project_info AS address')
    //             ->from('`#__gm_ceiling_projects` AS `a`')
    //             ->group('id');

    //         $query->select('status.title AS `status`, status.id AS status_id')
    //             ->join('LEFT', '`#__gm_ceiling_status` AS `status` ON status.id = a.project_status');

    //         $query->select('`client`.client_name AS `client_name`, `client`.dealer_id as dealer_id')
    //             ->join('LEFT', '`#__gm_ceiling_clients` AS `client` ON client.id = a.client_id');

    //         $query->select('dealer.name AS `dealer_name`')
    //             ->join('LEFT', '`#__users` as `dealer` ON dealer.id = `client`.dealer_id');

    //         $query->select(' client_contact.phone AS `client_contacts`')
    //             ->join('LEFT', '`#__gm_ceiling_clients_contacts` AS `client_contact` ON client_contact.client_id = a.client_id');

    //         /*$query->select('mounter_group.name AS group_name, mounter_group.id AS group_id, mounter_group.brigadir_id AS brigadir_id')
    //             ->join('LEFT', '`#__gm_ceiling_groups` AS `mounter_group` ON mounter_group.id = a.project_mounter');*/

    //         $query->select('(SUM(calculation.components_sum) + SUM(calculation.canvases_sum)) AS self_price,sum(calculation.n4) AS quadrature, count(calculation.id) AS count_ceilings')
    //             ->select('((sum(calculation.components_sum) * 100) / (100 - a.gm_components_margin - a.dealer_components_margin + a.gm_components_margin * a.dealer_components_margin)) AS components_margin_sum')
    //             ->select('((sum(calculation.canvases_sum) * 100) / (100 - a.gm_canvases_margin - a.dealer_canvases_margin + a.gm_canvases_margin * a.dealer_canvases_margin)) AS canvases_margin_sum')
    //             ->select('((sum(calculation.mounting_sum) * 100) / (100 - a.dealer_mounting_margin)) AS mounting_margin_sum')
    //             ->join('LEFT', '`#__gm_ceiling_calculations` AS `calculation` ON calculation.project_id = a.id');

    //         $query->leftJoin("`#__gm_ceiling_projects_mounts` AS pm ON a.id = pm.project_id");
    //         $sql_query = (string)$query;

    //         $query = $db->getQuery(true);
    //         $query->select('a.*')
    //             ->select('(a.components_margin_sum + a.canvases_margin_sum + a.mounting_margin_sum) AS project_margin_sum')
    //             ->select('self_price')
    //             ->from('(' . $sql_query . ') AS a');

    //         $user = JFactory::getUser();
    //         $userId = $user->get('id');
    //         $groups = $user->get('groups');

    //         $app = JFactory::getApplication();
    //         $type = $app->input->getString('type', NULL);
    //         $subtype = $app->input->getString('subtype', NULL);

    //         switch ($type) {
    //             case "managerprojects":
    //                 $query->where('a.project_status = 3');
    //                 $query->where('dealer_id = ' . $user->dealer_id);
    //                 break;
    //             case "chiefprojects":
    //                 $query->where("((a.project_status = 4 AND a.project_mounting_date IS NOT NULL AND a.project_mounting_date <> '00.00.0000 00:00')
    //                 OR (a.project_status IN(5,10) AND (a.project_mounting_date IS NULL OR a.project_mounting_date = '00.00.0000 00:00')))");
    //                 $query->where('dealer_id = ' . $user->dealer_id);
    //                 $query->where('a.deleted_by_user <> 1');
    //                 break;
    //             case "calculatorprojects":
    //                 $query->where('a.project_status = 1');
    //                 $query->where('dealer_id = ' . $user->dealer_id);
    //                 break;
    //             case "gmmanager":
    //                 $query->where('a.deleted_by_user = 0');
    //                 if ($subtype == "runprojects") {
    //                     $query->where('a.project_status in (10, 11, 16, 17, 19, 24, 25, 26,27,28,29)');
    //                 } elseif ($subtype == "archive") {
    //                     $query->where('a.project_status = 12');
    //                 } elseif ($subtype == "refused") {
    //                     $query->where('a.project_status = 22');
    //                 } else {
    //                     $query->where('a.project_status = 5 or a.project_status = 4');
    //                     $query->where('a.project_verdict  = 1');
    //                 }
    //                 break;
    //             case "manager":
    //                 $query->where('dealer_id = ' . $user->dealer_id);
    //                 if ($subtype == "refused") {
    //                     $query->where('a.project_verdict = 0');
    //                     $query->where('a.project_status in (2,3,15)');
    //                 } else {
    //                     $query->where('a.project_status = 5');
    //                     $query->where('a.project_verdict = 1');
    //                 }
    //                 break;
    //             case "gmcalculator":
    //                 //$query->where('a.who_calculate = 1');
    //                 if ($subtype == "calendar") {
    //                     $query->where('a.project_status = 1');
    //                     $query->where("a.project_calculator = '$user->id'");
    //                     $query->order('a.project_calculation_date');
    //                 } elseif ($subtype == "projects") {
    //                     $query->where('a.project_verdict = 1 AND a.project_status BETWEEN 5 AND 15');
    //                 } elseif ($subtype == "refused") {
    //                     $query->where('a.project_verdict = 0');
    //                 }
    //                 break;
    //             case "calculator":
    //                 if (in_array("14", $groups)) {
    //                     $query->where('( dealer_id = ' . $user->id . ')');
    //                 } elseif (in_array("21", $groups) || in_array("12", $groups)) {
    //                     $query->where('( dealer_id = ' . $user->dealer_id . ')');
    //                 }
    //                 if ($subtype == "calendar") {
    //                     $query->where('a.project_status = 1');
    //                     $query->order('a.project_calculation_date');
    //                 } elseif ($subtype == "projects") {
    //                     $query->where('a.project_verdict = 1 AND a.project_status BETWEEN 5 AND 15');
    //                 } elseif ($subtype == "refused") {
    //                     $query->where('a.project_verdict = 0');
    //                 }
    //                 $query->where('a.deleted_by_user <> 1');
    //                 break;
    //             case "gmchief":
    //                 $query->innerJoin('`#__gm_ceiling_clients` as c on a.client_id = c.id');
    //                 if($subtype!="service"){
    //                     $query->where('c.dealer_id = '. $user->dealer_id);
    //                 }
    //                 if ($user->dealer_type == 2) {
    //                     $query->where('a.project_status >= 0');
    //                 } elseif ($subtype =="run") {
    //                     $query->where('a.project_status = 12 AND a.project_verdict = 1 ');
    //                 } elseif ($subtype == "gaugings") {
    //                     $query->where('a.project_status in ("1")');
    //                     $query->where('a.who_calculate = "1"');
    //                 } elseif($subtype == "service"){
    //                     $query->innerJoin('`#__user_usergroup_map` as umap on umap.user_id in (a.project_mounter)');
    //                     $query->innerJoin('`rgzbn_users` AS u ON umap.user_id = u.id');
    //                     $query->where("umap.group_id IN(11,26) AND (u.dealer_id is NULL or u.dealer_id = 1 )  AND a.project_status IN(5,10,19)");
    //                 } else {
    //                     $query->where('a.project_status in ("10", "5", "11", "16", "17","24","25","26","27","28","29")');
    //                 }
    //                 break;
    //             case "chief":
    //                 $query->innerJoin('`#__gm_ceiling_clients` as c on a.client_id = c.id');
    //                 $query->where('c.dealer_id = '. $user->dealer_id);
    //                 if ($user->dealer_type == 2) {
    //                     $query->where('a.project_status >= 0');
    //                 } elseif ($subtype =="run") {
    //                     $query->where('a.project_status = 12 AND a.project_verdict = 1 ');
    //                 } elseif ($subtype == "gaugings") {
    //                     $query->where('a.project_status in ("1")');
    //                     $query->where('a.who_calculate = "0"');
    //                 } else {
    //                     $query->where('a.project_status in ("10", "5", "11", "16", "17", "23", "6", "7", "8","24","25","26","27","28","29")');
    //                 }
    //                 $query->where('a.deleted_by_user <> 1');
    //                 break;
    //             case "dealer":
    //                 $query->where('( dealer_id = ' . $user->id . ' OR dealer_id = ' . $user->dealer_id . ')');
    //                 break;
    //             default:
    //                 $query->where('dealer_id = -1');
    //                 break;
    //         }


    //         $client_id = $this->getState('filter.client_id');
    //         if ($client_id) $query->where('a.client_id = ' . $client_id);

    //         // Filtering project_status
    //         $filter_project_status = $this->state->get("filter.project_status");
    //         if ($filter_project_status != '') $query->where("a.project_status = '" . $db->escape($filter_project_status) . "'");

    //         // Add the list ordering clause.
    //         $orderCol = $this->state->get('list.ordering');
    //         $orderDirn = $this->state->get('list.direction');

    //         if (!empty($orderCol) && !empty($orderDirn)) $query->order($db->escape($orderCol . ' ' . $orderDirn));
    //         else if (($type == "gmcalculator" && $subtype == "calendar") || ($type == "calculator" && $subtype == "calendar"))
    //             $query->order('a.calculation_date DESC');
    //         $query->order('a.id DESC');

    //         $this->setState('list.limit', null);
            
    //         return $query;
    //     } catch(Exception $e) {
    //         Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
    //     }
    // }

    // для кружочков на кнопках
    public function getDataByStatus($status, $data=null) {
        try
        {
            $user       = JFactory::getUser();
            $userId     = $user->get('id');
            $dealer_id = $user->dealer_id;
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            // дилер (замерщик), НМС (график замеров)
            if ($status == "GaugingsGraph") {
                $groups = $user->groups;
                if (in_array("12", $groups) || in_array("14", $groups) || in_array("17", $groups) || in_array("13", $groups)) {
                    if ($user->dealer_id == 1 && !in_array("14", $groups)) {
                        $who = "1";
                    } else {
                        $who = "0";
                    }
                    $query->select('count(projects.id) as count')
                        ->from('#__gm_ceiling_projects as projects')
                        ->innerJoin("#__gm_ceiling_clients as clients ON projects.client_id = clients.id")
                        ->innerJoin("#__users as u on clients.dealer_id = u.id")
                        ->where("projects.project_status = '1' and clients.dealer_id = '$user->dealer_id' and clients.id <> u.associated_client");
                } else if (in_array("21", $groups) || in_array("22", $groups)) {
                    $query->select('count(projects.id) as count')
                        ->from('#__gm_ceiling_projects as projects')
                        ->where("projects.project_status = '1' and projects.project_calculator  = '$userId'");
                }
                $query->where('projects.deleted_by_user <> 1');
            } else
                // замерщик (график замеров), НМС (войти как замерщик)
                if ($status == "GaugingsGraphNMS") {
                    $query->select('count(projects.id) as count')
                        ->from('#__gm_ceiling_projects as projects')
                        ->where("projects.project_status = '1' and projects.project_calculator  = '$userId'");
                    $query->where('projects.deleted_by_user <> 1');
                } else
                    // НМС (монтажи)
                    if ($status == "Mountings") {
                        $query->select('count(distinct p.id) as count')
                            ->from('#__gm_ceiling_projects as p')
                            ->innerJoin("#__gm_ceiling_clients as c ON p.client_id = c.id")
                            ->leftJoin("#__gm_ceiling_projects_mounts as m ON p.id = m.project_id")
                            ->where("p.project_status in ('5', '6', '7', '8', '10', '11', '16', '17','23') and m.date_time is not null and m.date_time <> '0000-00-00 00:00:00' and c.dealer_id = '$user->dealer_id'");
                        $query->where('p.deleted_by_user <> 1');
                    } else
                        // НМС (завершенные заказы)
                        if ($status == "ComplitedMountings") {
                            $query->select('count(projects.id) as count')
                                ->from('#__gm_ceiling_projects as projects')
                                ->innerJoin("#__gm_ceiling_clients as clients ON projects.client_id = clients.id")
                                ->where("projects.project_status = '11' and clients.dealer_id = '$user->dealer_id'");
                            $query->where('projects.deleted_by_user <> 1');
                        } else
                            // НМС (незавершенные заказы)
                            if ($status == "UnComplitedMountings") {
                                $query->select('count(distinct p.id) as `count`')
                                    ->from('#__gm_ceiling_projects as p')
                                    ->innerJoin("#__gm_ceiling_clients as c ON p.client_id = c.id")
                                    ->leftJoin("#__gm_ceiling_projects_mounts as m ON p.id = m.project_id")
                                    ->where("((p.project_status = 4 AND m.date_time IS NOT NULL AND m.date_time <> '0000-00-00 00:00:00')
                    OR (p.project_status in (5,10) AND (m.date_time IS NULL OR m.date_time = '0000-00-00 00:00:00')))
                    AND c.dealer_id = $user->dealer_id AND p.deleted_by_user <> 1");
                            } else
                                // менеджер (в производстве)
                                if ($status == "InProduction") {
                                    $query->select('count(projects.id) as count')
                                        ->from('#__gm_ceiling_projects as projects')
                                        ->where("project_status = 5");
                                    $query->where('projects.deleted_by_user <> 1');
                                } else
                                    //менеджер (заявки с сайта)
                                    if ($status == "ZayavkiSSaita") {
                                        $query->select('count(r.id) as count')
                                            ->from('#__gm_ceiling_requests_from_promo as r');
                                    } else
                                        // менеджер (звонки)
                                        if ($status == "Zvonki") {
                                            $query->select('count(c.id) as count')
                                                ->from('#__gm_ceiling_callback as c')
                                                ->where("date_time <= '$data 23:59:59' and manager_id IN ('$userId', '$dealer_id')");
                                        } else
                                            // менеджер (запущенные)
                                            if ($status == "Zapushennie") {
                                                $query->select('count(projects.id) as count')
                                                    ->from('#__gm_ceiling_projects as projects')
                                                    ->where("project_status in ('10', '11', '16', '17')");
                                                $query->where('projects.deleted_by_user <> 1');
                                            } else
                                                // дилер (менеджер)
                                                if ($status == "FindManagers") {
                                                    if ($id = 1) {
                                                        $group = 16;
                                                    } else {
                                                        $group = 13;
                                                    }
                                                    $query->select('users.id')
                                                        ->from('#__users as users')
                                                        ->innerJoin("#__user_usergroup_map as map ON map.user_id = users.id")
                                                        ->where("group_id = '$group'");
                                                } else
                                                    // менеджер (запущенные в производстве)
                                                    if ($status == "RunInProduction") {
                                                        if ($id = 1) {
                                                            if ($data != "") {
                                                                $data = $data.", 1";
                                                            } else {
                                                                $data = $data."1";
                                                            }
                                                        }
                                                        $query->select('count(projects.id) as count')
                                                            ->from('#__gm_ceiling_projects as projects')
                                                            ->where("project_status in ('4', '5', '10', '11', '16', '17') and read_by_manager in ($data)");
                                                        $query->where('projects.deleted_by_user <> 1');
                                                    } else
                                                        // менеджер (пропущенные)
                                                        if ($status == "MissedCalls") {
                                                            $query->select('call_id')
                                                                ->from('#__gm_ceiling_missed_calls');
                                                        }
                                                        else
                                                            if($status == "MountService"){
                                                                $query
                                                                    ->select("count(distinct pm.project_id) as `count`")
                                                                    ->from("`#__gm_ceiling_projects_mounts` as pm")
                                                                    ->innerJoin("`#__user_usergroup_map` as umap on umap.user_id = pm.mounter_id")
                                                                    ->innerJoin("`#__gm_ceiling_projects` as p on p.id = pm.project_id")
                                                                    ->where("umap.group_id = 26 and p.deleted_by_user = 0 and `p`.`project_status` IN (5, 10, 19) ");
                                                            }

            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getDataByStatusAndAdvt($projects,$date1 = null,$date2 = null){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $subquery = $db->getQuery(true);
            $subquery
                ->select("SUM(COALESCE(c.components_sum,0)+COALESCE(c.canvases_sum,0)+COALESCE(c.mounting_sum,0))")
                ->from("`#__gm_ceiling_calculations` as c")
                ->where("c.project_id = p.id");
            $query
                ->select('p.id')
                ->select('s.title as `status`')
                ->select('p.project_info')
                ->select('COALESCE(p.project_sum,0) as project_sum')
                ->select('COALESCE(p.new_project_sum,0) as new_project_sum')
                ->select('COALESCE(p.new_mount_sum,0) as new_mount_sum')
                ->select('COALESCE(p.new_material_sum,0) as new_material_sum')
                ->select('IF(((ISNULL(`p`.`project_sum`) OR (`p`.`project_sum` = 0)) AND ((`p`.`new_project_sum` IS NOT NULL) OR (`p`.`new_project_sum` <> 0))),`p`.`new_project_sum`,`p`.`project_sum`) AS `sum`')
                ->select("(IF(((ISNULL(`p`.`project_sum`) OR (`p`.`project_sum` = 0)) AND ((`p`.`new_project_sum` IS NOT NULL) OR (`p`.`new_project_sum` <> 0))),`p`.`new_project_sum`,`p`.`project_sum`) - IF((COALESCE((`p`.`new_material_sum` + `p`.`new_mount_sum`),0) = 0),
                ($subquery),COALESCE((`p`.`new_material_sum` + `p`.`new_mount_sum`),0))) AS `profit`")
                ->select('p.client_id')
                ->select("ifnull(($subquery),0) as cost")
                ->from('`#__gm_ceiling_projects` as p')
                ->leftJoin("`#__gm_ceiling_status` as s on p.project_status = s.id")
                ->where("p.id in $projects");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
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

            return $items;
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
    public function getClientItems($client_id)
    {
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            $model->setState('filter.client_id', $client_id);

            $items = $model->getItems();

            foreach ($items as $item) {
                //KM_CHANGED START
                $item->project_status_id = $item->project_status;
                $item->client_contacts = "";
                //KM_CHANGED END
                $item->project_status = JText::_('COM_GM_CEILING_PROJECTS_PROJECT_STATUS_OPTION_' . strtoupper($item->project_status_id));
                $item->project_mounting_daypart = JText::_('COM_GM_CEILING_PROJECTS_PROJECT_MOUNTING_DAYPART_OPTION_' . strtoupper($item->project_mounting_daypart));

                if (isset($item->client_id) && $item->client_id != '') {
                    if (is_object($item->client_id)) {
                        $item->client_id = \Joomla\Utilities\ArrayHelper::fromObject($item->client_id);
                    }

                    $values = (is_array($item->client_id)) ? $item->client_id : explode(',', $item->client_id);
                    //KM_CHANGED START
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);
                    $query
                        ->select('`#__gm_ceiling_calculations_2483036`.`id`')
                        ->from($db->quoteName('#__gm_ceiling_calculations', '#__gm_ceiling_calculations_2483036'))
                        ->where($db->quoteName('project_id') . ' = ' . $db->quote($item->id))
                        ->where($db->quoteName('state') . ' = 1');
                    $db->setQuery($query);
                    $item->project_calculations = sizeof($db->loadObjectList());

                    $textValue = array();
                    $textValue2 = array();

                    foreach ($values as $value) {

                        $query = $db->getQuery(true);
                        $query
                            ->select('`#__gm_ceiling_groups_2483036`.`client_name`')
                            ->from($db->quoteName('#__gm_ceiling_clients', '#__gm_ceiling_groups_2483036'))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
                        $db->setQuery($query);
                        $results = $db->loadObject();

                        if ($results) {
                            $textValue[] = $results->client_name;
                        }


                        $query = $db->getQuery(true);
                        $query
                            ->select('`#__gm_ceiling_groups_2483036`.`client_contacts`')
                            ->from($db->quoteName('#__gm_ceiling_clients', '#__gm_ceiling_groups_2483036'))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
                        $db->setQuery($query);
                        $results = $db->loadObject();

                        if ($results) {
                            $textValue2[] = $results->client_contacts;
                        }
                    }

                    $item->client_id = !empty($textValue) ? implode(', ', $textValue) : $item->client_id;
                    $item->client_contacts = !empty($textValue2) ? implode(', ', $textValue2) : $item->client_contacts;
                    //KM_CHANGED END

                }
                if (isset($item->project_mounter) && $item->project_mounter != '') {
                    if (is_object($item->project_mounter)) {
                        $item->project_mounter = \Joomla\Utilities\ArrayHelper::fromObject($item->project_mounter);
                    }

                    $values = (is_array($item->project_mounter)) ? $item->project_mounter : explode(',', $item->project_mounter);
                    $textValue = array();

                    foreach ($values as $value) {
                        $db = JFactory::getDbo();
                        $query = $db->getQuery(true);
                        $query
                            ->select('`#__gm_ceiling_groups_2483036`.`name`')
                            ->from($db->quoteName('#__gm_ceiling_groups', '#__gm_ceiling_groups_2483036'))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
                        $db->setQuery($query);
                        $results = $db->loadObject();

                        if ($results) {
                            $textValue[] = $results->team_title;
                        }
                    }

                    $item->project_mounter = !empty($textValue) ? implode(', ', $textValue) : $item->project_mounter;
                }

            }
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function updateManagerId($manager_id,$client_id)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_ceiling_projects`')
                ->set("created_by = $manager_id ")
                ->set("modified_by = $manager_id")
                ->set("read_by_manager = $manager_id")
                ->where("client_id = $client_id AND project_status=0");
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*public function updateDealerId($client_id,$dealer_id,$project_id=null)
    {
        try
        {
            if(empty($project_id)){
                $where = "client_id = $client_id";
            }
            else{
                $where = "client_id = $client_id AND id = $project_id";
            }
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_ceiling_projects`')
                ->set("`dealer_id` = $dealer_id ")
                ->where($where);
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/
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

    public function getProjetsForRealization($type = "Stock")
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->from('`#__gm_ceiling_projects` AS project')
                ->select('project.id as id, project.project_info as name, client.dealer_id as dealer_id, project.client_id as client_id, project.created as created');
            if ($type == "Stock") $query->where('project.project_status IN (5, 6, 7, 8, 19) OR project.project_status IS NULL');
            else if ($type == "Guild") $query->where('project.project_status IN (5, 7)');
            $query->join("LEFT","`#__gm_ceiling_status` as s ON s.id = project.project_status")
                ->select("s.id as status, s.title as status_title")
                ->join("LEFT","`#__gm_ceiling_clients` as client ON client.id = project.client_id")
                ->select('client.client_name as client')
                ->join("LEFT","`#__users` as user ON user.id = client.dealer_id")
                ->select('user.name as dealer')
                ->order('project.created DESC')
                ->order('project.id desc');

            $db->setQuery($query);
            $return = $db->loadObjectList();
            return $return;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getStatus()
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from('`#__gm_ceiling_status` AS status')
                ->select('*')
                ->where('status.id BETWEEN 0 AND 5 OR status.id BETWEEN 9 AND 12 OR status.id =15');
            $db->setQuery($query);
            $return = $db->loadObjectList();
            return $return;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getFilteredStatus($id)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from('`#__gm_ceiling_status` AS status')
                ->select('*')
                ->where('status.id = '. $id);
            $db->setQuery($query);
            $return = $db->loadObject();
            return $return;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function deleteEmptyProject($id)
    {
        try
        {
            $user = JFactory::getUser();
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query->from('`#__gm_ceiling_clients`')
                ->select('dealer_id')
                ->where('id = '. $id);
            $db->setQuery($query);
            $dealer = $db->loadObject();
            if ($dealer->dealer_id == $user->id) {
                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__gm_ceiling_projects'));
                $query->where('project_status = 0 AND client_id = ' . $id);
                $db->setQuery($query);
                $db->execute();
            }

            return 1;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function filterProjectForStatus($status, $search, $dealer_id=null){
        try
        {
            $user = JFactory::getUser();
            if ($dealer_id == null) {
                $dealer_id = $user->dealer_id;
            }

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_clients` as `client`")
                ->join("LEFT", "`#__gm_ceiling_clients_contacts` as `phone` ON `phone`.`client_id` = `client`.`id`")
                ->join("LEFT", "(SELECT * FROM `#__gm_ceiling_projects` ORDER BY `id` DESC) as `p` ON `p`.`client_id` = `client`.`id`")
                ->join("LEFT", "`#__users` as `u` ON `client`.`id` = `u`.`associated_client`")
                ->join("LEFT", "`#__gm_ceiling_status` as `s` ON `p`.`project_status` = `s`.`id`")
                ->select("`p`.`id`, `p`.`project_info` as `address`, `s`.`title` as `status`")
                ->select("`client`.`client_name` as `client_name`, `client`.`created`, `client`.`id` as `client_id`")
                ->select("GROUP_CONCAT(distinct `phone`.`phone` SEPARATOR ', ') as `client_contacts`")
                ->where("`u`.`associated_client` IS NULL")
                ->order("`client`.`id` DESC")
                ->group("`client`.`id`");

            if($status && !$search)
                $query->where("`p`.`project_status` = $status AND `client`.`dealer_id` = $dealer_id");
            elseif($status && $search)
                $query->where("`p`.`project_status` =  $status AND `client`.`dealer_id` = $dealer_id AND (`client`.`client_name` LIKE '%$search%' OR `phone`.`phone` LIKE '%$search%' OR `p`.`id` LIKE '%$search%' OR `p`.`project_info` LIKE '%$search%')");
            elseif(!$status && !$search)
                $query->where("`client`.`dealer_id` = $dealer_id");
            elseif(!$status && $search)
                $query->where("`client`.`dealer_id` = $dealer_id AND (`client`.`client_name` LIKE '%$search%' OR `phone`.`phone` LIKE '%$search%' OR `p`.`id` LIKE '%$search%' OR `p`.`project_info` LIKE '%$search%')");
            // print_r((string)$query); exit;
            $db->setQuery($query);

            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getMeasuresAndDayoffsByDealerId($dealer_id){
        try
        {
            $currentDate = date("Y-m-d").' 00:00:00';
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('`u`.`id` AS `project_calculator`,
                    GROUP_CONCAT(DISTINCT CONCAT(`p`.`project_calculation_date`, \'|\', `p`.`id`, \'|\', REPLACE(REPLACE(`p`.`project_info`, \'|\', \'\'), \'!\', \'\')) SEPARATOR \'!\') AS `calc_dates`,
                    GROUP_CONCAT(DISTINCT CONCAT(`d`.`date_from`, \'|\', `d`.`date_to`) SEPARATOR \',\') AS `off_dates`');
            $query->from('`#__users` AS `u`');
            $query->leftJoin('`rgzbn_gm_ceiling_projects` AS `p` ON `p`.`project_calculator` = `u`.`id`');
            $query->leftJoin('`rgzbn_gm_ceiling_day_off` AS `d` ON `u`.`id` = `d`.`id_user`');
            $query->where("`u`.`dealer_id` = $dealer_id AND (`p`.`project_status` = 1 OR `p`.`project_status` IS NULL) AND (`p`.`project_calculation_date` > '$currentDate' OR `d`.`date_to` > '$currentDate')");
            $query->group('`u`.`id`');
            $db->setQuery($query);
            $result = $db->loadObjectList();

            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getMountsAndDayoffsByDealerId($dealer_id){
        try
        {
            $service = ($dealer_id == 1) ? " OR `g`.`group_id` = 32" : "";
            $currentDate = date("Y-m-d").' 00:00:00';
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('`u`.`id` AS `project_mounter`,

                    GROUP_CONCAT(DISTINCT CONCAT(`m`.`date_time`, \'|\', `p`.`id`, \'|\', REPLACE(REPLACE(ifnull(`p`.`project_info`,\'\'), \'|\', \'\'), \'!\', \'\')) SEPARATOR \'!\') AS `mount_dates`,
                    GROUP_CONCAT(DISTINCT CONCAT(`d`.`date_from`, \'|\', `d`.`date_to`) SEPARATOR \',\') AS `off_dates`');
            $query->from('`#__users` AS `u`');
            $query->leftJoin('`#__gm_ceiling_projects_mounts` AS `m` ON `m`.`mounter_id` = `u`.`id`');
            $query->leftJoin('`#__gm_ceiling_projects` AS `p` ON `p`.`id` = `m`.`project_id`');
            $query->leftJoin('`#__gm_ceiling_day_off` AS `d` ON `u`.`id` = `d`.`id_user`');
            $query->innerJoin('`#__user_usergroup_map` AS `g` ON `u`.`id` = `g`.`user_id`');
            $query->where("`u`.`dealer_id` = $dealer_id AND (`g`.`group_id` = 11 $service OR `g`.`group_id` = 14) AND (`p`.`project_status` > 3 OR `p`.`project_status` = 1 OR `p`.`project_status` IS NULL) AND (`m`.`date_time` > '$currentDate' OR `d`.`date_to` > '$currentDate')");
            $query->group('`u`.`id`');
            $db->setQuery($query);
            $result = $db->loadObjectList();

            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getClientsProjects($client_id){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id,project_info')
                ->from('`#__gm_ceiling_projects` as p')
                ->where("p.client_id = $client_id");
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;

        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getInfoDealersAnalytic($projects){
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $quadr_subquery = $db->getQuery(true);
            $quadr_subquery
                ->select("sum(n4)")
                ->from("`#__gm_ceiling_calculations`")
                ->where("project_id = p.id");
            $query
                ->select("distinct p.id,p.project_info,($quadr_subquery) as quadr")
                ->from('`#__gm_ceiling_projects` as p')
                ->innerJoin("`#__gm_ceiling_calculations` as calc on calc.project_id = p.id")
                ->where("p.id in ($projects)")
                ->order("p.id");
            $db->setQuery($query);
            $result = $db->loadObjectList();

            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getProjectsForBuh($dateFrom, $dateTo) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`p`.`id` AS `project_id`,
                          DATE_FORMAT(`ph`.`date_of_change`, \'%d.%m.%Y\') AS `date_production`,
                          `calc`.`id` AS `calc_id`,
                          `calc`.`canvases_sum`,
                          `u`.`name` AS `dealer_name`,
                          DATE_FORMAT(`p`.`created`, \'%d.%m.%Y\') AS `date_created`')
                    ->from('`#__gm_ceiling_projects` AS `p`')
                        ->innerJoin('`#__gm_ceiling_projects_history` AS `ph` ON
                                        `p`.`id` = `ph`.`project_id`')
                        ->leftJoin('`#__gm_ceiling_calculations` AS `calc` ON
                                        `calc`.`project_id` = `p`.`id`')
                        ->innerJoin('`rgzbn_gm_ceiling_clients` AS `cl` ON
                                        `p`.`client_id` = `cl`.`id`')
                        ->innerJoin('`rgzbn_users` AS `u` ON
                                        `cl`.`dealer_id` = `u`.`id`')
                    ->where("`ph`.`date_of_change` >= '$dateFrom' AND
                             `ph`.`date_of_change` <= '$dateTo' AND
                             `ph`.`new_status` = 5")
                    ->group('`calc`.`id`')
                    ->order('`ph`.`date_of_change`,
                             `project_id`,
                             `calc_id`');
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

}
