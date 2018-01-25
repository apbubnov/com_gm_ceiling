<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2017 al.p.bubnov@gmail.com>
 * @copyright  2017 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$user       = JFactory::getUser();
$userId     = $user->get('id');

?>
<?=parent::getButtonBack();?>
<h3 class = "center">Номера телефонов</h3>
<style type="text/css">
    .analitic-table {
        color: #414099;
    }
    .analitic-table td {
        border-bottom: 1px solid #d3d3f9;
    }
    .analitic-table th {
        background-color: #d3d3f9;
        color: #414099;
    }
    input {
        color: #414099;
        border: 1px solid #414099;
    }

</style>

<table class = "analitic-table" style="margin-top: 2em"> 
    <thead class = "caption-style-tar">
        <th>ID</th>
        <th>Реклама</th>
        <th>Номер</th>
        <th>Описание</th>
        <th>Сайт</th>
	</thead> 

    <?php foreach($this->item as $item){?>
    <tr>
        <td>
           <?php echo $item->id;?>
        </td>
        <td>
            <?php echo $item->name;?>
        </td>
        <td>
            <?php echo $item->number;?>
        </td>
        <td>
            <input value="<?php echo $item->description;?>" type="text" onkeyup="text_description_update(this, <?php echo $item->id;?>)">
        </td>
         <td>
            <?php echo $item->site;?>
        </td>
    </tr>
    <?php }?>
</table>
<br>
<center><button id="save" type="button" class="btn btn-large btn-primary">Сохранить</button></center>
<script type="text/javascript">
    var descriptions = [];
    function text_description_update(e, id)
    {
        descriptions[id] = e.value;
    }
    document.getElementById('save').onclick = function()
    {
        jQuery.ajax({
            url: "/index.php?option=com_gm_ceiling&task=update_descriptions_api_phones",
            async: false,
            data:
            {
                descriptions: descriptions
            },
            type:"post",
            success: function(data)
            {
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Сохранено"
                });
            },
            dataType: "json",
            timeout: 10000,
            error: function(data){
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }
        });
    };
</script>