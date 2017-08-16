
function customDel(res, preffix, activeTab, closeParamName) {
	if (typeof (activeTab) == 'undefined') {
		activeTab = 1;
	}
	if (typeof (preffix) == 'undefined') {
		preffix = '';
	}
	if (typeof (closeParamName) == 'undefined') {
		closeParamName = 'close';
	}
	var id = 'main_' + res + preffix;
	
	var tabParam = 'tab_' + res;	
	var val = listx.getCheked(id, true);
	
	var res = res.split('_');
	
	if (typeof(res[1]) == 'undefined') {
		var act = new Array('');
	} else {
		var act = res[1].split('xxx');
	}
	
	$('.error')[0].style.display = 'none';
	
	var closeParam = '';
	for (i = 0; i < val.length; i++) {
		closeParam = closeParam + '&' + closeParamName + '[]=' + val[i];  
	}
	
	$.post('index.php',
		'module=' + res[0] + '&action=' + act[0] + closeParam + '&' + tabParam + '=' + activeTab,
		function(data) {
			if (data.error) {
				$('.error').html(data.error);
				$('.error')[0].style.display = 'block';
			} else {
				load('index.php?module=' + res[0] + '&action=' + act[0] + '&' + tabParam + '=' + activeTab);
			}
		},
		"json"
	);
}

function dateBlur(id) {
	if (document.getElementById('date_' + id)) {
		document.getElementById('date_' + id).value = document.getElementById(id + '_year').value + '-' + document.getElementById(id + '_month').value + '-' +document.getElementById(id + '_day').value;
		if (document.getElementById('date_' + id).value == "--") document.getElementById('date_' + id).value = '';
	}
}
function dateInt(evt) {
	var code = evt.charCode;
	if (document.all) {
		code = evt.keyCode;
	}
	var av = new Array(0,48,49,50,51,52,53,54,55,56,57);
	for (var i = 0; i < av.length; i++) {
		if (av[i] == code) return true;
	}
	return false;
}


