<?php

namespace App\Jobs;

use App\Traits\GeneralTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class NotificationJobProfile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GeneralTrait;

    private $profile,$title,$body;
    public function __construct($profile,$title,$body)
    {
        $this->title=$title;
        $this->body=$body;
        $this->profile=$profile;
    }


    public function handle(): void
    {
        $user=$this->profile->user()->first();

        $this->send($user,$this->title,$this->body);
    }
}
