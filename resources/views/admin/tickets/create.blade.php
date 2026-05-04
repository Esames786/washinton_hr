@extends('layout.master')

@section('pageName', 'Create Ticket')

@section('content')
    <div class="card h-100">
        <div class="card-header"><h4>Create Ticket</h4></div>
        <div class="card-body">
            <form action="{{ route('admin.tickets.store') }}" method="POST">
                @csrf
                <div class="row pb-3">
                    <div class="col-6">
                        <label>Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label>Ticket Type</label>
                        <select name="ticket_type_id" id="ticket_type_id" class="form-select" required>
                            <option value="">Select Type</option>
                            @foreach($ticketTypes as $type)
                                <option value="{{ $type->id }}" data-fields='@json($type->form_fields)'>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="dynamic-fields"></div>

                <div class="text-end">
                    <button class="btn btn-primary">Create Ticket</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function(){
            $('#ticket_type_id').on('change', function(){
                let fields = $(this).find(':selected').data('fields');
                let container = $('#dynamic-fields');
                container.empty();

                if (fields && fields.length > 0) {
                    fields.forEach(field => {
                        let html = '';
                        if (field.type === 'textarea') {
                            html = `<div class="mb-3">
                                <label>${field.name}</label>
                                <textarea name="fields[${field.name}]" class="form-control" ${field.required ? 'required' : ''}></textarea>
                            </div>`;
                        } else {
                            html = `<div class="mb-3">
                                <label>${field.name}</label>
                                <input type="${field.type}" name="fields[${field.name}]" class="form-control" ${field.required ? 'required' : ''}>
                            </div>`;
                        }
                        container.append(html);
                    });
                }
            });
        });
    </script>
@endpush
