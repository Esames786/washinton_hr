@extends('layout.master')
@section('pageName', 'Onboarding Questionnaire')

@section('content')

    @include('partials.alerts')

    @if($errors->any())
        <div class="alert alert-danger py-2" style="font-size:13.5px;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="row g-3">

        {{-- ── Existing questions ── --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-light d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Questions</h5>
                    <span class="text-muted" style="font-size:12.5px;">
                        Agents answer these after signing their NDA
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($questions->isEmpty())
                        <div class="p-4 text-center text-muted">
                            No questions yet. Add the first one using the form beside this.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead>
                                <tr>
                                    <th style="width:48px;">#</th>
                                    <th>Question</th>
                                    <th style="width:130px;">Type</th>
                                    <th style="width:90px;">Required</th>
                                    <th style="width:90px;">Answers</th>
                                    <th style="width:150px;" class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($questions as $q)
                                    <tr class="{{ $q->status ? '' : 'opacity-50' }}">
                                        <td>{{ $q->sort_order }}</td>
                                        <td>
                                            {{ $q->question }}
                                            @unless($q->status)
                                                <span class="badge bg-secondary ms-1">inactive</span>
                                            @endunless
                                            @if($q->brand)
                                                <span class="badge bg-info text-dark ms-1">{{ $q->brand }}</span>
                                            @endif
                                            @if($q->help_text)
                                                <div class="text-muted" style="font-size:12px;">{{ $q->help_text }}</div>
                                            @endif
                                            @if($q->choices())
                                                <div class="text-muted" style="font-size:12px;">
                                                    Choices: {{ implode(' · ', $q->choices()) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-light text-dark">{{ \App\Models\QuestionnaireQuestion::TYPES[$q->field_type] ?? $q->field_type }}</span></td>
                                        <td>{{ $q->is_required ? 'Yes' : 'No' }}</td>
                                        <td>{{ $answerCounts[$q->id] ?? 0 }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="collapse" data-bs-target="#edit{{ $q->id }}">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.questionnaire.destroy', $q->id) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('{{ ($answerCounts[$q->id] ?? 0) > 0 ? 'Agents have already answered this. It will be deactivated, not deleted. Continue?' : 'Delete this question?' }}');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="edit{{ $q->id }}">
                                        <td colspan="6" class="bg-light">
                                            <form method="POST" action="{{ route('admin.questionnaire.update', $q->id) }}" class="row g-2">
                                                @csrf @method('PUT')
                                                <div class="col-md-12">
                                                    <label class="form-label small fw-semibold">Question</label>
                                                    <input name="question" value="{{ $q->question }}" class="form-control form-control-sm" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Help text</label>
                                                    <input name="help_text" value="{{ $q->help_text }}" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-semibold">Type</label>
                                                    <select name="field_type" class="form-select form-select-sm">
                                                        @foreach(\App\Models\QuestionnaireQuestion::TYPES as $k => $label)
                                                            <option value="{{ $k }}" @selected($q->field_type === $k)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-semibold">Order</label>
                                                    <input type="number" name="sort_order" value="{{ $q->sort_order }}" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Choices (one per line)</label>
                                                    <textarea name="options" rows="2" class="form-control form-control-sm">{{ $q->options }}</textarea>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-semibold">Brand</label>
                                                    <select name="brand" class="form-select form-select-sm">
                                                        <option value="" @selected(!$q->brand)>Both</option>
                                                        <option value="hellotransport" @selected($q->brand==='hellotransport')>Hello only</option>
                                                        <option value="crazyrays" @selected($q->brand==='crazyrays')>CrazyRays only</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 d-flex align-items-end gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="is_required" value="1" id="req{{ $q->id }}" @checked($q->is_required)>
                                                        <label class="form-check-label small" for="req{{ $q->id }}">Required</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="status" value="1" id="act{{ $q->id }}" @checked($q->status)>
                                                        <label class="form-check-label small" for="act{{ $q->id }}">Active</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <button class="btn btn-sm btn-primary">Save changes</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Add a question ── --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light"><h6 class="mb-0">Add a question</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.questionnaire.store') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Question <span class="text-danger">*</span></label>
                            <textarea name="question" rows="2" class="form-control form-control-sm" required
                                      placeholder="e.g. Describe your experience selling to U.S. customers.">{{ old('question') }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Help text</label>
                            <input name="help_text" value="{{ old('help_text') }}" class="form-control form-control-sm"
                                   placeholder="Optional guidance shown under the question">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Answer type</label>
                            <select name="field_type" id="newType" class="form-select form-select-sm">
                                @foreach(\App\Models\QuestionnaireQuestion::TYPES as $k => $label)
                                    <option value="{{ $k }}" @selected(old('field_type')===$k)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Choices (one per line)</label>
                            <textarea name="options" rows="3" class="form-control form-control-sm"
                                      placeholder="Only for dropdown or multiple choice">{{ old('options') }}</textarea>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Order</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Brand</label>
                                <select name="brand" class="form-select form-select-sm">
                                    <option value="">Both</option>
                                    <option value="hellotransport">Hello only</option>
                                    <option value="crazyrays">CrazyRays only</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="newReq" checked>
                            <label class="form-check-label small" for="newReq">Required</label>
                        </div>
                        <button class="btn btn-primary btn-sm w-100">Add question</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection
