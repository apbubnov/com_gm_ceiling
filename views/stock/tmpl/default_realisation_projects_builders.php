<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user = JFactory::getUser();
$userId = $user->get('id');
$userGroup = $user->groups;
//if (!(array_search('19', $userGroup) || array_search('18', $userGroup))) header('Location: ' . $_SERVER['REDIRECT_URL']);

$monthBegin = date('Y-m-01');
$today = date('Y-m-d');
$projects = Gm_ceilingHelpersGm_ceiling::getModel('Projects')->getProjectsForRealisationBuilders();
$builders = Gm_ceilingHelpersGm_ceiling::getModel('users')->getBuilders();
?>
<?= parent::getPreloader(); ?>

<style>
    body {
        background-color: #E6E6FA;
    }

    .Actions {
        margin: 10px 0;
    }

    .Projects {
        width: 100%;
        height: auto;
    }
    .Projects .Elements {
        min-width: 100%;
        position: relative;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .Projects .Elements tr {
        border: 1px solid #414099;
        background-color: #E6E6FA;
        color: #000000;
    }
    .Projects .Elements tr td {
        border: 0;
        border-right: 1px solid #414099;
        width: auto;
        height: 30px;
        line-height: 20px;
        padding: 0 5px;
    }
    .Projects .Elements tr td.Date {
        min-width: 130px;
    }
    .Projects .Elements tr td.Status {
        min-width: 130px;
    }
    .Projects .Elements tr td button {
        display: inline-block;
        float: left;
        border: none;
        width: 30px;
        height: 30px;
        background-color: inherit;
        color: rgb(54, 53, 127);
        border-radius: 5px;
        cursor: pointer;
    }
    .Projects .Elements thead {
        position: relative;
        top: 0;
        left: 0;
    }
    .Projects .Elements thead tr td {
        background-color: #414099;
        color: #ffffff;
        border-color: #ffffff;
        padding: 5px 10px;
        text-align: center;
        min-width: 102px;
    }
    .Projects .Elements tbody tr {
        cursor: pointer;
    }
    .Projects .Elements tbody tr:hover {
        background-color: #97d8ee;
    }
    .Projects .Elements tr td:last-child {
        border-right: 0;
    }
    .Projects .Elements .CloneElementsHead {
        position: fixed;
        top: 0;
        left: 0;
    }
    .Projects .Elements .CloneElementsHeadTr {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1;
    }
    .Projects .Show {
        display: inline-block !important;
    }
</style>

<h1>Реализация</h1>
<div class="row" style="margin-bottom: 1em;">
    <div class="col-md-1">
        <?= parent::getButtonBack(); ?>
    </div>
    <div class="col-md-2">
        <a class="btn btn-large btn-primary" id="Create"
           href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=realisation&subtype=create', false, 2); ?>"
           style="margin-left: 10px;"><i class="fas fa-edit" aria-hidden="true"></i> Создать реализацию</a>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="col-md-5">
            <b>
                <span style="vertical-align: middle">Выберите застройщика</span>
            </b>
        </div>
        <div class="col-md-7">
            <select class="form-control" id="choose_builder">
                <option value="">Выберите застройщика</option>
                <?php foreach($builders as $builder){
                    echo "<option value='$builder->id'>$builder->name</option>";
                } ?>
            </select>
        </div>
    </div>
    <div class="col-md-1" style="text-align: right; padding-right: 0;padding-left: 0;padding-top: 5px;">
        <i class="fas fa-search"></i>
        <b><span style="vertical-align: middle">Поиск: </span></b>
    </div>
    <div class="col-md-4" style="text-align: right">
        <input type="text" class="form-control" id="search_text" placeholder="Введите номер, проект или этаж" />
    </div>
    <div class="col-md-1">
        <button class="btn btn-primary" id="seach_btn">Найти</button>
    </div>
</div>
<div class="Projects">
    <table class="Elements" id="projects_table">
        <thead class="ElementsHead">
        <tr class="ElementsHeadTr">
            <td class="Dealer">№</td>
            <td class="Dealer">Проект</td>
            <td class="center">Кол-во потолков</td>
            <td class="Dealer">Этаж</td>
            <td class="Dealer">Застройщик</td>
        </tr>
        </thead>
        <tbody>
        <? foreach ($projects as $p):?>
            <tr data-href="/index.php?option=com_gm_ceiling&view=stock&type=realisation&subtype=project&id=<?=(int)$p->id;?>">
                <td class="Name"><?= $p->id; ?></td>
                <td class="Name"><?= $p->name; ?></td>
                <td class="center"><?= $p->calc_count; ?></td>
                <td class="Client"><?= $p->client; ?></td>
                <td class="Dealer"><?= $p->dealer; ?></td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    jQuery(document).ready(function(){

        jQuery('#choose_builder').change(function(){
            getFilteredProjects();
        });

        jQuery('#seach_btn').click(function () {
            getFilteredProjects();
        });

        jQuery('#projects_table').on('click','tr',function(){
            location.href = jQuery(this).data('href');
        });

        function getFilteredProjects(){
            var text = jQuery('#search_text').val(),
                builder = jQuery('#choose_builder').val();
            jQuery.ajax({
                type: 'POST',
                async: false,
                url: "/index.php?option=com_gm_ceiling&task=projects.getProjectsForRealisationBuilder",
                data: {
                    search: text,
                    builder: builder
                },
                success: function (data) {
                    console.log(JSON.parse(data));
                    if(!empty(data)){
                        data = JSON.parse(data);
                        jQuery('#projects_table > tbody').empty();
                        jQuery.each(data,function (i,p) {
                            jQuery('#projects_table > tbody').append('<tr data-href="/index.php?option=com_gm_ceiling&view=stock&type=realisation&subtype=project&id='+p.id+'">' +
                                '<td>'+p.id+'</td>'+
                                '<td>'+p.name+'</td>'+
                                '<td>'+p.calc_count+'</td>'+
                                '<td>'+p.client+'</td>'+
                                '<td>'+p.dealer+'</td>'+
                                '</tr>');
                        });
                    }
                    else{
                        noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "warning",
                            text: "по Вашему запросу ничего не найдено!"
                        });
                    }
                },
                error: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных!"
                    });
                }

            });
        }
    });


</script>
