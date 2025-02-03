@extends('layouts.dashboard')
@push('css')
    <style>
        #deleteData.disabled {
            pointer-events: none;
            background: #2aa9b9;
        }

    </style>
@endpush
@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive mt-4" style="border-top: 3px solid #2aa9b9;">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="m-t-0 header-title mb-5"><b>{{ __('page.common.customer') }}</b></h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <button class="btn btn-primary waves-effect waves-light m-b-5" id="addNew"> <i
                                class="fa fa-plus-square m-r-5"></i> <span>{{ __('page.customer')[1] }}</span>
                        </button>
                    </div>
                </div>

                <table id="data-table" class="table table-striped table-bordered mt-4" cellspacing="0" width="100%">
                    <thead class="theme-primary text-white">
                        <tr>
                            <th>{{ __('page.common.sl') }}</th>
                            <th>{{ __('page.common.name') }}</th>
                            <th>{{ __('page.common.email') }}</th>
                            <th>{{ __('page.common.mobile') }}</th>
                            <th>{{ __('page.common.farms') }}</th>
                            <th>{{ __('page.common.action') }}</th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Business Modal Start -->
    <div class="modal fade bd-example-modal-xl" id="modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" style="width: 100%;">
            <div class="modal-content" id="modalcontent" style="padding: 0px !important">

            </div>
        </div>
    </div>
    <!-- Business Modal End -->


@endsection
@section('script')
    <script>
        $(function() {
            $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('app-customer.index') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        width: '6%',
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: true,
                        searchable: false
                    },
                    {
                        width: '17%',
                        data: 'name',
                        name: 'name'
                    },
                    {
                        width: '17%',
                        data: 'email',
                        name: 'email'
                    },
                    {
                        width: '17%',
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        width: '17%',
                        data: 'farms',
                        name: 'farms'
                    },
                    {
                        width: '14%',
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: true
                    },
                ],
                drawCallback: function(settings) {
                    $("[data-toggle=popover]").popover();
                }
            });
        });

        $(document).ready(function() {

            $('body').on('click', "#addNew", function() {
                $.get("{{ route('app-customer.create') }}", function(data) {
                    $('#modalcontent').html(data)
                    $("#modal").modal('show')
                });
            })

            $("body").on("submit","#customer-form",function(e) {
                e.preventDefault();
                    $.ajax({
                        url: "{{ route('app-customer.store') }}",
                        data: $(this).serialize(),
                        method: 'POST',
                        dataType: "json",
                    })
                    .then(function(data) {
                        toastr.success(data)
                        const oTable = $('#data-table').dataTable();
                        oTable.fnDraw(false);
                        $("#modal").modal('hide')
                    })
                    .catch(error => {
                        toastr.error(error.responseJSON.error)
                    });
            })

            $('body').on('click', "#editAppCustomer", function() {
                let id = $(this).data('id')
                $.get('{{ route('app-customer.edit', '#id') }}'.replace('#id', id), function(data) {
                    $('#modalcontent').html(data)
                    $("#modal").modal('show')
                });
            })

            $('body').on('submit', "#customer-update", function(e) {
                e.preventDefault();

                $.ajax({
                        url: $(this)[0].action,
                        data: $(this).serialize(),
                        method: 'post'
                    })
                    .then(function(data) {
                        toastr.success(data)
                        const oTable = $('#data-table').dataTable();
                        oTable.fnDraw(false);
                        $("#modal").modal('hide')
                    })
                    .catch(error => {
                        toastr.error(error.responseJSON.error)
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
                                    url: "{{ route('app-customer.destroy', '#id') }}".replace(
                                        '#id', id),
                                    data: {
                                        _token: "{{ csrf_token() }}"
                                    },
                                    method: 'DELETE'
                                })
                                .then(function(data) {
                                    toastr.success(data)
                                    const oTable = $('#data-table').dataTable();
                                    oTable.fnDraw(false);
                                });
                        } else {
                            swal("Cancelled", "Your Data Is Safe :)", "error");
                        }
                    });
            })
        })
    </script>
@endsection
