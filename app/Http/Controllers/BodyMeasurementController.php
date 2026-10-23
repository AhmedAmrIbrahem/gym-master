<?php

namespace App\Http\Controllers;

use App\Models\BodyMeasurement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BodyMeasurementController extends Controller
{
    public function index()
    {
        Carbon::setLocale('ar');

        $measurements = auth()->user()->bodyMeasurements()
            ->orderBy('date', 'desc')
            ->paginate(10);

        $latest = auth()->user()->bodyMeasurements()->orderBy('date', 'desc')->first();
        $previous = auth()->user()->bodyMeasurements()->orderBy('date', 'desc')->skip(1)->first();

        // حساب فارق الوزن عن التسجيل السابق
        $weightChange = null;
        if ($latest && $previous) {
            $weightChange = $latest->weight - $previous->weight;
        }

        // متوسط الوزن هذا الشهر
        $monthlyAvgWeight = auth()->user()->bodyMeasurements()
            ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->avg('weight');

        return view('measurements.index', compact('measurements', 'latest', 'weightChange', 'monthlyAvgWeight'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'weight' => 'required|numeric|min:20|max:300',
            'height' => 'required|numeric|min:50|max:250', // أصبح إجبارياً الآن
            'chest' => 'nullable|numeric|min:10|max:200',
            'waist' => 'nullable|numeric|min:10|max:200',
            'biceps' => 'nullable|numeric|min:10|max:100',
            'thighs' => 'nullable|numeric|min:10|max:150',
            'neck' => 'nullable|numeric|min:10|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        auth()->user()->bodyMeasurements()->updateOrCreate(
            ['date' => $request->date],
            $validated
        );

        return back()->with('success', 'تم حفظ قياسات الوزن والجسم بنجاح! ⚖️');
    }

    public function destroy(BodyMeasurement $measurement)
    {
        if ($measurement->user_id !== auth()->id()) {
            abort(403);
        }

        $measurement->delete();

        return back()->with('success', 'تم حذف السجل بنجاح.');
    }
}
