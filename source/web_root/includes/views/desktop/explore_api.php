<?php 
	include 'includes/views/desktop/shared/page_top.php';
?>

	<h2>Explore the API</h2>

	<div class='hidden-xs hidden-sm'><!-- desktop view -->
		<div class='row'>
			<div class='col-md-3'>Current API version:</div>
			<div class='col-md-1'><span class="label label-default">1.0</span>&nbsp;</div>
			<div class='col-md-3 col-md-offset-1'>Daily query limit:</div>
			<div class='col-md-2'><?php
				if (@$logged_in['unlimited_api_queries']) echo "Unlimited";
				else echo $apiCap;
			?></div>
			<div class='col-md-2 pull-right'>
<?php 
			if (@$apiKey[0]['hash']) echo "<button class='btn btn-default btn-xs' onClick='document.location.href=" . '"http://api.unahakika.org/v1-0/' . $apiKey[0]['hash'] . '/rumours/xml/country%3DKE%7Cstatus%3DNU"' . ";'>Test API</button>\n";
			else echo "<button class='btn btn-default btn-xs' onClick='alert(" . '"Please request your API first!"' . "); return false;'>Test API</button>\n";
?>
			</div>
		</div>
	</div>
	<div class='visible-xs visible-sm'><!-- mobile view -->
		<div class='row'>
			<div class='col-sm-9 col-xs-9'>Current API version:</div>
			<div class='col-sm-3 col-xs-3 pull-right'><span class="label label-default">1.0</span>&nbsp;</div>
		</div>
		<div class='row'>
			<div class='col-sm-9 col-xs-9'>Daily query limit:</div>
			<div class='col-sm-3 col-xs-3 pull-right'><?php
				if (@$logged_in['unlimited_api_queries']) echo "Unlimited";
				else echo $apiCap;
			?></div>
		</div><br />
		<div>
<?php 
			if (@$apiKey[0]['hash']) echo "<button class='btn btn-default btn-block' onClick='document.location.href=" . '"http://api.unahakika.org/v1-0/' . $apiKey[0]['hash'] . '/rumours/xml/country%3DKE%7Cstatus%3DNU"' . ";'>Test API</button>\n";
			else echo "<button class='btn btn-default btn-block' onClick='alert(" . '"Please request your API first!"' . "); return false;'>Test API</button>\n";
?>
		</div>
	</div>
		
	<br />
	
<?php echo retrieveContentBlock('api_intro');; ?>

	<br />
	<div class="tabbable">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#tab3" data-toggle="tab">Version 1.0</a></li>
			<li><a href="#tabError" data-toggle="tab">Warning, error and status codes</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="tab3"><?php echo retrieveContentBlock('api_v1'); ?></div>
			<div class="tab-pane" id="tabError">
				<h4>Warning codes</h4>
				
				<table class="table table-condensed table-hover">
<?php 
				foreach ($warningCodes as $code => $meaning) {
					echo "<tr>\n";
					echo "<th>" . $code . "</th>\n";
					echo "<td>" . $meaning . "</td>\n";
					echo "</tr>\n";
				}
?>
				</table>

				<h4>Error codes</h4>
				
				<table class="table table-condensed table-hover">
<?php 
				foreach ($errorCodes as $code => $meaning) {
					echo "<tr>\n";
					echo "<th>" . $code . "</th>\n";
					echo "<td>" . $meaning . "</td>\n";
					echo "</tr>\n";
				}
?>
				</table>
				
				<h4>Status codes</h4>
				
				<table class="table table-condensed table-hover">
<?php 
				foreach ($rumourStatuses as $code => $meaning) {
					echo "<tr>\n";
					echo "<th>" . $code . "</th>\n";
					echo "<td>" . $meaning . "</td>\n";
					echo "</tr>\n";
				}
?>
				</table>
			</div>
		</div>
	</div>

<?php 
	include 'includes/views/desktop/shared/page_bottom.php';
?>