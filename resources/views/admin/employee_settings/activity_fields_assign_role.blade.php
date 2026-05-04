@extends('layout.master')

@section('pageName','Daily Activity Fields Assign to Roles')

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
            <h4>{{$daily_activity->name .(' Type: '.$daily_activity->field_type)}}</h4>
{{--            <form method="post" action="{{route('admin.permissions.store')}}">--}}
{{--                @csrf--}}
{{--                <input type="hidden" value="{{$guard_name}}" name="guard_name">--}}
{{--                <button type="submit" class="btn btn-primary">Sync Permission</button>--}}
{{--            </form>--}}
            {{--            <button type="button" class="btn btn-primary" id="syncPermission">Sync Permission</button>--}}
            <div>
                <button type="button" class="btn btn-sm btn-info" id="selectAll">Select All</button>
                <button type="button" class="btn btn-sm btn-warning" id="deselectAll">Deselect All</button>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.daily_activity_fields.assign_roles', ['id' => $id]) }}" method="post">
                @csrf
                <div class="permission-card">
                    <div class="permission-title">Roles</div>
                    @foreach($roles  as $role)
                        <div class="permission-actions">
                                <label>
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="roles[]"
                                           value="{{ $role->id }}"  {{ in_array($role->id, $assignedRoleIds) ? 'checked' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </label>
                        </div>
                    @endforeach
                </div>

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
