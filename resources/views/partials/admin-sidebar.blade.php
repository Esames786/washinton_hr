@php
    use App\Helpers\AdminSidebarHelper;

    $menus = AdminSidebarHelper::menu();
    $user = auth()->guard('admin')->user();

//dd([
//    'user_class'     => get_class($user),
//    'guard_name'     => $user->guard_name,
//    'roles_relation' => $user->roles->pluck('name'),
//    'get_role_names' => $user->getRoleNames(),
//    'permissions'    => $user->getAllPermissions()->pluck('name'),
//]);
@endphp

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
{{--        <ul class="sidebar-menu" id="sidebar-menu">--}}
{{--            <li>--}}
{{--                <a href="{{route('admin.dashboard')}}">--}}
{{--                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>--}}
{{--                    <span>Dashboard</span>--}}
{{--                </a>--}}
{{--            </li>--}}
{{--            <li class="dropdown">--}}
{{--                <a href="javascript:void(0)">--}}
{{--                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>--}}
{{--                    <span>Employee Settings</span>--}}
{{--                </a>--}}
{{--                <ul class="sidebar-submenu">--}}
{{--                    <li>--}}
{{--                        <a href="{{route('admin.gratuity_settings.index')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Gratuity Setting</a>--}}
{{--                    </li>--}}
{{--                    <li>--}}
{{--                        <a href="{{route('admin.commission_settings.index')}}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>--}}
{{--                            Commission Setting</a>--}}
{{--                    </li>--}}
{{--                    <li>--}}
{{--                        <a href="{{route('admin.document_settings.index')}}"><i class="ri-circle-fill circle-icon text-info-main w-auto"></i>Document Setting</a>--}}
{{--                    </li>--}}
{{--                    <li>--}}
{{--                        <a href="{{route('admin.shift_attendance_rules.index')}}"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i>Employee Shifts Rules</a>--}}
{{--                    </li>--}}
{{--                </ul>--}}
{{--            </li>--}}
{{--            <li class="dropdown">--}}
{{--                <a href="javascript:void(0)">--}}
{{--                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>--}}
{{--                    <span>User Management</span>--}}
{{--                </a>--}}
{{--                <ul class="sidebar-submenu">--}}
{{--                    <li>--}}
{{--                        <a href="{{route('admin.roles.index')}}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Roles</a>--}}
{{--                    </li>--}}
{{--                    <li>--}}
{{--                        <a href="{{route('admin.permissions.index')}}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>Permission</a>--}}
{{--                    </li>--}}
{{--                </ul>--}}
{{--            </li>--}}

{{--        </ul>--}}
        <ul class="sidebar-menu" id="sidebar-menu">
            @foreach($menus as $menu)
                @if(isset($menu['route']))
                    {{-- Single link --}}
                    @if($user->can($menu['route']) || $user->role_id==1)
                        <li  class="mb-2">
                            <a href="{{ route($menu['route']) }}">
                                <i class="{{ $menu['icon'] }} menu-icon"></i>
                                {{--<iconify-icon icon="{{ $menu['icon'] }}" class="menu-icon"></iconify-icon>--}}
                                <span>{{ $menu['title'] }}</span>
                            </a>
                        </li>
                    @endif
                @else
                    {{-- Dropdown --}}
                    @php
                        $submenuItems = collect($menu['items'])->filter(fn($item) => $user->can($item['route']) || $user->role_id==1);
                    @endphp

                    @if($submenuItems->isNotEmpty())
                        <li class="dropdown mb-2">
                            <a href="javascript:void(0)">
                                <i class="{{ $menu['icon'] }} menu-icon"></i>
{{--                                <iconify-icon icon="{{ $menu['icon'] }}" class="menu-icon"></iconify-icon>--}}
                                <span>{{ $menu['title'] }}</span>
                            </a>
                            <ul class="sidebar-submenu">
                                @foreach($submenuItems as $item)
                                    <li>
                                        <a href="{{ route($item['route']) }}">
                                            <i class="ri-circle-fill circle-icon {{ $item['iconClass'] ?? '' }} w-auto"></i>
                                            {{ $item['title'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endif
            @endforeach
        </ul>
    </div>
</aside>

