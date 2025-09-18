@extends('admin.layouts.common')
@section('content')
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Settings</span></h4>
    <x-success-message />

    <div class="row">
        <!-- Left Side - Vertical Tabs inside Card -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="card-body">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link {{ session('activeTab') == 'google' || session('activeTab') == '' ? 'active' : '' }}"
                            id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab"
                            aria-controls="v-pills-home" aria-selected="true">Google </a>
                        <a class="nav-link {{ session('activeTab') == 'facebook' ? 'active' : '' }}" id="v-pills-email-tab"
                            data-bs-toggle="pill" href="#v-pills-email" role="tab" aria-controls="v-pills-email"
                            aria-selected="false">Facebook</a>
                        <a class="nav-link {{ session('activeTab') == 'apple' ? 'active' : '' }}" id="v-pills-getemail-tab"
                            data-bs-toggle="pill" href="#v-pills-getemail" role="tab" aria-controls="v-pills-getemail"
                            aria-selected="false">Apple</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form Content Inside a Card -->
        <div class="col-md-9">
            <div class="tab-content" id="v-pills-tabContent">
                <!-- Tab 1 Content (store setting) -->
                <div class="tab-pane fade {{ session('activeTab') == 'google' || session('activeTab') == '' ? 'active show' : '' }}"
                    id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Google Settings</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('authentication.setting.update') }}" method="post">
                                @csrf
                                <div class="mb-3">
                                    <input type="hidden" name="google" value="google">

                                    <label class="form-label" for="google_client_id">Client Id</label>
                                    <input type="text" name="google_client_id" class="form-control"
                                        value="{{ get_options('google_client_id') ?? '' }}" id="google_client_id"
                                        placeholder="Enter Store Id" />
                                    @error('google_client_id')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="google_client_secret">Client Secret</label>
                                    <div class="input-group input-group-merge">
                                        <input type="text" name="google_client_secret"
                                            value="{{ get_options('google_client_secret') ?? '' }}"
                                            id="google_client_secret" class="form-control"
                                            placeholder="Enter Access Token" />
                                    </div>
                                    @error('google_client_secret')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="google_redirect_url">Redirect</label>
                                    <div class="input-group input-group-merge">
                                        <input type="text" name="google_redirect_url"
                                            value="{{ get_options('google_redirect_url') ?? '' }}" id="google_redirect_url"
                                            class="form-control" placeholder="Enter redirect url" />
                                    </div>
                                    @error('google_redirect_url')
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

                <!-- Tab 2 Content (email settings) -->
                <div class="tab-pane fade {{ session('activeTab') == 'facebook' ? 'active show' : '' }}" id="v-pills-email"
                    role="tabpanel" aria-labelledby="v-pills-email-tab">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Facebook Setting</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('authentication.setting.update') }}" method="post">
                                @csrf
                                <div class="mb-3">
                                    <input type="hidden" name="facebook" value="facebook">

                                    <label class="form-label" for="facebook_client_id">Client Id</label>
                                    <input type="text" name="facebook_client_id" class="form-control"
                                        value="{{ get_options('facebook_client_id') ?? '' }}" id="facebook_client_id"
                                        placeholder="Enter Store Id" />
                                    @error('facebook_client_id')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="facebook_client_secret">Client Secret</label>
                                    <div class="input-group input-group-merge">
                                        <input type="text" name="facebook_client_secret"
                                            value="{{ get_options('facebook_client_secret') ?? '' }}"
                                            id="facebook_client_secret" class="form-control"
                                            placeholder="Enter Access Token" />
                                    </div>
                                    @error('facebook_client_secret')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="facebook_redirect_url">Redirect</label>
                                    <div class="input-group input-group-merge">
                                        <input type="text" name="facebook_redirect_url"
                                            value="{{ get_options('facebook_redirect_url') ?? '' }}"
                                            id="facebook_redirect_url" class="form-control"
                                            placeholder="Enter redirect url" />
                                    </div>
                                    @error('facebook_redirect_url')
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

                <!-- Tab 2 Content (get emails setting) -->
                <div class="tab-pane fade {{ session('activeTab') == 'apple' ? 'active show' : '' }}"
                    id="v-pills-getemail" role="tabpanel" aria-labelledby="v-pills-getemail-tab">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Apple Setting</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('authentication.setting.update') }}" method="post">
                                @csrf
                                <div class="mb-3">
                                    <input type="hidden" name="apple" value="apple">

                                    <label class="form-label" for="google_client_id">Client Id</label>
                                    <input type="text" name="apple_client_id" class="form-control"
                                        value="{{ get_options('apple_client_id') ?? '' }}" id="apple_client_id"
                                        placeholder="Enter Store Id" />
                                    @error('apple_client_id')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="apple_client_secret">Client Secret</label>
                                    <div class="input-group input-group-merge">
                                        <input type="text" name="apple_client_secret"
                                            value="{{ get_options('apple_client_secret') ?? '' }}"
                                            id="apple_client_secret" class="form-control"
                                            placeholder="Enter Access Token" />
                                    </div>
                                    @error('apple_client_secret')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="apple_redirect_url">Redirect</label>
                                    <div class="input-group input-group-merge">
                                        <input type="text" name="apple_redirect_url"
                                            value="{{ get_options('apple_redirect_url') ?? '' }}" id="apple_redirect_url"
                                            class="form-control" placeholder="Enter redirect url" />
                                    </div>
                                    @error('apple_redirect_url')
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
        </div>
    </div>
@endsection
