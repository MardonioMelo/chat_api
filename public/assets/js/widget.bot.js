
const baseUrl = "";
const msgerChat = $(".j_msger_chat");
const datachat = $(".j_data_chatbot");
const BOT_NAME = datachat[0].dataset.botname;
const BOT_IMG = datachat[0].dataset.botimg;
const PERSON_IMG = datachat[0].dataset.userimg;
const PERSON_NAME = datachat[0].dataset.username;
const PERSON_ID = datachat[0].dataset.userid;
const DEST_ID = datachat[0].dataset.userdestid;
const url_websocket = datachat[0].dataset.urlwebsocket;
const patch = baseUrl + "bot";
const imgloading = baseUrl + 'public/assets/img/carregando.gif';
const open = ".chatbox-open";
const close = ".chatbox-close";
const popup = ".chatbox-popup";
const popup_close = popup + ", " + close;
const maximize = ".chatbox-maximize";
const minimize = ".chatbox-minimize";
const popup_open_close = popup + ", " + open + ", " + close;
const panel = ".chatbox-panel";
const panel_close = ".chatbox-panel-close";
const scroll_popup = '.j_scroll_chat_popup';
const scroll_panel = '.j_scroll_chat_panel';
const send_popup = '.j_bot_send_popup';
const text_popup = '.j_bot_text_popup';
const send_panel = '.j_bot_send_panel';
const text_panel = '.j_bot_text_panel';
const bot_option = '.j_bot_option';
const conn = new WebSocket(url_websocket);
var is_Bot = false;


// *********************************
//  Widget
// *********************************

//Enviar e receber dados do bot
function send_bot(classe_input) {

    let time = hora();
    let msgerInput = $(classe_input);
    let personMsg = msgerInput.val();
    let data = {
        "driver": "web",
        "userId": PERSON_ID,
        "userDestId": DEST_ID,
        "message": personMsg,
        "type": "text",
        "time": time
    };

    appendMessage(PERSON_NAME, PERSON_IMG, "right", { "type": "text", "text": personMsg }, time);
   
    if (is_Bot === true) {
        $.ajax({
            url: patch,
            data: data,
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function () {
                appendMsgWait(true);
                msgerInput.val('');
            },
            success: function (result) {

                if (result.status === 200) {
                    result.messages.forEach(function (messages) {
                        botResponse(messages, time);
                    });
                } else {
                    botResponse({ "type": "text", "text": "Opss! Volte mais tarde!" }, time);
                }
            }
        });

    } else {    

        delete data.message
        data.text = personMsg
        data.attachment = null
      
        sendMessage(JSON.stringify(data))
    }

    return false;
}

//Enviar e receber dados do bot conforme opção
function send_bot_option(text) {

    let time = hora();
    let data = {
        "driver": "web",
        "userId": PERSON_ID,
        "userDestId": DEST_ID,
        "message": text.value,
        "type": "text",
        "time": time
    };

    appendMessage(PERSON_NAME, PERSON_IMG, "right", { "type": "text", "text": text.text }, time);

    $.ajax({
        url: patch,
        data: data,
        type: 'POST',
        dataType: 'JSON',
        beforeSend: function () {
            appendMsgWait(true);
        },
        success: function (result) {

            if (result.status === 200) {
                result.messages.forEach(function (messages) {
                    botResponse(messages, time);
                });
            } else {
                botResponse({ "type": "text", "text": "Opss! Volte mais tarde!" }, time);
            }
        }
    });
    return false;
}

