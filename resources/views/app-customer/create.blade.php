<div class="card">
    <div class="card-header">
        <h5 style="margin-bottom: 20px;">{{ __('page.customer')[1] }}</h5>
    </div>
    <div class="card-body">
        <form id="customer-form" method="POST">
            @csrf
            <div class="form-group" style="display:{{ isRole(ROLE_AGENT) ? 'none' : '' }}">
                <label for="">{{ __('page.common.agent') }} <span class="text-danger">*</span></label>
                <select name="agent_id" id="agent_id" class="form-control" required>
                    <option value="">Select</option>
                    @foreach (getCachedAgents() as $agent)
                        <option value="{{ $agent->id }}" {{ auth()->id() == $agent->id ? "selected" : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>

                @error('agent_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="">{{ __('page.common.name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" placeholder="Enter Name" required>

                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="">{{ __('page.common.mobile') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="mobile" placeholder="Enter Mobile No." required>

                @error('mobile')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- <div class="form-group">
                <label for="">{{ __('page.common.email') }}</label>
                <input type="email" class="form-control" name="email" placeholder="Enter Email Address">

                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="">{{ __('page.common.password') }}</label>
                <input type="password" class="form-control" name="password" placeholder="Enter Password">

                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div> --}}

            <div class="text-right">
                <button class="btn btn-success" type="submit" id="submit">{{ __('page.unit')[6] }}</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('page.unit')[7] }}</button>
            </div>

        </form>
    </div>
</div>
