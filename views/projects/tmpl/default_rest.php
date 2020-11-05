<table class="rwd-table" id="projectList">
    <thead>
    <tr>
        <th class='center'>
            №
        </th>
        <th class="center">
            Клиент
        </th>
        <th class='center'>
            Адрес
        </th>
        <th class="center">
            Статус
        </th>
        <th class='center'>
            Остаток
        </th>
        <th class='center'>
            З\п бригаде
        </th>
        <th class='center'>
            Остаток для сдачи
        </th>
        <th class='center'>
            Смета по расходным материалам
        </th>
    </tr>
    </thead>
    <tbody>
<?php
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$calculationformModel = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
    foreach ($this->items as $item) {
        $project_sum = !empty(floatval($item->new_project_sum)) ? $item->new_project_sum : $item->project_sum;
        $path = "/costsheets/" . md5($item->id . "consumables") . ".pdf";
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
           // Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($item->id);
        }
        $transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($item->id);
        $mountSalary = $mount['mounter_sum'];
        $calculations = $calculationsModel->new_getProjectItems($item->id);
        foreach ($calculations as $calculation) {
            if (!empty($calculation->n3)) {
                $mount_data = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, $calculation->id, null, "serviceSelf");
                $mountSalary += $mount_data['total_gm_mounting'];
            } else {
                /*иначе она с новой структурой*/
                $total_gm_sum = 0;
                $all_gm_jobs = $calculationformModel->getJobsPricesInCalculation($calculation->id, 1);
                foreach ($all_gm_jobs as $job) {
                    $total_gm_sum += $job->price_sum;
                }
            }

            $mountSalary += $total_gm_sum;

        }
        //$mountSalary += $item->transport_cost;
?>
        <tr>
            <td data-th = "№" class="center one-touch">
                <?= $item->id;?>
            </td>
            <td data-th = "Клиент" class="center one-touch">
                <?= $item->client_id;?>
            </td>
            <td data-th = "Адрес" class="center one-touch">
                <?= $item->project_info;?>
            </td>
            <td data-th = "Статус" class="center one-touch">
                <?= $item->status;?>
            </td>
            <td data-th = "Остаток" class="center one-touch">
                <?= $project_sum - $item->prepayment;?>
            </td>
            <td data-th = "З\п бригаде" class="center one-touch">
                <?= $mountSalary;?>
            </td>
            <td data-th = "Остаток для сдачи" class="center one-touch">
                <?= $project_sum - $item->prepayment - $mountSalary;?>
            </td>

            <td> <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a></td>
        </tr>
<?php }?>
    </tbody>
</table>

