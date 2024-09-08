<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AdminNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $title,$body;

    public function __construct($title,$body)
    {
        $this->title=$title;
        $this->body=$body;
    }


    public function handle(): void
    {
        Notification::create([
            'title'=>$this->title,
            'body'=>$this->body,
            'user_id'=>1,
        ]);
    }
}
