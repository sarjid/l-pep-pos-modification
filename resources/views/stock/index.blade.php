@extends('layouts.dashboard')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="card-box table-responsive mt-4" style="border-top: 3px solid #157c34;">
                <h4 class="m-t-0 header-title mb-2"><b>{{ __('page.stock')[0] }}</b></h4>

                <form action="" id="searching">
                    <div class="row justify-content-end">
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="text" name="product_name" value="{{ $search }}" class="form-control"
                                    placeholder="Enter Product Name">
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-rep-plugin">
                    <div class="table-responsive" id="tablefixed">
                        <table id="" class="table table-bordered table-hover mt-0" cellspacing="0">
                            <thead class="theme-primary text-white">
                                <tr>

                                    <th style="width: 5%;">{{ __('page.stock')[1] }}</th>
                                    <th style="width: 15%;">{{ __('page.stock')[2] }}</th>
                                    <th style="width: 10%;">{{ __('page.stock')[4] }}</th>
                                    <th style="width: 10%;">{{ __('page.stock')[5] }}</th>
                                    <th style="width: 10%;">{{ __('page.stock')[6] }}</th>
                                    <th style="width: 10%;">{{ __('page.stock')[7] }}</th>
                                    <th style="width: 10%;">{{ __('page.stock')[8] }}</th>
                                    <th style="width: 10%;">{{ __('page.stock')[9] }}</th>
                                    <th style="width: 10%;">{{ __('page.stock')[10] }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $stock_sale_price = 0;
                                    $stock_purchase_price = 0;
                                @endphp
                                @foreach ($products as $product)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>
                                            <img src="{{ checkImage($product->image) }}"
                                                alt="{{ $product->product_name }}" height="45px"><br>
                                            <strong>{{ $product->product_name }}</strong>
                                        </td>
                                        <td>{{ $product->purchase_price }}</td>
                                        <td>{{ $product->selling_price }}</td>
                                        @php
                                            $total_stock_in = round($product->purchase_qty + $product->sale_return_qty + $product->transferred_in_qty, 2);
                                            $total_stock_out = round($product->sale_qty + $product->purchase_return_qty + $product->transferred_out_qty, 2);
                                            $stock_qty = $total_stock_in - $total_stock_out;

                                            $sale_price = $stock_qty * $product->selling_price;
                                            $purchase_price = $stock_qty * $product->purchase_price;

                                            $stock_sale_price += $sale_price;
                                            $stock_purchase_price += $purchase_price;
                                        @endphp
                                        <td>{{ $total_stock_in }}</td>
                                        <td>{{ $total_stock_out }}</td>
                                        <td>{{ $stock_qty }}</td>
                                        <td>{{ $sale_price }}</td>
                                        <td>{{ $purchase_price }}</td>
                                    </tr>
                                @endforeach

                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6"></td>
                                    <td><b>Total</b></td>
                                    <td><b>{{ $stock_sale_price }} BDT</b></td>
                                    <td><b>{{ $stock_purchase_price }} BDT</b></td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>
                <div>
                    {{ $products->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->


    <!-- Business Modal Start -->
    <div class="modal fade bd-example-modal-xl" id="unitModal" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="width: 100%">
            <div class="modal-content" id="modalcontent" style="width: 100%">

            </div>
        </div>
    </div>
    <!-- Business Modal End -->

@endsection
@section('script')
    <script>
        $(function() {
            $('#tablefixed').responsiveTable({
                addFocusBtn: false
            });
        });
    </script>
@endsection
