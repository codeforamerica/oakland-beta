$('document').ready(function() {

	$(document).on('click', function(e) {
		if($(e.target).hasClass('filterbox-filter-label')) {
			var currFilter = $(e.target).parent();
			console.log(!$(currFilter).hasClass('filterbox-filter-is-open'));
			if(!$(currFilter).hasClass('filterbox-filter-is-open')) {
				$('.filterbox-filter').removeClass('filterbox-filter-is-open');
				$(currFilter).addClass('filterbox-filter-is-open');
			}
			else {
				$(currFilter).removeClass('filterbox-filter-is-open');
			}
		}

		else if(!$(e.target).closest('.filterbox-drawer').length) {
			$('.filterbox-filter-is-open').removeClass('filterbox-filter-is-open');
		}
	})

})