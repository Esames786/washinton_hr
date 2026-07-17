@php
    // #17: CrazyRays-originated subcontractors see CrazyRays branding; default Hello Transport.
    $__footEmp = auth('employee')->check() ? auth('employee')->user() : null;
    $__brand   = $__footEmp ? $__footEmp->brandName() . ' HR' : 'Hello Transport HR';
@endphp
<footer class="d-footer">
    <div class="row align-items-center justify-content-between">
        <div class="col-auto">
            <p class="mb-0">© {{ date('Y') }} {{ $__brand }}. All Rights Reserved.</p>
        </div>
    </div>
</footer>
