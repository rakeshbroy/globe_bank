
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Globe Bank <?php if(isset($page_title)) { echo '- ' . h($page_title); } ?>
	<?php if(isset($preview) && $preview) { echo ' [PREVIEW]'; } ?>
	</title>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo url_for('/stylesheets/public.css'); ?>" media="all">
</head>
<body>
	<header>

		<h1>
			<a href="<?php echo url_for('/index.php'); ?>">
				<img src="<?php echo url_for('/images/gbi_logo.png'); ?>" height="71" width="298" alt="">
			</a>
		</h1>

	</header>
