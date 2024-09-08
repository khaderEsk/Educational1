<?php

namespace App\Jobs;

use App\Models\Ads;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EndDateAdsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user_id,$ads_id;
    public function __construct($user_id,$ads_id)
    {
        $this->user_id=$user_id;
        $this->ads_id=$ads_id;
    }


    public function handle(): void
    {
        $ads=Ads::find($this->ads_id);
        $ads->update(['active' => 0]);
        $user=User::find($this->user_id);
        $today=Carbon::today()->toDateString();
        if($ads->end_date==$today){

            $reservation_ads = $ads->reservation_ads()->get();
            $count = $reservation_ads->count();

            $wallet = $user->wallet()->first();
            $wallet->update([
                'value' => $count * $ads->price
            ]);

        }

    }
}
