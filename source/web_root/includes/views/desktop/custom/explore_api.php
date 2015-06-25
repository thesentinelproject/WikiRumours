
	<h2>Explore the API</h2>

	<div class='hidden-xs hidden-sm'><!-- desktop view -->
		<div class='row'>
			<div class='col-md-3'>Current API version:</div>
			<div class='col-md-1'><span class="label label-default">2.0</span>&nbsp;</div>
			<div class='col-md-3 col-md-offset-1'>Daily query limit:</div>
			<div class='col-md-2'><?php
				if (@$logged_in['unlimited_api_queries']) echo "Unlimited";
				else echo $systemPreferences['Maximum API calls'];
			?></div>
			<div class='col-md-2 pull-right'>
<?php 
			if (@$apiKey[0]['hash']) echo "<button class='btn btn-default btn-xs' onClick='document.location.href=" . '"http://api.wikirumours.org/v2-0/' . $apiKey[0]['hash'] . '/rumours/xml/country_id%3DKE%7Cstatus_id%3D1"' . ";'>Test API</button>\n";
			else echo "<button class='btn btn-default btn-xs' onClick='alert(" . '"Please request your API key first!"' . "); return false;'>Test API</button>\n";
?>
			</div>
		</div>
	</div>
	<div class='visible-xs visible-sm'><!-- mobile view -->
		<div class='row'>
			<div class='col-sm-9 col-xs-9'>Current API version:</div>
			<div class='col-sm-3 col-xs-3 pull-right'><span class="label label-default">2.0</span>&nbsp;</div>
		</div>
		<div class='row'>
			<div class='col-sm-9 col-xs-9'>Daily query limit:</div>
			<div class='col-sm-3 col-xs-3 pull-right'><?php
				if (@$logged_in['unlimited_api_queries']) echo "Unlimited";
				else echo $systemPreferences['Maximum API calls'];
			?></div>
		</div><br />
		<div>
<?php 
			if (@$apiKey[0]['hash']) echo "<button class='btn btn-default btn-block' onClick='document.location.href=" . '"http://api.wikirumours.org/v2-0/' . $apiKey[0]['hash'] . '/rumours/xml/country_id%3DKE%7Cstatus_id%3D1"' . ";'>Test API</button>\n";
			else echo "<button class='btn btn-default btn-block' onClick='alert(" . '"Please request your API key first!"' . "); return false;'>Test API</button>\n";
?>
		</div>
	</div>
		
	<br />
	
<?php
	$slug = 'api_intro';
	include 'includes/views/shared/cms_block.php';
?>

	<br />
	<div class="tabbable">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#v2" data-toggle="tab">Version 2.0</a></li>
			<li><a href="#v1" data-toggle="tab">Version 1.0</a></li>
			<li><a href="#codes" data-toggle="tab">Warning, error and status codes</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="v2"><br /><?php
				$slug = 'api_v2';
				include 'includes/views/shared/cms_block.php';
			?></div>
			<div class="tab-pane" id="v1"><br /><?php echo $apiErrorCodes[6]; ?></div>
			<div class="tab-pane" id="codes"><br />
				<h4>Warning codes</h4>
				
				<table class="table table-condensed table-hover">
<?php 
				foreach ($apiWarningCodes as $code => $meaning) {
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
				foreach ($apiErrorCodes as $code => $meaning) {
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

				<h4>Priority codes</h4>
				
				<table class="table table-condensed table-hover">
<?php 
				foreach ($rumourPriorities as $code => $meaning) {
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