//html padrão das mensagens
function appendMessage(name, avatar, side, messages, time) {
    let content = "";

    if (messages.type === "text") {

        if (messages.attachment === null || messages.attachment === undefined) {
            content += '<div class="msg-text"><p>' + messages.text + '</p></div>';
        } else if (messages.attachment.type === "image") {
            content += '<p><img src="' + messages.attachment.url + '" alt="Imagem"><br><b>'
                + messages.attachment.title + '</b></p><p>' + messages.text + '</p>';
        } else if (messages.attachment.type === "audio") {
            content += '<audio controls style="width: 250px">'
                + '<source src="' + messages.attachment.url + '" type="audio/mpeg">'
                + messages.attachment.title
                + '</audio>';
        }

    } else if (messages.type === "actions") {
        content += '<div class="msg-text"><p>' + messages.text + '</p><p>';

        messages.actions.forEach(function (action) {
            if (action.type === "button") {
                content += '<a class="'
                    + action.additional.classes + '" data-value="'
                    + action.value + '">'
                    + action.text + '</a> ';
            }
        });

        content += '</p></div>';
    }

    msgerChat.append('<div class="msg ' + side + '-msg">' +
        '<div class="msg-img" style="background-image: url(' + avatar + ')"></div>' +
        '<div class="msg-bubble">' +
        '<div class="msg-info">' +
        '<div class="msg-info-name">' + name + '</div>' +
        '<div class="msg-info-time">' + time + '</div>' +
        '</div>' +
        '<div class="msg-text">' + content + '</div>' +
        '</div>' +
        '</div>');
    addScroll();

    //Enviar ao clicar no btn de opção do bot
    $(bot_option).click(function () {
        let data = { "text": $(this)[0].innerText, "value": $(this)[0].dataset.value };
        send_bot_option(data);
    });
}

//html da mensagem em espera
function appendMsgWait(set) {

    const msgWait = $('.j_MsgWait');
    const msgHTML = '<div class="msg left-msg j_MsgWait">' +
        '<div class="msg-img" style="background-image: url(' + BOT_IMG + ')"></div>' +
        '<div class="msg-bubble">' +
        '<div class="msg-info">' +
        '<div class="msg-info-name">' + BOT_NAME + '</div>' +
        '</div>' +
        '<div class="msg-text"><img src="' + imgloading + '" width="100" alt="Imagem"></div>' +
        '</div>' +
        '</div>';

    if (set) {
        msgerChat.append(msgHTML);
    } else {
        msgWait.remove();
    }
}

//Mensagem do bot
function botResponse(messages, time) {

    setTimeout(() => {
        appendMsgWait(false);
        appendMessage(BOT_NAME, BOT_IMG, "left", messages, time);
    }, 1000);
    addScroll();
}

//Adicionar mais scroll
function addScroll() {
    document.querySelector(scroll_popup).scrollTop += 500;
    document.querySelector(scroll_panel).scrollTop += 550;
}

//Hora e minutos da mensagem
function hora() {
    let now = new Date;
    return now.getHours() + ":" + ("0" + now.getMinutes()).slice(-2);
}

// *********************************
//  Socket
// *********************************

//Carregar histórico de msg 
function inputHistoric(msg = "Conexão ok!") {
    //aqui deve fazer um loop no histórico para carregar cada msg por vez
    appendMessage(BOT_NAME, BOT_IMG, "left", { "type": "text", "text": msg }, hora())
}

//Abrir conexão
function openConSocket() {
    conn.onopen = function (e) {
        inputHistoric(e.data);
    };
}

//Receber msg
function getNewMessage() {
    conn.onmessage = function (e) {    
        let data = JSON.parse(e.data)  
        appendMessage(BOT_NAME, BOT_IMG, "left", data, data.time)
    };
}

//Enviar msg
function sendMessage(msg) {
    conn.send(msg);
}

openConSocket()
getNewMessage()

/* ======= widget do chatbox ======= */

$(open).click(function () {
    $(popup_close).fadeIn()
});

$(close).click(function () {
    $(popup_close).fadeOut()
});

$(maximize).click(function () {
    $(popup_open_close).fadeOut();
    $(panel).fadeIn();
    $(panel).css({ display: "flex" });
});

$(minimize).click(function () {
    $(panel).fadeOut();
    $(popup_open_close).fadeIn();
});

$(panel_close).click(function () {
    $(panel).fadeOut();
    $(open).fadeIn();
});

/* ================================= */

//Enviar ao clicar no btn - popup
$(send_popup).click(function () {
    send_bot(text_popup);
});

//Enviar ao pressionar Enter na caixa de msg - popup
$(text_popup).keypress(function (e) {
    if (e.which === 13) {
        send_bot(text_popup);
    }
});

//Enviar ao clicar no btn - panel
$(send_panel).click(function () {
    send_bot(text_panel);
});

//Enviar ao pressionar Enter na caixa de msg - panel
$(text_panel).keypress(function (e) {
    if (e.which === 13) {
        send_bot(text_panel);
    }
});