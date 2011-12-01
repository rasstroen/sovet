/**
 * hardtechno.ru chat by rasstroen (http://vkontekte.ru/server_side)
 */
var chat = {
	//
	// settings
	//
	// where is server
	server_url:'/',
	// cookie name with auth hash
	authCookieName:'thardhash_',
	// cookie name with user id
	authCookieIdName:'thardid_',
	// chat refresh interval in seconds
	refreshSpeed: 5,
	// translates
	translate_say : 'написать',
	// variables
	//
	// can i write?
	can_write : false,
	// timer link
	timer_v : false,
	// div with messages
	chat_messages_window : false,
	// chat status
	status : 1,
	// how many times we asked server for messages list
	requestNumber : 0,
	// after getOnlinersAfter request we refresh online users
	getOnlinersAfter : 1,
	// statuses
	status_wait : 1, // do nothing
	status_request_sended_fetch : 2, // sent request fo fetch new data
	status_request_sended_message : 3, // sent message
	// last message user see now
	last_message_received_id : 0,
	// last message timestamp
	last_message_received_time : 0,
	// messages
	messages : {},
	// count
	messages_count : 0,
	// users
	users : {},
	// online users
	online_users : {},
	//
	online_users_count : 0,
	// element on page for draw chat
	divElement:false,
	// container for message input & button
	send_plank_div:false,
	// input area
	chat_input:false,
	// send button
	chat_submit:false,
	// secret for authorization
	authCookie:'',
	// user's id
	authId:'',
	// user's profile
	profile:{},
	// initializing
	init : function(id){
		chat.divElement = document.getElementById(id);
		chat.divElement.innerHTML = '';
		chat.chat_messages_window = false;
		chat.profile = {};
		chat.status = 1;
		chat.online_users_count = 0;
		if(chat.timer_v)
			clearInterval(chat.timer_v);
		chat.authCookie = chat.getCookie(chat.authCookieName);
		chat.authId = chat.getCookie(chat.authCookieIdName);
		chat.authorize();
		chat.messages = {};
		chat.last_message_received_id = 0;
		chat.last_message_received_time = 0;
	},
	// authorize user via server
	authorize : function(){
		data = {};
		data.action = 'authorize';
		data.id = chat.authId;
		data.secret = chat.authCookie;
		chat.request(data, chat.on_authorize);
	},
	clickButton : function(e) {
		var keynum;
		var keychar;
		var numcheck;
		if(window.event){
			keynum = e.keyCode
		}
		else if(e.which){
			keynum = e.which
		}
		if(keynum == 13){
			chat.send();
		}
	},
	// меняем favicon
	changeIcon :   function(url){
		var head = document.getElementsByTagName("head")[0];
		// удаление старой иконки
		var links = head.getElementsByTagName("link");
		for (var i = 0; i < links.length; i++) {
			var lnk = links[i];
			if (lnk.rel=="shortcut icon") {
				head.removeChild(lnk);
				return;
			}
		}

		// создание и добавление новой иконки
		var link = document.createElement("link");
		link.setAttribute("href", url);
		link.setAttribute("type","image/x-icon");
		link.setAttribute("rel","shortcut icon");
		head.appendChild(link);
	},
	// пишем приват
	write_private : function(id){
		var tmp = 's';
		if(chat.online_users[id]){
			tmp = '/to '+chat.online_users[id].nickname.toString() +': '+ chat.chat_input.value;
		}else
		if(chat.users[id]){
			tmp = '/to '+chat.users[id].nickname.toString() +': '+chat.chat_input.value;
		}else
			tmp = '/to '+id.toString() +': '+ chat.chat_input.value;
		chat.chat_input.value = tmp;
		chat.chat_input.focus();
		
	},
	drawAuthForm:function(){
		chat.send_plank_div = document.createElement('DIV');
		chat.send_plank_div.id = 'chat_send_plank_div';
		chat.divElement.appendChild(chat.send_plank_div);
		
		chat.send_plank_div.innerHTML = '<i>Войдите, чтобы участвовать в чате</i>';	
	},
	drawInput : function(){
		chat.send_plank_div = document.createElement('DIV');
		chat.send_plank_div.id = 'chat_send_plank_div';
		chat.divElement.appendChild(chat.send_plank_div);
		
		var _input = document.createElement('input');
		_input.type = 'text';
		_input.id = 'chat_input';
		chat.send_plank_div.appendChild(_input);
		chat.chat_input = _input;
		chat.chat_input.onkeypress = function(event){
			chat.clickButton(event)
		};
		
		var _button = document.createElement('input');
		_button.type = 'button';
		_button.id = 'chat_submit';
		_button.value = chat.translate_say;
		_button.onclick = function(){
			chat.send();
		}
		chat.chat_submit = _button;
		chat.send_plank_div.appendChild(_button);
		
		chat.chat_input.focus();
	},
	set_authorized : function (is_authorized){
		if(is_authorized)	{
			chat.can_write = 1;
			chat.drawInput();
		}else{
			chat.drawAuthForm();
		}
	},
	on_authorize : function(data){
		if(data && data.success){
			// authorized, so we can wrote
			chat.profile = data.profile;
			chat.set_authorized(true);
		}else
			chat.set_authorized(false);
		chat.start_timer();
	},
	start_timer : function(){
		chat.on_timer();
		chat.timer_v = setInterval('chat.on_timer()',chat.refreshSpeed*1000);
	},
	on_timer: function(){
		// if it's no pending get requests
		// put new request
		if(chat.status == chat.status_wait){
			chat.requestNumber++;
			// we can sent request to fetch data
			chat.get(chat.requestNumber % chat.getOnlinersAfter == 0);
		}
	},
	send : function(){
		chat.status = chat.status_request_sended_message;
		var data = {};
		data.action = 'say';
		data.message = document.getElementById('chat_input').value;
		data.last_message_received_id = chat.last_message_received_id;
		data.last_message_received_time = chat.last_message_received_time;
		// message not empty & we are authorized to write
		if(data.message && chat.can_write){
			chat.chat_input.disabled = 'disabled';
			chat.request(data, chat.on_after_send);
		}
		chat.chat_input.value = '';
	},
	// request for get messages
	get : function(get_onliners){
		var data = {};
		data.action = 'fetch';
		data.get_onliners = get_onliners? 1 : 0;
		data.last_message_received_id = chat.last_message_received_id;
		data.last_message_received_time = chat.last_message_received_time;
		chat.status = chat.status_request_sended_fetch;
		chat.request(data, chat.on_after_get);
	},
	show_last_messages : function(){
		chat.chat_messages_window.scrollTop = chat.chat_messages_window.scrollHeight;	
	},
	refresh_onliners : function(){
		chat.online_users_count = 0;
		if(chat.chat_online_window)
			chat.chat_online_window.innerHTML = '';
		for(var i in chat.online_users){
			chat.online_users_count++;
			chat.draw_online_user(chat.online_users[i]);
		}
	},
	draw_online_user : function(profile){
		chat.messages_count++;
		if(!chat.chat_online_window){
			chat.chat_online_window = document.createElement('DIV');
			chat.chat_online_window.id = 'chat_online_window';
			chat.divElement.appendChild(chat.chat_online_window);
		}
		var odd = 0;
		if(chat.online_users_count % 2 == 0) odd =1;
		var online_plank = document.createElement('div');
		online_plank.id = 'online_'+profile.id;
		online_plank.className = 'online_plank'+(odd?' odd':'');
		online_plank.name = 'chat_online';
		
		var online_author_div = document.createElement('div');
		online_author_div.id = 'online_author_div'+profile.id;
		online_author_div.className = 'online_author_div';
		online_plank.appendChild(online_author_div);
		online_author_div.innerHTML = chat.draw_user(profile.id)
		chat.chat_online_window.appendChild(online_plank);
	},
	on_after_get : function(data){
		if(data && data['success']){
			if(data.users){
				for(var i in data.users){
					chat.users[i] = data.users[i];
				}
			}
			if(data.online_users){
				chat.online_users = {};
				for(var i in data.online_users){
					chat.online_users[i] = data.online_users[i];
				}
				chat.refresh_onliners();
			}
			if(data.messages){
				for(var i in data.messages){
					if(!chat.messages[data.messages[i].id]){
						// new message
						chat.messages[data.messages[i].id] = data.messages[i];
						chat.draw_message(data.messages[i]);
						chat.last_message_received_id = Math.max(chat.last_message_received_id, data.messages[i].id)
						chat.last_message_received_time = Math.max(chat.last_message_received_time, data.messages[i].time)
						chat.show_last_messages();
					}
				}
				$('abbr.timeago').timeago();
			}
			if(data.last_message_id>-1){
				chat.last_message_received_id = data.last_message_id;
			}
			if(data.refresh){
				chat.init(chat.divElement.id)
			}	
		}
		chat.status = chat.status_wait;
	},
	// after send request to server
	on_after_send : function(data){
		chat.status = chat.status_wait;
		chat.last_message_received_id = data.last_message_id;
		chat.on_after_get(data);
		chat.chat_input.disabled = '';
		chat.show_last_messages();
	},
	draw_user: function(id){
		//chat.users[id].nickname
		if(chat.users[id])
			return '<img onclick="chat.write_private('+chat.users[id].id+')" alt="написать приватное сообщение для '+chat.users[id].nickname+'" title="написать приватное сообщение для '+chat.users[id].nickname+'" src="'+chat.users[id].picture+'"></img>';
	},
	// inserting message div by message object into chat window
	draw_message : function(message){
		chat.messages_count++;
		if(!chat.chat_messages_window){
			chat.chat_messages_window = document.createElement('DIV');
			chat.chat_messages_window.id = 'chat_messages_window';
			chat.divElement.appendChild(chat.chat_messages_window);
		}
		var odd = 0;
		if(chat.messages_count % 2 == 0) odd =1;
		var message_plank = document.createElement('div');
		message_plank.id = 'message_'+message.id;
		message_plank.className = 'message_plank'+(odd?' odd':'');
		if(message.is_private > 0){
			if(message.is_private == chat.authId){
				message_plank.className = message_plank.className+' private_to_me';
			}else{
				message_plank.className = message_plank.className+' private_from_me';
			}
			
		}
		
		message_plank.name = 'chat_message';
		
		var message_time_div = document.createElement('div');
		message_time_div.id = 'message_time_div'+message.id;
		message_time_div.className = 'message_time_div';
		message_plank.appendChild(message_time_div);
		var _time ='<abbr class="timeago" title="'+message.date_time+'">'+message.date_time+'</abbr>';
		message_time_div.innerHTML = _time + '<br clear="all"/><div class="chat_nickname">' + chat.users[message.id_user].nickname + '</div>';
		
		
		var message_author_div = document.createElement('div');
		message_author_div.id = 'message_author_div'+message.id;
		message_author_div.className = 'message_author_div';
		message_plank.appendChild(message_author_div);
		message_author_div.innerHTML = chat.draw_user(message.id_user)
		
		if(message.is_private > 0){
			var pmessage_author_div = document.createElement('div');
			pmessage_author_div.id = 'pmessage_author_div'+message.id;
			pmessage_author_div.className = 'pmessage_author_div';
			if(message.is_private == chat.authId)
				pmessage_author_div.className += ' forme';
			message_plank.appendChild(pmessage_author_div);
			pmessage_author_div.innerHTML = chat.draw_user(message.is_private)
		}
		
		
		var message_text_div = document.createElement('div');
		message_text_div.id = 'message_text_div'+message.id;
		message_text_div.className = 'message_text_div';
		message_plank.appendChild(message_text_div);
		message_text_div.innerHTML = message.message;
		message_plank.innerHTML += '<div class="chat_clear" />';
		// find place
		chat.chat_messages_window.appendChild(message_plank);
	},
	// getting browser's Cookie
	getCookie : function (c_name){
		var i,x,y,ARRcookies=document.cookie.split(";");
		for (i=0 ; i<ARRcookies.length; i++){
			x = ARRcookies[i].substr(0 , ARRcookies[i].indexOf("="));
			y = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			x = x.replace(/^\s+|\s+$/g , "");
			if (x == c_name)
				return unescape(y);
		}
		return false;
	},
	// making request
	request : function(data , callback){
		callback = callback ? callback : function(data){};
		data.jquery = 'chat_module';

		var request = $.ajax({
			cache: false,
			url: chat.server_url,
			type: "POST",
			data: data,
			timeout: 10000,
			dataType: "json",
			global: false
		});

		request.done(function(data){
			// request done
			callback(data);
		});
		request.fail(function(o , status){
			// we will try another time
			chat.status = chat.status_wait;
		});
	}
}
