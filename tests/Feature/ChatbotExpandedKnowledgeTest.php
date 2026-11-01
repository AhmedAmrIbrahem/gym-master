<?php

namespace Tests\Feature;

use App\Models\BodyMeasurement;
use App\Models\ChatbotFaq;
use App\Models\User;
use App\Services\CalorieCalculatorService;
use App\Services\ChatbotKnowledgeService;
use App\Services\ChatbotMemberDataService;
use Database\Seeders\ChatbotFaqTrainingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotExpandedKnowledgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_training_pack_has_many_questions_and_makes_reruns_safe(): void
    {
        $this->seed(ChatbotFaqTrainingSeeder::class);
        $this->assertGreaterThanOrEqual(80, ChatbotFaq::count());

        $faq = ChatbotFaq::where('question', 'يعني إيه PPL؟')->firstOrFail();
        $faq->update(['answer' => 'إجابة المدير المخصصة', 'is_active' => false]);
        $count = ChatbotFaq::count();

        $this->seed(ChatbotFaqTrainingSeeder::class);
        $this->assertSame($count, ChatbotFaq::count());
        $this->assertDatabaseHas('chatbot_faqs', [
            'id' => $faq->id,
            'answer' => 'إجابة المدير المخصصة',
            'is_active' => false,
        ]);
    }

    public function test_local_answers_cover_different_phrasings_without_fuzzy_mistakes(): void
    {
        $this->seed(ChatbotFaqTrainingSeeder::class);
        $knowledge = app(ChatbotKnowledgeService::class);

        $this->assertStringContainsString('Push', $knowledge->findAnswer('PPL يعني ايه'));
        $this->assertStringContainsString('Push', $knowledge->findAnswer('يا كوتش، PPL يعني ايه؟'));
        $this->assertStringContainsString('4', $knowledge->findAnswer('البروتين بكام كالوري'));
        $this->assertStringContainsString('100 جرام', $knowledge->findAnswer('ليه السعرات لكل 100 جرام'));
        $this->assertStringContainsString('النوم', $knowledge->findAnswer('السهر بياثر على التضخيم'));
        $this->assertNull($knowledge->findAnswer('إيه أفضل جهاز تمرين عند جاري؟'));
        $user = User::factory()->create();
        $this->assertNull(app(ChatbotMemberDataService::class)->answer($user, 'هل الكارب بيمنع التخسيس؟'));
        $this->assertNull(app(ChatbotMemberDataService::class)->answer($user, 'أضيف أكلة للفطار إزاي؟'));
    }

    public function test_calorie_goal_phrasings_start_local_calculator_instead_of_generic_advice(): void
    {
        $user = User::factory()->create();
        $calculator = app(CalorieCalculatorService::class);

        foreach ([
            'عايز احسب الكالوري بتاعي عشان اخس',
            'كام سعرة محتاج اكل عشان اخس',
            'محتاج اعرف احتياجي من السعرات',
            'معدل الحرق عندي كام',
        ] as $question) {
            $this->assertTrue($calculator->isExplicitCalculationRequest($question), $question);
            $this->assertStringContainsString('🧮 حاسبة السعرات', $calculator->answer($user, $question), $question);
        }

        $this->assertFalse($calculator->isExplicitCalculationRequest('فاضلي كام سعره النهارده'));
        $this->assertFalse($calculator->isExplicitCalculationRequest('اكلت كام سعره اليوم'));
    }

    public function test_personal_weight_trend_is_read_from_only_members_measurements(): void
    {
        $member = User::factory()->create();
        $stranger = User::factory()->create();
        BodyMeasurement::create(['user_id' => $member->id, 'date' => '2026-10-20', 'weight' => 90]);
        BodyMeasurement::create(['user_id' => $member->id, 'date' => '2026-10-22', 'weight' => 88]);
        BodyMeasurement::create(['user_id' => $stranger->id, 'date' => '2026-10-23', 'weight' => 130]);

        $service = app(ChatbotMemberDataService::class);
        $answer = $service->answer($member, 'وزني زاد ولا قل؟');
        $this->assertStringContainsString('وزنك قل 2 كجم', $answer);
        $this->assertStringNotContainsString('130', $answer);

        $strangerAnswer = $service->answer($stranger, 'فرق وزني');
        $this->assertStringContainsString('محتاج قياسين', $strangerAnswer);
    }

    public function test_colloquial_macro_targets_are_answered_using_member_data(): void
    {
        $member = User::factory()->create(['target_protein' => 150]);
        $answer = app(ChatbotMemberDataService::class)->answer($member, 'هدف البروتين كام النهارده');
        $this->assertStringContainsString('150', $answer);
        $this->assertStringContainsString('لا توجد أطعمة مسجلة', $answer);
    }
}
