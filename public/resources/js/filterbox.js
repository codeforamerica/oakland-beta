$('document').ready(function() {
	$('.filterbox-filter-label').click(function(e) {
		var currFilter = $(this).parent();
		if($(currFilter).hasClass('filterbox-filter-is-open')) {
			$(currFilter).removeClass('filterbox-filter-is-open');
		}
		else {
			$('.filterbox-filter').removeClass('filterbox-filter-is-open');
			$(currFilter).addClass('filterbox-filter-is-open');
		}
	})
})