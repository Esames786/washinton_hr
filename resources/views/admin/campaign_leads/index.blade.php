@extends('layout.master')
@section('pageName', 'Campaign Leads')

@section('content')

    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="mb-0">Campaign Leads</h5>
            <form method="GET" class="d-flex gap-2">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       placeholder="Name, email or phone" style="min-width:220px;">
                <button class="btn btn-sm btn-primary">Search</button>
                @if(request('q'))
                    <a href="{{ route('admin.campaign_leads.index', ['status' => $status]) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </form>
        </div>

        {{-- Status tabs, with counts so the admin can see where the work is --}}
        <div class="px-24 pt-16">
            @php
                $tabs = [
                    'all'          => 'All',
                    'pending'      => 'Application Received',
                    'under_review' => 'Under Review',
                    'shortlisted'  => 'Shortlisted',
                    'rejected'     => 'Rejected',
                ];
            @endphp
            <ul class="nav nav-pills gap-2 mb-3">
                @foreach($tabs as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 {{ ($status ?? 'all') === $key ? 'active' : 'bg-light text-dark' }}"
                           href="{{ route('admin.campaign_leads.index', array_filter(['status' => $key, 'q' => request('q')])) }}">
                            {{ $label }}
                            <span class="badge bg-white text-dark ms-1">{{ $counts[$key] ?? 0 }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card-body p-24 pt-0">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Location</th>
                        <th>Applied</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td>{{ $lead->id }}</td>
                            <td>{{ $lead->full_name }}</td>
                            <td>{{ $lead->email }}</td>
                            <td>{{ $lead->phone }}</td>
                            <td>{{ collect([$lead->city, $lead->state, $lead->country])->filter()->implode(', ') ?: '—' }}</td>
                            <td>{{ optional($lead->created_at)->format('d M Y') ?? '—' }}</td>
                            <td><span class="badge {{ $lead->status_badge }}">{{ $lead->status_label }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('admin.campaign_leads.show', $lead->id) }}" class="btn btn-sm btn-outline-primary">
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No applications here yet.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $leads->links() }}
            </div>
        </div>
    </div>

@endsection
