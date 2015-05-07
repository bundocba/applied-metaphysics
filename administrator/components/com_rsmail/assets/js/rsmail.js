function isset () {
    var a = arguments,
        l = a.length,
        i = 0,
        undef;

    if (l === 0) {
        throw new Error('Empty isset');
    }

    while (i !== l) {
        if (a[i] === undef || a[i] === null) {
            return false;
        }
        i++;
    }
    return true;
}

function rsm_unsubscribe_lists() {
	var selectedOption = document.getElementById('jform_unsubscribe_option').value;
	
	if (document.getElementById('jform_unsubscribe_option').getParent().getParent().hasClass('control-group')) {
		if (selectedOption == 'lists') {
			document.getElementById('jform_unsubscribe_lists').getParent().getParent().style.display = '';
		} else {
			document.getElementById('jform_unsubscribe_lists').getParent().getParent().style.display = 'none';
		}
	} else {
		if (selectedOption == 'lists') {
			document.getElementById('jform_unsubscribe_lists').getParent().style.display = '';
		} else {
			document.getElementById('jform_unsubscribe_lists').getParent().style.display = 'none';
		}
	}
}

function rsm_calculate_cron_emails() {
	var emails_nr 		= document.getElementById('jform_cron_emails').value;
	var cron_period		= document.getElementById('jform_cron_period');
	var check_interval 	= document.getElementById('jform_cron_interval_check').value;

	if(cron_period.selectedIndex == 0) {
		var minutes_nr = 60;
		var period_type = Joomla.JText._('RSM_CONF_CRON_PERIOD_HOUR');
	} else {
		var minutes_nr = (24*60);
		var period_type = Joomla.JText._('RSM_CONF_CRON_PERIOD_DAY');
	}
	
	var result = Math.ceil(emails_nr/ (minutes_nr/check_interval));
	document.getElementById('rsm_cron_message_nr').innerHTML = result;
	document.getElementById('rsm_cron_message_min').innerHTML = check_interval;
	document.getElementById('rsm_cron_message_period').innerHTML = period_type;
	document.getElementById('rsm_cron_period').innerHTML = check_interval;
}

function rsm_notice(val) {
	if (val == 'pop3')
		document.getElementById('rsm_connection').style.display = '';
	else 
		document.getElementById('rsm_connection').style.display = 'none';
}

function rsm_check_handle(value) {
	if (value == 1 || value == 2)
		document.getElementById('cron_message').style.display = '';
	else 
		document.getElementById('cron_message').style.display = 'none';
	
	if (value == 0 || value == 2)
		document.getElementById('manual_message').style.display = '';
	else 
		document.getElementById('manual_message').style.display = 'none';
}

function rsm_check_rule(value) {
	if (document.getElementById('jform_bounce_rule').getParent().getParent().hasClass('control-group')) {
		if (value == 0)
			document.getElementById('jform_bounce_delete_no_action0').getParent().getParent().getParent().style.display = '';
		else document.getElementById('jform_bounce_delete_no_action0').getParent().getParent().getParent().style.display = 'none';
		
		if (value == 2)
			document.getElementById('jform_bounce_delete_forward0').getParent().getParent().getParent().style.display = '';
		else document.getElementById('jform_bounce_delete_forward0').getParent().getParent().getParent().style.display = 'none';
		
		if (value == 2)
			document.getElementById('jform_bounce_to_email').getParent().getParent().style.display = '';
		else document.getElementById('jform_bounce_to_email').getParent().getParent().style.display = 'none';
	} else {
		if (value == 0)
			document.getElementById('jform_bounce_delete_no_action0').getParent().getParent().style.display = '';
		else document.getElementById('jform_bounce_delete_no_action0').getParent().getParent().style.display = 'none';
		
		if (value == 2)
			document.getElementById('jform_bounce_delete_forward0').getParent().getParent().style.display = '';
		else document.getElementById('jform_bounce_delete_forward0').getParent().getParent().style.display = 'none';
		
		if (value == 2)
			document.getElementById('jform_bounce_to_email').getParent().style.display = '';
		else document.getElementById('jform_bounce_to_email').getParent().style.display = 'none';
	}
}

function exportCSV() {
	var filtered_results = jQuery('input[name=filtered_results]').val();
	var params = '';
	
	if (filtered_results == 1) {
		params += '&filtered_results=1';
	} else {
		jQuery('input[name="cid[]"]:checked').each(function(i, cid){
			params += '&cid[]='+jQuery(cid).val();
		});
	}
	
	var req = new Request({
		method: 'post',
		url: 'index.php?option=com_rsmail',
		data: 'task=subscribers.export&position=' + $('rsmail_position').value + params,
		onSuccess: function(responseText, responseXML) {
			var response = responseText;
			response = eval('(' + response + ')');
			//get the position
			position = parseInt(response.Position);
			//get the total
			total = parseInt(response.Total);

			$('rsmail_position').value = position;
			// show loader
			$('com-rsmail-import-progress').style.display = 'block';

			if (position < total) {
				//set the width and the procentage of the status bar
				var progress = Math.ceil(position * 100 / total) + '%';
				$('com-rsmail-bar').style.width = progress;
				$('com-rsmail-bar').innerHTML = progress;
				exportCSV();
			} else {
				$('rsmail_position').value = 0;
				$('com-rsmail-bar').style.width = '100%';
				$('com-rsmail-bar').innerHTML = '100%';
				// get the csv file 
				window.location = 'index.php?option=com_rsmail&task=subscribers.getfile';
			}
		}
	});
	req.send();
}

