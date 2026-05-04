@extends('layout.master')

@section('pageName','Permissions')

@push('cssLinks')
    <style>
        .permission-card {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .permission-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .permission-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .permission-actions label {
            font-weight: normal;
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <form method="post" action="{{route('admin.permissions.store')}}">
                @csrf
                <input type="hidden" value="{{$guard_name}}" name="guard_name">
                <button type="submit" class="btn btn-primary">Sync Permission</button>
            </form>
{{--            <button type="button" class="btn btn-primary" id="syncPermission">Sync Permission</button>--}}
            <div>
                <button type="button" class="btn btn-sm btn-purple" id="selectAll">Select All</button>
                <button type="button" class="btn btn-sm btn-warning" id="deselectAll">Deselect All</button>
            </div>
        </div>

        <div class="card-body">

{{--            <form action="{{ route('permissions.assignPermission', ['id' => $id]) }}" method="post">--}}
{{--                @foreach($permissions  as $permission)--}}
{{--                    <div class="permission-card">--}}
{{--                        <div class="permission-title">Clinical trials</div>--}}
{{--                        <div class="permission-actions">--}}
{{--                            <label><input type="checkbox" class="form-check-input" name="permissions[]" value="create"> create</label>--}}
{{--                            <label><input type="checkbox" class="form-check-input" name="permissions[]" value="view"> view</label>--}}
{{--                            <label><input type="checkbox" class="form-check-input" name="permissions[]" value="list"> list</label>--}}
{{--                            <label><input type="checkbox" class="form-check-input" name="permissions[]" value="view-by-id"> view-by-id</label>--}}
{{--                            <label><input type="checkbox" class="form-check-input" name="permissions[]" value="edit"> edit</label>--}}
{{--                            <label><input type="checkbox" class="form-check-input" name="permissions[]" value="store"> store</label>--}}
{{--                            <label><input type="checkbox" class="form-check-input" name="permissions[]" value="update"> update</label>--}}
{{--                            <label><input type="checkbox" class="form-check-input" name="permissions[]" value="delete"> delete</label>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                @endforeach--}}
{{--            </form>--}}
{{--            <form action="{{ route('admin.permissions.assignPermission', ['id' => $id]) }}" method="post">--}}
{{--                @csrf--}}
{{--                {{$permissions}};--}}
{{--                @foreach($permissions->groupBy(function($perm) {--}}
{{--                    return explode('.', $perm->name)[1]; // Group by prefix before first dot--}}
{{--                }) as $category => $categoryPermissions)--}}
{{--                    <div class="permission-card">--}}
{{--                        <div class="permission-title">{{ ucfirst(str_replace('_', ' ', $category)) }}</div>--}}
{{--                        <div class="permission-actions">--}}
{{--                            @foreach($categoryPermissions as $permission)--}}
{{--                                <label>--}}
{{--                                    <input type="checkbox"--}}
{{--                                           class="form-check-input"--}}
{{--                                           name="permissions[]"--}}
{{--                                           value="{{ $permission->name }}"--}}
{{--                                           id="{{ $permission->name }}"--}}
{{--                                           @if($permission->roles->contains($id)) checked @endif>--}}
{{--                                    {{ str_replace($category . '.', '', $permission->name) }}--}}
{{--                                </label>--}}
{{--                            @endforeach--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                @endforeach--}}
{{--            </form>--}}
            <form action="{{ route('admin.permissions.assignPermission', ['id' => $id]) }}" method="post">
                @csrf

                @foreach($permissions->groupBy(function($perm) {
                    // 1st level group (ignoring "admin")
                    $parts = explode('.', $perm->name);
                    return $parts[1] ?? 'other';
                }) as $level1 => $level1Permissions)

                    <div class="permission-card">
                        <div class="permission-title">{{ ucfirst(str_replace('_', ' ', $level1)) }}</div>

                        @php
                            // Group by second level if exists
                            $hasSecondLevel = $level1Permissions->contains(function($perm) {
                                return count(explode('.', $perm->name)) > 3;
                            });
                        @endphp

                        @if($hasSecondLevel)
                            {{-- Nested grouping for submodules --}}
                            @foreach($level1Permissions->groupBy(function($perm) {
                                $parts = explode('.', $perm->name);
                                return $parts[2] ?? 'general';
                            }) as $level2 => $level2Permissions)

                                <div class="sub-permission-card" style="margin-left:20px;">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $level2)) }}</strong>
                                    <div class="permission-actions">
                                        @foreach($level2Permissions as $permission)
                                            @php
                                                $parts = explode('.', $permission->name);
                                                $action = end($parts);
                                            @endphp
                                            <label>
                                                <input type="checkbox"
                                                       class="form-check-input"
                                                       name="permissions[]"
                                                       value="{{ $permission->name }}"
                                                       @if($permission->roles->contains($id)) checked @endif>
                                                {{ ucfirst($action) }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                            @endforeach
                        @else
                            {{-- No submodules, show actions directly --}}
                            <div class="permission-actions">
                                @foreach($level1Permissions as $permission)
                                    @php
                                        $parts = explode('.', $permission->name);
                                        $action = end($parts);
                                    @endphp
                                    <label>
                                        <input type="checkbox"
                                               class="form-check-input"
                                               name="permissions[]"
                                               value="{{ $permission->name }}"
                                               @if($permission->roles->contains($id)) checked @endif>
                                        {{ ucfirst($action) }}
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                @endforeach

                <button type="submit" class="btn btn-success btn-md float-end">Assign to Role
                </button>
            </form>



        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllBtn = document.getElementById('selectAll');
            const deselectAllBtn = document.getElementById('deselectAll');
            const syncBtn = document.getElementById('syncPermission');

            const getAllCheckboxes = () => document.querySelectorAll('input[type="checkbox"].form-check-input');

            selectAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                getAllCheckboxes().forEach(cb => cb.checked = true);
            });

            deselectAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                getAllCheckboxes().forEach(cb => cb.checked = false);
            });

            syncBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // example: show selected values (replace with AJAX as needed)
                const selected = Array.from(getAllCheckboxes()).filter(c=>c.checked).map(c=>c.value);
                alert('Selected permissions: ' + (selected.length ? selected.join(', ') : 'none'));
            });
        });
    </script>
@endpush