var listx = {
	look: function (id) {
		$('#' + id).toggle();
	},
	dateKeyup: function (id, obj) {
		if (obj.id == id + '_day' && Number(obj.value) > 31) {
			obj.value = '';
			obj.focus();
			return false;
		}
		if (obj.id == id + '_month' && Number(obj.value) > 12) {
			obj.value = '';
			obj.focus();
			return false;
		}
	},
	gMonths : new Array("","Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь"),
	loc : {},
	create_date : function (cal, dateFormat) {
			var from = $("#date_" + cal + "0")
					.datepicker({
						dateFormat: dateFormat,
						changeMonth: true,
						numberOfMonths: 1
					})
					.on( "change", function() {
						to.datepicker( "option", "minDate", listx.getDate( this, dateFormat ) );
					}),
				to = $("#date_" + cal  + "1").datepicker({
						dateFormat: dateFormat,
						defaultDate: "+1w",
						changeMonth: true,
						numberOfMonths: 2
					})
					.on( "change", function() {
						from.datepicker( "option", "maxDate", listx.getDate( this, dateFormat ) );
					});
	},
	getDate : function ( element, dateFormat ) {
		var date;
		try {
			date = $.datepicker.parseDate( dateFormat, element.value );
		} catch( error ) {
			date = null;
		}
		return date;
	},
	modalClose: function(id, caption) {
		parent.xxxx = {id:id, name:caption};
		parent.$.modal.close();
	},
	pageSw: function(obj, id, isAjax) {
		var o = $('#pagin_' + id).find('input');
		o.value = obj.getAttribute('title');
		var container = '';
		var p = '_page_' + id + '=' + o.value;
		if (isAjax)	{
			container = document.getElementById("list" + id).parentNode;
			if (listx.loc[id].indexOf('&__') < 0) {
				if (container.id) {
					location.hash = preloader.prepare(location.hash.substr(1) + '&--' + container.id + '=' + preloader.toJson(listx.loc[id] + "&" + p));
				}
			} else {
				load(listx.loc[id] + '&' + p, '', container);
			}
		}
		else load(listx.loc[id] + '&' + p, '', container);
	},
	goToPage: function(obj, id, isAjax) {
		var container = '';
		var o = $('#pagin_' + id).find('input');
		var p = '_page_' + id + '=' + o.val();
		if (isAjax)	{
			container = document.getElementById("list" + id).parentNode;
			if (listx.loc[id].indexOf('&__') < 0) {
				if (container.id) {
					location.hash = preloader.prepare(location.hash.substr(1) + '&--' + container.id + '=' + preloader.toJson(listx.loc[id] + "&" + p));
				}
			} else {
				load(listx.loc[id] + '&' + p, '', container);
			}
		}
		else {
			load(listx.loc[id] + '&' + p, '', container);
		}
	},
	countSw: function(obj, id, isAjax) {
		var container = '';
		if (isAjax)	container = document.getElementById("list" + id).parentNode;
		var post = {};
		post['count_' + id] = obj.value;
		load(listx.loc[id], post, container);
	},
	switch_active: function($this, e) {	
		e.cancelBubble = true;
		var data = String($($this).attr('t_name'));
		var src = String($($this).attr('src'));
		var alt = $($this).attr('alt'); 				     		
		var val = $($this).attr('val');
		if (alt == 'on') {
			var is_active 	= "N";
			var new_src 	= src.replace("on.png", "off.png");
			var new_alt 	= "off";
			var str 		= "Деактивировать запись?";
		} else {
			var is_active 	= "Y";
			var new_src 	= src.replace("off.png", "on.png");
			var new_alt 	= "on";
			var str 		= "Активировать запись?";
		}						
		if (confirm(str)) {				
			$.post('index.php?module=admin&action=switch&loc=core',
				{data: data, is_active: is_active, value: val},
				function(data, textStatus) {
					if (textStatus == 'success' && data.status == "ok") {
						$($this).attr('src', new_src);
						$($this).attr('alt', new_alt);
					} else {
						if (data.status) alert(data.status);
					}
				},
				'json'
			);
		}		
	},
	buttonAction : function(id, url, text, nocheck, obj, callback) {
		obj.disabled = true;
		obj.className = "buttonDisabled";
		if (!url) {
			alert('Временно недоступна.');
			obj.disabled = false;
			obj.className = "button";
			return;
		}
		var val = "";
		if (!nocheck) {
			val = this.getCheked(id);
		}
		if (!val && !nocheck) {
			alert("Вы должны выбрать как минимум одну запись.");
			obj.disabled = false;
			obj.className = "button";
			return false;
		} else {
			if (text) {
				if (!confirm(text)) {
					obj.disabled = false;
					obj.className = "button";
					return false;
				}
			}
			if (val && !nocheck) {
				val = val.slice(0, -1);
			}
			if (!callback) {
				callback = function(data) {
					if (data && data.error) {
						$('.error').html(data.error);
						$('.error')[0].style.display = 'block';
					} else {
						load(url);
					}
				}
			}
			$.post(url,
				{record_id: val},
				callback,
				"json"
			);
		}
		obj.className = "button";
		obj.disabled = false;
		return;
	},
	getCheked : function (id, returnArray) {
		var j = 1;
		if (returnArray == true) {
			var val = [];
		} else {
			var val = "";
		}

		for(var i = 0; i < j; i++) {
			if (document.getElementById("check" + id + i)) {
				if (document.getElementById("check" + id + i).checked) {
					if (returnArray == true) {
						val.push(document.getElementById("check" + id + i).value);
					} else {
						val += document.getElementById("check" + id + i).value + ",";
					}
				}
				j++;
			}
		}
		return val;
	},
	del: function (id, text, isAjax) {
		var val = this.getCheked(id, true);
		if (val) {
			if (val.length) {
				if (confirm(text)) {
					preloader.show();
					$("#main_" + id + "_error").hide();
					var container = '';
					if (isAjax) var container = document.getElementById("list" + id).parentNode;
					if (listx.loc[id]) {
						$.ajax({
							method: "DELETE",
							dataType: "json",
							url: "index.php?res=" + id + "&id=" + val,
							success: function (data) {
								if (data == true) {
									load(listx.loc[id], '', container);
								} else {
									if (!data || data.error) {
										var msg = data.error ? data.error : "Не удалось выполнить удаление";
										$("#main_" + id + "_error").html(msg);
										$("#main_" + id + "_error").show();
									} else {
										if (data.alert) {
											alert(data.alert);
										}
										if (data.loc) {
											load(data.loc, '', container);
										}
									}
								}
							}
						}).fail(function () {
							alert("Не удалось выполнить удаление");
						}).always(function () {
							preloader.hide();
						});
					}
				}
			} else {
				alert('Нужно выбрать хотябы одну запись');
			}
		}
	},
	cancel : function (e, id) {
		e.cancelBubble = true;
		if (id) listx.checkChecked(id);
		return false;
	},
	cancel2 : function (e, id) {
		e.cancelBubble = true;
		this.look(id);
		return false;
	},
	checkChecked : function (id) {
		var obj = document.getElementById("edit_" + id);
		if (obj) {
			var j = 1;
			var gotit = 0;
			for (var i = 0; i < j; i++) {
				if (document.getElementById("check" + id + i)) {
					if (gotit >= 2) break;
					if (document.getElementById("check" + id + i).checked) {
						gotit++;
					}
					j++;
				}
			}
			if (gotit >= 2) obj.style.display = '';
			else obj.style.display = 'none';
		}
		return;
	},
	checkAll : function (obj, id) {
		var j = 1;
		if (obj.checked) {
			var check = true;
		}
		else var check = false;
		for(var i = 0; i < j; i++) {
			if (document.getElementById("check" + id + i)) {
				document.getElementById("check" + id + i).checked = check;
				j++;
			}
		}
		//list.checkChecked(id);
		return;
	},
	showFilter : function(id) {
		var f = $("#filter" + id);
		this.toggle(f);
		f.find("form")[0].elements[0].focus();
		return;
	},
	toggle : function(f) {
		if (f.hasClass('hide')) {
			f.toggle('fast');
			f.removeClass('hide');
		} else {
			f.toggle('fast');
			f.addClass('hide');
		}
	},
	columnFilter : function(id) {
		var f = $("#filterColumn" + id);
		this.toggle(f);
	},
	columnFilterStart : function(id, isAjax) {
		var o = $('#filterColumn' + id + ' form').find(':checkbox:checked');
		var l = o.length;
		var post = {};
		var t = [];

		for (var i = 0; i < l; i++) {
			t.push(o[i].value);
		}
		post['column_' + id] = t;
		var container = '';

		if (listx.loc[id]) {
			if (isAjax) {
				container = document.getElementById("list" + id).parentNode;
				load(listx.loc[id] + '&__filter=1', post, container);
			} else {
				load(listx.loc[id], post, container);
			}
		}
	},
	clearFilter: function(id, isAjax) {
		var post = {};
		post['clear_form' + id] = 1;
		var container = '';
		if (listx.loc[id]) {
			if (isAjax) {
				container = document.getElementById("list" + id).parentNode;
				load(listx.loc[id] + '&__clear=1', post, container);
			} else {
				load(listx.loc[id], post, container);
			}
		}
	},
	startSearch : function(id, isAjax) {
		var allInputs = $("#filter" + id).find(":input");
		var l = allInputs.length;
		var post = {};
		for (var i = 0; i < l; i++) {
			post[allInputs[i].name] = allInputs[i].value;
		}
		post = allInputs.serializeArray();
		var container = '';

		if (listx.loc[id]) {
			if (isAjax) {
				container = document.getElementById("list" + id).parentNode;
				load(listx.loc[id] + '&__search=1', post, container);
			} else {
				load(listx.loc[id], post, container);
			}
		}
		//load(listx.loc[id], post);
	},
	doOrder : function(id, data, isAjax) {
		var container = '';
		var post = {}
		post['orderField_main_' + id] = data;
		if (listx.loc[id]) {
			if (isAjax) {
				container = document.getElementById("list" + id).parentNode;
				load(listx.loc[id] + '&__order=1', post, container);
			} else {
				load(listx.loc[id], post, container);
			}
		}
	},
	initSort : function(id, tbl) {
		$("#list" + id + " > tbody").sortable({ opacity:0.6,
			distance:5,
			start:function (event, ui) {
				ui.helper.click(function (event) {
					event.stopImmediatePropagation();
					event.stopPropagation();
					return false;
				});

			},
			update : function (event, ui) {

				var src = ui.item[0].parentNode.childNodes;
				var so = new Array();
				if (src) {
					for (var k in src) {
						if (src[k].childNodes && src[k].childNodes.length) {
							var el = src[k].childNodes[0]
							if (el && el.nodeName == "TD") {
								if (typeof el.getAttribute == "function") {
									var id = el.getAttribute("title");
									if (id) {
										so.push(id);
									}
								}
							}
						}
					}
				}
				$.post("index.php?module=admin&action=seq",
					{data : so, tbl : tbl},
					function (data, textStatus) {
						if (textStatus != 'success') {
							alert(textStatus);
						} else {
							if (data && data.error) alert(data.error);
						}
					},
					"json"
				);
			}
		});
		$("#list" + id + " tbody").disableSelection();
	},
	fixHead: function (id) {
		$('#' + id).floatThead({top: 55, zIndex: 200, headerCellSelector: 'tr.headerText>td:visible'})
	}
	
};
