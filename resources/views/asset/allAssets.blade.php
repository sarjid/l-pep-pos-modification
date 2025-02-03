@extends('layouts.dashboard')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive mt-4" style="border-top: 3px solid #2aa9b9;">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="m-t-0 header-title mb-5"><b>{{ __('page.asset')[0] }}</b></h4>
                    </div>
                    <div class="col-md-6 text-right">
                        @if (permission('ex2'))
                            <button class="btn btn-primary waves-effect waves-light m-b-5" id="addNew"> <i
                                    class="fa fa-plus-square m-r-5"></i> <span>{{ __('page.asset')[1] }}</span> </button>
                        @endif
                    </div>
                </div>

                <table id="data-table" class="table table-striped table-bordered mt-4" cellspacing="0" width="100%">
                    <thead class="theme-primary text-white">
                        <tr>
                            <th> {{ __('page.asset')[2] }} </th>
                            <th> {{ __('page.asset')[3] }} </th>
                            <th> {{ __('page.asset')[4] }} </th>
                            <th> {{ __('page.asset')[5] }} </th>
                            <th> {{ __('page.asset')[6] }} </th>
                            <th>{{ __('page.asset')[7] }}</th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Business Modal Start -->
    <div class="modal fade bd-example-modal-xl" id="unitModal" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width: 100%">
            <div class="modal-content" id="modalcontent">

            </div>
        </div>
    </div>
    <!-- Business Modal End -->


@endsection
@section('script')
    <script>
        $(document).ready(function() {

            function getData() {
                $('#data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('asset.all') }}",
                    columns: [{
                            "data": 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'asset_category',
                            name: 'asset_category'
                        },
                        {
                            data: 'asset_date',
                            name: 'asset_date'
                        },
                        {
                            data: 'amount',
                            name: 'amount'
                        },
                        {
                            data: 'note',
                            name: 'note'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: true
                        },
                    ]
                });
            }
            getData();

            $('body').on('click', "#addNew", function() {
                $.get("{{ route('asset.create') }}", function(data) {
                    $('#modalcontent').html(data)
                    $("#unitModal").modal('show')
                });
            })

            $('body').on('click', "#submit", function(e) {
                e.preventDefault();
                let expanse_date = $("input[name=expanse_date]").val();
                let amount = $("input[name=amount]").val();
                let note = $("textarea[name=note]").val();
                let expense_type_id = $("select[name=expense_type_id]").val();
                let payment_type = $("select[name=payment_type]").val();
                let account_id = $("select[name=account_id]").val();
                let _token = "{{ csrf_token() }}"
                $.post("{{ route('asset.store') }}", {
                    _token: _token,
                    pay_by: payment_type,
                    expanse_date: expanse_date,
                    amount: amount,
                    expense_type_id: expense_type_id,
                    note: note,
                    account_id: account_id
                }, function(data) {
                    toastr.success(data)
                    var oTable = $('#data-table').dataTable();
                    oTable.fnDraw(false);
                    $("#unitModal").modal('hide')
                })
            })

            $('body').on('click', "#submitUpdate", function(e) {
                e.preventDefault();
                let id = $("input[name=id]").val();
                let expanse_date = $("input[name=expanse_date]").val();
                let amount = $("input[name=amount]").val();
                let note = $("textarea[name=note]").val();
                let expense_type_id = $("select[name=expense_type_id]").val();
                let payment_type = $("select[name=payment_type]").val();
                let account_id = $("select[name=account_id]").val();
                let _token = "{{ csrf_token() }}"
                $.post("{{ route('asset.update') }}", {
                    _token: _token,
                    id: id,
                    pay_by: payment_type,
                    expanse_date: expanse_date,
                    amount: amount,
                    expense_type_id: expense_type_id,
                    note: note,
                    account_id: account_id
                }, function(data) {
                    toastr.success(data)
                    var oTable = $('#data-table').dataTable();
                    oTable.fnDraw(false);
                    $("#unitModal").modal('hide')
                })
            })

            $('body').on('click', "#unitEdit", function() {
                let id = $(this).data('id')

                $.get(`/asset/${id}/edit`, function(data) {
                    $('#modalcontent').html(data)
                    $("#unitModal").modal('show')
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
                            let _token = "{{ csrf_token() }}"
                            $.get(`/asset/${id}/delete`, function(data) {
                                toastr.success(data)
                                var oTable = $('#data-table').dataTable();
                                oTable.fnDraw(false);
                            })
                        } else {
                            swal("Cancelled", "Your Data Is Safe :)", "error");
                        }
                    });
            })
        })

        $("body").on('change', "#pay_by", function() {
            // purchase.payment.account
            let account_type = $(this).val()
            let _token = "{{ csrf_token() }}"
            $.post("{{ route('asset.payment.account') }}", {
                _token: _token,
                account_type: account_type
            }, function(data) {
                $("#account_info").html(data)
                $(".select3").select2();
            })

        })
    </script>
@endsection
