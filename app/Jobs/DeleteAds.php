<?php

namespace App\Jobs;

use App\Models\Ads;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteAds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $id;
    public function __construct($id)
    {
        $this->id=$id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ads=Ads::find($this->id);

        $reservation_ads=$ads->reservation_ads()->get();
        $ads->delete();

        foreach ($reservation_ads as $item)
        {
            $profile_student=$item->profile_student()->first();
            $user=$profile_student->user()->first();
            $wallet=$user->wallet()->first();
            $wallet->update([
                'value'=>$wallet->value+$ads->price
            ]);
        }
    }
}
