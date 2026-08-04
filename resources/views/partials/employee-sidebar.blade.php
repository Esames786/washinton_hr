<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    @php
        // Portal chrome follows the DOMAIN it is being viewed on (PORTAL_BRAND), falling back to
        // the signed-in person's own brand on the Hello domains. Previously the non-CrazyRays
        // branch still served crazyrays.svg, so Hello staff saw the CrazyRays logo either way.
        $__brand = \App\Support\Brand::current();
        $__isCrBrand = ($__brand['key'] ?? '') === 'crazyrays';
    @endphp
    <div>
        @if($__isCrBrand)
            <a href="#" class="sidebar-logo justify-content-center" style="text-decoration:none;">
                <span style="display:inline-flex;align-items:center;gap:8px;">
                    <span style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,#16a34a,#0ea5e9);color:#fff;font-weight:900;font-size:13px;display:inline-flex;align-items:center;justify-content:center;">CR</span>
                    <span class="light-logo" style="font-weight:800;color:#111;font-size:14px;white-space:nowrap;">{{ $__brand['name'] }}</span>
                    <span class="dark-logo" style="font-weight:800;color:#fff;font-size:14px;white-space:nowrap;">{{ $__brand['name'] }}</span>
                </span>
            </a>
        @else
            <a href="#" class="sidebar-logo justify-content-center">
                <img src="{{ asset($__brand['logo'] ?? 'assets/images/logo/hello_transport.png') }}" alt="{{ $__brand['name'] ?? 'site logo' }}" class="light-logo">
                <img src="{{ asset($__brand['logo'] ?? 'assets/images/logo/hello_transport.png') }}" alt="{{ $__brand['name'] ?? 'site logo' }}" class="dark-logo">
                <img src="{{ asset($__brand['logo'] ?? 'assets/images/logo/hello_transport.png') }}" alt="{{ $__brand['name'] ?? 'site logo' }}" class="logo-icon">
            </a>
        @endif
    </div>
    <div class="sidebar-menu-area">
                <ul class="sidebar-menu" id="sidebar-menu">
                    <li>
                        <a href="{{route('employee.dashboard')}}">
                            <i class="bi bi-speedometer2 menu-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{route('employee.activities.index')}}">
                            <i class="bi bi-journal-text menu-icon"></i>
                            <span>Daily Activities</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{route('employee.attendance.index')}}">
                            <i class="bi bi-calendar-check menu-icon"></i>
                            <span>Attendance List</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{route('employee.breaks.index')}}">
                            <i class="bi bi-calendar-check menu-icon"></i>
                            <span>Break List</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{route('employee.tickets.index')}}">
                            <i class="bi bi-ticket-perforated menu-icon"></i>
                            <span>Tickets</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{route('employee.payslips.index')}}">
                            <i class="bi bi-cash-stack menu-icon"></i>
                            <span>Payslips</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{route('employee.orders.list')}}">
                            <i class="bi bi-box-seam menu-icon"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                </ul>
    </div>
</aside>

