<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 10.09.2019
 * Time: 9:28
 */
$projects_model = Gm_ceilingHelpersGm_ceiling::getModel('Projects');
$projects = $projects_model->getProjectsByHistoryStatus(8);
?>