<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     aleksander <nigga@hotmail.ru>
 * @copyright  2017 aleksander
 * @license    GNU General Public License версии 2 или более поздней; Смотрите LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$groups = $user->get('groups');

$stock = (in_array(18, $groups) || in_array(19, $groups));

$flag = $this->item->component_id;
$httpref = getenv("HTTP_REFERER");

$component = $this->item;
$option = $component->option;
?>
<?= parent::getPreloader(); ?>

<style>
    .receipt, .receipt div {
        width: 100%;
        display: inline-block;
    }

    .receipt input {
        padding: 0 0 0 10px;
    }

    .receipt .inputComponentList {
        width: 100%;
        padding: 5px;
        margin: 5px 0;
        background-color: rgba(0, 0, 0, .1);
    }

    .receipt .inputComponent {
        width: calc(100% + 10px);
        margin: 0 -5px;
        float: left;
    }

    .receipt .inputComponentList .inputOptionsList {
        width: calc(100% + 10px);
        margin: 0 0 -5px -5px;
        padding: 5px 0 0 0;
        float: left;
    }

    .receipt .inputComponent .input,
    .receipt .inputComponentList .inputOptionsList .input{
        width: calc(100% / 2 - 10px);
        margin: 0 5px;
        float: left;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.5);
    }

    .receipt .input input,
    .receipt .input .lable {
        width: 70%;
        height: 30px;
        line-height: 30px;
        float: left;
        background-color: rgb(255,255,255);
    }

    .receipt .input .lable {
        width: 30%;
        padding-left: 10px;
    }

    .receipt .buttonComplite, .receipt .buttonCancel {
        float: right;
        width: auto;
        margin-right: 0;
        margin-left: 10px;
    }

    .receipt .lockSelect {
        display: none;
        z-index: 1;
        position: relative;
        width: 70%;
        height: 0;
        margin-top: 0;
        float: right;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, .3);
    }

    .receipt .select {
        position: absolute;
        top: 0;
        left: 0;
        width: calc(100% - 2px);
        margin-left: 1px;
        max-height: 70px;
        overflow-y: auto;
        overflow-x: hidden;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, .3);
    }

    .receipt .input .select div {
        width: 100%;
        padding-left: 5px;
        height: 20px;
        line-height: 20px;
        font-size: 14px;
        margin: 0;
        background: rgb(240, 240, 240);
        float: left;
        box-shadow: inset 0 -1px 0 0 rgba(0, 0, 0, .3);
        cursor: pointer;
    }

    .receipt .input .select div:hover {
        background: rgb(220, 220, 220);
    }

    .receipt input {
        border: none;
    }

    .receipt .invalid {
        box-shadow: 0 0 0 2px rgb(255, 51, 49) !important;
    }
</style>

<h1>Редактирование компонента</h1>
<form id="form-canvas" class="receipt"
      action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=componentform.edit'); ?>"
      method="post" enctype="multipart/form-data">
    <input type="hidden" readonly name="link" value="<?=$httpref;?>">
    <input type="hidden" readonly name="component[option][id]" value="<?=$component->option->id;?>">
    <div class="Components">
        <div class="List inputComponentList">
            <div class="inputComponent">
                <div class="input componentsTitle" top="List">
                    <input type="hidden" readonly name="component[id]" id="inputComponentId"
                           tname="id" parent="components" value="<?=$component->id;?>">
                    <div class="lable">Тип: </div>
                    <input type="text" name="component[title]" id="inputComponentTitle" tname="title"
                           parent="components"
                           onkeyup="getList(this, ['inputComponentUnit'])"
                           onfocus="getList(this, ['inputComponentUnit'])"
                           onblur="hideItems(this);"
                           placeholder="Введите тип:"
                           value="<?= (!empty($component)) ? $component->title : ''; ?>" autocomplete="off"
                           required <?=!$stock?"disabled":"";?>>
                    <div class="lockSelect">
                        <div class="select title"></div>
                    </div>
                </div>
                <div class="input componentsUnit" top="List">
                    <div class="lable">Размерность: </div>
                    <input type="text" name="component[unit]" id="inputComponentUnit" tname="unit"
                           parent="components"
                           onkeyup="getList(this, ['inputComponentTitle'])"
                           onfocus="getList(this, ['inputComponentTitle'])"
                           onblur="hideItems(this);"
                           placeholder="Введите размерность:"
                           value="<?= (!empty($component)) ? $component->unit : ''; ?>" autocomplete="off"
                           required <?=!$stock?"disabled":"";?>>
                    <div class="lockSelect">
                        <div class="select unit"></div>
                    </div>
                </div>
            </div>
            <div class="subList inputOptionsList">
                <div class="inputOption">
                    <div class="input optionTitle" top="subList">
                        <div class="lable">Название: </div>
                        <input type="text" name="component[option][title]" id="inputOptionTitle"
                               tname="title"
                               parent="options"
                               onkeyup="getList(this, ['inputComponentTitle'])"
                               onfocus="getList(this, ['inputComponentTitle'])"
                               onblur="hideItems(this);"
                               placeholder="Введите название:"
                               value="<?= (!empty($option)) ? $option->title : ''; ?>" autocomplete="off"
                               required <?=!$stock?"disabled":"";?>>
                        <div class="lockSelect">
                            <div class="select title"></div>
                        </div>
                    </div>
                    <div class="input optionPurchasingPrice" top="subList">
                        <div class="lable">Цена: </div>
                        <input type="text" name="component[option][<?=!$stock ? 'price' : 'purchasing_price';?>]" id="inputOptionPurchasingPrice"
                               tname="price"
                               parent="<?=!$stock ? 'options' : 'analytic';?>"
                               onkeyup="getList(this, ['inputComponentTitle'])"
                               onfocus="getList(this, ['inputComponentTitle'])"
                               onblur="hideItems(this);"
                               placeholder="Введите цену за еденицу размерности:" pattern="(\d+)|(\d+[.,]\d+)"
                               value="<?= (!empty($option)) ? (!$stock ? $option->price : $option->purchasing_price) : ''; ?>" autocomplete="off" required>
                        <div class="lockSelect">
                            <div class="select price"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="Complite">
        <button type="submit" class="buttonComplite btn btn-primary"><i class="fa fa-paper-plane" aria-hidden="true"> Отправить</i>
        </button>
        <a href="<?=$httpref;?>" class="buttonCancel btn btn-primary">Отмена</a>
    </div>
