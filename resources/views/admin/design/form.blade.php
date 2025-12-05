@extends('admin.layouts.common')
@section('content')
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Design/</span>
        @if (isset($design))
            Edit
        @else
            Create
        @endif
    </h4>
    <x-success-message />

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Design </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($design) ? route('designs.update', $design->id) : route('designs.store') }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        @if (isset($design))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="image_name">Image</label>
                            <input id="image_name" type="file" name="image_name" class="form-control" placeholder="">
                            @error('image_name')
                                <div class="error-alert">{{ $message }}</div>
                            @enderror
                            @if (isset($design))
                                <img class="mt-3" src="{{ asset('storage/' . $design->image_path) }}" width="70"
                                    height="70">
                            @endif

                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="x_axis">X Axis Value</label>
                            <input id="x_axis" type="text" name="x_axis" class="form-control" placeholder=""
                                value="{{ isset($design) ? $design->x_axis : '' }}">
                            @error('x_axis')
                                <div class="error-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="y_axis">Y Axis Value</label>
                            <input id="y_axis" type="text" name="y_axis" class="form-control" placeholder=""
                                value="{{ isset($design) ? $design->y_axis : '' }}">
                            @error('y_axis')
                                <div class="error-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="target_width">Target Width</label>
                            <input id="target_width" type="text" name="target_width" class="form-control" placeholder=""
                                value="{{ isset($design) ? $design->target_width : '' }}">
                            @error('target_width')
                                <div class="error-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="y_axis">Target Height</label>
                            <input id="target_height" type="text" name="target_height" class="form-control"
                                placeholder="" value="{{ isset($design) ? $design->target_height : '' }}">
                            @error('target_height')
                                <div class="error-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="y_axis">Rotation</label>
                            <input id="rotation" type="text" name="rotation" class="form-control"
                                placeholder="" value="{{ isset($design) ? $design->rotation : '' }}">
                            @error('rotation')
                                <div class="error-alert">{{ $message }}</div>
                            @enderror
                        </div>


                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    @endpush
@endsection
