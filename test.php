<?php
use Yohns\GPT\gui\BootstrapChainer;
include __DIR__.'/GPT/gui/BootstrapChainer.php';

?>
<!doctype html bs-mode="dark">
<html lang="en">
<head>
	<title>YoMySQL</title>
	<!-- Required meta tags -->
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
</head>
<body>
<header>
	<!-- place navbar here -->
</header>
<main>
	<div class="row justify-content-center align-items-center g-2">
		<div class="col-3">
			<?php
			$list = new BootstrapChainer;

			?>
		</div>
		<div class="col-9">

		</div>
	</div>

</main>
<footer>
	<!-- place footer here -->
</footer>
<!-- Bootstrap JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundled.min.js"></script>
</body>
</html>
