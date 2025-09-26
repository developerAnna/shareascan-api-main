@extends('admin.layouts.common')
@section('content')
    @push('css')
        <style>
            /* .btn-secondary {
                padding: 10px 20px;
                font-size: 16px;
                border-radius: 6px;
                text-decoration: none;
                cursor: pointer;
            }

            .btn-secondary {
                color: #fff;
                background-color: #6c757d;
            }

            .btn-secondary:hover {
                background-color: #5a6268;
            }

            .btn-primary {
                color: #fff;
                background-color: #007bff;
            }

            .btn-primary:hover {
                background-color: #0056b3;
            } */
        </style>
    @endpush
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">QR Code/</span>
        Show
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">QR Code</h5>
                </div>
                <div class="card-body">

                    <!-- QR Image Section -->
                    <div class="mb-4">
                        <label class="form-label" for="hexa_color">QR Image</label>
                        <!-- QR Code Image -->
                        <div class="qr-image-container mb-3">
                            <img src="{{ asset('storage/' . $qrcode->qr_image_path) }}" width="200px" height="200px"
                                alt="QR Code" class="img-fluid rounded shadow">
                        </div>
                        <!-- Download Link -->
                        <div>
                            <a href="{{ route('downloadQrImage', $qrcode->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-download"></i> Download QR Image
                            </a>
                        </div>
                    </div>

                    <!-- QR Data Section -->
                    <div class="mb-4">
                        <label class="form-label" for="qr_data">QR Data</label>
                        <!-- Display QR Data -->
                        <div class="qr-data-container">
                            <h5 class="text-dark font-weight-bold">{{ $qrcode->qr_data }}</h5>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mt-3">
                        <a href="{{ route('qrcode.index') }}" class="btn btn-secondary"
                            style="display: inline-block; margin-right: 10px;">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
    @endpush
@endsection
