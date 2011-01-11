var actickets = {
	tickets: [],  // list of all ticket links
	requests: [],  // list of all ticket requests we have to issue
	requested: 0,  // count of requests we already fetched from the requests list
	batch: 15,  // count of requests to made with one ajax request
	pattern: /\/projects\/(\d+)\/tickets\/(\d+)/,
	ajax: null,

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

				// not listed for request?
				var found = false;
				for (var r = 0; r < actickets.requests.length; r++) {
					req = actickets.requests[r];
					if (req.ticketId == ticket.ticketId && req.projectId == ticket.projectId) {
						found = true;
						break;
					}
				}
				if (!found) {
					actickets.requests[actickets.requests.length] = {
						ticketId: ticket.ticketId,
						projectId: ticket.projectId
					};
				}
			} else {
				ticket.acticket = false;
			}
		}

		// prepare ajax client
		actickets.ajax = new sack(DOKU_BASE + 'lib/plugins/actickets/ajax.php');
		actickets.ajax.AjaxFailedAlert = '';
		actickets.ajax.encodeURIString = false;
		actickets.ajax.onCompletion = actickets.resolved;

		// start requests
		actickets.request();
	},

	request: function() {
		// create ajax query
		var request = '';

		// ask for the next batchszie of requests
		var end = actickets.requested + actickets.batch;
		if (end > actickets.requests.length) {
			end = actickets.requests.length;
		}
		var r = 0;
		for (var n = actickets.requested; n < end; n++) {
			var ticket = actickets.requests[n];
			request = request
				+ 'tickets[' + r + '][projectId]=' + ticket.projectId + '&'
				+ 'tickets[' + r + '][ticketId]=' + ticket.ticketId + '&';
			r++;
		}
		actickets.requested = end;

		actickets.ajax.runAJAX(request);
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
		if (actickets.requested < actickets.requests.length) {
			actickets.request();
		}
	}
};

addInitEvent(actickets.init);
