<?php 

require_once('../../../private/initialize.php'); 

require_login();

if(!isset($_GET['id'])) {
	redirect_to(url_for('/staff/admins/index.php'));
}

$id = $_GET['id'];


if(is_post_request()) {

	$result = delete_admin($id);
	$_SESSION['message'] = 'The admin was deleted successfully';
	redirect_to(url_for('/staff/admins/index.php'));
	
} else {
	
	$admin = find_admin_by_id($id);

}

?>


<?php $page_title = "Delete admin"; ?>
<?php include(SHARED_PATH . '/staff_header.php'); ?>


<div id="content">
	<a href="<?php echo url_for('/staff/admins/index.php'); ?>" class="back-link">&laquo;Back to List</a>

	<div class="admin delete">
		<h1>Delete Admin</h1>
		<p>Are you sure you want to delete this admin?</p>
		<p class="item"><?php echo h($admin['username']); ?></p>

		<form action="<?php echo url_for('/staff/admins/delete.php?id=' . h(u($admin['id']))); ?>" method="post">

			<div id="operations">
				<input type="submit" name="commit" value="Delete Admin">
			</div>
		</form>
	</div>

</div>


<?php require_once(SHARED_PATH . '/staff_footer.php'); ?>