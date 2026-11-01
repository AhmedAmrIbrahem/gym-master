<?php

namespace App\Services;

use App\Models\User;

/** Estimates for adults only; no user measurements or goals are modified. */
class CalorieCalculatorService
{
    private ?array $lastResult = null;

    public function resultData(): ?array
    {
        return $this->lastResult;
    }

    public function answer(User $user, string $message, array $history = []): ?string
    {
        $this->lastResult = null;
        $text = $this->normalize($message);
        $requested = $this->isExplicitCalculationRequest($message);
        // استكمال الحاسبة فقط عندما تكون آخر إجابة للبوت تطلب بيانات الحاسبة.
        // لا نعترض سؤالاً مختلفاً لمجرد وجود طلب حساب قديم في التاريخ.
        $lastBot = null;
        foreach (array_reverse($history) as $entry) {
            if (is_array($entry) && ($entry['sender'] ?? '') === 'bot') {
                $lastBot = (string) ($entry['text'] ?? '');
                break;
            }
        }
        $inProgress = $lastBot !== null
            && str_contains($lastBot, '🧮 حاسبة السعرات')
            && (str_contains($lastBot, 'الناقص بس:') || str_contains($lastBot, 'ابعت البيانات'));

        if (! $requested && ! $inProgress) {
            return null;
        }

        // اجمع مدخلات المشترك عبر رسائل المتابعة بعد بداية آخر حوار حاسبة.
        // الأحدث يعلو الأقدم إذا صحح المستخدم قيمة.
        $fields = [];
        if ($inProgress) {
            $startIndex = null;
            foreach ($history as $index => $entry) {
                if (($entry['sender'] ?? '') === 'bot'
                    && str_contains((string) ($entry['text'] ?? ''), '🧮 حاسبة السعرات')
                    && (str_contains((string) ($entry['text'] ?? ''), 'الناقص بس:') || str_contains((string) ($entry['text'] ?? ''), 'ابعت البيانات'))) {
                    // خذ أول طلب في سلسلة المتابعة الحالية، بحد أقصى آخر 20 رسالة.
                    if ($startIndex === null) {
                        $startIndex = $index;
                    }
                }
            }
            // Include the original user request immediately before the first calculator prompt.
            $from = $startIndex === null ? 0 : max(0, $startIndex - 1);
            foreach (array_slice($history, $from) as $entry) {
                if (is_array($entry) && ($entry['sender'] ?? '') === 'user') {
                    $prior = $this->normalize((string) ($entry['text'] ?? ''));
                    $fields = array_merge($fields, $this->extract($prior));
                    $fields = array_merge($fields, $this->extractCompact($prior));
                }
            }
        }
        $fields = array_merge($fields, $this->extract($text));
        if ($inProgress) {
            $fields = array_merge($fields, $this->extractCompact($text));
        }

        // جلب الوزن تلقائياً من أحدث قياس مسجل إذا لم يتم إدخاله
        if (! isset($fields['weight'])) {
            $m = $user->bodyMeasurements()->whereNotNull('weight')->orderByDesc('date')->orderByDesc('id')->first();
            if ($m && is_numeric($m->getRawOriginal('weight'))) {
                $fields['weight'] = (float) $m->weight;
            }
        }

        // جلب الطول تلقائياً من أحدث قياس مسجل إذا لم يتم إدخاله
        if (! isset($fields['height'])) {
            $m = $user->bodyMeasurements()->whereNotNull('height')->orderByDesc('date')->orderByDesc('id')->first();
            if ($m && is_numeric($m->height)) {
                $fields['height'] = (float) $m->height;
            }
        }

        $missing = [];
        foreach (['weight' => 'الوزن بالكيلو', 'height' => 'الطول بالسنتيمتر', 'age' => 'العمر', 'sex' => 'النوع (ذكر/أنثى)', 'activity' => 'النشاط (خامل/خفيف/متوسط/عالٍ)', 'goal' => 'الهدف (ثبات/تنشيف/زيادة)'] as $key => $label) {
            if (! isset($fields[$key])) {
                $missing[] = $label;
            }
        }
        if ($missing) {
            $labels = [
                'weight' => 'الوزن', 'height' => 'الطول', 'age' => 'العمر',
                'sex' => 'النوع', 'activity' => 'النشاط', 'goal' => 'الهدف',
            ];
            $descriptions = [
                'weight' => fn ($v) => $v.' كجم',
                'height' => fn ($v) => $v.' سم',
                'age' => fn ($v) => $v.' سنة',
                'sex' => fn ($v) => $v === 'male' ? 'ذكر' : 'أنثى',
                'activity' => fn ($v) => ['sedentary' => 'خامل', 'light' => 'خفيف', 'moderate' => 'متوسط', 'high' => 'عالٍ'][$v],
                'goal' => fn ($v) => ['cut' => 'خسارة الوزن', 'bulk' => 'زيادة', 'maintain' => 'ثبات'][$v],
            ];
            $known = [];
            foreach ($labels as $key => $label) {
                if (isset($fields[$key])) {
                    $known[] = $label.': '.$descriptions[$key]($fields[$key]);
                }
            }

            return "🧮 حاسبة السعرات والماكروز\n"
                .($known ? 'فهمت منك: '.implode('، ', $known)."\n" : '')
                .'الناقص بس: '.implode('، ', $missing)
                ."\nابعت البيانات الناقصة فقط، ومش محتاج تكرر اللي قلته.\n"
                .'الحساب تقديري للبالغين ومش بديل عن متابعة مختص.';
        }

        $weight = $fields['weight'];
        $height = $fields['height'];
        $age = $fields['age'];
        if ($weight < 30 || $weight > 350 || $height < 120 || $height > 230 || $age < 18 || $age > 100) {
            return '🧮 حاسبة السعرات: راجع البيانات؛ الحاسبة مخصصة للبالغين (18–100 سنة) بوزن 30–350 كجم وطول 120–230 سم. لو خارج النطاق ده استشر مختص.';
        }
        $bmr = 10 * $weight + 6.25 * $height - 5 * $age + ($fields['sex'] === 'male' ? 5 : -161);
        $factors = ['sedentary' => 1.2, 'light' => 1.375, 'moderate' => 1.55, 'high' => 1.725];
        $tdee = round($bmr * $factors[$fields['activity']]);
        $target = $fields['goal'] === 'cut' ? round($tdee * 0.85) : ($fields['goal'] === 'bulk' ? round($tdee * 1.10) : $tdee);
        // Choose protein and fat in grams, then derive carbohydrates from remaining calories.
        $protein = round(1.6 * $weight);
        $fat = round(0.8 * $weight);
        $carb = round(($target - 4 * $protein - 9 * $fat) / 4);
        if ($carb < 0) {
            return '🧮 حاسبة السعرات: المدخلات تؤدي إلى توزيع ماكروز غير مناسب. راجع بياناتك مع مختص تغذية.';
        }
        $goalText = ['cut' => 'تنشيف (عجز تقريبي 15%)', 'bulk' => 'زيادة (فائض تقريبي 10%)', 'maintain' => 'ثبات'][$fields['goal']];
        $actualTarget = 4 * $protein + 9 * $fat + 4 * $carb;
        $this->lastResult = [
            'bmr' => (int) round($bmr),
            'tdee' => (int) $tdee,
            'calories' => (int) $actualTarget,
            'goal' => $fields['goal'],
            'goal_label' => $goalText,
            'protein' => (int) $protein,
            'carbs' => (int) $carb,
            'fat' => (int) $fat,
        ];

        return "🧮 حاسبة السعرات والماكروز\n"
            .'BMR: '.round($bmr)." سعرة/يوم\n"
            ."TDEE (الثبات): {$tdee} سعرة/يوم\n"
            ."الهدف: {$goalText}\n"
            ."السعرات المقترحة: {$actualTarget} سعرة/يوم\n"
            ."بروتين: {$protein} جم | كارب: {$carb} جم | دهون: {$fat} جم\n"
            .'دي تقديرات مبدئية وليست وصفة طبية؛ راقب التغير الفعلي وعدّل مع مختص عند الحاجة.';
    }

