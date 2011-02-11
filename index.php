<?php
	require_once('./include/db.php');

	ob_start("ob_gzhandler");

	$host = $_SERVER['HTTP_HOST'];
	$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$base_url = 'http://'.$host.$uri.'/';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
	<head>
		<title><?php echo POLL_NAME; ?></title>

		<meta http-equiv="content-type" content="text/html; charset=utf-8">

		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
		<script type="text/javascript" src="./js/protovis-r3.3.js"></script>

		<link rel="stylesheet" type="text/css" href="./css/reset.css" media="screen">
		<link rel="stylesheet" type="text/css" href="./css/main.css" media="screen">
	</head>
	<body>
		<div id="container">
			<h1>Text team number to <strong><?php echo PHONE_NUMBER ?></strong></h1>
			<div id="results">
				<script type="text/javascript+protovis">
					<?php
						$db = new DB();
						
						$teams = $db->get_teams();

						$images = json_encode($logos);

						foreach ($teams as $team)
						{
							$data[] = (int) $team['votes'];
						}

						$data = json_encode($data);
					?>

					var images = <?php echo $images; ?>;

					var data = <?php echo $data; ?>;

					/* Protovis wizardy by Will Light (http://williamlight.net) */

					/* Sizing and scales. */
					var w = 700,
						h = 400,
						x = pv.Scale.linear(0, (pv.max(data) == 0 ? 1 : pv.max(data))).range(0, w),
						y = pv.Scale.ordinal(pv.range(data.length)).splitBanded(0, h, 4/5);

					/* The root panel. */
					var vis = new pv.Panel()
						.width(w)
						.height(h)
						.bottom(20)
						.left(90)
						.right(40)
						.top(5);

					/* The bars. */
					var bar = vis.add(pv.Bar)
						.data(function() data)
						.top(function() y(this.index))
						.height(y.range().band)
						.left(60)
						.width(x);

					/* Y-axis label */
					vis.add(pv.Label)
						.data(["Team Number"])
						.left(-63)
						.bottom(h/2)
						.font("30px Helvetica")
						.textAlign("center")
						.textAngle(-Math.PI/2);

					/* The variable label. */
					var what = bar.anchor("left")
						.add(pv.Bar)
						.width(function() this.root.left())
						.height(y.range().band)
						.top(function() y(this.index))
						.fillStyle("rgba(0, 0, 0, 0)");

					bar.anchor("left").add(pv.Label)
						.textMargin(5)
						.left(55)
						.textAlign("right")
						.font("bold 30px Helvetica")
						.text(function() this.index + 1);

					what.add(pv.Image)
						.left(-40)
						.top(function() y(this.index) + ((y.range().band / 2) - (this.height() / 2)))
						.width(64)
						.height(64)
						.url(function() images[this.index]);

					vis.render();
					getData();

					function getData() {
						$.getJSON ("<?php echo $base_url.'get_votes.php'; ?>", function (d) {
							data = d;
							x = pv.Scale.linear(0, (pv.max(data) == 0 ? 1 : pv.max(data))).range(0, w);
							bar.width(x);

							vis.transition()
								.duration(500)
								.ease("elastic-out")
								.start();
						});
					} 

					setInterval(function() { getData(); }, 2000);
				</script>
			</div>
			<p><img src="./images/twilio_logo.png"></p>
		</div>
	</body>
</html>
