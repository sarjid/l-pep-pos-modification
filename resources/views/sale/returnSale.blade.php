@extends('layouts.dashboard')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="card-box table-responsive mt-4" style="border-top: 3px solid #2aa9b9;">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="m-t-0 header-title mb-5"><b>Edit Sale</b></h4>
                    </div>
                </div>

                <form action="{{ route('return.sale.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label for="">Customer Name</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="hidden" name="customer_id" value="{{ $sale->contact_id }}">
                                    <input type="text" class="form-control" value="{{ $sale->customer->name }}"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label for="">Invoice No</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="invoice_no" value="{{ date('Y') . $sale->id }}"
                                        class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label for="">Sale Date</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" name="sale_date" value="{{ $sale->sale_date }}"
                                        class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label for="">Return Date</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" name="return_date" value="<?php echo date('Y-m-d'); ?>"
                                        class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">

                            <div class="table-responsive mt-5">
                                <table class="table table-bordered table-hover" id="purchase_entry_table">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%" class="text-center">Product Name </th>
                                            <th style="width: 15%" class="text-center"> Unit Price </th>
                                            <th style="width: 20%" class="text-center">Sell Quantity </th>
                                            <th style="width: 20%" class="text-center">Return Quantity </th>
                                            <th style="width: 15%" class="text-center">Return Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="myTableData">
                                        @foreach ($sale->saleProducts as $product)
                                            <tr id="deleteDBdata{{ $product->id }}">
                                                <td>
                                                    {{ $product->product->product_name }}
                                                    <input type="hidden" value="{{ $product->id }}"
                                                        name="sale_product_id[]">
                                                    <input type="hidden" value="{{ $product->product->id }}"
                                                        name="product_id[{{ $product->id }}]">
                                                    <input type="hidden" value="{{ $product->product_model_id }}"
                                                        name="product_model_id[{{ $product->id }}]">
                                                </td>
                                                <td>
                                                    <span>{{ $product->price }}</span>
                                                    <input type="hidden" value="{{ $product->price }}"
                                                        id="unitPrice{{ $product->id }}"
                                                        name="product_price[{{ $product->id }}]">
                                                </td>
                                                <td>
                                                    <span>{{ $product->qty }}</span>
                                                    <input type="hidden" value="{{ $product->qty }}"
                                                        id="saleQty{{ $product->id }}"
                                                        name="saleQty[{{ $product->id }}]">
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        style="height: 30px;margin-top: 6px;border-radius: 3px;width: 140px;text-align: right; border: 1px solid #b3afaf;"
                                                        class="returnqty" data-sale_id="{{ $product->id }}"
                                                        id="returnQty{{ $product->id }}" value="0"
                                                        name="return_qty[{{ $product->id }}]">
                                                </td>
                                                <td>
                                                    <span id="returnTotal{{ $product->id }}">0</span>
                                                    <input type="hidden" name="returnTotalPice[{{ $product->id }}]"
                                                        class="returnprice returnTotalPice{{ $product->id }}" value="0">
                                                </td>
                                            </tr>
                                        @endforeach

                                    <tbody>

                                    </tbody>
                                    <tfoot>

                                        <tr>
                                            <td colspan="3" style="width: 65%"></td>
                                            <td colspan="" style="width: 20%"><b>Sale Return Amount:</b></td>
                                            <td colspan="" style="width: 15%">
                                                <input type="text"
                                                    style="height: 30px;margin-top: 6px;border-radius: 3px;width: 140px;text-align: right; border: 1px solid #b3afaf;"
                                                    name="sale_return_amount" value="" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="width: 65%"></td>
                                            <td colspan="" style="width: 20%"><b>Pay Return Amount:</b></td>
                                            <td colspan="" style="width: 15%">
                                                <input type="text"
                                                    style="height: 30px;margin-top: 6px;border-radius: 3px;width: 140px;text-align: right; border: 1px solid #b3afaf;"
                                                    name="pay_return_amount" value="0">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="3" style="width: 65%"></td>
                                            <td colspan="" style="width: 20%"><b>Pay By:</b></td>
                                            <td colspan="" style="width: 15%">
                                                <select name="pay_by" id="pay_by"
                                                    style="height: 30px;margin-top: 6px;border-radius: 3px;width: 140px;text-align: right; border: 1px solid #b3afaf;">
                                                    <option value="Cash" selected>Cash</option>
                                                    <option value="Mobile Banking">Mobile Banking</option>
                                                    <option value="Card">Card</option>
                                                    <option value="Bank Account">Bank Account</option>
                                                </select>
                                            </td>
                                        </tr>

                                        <tr id="account_list">

                                        </tr>

                                    </tfoot>

                                </table>

                            </div>

                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </div>
                </form>

            </div>
        </div>
    </div>


@endsection

@section('script')
    <script>
        $('body').on('change', ".returnqty", function() {
            let data_id = $(this).data('sale_id')
            let return_qty = $(this).val()
            let qty = $(`#saleQty${data_id}`).val()
            let unit_price = $(`#unitPrice${data_id}`).val()

            if (qty < return_qty) {
                toastr.error("Return Quantity Must Be Smaller Or Same Than Sell Quantity")
                $(this).val('')
            } else {
                $(`#returnTotal${data_id}`).text(unit_price * return_qty)
                $(`.returnTotalPice${data_id}`).val(unit_price * return_qty)
                total()
            }
        })

        function total() {
            var sum = 0;
            $('.returnprice').each(function() {
                var price = $(this);
                sum += parseInt(price.val());
            });
            $("input[name=sale_return_amount]").val(sum)
        }

        $("body").on('change', "#pay_by", function() {
            let account_type = $(this).val()
            let _token = "{{ csrf_token() }}"
            $.post("{{ route('returnsale.payment.account') }}", {
                _token: _token,
                account_type: account_type
            }, function(data) {
                $("#account_list").html(data)
                $(".select3").select2();
            })

        })
    </script>
@endsection
