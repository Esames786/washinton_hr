<div class="row g-3">
    @forelse($employee->documents as $doc)
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card doc-card h-100 text-center shadow-sm border-0">
                @php $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION); @endphp
                @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','bmp','webp']))
                    <img src="{{ asset($doc->file_path) }}"
                         class="img-fluid rounded mb-2 doc-img h-200-px"
                         alt="{{ $doc->file_name }}">
                @elseif(strtolower($ext) === 'pdf')
                    <i class="bi bi-file-earmark-pdf text-danger doc-img" style="font-size:48px;"></i>
                @else
                    <i class="bi bi-file-earmark doc-img" style="font-size:48px;"></i>
                @endif

                <p class="small fw-medium mb-1 text-truncate">{{ $doc->file_name }}</p>

                <a href="{{ asset($doc->file_path) }}" target="_blank"
                   class="btn btn-sm btn-outline-primary mb-2 w-100">View</a>

                <div class="form-switch switch-primary mt-2">
                    <input class="form-check-input verify-checkbox" type="checkbox" data-id="{{ $doc->id }}" role="switch" id="switch{{ $doc->id }}"
                        {{ $doc->status ? 'checked' : '' }}>
                    <label class="form-check-label line-height-1 fw-medium text-secondary-light" for="switch{{ $doc->id }}">
                        Verified
                    </label>
                </div>
{{--                <div class="form-check form-switch switch-primary d-flex align-items-center gap-2 justify-content-center">--}}
{{--                    <input class="form-check-input verify-checkbox" type="checkbox"--}}
{{--                           data-id="{{ $doc->id }}"--}}
{{--                        {{ $doc->status ? 'checked' : '' }}>--}}
{{--                    <label class="form-check-label fw-medium text-secondary-light">Verified</label>--}}
{{--                </div>--}}
            </div>
        </div>
    @empty
        <p class="text-muted">No documents uploaded.</p>
    @endforelse
</div>
