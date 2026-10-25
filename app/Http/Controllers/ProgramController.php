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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\WorkoutDay;
use App\Models\WorkoutSession;
use App\Models\WorkoutProgram;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProgramController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // Load only the current page's programs and their related day cards.
        // The exercise bank is fetched separately, only when a picker opens.
        $programs = $request->user()->workoutPrograms()
            ->with('days.dayExercises.exercise')
            ->orderBy('id')
            ->paginate(5);

        return view('programs.index', compact('programs'));
    }

    public function searchExercises(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['sometimes', 'nullable', 'string', 'max:80'],
        ]);

        $term = trim($validated['q'] ?? '');

        // Identical access scope to the exercises bank and attach action:
        // global exercises and the authenticated user's own exercises.
        $exercises = Exercise::query()
            ->accessibleTo($request->user())
            ->when($term !== '', fn ($query) => $query->where(function ($matched) use ($term) {
                $matched->where('name', 'like', '%'.$term.'%')
                    ->orWhere('target_muscle', 'like', '%'.$term.'%');
            }))
            ->orderBy('target_muscle')
            ->orderBy('name')
            ->orderBy('id')
            ->limit(40)
            ->get(['id', 'name', 'target_muscle']);

        return response()->json(['items' => $exercises])
            ->header('Cache-Control', 'private, no-store');
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

        // Preserve the linked-record deletion safeguard from the earlier patch.
        if ($program->days()->exists()) {
            return back()->with('error', 'لا يمكن حذف النظام التدريبي لأنه يحتوي على أيام مرتبطة به. حذف النظام سيمسح الأيام وتمارينها؛ احذف العناصر غير المستخدمة أولًا.');
        }

        $program->delete();

        return back()->with('success', 'تم حذف النظام بنجاح.');
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

        if (WorkoutSession::where('workout_day_id', $day->id)->exists()) {
            return back()->with('error', 'لا يمكن حذف اليوم لأنه مرتبط بجلسات تدريب مسجلة. حذف اليوم سيؤدي لفقدان تاريخ التمرين.');
        }

        if ($day->dayExercises()->exists()) {
            return back()->with('error', 'لا يمكن حذف اليوم لأنه يحتوي على تمارين مرتبطة به. أزل التمارين أولًا إذا لم تكن مستخدمة.');
        }

        $day->delete();

        return back()->with('success', 'تم حذف اليوم بنجاح.');
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
