@extends('layout.master')
@section('pageName', 'Review Application')

@section('content')

    @include('partials.alerts')

    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h5 class="mb-0">
            {{ $lead->full_name }}
            <span class="badge {{ $lead->status_badge }} ms-2">{{ $lead->status_label }}</span>
        </h5>
        <a href="{{ route('admin.campaign_leads.index') }}" class="btn btn-sm btn-outline-secondary">
            ← Back to Campaign Leads
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-light fw-bold">Applicant</div>
                <div class="card-body p-3">
                    <div class="row g-1 small">
                        <div class="col-6"><strong>Email:</strong> {{ $lead->email }}</div>
                        <div class="col-6"><strong>Phone:</strong> {{ $lead->phone ?: '—' }}</div>
                        <div class="col-6"><strong>Country:</strong> {{ $lead->country ?: '—' }}</div>
                        <div class="col-6"><strong>City / State:</strong> {{ collect([$lead->city, $lead->state])->filter()->implode(', ') ?: '—' }}</div>
                        <div class="col-6"><strong>Time Zone:</strong> {{ $lead->time_zone ?: '—' }}</div>
                        <div class="col-6"><strong>DOB:</strong> {{ optional($lead->dob)->format('d M Y') ?? '—' }}</div>
                        <div class="col-6"><strong>ID Number:</strong> {{ $lead->national_id ?: '—' }}</div>
                        <div class="col-6"><strong>Father's Name:</strong> {{ $lead->father_name ?: '—' }}</div>
                        <div class="col-12"><strong>Address:</strong> {{ $lead->address ?: '—' }}</div>
                    </div>

                    <hr class="my-3">

                    <div class="row g-1 small">
                        <div class="col-6">
                            <strong>Worked with U.S. customers:</strong>
                            {{ $lead->us_experience ? ucfirst($lead->us_experience) : '—' }}
                        </div>
                        <div class="col-6">
                            <strong>Can work U.S. hours:</strong>
                            {{ $lead->us_hours ? ucfirst($lead->us_hours) : '—' }}
                        </div>
                        <div class="col-12 mt-1">
                            <strong>LinkedIn:</strong>
                            @if($lead->linkedin_url)
                                <a href="{{ $lead->linkedin_url }}" target="_blank" rel="noopener">{{ $lead->linkedin_url }}</a>
                            @else — @endif
                        </div>
                        <div class="col-12 mt-1">
                            <strong>CV:</strong>
                            @if($lead->resume_path)
                                {{-- The CV is saved by the website's application form, on the website's
                                     disk (hellotransport.com), not on this HR server. --}}
                                <a href="{{ rtrim(env('HELLOTRANSPORT_BRIDGE_URL', 'https://hellotransport.com'), '/') . '/storage/' . ltrim($lead->resume_path, '/') }}" target="_blank" rel="noopener">Download</a>
                            @else — @endif
                        </div>
                    </div>

                    @if($lead->additional_info)
                        <hr class="my-3">
                        <strong class="small">About the applicant</strong>
                        <p class="small text-muted mb-0 mt-1" style="white-space:pre-wrap;">{{ $lead->additional_info }}</p>
                    @endif

                    @if($lead->rejection_note)
                        <hr class="my-3">
                        <strong class="small text-danger">Rejection reason</strong>
                        <p class="small text-muted mb-0 mt-1">{{ $lead->rejection_note }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-light fw-bold">Decision</div>
                <div class="card-body p-3">

                    @if($lead->is_shortlisted)
                        <div class="alert alert-success py-2 small mb-2">
                            Shortlisted. An HR profile was created for this applicant.
                        </div>
                        @if($lead->employee)
                            <a href="{{ route('admin.employees.show', $lead->employee->id) }}" class="btn btn-sm btn-primary w-100">
                                Open HR profile
                            </a>
                        @endif
                    @elseif($lead->status === \App\Models\CampaignLead::REJECTED)
                        <div class="alert alert-danger py-2 small mb-0">
                            This application was rejected.
                        </div>
                    @else
                        <p class="small text-muted">
                            Shortlisting creates the HR profile and starts onboarding at
                            <strong>NDA Pending</strong>. No CRM account is created yet — that happens
                            once onboarding completes.
                        </p>

                        @if($lead->status !== \App\Models\CampaignLead::UNDER_REVIEW)
                            <form method="POST" action="{{ route('admin.campaign_leads.review', $lead->id) }}" class="mb-2">
                                @csrf
                                <button class="btn btn-sm btn-outline-info w-100">Mark as Under Review</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.campaign_leads.shortlist', $lead->id) }}" class="mb-3"
                              onsubmit="return confirm('Shortlist this applicant and create their HR profile?');">
                            @csrf
                            <button class="btn btn-sm btn-success w-100">Shortlist &amp; create HR profile</button>
                        </form>

                        <hr>

                        <form method="POST" action="{{ route('admin.campaign_leads.reject', $lead->id) }}"
                              onsubmit="return confirm('Reject this application?');">
                            @csrf
                            <label class="form-label small mb-1">Reason for rejection (sent to the applicant)</label>
                            <textarea name="rejection_note" class="form-control form-control-sm mb-2" rows="3"
                                      placeholder="Optional"></textarea>
                            <button class="btn btn-sm btn-outline-danger w-100">Reject application</button>
                        </form>
                    @endif

                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light fw-bold">Submission</div>
                <div class="card-body p-3 small">
                    <div><strong>Applied:</strong> {{ optional($lead->created_at)->format('d M Y, H:i') ?? '—' }}</div>
                    <div><strong>IP address:</strong> {{ $lead->ip_address ?: '—' }}</div>
                    <div><strong>Terms accepted:</strong>
                        {{ $lead->contract_accepted_at ? $lead->contract_accepted_at->format('d M Y, H:i') : 'No' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
