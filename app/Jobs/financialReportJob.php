<?php

namespace App\Jobs;

use App\Models\ProfitRatio;
use App\Models\FinancialReport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class financialReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $type,$price,$user;

    public function __construct($type,$price,$user)
    {
        $this->type=$type;
        $this->price=$price;
        $this->user=$user;
    }


    public function handle(): void
    {
        /*start Khader */
        $profit = ProfitRatio::where('type', $this->type)->first();
        $financialReport =FinancialReport::create([
            'type' => $this->type,
            'teacherName' => $this->user->name,
            'value' => $this->price,
            'ProfitAmount' => $this->price * ($profit->value / 100),
            'profitRatio' => $profit->value
        ]);
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();
        $admin->load('wallet');
        $admin->wallet->update([
            'value' => $admin->wallet->value + $this->price * ($profit->value / 100)
        ]);
        /*end khader */

        $this->user->wallet->update([
            'value' =>$this->user->wallet->value-$profit->value
        ]);
    }
}
