<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="#" class="sidebar-logo justify-content-center">
            <img src="{{asset('assets/images/logo/hello_transport.png')}}" alt="site logo" class="light-logo">
            <img src="{{asset('assets/images/logo/hello_transport.png')}}" alt="site logo" class="dark-logo">
            <img src="{{asset('assets/images/logo/hello_transport.png')}}" alt="site logo" class="logo-icon">
        </a>
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

