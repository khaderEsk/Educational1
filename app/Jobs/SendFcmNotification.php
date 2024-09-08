<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendFcmNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id,$message,$title;

    public function __construct($user_id,$message,$title)
    {
        $this->user_id=$user_id;
        $this->message=$message;
        $this->title=$title;
    }


    public function handle(): void
    {
        $SERVER_KEY=env('FCM_SERVER_KEY');
        $user=User::find($this->user_id);
        $fcm=Http::acceptJson()->withToken($SERVER_KEY)
            ->post('https://fcm.googleapis.com/fcm/send',
            [
                'to'=>$user->fcm_token,
                'notification'=>
                [
                    'title'=>$this->title,
                    'body'=>$this->message
                ]
            ]);
        info( json_decode($fcm));
    }
}
