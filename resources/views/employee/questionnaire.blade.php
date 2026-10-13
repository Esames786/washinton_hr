@extends('layout.master')
@section('pageName', 'Questionnaire')

@section('content')

    @include('partials.alerts')

    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-1">Onboarding Questionnaire</h5>
                    <p class="mb-0 text-muted" style="font-size:13.5px;">
                        Please answer the questions below. Once you submit, your portal will pause
                        while we arrange your interview and a short practical assessment — that is
                        expected, and we will email you.
                    </p>
                </div>

                <div class="card-body p-4">

                    @if($questions->isEmpty())
                        <div class="alert alert-warning mb-0">
                            There are no questions set up yet. Please contact HR.
                        </div>
                    @else
                        @if($employee->questionnaire_submitted_at)
                            <div class="alert alert-success" style="font-size:13.5px;">
                                You submitted this questionnaire on
                                {{ $employee->questionnaire_submitted_at->format('d M Y, H:i') }}.
                                Your answers are shown below.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('employee.questionnaire.store') }}">
                            @csrf

                            @foreach($questions as $q)
                                @php
                                    $name = 'q_' . $q->id;
                                    $val  = old($name, $answers[$q->id] ?? '');
                                @endphp

                                <div class="mb-4">
                                    <label class="form-label fw-semibold" style="font-size:14.5px;">
                                        {{ $loop->iteration }}. {{ $q->question }}
                                        @if($q->is_required)<span class="text-danger">*</span>@endif
                                    </label>

                                    @if($q->help_text)
                                        <div class="text-muted mb-2" style="font-size:12.5px;">{{ $q->help_text }}</div>
                                    @endif

                                    @switch($q->field_type)
                                        @case('textarea')
                                            <textarea name="{{ $name }}" rows="4"
                                                      class="form-control @error($name) is-invalid @enderror">{{ $val }}</textarea>
                                            @break

                                        @case('number')
                                            <input type="number" step="any" name="{{ $name }}" value="{{ $val }}"
                                                   class="form-control @error($name) is-invalid @enderror">
                                            @break

                                        @case('date')
                                            <input type="date" name="{{ $name }}" value="{{ $val }}"
                                                   class="form-control @error($name) is-invalid @enderror">
                                            @break

                                        @case('select')
                                            <select name="{{ $name }}" class="form-select @error($name) is-invalid @enderror">
                                                <option value="">-- Select --</option>
                                                @foreach($q->choices() as $choice)
                                                    <option value="{{ $choice }}" @selected($val === $choice)>{{ $choice }}</option>
                                                @endforeach
                                            </select>
                                            @break

                                        @case('radio')
                                            @foreach($q->choices() as $choice)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="{{ $name }}"
                                                           id="{{ $name }}_{{ $loop->index }}"
                                                           value="{{ $choice }}" @checked($val === $choice)>
                                                    <label class="form-check-label" for="{{ $name }}_{{ $loop->index }}">
                                                        {{ $choice }}
                                                    </label>
                                                </div>
                                            @endforeach
                                            @break

                                        @case('yes_no')
                                            @foreach(['Yes', 'No'] as $choice)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="{{ $name }}"
                                                           id="{{ $name }}_{{ $choice }}"
                                                           value="{{ $choice }}" @checked($val === $choice)>
                                                    <label class="form-check-label" for="{{ $name }}_{{ $choice }}">{{ $choice }}</label>
                                                </div>
                                            @endforeach
                                            @break

                                        @default
                                            <input type="text" name="{{ $name }}" value="{{ $val }}"
                                                   class="form-control @error($name) is-invalid @enderror">
                                    @endswitch

                                    @error($name)
                                        <div class="text-danger mt-1" style="font-size:12.5px;">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach

                            <div class="border-top pt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <small class="text-muted">
                                    Your portal will pause after you submit. We will be in touch by email.
                                </small>
                                <button type="submit" class="btn btn-primary px-4"
                                        onclick="return confirm('Submit your questionnaire? Your portal will pause while we review it.');">
                                    Submit Questionnaire
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>

        </div>
    </div>

@endsection
