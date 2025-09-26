@extends('admin.layouts.common')
@section('content')
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    @endpush
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
                        <a class="nav-link {{ session('activeTab') == 'shop_page_products_settings' || session('activeTab') == '' ? 'active' : '' }}"
                            id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab"
                            aria-controls="v-pills-home" aria-selected="true">Shop Page & Hot Products Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form Content Inside a Card -->
        <div class="col-md-9">
            <div class="tab-content" id="v-pills-tabContent">
                <!-- Tab 1 Content (store setting) -->
                <div class="tab-pane fade {{ session('activeTab') == 'shop_page_products_settings' || session('activeTab') == '' ? 'active show' : '' }}"
                    id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Shop Page & Hot Products Settings</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('shoppage.setting.update') }}" method="post">
                                @csrf
                                <div class="mb-3">
                                    <input type="hidden" name="shop_page_products_settings"
                                        value="shop_page_products_settings">
                                    <label class="form-label" for="best_sellers_products">Best Sellers Products</label>
                                    <select name="best_sellers_products[]" class="form-control" id="best_sellers_products"
                                        multiple>
                                        @if (isset($merchmake_products) && !empty($merchmake_products))
                                            @foreach ($merchmake_products as $key => $merchmake_product)
                                                <option
                                                    value="{{ $merchmake_product['id'] }}start_title{{ $merchmake_product['title'] }}"
                                                    @if (isset($best_seller_products) &&
                                                            !empty($best_seller_products) &&
                                                            in_array($merchmake_product['id'], $best_seller_products)) selected @endif>
                                                    {{ $merchmake_product['title'] }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('best_sellers_products')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="new_arrival_products">New Arrivals Products</label>
                                    <select name="new_arrival_products[]" class="form-control" id="new_arrival_products"
                                        multiple>
                                        @if (isset($merchmake_products) && !empty($merchmake_products))
                                            @foreach ($merchmake_products as $key => $merchmake_product)
                                                <option
                                                    value="{{ $merchmake_product['id'] }}start_title{{ $merchmake_product['title'] }}"
                                                    @if (isset($new_arrival_products) &&
                                                            !empty($new_arrival_products) &&
                                                            in_array($merchmake_product['id'], $new_arrival_products)) selected @endif>
                                                    {{ $merchmake_product['title'] }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('new_arrival_products')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="categories">Categories</label>
                                    <select name="categories[]" class="form-control" id="categories" multiple>
                                        @if (isset($merchmake_categories) && !empty($merchmake_categories))
                                            @foreach ($merchmake_categories as $key => $merchmake_category)
                                                <option
                                                    value="{{ $merchmake_category['id'] }}start_title{{ $merchmake_category['title'] }}"
                                                    @if (isset($db_categories) && !empty($db_categories) && in_array($merchmake_category['id'], $db_categories)) selected @endif>
                                                    {{ $merchmake_category['title'] }}</option>

                                                <!-- Check if the category has subcategories -->
                                                @if (isset($merchmake_category['sub_categories']) && !empty($merchmake_category['sub_categories']))
                                                    @foreach ($merchmake_category['sub_categories'] as $sub_category)
                                                        <!-- Subcategory option -->
                                                        <option
                                                            value="{{ $sub_category['id'] }}start_title{{ $merchmake_category['title'] }} > {{ $sub_category['title'] }}"
                                                            @if (isset($db_categories) && !empty($db_categories) && in_array($sub_category['id'], $db_categories))
                                                            selected
                                                    @endif>
                                                    {{ $merchmake_category['title'] }} >
                                                    {{ $sub_category['title'] }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        @endforeach
                                        @endif
                                    </select>
                                    @error('categories')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <input type="hidden" name="hot_items_setting" value="hot_items_setting">
                                    <label class="form-label" for="hot_products">Hot Products</label>
                                    <select name="hot_products[]" class="form-control" id="hot_products" multiple>
                                        @if (isset($merchmake_products) && !empty($merchmake_products))
                                            @foreach ($merchmake_products as $key => $merchmake_product)
                                                <option
                                                    value="{{ $merchmake_product['id'] }}start_title{{ $merchmake_product['title'] }}"
                                                    @if (isset($hot_products) && !empty($hot_products) && in_array($merchmake_product['id'], $hot_products)) selected @endif>
                                                    {{ $merchmake_product['title'] }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Tab 2 Content (Hot Items setting) -->
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#best_sellers_products').select2({
                    placeholder: "Select Products",
                    allowClear: true
                });

                $('#new_arrival_products').select2({
                    placeholder: "Select Products",
                    allowClear: true
                });

                $('#categories').select2({
                    placeholder: "Select Category",
                    allowClear: true
                });

                $('#hot_products').select2({
                    placeholder: "Select Products",
                    allowClear: true
                });
            });
        </script>
    @endpush
@endsection
