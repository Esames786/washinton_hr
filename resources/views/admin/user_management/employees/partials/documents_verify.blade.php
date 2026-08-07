@php
    // Round-4: uploads are versioned — subcontractors can re-upload but never delete, so a
    // document type can have several rows. The newest per type is the CURRENT one; older rows
    // remain here, clearly labelled, and stay downloadable. Removal is HR-only (button below).
    $__docs = ($employee->documents ?? collect())->sortByDesc('id')->values();
    $__latestIds = $__docs->groupBy('document_setting_id')->map(fn ($g) => $g->first()->id);
@endphp
<div class="row g-3">
    @forelse($__docs as $doc)
        @php $__isCurrent = ((int) $__latestIds->get($doc->document_setting_id) === (int) $doc->id); @endphp
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card doc-card h-100 text-center shadow-sm border-0 {{ $__isCurrent ? '' : 'opacity-75' }}">
                @php
                    $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                    // Round-4 #2: file may live on another deployment — serve via the doc-file route.
                    $__docUrl = route('admin.employees.documents.file', $doc->id);
                @endphp
                @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','bmp','webp','jfif','heic','heif','avif']))
                    <img src="{{ $__docUrl }}"
                         class="img-fluid rounded mb-2 doc-img h-200-px"
                         alt="{{ $doc->file_name }}">
                @elseif(strtolower($ext) === 'pdf')
                    <i class="bi bi-file-earmark-pdf text-danger doc-img" style="font-size:48px;"></i>
                @else
                    <i class="bi bi-file-earmark doc-img" style="font-size:48px;"></i>
                @endif

                <p class="small fw-medium mb-1 text-truncate">{{ $doc->file_name }}</p>
                <p class="mb-1">
                    @if($__isCurrent)
                        <span class="badge bg-primary-subtle text-primary">Current</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">Older version</span>
                    @endif
                    <span class="text-muted" style="font-size:11px;">{{ optional($doc->created_at)->format('d M Y H:i') }}</span>
                </p>

                <a href="{{ asset($doc->file_path) }}" target="_blank"
                   class="btn btn-sm btn-outline-primary mb-2 w-100">View</a>

                <div class="form-switch switch-primary mt-2">
                    <input class="form-check-input verify-checkbox" type="checkbox" data-id="{{ $doc->id }}" role="switch" id="switch{{ $doc->id }}"
                        {{ $doc->status ? 'checked' : '' }}>
                    <label class="form-check-label line-height-1 fw-medium text-secondary-light" for="switch{{ $doc->id }}">
                        Verified
                    </label>
                </div>

                {{-- Round-4: removal is HR-only — subcontractors have no delete anymore. --}}
                <form method="POST" action="{{ route('admin.employees.documents.delete', $doc->id) }}"
                      class="mt-2" onsubmit="return confirm('Permanently remove this document file? The subcontractor was told to contact HR for removals.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">Remove</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted">No documents uploaded.</p>
    @endforelse
</div>