</form>
<script>
    jQuery(document).ready(function () {
    });

    function filtres(id, values) {
        var pages = {
            canvas: '<?= JRoute::_('index.php?option=com_gm_ceiling&task=canvasform.getCanvases');?>',
            component: '<?= JRoute::_('index.php?option=com_gm_ceiling&task=componentform.getComponents');?>'
        };
        var like = function () {
            var where = {};
            jQuery.each(values, function (id, value) {
                where[value.name] = '\'%' + value.value + '%\'';
            });
            return where;
        };
        switch (id) {
            case 'inputCanvasCountry':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputCanvasName':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputCanvasWidth':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputCanvasTexture':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputCanvasColor':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputCanvasColorHex':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputRollerQuadrature':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputRollerPurchasingPrice':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputComponentTitle':
                var filter = {
                    filter: {
                        select: {title: values[0].name, id: 'components.id'},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id, id: 'inputComponentId'},
                        page: pages.component
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputComponentUnit':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.component
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputOptionTitle':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.component
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputOptionPurchasingPrice':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: []
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.component
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
        }
    }

    function getList(thisObject, Objects = null) {
        var input = jQuery(thisObject);

        var root = input.closest('.List');
        input.attr('check', 0);

        var id = input.attr('id');

        var values = [];
        values.push({name: input.attr('parent') + '.' + input.attr('tname'), value: input.val(), id: id});
        jQuery.each(Objects, function (key, idObject) {
            var itemTemp = root.find('#' + idObject);
            values.push({
                name: itemTemp.attr('parent') + '.' + itemTemp.attr('tname'),
                value: itemTemp.val(),
                id: idObject
            });
        });

        var filter = filtres(id, values);

        var items = input.parent().find('.select').filter('.' + input.attr('tname'));
        var lockSelect = items.parent();
        var option = jQuery('<div class="add" onclick="selectItem(this)" parent="' + id + '">+ Добавить</div>');

        if (input.is(":focus")) {
            jQuery.ajax({
                type: 'POST',
                url: filter.filter.page,
                data: filter,
                success: function (data) {
                    data = JSON.parse(data);
                    items.empty();
                    jQuery.each(data, function (index, item) {
                        var itemObj = option.clone().html(item.title).attr({'class': 'option'});
                        jQuery.each(item, function (index, value) {
                            itemObj.attr(index, value);
                        });

                        items.append(itemObj);
                    });
                    if (data.length < 1 && id == 'inputCanvasColor') {
                        var optionNo = option.clone().html("Нет").attr('class', 'empty');
                        items.append(optionNo);
                    }
                    items.append(option);
                    lockSelect.show();
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Сервер не отвечает!"
                    });
                }
            });
        }
    }

    function hideItems(thisObject) {
        var input = jQuery(thisObject);

        var select = input.parent().find('.select');
        var lockSelect = select.parent();

        setTimeout(function () {
            if (!input.attr('check') || input.attr('check') == 0) input.val('');
            select.empty();
            lockSelect.hide();

            jQuery.each(jQuery('input'), function (key, val) {
                if (jQuery(val).is(':invalid'))
                    jQuery(val).parent().addClass('invalid');
                else jQuery(val).parent().removeClass('invalid');
            });
        }, 200);
    }

    function selectItem(thisObject) {
        var item = jQuery(thisObject);
        var select = item.parent();
        var lockSelect = select.parent();

        var inputDiv = item.closest('.input');
        var root = inputDiv.closest('.' + inputDiv.attr('top'));
        var other = inputDiv.find(".other");
        if (other.attr('class') != root.find('#' + item.attr("parent")).attr('class')) other.hide().val('');

        var iclass = item.attr('class');
        var id = item.attr('parent');

        if (iclass == 'option') {

            var filter = filtres(item.attr("parent"), [{name: item.attr("parent"), value: ''}]);
            var objects = filter.filter.objectsId;

            jQuery.each(objects, function (key, val) {
                root.find('#' + val).val(item.attr(key));
            });
        }
        else if (iclass == 'empty') {
            root.find('#' + id).val(item.html());
        }
        else if (iclass == 'add') {
            inputDiv.find(".other").show();
        }

        root.find('#' + id).attr('check', '1');

        select.empty();
        lockSelect.hide();


        jQuery.each(jQuery('input'), function (key, val) {
            if (jQuery(val).is(':invalid'))
                jQuery(val).parent().addClass('invalid');
            else jQuery(val).parent().removeClass('invalid');
        });
    }
</script>