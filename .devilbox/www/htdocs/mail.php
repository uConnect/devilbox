<?php require '../config.php';  ?>
<?php loadClass('Helper')->authPage(); ?>
<?php

//
// $_POST submit for sending a test email
//
if (isset($_GET['email']) && isset($_GET['subject']) && isset($_GET['message'])) {
	$mail = $_GET['email'];
	$subj = $_GET['subject'];
	$mess = $_GET['message'];
	if (! mail($mail, $subj, $mess)) {
		loadClass('Logger')->error('Could not send mail to: '.$mail.' | subject: '.$subj);
	}
	header('Location: /mail.php');
	exit();
}

//
// Includes
//
require $VEN_DIR . DIRECTORY_SEPARATOR . 'Mail' . DIRECTORY_SEPARATOR .'Mbox.php';
require $VEN_DIR . DIRECTORY_SEPARATOR . 'Mail' . DIRECTORY_SEPARATOR .'mimeDecode.php';
require $LIB_DIR . DIRECTORY_SEPARATOR . 'Mail.php';
require $LIB_DIR . DIRECTORY_SEPARATOR . 'Sort.php';

//
// Setup Sort/Order
//

// Sort/Order settings
$defaultSort	= array('sort' => 'date', 'order' => 'DESC');
$allowedSorts	= array('date', 'subject', 'x-original-to', 'from');
$allowedOrders	= array('ASC', 'DESC');
$GET_sortKeys	= array('sort' => 'sort', 'order' => 'order');

// Get sort/order
$MySort = new \devilbox\Sort($defaultSort, $allowedSorts, $allowedOrders, $GET_sortKeys);
$sort = $MySort->getSort();
$order = $MySort->getOrder();

// Evaluate Sorters/Orderers
$orderDate	= '<a href="/mail.php?sort=date&order=ASC"><i class="fa fa-sort" aria-hidden="true"></i></a>';
$orderFrom	= '<a href="/mail.php?sort=from&order=ASC"><i class="fa fa-sort" aria-hidden="true"></i></a>';
$orderTo	= '<a href="/mail.php?sort=x-original-to&order=ASC"><i class="fa fa-sort" aria-hidden="true"></i></a>';
$orderSubj	= '<a href="/mail.php?sort=subject&order=ASC"><i class="fa fa-sort" aria-hidden="true"></i></a>';

if ($sort == 'date') {
	if ($order == 'ASC') {
		$orderDate = '<a href="/mail.php?sort=date&order=DESC"><i class="fa fa-sort" aria-hidden="true"></i></a> <i class="fa fa-sort-numeric-asc" aria-hidden="true"></i>';
	} else {
		$orderDate = '<a href="/mail.php?sort=date&order=ASC"><i class="fa fa-sort" aria-hidden="true"></i></a> <i class="fa fa-sort-numeric-desc" aria-hidden="true"></i> ';
	}
} else if ($sort == 'subject') {
	if ($order == 'ASC') {
		$orderSubj = '<a href="/mail.php?sort=subject&order=DESC"><i class="fa fa-sort" aria-hidden="true"></i></a> <i class="fa fa-sort-alpha-asc" aria-hidden="true"></i>';
	} else {
		$orderSubj = '<a href="/mail.php?sort=subject&order=ASC"><i class="fa fa-sort" aria-hidden="true"></i></a> <i class="fa fa-sort-alpha-desc" aria-hidden="true"></i>';
	}
} else if ($sort == 'x-original-to') {
	if ($order == 'ASC') {
		$orderTo = '<a href="/mail.php?sort=x-original-to&order=DESC"><i class="fa fa-sort" aria-hidden="true"></i></a> <i class="fa fa-sort-alpha-asc" aria-hidden="true"></i>';
	} else {
		$orderTo = '<a href="/mail.php?sort=x-original-to&order=ASC"><i class="fa fa-sort" aria-hidden="true"></i></a> <i class="fa fa-sort-alpha-desc" aria-hidden="true"></i>';
	}
} else if ($sort == 'from') {
	if ($order == 'ASC') {
		$orderFrom = '<a href="/mail.php?sort=from&order=DESC"><i class="fa fa-sort" aria-hidden="true"></i></a> <i class="fa fa-sort-alpha-asc" aria-hidden="true"></i>';
	} else {
		$orderFrom = '<a href="/mail.php?sort=from&order=ASC"><i class="fa fa-sort" aria-hidden="true"></i></a> <i class="fa fa-sort-alpha-desc" aria-hidden="true"></i>';
	}
}


//
// Mbox Reader
//
$MyMbox = new \devilbox\Mail('/var/mail/devilbox');

// If default sort is on, use NULL, so we do not have to sort the mails after retrieval,
// because they are being read in the default sort/order anyway
$sortOrderArr = $MySort->isDefault($sort, $order) ? null : array($sort => $order);
$messages = $MyMbox->get($sortOrderArr);

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo loadClass('Html')->getHead(true); ?>
	</head>

	<body>
		<?php echo loadClass('Html')->getNavbar(); ?>

		<div class="container">
			<h1>Mail</h1>
			<br/>
			<br/>

			<div class="row">
				<div class="col-md-12">
					<h3>Send test Email</h3>
					<br/>
				</div>
			</div>


			<div class="row">
				<div class="col-md-12">

					<form class="form-inline">
						<div class="form-group">
							<label class="sr-only" for="exampleInputEmail1">Email to</label>
							<input name="email" type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter to email">
						</div>

						<div class="form-group">
							<label class="sr-only" for="exampleInputEmail2">Subject</label>
							<input name="subject" type="text" class="form-control" id="exampleInputEmail2" placeholder="Subject">
						</div>

						<div class="form-group">
							<label class="sr-only" for="exampleInputEmail3">Message</label>
							<input name="message" type="text" class="form-control" id="exampleInputEmail3" placeholder="Message">
						</div>

						<button type="submit" class="btn btn-primary">Send Email</button>
					</form>
					<br/>
					<br/>

				</div>
			</div>


			<div class="row">
				<div class="col-md-12">
					<h3>Received Emails</h3>
					<br/>
				</div>
			</div>

			<iframe seamless="seamless" id="received" src="/_iframe_mail.php" frameborder="0" scrolling="no" height="100%" width="100%" style="width:100%; height:100%;margin:0px;border:0px;"></iframe>





		</div><!-- /.container -->
		<?php echo loadClass('Html')->getFooter(); ?>
		<script>
		$(function() {
			// Add click event to iframe
			var iframe = $('#received');
			iframe.on('load', function() {
				resize();
				iframe.contents().click(function (event) {
					iframe.trigger('click');
				});
			});
			function resize() {
				var table	= iframe.contents().find("#emails");
				var height	= table.height();
				iframe.css("height", height + "px");
			};

			// iframe onclick
			iframe.click(function () {
				resize();

				setTimeout(function() {
					resize();
				}, 500);
			});
		});

		</script>
	</body>
</html>
