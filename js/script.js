/*
 * Copyright (C) 2011 Johannes Bechberger
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function log(msg){
    console.log(msg);
}

/*$.ajaxError(function(){
    log("Kommunikation mit dem Server fehlgeschlagen...")
})*/

Application = {
    chat_update_pause: 500,
    chat_last_msg: 0,
    chat_max_msgs: 0xff,
    url_appendix: "",
    id: 0,
    uid: "",
    socket: null
}

function chatUpdateInitialize(last_msg, id, uid, wsserverstring, url_appendix){
    Application.chat_last_msg = last_msg;
    Application.url_appendix = url_appendix;
    Application.uid = uid;
    Application.id = id;
    /*
     * Websocket zwar implementiert ab nicht verwendet, da manglende Browserunterstützung
     *if ("MozWebSocket" in window || "WebSocket" in window){
        try {
            Application.id = id;
            Application.pwdstring = pwdstring;
            if ($.browser.mozilla){
                socket = new MozWebSocket(wsserverstring);
            }
            else {
                socket = new WebSocket(wsserverstring);
            }	
            Application.socket = socket;
            socket.onopen = function(msg){};
            socket.onmessage = function(msg){
                var response = JSON.parse(msg.data);
                switch(response.action){
                    case "newmessage":
                        chatAppendMsgHTML(response.data, 1);
                        window.getAttention();
                        break;
                    case "userlisthtml":
                        chatSetUserlistHTML(response.data);
                        break;
                }
            };
            socket.onclose = function(msg){};
            window.onclose = function(){
                socket.close();
            }
            $('#textsubmit').click(function(){
                var obj = $("#textinput");
                if (obj.val().length <= 1){
                    return;
                }
                chatSendData("addMsg", {
                    content: obj.val()
                });
            });	
            chatSendData("verification", {});
            return;
        } catch(exp){}
    }*/
            
    chatUpdate();
    $('#textsubmit').click(function(){
        chatSendMessage();
    });
    $("#textinput").keyup(function(event){
        if (event.keyCode == 13){
            $("#textsubmit").click();
        }
    });
}

function chatUpdate(){
    $.ajax({
        url: "chat.php",
        success: function(val){
            var arr = val.split("|");
            chatAppendMsgHTML(arr[1], arr[0]);
            chatSetUserlistHTML(arr[2]);
        },
        cache: false,
        data: "method=update&ajax&chat&last=" + Application.chat_last_msg + "&sid=" + Application.id + "&uid=" + Application.uid + "&" + Application.url_appendix
    });
    setTimeout(function(){
        chatUpdate()
    }, Application.chat_update_pause);
}

function chatAppendMsgHTML(msghtml, number){
    if (!msghtml){
        return;
    }
    $("#chat_area").append(msghtml);
    Application.chat_last_msg += Number(number);
    var childs = $("#chat_area").children();
    var num = Application.chat_max_msgs - childs.length;
    childs.each(function (val){
        if (num < 1){
            val.remove();
        } 
        num++;
    });
}

function chatSetUserlistHTML(userlisthtml){
    $("#chatuserlist_container").html(userlisthtml);
}

function chatSendMessage(){
    if ($("#textinput").val().length > 1){
        $.ajax({
            url: "chat.php",
            cache: false,
            data: "method=addmsg&ajax&chat&msg=" + $("#textinput").serialize()  + "&sid=" + Application.id + "&uid=" + Application.uid + "&" + Application.url_appendix
        });
        $("#textinput").val("");
    }
}

function chatSendData(action, data){
    if (Application.socket){
        if (!data){
            data = {};
        }
        var payload = new Object();				
        payload['action'] = action;
        data["id"] = Application.id;
        data["pwdstring"] = Application.pwdstring;
        payload['data'] = data;	
        Application.socket.send(JSON.stringify(payload));
    }
}