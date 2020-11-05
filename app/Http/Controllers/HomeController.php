<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // $users = User::where('id', '!=', Auth::id())->get();

        $users = DB::select('select users.id, users.name, users.avatar, users.email, count(status) as unread from users
        LEFT JOIN message ON users.id = message.from and status = 0 and message.to = ' . Auth::id() . ' where users.id != '. Auth::id() . '
        group by users.id, users.name, users.avatar, users.email');

        return view('home', ['users' => $users]);
    }

    public function getMessage($userId1)
    {
        $userId2 = Auth::id();

        Message::where(['from' => $userId1, 'to' => $userId2])->update(['status' => 1]);

        $messages = Message::where(function($query) use($userId1, $userId2) {
            $query->where('from', $userId2)->where('to', $userId1);
        })->orWhere(function($query) use($userId1, $userId2) {
            $query->where('from', $userId1)->where('to', $userId2);
        })->get();

        return view('message.chat', ['messages' => $messages]);
    }

    public function sentMessage(Request $request)
    {
        $from = Auth::id();
        $to = $request->receive_id;

        $message = Message::create([
            'from' => $from,
            'to'   => $to,
            'body_message' => $request->message,
            'status'    => 0
        ]);

        $options = [
            'cluster'  => 'ap1',
            'useTLS'  => true
        ];

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options,
        );

        $data = ['from' => $from, 'to' => $to];

        $pusher->trigger('chat-channel', 'chat-event', $data);
    }
}
