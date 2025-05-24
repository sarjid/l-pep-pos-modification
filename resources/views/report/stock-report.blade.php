@extends('layouts.dashboard')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card-box table-responsive mt-4" style="border-top: 3px solid #157c34;">
                <h4 class="m-t-0 header-title mb-2 text-center"><b>{{ __('Stock Report') }}</b></h4>
                <div class="no-print">
                    <button onclick="window.print()" class="btn btn-primary mb-3">🖨 Print</button>
                    <a href="{{ route('report.stock.export') }}" class="btn btn-success mb-3">⬇ Export Excel</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('page.stock')[1] }}</th>
                                <th>{{ __('page.stock')[2] }}</th>
                                <th>{{ __('page.stock')[4] }}</th>
                                <th>{{ __('page.stock')[5] }}</th>
                                <th>{{ __('page.stock')[6] }}</th>
                                <th>{{ __('page.stock')[7] }}</th>
                                <th>{{ __('page.stock')[8] }}</th>
                                <th>{{ __('page.stock')[9] }}</th>
                                <th>{{ __('page.stock')[10] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $stock_sale_price = 0;
                                $stock_purchase_price = 0;
                            @endphp
                            @foreach ($products as $product)
                                @php
                                    $total_stock_in = round(
                                        $product->purchase_qty +
                                            $product->sale_return_qty +
                                            $product->transferred_in_qty,
                                        2,
                                    );
                                    $total_stock_out = round(
                                        $product->sale_qty +
                                            $product->purchase_return_qty +
                                            $product->transferred_out_qty,
                                        2,
                                    );
                                    $stock_qty = $total_stock_in - $total_stock_out;
                                    $sale_price = $stock_qty * $product->selling_price;
                                    $purchase_price = $stock_qty * $product->purchase_price;
                                    $stock_sale_price += $sale_price;
                                    $stock_purchase_price += $purchase_price;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->purchase_price }}</td>
                                    <td>{{ $product->selling_price }}</td>
                                    <td>{{ $total_stock_in }}</td>
                                    <td>{{ $total_stock_out }}</td>
                                    <td>{{ $stock_qty }}</td>
                                    <td>{{ $sale_price }}</td>
                                    <td>{{ $purchase_price }}</td>
                                </tr>
                            @endforeach

                            <tr style="font-weight: bold; background-color: #f0f0f0;">
                                <td colspan="7">{{ __('Total') }}</td>
                                <td>{{ $stock_sale_price }}</td>
                                <td>{{ $stock_purchase_price }}</td>
                            </tr>
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection


@push('css')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            .card-box {
                margin-bottom: 0 !important;
                padding-bottom: 0 !important;
            }
        }
    </style>
@endpush
