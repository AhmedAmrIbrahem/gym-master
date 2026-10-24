<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;

/** Read-only answers scoped to the authenticated user passed by the controller. */
class ChatbotMemberDataService
{
    private function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);
        $text = str_replace(['أ', 'إ', 'آ', 'ى', 'ـ'], ['ا', 'ا', 'ا', 'ي', ''], $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public function answer(User $user, string $question): ?string
    {
        $q = $this->normalize($question);

        // Personal account overview: only queries scoped through this User's relations.
        if (preg_match('/(?:ملخص حسابي|ملخص بياناتي|معلوماتي المسجله|معلوماتي في التطبيق|اعرف عني ايه|تعرف عني ايه|ايه البيانات اللي عندك عني|كل بياناتي|ملفي الرياضي)/u', $q)) {
            return $this->accountOverview($user);
        }

        // Read-only profile information belonging to the signed-in member.
        if (preg_match('/(?:اسمي ايه|اسمي اي|اسمي كام|اسم حسابي|ما اسمي|ايه اسمي|بريدي ايه|ايميلي ايه|الايميل بتاعي|البريد بتاعي|رقم موبايلي|رقم تليفوني|رقمي كام|رقم هاتفي)/u', $q)) {
            if (preg_match('/(?:بريد|ايميل|ايميل|الايميل)/u', $q)) {
                return 'البريد الإلكتروني المسجل في حسابك: '.$user->email;
            }
            if (preg_match('/(?:رقم|موبايل|تليفون|هاتف)/u', $q)) {
                return $user->phone ? 'رقم الهاتف المسجل في حسابك: '.$user->phone : 'لم يتم تسجيل رقم هاتف في حسابك.';
            }
            return 'اسمك المسجل في حساب Gym Master: '.$user->name;
        }

        // Questions about the member's saved height must use their own measurements,
        // not the AI fallback. Keep numeric/calculator input handled by the calculator.
        if (preg_match('/(?:طولي كام|طولي كم|كم طولي|كام طولي|ايه طولي|ما هو طولي|طولي الحالي|اخر طول|الطول المسجل|الطول بتاعي|طولي قد ايه|طولي قد اي|طولي ايه|طولي كام سم)/u', $q)
            && ! preg_match('/\d/u', $q)) {
            $measurement = $user->bodyMeasurements()
                ->whereNotNull('height')
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->first();

            return $measurement !== null
                ? 'آخر طول مسجل لك: '.$measurement->height.' سم (بتاريخ '.$measurement->date->format('Y-m-d').').' 
                : 'لا يوجد طول مسجل في قياسات حسابك حتى الآن. يمكنك تسجيله من صفحة وزن الجسم والقياسات.';
        }

        // 💡 منع عرض الوزن القديم إذا كان المستخدم يكتب بيانات تخص حاسبة السعرات أو أرقاماً جديدة
        $isCalculatorInput = preg_match('/(?:طولي|عمرى|عمر|سنة|سنين|سم|كجم|كيلو|تنشيف|تضخيم|ثبات|ذكر|انثى)/u', $q) || preg_match('/\d{2,3}/u', $q);

        if (! $isCalculatorInput && preg_match('/(?:وزني|وزن[ي]? الحالي|اخر وزن|كم وزني|وزني كام)/u', $q)) {
            $measurement = $user->bodyMeasurements()->whereNotNull('weight')->orderByDesc('date')->orderByDesc('id')->first();

            return $measurement !== null
                ? 'آخر وزن مسجل لك: '.$measurement->weight.' كجم (بتاريخ '.$measurement->date->format('Y-m-d').').'
                : 'لا يوجد وزن مسجل في قياسات حسابك حتى الآن.';
        }

        if (preg_match('/(?:قياساتي|اخر قياس|قياسات جسمي|مقاساتي|مقاسات جسمي|قياسات جسمي كام)/u', $q)) {
            $m = $user->bodyMeasurements()->orderByDesc('date')->orderByDesc('id')->first();
            if (! $m) {
                return 'لم تسجل قياسات جسم في حسابك بعد.';
            }
            $labels = ['weight' => 'الوزن', 'chest' => 'الصدر', 'waist' => 'الخصر', 'biceps' => 'الذراع', 'thighs' => 'الفخذ', 'neck' => 'الرقبة'];
            $lines = ['آخر قياساتك ('.$m->date->format('Y-m-d').'):'];
            foreach ($labels as $field => $label) {
                if ($m->{$field} !== null) {
                    $lines[] = $label.': '.$m->{$field}.($field === 'weight' ? ' كجم' : ' سم');
                }
            }

            return implode("\n", $lines);
        }

        // Reading meal items and configured nutrition goals directly from the user's own records.
        // Rest-day targets are used only when the user explicitly asks about a rest day.
        if ($this->isNutritionQuestion($q)) {
            return $this->nutritionAnswer($user, $q);
        }

        // Workout queries: read only the authenticated member's workouts.
        if ($this->isWorkoutQuestion($q)) {
            return $this->workoutAnswer($user, $q);
        }

        return null;
    }

    private function isWorkoutQuestion(string $q): bool
    {
        return (bool) preg_match('/(?:جدول تمريني|جدول تماريني|تفاصيل جدولي|تفاصيل نظامي|تمارين برنامجي|جدول تماريني الحالي|ايه جدول تماريني|ايه جدول تمريني|ايه جدولي|جدولي الرياضي|برنامج تمريني|البرنامج التدريبي|برنامجي الرياضي|التمارين بتاعتي|جدولي الحالي|تمريني النهارده|تمرين النهارده|انا اتمرنت|اتمرنت ايه|اتمرنت اي|لعبت ايه|لعبت اي|لعبت النهارده|عملت ايه في الجيم|لعبت كام مجموعه|لعبت كام مجموعة|عملت كام مجموعه|عملت كام مجموعة|شلت كام|الاوزان اللي لعبتها|العدات اللي عملتها|تماريني النهارده|عندي تمرين|هتمرن ايه|اتمرن ايه النهارده|اخر تمرين|اخر مره تمرنت|اخر مرة تمرنت|اخر جلسه|اخر جلسة|امتي اخر مرة تمرنت|امتي اخر مره تمرنت|اخر مرة روحت الجيم|اخر مره روحت الجيم|امتي اخر مرة روحت الجيم|امتي اخر مره روحت الجيم|تمرنت امتي اخر مرة|تمرنت كام|عدد التمرينات|عدد الجلسات|تماريني الاسبوع|تقدمي في التمرين|بتقدم في التمرين|تطوري في التمرين|ادائي في التمرين|مستواي في التمرين|التزامي بالتمرين)/u', $q);
    }

    private function workoutAnswer(User $user, string $q): string
    {
        // Actual completed workout questions must read saved sessions, not the planned program.
        if (preg_match('/(?:انا اتمرنت|اتمرنت ايه|اتمرنت اي|لعبت ايه|لعبت اي|لعبت النهارده|عملت ايه في الجيم|لعبت كام مجموعه|لعبت كام مجموعة|عملت كام مجموعه|عملت كام مجموعة|شلت كام|الاوزان اللي لعبتها|العدات اللي عملتها)/u', $q)) {
            return $this->workoutHistoryAnswer($user, $q);
        }

        $sessionRequest = (bool) preg_match('/(?:اخر تمرين|اخر مره تمرنت|اخر مرة تمرنت|اخر جلسه|اخر جلسة|امتي اخر مرة تمرنت|امتي اخر مره تمرنت|اخر مرة روحت الجيم|اخر مره روحت الجيم|امتي اخر مرة روحت الجيم|امتي اخر مره روحت الجيم|تمرنت امتي اخر مرة|تمرنت كام|عدد التمرينات|عدد الجلسات|تماريني الاسبوع|تقدمي في التمرين|بتقدم في التمرين|تطوري في التمرين|ادائي في التمرين|مستواي في التمرين|التزامي بالتمرين)/u', $q);
        if ($sessionRequest) {
            return $this->workoutHistoryAnswer($user, $q);
        }

        $program = $user->workoutPrograms()->where('is_active', true)
            ->with(['days.dayExercises.exercise'])->latest('id')->first();
        if (! $program) {
            return 'ليس لديك برنامج تمارين نشط مسجل في Gym Master حاليًا.';
        }

        $todayQuestion = (bool) preg_match('/(?:النهارده|اليوم)/u', $q);
        $lines = ['### برنامج التمارين الحالي: '.$program->name];
        $days = $program->days->sortBy('id');
        if ($days->isEmpty()) {
            return implode("\n", [...$lines, 'لا توجد أيام تمارين مضافة لهذا البرنامج.']);
        }

        foreach ($days as $day) {
            $lines[] = '### '.$day->name;
            if ($day->dayExercises->isEmpty()) {
                $lines[] = '• لا توجد تمارين مضافة لهذا اليوم.';

                continue;
            }
            foreach ($day->dayExercises as $entry) {
                if (! $entry->exercise) {
                    continue;
                }
                $sets = ' — '.$entry->default_sets.' مجموعات';
                $reps = ' × '.$entry->default_reps.' تكرار';
                $lines[] = '• '.$entry->exercise->name.$sets.$reps;
            }
        }
        if ($todayQuestion) {
            $lines[] = 'ملاحظة: البرنامج يحتوي على أيام تمارين مسماة، لكن لا يوجد ربط مؤكد بينها وبين أيام الأسبوع؛ لذلك لا أستطيع تحديد تمرين اليوم تلقائيًا. اختَر اليوم المحدد في برنامجك.';
        }

        return implode("\n", $lines);
    }

    private function workoutHistoryAnswer(User $user, string $q): string
    {
        $sessions = $user->workoutSessions();
        $askedToday = (bool) preg_match('/(?:النهارده|اليوم|النهاردة)/u', $q);
        $showSetDetails = (bool) preg_match('/(?:كام مجموعه|كام مجموعة|شلت كام|الاوزان|العدات|تفاصيل|كل مجموعه|كل مجموعة)/u', $q);
        $askedWhat = (bool) preg_match('/(?:انا اتمرنت|اتمرنت ايه|اتمرنت اي|لعبت ايه|لعبت اي|لعبت النهارده|عملت ايه في الجيم|لعبت كام مجموعه|لعبت كام مجموعة|عملت كام مجموعه|عملت كام مجموعة|شلت كام|الاوزان اللي لعبتها|العدات اللي عملتها)/u', $q);
        if ($askedToday && $askedWhat) {
            $today = Carbon::today('Africa/Cairo')->toDateString();
            $todaySessions = (clone $sessions)->whereDate('date', $today)
                ->with(['workoutDay', 'logs.exercise'])->orderBy('id')->get();
            if ($todaySessions->isEmpty()) {
                return 'لا توجد جلسات تمرين مسجلة في حسابك بتاريخ '.$today.'. لو تمرنت ولم تحفظ المجموعات، سجّلها من صفحة تسجيل التمرين.';
            }
            $lines = ['### التمارين اللي سجلتها النهارده ('.$today.')'];
            foreach ($todaySessions as $session) {
                $lines[] = '### '.($session->workoutDay->name ?? 'جلسة تمرين');
                if ($session->logs->isEmpty()) {
                    $lines[] = '• مفيش مجموعات محفوظة للجلسة دي.';
                    continue;
                }
                foreach ($session->logs->groupBy('exercise_id') as $logs) {
                    $name = $logs->first()?->exercise->name ?? 'تمرين غير معروف';
                    $lines[] = '• '.$name.' — '.$logs->count().' مجموعات';
                    if ($showSetDetails) {
                        foreach ($logs as $log) {
                            $lines[] = '  - مجموعة '.$log->set_number.': '.$this->number($log->weight).' كجم × '.$log->reps.' عدة';
                        }
                    }
                }
            }
            return implode("\n", $lines);
        }
        $last = (clone $sessions)->with(['workoutDay', 'logs.exercise'])
            ->orderByDesc('date')->orderByDesc('id')->first();
        if (! $last) {
            return 'لا توجد جلسات تمارين مسجلة في حسابك حتى الآن.';
        }

        if (preg_match('/(?:تمرنت كام|عدد التمرينات|عدد الجلسات|تماريني الاسبوع|التزامي بالتمرين)/u', $q)) {
            $start = Carbon::now('Africa/Cairo')->startOfWeek()->toDateString();
            $end = Carbon::now('Africa/Cairo')->endOfWeek()->toDateString();
            $count = (clone $sessions)->whereBetween('date', [$start, $end])->count();

            return 'عدد جلسات التمرين المسجلة هذا الأسبوع ('.$start.' إلى '.$end.'): '.$count
                .' جلسة. هذا عدد الجلسات المسجلة، ولا يثبت وحده إكمال جميع التمارين.';
        }

        if (preg_match('/(?:تقدمي في التمرين|بتقدم في التمرين|تطوري في التمرين|ادائي في التمرين|مستواي في التمرين)/u', $q)) {
            $recent = (clone $sessions)->with('logs')->orderByDesc('date')->orderByDesc('id')->limit(10)->get();
            $withLogs = $recent->filter(fn ($session) => $session->logs->isNotEmpty());
            if ($withLogs->count() < 2) {
                return 'عندك '.$recent->count().' جلسة ضمن آخر السجلات، لكن لا توجد جلستان على الأقل بهما مجموعات وأوزان مسجلة للمقارنة. سجّل المجموعات والتكرارات والأوزان لنقيس التقدم.';
            }
            $lines = ['### ملخص آخر جلساتك', '• عدد الجلسات التي راجعتها: '.$recent->count(), '• الجلسات التي تحتوي على مجموعات مسجلة: '.$withLogs->count()];
            $lines[] = 'لا يمكن الحكم بدقة على التحسن من عدد الجلسات وحده؛ قارن وزن وتكرارات نفس التمرين عبر تواريخ مختلفة لتقييم الأداء.';

            return implode("\n", $lines);
        }

        $lines = ['### آخر جلسة تمرين', '• التاريخ: '.Carbon::parse($last->date)->format('Y-m-d'),
            '• اليوم: '.(optional($last->workoutDay)->name ?? 'غير محدد')];
        if ($last->logs->isEmpty()) {
            $lines[] = 'لا توجد مجموعات مسجلة لهذه الجلسة.';
        } else {
            foreach ($last->logs->groupBy('exercise_id') as $logs) {
                $exerciseName = optional($logs->first()->exercise)->name ?? 'تمرين غير معروف';
                $lines[] = '• '.$exerciseName.': '.$logs->count().' مجموعات مسجلة';
                if ($showSetDetails) {
                    foreach ($logs as $log) {
                        $lines[] = '  - مجموعة '.$log->set_number.': '.$this->number($log->weight).' كجم × '.$log->reps.' عدة';
                    }
                }
            }
        }

        return implode("\n", $lines);
    }

    private function isNutritionQuestion(string $q): bool
    {
        // Avoid capturing calculator questions such as "احسب سعراتي" or "كم احتاج سعرات".
        return (bool) preg_match('/(?:اكلت|كلت|اكلت ايه|كلت ايه|كلت اي|فطرت ايه|اتغديت ايه|اتعشيت ايه|اكلي|الاكل اللي|الاكل الي|وجباتي|وجبات اليوم|وجبات النهارده|فطار|فطاري|غدايا|عشايا|بروتيني|كارب(?:ي)?|دهوني|ماكروز اليوم|سعرات النهارده|سعرات اليوم|سعراتي المستهدفه|سعراتي اليوميه|هدف السعرات|هدفي من السعرات|هدف الماكروز|الماكروز المستهدفه|فاضل(?:ي|لي)?|باقي(?:لي)?|متبقي|حققت هدفي|الالتزام بالتغذيه|نظامي الغذائي اليوم|نظام الاكل|نظام اكلي|جدول اكلي|جدول الاكل|اكلي النهارده|اكلت النهارده|كلت النهارده)/u', $q);
    }

    private function nutritionAnswer(User $user, string $q): string
    {
        $day = Carbon::today('Africa/Cairo')->toDateString();
        $isRest = (bool) preg_match('/(?:يوم راحه|الراحه|rest day)/u', $q);
        $prefix = $isRest ? 'rest_target_' : 'target_';
        $targets = [
            'calories' => $user->{$prefix.'calories'},
            'protein' => $user->{$prefix.'protein'},
            'carbs' => $user->{$prefix.'carbs'},
            'fats' => $user->{$prefix.'fats'},
        ];
        $labels = ['calories' => 'السعرات', 'protein' => 'البروتين', 'carbs' => 'الكارب', 'fats' => 'الدهون'];
        $units = ['calories' => 'سعرة', 'protein' => 'جم', 'carbs' => 'جم', 'fats' => 'جم'];
        $dayLabel = $isRest ? 'يوم الراحة' : 'يوم الجيم';

        $meals = $user->dailyMeals()->whereDate('date', $day)
            ->with('items.food')->get();
        $lines = [];
        $consumed = array_fill_keys(array_keys($labels), 0.0);
        $hasItems = false;
        foreach ($meals as $meal) {
            $itemLines = [];
            foreach ($meal->items as $item) {
                if (! $item->food) {
                    continue;
                }
                $hasItems = true;
                foreach ($consumed as $key => $_) {
                    $consumed[$key] += (float) $item->{$key};
                }
                $itemLines[] = '• '.$item->food->name.' — '.$this->number($item->grams)
                    .' جم ('.$this->number($item->calories).' سعرة)';
            }
            if ($itemLines) {
                $lines[] = '### '.$meal->meal_type;
                array_push($lines, ...$itemLines);
            }
        }

        $goalOnly = (bool) preg_match('/(?:هدف السعرات|سعراتي المستهدفه|سعراتي اليوميه|هدفي من السعرات|هدف الماكروز|الماكروز المستهدفه)/u', $q);
        $foodDetails = (bool) preg_match('/(?:اكلت ايه|اكلت اي|كلت ايه|كلت اي|فطرت ايه|اتغديت ايه|اتعشيت ايه|الاكل اللي|الاكل الي|وجباتي|وجبات اليوم|وجبات النهارده|فطاري|فطار|غدايا|عشايا|اكلي)/u', $q);
        $remaining = (bool) preg_match('/(?:فاضل|باقي|متبقي|حققت|الالتزام)/u', $q);

        $result = ['### تغذيتك اليوم ('.$day.')'];
        if (! $hasItems) {
            $result[] = 'لا توجد أطعمة مسجلة اليوم حتى الآن. الصفر يعني عدم وجود تسجيل، وليس بالضرورة أنك لم تأكل.';
        } elseif ($foodDetails) {
            array_push($result, ...$lines);
        }

        if (! $goalOnly || $remaining) {
            $result[] = '### الإجمالي المسجل';
            foreach ($labels as $key => $label) {
                $result[] = '• '.$label.': '.$this->number($consumed[$key]).' '.$units[$key];
            }
        }

        $result[] = '### أهداف '.$dayLabel.' والمتبقي';
        foreach ($labels as $key => $label) {
            if ($targets[$key] === null) {
                $result[] = '• '.$label.': الهدف غير مضبوط في حسابك.';

                continue;
            }
            $target = (float) $targets[$key];
            $diff = $target - $consumed[$key];
            $result[] = '• '.$label.': الهدف '.$this->number($target).' '.$units[$key]
                .' — '.($diff >= 0 ? 'المتبقي ' : 'تجاوز الهدف بـ ')
                .$this->number(abs($diff)).' '.$units[$key];
        }
        $result[] = 'ملاحظة: حسبت هدف '.$dayLabel.'. لو تقصد '.($isRest ? 'يوم الجيم' : 'يوم الراحة')
            .' اذكره في سؤالك. البيانات المعروضة خاصة بحسابك وبما سجلته فقط.';

        return implode("\n", $result);
    }

    /** Overview is calculated on demand and never uses another member's records. */
    private function accountOverview(User $user): string
    {
        $program = $user->workoutPrograms()->where('is_active', true)->latest('id')->first();
        $measurement = $user->bodyMeasurements()->orderByDesc('date')->orderByDesc('id')->first();
        $sessions = $user->workoutSessions()->count();
        $todayMeals = $user->dailyMeals()->whereDate('date', Carbon::today('Africa/Cairo')->toDateString())->count();

        $lines = [
            '### ملخص حسابك في Gym Master',
            '• الاسم: '.$user->name,
            '• البرنامج النشط: '.($program !== null ? $program->name : 'لا يوجد برنامج مفعّل'),
            '• عدد جلسات التدريب المسجلة: '.$sessions,
            '• عدد الوجبات المسجلة اليوم: '.$todayMeals,
        ];
        if ($measurement !== null) {
            $lines[] = '• آخر وزن مسجل: '.$this->number($measurement->weight).' كجم';
            if ($measurement->height !== null) {
                $lines[] = '• آخر طول مسجل: '.$this->number($measurement->height).' سم';
            }
        } else {
            $lines[] = '• لا توجد قياسات جسم مسجلة.';
        }
        $lines[] = 'اسألني عن تفاصيل برنامجك، تمرينك المسجل، مجموعاتك وأوزانك، أو أكلك وسعراتك اليوم.';

        return implode("\n", $lines);
    }

    private function number($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 1, '.', ''), '0'), '.');
    }
}
