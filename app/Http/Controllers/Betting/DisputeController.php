<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\DisputeAttachment;
use App\Betting\Models\Market;
use App\Betting\Services\SettlementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DisputeController extends Controller
{
    public function store(Request $request, Market $market, SettlementService $settlementService)
    {
        $this->authorize('dispute', $market);

        $validated = $request->validate([
            'reason_category' => 'required|string|max:64',
            'explanation' => 'nullable|string|max:2000',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $dispute = $settlementService->openDispute(
                $market,
                $request->user(),
                $validated['reason_category'],
                $validated['explanation'] ?? null
            );

            foreach ($request->file('attachments', []) as $file) {
                $path = $file->store('betting/disputes/'.$dispute->id, 'local');
                DisputeAttachment::create([
                    'betting_dispute_id' => $dispute->id,
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize() ?: 0,
                ]);
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('betting.dispute_submitted'));
    }
}
