var invoice = {};

invoice.recount = function() {
	$('[data-invoice-item-id]').each(function() {
		var $item = $(this);
		var price_per_unit = $item.find('[name="price_per_unit"]').val();
		var units = $item.find('[name="units"]').val();
		var total = $item.find('[name="total"]');

		price_per_unit = parseFloat(price_per_unit.length ? price_per_unit : 0);
		units = parseFloat(units.length ? units : 0);

		total.val(price_per_unit * units);
	});
	var total = 0;
	$('[name="total"]').each(function() {
		var $item = $(this);
		var total_item = $item.val().length ? $item.val() : 0;
		total = total + parseFloat(total_item);
	});
	$('[name="price"]').val(total);
}

$(function() {
	$(document).on('keyup', '[name="price_per_unit"], [name="units"]', function() {
		invoice.recount();
	});
});
