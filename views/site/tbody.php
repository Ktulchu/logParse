<?php foreach($total as $key => $col) : ?>
  <tr data-date="<?php echo $key; ?>" data-requests="<?php echo $col['total_requests']; ?>" data-url="<?php echo $col['total_url'];?>" data-brous="<?php echo $col['total_brous'];?>">
	<td><?php echo $key; ?></td>
	<td><?php echo $col['total_requests']; ?></td>
	<td><?php echo ($col['total_requests'] > 0) ? $col['total_url'] : ''; ?></td>
	<td><?php echo ($col['total_requests'] > 0) ? $col['total_brous'] : ''; ?></td>
  </tr>
<?php endforeach; ?>
<style>
th.asc,
th.desc {
    position: relative;
}

th.desc::after,
th.asc::after {
    right: 8px;
    position: absolute;
}

th.desc::after {
	content: "▼"
}

th.asc::after {
	content: "▲"
}
</style>

