var actickets = {
	tickets: [],
	pattern: /\/projects\/(\d+)\/tickets\/(\d+)/,

	init: function() {
		// get all ticket links
		actickets.tickets = getElementsByClass('acticket', document, 'a');
		if (!actickets.tickets.length) return;

		// parse projectId and ticketId
		for (var n = 0; n < actickets.tickets.length; n++) {
			var ticket = actickets.tickets[n];

			matches = ticket.href.match(actickets.pattern);
			if (matches) {
				ticket.acticket = true;
				ticket.projectId = matches[1];
				ticket.ticketId = matches[2];
			} else {
				ticket.acticket = false;
			}
		}

		// create ajax query
		var ajax = new sack(DOKU_BASE + 'lib/plugins/actickets/ajax.php');
		ajax.AjaxFailedAlert = '';
		ajax.encodeURIString = false;
		ajax.onCompletion = actickets.resolved;

		var request = '';
		var r = 0;
		for (var n = 0; n < actickets.tickets.length; n++) {
			var ticket = actickets.tickets[n];
			if (ticket.acticket) {
				request = request
					+ 'tickets[' + r + '][projectId]=' + ticket.projectId + '&'
					+ 'tickets[' + r + '][ticketId]=' + ticket.ticketId + '&';
				r++;
			}
		}

		ajax.runAJAX(request);
	},

	resolved: function() {
		result = eval('(' + this.response + ')');
		for (var n = 0; n < result.length; n++) {
			var ticket = result[n];
			if (ticket != null) {
				for (var t = 0; t < actickets.tickets.length; t++) {
					var acticket = actickets.tickets[t];
					if (acticket.projectId == ticket.project_id
						&& acticket.ticketId == ticket.ticket_id) {
						// informations for a linked ticket received
						acticket.title = ticket.name;
						if (ticket.priority == 1) {
							acticket.className = acticket.className + ' acticket_prio';
							acticket.className = acticket.className + ' acticket_prio_high';
						} else if (ticket.priority == 2) {
							acticket.className = acticket.className + ' acticket_prio';
							acticket.className = acticket.className + ' acticket_prio_highest';
						} else if (ticket.priority == -1) {
							acticket.className = acticket.className + ' acticket_prio';
							acticket.className = acticket.className + ' acticket_prio_low';
						} else if (ticket.priority == -2) {
							acticket.className = acticket.className + ' acticket_prio';
							acticket.className = acticket.className + ' acticket_prio_lowest';
						}
						if (ticket.completed_on != null) {
							acticket.className = acticket.className + ' acticket_completed';
						}
					}
				}
			}
		}
	}
};

addInitEvent(actickets.init);
