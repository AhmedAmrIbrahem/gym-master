<?php

namespace App\Http\Controllers;

use App\Actions\Programs\ActivateProgramAction;
use App\Actions\Programs\AttachExerciseToDayAction;
use App\Actions\Programs\CreateProgramAction;
use App\Http\Requests\AttachExerciseRequest;
use App\Http\Requests\ProgramNameRequest;
use App\Http\Requests\UpdateDayExerciseRequest;
use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\WorkoutDay;
use App\Models\WorkoutProgram;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProgramController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $programs = auth()->user()->workoutPrograms()->with('days.dayExercises.exercise')->get();

        // Fetch once per request, rather than querying from inside each day card.
        $groupedExercises = Exercise::query()
            ->accessibleTo(auth()->user())
            ->orderBy('name')
            ->get()
            ->groupBy('target_muscle');

        return view('programs.index', compact('programs', 'groupedExercises'));
    }

    public function store(ProgramNameRequest $request, CreateProgramAction $action)
    {
        $action->execute($request->user(), $request->validated('name'));

        return back()->with('success', 'تم إنشاء النظام بنجاح!');
    }

    public function update(ProgramNameRequest $request, WorkoutProgram $program)
    {
        $this->authorize('update', $program);

        $program->update(['name' => $request->name]);

        return back()->with('success', 'تم تعديل اسم النظام بنجاح!');
    }

    public function destroy(WorkoutProgram $program)
    {
        $this->authorize('delete', $program);

        $program->delete();

        return back()->with('success', 'تم حذف النظام بالكامل!');
    }

    public function activate(WorkoutProgram $program, ActivateProgramAction $action)
    {
        $this->authorize('activate', $program);

        $action->execute(auth()->user(), $program);

        return back()->with('success', 'تم تفعيل النظام بنجاح!');
    }

    public function storeDay(ProgramNameRequest $request, WorkoutProgram $program)
    {
        $this->authorize('addDay', $program);

        $program->days()->create(['name' => $request->name]);

        return back()->with('success', 'تم إضافة اليوم للنظام!');
    }

    public function updateDay(ProgramNameRequest $request, WorkoutDay $day)
    {
        $this->authorize('update', $day);

        $day->update(['name' => $request->name]);

        return back()->with('success', 'تم تعديل اسم اليوم بنجاح!');
    }

    public function destroyDay(WorkoutDay $day)
    {
        $this->authorize('delete', $day);

        $day->delete();

        return back()->with('success', 'تم حذف اليوم بجميع تمارينه!');
    }

    public function attachExercise(AttachExerciseRequest $request, WorkoutDay $day, AttachExerciseToDayAction $action)
    {
        $this->authorize('attachExercise', $day);

        $action->execute(auth()->user(), $day, $request->validated());

        return back()->with('success', 'تم إضافة التمرين لليوم!');
    }

    public function updateDayExercise(UpdateDayExerciseRequest $request, DayExercise $dayExercise)
    {
        $this->authorize('update', $dayExercise->workoutDay);

        $dayExercise->update([
            'default_sets' => $request->default_sets,
            'default_reps' => $request->default_reps,
        ]);

        return back()->with('success', 'تم تعديل عدد المجموعات والعدات!');
    }

    public function destroyDayExercise(DayExercise $dayExercise)
    {
        $this->authorize('delete', $dayExercise->workoutDay);

        $dayExercise->delete();

        return back()->with('success', 'تم إزالة التمرين من اليوم!');
    }
}
