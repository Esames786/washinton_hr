{{-- The zone the shift hours are read in: check-in windows, late marks, breaks and the portal
     clock all use it (Employee::tz()). Hello agents choose it when they apply; Pakistan-based
     staff stay on Asia/Karachi, the default. Any other stored zone is kept as an option. --}}
@php
    $__zones = [
        'Asia/Karachi'        => 'Pakistan (Asia/Karachi)',
        'America/New_York'    => 'US Eastern (America/New_York)',
        'America/Chicago'     => 'US Central (America/Chicago)',
        'America/Denver'      => 'US Mountain (America/Denver)',
        'America/Phoenix'     => 'US Arizona (America/Phoenix)',
        'America/Los_Angeles' => 'US Pacific (America/Los_Angeles)',
        'America/Anchorage'   => 'US Alaska (America/Anchorage)',
        'Pacific/Honolulu'    => 'US Hawaii (Pacific/Honolulu)',
    ];
    $__tzSelected = old('timezone', $current ?: 'Asia/Karachi');
    if (! isset($__zones[$__tzSelected])) {
        $__zones[$__tzSelected] = $__tzSelected;
    }
@endphp
<div class="col-6">
    <label class="form-label">Time zone</label>
    <div class="position-relative">
        <select name="timezone" id="timezone" class="form-control">
            @foreach ($__zones as $__tz => $__label)
                <option value="{{ $__tz }}" {{ $__tzSelected === $__tz ? 'selected' : '' }}>{{ $__label }}</option>
            @endforeach
        </select>
    </div>
    <small class="text-muted">Shift hours, check-in and breaks are timed in this zone.</small>
</div>
