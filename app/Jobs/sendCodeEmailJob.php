<?php

namespace App\Jobs;

use App\Mail\CodeEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Jobs\DeleteCodeJob;

class sendCodeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $mailData,$userEmail;

    public function __construct($mailData,$userEmail)
    {
        $this->mailData=$mailData;
        $this->userEmail=$userEmail;
    }


    public function handle(): void
    {
        //$user=User::find($this->id);
        Mail::to($this->userEmail['email'])->send(new CodeEmail($this->mailData));
        //DeleteCodeJob::dispatch($this->userEmail)->delay(Carbon::now()->addMinutes(2));
    }
}