    /** هل المطلوب حساب احتياج جديد، لا الاستعلام عن بيانات التغذية المسجلة؟ */
    public function isExplicitCalculationRequest(string $message): bool
    {
        $text = $this->normalize($message);

        // "أكلت كام سعرة" و"فاضلي كام سعرة" استعلامان عن سجل التغذية.
        if (preg_match('/(?:اكلت|اكل|فاضل|متبقي|باقي|استهلكت|سجلت|هدفي اليوم|هدف السعرات).{0,45}(?:سعر|كالوري|بروتين|كارب|دهون)/u', $text)) {
            return false;
        }

        return (bool) preg_match(
            '/(?:احسب|احسبلي|احسبلي|حساب|حاسبه|احتياج|محتاج اعرف|عايز احسب).{0,100}(?:سعر|كالوري|ماكروز|bmr|tdee)'
            .'|(?:سعراتي|السعرات|سعرات|كالوري|ماكروز).{0,60}(?:احسب|احسبلي|احتياجي)'
            .'|(?:^|\s)(?:bmr|tdee)(?:\s|$)'
            .'|(?:عايز|عاوزه|محتاج|نفسي|ازاي|ممكن|عاوز).{0,50}(?:اعرف|احسب|حساب|اطلع).{0,60}(?:كالوري|سعرات|السعرات|احتياجي|اكلي).{0,40}(?:اخس|اتخن|انشف|اضخم|تخسيس|تنشيف|تضخيم)?'
            .'|(?:كام|كم|قد ايه).{0,40}(?:سعره|سعرة|سعرات|كالوري).{0,35}(?:احتاج|محتاج|لازم|اكل|يومي|للتنشيف|للتضخيم|عشان اخس)'
            .'|(?:احتياجي|معدل الحرق|معدل حرقي).{0,45}(?:كام|كم|قد ايه|سعره|سعرات|كالوري)/u',
            $text
        );
    }

