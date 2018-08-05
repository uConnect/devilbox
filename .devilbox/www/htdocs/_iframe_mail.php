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
<html lang="en" style="background-color:#FFFFFF;">
	<head>
		<?php echo loadClass('Html')->getHead(true); ?>
	</head>

	<body style="margin-bottom: 0 !important">
					<table class="table table-striped table-hover" id="emails">
						<thead class="thead-inverse">
							<tr>
								<th>#</th>
								<th>Date <?php echo $orderDate;?></th>
								<th>From <?php echo $orderFrom;?></th>
								<th>To <?php echo $orderTo;?></th>
								<th>Subject <?php echo $orderSubj;?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($messages as $data): ?>
								<?php
									$message = htmlentities($data['raw']);
									$structure = $data['decoded'];
								 ?>
								<tr id="<?php echo $data['num'];?>" class="subject">
									<td><?php echo $data['num'];?></td>
									<td>
										<?php echo date('H:i', strtotime($structure->headers['date']));?><br/>
										<small><?php echo date('Y-m-d', strtotime($structure->headers['date']));?></small>
									</td>
									<td><?php echo htmlentities($structure->headers['from']);?></td>
									<td><?php echo htmlentities($structure->headers['x-original-to']);?></td>
									<td><?php echo htmlentities($structure->headers['subject']);?></td>
								</tr>
								<tr></tr>
								<tr id="mail-<?php echo $data['num'];?>" style="display:none">
									<td></td>
									<td colspan="4">
										<?php if (isset($structure->body)): ?>
											<?php echo ($structure->body) ?>
										<?php elseif(isset($structure->parts[1]->body)): ?>
											<?php echo ($structure->parts[1]->body) ?>
										<?php elseif(isset($structure->parts[0]->body)): ?>
											<?php echo ($structure->parts[0]->body) ?>
										<?php else: ?>
											<?php echo '<div class="alert alert-warning" role="alert">
												No valid body found
											</div>' ?>
										<?php endif; ?>
										<hr>
										<p><a class="btn btn-primary" data-toggle="collapse" href="#email-<?php echo $data['num'];?>" aria-expanded="false" aria-controls="email-<?php echo $data['num'];?>">Raw source</a></p>
										<div class="collapse" id="email-<?php echo $data['num'];?>"><pre><?php echo $message;?></pre></div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

		<?php echo loadClass('Html')->getFooterScripts(); ?>
		<script>
		$(function() {
			$('.subject').each(function() {
				$(this).click(function() {
					var id = ($(this).attr('id'));
					$('#mail-'+id).toggle();

				})
			})
			// Handler for .ready() called.
		});
		</script>
	</body>
</html>
