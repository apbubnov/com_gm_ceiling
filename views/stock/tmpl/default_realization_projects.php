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
if (!(array_search('19', $userGroup) || array_search('18', $userGroup))) header('Location: ' . $_SERVER['REDIRECT_URL']);

$projects = Gm_ceilingHelpersGm_ceiling::getModel('Projects')->getProjetsForRealization();
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
<div class="Actions">
    <?= parent::getButtonBack(); ?>
    <a class="btn btn-large btn-primary" id="Create"
       href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=realization&subtype=create', false, 2); ?>"
       style="margin-left: 10px;"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Создать реализацию</a>
</div>
<div class="Projects">
    <table class="Elements">
        <thead class="ElementsHead">
        <tr class="ElementsHeadTr">
            <td>№</td>
            <td class="Name">Наименование</td>
            <td class="Client">Клиент</td>
            <td class="Dealer">Дилер</td>
            <td class="Status">Статус</td>
            <td class="Date">Дата создания</td>
        </tr>
        </thead>
        <tbody>
        <? foreach ($projects as $p):?>
            <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=stock&type=realization&subtype=project&id=' . (int)$p->id); ?>">
                <td class="Name"><?= $p->id; ?></td>
                <td class="Name"><?= $p->name; ?></td>
                <td class="Client"><?= $p->client; ?></td>
                <td class="Dealer"><?= $p->dealer; ?></td>
                <td class="Status"><?= $p->status_title; ?></td>
                <td class="Date"><?= date("d.m.Y", strtotime($p->created)); ?></td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
</div>

    <script>
        var $ = jQuery,
            SCROLL = {};

        $(document).ready(Init);
        $(document).scroll(Scroll);
        $(window).resize(Resize);

        function Init() {
            $('.Projects .Elements tbody tr').click(function () {
                document.location.href = $(this).data('href');
            });

            ScrollInit();
            Resize();
        }
        function Resize() {
            ResizeHead();
        }

        function ScrollInit() {
            SCROLL.EHead = $(".Projects .Elements .ElementsHead");
            SCROLL.EHeadTr = SCROLL.EHead.find(".ElementsHeadTr");
            SCROLL.EHeadTrClone = SCROLL.EHeadTr.clone();

            SCROLL.EHeadTrClone.removeClass("ElementsHeadTr").addClass("CloneElementsHeadTr");
            SCROLL.EHead.append(SCROLL.EHeadTrClone);

            $(".Projects").scroll(ResizeHead);
        }
        function ResizeHead() {
            SCROLL.EHeadTrClone.css("left", (SCROLL.EHeadTr.offset().left));

            for (var i = 0; i < SCROLL.EHeadTr.children().length; i++)
                $(SCROLL.EHeadTrClone.children()[i]).width($(SCROLL.EHeadTr.children()[i]).width() - ((i === 0)?1:0));
        }
        function Scroll() {
            var scrollTop = $(window).scrollTop(),
                offset = SCROLL.EHeadTr.offset(),
                has = SCROLL.EHeadTrClone.hasClass("Show");
            if (scrollTop >= offset.top) { if (!has) SCROLL.EHeadTrClone.addClass("Show"); }
            else { if (has) SCROLL.EHeadTrClone.removeClass("Show"); }
        }
    </script>

<?if(false):?>
<style>
    .Projects {
        margin-top: 20px;
    }

    .table {
        margin-top: 20px;
    }

    body {
        background-color: #E6E6FA;
    }

     .Projects .table thead tr, .Projects .table tfoot tr {
         background-color: #36357f;
         color: #ffffff;
     }

    .Projects .table tbody tr {
        background-color: #f2f2f2;
        color: #000000;
        cursor: pointer;
    }

    .Projects .table tbody tr:hover {
        background-color: #dedede;
    }

    .Projects .table tr th {
        box-shadow: inset .5px .5px 0 0 rgba(255, 255, 255, .5);
    }

    .Projects .table tr td {
        box-shadow: inset .5px .5px 0 0 rgba(147, 147, 147, 0.5);
    }
</style>

<div class="List Projects">
    <h1>Проекты на сборку</h1>
    <table class="table table-striped" id="ProjectsTable">
        <thead id="ProjectsHead">
        <tr>
            <th class="center">Наименование</th>
            <th class="center">Клиент</th>
            <th class="center">Дилер</th>
            <th class="center">Статус</th>
            <th class="center">Дата создания</th>
        </tr>
        </thead>
        <tbody id="ProjectsBody">
        <? foreach ($projects as $p): ?>
            <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=stock&type=realization&subtype=project&id=' . (int)$p->id); ?>">
                <td class="center"><?= $p->name; ?></td>
                <td class="center"><?= $p->client; ?></td>
                <td class="center"><?= $p->dealer; ?></td>
                <td class="center"><?= $p->status_title; ?></td>
                <td class="center"><?= date("d.m.Y", strtotime($p->created)); ?></td>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot id="ProjectsFoot">
        <tr>
            <th colspan="5"></th>
        </tr>
        </tfoot>
    </table>
</div>

<script>
    jQuery(document).ready(function () {
        jQuery('.table tbody tr').click(function () {
            document.location.href = jQuery(this).data('href');
        });
    })
</script>
<?endif;?>