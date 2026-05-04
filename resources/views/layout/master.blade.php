<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @include('partials.css')
    @stack('cssLinks')
</head>

<body>
@auth('admin')
    @include('partials.admin-sidebar')
@endauth

@auth('employee')
    @include('partials.employee-sidebar')
@endauth

<main class="dashboard-main">
    @include('partials.navbar-header')

    <div class="dashboard-main-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
            <h6 class="fw-semibold mb-0">@yield('pageName')</h6>
            <ul class="d-flex align-items-center gap-2">
                <li class="fw-medium">
                    <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
                        <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                        Dashboard
                    </a>
                </li>
                <li>-</li>
                <li class="fw-medium">AI</li>
            </ul>
        </div>

        @yield('content')
    </div>

    @include('partials.footer')
</main>

@include('partials.scripts')

@stack('scripts')

</body>

</html>
