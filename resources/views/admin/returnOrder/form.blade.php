@extends('admin.layouts.common')
@section('content')
    @push('css')
        <style>
            input.form-control:read-only {
                background-color: #D3D3D3;
            }

            textarea.form-control:read-only {
                background-color: #D3D3D3;
            }
        </style>
    @endpush

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Return Order/</span>
        Edit
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Return Order </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('return-orders.update', $return_order->id) }}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="question">Order Id</label>
                                <input type="text" name="order_id" class="form-control"
                                    value="{{ isset($return_order) ? $return_order->order_id : '' }}" id="order_id"
                                    readonly />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="merchmake_order_id">MerchMake Order Id</label>
                                <input id="merchmake_order_id" name="merchmake_order_id" class="form-control"
                                    value="{{ isset($return_order) ? $return_order->order->merchmake_order_id : '' }}"
                                    readonly />
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label" for="user_name">User Name</label>
                                <input type="text" name="user_name" class="form-control"
                                    value="{{ isset($return_order) ? $return_order->order->user->name . ' ' . $return_order->order->user->last_name : '' }}"
                                    id="user_name" readonly />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="user_email">User Email</label>
                                <input type="text" name="user_email" class="form-control"
                                    value="{{ isset($return_order) ? $return_order->order->user->email : '' }}"
                                    id="user_email" readonly />
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Return Status</label>
                                <select class="form-select" name="return_status" id="return_status"
                                    aria-label="Default select example">
                                    <option value="Pending" @if (old('return_status', isset($return_order) ? $return_order->return_status : '') == 'Pending') selected @endif> Pending
                                    </option>
                                    <option value="Processing" @if (old('return_status', isset($return_order) ? $return_order->return_status : '') == 'Processing') selected @endif> Processing
                                    </option>
                                    <option value="Refunded" @if (old('return_status', isset($return_order) ? $return_order->return_status : '') == 'Refunded') selected @endif> Refunded
                                    </option>
                                    <option value="Canceled" @if (old('return_status', isset($return_order) ? $return_order->return_status : '') == 'Canceled') selected @endif> Cancel
                                    </option>
                                </select>
                                @error('return_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mt-3" id="cancle_reason_div"
                                @if (isset($return_order) && $return_order->return_status == 'Canceled' || old('return_status') == 'Canceled') style="displa:block" @else style="display: none" @endif>
                                <label class="form-label" for="reason">Cancle Reason</label>
                                <input type="text" name="cancle_reason" class="form-control"
                                    value="{{ isset($return_order) ? $return_order->cancle_reason : '' }}"
                                    id="cancle_reason" />
                                @error('cancle_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mt-3">
                                <label class="form-label" for="reason">Reason</label>
                                <input type="text" name="reason" class="form-control"
                                    value="{{ isset($return_order) ? $return_order->reason : '' }}" id="reason"
                                    @if ($return_order->is_send_to_merchmake == 1) readonly @endif />
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mt-3">
                                <label class="form-label" for="description">Description</label>
                                <textarea name="description" rows="4" class="form-control" id="description"
                                    @if ($return_order->is_send_to_merchmake == 1) readonly @endif>{{ $return_order->description }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if (isset($return_order->returnOrderImages) && $return_order->returnOrderImages->count() > 0)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label" for="question">Images</label>
                                    <div class="review-images-container">
                                        @foreach ($return_order->returnOrderImages as $return_img)
                                            <div class="review-image-wrapper position-relative d-inline-block mr-2">
                                                <div class="image-item mt-3">
                                                    <img src="{{ asset('storage/' . $return_img->image_path) }}"
                                                        alt="Return Order Image" width="200" height="200" />
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary" id="submitBtn" name="action"
                            value="submit">Submit</button>

                        <button type="submit" class="btn btn-secondary ml-5" id="submitSendRequestBtn" name="action"
                            value="submit_and_send">Submit And Send Request In Merchmake</button>
                        <a href="{{ route('return-orders.index') }}" class="btn btn-primary">
                            Back
                        </a>
                    </form>
                    </form>

                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            $('#return_status').on('change', function() {
                var val = $(this).val();
                if (val == "Canceled") {
                    $('#cancle_reason_div').show();
                } else {
                    $('#cancle_reason_div').hide();
                    $('#cancle_reason').val('');
                }
            })
        </script>
    @endpush
@endsection
