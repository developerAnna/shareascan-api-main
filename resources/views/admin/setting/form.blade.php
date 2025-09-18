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
                        <a class="nav-link {{ session('activeTab') == 'store_settings' || session('activeTab') == '' ? 'active' : '' }}"
                            id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab"
                            aria-controls="v-pills-home" aria-selected="true">Store Settings</a>
                        <a class="nav-link {{ session('activeTab') == 'email_settings' ? 'active' : '' }}"
                            id="v-pills-email-tab" data-bs-toggle="pill" href="#v-pills-email" role="tab"
                            aria-controls="v-pills-email" aria-selected="false">Email Settings</a>
                        <a class="nav-link {{ session('activeTab') == 'getemail_settings' ? 'active' : '' }}"
                            id="v-pills-getemail-tab" data-bs-toggle="pill" href="#v-pills-getemail" role="tab"
                            aria-controls="v-pills-getemail" aria-selected="false">Get Emails Setting</a>
                        <a class="nav-link {{ session('activeTab') == 'payment_settings' ? 'active' : '' }}"
                            id="v-pills-payment-tab" data-bs-toggle="pill" href="#v-pills-payment" role="tab"
                            aria-controls="v-pills-payment" aria-selected="false">Payment Setting</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form Content Inside a Card -->
        <div class="col-md-9">
            <div class="tab-content" id="v-pills-tabContent">
                <!-- Tab 1 Content (store setting) -->
                <div class="tab-pane fade {{ session('activeTab') == 'store_settings' || session('activeTab') == '' ? 'active show' : '' }}"
                    id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Store Settings</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('store.setting.update') }}" method="post">
                                @csrf
                                <div class="mb-3">
                                    <input type="hidden" name="store_settings" value="store_settings">

                                    <label class="form-label" for="store_id">Store Id</label>
                                    <input type="text" name="merchmake_store_id" class="form-control"
                                        value="{{ get_options('merchmake_store_id') ?? '' }}" id="store_id"
                                        placeholder="Enter Store Id" />
                                    @error('merchmake_store_id')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="access_token">Access Token</label>
                                    <div class="input-group input-group-merge">
                                        <input type="text" name="merchmake_access_token"
                                            value="{{ get_options('merchmake_access_token') ?? '' }}" id="access_token"
                                            class="form-control" placeholder="Enter Access Token" />
                                    </div>
                                    @error('merchmake_access_token')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="merchmake_base_url">Base URL</label>
                                    <input type="text" name="merchmake_base_url" class="form-control"
                                        value="{{ get_options('merchmake_base_url') ?? '' }}" id="merchmake_base_url"
                                        placeholder="Enter Store Id" />
                                    @error('merchmake_base_url')
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
                <div class="tab-pane fade {{ session('activeTab') == 'email_settings' ? 'active show' : '' }}"
                    id="v-pills-email" role="tabpanel" aria-labelledby="v-pills-email-tab">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Email Setting</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('store.setting.update') }}" method="post">
                                @csrf
                                <div class="row">
                                    <input type="hidden" name="email_settings" value="email_settings">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="control-label">Mail Type</label>
                                            <select class="form-control auto-select" data-selected="" name="mail_type"
                                                id="mail_type" required>
                                                <option value="smtp"
                                                    {{ get_options('mail_type') == 'smtp' ? 'selected' : '' }}>SMTP
                                                </option>
                                            </select>
                                            @error('mail_type')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="control-label">From Email</label>
                                            <input type="text" class="form-control" name="from_email"
                                                value="{{ get_options('from_email') ?? '' }}" required>
                                            @error('from_email')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="control-label">From Name</label>
                                            <input type="text" class="form-control" name="from_name"
                                                value="{{ get_options('from_name') ?? '' }}" required>
                                            @error('from_name')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="control-label">SMTP Host</label>
                                            <input type="text" class="form-control smtp" name="smtp_host"
                                                value="{{ get_options('smtp_host') ?? '' }}">
                                            @error('smtp_host')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="control-label">SMTP Port</label>
                                            <input type="text" class="form-control smtp" name="smtp_port"
                                                value="{{ get_options('smtp_port') ?? '' }}">
                                            @error('smtp_port')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="control-label">SMTP Username</label>
                                            <input type="text" class="form-control smtp" autocomplete="off"
                                                name="smtp_username" value="{{ get_options('smtp_username') ?? '' }}">
                                            @error('smtp_username')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="control-label">SMTP Password</label>
                                            <input type="password" class="form-control smtp" autocomplete="off"
                                                name="smtp_password" value="{{ get_options('smtp_password') ?? '' }}">
                                            @error('smtp_password')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="control-label">SMTP Encryption</label>
                                            <select class="form-control smtp auto-select" data-selected="}"
                                                name="smtp_encryption">
                                                <option value="none"
                                                    {{ get_options('smtp_encryption') == 'none' ? 'selected' : '' }}>None
                                                </option>
                                                <option value="ssl"
                                                    {{ get_options('smtp_encryption') == 'ssl' ? 'selected' : '' }}>SSL
                                                </option>
                                                <option value="tls"
                                                    {{ get_options('smtp_encryption') == 'tls' ? 'selected' : '' }}>TLS
                                                </option>
                                            </select>
                                            @error('smtp_encryption')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tab 3 Content (get emails setting) -->
                <div class="tab-pane fade {{ session('activeTab') == 'getemail_settings' ? 'active show' : '' }}"
                    id="v-pills-getemail" role="tabpanel" aria-labelledby="v-pills-getemail-tab">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Get Email Setting</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('store.setting.update') }}" method="post">
                                @csrf
                                <div class="row">
                                    <input type="hidden" name="getemail_settings" value="getemail_settings">

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="control-label">Get email On</label>
                                            <input type="email" class="form-control" name="get_contact_us_email_on"
                                                value="{{ get_options('get_contact_us_email_on') ?? '' }}" required>
                                            @error('get_contact_us_email_on')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="control-label">MerchMake Support Email</label>
                                            <input type="email" class="form-control" name="merchmake_support_email"
                                                value="{{ get_options('merchmake_support_email') ?? '' }}" required>
                                            @error('merchmake_support_email')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tab 4 Content (payment setting) -->

                <div class="tab-pane fade {{ session('activeTab') == 'payment_settings' ? 'active show' : '' }}"
                    id="v-pills-payment" role="tabpanel" aria-labelledby="v-pills-payment-tab">
                    <div class="accordion" id="paymentSettingsAccordion">
                        <!-- Stripe Accordion Item -->
                        <div class="accordion-item mb-3"> <!-- Added margin-bottom for spacing -->
                            <h2 class="accordion-header" id="headingStripe">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseStripe" aria-expanded="false"
                                    aria-controls="collapseStripe">
                                    Stripe Payment
                                </button>
                            </h2>
                            <div id="collapseStripe" class="accordion-collapse collapse" aria-labelledby="headingStripe"
                                data-bs-parent="#paymentSettingsAccordion">
                                <div class="accordion-body">
                                    <form action="{{ route('store.setting.update') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="payment_settings" value="payment_settings">

                                        <!-- Stripe Client ID -->
                                        <div class="mb-3">
                                            <label class="form-label" for="stripe_client_id">Client Id</label>
                                            <input type="text" name="stripe_client_id" class="form-control"
                                                value="{{ get_options('stripe_client_id') ?? '' }}" id="stripe_client_id"
                                                placeholder="Enter Stripe Client Id" />
                                            @error('stripe_client_id')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>

                                        <!-- Stripe Client Secret -->
                                        <div class="mb-3">
                                            <label class="form-label" for="stripe_client_secret">Client Secret</label>
                                            <div class="input-group input-group-merge">
                                                <input type="text" name="stripe_client_secret"
                                                    value="{{ get_options('stripe_client_secret') ?? '' }}"
                                                    id="stripe_client_secret" class="form-control"
                                                    placeholder="Enter Stripe Client Secret" />
                                            </div>
                                            @error('stripe_client_secret')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>

                                        <!-- Submit Button for Stripe -->
                                        <button type="submit" class="btn btn-primary">Save Stripe Settings</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- PayPal Accordion Item -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPaypal">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapsePaypal" aria-expanded="false"
                                    aria-controls="collapsePaypal">
                                    PayPal Payment
                                </button>
                            </h2>
                            <div id="collapsePaypal" class="accordion-collapse collapse" aria-labelledby="headingPaypal"
                                data-bs-parent="#paymentSettingsAccordion">
                                <div class="accordion-body">
                                    <form action="{{ route('store.setting.update') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="payment_settings" value="payment_settings">

                                        <!-- PayPal Client ID -->
                                        <div class="mb-3">
                                            <label class="form-label" for="paypal_client_id">Client Id</label>
                                            <input type="text" name="paypal_client_id" class="form-control"
                                                value="{{ get_options('paypal_client_id') ?? '' }}" id="paypal_client_id"
                                                placeholder="Enter Paypal Client Id" />
                                            @error('paypal_client_id')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>

                                        <!-- PayPal Client Secret -->
                                        <div class="mb-3">
                                            <label class="form-label" for="paypal_client_secret">Client Secret</label>
                                            <div class="input-group input-group-merge">
                                                <input type="text" name="paypal_client_secret"
                                                    value="{{ get_options('paypal_client_secret') ?? '' }}"
                                                    id="paypal_client_secret" class="form-control"
                                                    placeholder="Enter Paypal Client Secret" />
                                            </div>
                                            @error('paypal_client_secret')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>

                                        <!-- PayPal Mode -->
                                        <div class="mb-3">
                                            <label class="form-label" for="paypal_mode">Mode</label>
                                            <select name="paypal_mode" id="paypal_mode" class="form-select">
                                                <option value="sandbox" {{ get_options('paypal_mode') == 'sandbox' ? 'selected' : '' }}>sandbox</option>
                                                <option value="live" {{ get_options('paypal_mode') == 'live' ? 'selected' : '' }}>live</option>
                                            </select>
                                            @error('paypal_mode')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>

                                        <!-- PayPal Webhook ID -->
                                        <div class="mb-3">
                                            <label class="form-label" for="paypal_webhook_id">Webhook Id</label>
                                            <div class="input-group input-group-merge">
                                                <input type="text" name="paypal_webhook_id"
                                                    value="{{ get_options('paypal_webhook_id') ?? '' }}"
                                                    id="paypal_webhook_id" class="form-control"
                                                    placeholder="Enter Paypal Webhook Id" />
                                            </div>
                                            @error('paypal_webhook_id')
                                                <div class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </div>
                                            @enderror
                                        </div>

                                        <!-- Submit Button for PayPal -->
                                        <button type="submit" class="btn btn-primary">Save PayPal Settings</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
