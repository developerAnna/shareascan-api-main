@extends('admin.layouts.common')
@section('content')
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">My Profile/</span> Edit</h4>
    <x-success-message />

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.update.profile') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}"
                                id="basic-default-fullname" placeholder="Enter Name" />
                            @error('name')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="basic-default-email">Email</label>
                            <div class="input-group input-group-merge">
                                <input type="email" name="email" value="{{ $user->email }}" id="basic-default-email"
                                    class="form-control" placeholder="Enter Email" aria-label="john.doe"
                                    aria-describedby="basic-default-email2" />
                            </div>
                            @error('email')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.update.profile') }}" method="post">
                        @csrf
                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="password">Current Password</label>
                            </div>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="new_password">New Password</label>
                            </div>
                            <div class="input-group input-group-merge">
                                <input type="password" id="new_password" class="form-control" name="new_password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="new_password" />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                            @error('new_password')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="confirm_password">Confirm Password</label>
                            </div>
                            <div class="input-group input-group-merge">
                                <input type="password" id="confirm_password" class="form-control" name="confirm_password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="confirm_password" />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                            @error('confirm_password')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
