$(function(){

		$('.res  tr,h3,table').filter(function(){
			return (
				$(this).attr('data-path').contains('subtrim') ||
				$(this).attr('data-path').contains('cyclic_ring_range') ||
				$(this).attr('data-path').contains('servo_travel_correction') ||
				$(this).attr('data-path').contains('rudder_end_points_no_break') ||
				$(this).attr('data-path').contains('channels') ||
				$(this).attr('data-path').contains('correction') ||
				$(this).attr('data-path').contains('limits')
				 );
		}
		).hide();

});
