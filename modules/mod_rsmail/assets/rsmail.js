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

function checkEmail(str,text) {
	var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (regex.test(str))
		return true;
	else {
		alert(text);
		return false;
	}
}

function doValidate(root, str1, str2, str3, mid) {
	params = '&id=' + mid;
	
	if (isset(document.getElementById('submit_captcha'+mid))) {
		document.getElementById('submit_captcha'+mid).removeClass('invalid');
		params += '&captcha'+mid+'=' + document.getElementById('submit_captcha'+mid).value;
	}
	
	if (isset(document.getElementById('recaptcha_challenge_field'))) {
		params += '&recaptcha_challenge_field=' + document.getElementById('recaptcha_challenge_field').value;
		params += '&recaptcha_response_field=' + document.getElementById('recaptcha_response_field').value;
	}
	params += '&randomTime='+Math.random();
	
	
	var req = new Request({
		method: 'post',
		url: root + 'index.php?option=com_rsmail',
		data: 'task=checkcaptcha&tmpl=component' + params,
		onSuccess: function(responseText, responseXML) {
			if (responseText == 1) {
				rsm_validation(root, str1, str2, str3, mid);
			} else {
				if (isset(document.getElementById('submit_captcha'+mid))) {
					rsm_refresh_captcha(mid);
					document.getElementById('submit_captcha'+mid).value = '';
					document.getElementById('submit_captcha'+mid).addClass('invalid');
				}
				
				if (isset(document.getElementById('recaptcha_challenge_field')))
					Recaptcha.reload();
			}
		}
	});
	req.send();
}

function rsm_validation(root, str1, str2, str3, mid) {
	var ret = true;
	var email = document.getElementById('rsm_email'+mid);
	
	if ( email.value==null || email.value=="" ) {
		alert(str2);
		email.focus();
		ret = false;
	}
	
	if (email.value == str1) {
		email.value="";
		email.focus();
		ret = false;
	}
	
	if (checkEmail(email.value,str3)==false) {
		email.value="";
		email.focus();
		ret = false;
	}
	
	if (ret) {
		document.getElementById('rsm_subscribe'+mid).submit();
	} else {
		if (isset(document.getElementById('submit_captcha'+mid))) {
			rsm_refresh_captcha(mid);
			document.getElementById('submit_captcha'+mid).value = '';
		}
	}
}

function rsm_show_fields(root,val,fields,mid) {
	document.getElementById('rsm_fields_location'+mid).innerHTML = '';
	document.getElementById('rsm_loader'+mid).style.display = 'block';
	
	var req = new Request({
		method: 'post',
		url: root + 'index.php?option=com_rsmail',
		data: 'task=getfields&tmpl=component&cid=' + val + '&fields=' + fields + '&randomTime=' + Math.random(),
		onSuccess: function(responseText, responseXML) {
			document.getElementById('rsm_fields_location'+mid).innerHTML = responseText;
			document.getElementById('rsm_loader'+mid).style.display = 'none';
		}
	});
	req.send();
}