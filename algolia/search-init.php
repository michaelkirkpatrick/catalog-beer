<script src="https://unpkg.com/@algolia/sitesearch@latest/dist/search.min.js"></script>
<script>
SiteSearch.init({
	container: '#search',
	applicationId: '<?php echo ALGOLIA_APPLICATION_ID; ?>',
	apiKey: '<?php echo ALGOLIA_SEARCH_API_KEY; ?>',
	indexName: 'catalog',
	attributes: {
		primaryText: 'name',
		secondaryText: 'subtitle',
		url: 'page_url'
	},
	placeholder: 'Search brewers, beers, and locations...',
	hitsPerPage: 8
});
</script>
