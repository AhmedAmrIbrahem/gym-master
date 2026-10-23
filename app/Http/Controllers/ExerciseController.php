<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExerciseFormRequest;
use App\Models\Exercise;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Exercise::query()->accessibleTo($request->user());

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('target_muscle', 'like', '%'.$request->search.'%');
            });
        }

        $exercises = $query->orderBy('target_muscle')->orderBy('name')->paginate(15);

        return view('exercises.index', compact('exercises'));
    }

    public function store(ExerciseFormRequest $request)
    {

        Exercise::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'target_muscle' => $request->target_muscle,
        ]);

        return back()->with('success', 'تم إضافة التمرين بنجاح!');
    }

    public function update(ExerciseFormRequest $request, Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $exercise->update([
            'name' => $request->name,
            'target_muscle' => $request->target_muscle,
        ]);

        return back()->with('success', 'تم تعديل بيانات التمرين بنجاح!');
    }

    public function destroy(Exercise $exercise)
    {
        $this->authorize('delete', $exercise);

        $exercise->delete();

        return back()->with('success', 'تم حذف التمرين بنجاح.');
    }
}
