$(document).ready(function () {

    //Config
    const host_http = "http://localhost:81/chatbot_api"
    const html_chat = "http://localhost:81/chatbot_api/widget"

    const chat = $("#j_chat_user")

    //Obter o 1º token
    function createToken(uuid, type, public) {

        $.ajax({
            type: "POST",
            url: host_http + "/api/create/token",
            data: {
                "uuid": uuid,
                "type": type,
                "public": public
            },
            dataType: "json",
            success: function (response) {
                if (response.result) {
                    saveDataToken(response.error)                   
                } else {
                    saveDataToken(null)
                    console.log(response.error.msg)
                }
            },
            error: function (response) {
                saveDataToken(null)
                console.log(response.responseText)
            }
        });
    }

    //Set box do chat
    function setBoxChat(token) {
        $.ajax({
            type: 'GET',
            url: html_chat,
            dataType: "html",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Authorization", token);
            },
            success: function (response) {
                chat.html(response);
            }
        })
    }    

    //Init
    function initChat() {
        var token = getToken()     
       
        if (token == null || token == "" || getNowSeg() > getExpTime(token)) {
            createToken(chat[0].dataset.uuid, chat[0].dataset.type, chat[0].dataset.public)
            setBoxChat(getToken())
        } else {
            setBoxChat(token)
        }       
    }

    //Salvar token no session storage
    function saveDataToken(data) {
        if (data == null) {
            sessionStorage.setItem('token_chat', "");           
        } else {
            sessionStorage.setItem('token_chat', data.token);           
        }
    }

    //Recuperar token do session storage
    function getToken() {
        return sessionStorage.getItem('token_chat');
    }

    //Tempo atual em segundos
    function getNowSeg() {
        return parseInt(Date.now() / 1000)
    }

    //Tempo de expiração do token
    function getExpTime(token){        
        var [,base] = token.split(".")
        return JSON.parse(atob(base)).exp
    }

    //Init
    initChat()
});