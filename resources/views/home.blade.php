@extends('layouts.app')

@push('css')
    <style>

        ul {
            margin: 0;
            padding: 0;
        }

        li {
            list-style: none;
        }

        .user-wrapper, .message-wrapper {
            border: 1px solid #dddddd;
            overflow-y: auto;

        }

        .user-wrapper {
            height: 600px;

        }

        .user {
            cursor: pointer;
            padding: 5px 0;
            position: relative;
        }

        .user:hover {
            background: #eeeeee
        }

        .user:last-child {
            margin-bottom: 0;
        }

        .pending {
            position: absolute;
            left: 11px;
            top: 9px;
            background: #b600ff;
            margin: 0;
            border-radius: 50%;
            width: 15px;
            height: 14px;
            line-height: 10px;
            padding-left: 4px;
            color: #ffffff;
            font-size: 11px;
        }

        .media-left {
            margin: 0 10px;
        }

        .media-left img {
            width: 64px;
            border-radius: 64px;
        }

        .media-body p {
            margin: 6px 0;
        }

        .message-wrapper {
            padding: 10px;
            height: 536px;
            background: #eeeeee;
        }

        .messages .message {
            margin-bottom: 15px;
        }

        .messages .message:last-child {
            margin-bottom: 0;

        }

        .receive, .sent {
            width: 45%;
            padding: 3px 10px;
            border-radius: 10px;
        }

        .receive {
            background: #ffffff;
        }

        .sent {
            background: rgb(58, 148, 233);
            float: right;
            text-align: right;
        }

        .message p {
            margin: 5px 0;
        }

        .date {
            color: #777777;
            font-size: 12px;
        }

        .active {
            background: #eeeeee;
        }

        input[type=text] {
            width: 100%;
            padding: 12px 20px;
            margin: 15px 0 0;
            display: inline-block;
            border-radius: 4px;
            box-sizing: border-box;
            outline: none;
            border: 1px solid #cccccc;
        }

        input[type=text]:focus {
            border: 1px solid #aaaaaa;
        }



    </style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="user-wrapper">
                <ul class="users">
                    @foreach ($users as $user)
                        <li class="user" id="{{ $user->id }}">
                            @if ($user->unread)
                                <span class="pending">{{ $user->unread }}</span>
                            @endif

                            <div class="media">
                                <div class="media-left">
                                    <img src="{{ $user->avatar }}" alt="" class="media-object">
                                </div>

                                <div class="media-body">
                                    <p class="name">{{ $user->name }}</p>
                                    <p class="email">{{ $user->email }}</p>
                                </div>
                            </div>

                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="col-md-8" id="messages">

        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    var receiveId = ''
    var myId = `{{ Auth::id() }}`

    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')

            }
        })

        //begin pusher
        Pusher.logToConsole = true;

        var pusher = new Pusher('b9af7f7e3ca244df2ad9', {
            cluster: 'ap1',
            forceTLS: true
        });

        var channel = pusher.subscribe('chat-channel');
            channel.bind('chat-event', function(data) {
            // alert(JSON.stringify(data));

            if (myId == data.from) {
                $('#'+ data.to).click()
            } else if (myId == data.to) {
                if (receiveId == data.from) {
                    $('#'+ data.from).click()
                } else {
                    var pending = parseInt($('#'+ data.from).find('.pending').html())

                    if (pending) {
                        $('#'+ data.from).find('.pending').html(pending + 1)
                    } else {
                        $('#'+ data.from).append('<span class="pending">1</span>')
                    }
                }
            }
        });
        //end pusher

        $('.user').click(function() {
            $('.user').removeClass('active')
            $(this).addClass('active')
            $(this).find('.pending').remove()

            receiveId = $(this).attr('id')

            $.ajax({
                type: 'get',
                url: 'message/'+receiveId,
                data: '',
                cache: false,
                success: function(data) {
                    $('#messages').html(data)
                    scrollToBottom()
                }
            })
        })

        $(document).on('keyup', '.input-text input', function(e) {
            var message = $(this).val()

            if (e.keyCode == 13 && message != '' && receiveId != '') {
                $(this).val('')

                var urlStr = 'receive_id=' + receiveId + '&message=' + message

                $.ajax({
                    type: 'post',
                    url: 'message',
                    data: urlStr,
                    cache: false,
                    success: function(data) {

                    },
                    error: function(jqXHR,status, err) {

                    },
                    complete: function() {
                        scrollToBottom()
                    }
                })
            }
        })
    })

    function scrollToBottom() {
        $('.message-wrapper').animate({
            scrollTop: $('.message-wrapper').get(0).scrollHeight
        }, 50)
    }
</script>
@endpush
