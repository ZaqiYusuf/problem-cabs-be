<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Location;
use App\Models\Package;
use App\Models\Periode;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        User::create([
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'level' => 'administrator',
            'block' => 'NO',
        ]);

        // $user =  User::create([
        //     'email' => 'user@gmail.com',
        //     'password' => bcrypt('password'),
        //     'level' => 'user',
        //     'block' => 'NO',
        // ]);

        // $cat = Category::create([
        //     'item' => 'Tenant',
        //     'type' => 'Truck'
        // ]);

        // Category::create([
        //     'item' => 'Non Tenant',
        //     'type' => 'Truck'
        // ]);

        // Vehicle::create([
        //     'plate_number' => 'F 1233 XXX',
        //     'no_lambung' => '7',
        //     'number_stiker' => 1,
        //     'stnk' => '39842934792384',
        //     'category_id' => $cat->id,
        // ]);

        // Periode::create([
        //     "amount" => "3",
        //     "tipe" => "Bulan",
        //     "category_id" => $cat->id,
        //     "cost" =>  "2000000"
        // ]);

        // Driver::create([
        //     'name_driver' => "Akmal",
        //     'sim' => "390039890902",
        // ]);

        Tenant::create([
            'name_tenant' => 'PKT',
        ]);

        Location::create([
            'location' => 'Tj Harapan',
        ]);

        // Package::create([
        //     'item' => 'Non Tenant',
        //     'type' => 'Vehicle',
        //     'periode' => '3',
        //     'periodeType' => 'Months',
        //     'price' => '75000',
        //     'detail' => 'Light Vehicle (Niaga)'
        // ]);

        // Customer::create([
        //     "user_id" => $user->id,
        //     "email" => $user->email,
        //     "name_customer" => "PT Angkasa Pura II",
        //     "address" => "Bekasi",
        //     "pic" => "Agus",
        //     "pic_number" => "8282828282",
        // ]);
    }
}
