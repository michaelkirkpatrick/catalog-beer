<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Catalog.beer: The Internet\'s Beer Database');
echo $htmlHead->html;

// Required Classes
$api = new API();
?>
<body>
<style>
	@media only screen and (max-width: 768px) {
			.card-addPad {
					margin-top:1rem;
			}
	}
</style>
	
	<?php echo $nav->navbar(''); ?>
	<div class="container">
    <div class="row">
    	<div class="col">
    		<img src="/images/logo-black.svg" class="img-fluid" style="width: 100% \9; margin:0 auto; display: block; padding-top:3rem; padding-bottom:3rem; width:150px;">
				<h1 class="text-center" style="margin-bottom:2rem;"><small>Catalog.beer</small><br/>The Internet&#8217;s Beer Database</h1>
			</div>
		</div>
		<div class="row">
			<div class="col"></div>
			<div class="col-md-3">
				<div class="card">
					<div class="card-body">
						<h4 class="card-title">Brewers</h4>
						<?php
						// Get Number of Brewers
						$brewerResp = $api->request('GET', '/brewer/count', '');
						$brewerCount = json_decode($brewerResp);
						if(isset($brewerCount->value)){
							echo '<p class="card-text">Browse ' . number_format($brewerCount->value) . ' brewers.</p>' . "\n";
						}
						
						// Links
						echo '<a href="/brewer" class="card-link">Browse</a>';
						echo '<a href="/brewer/add" class="card-link">Add</a>';
						?>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card card-addPad">
					<div class="card-body">
						<h4 class="card-title">Beer</h4>
						<?php
						// Get Number of Brewers
						$beerResp = $api->request('GET', '/beer/count', '');
						$beerCount = json_decode($beerResp);
						if(isset($beerCount->value)){
							echo '<p class="card-text">Browse ' . number_format($beerCount->value) . ' beers.</p>' . "\n";
						}
						
						// Links
						echo '<a href="/beer" class="card-link">Browse</a>';
						?>
					</div>
				</div>
			</div>
			<div class="col"></div>
		</div>
  </div>
  <?php echo $nav->footer(); ?> 
</body>
</html>