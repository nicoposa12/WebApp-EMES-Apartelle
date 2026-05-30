<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotResponse;
use Illuminate\Http\Request;

class ChatbotResponseController extends Controller
{
    public function index()
    {
        return response()->json(ChatbotResponse::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'trigger' => 'required|string',
            'response' => 'required|string',
            'follow_up_question' => 'nullable|string',
            'suggested_triggers' => 'nullable|string',
            'match_type' => 'required|in:exact,contains',
            'is_active' => 'boolean'
        ]);

        $rule = ChatbotResponse::create($request->all());

        return response()->json($rule, 201);
    }

    public function show($id)
    {
        return response()->json(ChatbotResponse::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $rule = ChatbotResponse::findOrFail($id);
        
        $request->validate([
            'trigger' => 'required|string',
            'response' => 'required|string',
            'follow_up_question' => 'nullable|string',
            'suggested_triggers' => 'nullable|string',
            'match_type' => 'required|in:exact,contains',
            'is_active' => 'boolean'
        ]);

        $rule->update($request->all());

        return response()->json($rule);
    }

    public function destroy($id)
    {
        $rule = ChatbotResponse::findOrFail($id);
        $rule->delete();
        return response()->json(null, 204);
    }

    public function public()
    {
        return response()->json(ChatbotResponse::where('is_active', true)->get());
    }
}
