@extends('layouts.dashboard')

@section('content')
    <style>
        .dt-buttons {
            margin-left: -3px !important;
        }
    </style>
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive mt-4" style="border-top: 3px solid #2aa9b9;">

                <div class="row">
                    <div class="col-md-6">
                        <h4 class="m-t-0 header-title mb-5"><b>{{ __('page.m-account')[0] }}</b></h4>
                    </div>
                    <div class="col-md-6 text-right">
                        @if (permission('uni2'))
                            <button class="btn btn-success waves-effect waves-light m-b-5 btn-rounded" id="addNew"><i
                                    class="fa fa-plus-square m-r-5"></i> <span>{{ __('page.m-account')[1] }}</span>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">

                        <form method="GET" action="{{ route('m-accounts.index') }}">

                            <div class="d-flex justify-content-between">
                                <div class="d-flex">
                                    <select id="year" name="year" class="form-control" style="width: 200px;">
                                        <option value="">Select Year</option>
                                        @for ($year = 2023; $year <= now()->year; $year++)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endfor
                                    </select>
                                    <select id="month" name="month" class="form-control" style="width: 200px;">
                                        <option value="">Select Month</option>
                                        @for ($month = 1; $month <= 12; $month++)
                                            <option value="{{ $month }}">
                                                {{ DateTime::createFromFormat('!m', $month)->format('F') }}</option>
                                        @endfor
                                    </select>
                                    <button class="btn btn-sm btn btn-success btn-rounded"
                                        style="width: 60px;height: 38px;margin-left: 10px;">Filter</button>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="col-md-6 text-right">
                        <h5 class="text-success">Balance Amount</h5>
                        <h4 class="m-t-0 header-title mb-5" id="balance"><b>
                                @if ($balace > 0)
                                    {{ $balace }}
                                @else
                                    <span class="text-danger">{{ $balace }}</span>
                                @endif
                            </b></h4>
                    </div>
                </div>


                <table id="data-table" class="table table-striped table-bordered mt-4" cellspacing="0" width="100%">
                    <thead class="theme-primary text-white">
                        <tr>
                            <th>{{ __('page.m-account')[2] }}</th>
                            <th>{{ __('page.m-account')[8] }}</th>
                            <th>Item {{ __('page.m-account')[3] }}</th>
                            <th>Date</th>
                            <th>{{ __('page.m-account')[9] }}</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>

                            <th>{{ __('page.m-account')[4] }}</th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Modal Start -->
    <div class="modal fade bd-example-modal-xl" id="tableModal" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width: 100%">
            <div class="modal-content" id="modalContent">

            </div>
        </div>
    </div>
    <!-- Modal End -->
@endsection
@section('script')
    <script>
        $(document).ready(function() {

            function getData() {
                $('#data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('m-accounts.json.all') }}",
                    order: [
                        [0, 'asc']
                    ],
                    columns: [{
                            width: '8%',
                            data: 'DT_RowIndex',
                            name: 'id',
                            orderable: true,
                            searchable: false
                        },
                        {
                            width: '24%',
                            data: 'bn_type',
                            name: 'bn_type'
                        },
                        {
                            width: '24%',
                            data: 'name',
                            name: 'name'
                        },
                        {
                            width: '24%',
                            data: 'date',
                            name: 'date'
                        },
                        {
                            width: '24%',
                            data: 'unit',
                            name: 'unit'
                        },
                        {
                            width: '24%',
                            data: 'quantity',
                            name: 'quantity'
                        },
                        {
                            width: '24%',
                            data: 'total_amount',
                            name: 'total_amount'
                        },

                        {
                            width: '20%',
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: true
                        },
                    ],
                    dom: 'Blfrtip',
                    buttons: [
                        'csv', 'excel', 'pdf'
                    ],
                    drawCallback: function(settings) {
                        $("[data-toggle=popover]").popover();
                    }
                });

            }

            getData();

            function balance() {
                $.ajax({
                        url: "{{ route('balance') }}",
                        method: 'GET'
                    })
                    .then(function(data) {
                        $('#balance').empty();

                        $('#balance').text(data);
                    });
            }


            $('body').on('click', "#addNew", function() {
                $.get("{{ route('m-accounts.create') }}", function(data) {
                    $('#modalContent').html(data)
                    $("#tableModal").modal('show')
                });
            })

            $('body').on('click', "#submit", function(e) {
                e.preventDefault();
                const name = $("input[name=name]").val();
                const type = $("select[name=type]").val();
                const unit = $("input[name=unit]").val();
                const total_amount = $("input[name=total_amount]").val();
                const quantity = $("input[name=quantity]").val();
                const date = $("input[name=date]").val();

                $.ajax({
                        url: "{{ route('m-accounts.store') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            user_id: '{{ auth()->id() }}',
                            name: name,
                            type: type,
                            unit: unit,
                            total_amount: total_amount,
                            quantity: quantity,
                            date: date
                        },
                        method: 'POST'
                    })
                    .then(function(data) {
                        console.log(data);
                        toastr.success(data)
                        const oTable = $('#data-table').dataTable();
                        oTable.fnDraw(false);
                        $("#tableModal").modal('hide')
                        balance();
                    });
            })

            $('body').on('click', "#submitUpdate", function(e) {
                e.preventDefault();
                const id = $("input[name=id]").val();
                const name = $("input[name=name]").val();
                const type = $("select[name=type]").val();
                const unit = $("input[name=unit]").val();
                const total_amount = $("input[name=total_amount]").val();
                const quantity = $("input[name=quantity]").val();
                const date = $("input[name=date]").val();

                $.ajax({
                        url: "{{ route('m-accounts.update', '#id') }}".replace('#id', id),
                        data: {
                            _token: "{{ csrf_token() }}",
                            user_id: '{{ auth()->id() }}',
                            name: name,
                            type: type,
                            unit: unit,
                            total_amount: total_amount,
                            quantity: quantity,
                            date: date
                        },
                        method: 'PUT'
                    })
                    .then(function(data) {
                        toastr.success(data)
                        const oTable = $('#data-table').dataTable();
                        oTable.fnDraw(false);
                        $("#tableModal").modal('hide');
                        balance();
                    });
            })

            $('body').on('click', "#tableEdit", function() {
                let id = $(this).data('id')

                $.get('{{ route('m-accounts.edit', '#id') }}'.replace('#id', id), function(data) {
                    $('#modalContent').html(data)
                    $("#tableModal").modal('show')
                });
            })

            $('body').on('click', "#deleteData", function() {
                let id = $(this).data('id')

                swal({
                        title: "Are you Want to Delete?",
                        text: "Once Delete, This will be permanently Delete!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                    url: "{{ route('m-accounts.destroy', '#id') }}".replace('#id',
                                        id),
                                    data: {
                                        _token: "{{ csrf_token() }}"
                                    },
                                    method: 'DELETE'
                                })
                                .then(function(data) {

                                    toastr.success(data)
                                    const oTable = $('#data-table').dataTable();
                                    oTable.fnDraw(false);
                                    balance();
                                });
                        } else {
                            swal("Cancelled", "Your Data Is Safe :)", "error");
                        }
                    });
            })

        })
    </script>
@endsection
