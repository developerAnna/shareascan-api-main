@extends('admin.layouts.common')
@section('content')
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">QR Code/</span>
        @if (isset($qrcode))
            Edit
        @else
            Create
        @endif
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">QR Code </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($qrcode) ? route('qrcode.update', $qrcode->id) : route('qrcode.store') }}"
                        method="post">
                        @csrf
                        @if (isset($qrcode))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="hexa_color">Hex Color Value</label>
                            <input type="text" name="hexa_color" class="form-control"
                                value="{{ old('hexa_color', isset($qrcode) ? $qrcode->hexa_color : '') }}" id="hexa_color"
                                placeholder="Enter Hexa color" />
                            @error('hexa_color')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="qr_data">QR data</label>
                            <input type="text" name="qr_data" class="form-control"
                                value="{{ old('qr_data', isset($qrcode) ? $qrcode->qr_data : 'https://shareascan.com') }}"
                                id="qr_data" placeholder="Enter QR Data" />
                            @error('qr_data')
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
    @push('scripts')
        <script>
            $(document).ready(function() {
                // When the input loses focus (blur event), check and add the '#' if missing
                $('#hexa_color').blur(function() {
                    var colorValue = $(this).val().trim();

                    // If the value doesn't start with '#', prepend it
                    if (colorValue && colorValue[0] !== '#') {
                        $(this).val('#' + colorValue);
                    }
                });

                // Optional: you can also handle the keyup event to automatically add the '#' while typing
                $('#hexa_color').keyup(function() {
                    var colorValue = $(this).val().trim();

                    // If the value starts with '#' and the user deletes it, we won't add it back
                    if (colorValue && colorValue[0] !== '#') {
                        $(this).val('#' + colorValue);
                    }
                });
            });
        </script>
    @endpush
@endsection
