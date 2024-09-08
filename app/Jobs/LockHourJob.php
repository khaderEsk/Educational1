<?php

namespace App\Jobs;

use App\Models\ServiceTeacher;
use App\Traits\GeneralTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LockHourJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,GeneralTrait;

    private $service_id,$title,$body;

    public function __construct($service_id,$title,$body)
    {
        $this->service_id=$service_id;
        $this->title=$title;
        $this->body=$body;
    }


    public function handle(): void
    {
        $service=ServiceTeacher::find($this->service_id);
        $profile=$service->profile_teacher;
        $this->send($profile->user,$this->title,$this->body);
    }
}
