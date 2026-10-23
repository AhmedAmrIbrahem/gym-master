<?php

namespace App\Http\Controllers;

use App\Actions\Workouts\SaveWorkoutExerciseAction;
use App\Http\Requests\StoreWorkoutExerciseRequest;
use App\Models\Exercise;
use App\Models\WorkoutDay;
use App\Models\WorkoutLog;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function index()
    {
        $activeProgram = auth()->user()->workoutPrograms()
            ->where('is_active', true)
            ->with('days.dayExercises.exercise')
            ->first();

        return view('dashboard', compact('activeProgram'));
    }

    public function start(WorkoutDay $day)
    {
        $this->authorizeDay($day);
        $day->load('dayExercises.exercise');

        // البحث عن جلسة اليوم إن وجدت
        $todaySession = WorkoutSession::where('user_id', auth()->id())
            ->where('workout_day_id', $day->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        $previousLogs = [];
        $savedTodayLogs = [];

        foreach ($day->dayExercises as $dayExercise) {
            // جلب آخر أداء سابق للتمرين
            $lastLog = WorkoutLog::where('exercise_id', $dayExercise->exercise_id)
                ->whereHas('workoutSession', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->when($todaySession, function ($q) use ($todaySession) {
                    $q->where('workout_session_id', '!=', $todaySession->id);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            $previousLogs[$dayExercise->exercise_id] = $lastLog;

            // جلب أداء اليوم لو كان اتسجل قبل كده
            if ($todaySession) {
                $savedTodayLogs[$dayExercise->exercise_id] = WorkoutLog::where('workout_session_id', $todaySession->id)
                    ->where('exercise_id', $dayExercise->exercise_id)
                    ->orderBy('set_number')
                    ->get();
            }
        }

        return view('workout.start', compact('day', 'previousLogs', 'savedTodayLogs', 'todaySession'));
    }

    // حفظ تمرين واحد فقط بشكل مستقل
    public function storeSingleExercise(
        StoreWorkoutExerciseRequest $request,
        SaveWorkoutExerciseAction $saveWorkoutExercise
    ) {
        $data = $request->validated();
        $day = WorkoutDay::findOrFail($data['workout_day_id']);
        $this->authorizeDay($day);

        abort_unless($day->dayExercises()->where('exercise_id', $data['exercise_id'])->exists(), 403);

        $saveWorkoutExercise->execute(
            userId: (int) $request->user()->id,
            workoutDayId: (int) $day->id,
            exerciseId: (int) $data['exercise_id'],
            sets: $data['sets'],
            notes: $data['notes'] ?? null,
        );

        return back()->with('success', 'تم حفظ بيانات التمرين بنجاح! 💪');
    }

    // عرض سجل التمارين التاريخي
    // عرض سجل التمارين التاريخي مع الفلاتر والإحصائيات
    public function history(Request $request)
    {
        Carbon::setLocale('ar');

        $query = WorkoutSession::where('user_id', auth()->id())
            ->with(['workoutDay', 'logs.exercise']);

        // فلتر بحسب اليوم (Push / Pull / Legs ...)
        if ($request->filled('workout_day_id')) {
            $query->where('workout_day_id', $request->workout_day_id);
        }

        // فلتر بحسب التمرين المباشر
        if ($request->filled('exercise_id')) {
            $query->whereHas('logs', function ($q) use ($request) {
                $q->where('exercise_id', $request->exercise_id);
            });
        }

        // فلتر بحسب التاريخ (من وإلى)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // بحث نصي
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('logs.exercise', function ($exQ) use ($search) {
                        $exQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $sessions = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // إحصائيات شريط الداش بورد المتقدم
        $totalSessions = WorkoutSession::where('user_id', auth()->id())->count();
        $totalLogs = WorkoutLog::whereHas('workoutSession', function ($q) {
            $q->where('user_id', auth()->id());
        })->count();

        // عدد الأيام الفعلية التي ذهبت فيها للجيم هذا الشهر الحالي
        $monthlyDaysCount = WorkoutSession::where('user_id', auth()->id())
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth()->addMonth())
            ->distinct()
            ->count('date');

        // قوائم الفلاتر
        $workoutDays = WorkoutDay::whereHas('program', function ($q) {
            $q->where('user_id', auth()->id());
        })->get();

        $exercises = Exercise::where(function ($q) {
            $q->whereNull('user_id')->orWhere('user_id', auth()->id());
        })->orderBy('name')->get();

        return view('workout.history', compact('sessions', 'totalSessions', 'totalLogs', 'monthlyDaysCount', 'workoutDays', 'exercises'));
    }

    private function authorizeDay(WorkoutDay $day): void
    {
        abort_unless($day->program()->where('user_id', auth()->id())->exists(), 403);
    }
}
