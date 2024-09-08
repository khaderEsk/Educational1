<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AdminSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'lujain',
            'email' => 'lujainshqer11@gmail.com',
            'password' => bcrypt('12341234a'),
            'address' => 'malke',
            'governorate' => 'Damascus',
            'birth_date' => '1999-9-9',
        ]);

        $admin=User::find(1);

        $role = Role::where('name','admin')->first();
        $admin->assignRole($role);

        $wallet = Wallet::create([
            'user_id' => $admin->id,
            'number' => random_int(1000000000000, 9000000000000),
            'value' => 0,
        ]);
    }
}
