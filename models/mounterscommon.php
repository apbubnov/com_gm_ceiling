<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 31.05.2019
 * Time: 15:58
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

class Gm_ceilingModelMountersCommon extends JModelItem {
    function getData(){
        try {
            /*
             * SELECT u.id AS mounter_id,u.name AS mounter_name,builder.name AS builder_name,builder.id AS builder_id,t.taken,cs.closed,ps.payed
                FROM `rgzbn_users` AS u
                LEFT JOIN `rgzbn_user_usergroup_map` AS um ON u.id = um.user_id
                LEFT JOIN `rgzbn_gm_ceiling_calcs_mount` AS cm ON u.id = cm.mounter_id
                LEFT JOIN `rgzbn_gm_ceiling_calculations` AS calc ON calc.id = cm.calculation_id
                LEFT JOIN `rgzbn_gm_ceiling_projects` AS p ON calc.project_id = p.id
                LEFT JOIN `rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id
                LEFT JOIN `rgzbn_users` AS builder ON builder.id = c.dealer_id
                LEFT JOIN (
                        SELECT cli.dealer_id,cm.mounter_id,SUM(cm.sum) AS `taken`
                        FROM `rgzbn_gm_ceiling_calcs_mount` AS cm
                        LEFT JOIN `rgzbn_gm_ceiling_calculations` AS cl ON cl.id = cm.calculation_id
                        LEFT JOIN `rgzbn_gm_ceiling_projects` AS pr ON cl.project_id = pr.id
                        LEFT JOIN `rgzbn_gm_ceiling_clients` AS cli ON pr.client_id = cli.id
                        LEFT JOIN `rgzbn_users` AS u ON u.id = cm.mounter_id
                        GROUP BY cli.dealer_id,cm.mounter_id
                    ) AS t ON t.dealer_id = builder.id AND t.mounter_id = u.id

                LEFT JOIN (
                        SELECT ms.mounter_id,c.dealer_id,SUM(GREATEST(0.00,ms.sum)) AS  closed, SUM(LEAST(0.00,ms.sum)) AS payed
                        FROM `rgzbn_gm_ceiling_mounters_salary` AS ms
                        LEFT JOIN `rgzbn_gm_ceiling_projects` AS p ON p.id = ms.project_id
                        LEFT JOIN `rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id
                        INNER JOIN `rgzbn_users` AS u ON u.id = ms.mounter_id
                        GROUP BY c.dealer_id,ms.mounter_id
                ) AS cs ON cs.mounter_id = u.id AND cs.dealer_id = builder.id

                LEFT JOIN (
                        SELECT ms.mounter_id,ms.builder_id,SUM(LEAST(0.00,ms.sum)) AS payed
                        FROM `rgzbn_gm_ceiling_mounters_salary` AS ms
                        LEFT JOIN `rgzbn_gm_ceiling_projects` AS p ON p.id = ms.project_id
                        LEFT JOIN `rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id
                        INNER JOIN `rgzbn_users` AS u ON u.id = ms.mounter_id
                        GROUP BY ms.mounter_id,ms.builder_id
                ) AS ps ON ps.mounter_id = u.id AND ps.builder_id = builder.id

                WHERE um.group_id = 34
                GROUP BY cm.mounter_id,builder.id;
            */
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $takenSubquery = $db->getQuery(true);
            $closedSubquery = $db->getQuery(true);
            $payedSubQuery = $db->getQuery(true);
            $takenSubquery
                ->select('cli.dealer_id,cm.mounter_id,SUM(cm.sum) AS `taken`')
                ->from('`rgzbn_gm_ceiling_calcs_mount` AS cm')
                ->leftJoin('`rgzbn_gm_ceiling_calculations` AS cl ON cl.id = cm.calculation_id')
                ->leftJoin('`rgzbn_gm_ceiling_projects` AS pr ON cl.project_id = pr.id')
                ->leftJoin('`rgzbn_gm_ceiling_clients` AS cli ON pr.client_id = cli.id')
                ->leftJoin('`rgzbn_users` AS u ON u.id = cm.mounter_id')
                ->group(' cli.dealer_id,cm.mounter_id');

            $closedSubquery
                ->select('ms.mounter_id,c.dealer_id,SUM(GREATEST(0.00,ms.sum)) AS  closed')
                ->from('`rgzbn_gm_ceiling_mounters_salary` AS ms')
                ->leftJoin('`rgzbn_gm_ceiling_projects` AS p ON p.id = ms.project_id')
                ->leftJoin('`rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id')
                ->innerJoin('`rgzbn_users` AS u ON u.id = ms.mounter_id')
                ->group('c.dealer_id,ms.mounter_id');

            $payedSubQuery
                ->select('ms.mounter_id,ms.builder_id,SUM(LEAST(0.00,ms.sum)) AS payed')
                ->from('`rgzbn_gm_ceiling_mounters_salary` AS ms')
                ->leftJoin('`rgzbn_gm_ceiling_projects` AS p ON p.id = ms.project_id')
                ->leftJoin('`rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id')
                ->innerJoin('`rgzbn_users` AS u ON u.id = ms.mounter_id')
                ->group(' ms.mounter_id,ms.builder_id');

            $query
                ->select('u.id AS mounter_id,u.name AS mounter_name,builder.name AS builder_name,builder.id AS builder_id,t.taken,cs.closed,ps.payed')
                ->from('`rgzbn_users` AS u')
                ->leftJoin('`rgzbn_user_usergroup_map` AS um ON u.id = um.user_id')
                ->leftJoin('`rgzbn_gm_ceiling_calcs_mount` AS cm ON u.id = cm.mounter_id')
                ->leftJoin('`rgzbn_gm_ceiling_calculations` AS calc ON calc.id = cm.calculation_id')
                ->leftJoin('`rgzbn_gm_ceiling_projects` AS p ON calc.project_id = p.id')
                ->leftJoin('`rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id')
                ->leftJoin('`rgzbn_users` AS builder ON builder.id = c.dealer_id')
                ->leftJoin("($takenSubquery) AS t ON t.dealer_id = builder.id AND t.mounter_id = u.id")
                ->leftJoin("($closedSubquery) AS cs ON cs.mounter_id = u.id AND cs.dealer_id = builder.id")
                ->leftJoin("($payedSubQuery) AS ps ON ps.mounter_id = u.id AND ps.builder_id = builder.id")
                ->where('um.group_id = 34')
                ->group('cm.mounter_id,builder.id');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            $result = [];
            foreach ($items as $item){
                $result[$item->mounter_id]['mounter_name'] = $item->mounter_name;
                $object = clone $item;
                $object->rest = $object->closed+$object->payed;
                unset($object->mounter_name);
                unset($object->mounter_id);
                $result[$item->mounter_id]['builder_data'][] = $object;
            }
            return $result;

        }

        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>


