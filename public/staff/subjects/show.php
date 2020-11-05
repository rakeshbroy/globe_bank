<?php require_once('../../../private/initialize.php'); ?>
<?php

require_login();

$id = $_GET['id'] ?? '1';

$subject = find_subject_by_id($id);
$page_set = find_page_by_subject_id($id);

?>
<?php $page_title = 'Show Subject'; ?>
<?php include(SHARED_PATH . '/staff_header.php'); ?>

<div id="content">
	<a class="back-link" href="<?= url_for('/staff/subjects/index.php'); ?>">&laquo;Back to List</a>
	<div class="Subject show">
		<h1>Subject: <?php echo h($subject['menu_name']); ?></h1>

		<div class="attributes">
			<dl>
				<dt>Menu Name</dt>
				<dd><?php echo h($subject['menu_name']); ?></dd>
			</dl>
			<dl>
				<dt>Position</dt>
				<dd><?php echo h($subject['position']); ?></dd>
			</dl>
			<dl>
				<dt>Visible</dt>
				<dd><?php echo $subject['visible'] == '1' ? 'true' : 'false'; ?></dd>
			</dl>
		</div>
	</div>

	<hr />

	<?php  

	// Page listing related to subject id

	?>

	<div class="Pages listing">
	<h2>Pages</h2>

	<div class="actions">
		<a class="action" href="<?php echo url_for('/staff/pages/new.php?id=' . u(h($subject['id']))); ?>">Create New Page</a>
	</div>
	
	<table class="list">
		<tr>
			<th>ID</th>
			<th>Position</th>
			<th>Visible</th>
			<th>Name</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
		</tr>

		<?php while($page = mysqli_fetch_assoc($page_set)) :?>
			<?php $subject = find_subject_by_id($page['subject_id']); ?>
			<tr>
				<td><?= h($page['id']) ?></td>
				<td><?= h($page['position']) ?></td>
				<td><?= $page['visible'] == 1 ? 'true' : 'false' ?></td>
				<td><?= h($page['menu_name']) ?></td>
				<td><a class="action" href="<?= url_for('/staff/pages/show.php?id=' . h(u($page['id']))); ?>">View</a></td>
				<td><a class="action" href="<?= url_for('/staff/pages/edit.php?id=' . h(u($page['id']))); ?>">Edit</a></td>
				<td><a class="action" href="<?= url_for('/staff/pages/delete.php?id=' . h(u($page['id']))); ?>">Delete</a></td>
			</tr>
		<?php endwhile; ?>
	</table>

	<?php

	mysqli_free_result($page_set);

	?>

</div>
</div>

<?php include(SHARED_PATH . '/staff_footer.php'); ?>