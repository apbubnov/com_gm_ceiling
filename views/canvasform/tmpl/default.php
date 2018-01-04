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

$flag = $this->item->canvases_id;
$httpref = getenv("HTTP_REFERER");

$canvas = $this->item;
$roller = $canvas->roller;
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

    .receipt .inputCanvasList {
        width: 100%;
        padding: 5px;
        margin: 5px 0;
        background-color: rgba(0, 0, 0, .1);
    }

    .receipt .inputCanvas {
        width: calc(100% + 10px);
        margin: 0 -5px;
        float: left;
    }

    .receipt .inputCanvasList .inputRollersList {
        width: calc(100% + 10px);
        margin: 0 0 -5px -5px;
        padding: 5px 0 0 0;
        float: left;
    }

    .receipt .inputCanvas .input,
    .receipt .inputCanvasList .inputRollersList .input {
        width: calc(100% / 3 - 10px);
        <?=$stock?"margin: 0 5px 10px 5px;":"margin: 5px;"?>
        float: left;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.5);
    }

    .receipt .inputCanvasList .inputRollersList .input {
        width: calc(100% / 2 - 10px);
        margin: -5px 5px 0 5px;
    }

    .receipt .input input,
    .receipt .input .lable {
        width: 70%;
        height: 30px;
        line-height: 30px;
        float: left;
        background-color: rgb(255, 255, 255);
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

<h1>Редактирование полотна</h1>
<form id="form-canvas" class="receipt"
      action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=canvasform.edit'); ?>"
      method="post" enctype="multipart/form-data">
    <input type="hidden" readonly name="link" value="<?= $httpref; ?>">
    <input type="hidden" readonly name="canvas[id]" value="<?= $canvas->id; ?>">
    <input type="hidden" readonly name="canvas[roller][id]" value="<?= $roller->id; ?>">
    <div class="Canvases">
        <div class="List inputCanvasList">
            <div class="inputCanvas">
                <div class="input canvasCountry" top="List">
                    <div class="lable">Страна: </div>
                    <input type="text" name="canvas[country]" id="inputCanvasCountry" tname="country"
                           parent="canvases"
                           onkeyup="getList(this, ['inputCanvasName', 'inputCanvasWidth']);"
                           onfocus="getList(this, ['inputCanvasName', 'inputCanvasWidth']);"
                           onblur="hideItems(this);"
                           placeholder="Введите страну:"
                           value="<?= (!empty($canvas)) ? $canvas->country : ''; ?>" autocomplete="off"
                           required <?=!$stock?"disabled":"";?>>
                    <div class="lockSelect">
                        <div class="select country"></div>
                    </div>
                </div>
                <div class="input canvasName" top="List">
                    <div class="lable">Название: </div>
                    <input type="text" name="canvas[name]" id="inputCanvasName" tname="name" parent="canvases"
                           onkeyup="getList(this, ['inputCanvasCountry', 'inputCanvasWidth'])"
                           onfocus="getList(this, ['inputCanvasCountry', 'inputCanvasWidth'])"
                           onblur="hideItems(this);"
                           placeholder="Введите название:" value="<?= (!empty($canvas)) ? $canvas->name : ''; ?>"
                           autocomplete="off" required <?=!$stock?"disabled":"";?>>
                    <div class="lockSelect">
                        <div class="select name"></div>
                    </div>
                </div>
                <div class="input canvasWidth" top="List">
                    <div class="lable">Ширина: </div>
                    <input type="text" name="canvas[width]" id="inputCanvasWidth" tname="width"
                           parent="canvases" pattern="(\d+)|(\d+[.,]\d+)"
                           onkeyup="getList(this, ['inputCanvasCountry', 'inputCanvasName'])"
                           onfocus="getList(this, ['inputCanvasCountry', 'inputCanvasName'])"
                           onblur="hideItems(this);"
                           placeholder="Введите ширину:" value="<?= (!empty($canvas)) ? $canvas->width : ''; ?>"
                           autocomplete="off" required <?=!$stock?"disabled":"";?>>
                    <div class="lockSelect">
                        <div class="select width"></div>
                    </div>
                </div>
                <div class="input canvasTexture" top="List">
                    <div class="lable">Фактура: </div>
                    <input type="text" name="canvas[texture]" id="inputCanvasTexture" tname="texture_title"
                           parent="textures"
                           onkeyup="getList(this)"
                           onfocus="getList(this)"
                           onblur="hideItems(this);"
                           placeholder="Введите фактуру:"
                           value="<?= (!empty($canvas)) ? $canvas->texture : ''; ?>" autocomplete="off"
                           required disabled>
                    <div class="lockSelect">
                        <div class="select texture_title"></div>
                    </div>
                </div>
                <div class="input canvasColor" top="List">
                    <div class="lable">Цвет: </div>
                    <input type="text" name="canvas[color]" id="inputCanvasColor" tname="title"
                           parent="colors"
                           onkeyup="getList(this, ['inputCanvasTexture'])"
                           onfocus="getList(this, ['inputCanvasTexture'])"
                           onblur="hideItems(this);"
                           placeholder="Введите цвет:" value="<?= (!empty($canvas)) ? $canvas->color : ''; ?>"
                           autocomplete="off"
                           required disabled>
                    <div class="lockSelect">
                        <div class="select title"></div>
                    </div>
                </div>
                <?if(!$stock):?>
                <div class="input canvasPrice" top="List">
                    <div class="lable">Цена: </div>
                    <input type="text" name="canvas[price]" id="inputCanvasPrice" tname="price"
                           parent="canvases"
                           onkeyup="getList(this, ['inputCanvasCountry', 'inputCanvasName'])"
                           onfocus="getList(this, ['inputCanvasCountry', 'inputCanvasName'])"
                           onblur="hideItems(this);" pattern="(\d+)|(\d+[.,]\d+)"
                           placeholder="Введите цену:" value="<?= (!empty($canvas)) ? $canvas->price : ''; ?>"
                           autocomplete="off"
                           required>
                    <div class="lockSelect">
                        <div class="select price"></div>
                    </div>
                </div>
                <?endif;?>
            </div>
            <?if($stock):?>
            <div class="subList inputRollersList">
                <div class="inputRoller">
                    <div class="input rollersQuadrature" top="subList">
                        <div class="lable">Квадратура: </div>
                        <input type="text" min="0" name="canvas[roller][quad]" id="inputRollerQuadrature"
                               tname="lenght" parent="canvases_all"
                               onkeyup="getList(this)"
                               onfocus="getList(this)"
                               onblur="hideItems(this);" pattern="(\d+)|(\d+[.,]\d+)"
                               placeholder="Введите квадратуру:"
                               value="<?= (!empty($roller)) ? $roller->quad : ''; ?>" autocomplete="off"
                               required>
                        <div class="lockSelect">
                            <div class="select lenght"></div>
                        </div>
                    </div>
                    <div class="input rollersPurchasingPrice" top="subList">
                        <div class="lable">Цена: </div>
                        <input type="text" min="0" name="canvas[roller][purchasingPrice]"
                               id="inputRollerPurchasingPrice" tname="purchasing_price"
                               parent="canvases_all"
                               onkeyup="getList(this)"
                               onfocus="getList(this)"
                               onblur="hideItems(this);" pattern="(\d+)|(\d+[.,]\d+)"
                               placeholder="Введите цену за м²:"
                               value="<?= (!empty($roller)) ? $roller->purchasingPrice : ''; ?>" autocomplete="off"
                               required>
                        <div class="lockSelect">
                            <div class="select purchasing_price"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?endif;?>
        </div>
    </div>
    <div class="Complite">
        <button type="submit" class="buttonComplite btn btn-primary"><i class="fa fa-paper-plane" aria-hidden="true">
                Отправить</i>
        </button>
        <a href="<?= $httpref; ?>" class="buttonCancel btn btn-primary">Отмена</a>
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
            case 'inputCanvasPrice':
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