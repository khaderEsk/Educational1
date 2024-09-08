<?php

namespace App\Jobs;

use App\Traits\GeneralTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificationJobUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GeneralTrait;

    private $user,$title,$body;
    public function __construct($user,$title,$body)
    {
        $this->title=$title;
        $this->body=$body;
        $this->user=$user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $this->send($this->user,$this->title,$this->body);
    }
}
