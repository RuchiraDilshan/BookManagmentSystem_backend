<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class BookCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('book_categories')->insert([
            ['name' => 'Fiction', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Science', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'History', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Technology', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Biography', 'created_at' => now(), 'updated_at' => now()],
        ]);


    }
}
