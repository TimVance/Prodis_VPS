var sort_items = {
	sort_pos:{},
	ajax_events:new Array(),
	this_sort:null,
	f_parent:false,
	f_page:false,
	desc: false,

	start:function () {
		this.desc = $("ul.list_move").is(".sort_desc");
		$("ul.list_move").sortable({
			cursor: 'move',
			handle: '.move .fa-arrows',
			connectWith: ".list",

			create:sort_items.actions.create,

			out:function () {
				sort_items.f_parent = true;
			},

			beforeStop:sort_items.actions.move_page,

			stop:function (event, ui) {
				if(sort_items.f_page)
				{
					return;
				}
				sort_items.this_sort = $(this);

				$(this).sortable('disable');

				//if (sort_items.f_parent) {
					sort_items.actions.parent_item(event, ui);
					sort_items.f_parent = false;
				//}

				$(document).tooltip("destroy");
				$(document).tooltip();

				sort_items.actions.move_item(event, ui);

				sort_items.update();
				$(this).sortable('enable');
			}
		});
		$("ul.list_move").disableSelection();

		//$(window).on("unload", this.update);
	},

	get_level:function (elem) { return $(elem).parents("ul.list_move").length; },


	actions:{
		create:function (event, ui) {
			var level=1;
			if(! ui.item)
			{
				sort_items.this_sort = $(this);
				level = $(this).parents("ul.list_move").length + 1;
			}
			else
			{
				level = $(ui.item).parents("ul.list_move").length + 1;
				sort_items.this_sort = $(ui.item).closest("ul.list_move");
			}
			//console.log(level);

			sort_items.sort_pos[level]=new Array();
			sort_items.this_sort.children("li").each(function (i) {
				var sort_id = parseInt($(this).attr("sort_id"), 10);
				if(sort_id > 0) {
					sort_items.sort_pos[level][i] = sort_id;
				}
			});

			sort_items.sort_pos[level].sort(
			function(i, ii) { // По убыванию
				if (i < ii)
					return (sort_items.desc ? 1 : -1);
				else if (i > ii)
					return (sort_items.desc ? -1 : 1);
				else
					return 0;
			}
		);

		},

		// обработка перемещения в родителя элемента
		parent_item:function (event, ui) {
			var parent_id = $(ui.item).attr("parent_id");
			if(parent_id === undefined)
			{
				return;
			}
			parent_id=parseInt(parent_id,10);

			var level = $(ui.item).parents("ul.list_move").length + 1;
			var old_level = level;
			var id = parseInt($(ui.item).attr("row_id"), 10);
			var new_parent_id = -1;

			//ищем текущего родителя
			$(ui.item).parent("ul.list_move").children().each(function () {
				var p = parseInt($(this).attr('row_id'), 10);
				var pp = parseInt($(this).attr('parent_id'), 10);
				var l = $(this).parents("ul.list_move").length;
				if (p != id) {
					if (pp == 0) {
						new_parent_id = 0;
						level = 1;
						return false;
					}
					else {
						if (pp != parent_id) {
							new_parent_id = pp;
							level = l;
							return false;
						}
					}
				}
			});

			if (new_parent_id == -1 || new_parent_id == parent_id)
			{
				var prev = $(ui.item).prev('li');
				var off = prev.offset();
				if(prev.length && off !== null)
				{
					// если смещено, относительно верхнего элемента больше, чем на 20 пикселей, то считаем его родителем
					if(ui.offset.left - off.left > 15)
					{
						new_parent_id = prev.attr('row_id');
						level = prev.parents("ul.list_move").length + 1;
						if(! prev.find('ul').length)
						{
							prev.append('<ul class="list list_move ui-sortable"></ul>');
						}
						prev.addClass('open');
						prev.find('ul').first().append($(ui.item));
					}
				}
			}

			if(new_parent_id != -1 && parent_id != new_parent_id)
			{
				sort_items.ajax.add('move_parent', {id:id, parent_id:new_parent_id},

					function (response) {
						$(ui.item).attr("parent_id", new_parent_id);
						//var l = level;
						//$(ui.item).attr("class", l);
						sort_items.actions.create(event,ui);
					}
				);
			}

			sort_items.update();
		},
		// определяем перетащили ли на страницу
		move_page:function (event, ui) {
			sort_items.f_page = false;
			var i = 0;
			$(".paginator").each(function () {
				i++;
				$("a.border", this).each(function () {
					var off = $(this).offset();
					off['right'] = off.left + $(this).innerWidth();
					off['bottom'] = off.top + $(this).innerHeight();
					if (ui.offset.left >= off.left && ui.offset.left <= off.right) {
						if(i == 2)
						{
							ui.offset.top += $(ui.item).innerHeight();
						}
						if (ui.offset.top >= off.top && ui.offset.top <= off.bottom) {
							$(this).addClass('border_hover');
							var values = {
								page: parseInt($(this).text()),
								id: parseInt(ui.item.attr("row_id"))
							};

							var href = $(this).attr('href');

							sort_items.ajax.add('move_page', values, function() {
								window.location = href;
							});
							sort_items.update();
							sort_items.f_page = true;
						}
					}
				});
			});

		},

		// перемещение элементов
		move_item:function (event, ui) {
			//console.log('> move_item');

			var new_sort = {};
			var level = sort_items.get_level($(ui.item));

			var is_heading = false;

			$(ui.item).parent("ul.list_move").children().each(function (i) {
				if ($(this).is(".item_heading"))
				{
					is_heading = true;
				}
				if (is_heading)
				{
					i--;
				}
				var row_id = parseInt($(this).attr("row_id"), 10);
				if (row_id > 0)
				{
					if(sort_items.sort_pos[level])
					{
						new_sort[row_id] = sort_items.sort_pos[level][i];
					}
					else
					{
						new_sort[row_id] = 1;
					}
				}
			});
			sort_items.ajax.add('move', {sort:new_sort},null,sort_items.f_page);
		}
	},

	ajax:{
		/**
		 * добавляет ajax обработчик
		 */
		add: function (action, values, success, start_list, stop) {

			stop = stop || false;
			success = success || null;

			var event = {action:action,values:values, success:success, stop:stop};
			var exist = false;
			for (var i in sort_items.ajax_events) {
				if(sort_items.ajax_events[i].action == action)
				{
					$.each(event, function(key) {
						sort_items.ajax_events[i].key=event.key;
					});
					exist=true;
				}
			};

			if(!exist) {
				if(!start_list)
				{
					sort_items.ajax_events.push(event);
				}
				else
				{
					sort_items.ajax_events.unshift(event);
				}
			}
		},

		send:function (event) {

			var data = {
				action:event.action,
				check_hash_user:$('.check_hash_user').text()
			};

			for (var key in event.values) {
				if (!data[key]) data[key] = event.values[key];
			}

			var success = false;

			$.ajax({
				url:window.location.href,
				type:"POST",
				dataType:"json",
				cache:false,
				async:false,
				data:data,

				success:function (response) {

					if (response.hash) {
						$('input[name=check_hash_user]').val(response.hash);
						$('.check_hash_user').text(response.hash);
					}

					if (response.error || !response.status) { // произошла ошибка меняем все обратно
						sort_items.this_sort.sortable('cancel');

					}
					else {
						if (typeof(event.success) == "function") {
							event.success(response);
						}
						success = true;
					}


				}
			});
			return success;
		}

	},

	update:function () {
		for(var i in sort_items.ajax_events){
			var stop = sort_items.ajax_events[i].stop;
			if (sort_items.ajax.send(sort_items.ajax_events[i]) == true) {
				delete sort_items.ajax_events[i];
			}
			if (stop) break;
		}
	}
};

sort_items.start();
