@extends('admin.layouts.common')
@section('content')
    <style>
        input.form-control:read-only {
            background-color: #D3D3D3;
        }

        textarea.form-control:read-only {
            background-color: #D3D3D3;
        }

        .review-images-container {
            display: flex;
            flex-wrap: wrap;
        }

        .review-image-wrapper {
            position: relative;
            margin-right: 30px;
            margin-bottom: 30px;
            overflow: visible;
            /* Ensures the close button isn't cut off */
        }

        .review-image-wrapper img {
            width: 100px;
            /* Set fixed width */
            height: 100px;
            /* Set fixed height */
            object-fit: cover;
            /* Ensure images are not stretched/distorted */
            display: block;
        }

        .close-btn {
            position: absolute;
            top: -10px;
            /* Move the button slightly outside the image */
            right: -10px;
            /* Move the button slightly outside the image */
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            font-size: 16px;
            padding: 5px;
            cursor: pointer;
            border-radius: 50%;
            /* Circular button */
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            background-color: red;
            /* Red background on hover */
        }

        .img-thumbnail {
            display: inline-block;
            border: 1px solid #ddd;
            padding: 4px;
        }
    </style>

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Review/</span>
        @if (isset($review))
            Review
        @else
            Create
        @endif
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Review </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('review.update', $review->id) }}" method="post">
                        @csrf
                        @if (isset($review))
                            @method('PUT')
                        @endif
                        <div class="row mb-3">
                            <div class="col-md-6"> <label class="form-label" for="question">Product Id</label>
                                <input type="text" name="product_id" class="form-control"
                                    value="{{ isset($review) ? $review->product_id : '' }}" id="product_id" readonly />
                            </div>
                            <div class="col-md-6"> <label class="form-label" for="product_title">Product Title</label>
                                <input id="product_title" name="product_title" class="form-control"
                                    value="{{ isset($review) ? $review->product_title : '' }}" readonly />
                            </div>

                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4"> <label class="form-label" for="question">User Name</label>
                                <input type="text" name="user_id" class="form-control"
                                    value="{{ isset($review) ? $review->user->name . ' ' . $review->user->last_name : '' }}"
                                    id="user_id" readonly />
                            </div>
                            <div class="col-md-4"> <label for="status" class="form-label">Rating</label>
                                <select class="form-select" name="star_count" id="star_count"
                                    aria-label="Default select example" readonly>
                                    <option value="1" @if (old('star_count', isset($review) ? $review->star_count : '') == 1) selected @endif> 1
                                    </option>
                                    <option value="2" @if (old('star_count', isset($review) ? $review->star_count : '') == 2) selected @endif> 2
                                    </option>
                                    <option value="3" @if (old('star_count', isset($review) ? $review->star_count : '') == 3) selected @endif> 3
                                    </option>
                                    <option value="4" @if (old('star_count', isset($review) ? $review->star_count : '') == 4) selected @endif> 4
                                    </option>
                                    <option value="5" @if (old('star_count', isset($review) ? $review->star_count : '') == 5) selected @endif> 5
                                    </option>
                                </select>
                                @error('star_count')
                                    <div class="error-alert">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4"> <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" id="status"
                                    aria-label="Default select example">
                                    <option value="1" @if (old('status', isset($review) ? $review->status : '') == 1) selected @endif> Active
                                    </option>
                                    <option value="0" @if (old('status', isset($review) ? $review->status : '') == 0) selected @endif> InActive
                                    </option>
                                </select>
                                @error('status')
                                    <div class="error-alert">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label" for="question">Content</label>
                                <textarea id="content" rows="5" name="content" class="form-control" placeholder="">{{ old('description', isset($review) ? $review->content : '') }}</textarea>
                                @error('content')
                                    <div class="error-alert">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @if (isset($review->reviewImages) && $review->reviewImages->count() > 0)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label" for="question">Images</label>
                                    <div class="review-images-container">
                                        @foreach ($review->reviewImages as $review_image)
                                            <div class="review-image-wrapper position-relative d-inline-block mr-2">
                                                <!-- Close button -->
                                                <button type="button"
                                                    class="close-btn position-absolute remove_review_image"
                                                    data-url="{{ route('delete-review-image', encrypt($review_image->id)) }}">
                                                    ×
                                                </button>
                                                <!-- Image -->
                                                <img src="{{ asset('storage/' . $review_image->file_path) }}"
                                                    class="img-thumbnail">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            $('.remove_review_image').on('click', function(e) {
                var URL = $(this).attr('data-url');
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: URL,
                            type: "DELETE",
                            success: function(response) {
                                console.log(response);
                                if (response.status === 'success') {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Record has been deleted successfully.',
                                        icon: 'success',
                                        showConfirmButton: true, // Show confirm button to let the user close the dialog
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload(); // Reload the page
                                        }
                                    });
                                } else if (response.status === 'error') {
                                    console.log("something went wrong");
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log(xhr.responseText);
                            }
                        });
                    }
                });
            })
        </script>
    @endpush
@endsection
