$(function () {

	$('#columns_order').sortable({
		cursor: 'move'
//		,grid: [ 20, 10 ]
		,handle: '.grip'
		,start: function (event, ui) {
			var field = ui.helper.data('field');
			if (field !== 'ignored') {
				var self = $(this).sortable('instance');
				var count = 0;
				for (var i = 0; i < self.items.length; i++) {
					if (self.items[i].item.data('field') === field) count++;
				}

				if (count > 1) {
					ui.helper.addClass('toRemove');
					ui.sender.draggable('cancel'); // Déclanche une erreur (non impactante) dans la console car ui.sender is null, mais à ne pas prendre en compte si on veut conserver l'animation de "retour"
				}
			}
		}
		,receive: function (event, ui) {

		}
		,stop: function (event, ui) {
			if (ui.item.hasClass('toRemove')) ui.item.remove();
		}
	});

	$('#columns_available li').draggable({
		connectToSortable: '#columns_order'
		,helper: 'clone'
		,revert: "invalid"
	});

	$('#columns_order, columns_available').disableSelection();


	removeElementPI = function (obj) {
		$(obj).parent('li').remove();
	};
});