<div class="modal-body">

    <div class="row invoice-info">
        <div class="col-sm-8 invoice-col">
            <b>Customer:</b> {{ $sale->customer->name }}<br>
            <b>Mobile:</b> {{ $sale->customer->mobile }}<br>
            <b>Email:</b> {{ $sale->customer->email }}
        </div>

        <div class="col-sm-4 invoice-col">
            <b>Invoice No:</b> #{{ $sale->invoice_no }}<br>
            <b>Date:</b> {{ $sale->sale_date }}<br>
            <b>{{ __('page.user_log.1') }}</b>: {{ $sale->created_at->format('Y-m-d H:i:s') }}
        </div>
    </div>

    <hr><br>

    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <div class="table-responsive text-center">
                <table class="table text-center bg-secondary text-white">
                    <thead class="">
                        <tr style=" background: #2dce89;">
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->saleProducts as $product)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $product->product->product_name }}</td>
                                <td>{{ $product->qty }}</td>
                                <td>{{ $product->price }}</td>
                                <td>{{ $product->total_price }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">

        </div>
        <div class="col-md-3">
            Total Points:
            <span class="display_currency pull-right" data-currency_symbol="true">
                ৳ {{ $sale->total_amount }}
            </span>
        </div>
    </div>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-danger no-print" data-dismiss="modal">Close</button>
</div>
