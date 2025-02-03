@extends('layouts.dashboard')
@section('title', '| User Edit')
@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive mt-4" style="border-top: 3px solid #2aa9b9;">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="m-t-0 header-title mb-5"><b>User Update</b></h4>
                    </div>

                </div>

                <a href="{{ route('user.index') }}" class="btn btn-primary waves-effect waves-light m-b-5">
                    <i class="fa fa-arrow-left m-r-5"></i>
                    <span>{{ __('Back') }}</span>
                </a>

                <form action="{{ route('user.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $user->id }}">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="">User Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ $user->name }}"
                                placeholder="User Name" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for=""> {{ __('Employee Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="employee_name" placeholder="Employee name"
                                required value="{{ $user->employee_name }}">
                            @error('employee_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for=""> {{ __('page.phone') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mobile" value="{{ $user->mobile }}"
                                placeholder="Mobile Number" required>
                            @error('mobile')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="">User Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="{{ $user->email }}"
                                placeholder="User Email" required>
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for=""> Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" placeholder="User Password">
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for=""> {{ __('page.usercreate')[6] }} <span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password_confirmation"
                                placeholder="User Confirm Password">
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-info">Update</button>
                    </div>

                </form>


            </div>
        </div>
    </div>

@endsection
