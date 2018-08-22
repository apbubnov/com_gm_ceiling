<?php
?>
<?=parent::getButtonBack();?>

<table class="table table-striped one-touch-view table_cashbox " id="projectList">
		<thead>
			<tr>
				<th class='center' width=5%>
					№
				</th>
				<th class='center' width=20%>
					Желаемая дата монтажа
				</th>
				<th class='center' width=40%>
					Адрес
				</th>
				<th class='center' width=10%>
					Телефон
				</th>
				<th class='center' width=14%>
					Дилер
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($this->items as $item){
				$dealer = JFactory::getUser($item->dealer_id);
			?>
				<tr style = "cursor: pointer;" data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmchief&subtype=service&id=' . $item->id); ?>">
					<td class='center one-touch'>
						<?php echo $item->id;?>
					</td>
					<td class='center one-touch'>
						<?php echo str_replace(',', ',<br>',$item->project_mounting_date);?>
					</td>
					<td class="one-touch">
						<?php echo $item->project_info;?>
					</td>
					
					<td class='center one-touch'>
						<?php echo $dealer->username;?>
					</td>
					<td class='center one-touch'>
						<?php echo $dealer->name; ?>
					</td>
				</tr>
			<?php }?>
		</tbody>
	</table>