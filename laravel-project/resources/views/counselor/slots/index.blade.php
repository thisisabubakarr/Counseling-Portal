@extends('layouts.app')

@section('title', 'Time Slots')

@section('content')
    <div class="page-header">
        <h2>Manage Time Slots</h2>
    </div>

    <form method="GET" style="margin-bottom:1rem;max-width:16rem">
        <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
    </form>

    <div class="slot-container">
        @foreach ($allSlots as $slot)
            @php $blockedSlot = $blocked->firstWhere('slot', $slot['label']); @endphp
            <div class="slot-pill {{ $blockedSlot ? 'slot-blocked' : 'slot-open' }}">
                <div class="slot-pill-time">{{ $slot['label'] }}</div>
                <div class="slot-pill-state">{{ $blockedSlot ? 'Blocked' : 'Open' }}</div>
                @if ($blockedSlot)
                    <form method="POST" action="{{ route('counselor.slots.destroy', $blockedSlot) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-secondary btn-sm">Unblock</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('counselor.slots.store') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="slot" value="{{ $slot['label'] }}">
                        <input type="text" name="reason" placeholder="Reason (optional)" class="form-control" style="margin-bottom:0.4rem">
                        <button type="submit" class="btn btn-warning btn-sm">Block</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
@endsection