function repeat() {
	var fieldnames = $(document.adminForm).getElements('select');
	var idlist = document.getElementById('IdList').value;
	
	var sendfieldnames = new Array();
	for (i=0; i<fieldnames.length; i++)
		sendfieldnames.push('FieldName[]=' + fieldnames[i].value);
	sendfieldnames = sendfieldnames.join('&');
	
	var req = new Request({
		method: 'post',
		url: 'index.php?option=com_rsmail',
		data: 'task=import.save&bytes=' + $('rsmail_bytes').value + '&' + sendfieldnames + '&IdList=' + idlist ,
		onSuccess: function(responseText, responseXML) {
			var response = responseText;
			response = response.split("\n");
			//get the bytes
			bytes = response[0];
			//get the total
			total = response[1];

			$('rsmail_bytes').value = bytes;
			// show loader
			$('com-rsmail-import-progress').style.display = 'block';

			if (bytes != 'END') {
				//set the width and the procentage of the status bar
				var progress = Math.ceil(bytes * 100 / total) + '%';
				$('com-rsmail-bar').style.width = progress;
				$('com-rsmail-bar').innerHTML = progress;
				repeat();
			} else {
				//redirect if the import has reached to the end
				$('com-rsmail-bar').style.width = '100%';
				$('com-rsmail-bar').innerHTML = '100%';
				setTimeout("location.href='index.php?option=com_rsmail&view=subscribers&showlist="+ idlist +"'", 2500);
			}
		}
	});
	req.send();
}

function rsm_fields(val, nameSelect, usernameSelect) {
	if (val != 0) {
		var req = new Request.JSON({
			method: 'post',
			url: 'index.php?option=com_rsmail',
			data: 'task=jsonfields&IdList=' + val + '&ignore=1',
			onSuccess: function(responseText, responseXML) {
				var name = $('jform_jur_name');
				name.options.length = 0;
				var username = $('jform_jur_username');
				username.options.length = 0;
				
				var response = responseText;
				response.each(function (el) {
					name.options[name.options.length] = new Option(el.FieldName, el.FieldName);
					username.options[username.options.length] = new Option(el.FieldName, el.FieldName);
				});
				
				if (nameSelect) {
					for (i=0;i<name.options.length;i++){
						if (name.options[i].value == nameSelect)
							name.options[i].selected = true;
					}
				}
				
				if (usernameSelect) {
					for (i=0;i<username.options.length;i++){
						if (username.options[i].value == usernameSelect)
							username.options[i].selected = true;
					}
				}
				
				if (typeof jQuery == 'function') {
					jQuery("#jform_jur_name").trigger("liszt:updated");
					jQuery("#jform_jur_username").trigger("liszt:updated");
				}
			}
		});
		req.send();
	}
}

function rsm_send(ids) {
	var req = new Request({
		method: 'post',
		url: 'index.php?option=com_rsmail',
		data: 'task=send.send&IdSession=' + ids + '&IdMessage=' + parseInt(document.getElementById('IdMessage').innerHTML) + '&randomTime=' + Math.random(),
		onSuccess: function(responseText, responseXML) {
			var start = strpos(responseText,'RSEM0') + 5;
			var end = strpos(responseText,'RSEM1');
			var string = responseText.substring(start, end);
			string = trim(string);
			string = string.split("\n");

			//get the position
			position = parseInt(trim(string[0]));
			//get the total
			total = parseInt(trim(string[1]));

			if (position < total && document.getElementById('rsmstatus').innerHTML != 'paused') {
				rsm_send(ids);
			} else {
				setTimeout(function() {
					location.href='index.php?option=com_rsmail&view=reports&layout=view&id='+ids;
				}, 2500);
			}

			// show loader
			$('com-rsmail-import-progress').style.display = 'block';

			//set the width and the procentage of the status bar
			var progress = Math.ceil(position * 100 / total) + '%';
			$('com-rsmail-bar').style.width = progress;
			$('com-rsmail-bar').innerHTML = progress;
		}
	});
	req.send();
}

function strpos (haystack, needle, offset) {
	var i = (haystack + '').indexOf(needle, (offset || 0));
	return i === -1 ? false : i;
}

function trim (str, charlist) {
	var whitespace, l = 0,
		i = 0;
	str += '';

	if (!charlist) {
		// default list
		whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
	} else {
		// preg_quote custom list
		charlist += '';
		whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
	}

	l = str.length;
	for (i = 0; i < l; i++) {
		if (whitespace.indexOf(str.charAt(i)) === -1) {
			str = str.substring(i);
			break;
		}
	}

	l = str.length;
	for (i = l - 1; i >= 0; i--) {
		if (whitespace.indexOf(str.charAt(i)) === -1) {
			str = str.substring(0, i + 1);
			break;
		}
	}

	return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
}