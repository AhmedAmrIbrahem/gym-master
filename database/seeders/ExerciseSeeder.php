<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            // Chest
            ['name' => 'Incline Dumbbell Press', 'target_muscle' => 'Chest'],
            ['name' => 'Flat Barbell Bench Press', 'target_muscle' => 'Chest'],
            ['name' => 'Cable Flyes', 'target_muscle' => 'Chest'],

            // Back
            ['name' => 'Lat Pulldown', 'target_muscle' => 'Back'],
            ['name' => 'T-Bar Row', 'target_muscle' => 'Back'],
            ['name' => 'Seated Cable Row', 'target_muscle' => 'Back'],

            // Shoulders
            ['name' => 'Face Pulls', 'target_muscle' => 'Shoulders'],
            ['name' => 'Overhead Dumbbell Press', 'target_muscle' => 'Shoulders'],
            ['name' => 'Lateral Raises', 'target_muscle' => 'Shoulders'],

            // Legs
            ['name' => 'Barbell Squat', 'target_muscle' => 'Legs'],
            ['name' => 'Leg Press', 'target_muscle' => 'Legs'],
            ['name' => 'Romanian Deadlift', 'target_muscle' => 'Legs'],

            // Arms
            ['name' => 'Bicep Barbell Curl', 'target_muscle' => 'Arms'],
            ['name' => 'Tricep Rope Pushdown', 'target_muscle' => 'Arms'],
        ];

        foreach ($exercises as $exercise) {
            Exercise::create($exercise);
        }
    }
}
