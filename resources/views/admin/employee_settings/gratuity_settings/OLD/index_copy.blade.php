@extends('layout.master')

@section('pageName','Gratuity')

@section('content')
    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div class="d-flex align-items-center flex-wrap gap-3">
{{--                <span class="text-md fw-medium text-secondary-light mb-0">Show</span>--}}
{{--                <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px">--}}
{{--                    <option>1</option>--}}
{{--                    <option>2</option>--}}
{{--                    <option>3</option>--}}
{{--                    <option>4</option>--}}
{{--                    <option>5</option>--}}
{{--                    <option>6</option>--}}
{{--                    <option>7</option>--}}
{{--                    <option>8</option>--}}
{{--                    <option>9</option>--}}
{{--                    <option>10</option>--}}
{{--                </select>--}}
{{--                <form class="navbar-search">--}}
{{--                    <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search">--}}
{{--                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>--}}
{{--                </form>--}}
{{--                <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px">--}}
{{--                    <option>Status</option>--}}
{{--                    <option>Active</option>--}}
{{--                    <option>Inactive</option>--}}
{{--                </select>--}}
            </div>
            <button class="btn btn-primary mb-3" id="addNew"> Add Gratuity</button>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive scroll-sm">
                <table class="table bordered-table sm-table mb-0" id="gratuityTable">
                    <thead>
                    <tr>
                        <th>S.L</th>
                        <th>Title</th>
                        <th>Amount Type</th>
                        <th>Amount Value</th>
                        <th>Gratuity Days/Year</th>
                        <th>Base</th>
                        <th>Min Years</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($settings as $index => $setting)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $setting->title }}</td>
                            <td>{{ ucfirst($setting->amount_type) }}</td>
                            <td>{{ $setting->amount_value }}</td>
                            <td>{{ $setting->gratuity_days_per_year }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $setting->calculation_base)) }}</td>
                            <td>{{ $setting->minimum_service_years }}</td>
                            <td>
                    <span class="badge bg-{{ $setting->is_active ? 'success' : 'secondary' }}">
                        {{ $setting->is_active ? 'Active' : 'Inactive' }}
                    </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info edit-btn" data-id="{{ $setting->id }}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $setting->id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
                <span>Showing 1 to 10 of 12 entries</span>
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    {{ $settings->links() }}
{{--                    <li class="page-item">--}}
{{--                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="javascript:void(0)"><iconify-icon icon="ep:d-arrow-left" class=""></iconify-icon></a>--}}
{{--                    </li>--}}
{{--                    <li class="page-item">--}}
{{--                        <a class="page-link text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md bg-primary-600 text-white" href="javascript:void(0)">1</a>--}}
{{--                    </li>--}}
{{--                    <li class="page-item">--}}
{{--                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px" href="javascript:void(0)">2</a>--}}
{{--                    </li>--}}
{{--                    <li class="page-item">--}}
{{--                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="javascript:void(0)">3</a>--}}
{{--                    </li>--}}
{{--                    <li class="page-item">--}}
{{--                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="javascript:void(0)">4</a>--}}
{{--                    </li>--}}
{{--                    <li class="page-item">--}}
{{--                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="javascript:void(0)">5</a>--}}
{{--                    </li>--}}
{{--                    <li class="page-item">--}}
{{--                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="javascript:void(0)"> <iconify-icon icon="ep:d-arrow-right" class=""></iconify-icon> </a>--}}
{{--                    </li>--}}
                </ul>
            </div>
        </div>
    </div>
    <div class="modal fade" id="gratuityModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title">Gratuity Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="gratuityForm">
                        @csrf
                        <input type="hidden" name="id" id="settingId">
                        <div class="mb-3">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Amount Type</label>
                            <select name="amount_type" class="form-select" required>
                                <option value="fixed">Fixed</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Amount Value</label>
                            <input type="number" name="amount_value" class="form-control" min="0" step="any" required>
                        </div>
                        <div class="mb-3">
                            <label>Gratuity Days Per Year</label>
                            <input type="number" name="gratuity_days_per_year" class="form-control" min="0">
                        </div>
                        <div class="mb-3">
                            <label>Calculation Base</label>
                            <select name="calculation_base" class="form-select">
                                <option value="basic_salary">Basic Salary</option>
                                <option value="gross_salary">Gross Salary</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Minimum Service Years</label>
                            <input type="number" name="minimum_service_years" class="form-control" min="0">
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('#addNew').on('click', function() {
                $('#gratuityForm')[0].reset();
                $('#gratuity_id').val('');
                $('#gratuityModal').modal('show');
            });

            $('.editBtn').on('click', function() {
                let id = $(this).data('id');
                $.get('/gratuity-settings/' + id + '/edit', function(data) {
                    $('#gratuity_id').val(data.id);
                    $('#title').val(data.title);
                    $('#amount_type').val(data.amount_type);
                    $('#amount_value').val(data.amount_value);
                    $('#description').val(data.description);
                    $('#gratuityModal').modal('show');
                });
            });

            $('#gratuityForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#gratuity_id').val();
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: '{{ route('admin.gratuity_settings.store')}}',
                    method: method,
                    data: {
                        _token: '{{ csrf_token() }}',
                        title: $('#title').val(),
                        amount_type: $('#amount_type').val(),
                        amount_value: $('#amount_value').val(),
                        description: $('#description').val(),
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('Validation failed.');
                    }
                });
            });

            $('.deleteBtn').on('click', function() {
                let id = $(this).data('id');
                if (confirm('Are you sure?')) {
                    $.ajax({
                        url: '/gratuity-settings/' + id,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $('#row-' + id).remove();
                        }
                    });
                }
            });
        });
    </script>
@endpush
