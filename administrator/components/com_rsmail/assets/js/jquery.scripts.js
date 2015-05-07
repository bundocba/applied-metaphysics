jQuery.noConflict();
jQuery(document).ready(function($){
	rsm_init_toolbar(jQuery);
	rsm_init_suckerfish(jQuery);

	jQuery('#rsm_operator').hide();
	jQuery('#rsm_text_input').hide();

	jQuery('#rsm_load_more_results').live('click', function(e){
		e.preventDefault();
		var limitstart = jQuery('#rsm_load_more_results').attr('rel');
		rsm_get_items(jQuery, false, limitstart);
	});

	//check all the fields
	jQuery('#checkAll').live('click',function(){
		jQuery('input[name$="cid[]"]').attr('checked', this.checked);
	});

	$('#rsm_select_all_btn').click(function(){
		var results = jQuery('#rsm_select_filter_subs').html();

		if(jQuery('#rsm_select_all_btn').hasClass('rsm_all_selected')){
			var btn_text = rsm_get_lang('RSM_SELECT_ALL_RESULTS').replace('%s',results);
			jQuery('#rsm_select_all_btn').removeClass('rsm_all_selected').html(btn_text);
			jQuery('#rsm_filtered_results').val(0);
		}
		else {
			var btn_text = rsm_get_lang('RSM_DESELECT_ALL_RESULTS').replace('%s',results);
			jQuery('#rsm_select_all_btn').addClass('rsm_all_selected').html(btn_text);
			jQuery('#rsm_filtered_results').val(1);
		}
	});

// toolbars
function rsm_init_toolbar($)
{
	$('#rsm_condition_and, #rsm_condition_or').click(function(e){
		e.preventDefault();
		var selected_condition = $(this).text();

		$(this).parent().siblings().children('a').removeClass('rsm_tick');
		$(this).parent().parent().parent().children('a').html(selected_condition);
		$(this).attr('class', 'rsm_tick');
		var condition = $('input[name="rsm_condition"]').val($(this).attr('rel'));

		rsm_get_items($, false);
		$('.rsm_filter_condition').each(function(i,span){
			$(span).html(selected_condition);
		});
	})
	$("#rsm_filter").click(function(e) {
		e.preventDefault();

		var current_published = $('#rsm_published .rsm_tick').attr('rel');
		var current_list	  = $('#rsm_list .rsm_tick').attr('rel');
		var current_field	  = $('#rsm_field .rsm_tick').attr('rel');
		var current_operator  = $('#rsm_operator .rsm_tick').attr('rel');

		$('#rsm_condition > ul > li > a.rsm_tick').children('span.sf-sub-indicator').remove();
		var current_condition = $('#rsm_condition > ul > li > a.rsm_tick').text();
		var text 			  = $('#rsm_filter_text').val();
		var already_published 	= document.getElementsByName('rsm_published[]');
		var already_lists     	= document.getElementsByName('rsm_lists[]');
		var already_fields     	= document.getElementsByName('rsm_fields[]');
		var already_operators 	= document.getElementsByName('rsm_operators[]');
		var already_values	  	= document.getElementsByName('rsm_values[]');
		var already_condition  	= document.getElementsByName('rsm_condition');

		for (var i=0; i<already_lists.length; i++)
		{
			if ($(already_published[i]).val() == current_published
				&& $(already_lists[i]).val() == current_list
				&& $(already_fields[i]).val() == current_field
				&& $(already_operators[i]).val() == current_operator
				&& $(already_values[i]).val() == text)
				return;
		}

		var count_filters = $('#rsm_filters li').length;

		var condition	= (count_filters > 0 ? $('<span class="rsm_filter_condition">').empty().text(current_condition) : '');
		var li 			= $('<li>');
		var published	= $('<span>').html($('#rsm_published > a').html());
		var list 		= $('<span>').html($('#rsm_list > a').html()) ;
		var field 		= $('#rsm_field ul li > a.rsm_tick').attr('rel') == 0 ? $('<span>').hide() : $('<span>').html($('#rsm_field > a').html());
		var operator 	= $('#rsm_field ul li > a.rsm_tick').attr('rel') == 0 ? $('<span>').hide() : $('<span>').html($('#rsm_operator > a').html());
		var value		= $('#rsm_field ul li > a.rsm_tick').attr('rel') == 0 ? $('<span>').hide() : $('<strong>').text(text);
		var close 		= $('<a>', {'href': 'javascript: void(0)', 'class': 'rsm_close'}).click(function(e) {
			e.preventDefault();
			$(this).parent('li').hide('highlight', 500, function() {
				if($(this).prev().hasClass('rsm_filter_condition'))
					$(this).prev().remove();
				$(this).remove();

				rsm_get_items($, false);

				if (count_filters <= 2 && $('#rsm_clear_filters').length > 0) {
					$('#rsm_clear_filters').remove();
				}
				if(count_filters == 1)
					$('#rsm_condition').css('display', 'none');

				if ($('#rsm_filters').children().first().hasClass('rsm_filter_condition'))
					$('#rsm_filters').children().first().remove();
			});
		});

		var input_published		= $('<input>', {'type': 'hidden', 'name': 'rsm_published[]', 'value': current_published});
		var input_lists 		= $('<input>', {'type': 'hidden', 'name': 'rsm_lists[]', 'value': current_list});
		var input_fields 		= $('<input>', {'type': 'hidden', 'name': 'rsm_fields[]', 'value': current_field});
		var input_operator  	= $('<input>', {'type': 'hidden', 'name': 'rsm_operators[]', 'value': current_operator});
		var input_value 		= $('<input>', {'type': 'hidden', 'name': 'rsm_values[]', 'value': text});

		if(count_filters > 0)
			$('#rsm_filters').append(condition);

		$('#rsm_filters').append(li.append(published, list, field, operator, value, close, input_published, input_lists, input_fields, input_operator, input_value));
		$('#rsm_clear_filters').remove();

		if (count_filters > 0) 
			$('#rsm_condition').css('display', 'block');
		else 
			$('#rsm_condition').css('display', 'none');

		if (count_filters >= 2)
		{
			var clear = $('<span>', {'id': 'rsm_clear_filters'}).html(rsm_get_lang('RSM_CLEAR_ALL_FILTERS')).click(function (e){
				e.preventDefault();
				$('#rsm_filters').children().hide('highlight', 500, function() { $(this).remove();});
				rsm_get_items($, true); 
				$('#rsm_condition').css('display', 'none');
			});
			$('#rsm_filters').append(clear);
		}

		rsm_get_items($, false);
	});

	// use this to trigger the filtering when enter is pressed and the form is submitted
	$("#rsm_filter_form").submit(function(e) {
		e.preventDefault();
		$('#rsm_filter').trigger('click');
	});

	// if there are any filters from the session, initialize the clicks
	$('.rsm_close').live('click',function(e) {
		e.preventDefault();
		$(this).parent('li').hide('highlight', 500, function() {
			if($(this).prev().hasClass('rsm_filter_condition'))
				$(this).prev().remove();

			$(this).remove();

			rsm_get_items($, false);

			// remove clear filters button if there are less than 3 filters applied
			if ($('#rsm_filters li').length < 3 && $('#rsm_clear_filters').length > 0)
				$('#rsm_clear_filters').remove();

			// hide condition if it is displayed only one filter
			if ($('#rsm_filters li').length < 2)
				$('#rsm_condition').css('display', 'none');
			
			// remove the or/and tag if it is the first element in the filter
			if ($('#rsm_filters').children().first().hasClass('rsm_filter_condition'))
				$('#rsm_filters').children().first().remove();
		});
	});
	
	$('#rsm_clear_filters').click(function (e){
		e.preventDefault();
		$('#rsm_filters').children().hide('highlight', 500, function() { $(this).remove(); });
		rsm_get_items($, true);
		$('#rsm_condition').css('display', 'none');
	});
}

// create suckerfish dropdowns
function rsm_init_suckerfish($)
{
	$(".rsm_filter_toolbar > ul").children('li').each(function (i, el) {
		maxwidth = 0;

		$(el).children('ul').each(function (j, ul) {
			$(ul).show();
			$(ul).children('li').each (function (k, li) {
				width = $(li).outerWidth();
				
				if (width > maxwidth)
					maxwidth = width;
			});
			$(ul).hide();
		});
		if (maxwidth > 0)
			$(el).css('width', maxwidth + 'px');
	});
	
	$(".rsm_filter_toolbar ul").superfish();

	// published
	$('#rsm_published ul li a').click(function (e){
		e.preventDefault();

		$(".rsm_filter_toolbar ul").hideSuperfishUl();
		$('#rsm_published > a').html($(this).html());
		
		$('#rsm_published ul li a').removeClass('rsm_tick');
		$(this).addClass('rsm_tick');
	});

	// lists
	$('#rsm_list ul li a').click(function (e){
		e.preventDefault();

		$(".rsm_filter_toolbar ul").hideSuperfishUl();
		$('#rsm_list > a').html($(this).html());
	
		$('#rsm_list ul li a').removeClass('rsm_tick');
		$(this).addClass('rsm_tick');
		
		var ListId = $(this).attr('rel');

		$.ajax({
			type: 'POST',
			url: "index.php?option=com_rsmail&task=jsonfields&action=filter_subscribers&IdList="+ListId,
			dataType: 'html',
			success: function(data)	{
				$('#rsm_field').show();
				$('#rsm_field ul').empty();
				jQuery.parseJSON(data).each(function(el,index){
					 $('#rsm_field ul').append('<li><a href="javascript: void(0);" rel="'+el.IdListFields+'" '+(el.IdListFields == 0 ? 'class="rsm_tick"' : '')+' >'+el.FieldName+'</a></li>');
				});
				$('#rsm_field > a').html($('#rsm_field li a.rsm_tick').html());
				$('#rsm_filter_text').val('');
			}
		});
	});

	// fields
	$('#rsm_field ul li a').live('click',function (e)
	{
		e.preventDefault();
		
		$(".rsm_filter_toolbar ul").hideSuperfishUl();
		$('#rsm_field > a').html($(this).html());
		
		$('#rsm_field ul li a').removeClass('rsm_tick');
		$(this).addClass('rsm_tick');
		
		if($(this).attr('rel') == 0) {
			$('#rsm_operator').hide();
			$('#rsm_text_input').hide();
		} else {
			$('#rsm_operator').show();
			$('#rsm_text_input').show();
		}
	});
	
	// operator
	$('#rsm_operator ul li a').click(function (e)
	{
		e.preventDefault();
		

		$(".rsm_filter_toolbar ul").hideSuperfishUl();
		$('#rsm_operator > a').html($(this).html());
		
		$('#rsm_operator ul li a').removeClass('rsm_tick');
		$(this).addClass('rsm_tick');
	});
}

function rsm_get_items($, clear, more)
{
	// parent container
	var parent = $('#rsm_subscribers');

	// clear contents
	if (clear == true){	parent.empty(); }

	var limitstart = 0;
	if(more) limitstart = more;
	
	$.ajax({
		type: 'POST',
		url: "index.php?option=com_rsmail&task=subscribers.ajax&limitstart="+limitstart,
		data: rsm_get_items_filter($, clear, more),
		dataType: 'json',
		success: function(data){
			// display subscribers
			if(limitstart == 0) parent.html(data.layout);
			else parent.append(data.layout);

			// tooltip
			var selector = jQuery('#rsm_subscribers .hasTip[title!=""]');
			selector.each(function(index,img){var title = img.get('title'); if (title) { var parts = title.split('::', 2); img.store('tip:title', parts[0]); img.store('tip:text', parts[1]); } });
			selector.each(function(index,el){ var JTooltips = new Tips(el, { maxTitleChars: 50, fixed: false}); });
			
			var current_results		= parseInt($('input[name="cid[]"]').length);
			var total_results		= parseInt(data.pagination.total);
			var thelimit			= parseInt(data.pagination.limit);
			
			if (current_results >= total_results) {
				$('#rsm_load_more_results').hide();
			} else {
				// calculate the number of subscribers that will be loaded
				var difference 		= total_results - current_results;
				var load_results 	= (difference > 0 && difference < thelimit ? difference : ( total_results > thelimit ?  thelimit : 0));
				
				$('#rsm_load_more_results').attr('rel', current_results);
				$('#rsm_load_limit').text(load_results);
				$('#rsm_load_more_results').show();
			}
			
			// update pagination info
			$('#rsm_current_results').html(current_results);
			$('#rsm_total_results').html(total_results);
			$('#rsm_select_filter_subs').html(total_results);
		}
	});
}

function rsm_get_items_filter($, clear,more)
{
	// rsm_published[]
	// rsm_lists[]
	// rsm_fields[]
	// rsm_operators[]
	// rsm_values[]
	// delete all filters

	if(clear){
		$('input[name="rsm_published[]"]').remove();
		$('input[name="rsm_lists[]"]').remove();
		$('input[name="rsm_fields[]"]').remove();
		$('input[name="rsm_operators[]"]').remove();
		$('input[name="rsm_values[]"]').remove();
		$('input[name="rsm_condition"]').val('AND');
	}
	
	var published = [];
	$('input[name="rsm_published[]"]').each(function (index, el){
		published.push($(el).val());
	});
	if (published.length == 0)
		published = '';
	
	var lists = [];
	$('input[name="rsm_lists[]"]').each(function (index, el){
		lists.push($(el).val());
	});
	if (lists.length == 0)
		lists = '';

	var fields = [];
	$('input[name="rsm_fields[]"]').each(function (index, el){
		fields.push($(el).val());
	});
	if (fields.length == 0)
		fields = '';

	var operators = [];
	$('input[name="rsm_operators[]"]').each(function (index, el){
		operators.push($(el).val());
	});

	if (operators.length == 0)
		operators = '';

	var values = [];
	$('input[name="rsm_values[]"]').each(function (index, el){
		values.push($(el).val());
	});
	if (values.length == 0)
		values = '';

	var condition = $('input[name="rsm_condition"]').val();
	
	var data = {
		'filter_published[]': published,
		'filter_lists[]': lists,
		'filter_fields[]': fields,
		'filter_operators[]': operators,
		'filter_values[]': values,
		'filter_condition': condition,
	};

	return data;
}
});