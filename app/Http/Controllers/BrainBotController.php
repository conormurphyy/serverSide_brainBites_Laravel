<?php

namespace App\Http\Controllers;

use App\Models\BrainBotMessage;
use App\Services\BrainBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrainBotController extends Controller
{
    public function chat(Request $request, BrainBotService $brainBot): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $result = $brainBot->answer($data['message']);

        if ($request->user()) {
            BrainBotMessage::create([
                'user_id' => $request->user()->id,
                'question' => $data['message'],
                'answer' => (string) ($result['answer'] ?? ''),
                'sources' => $result['sources'] ?? [],
            ]);
        }

        return response()->json($result);
    }

    public function history(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json([
                'history' => [],
            ]);
        }

        $history = BrainBotMessage::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->take(20)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (BrainBotMessage $row): array => [
                'question' => $row->question,
                'answer' => $row->answer,
                'sources' => $row->sources ?? [],
                'created_at' => optional($row->created_at)->toIso8601String(),
            ]);

        return response()->json([
            'history' => $history,
        ]);
    }
}
