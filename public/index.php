<?php require_once('../private/initialize.php'); ?>

<?php

	$preview = false;
	if(isset($_GET['preview'])) {
		// Previewing should require admin to log in
		$preview = $_GET['preview'] && is_logged_in() == 'true' ? true : false;
	}
	$visible = !$preview;

	if(isset($_GET['id'])) {
		$page_id = $_GET['id'];
		$page = find_page_by_id($page_id, ['visible' => $visible]);
		if(!$page) {
			redirect_to(url_for('/index.php'));	
		}
		$subject_id = $page['subject_id'];
		$subject = find_subject_by_id($subject_id, ['visible' => $visible]);
		if(!$subject) {
			redirect_to(url_for('/index.php'));
		}
	} elseif(isset($_GET['subject_id'])) {
		$subject_id = $_GET['subject_id'];
		$subject = find_subject_by_id($subject_id, ['visible' => $visible]);
		if(!$subject) {
			redirect_to(url_for('/index.php'));
		}
		$page_set = find_page_by_subject_id($subject_id, ['visible' => $visible]);
		$page = mysqli_fetch_assoc($page_set);	// first page
		mysqli_free_result($page_set);
		if(!$page) {
			redirect_to(url_for('/index.php'));
		}
		$page_id = $page['id'];

	} else {
		// nothing selected; show the homepage 
	}


?>

<?php include(SHARED_PATH . '/public_header.php'); ?>

<div id="main">

	<?php include(SHARED_PATH . '/public_navigation.php'); ?>

	<div id="page">

		<?php 
			if(isset($page)) {
				// Show the page from the database
				$allowed_tags = "<div><img><h1><h2><p><br><strong><em><ul><li>";
				echo strip_tags($page['content'], $allowed_tags);

			} else {
				// Show the homepage
				// The homepage content could:
				// * be static content (here or in the shared file)
				// * show the first page from the nav
				// * be in the database but add code to hide in the nav
				include(SHARED_PATH . '/static_homepage.php'); 	
			}
		?> 
	</div>
	
</div>


<?php include(SHARED_PATH . '/public_footer.php'); ?>