    private function normalize(string $text): string
    {
        $text = strtr($text, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']);
        $text = mb_strtolower($text, 'UTF-8');

        return str_replace(['أ', 'إ', 'آ', 'ى', 'ـ'], ['ا', 'ا', 'ا', 'ي', ''], $text);
    }

    private function extractCompact(string $text): array
    {
        // صيغة مختصرة: 88 174 23 ذكر متوسط تنشيف
        if (! preg_match('/^\s*(\d+(?:\.\d+)?)\s+(\d+(?:\.\d+)?)\s+(\d{1,3})\b/u', $text, $m)) {
            return [];
        }

        return [
            'weight' => (float) $m[1],
            'height' => (float) $m[2],
            'age' => (int) $m[3],
        ];
    }

    private function extract(string $text): array
    {
        $v = [];
        foreach (['weight' => '/(?:الوزن|وزن[ي]?|weight)\s*[:=]?\s*(\d+(?:\.\d+)?)/u', 'height' => '/(?:الطول|طول[ي]?|height)\s*[:=]?\s*(\d+(?:\.\d+)?)/u', 'age' => '/(?:العمر|عمر[ي]?|السن|سن[ي]?|age)\s*[:=]?\s*(\d+)/u'] as $name => $regex) {
            if (preg_match($regex, $text, $m)) {
                $v[$name] = (float) $m[1];
            }
        }

        // 💡 إضافة هامة جداً: التقاط العمر لو المستخدم كتب رقم لوحده مثل "23" أو "23 سنة"
        if (! isset($v['age']) && preg_match('/^\D*(\d{1,3})\s*(?:سنة|سنين|عام)?\D*$/u', trim($text), $m)) {
            $val = (int) $m[1];
            if ($val >= 18 && $val <= 100) {
                $v['age'] = $val;
            }
        }

        if (preg_match('/(?:انثي|انثى|female|سيده|بنت)/u', $text)) {
            $v['sex'] = 'female';
        } elseif (preg_match('/(?:ذكر|male|رجل|شاب)/u', $text)) {
            $v['sex'] = 'male';
        }
        if (preg_match('/(?:خامل|قليل الحركه|sedentary)/u', $text)) {
            $v['activity'] = 'sedentary';
        } elseif (preg_match('/(?:خفيف|light)/u', $text)) {
            $v['activity'] = 'light';
        } elseif (preg_match('/(?:متوسط|moderate)/u', $text)) {
            $v['activity'] = 'moderate';
        } elseif (preg_match('/(?:عالي|عال|شديد|نشيط جدا|high)/u', $text)) {
            $v['activity'] = 'high';
        }
        if (preg_match('/(?:تنشيف|تخسيس|خساره|نزول|اخس|خس|cut)/u', $text)) {
            $v['goal'] = 'cut';
        } elseif (preg_match('/(?:زياده|تضخيم|bulk)/u', $text)) {
            $v['goal'] = 'bulk';
        } elseif (preg_match('/(?:ثبات|ثابت|maintain)/u', $text)) {
            $v['goal'] = 'maintain';
        }

        return $v;
    }
}
