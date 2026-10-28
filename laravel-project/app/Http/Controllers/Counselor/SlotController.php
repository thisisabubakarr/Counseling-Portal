<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\BlockedSlot;
use App\Support\CodeGenerator;
use App\Support\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SlotController extends Controller
{
    public function index(Request $request)
    {
        $counselor = Auth::guard('counselor')->user();

        $date = $request->query('date', now()->toDateString());
        $blocked = BlockedSlot::where('counselor_id', $counselor->id)
            ->whereDate('date', $date)
            ->get();

        $allSlots = SlotService::generateTimeSlots();

        return view('counselor.slots.index', [
            'date' => $date,
            'blocked' => $blocked,
            'allSlots' => $allSlots,
        ]);
    }

    public function store(Request $request)
    {
        $counselor = Auth::guard('counselor')->user();

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'slot' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
        ]);

        $exists = BlockedSlot::where('counselor_id', $counselor->id)
            ->whereDate('date', $data['date'])
            ->where('slot', $data['slot'])
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages(['slot' => 'Slot already blocked.']);
        }

        BlockedSlot::create([
            'blocked_code' => CodeGenerator::make('BLK'),
            'counselor_id' => $counselor->id,
            'date' => $data['date'],
            'slot' => $data['slot'],
            'reason' => $data['reason'] ?? '',
        ]);

        return back()->with('status', 'Slot blocked.');
    }

    public function destroy(BlockedSlot $blockedSlot)
    {
        $counselor = Auth::guard('counselor')->user();
        abort_unless($blockedSlot->counselor_id === $counselor->id, 403);

        $blockedSlot->delete();

        return back()->with('status', 'Slot unblocked.');
    }
}
