@extends('layouts.dashboard')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="mt-4">
                <h4>{{ __('View Income Details') }}</h4>
            </div>
            <div class="card-box">
                <div>
                    <a href="{{ route('income.index') }}" class="btn btn-success waves-effect waves-light m-b-5">
                        <i class="fa fa-arrow-left m-r-5"></i>
                        <span>{{ __('Back') }}</span>
                    </a>

                    <div class="form-group">
                        <h4 class="text-center text-primary">{{ $data->income_date }}</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('#') }}</th>
                                    <th>{{ __('Employee Name') }}</th>
                                    @foreach ($data->details->first()->income_types ?? [] as $inctypeName => $value)
                                        <th>{{ $inctypeName }}</th>
                                    @endforeach
                                    <th>{{ __('Note') }}</th>
                                    <th>{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data->details as $detail)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $detail->user->employee_name }} - <small>{{ $detail->user->name }}</small></td>
                                        @foreach ($detail->income_types as $index => $incmtype)
                                            <td id="{{ $index }}">{{ $incmtype }} </td>
                                        @endforeach

                                        <td>{{ $detail->note }}</td>
                                        <td>{{ $detail->total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%">{{ __('Not Found...!') }}</td>
                                    </tr>
                                @endforelse

                                <tr>
                                    <td></td>
                                    <td>
                                        <strong>Total</strong>
                                    </td>
                                    @foreach ($data->details->first()->income_types ?? [] as $inctypeName => $value)
                                    <td></td>
                                    @endforeach
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="{{ $data->details->first() ? count($data->details->first()->income_types) + 3 : 5 }}">
                                        <strong>{{ __('Total') }} =</strong>
                                    </td>
                                    <td>
                                        <strong>
                                            {{ $data->details->sum('total') }}
                                        </strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection


@push('css')
    <style>
        .table th {
            font-size: 12px !important;
        }
    </style>
@endpush


@push('js')

@endpush

