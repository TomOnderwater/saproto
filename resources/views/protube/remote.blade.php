<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no"/>

    <meta name="theme-color" content="#C1FF00">

    <link rel="shortcut icon" href="{{ asset('images/favicons/favicon'.mt_rand(1, 4).'.png') }}"/>

    <title>S.A. Proto | Protube Remote</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://cdn.socket.io/socket.io-1.4.5.js"></script>

    <script>
        var server = "{!! env('HERBERT_SERVER') !!}";
        var token = 0;

        $(document).ready(function() {
            $.ajax({
                url: "{!! env('APP_URL') !!}/api/token",
                dataType: "jsonp",
                success: function(_token) {

                    token = _token;

                    var errorElement = $("body");

                for(var i = 0; i < 3; i++) {
                    input_flds.eq(i).val('');
                }
                input_flds.eq(0).focus();
            }

            remote.on("connect", function() {
                $("#connecting").hide(0);
                $("#connected").show(0);
                if(token) remote.emit("token", token);
            });

            setTimeout(function() {
                focusOnPin();
            }, 200);
                    var remote = io(server + '/protube-remote');

                    function focusOnPin() {
                        var input_flds = $('#pin-input').find(':input');

                        for(var i = 0; i < 3; i++) {
                            input_flds.eq(i).val('');
                        }
                        input_flds.eq(0).focus();
                    }

                    remote.on("connect", function() {
                        $("#connecting").hide(0);
                        $("#connected").show(0);
                        if(token) remote.emit("token", token);

                        focusOnPin();
                    });

                    remote.on("ytInfo", function(data) {
                        console.log('info', data);
                    });

                    remote.on("queue", function(data) {
                        $("#playing-queue ul").html("");
                        for(var i in data) {
                            var invisible = (data[i].showVideo ? '' : '<i class="fa fa-eye-slash" aria-hidden="true"></i>');
                            $("#playing-queue ul").append(`<li><img src="http://img.youtube.com/vi/${data[i].id}/0.jpg" /><h1>${data[i].title}${invisible}</h1></li>`);
                        }
                    });

                    $('#login').click(function(){
                        focusOnPin();
                    });

                    remote.on("reconnect", function() {
                        location.reload();
                    });

                    remote.on("disconnect", function() {
                        location.reload();
                    });

                    remote.on("queue", function(data) {
                        var queue = $("#queue");
                        queue.html("");

                        for(var i in data) {
                            queue.append('<img src="http://img.youtube.com/vi/' + data[i].id + '/0.jpg" />');
                        }
                    });

                    $('form').bind('submit', function(e){
                        e.preventDefault();
                        remote.emit("search", $("#searchBox").val());
                        $("#results").html("Loading...");
                    });

                    $(".pin").keyup(function(e) {
                        console.log('log');

                        var input_flds = $('#pin-input').find(':input');

                        if(e.keyCode == 8 && input_flds.index(this) > 0) {
                            input_flds.eq(input_flds.index(this) - 1).focus();
                        }
                        else if($(this).val().length == 1) {
                            input_flds.eq(input_flds.index(this) + 1).focus();
                        }
                        else if($(this).val().length > 1) {
                            $(this).val($(this).val()[0]);
                        }

                        if(input_flds.index(this) >= 2) {
                            var pincode = '';
                            for(var i = 0; i < 3; i++) {
                                pincode += input_flds.eq(i).val().toString();
                            }
                            remote.emit("authenticate", { 'pin' : pincode });
                        }
                    });

                    $('body').on('keydown', function(event){
                        if( $('#login').is(':visible') ) {
                            if(event.keyCode >= 48 && event.keyCode <= 57 ) { // 0-9 normal
                                $('.keyboard-key:contains("' + (event.keyCode - 48) + '")').click();
                            } else if(event.keyCode >= 96 && event.keyCode <= 105 ) { // 0-9 normal
                                $('.keyboard-key:contains("' + (event.keyCode - 96) + '")').click();
                            } else if( event.keyCode == 8 ) { // backspace
                                $('.keyboard-key.back').click();
                            }
                        }
                    });

                    remote.on("authenticated", function(correct) {
                        if(correct) {
                            $("#login").hide(0);
                            $("#loggedIn").show(0);
                        }
                        else{
                            $("#pin-input").css({
                                "animation": "shake 0.82s cubic-bezier(.36,.07,.19,.97) both"
                            });

                            setTimeout(function() {
                                $("#pin-input").css({
                                    "animation": "none"
                                });
                            }, 1000);

                            focusOnPin();
                        }
                    });

                    remote.on("searchResults", function(data) {
                        var results = $("#results");

                        results.html("");

                        for(var i in data) {
                            results.append(generateResult(data[i]));
                        }

                        $(".result").each(function(i) {
                            var current = $(this);
                            current.click(function(e) {
                                e.preventDefault();
                                console.log({
                                    id: current.attr("ytId"),
                                    showVideo: ($("#showVideo").prop("checked") ? true : false)
                                });
                                remote.emit("add", {
                                    id: current.attr("ytId"),
                                    showVideo: ($("#showVideo").prop("checked") ? true : false)
                                });
                            })
                        });
                    });


                }
            });
        });

        function generateResult(item) {
            var result = '<div class="result" ytId="' + item.id + '">' +
                    '<img src="http://img.youtube.com/vi/' + item.id + '/0.jpg" />' +
                    '<div>' +
                    '<h1>' + item.title + '</h1>' +
                    '<h2>' + item.channelTitle +  '</h2>' +
                    '<h3>' + item.duration + '</h3>' +
                    '</div>' +
                    '<div style="clear: both;"></div>' +
                    '</div>';

            return result;
        }
    </script>

    @include('website.layouts.assets.stylesheets')

    @section('stylesheet')
        @include('website.layouts.assets.customcss')
    @show

    <style>
        @import url("https://fonts.googleapis.com/css?family=Roboto:400,400italic,500,500italic,700,700italic,900,900italic,300italic,300,100italic,100");

        body {
            background-color: #333;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        input[type=number] {
            padding: 1rem;
            display: inline-block;
            width: 40px;
            height: 40px;
            text-align: center;
            font-size: 1.5rem;
            margin: 1rem .5rem;
        }

        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            /* display: none; <- Crashes Chrome on hover */
            -webkit-appearance: none;
            margin: 0; /* <-- Apparently some margin are still there even though it's hidden */
        }

        #pin-input {
            transform: translate3d(0, 0, 0);
            backface-visibility: hidden;
            display: block;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }

        @keyframes shake {
          10%, 90% {
            transform: translate3d(-2px, 0, 0);
          }

          20%, 80% {
            transform: translate3d(4px, 0, 0);
          }

          30%, 50%, 70% {
            transform: translate3d(-8px, 0, 0);
          }

          40%, 60% {
            transform: translate3d(8px, 0, 0);
          }
        }

        /*input {
            background-color: #000;
            color: #fff;
            border: #c3ff00 1px solid;
            font-size: 16px;
            padding: 20px;
        }*/

        button {
            background-color: #000;
            color: #fff;
            border: #c3ff00 1px solid;
            font-size: 16px;
            padding: 20px;
            width: 100%;
        }

        #connecting {
            display: block;
        }

        #connected {
            display: none;
        }

        .currently-playing ul {
            position: relative;
            padding: 0;
            margin: 0;
        }

        .currently-playing ul li {
            position: relative;
            width: 180px;
            height: 135px;
            display: inline-block;
            padding: 0;
            margin: 0;
        }

        .currently-playing ul li h1 {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 160px;
            height: 115px;
            overflow: hidden;
            font-size: 16px;
            font-weight: normal;
            color: #fff;
            text-shadow: #000 1px 1px;
            margin: 0;
            padding: 0;
        }

        #queue img {
            height: 100px;
        }

        .container--login {
            display: table;
            position: absolute;
            overflow: hidden;
            height: 100%;
            width: 100%;
        }

        .container--loggedin {
            overflow: hidden;
        }

        .input-box {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        #loggedIn {
            display: none;
        }

        #results {
            position: absolute;
            top: 75px;
            width: 100%;
        }

        .result {
            position: relative;
            background-color: #222;
            margin-bottom: 10px;
            cursor: pointer;
            height: 180px;
        }

        .input-group {
            width: 100%;
        }

        .currently-playing {
            position: fixed;
            padding: 1rem;
            background-color: #7FBA00;
            color: white;
            width: 100%;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .result > div {
            position: absolute;
            top: 0;
            left: 240px;
            right: 0;

            padding: 25px;
        }

        .result > div > h1 {
            font-size: 14px;
            margin: 0 0 5px;
            padding: 0 0 0 15px;
        }

        .result > div > h2 {
            font-size: 10px;
            margin: 0 0 5px;
            padding: 0 0 0 15px;
        }

        .result > div > h3 {
            font-weight: 300;
            font-size: 10px;
            margin: 0 0 5px;
            padding: 0 0 0 15px;
        }

        .result img {
            position: absolute;
            top: 0;
            left: 0;

            width: 240px;
            height: 180px;
        }

        .result:hover {
            background-color: #444;
        }
    </style>
</head>

<body>

<div id="connecting">
    Connecting...
</div>

<div id="connected">
    <div id="login" class="container--login">
        <section class="input-box">
            <form id="pin-input">
                <input name="pin-a" class="form-control pin pin--a" type="number" pattern="[0-9]*" inputmode="numeric" maxlength="1" />
                <input name="pin-b" class="form-control pin pin--b" type="number" pattern="[0-9]*" inputmode="numeric" maxlength="1" />
                <input name="pin-c" class="form-control pin pin--c" type="number" pattern="[0-9]*" inputmode="numeric" maxlength="1" />
            </form>
            <span>Enter Protube pin.</span>
        </section>

    </div>

    <div id="loggedIn" class="container container--loggedin">
        <form action="" method="get" class="navbar-form" role="search">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search" name="searchBox" id="searchBox">
                <div class="input-group-btn">
                    <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
                </div>
            </div>
        </form>

        <div id="results">
            <!-- Filled by JS -->
        </div>

        <footer id="playing-queue" class="currently-playing">
            <ul>
                <!-- Filled by JS -->
            </ul>
        </footer>
    </div>
</div>

</body>
</html>
