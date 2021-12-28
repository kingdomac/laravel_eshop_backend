<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        Category::factory(5)->hasProducts(20)->create();
        Category::factory(5)->isHome()->hasProducts(20)->create();
        User::factory(3)->create();
        User::factory(2)->create(["is_admin" => 1]);
    }
}
