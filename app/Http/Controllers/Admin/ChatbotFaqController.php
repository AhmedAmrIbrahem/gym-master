<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotFaq;
use Illuminate\Http\Request;

class ChatbotFaqController extends Controller
{
    public function index()
    {
        return view('admin.chatbot.index', ['faqs' => ChatbotFaq::latest()->paginate(15)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:10000',
            'category' => 'nullable|string|max:100',
            'aliases' => 'nullable|string|max:3000',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        ChatbotFaq::create($data);

        return back()->with('success', 'تمت إضافة السؤال.');
    }

    public function update(Request $request, ChatbotFaq $faq)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:10000',
            'category' => 'nullable|string|max:100',
            'aliases' => 'nullable|string|max:3000',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $faq->update($data);

        return back()->with('success', 'تم تعديل السؤال.');
    }

    public function destroy(ChatbotFaq $faq)
    {
        $faq->delete();

        return back()->with('success', 'تم حذف السؤال.');
    }
}
