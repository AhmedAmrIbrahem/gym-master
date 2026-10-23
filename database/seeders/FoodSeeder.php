<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            ['name' => 'جبنة قريش', 'calories_per_100g' => 98, 'protein_per_100g' => 11, 'carbs_per_100g' => 3.4, 'fats_per_100g' => 4.3],
            ['name' => 'فول إسكندراني', 'calories_per_100g' => 110, 'protein_per_100g' => 7.5, 'carbs_per_100g' => 14, 'fats_per_100g' => 3],
            ['name' => 'كشري أصفر (عدس وأرز)', 'calories_per_100g' => 145, 'protein_per_100g' => 6, 'carbs_per_100g' => 27, 'fats_per_100g' => 1.5],
            ['name' => 'بطاطا حلوة مشوية', 'calories_per_100g' => 90, 'protein_per_100g' => 2, 'carbs_per_100g' => 21, 'fats_per_100g' => 0.2],
            ['name' => 'صدور دجاج مطبوخة', 'calories_per_100g' => 165, 'protein_per_100g' => 31, 'carbs_per_100g' => 0, 'fats_per_100g' => 3.6],
            ['name' => 'بيض مسلوق (حبة متوسطة ~50ج)', 'calories_per_100g' => 155, 'protein_per_100g' => 12.6, 'carbs_per_100g' => 1.1, 'fats_per_100g' => 10.6],
            ['name' => 'أرز أبيض مطبوخ', 'calories_per_100g' => 130, 'protein_per_100g' => 2.7, 'carbs_per_100g' => 28, 'fats_per_100g' => 0.3],
            ['name' => 'شوفان حبة كاملة', 'calories_per_100g' => 389, 'protein_per_100g' => 16.9, 'carbs_per_100g' => 66, 'fats_per_100g' => 6.9],
            ['name' => 'موز (حبة متوسطة)', 'calories_per_100g' => 89, 'protein_per_100g' => 1.1, 'carbs_per_100g' => 23, 'fats_per_100g' => 0.3],
            ['name' => 'زيت زيتون', 'calories_per_100g' => 884, 'protein_per_100g' => 0, 'carbs_per_100g' => 0, 'fats_per_100g' => 100],
        ];

        foreach ($foods as $food) {
            Food::updateOrCreate(['name' => $food['name']], $food);
        }
    }
}
