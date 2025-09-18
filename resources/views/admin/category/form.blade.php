@extends('admin.layouts.common')
@section('content')
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Category/</span>
        @if (isset($db_category))
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
                    <h5 class="mb-0">Category </h5>
                </div>
                <div class="card-body">
                    <form
                        action="{{ isset($db_category) ? route('category.update', $db_category->id) : route('category.store') }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        @if (isset($db_category))
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" name="category_id" id="category_id"
                                aria-label="Default select example">
                                <option value="" selected>Select Category</option>
                                @if (!empty($merchmake_categories))
                                    @foreach ($merchmake_categories as $category)
                                        <!-- Main category option -->
                                        <option value="{{ $category['id'] }}|{{ $category['title'] }}"
                                            @if (isset($db_category) && $db_category->category_id == $category['id']) selected @endif>
                                            {{ $category['title'] }}</option>

                                        <!-- Check if the category has subcategories -->
                                        @if (isset($category['sub_categories']) && !empty($category['sub_categories']))
                                            @foreach ($category['sub_categories'] as $sub_category)
                                                <!-- Subcategory option -->
                                                <option
                                                    value="{{ $sub_category['id'] }}|{{ $category['title'] }} > {{ $sub_category['title'] }}"
                                                    @if (isset($db_category) && $db_category->category_id == $sub_category['id'])
                                                    selected
                                            @endif>
                                            {{ $category['title'] }} > {{ $sub_category['title'] }}
                                            </option>
                                        @endforeach
                                    @endif
                                @endforeach
                                @endif
                            </select>

                            @error('category_id')
                                <div class="error-alert">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="mb-3">
                            <label class="form-label" for="image">Image</label>
                            <input id="image" type="file" name="image" class="form-control" placeholder="">
                            @error('image')
                                <div class="error-alert">{{ $message }}</div>
                            @enderror
                            @if (isset($db_category))
                                <img class="mt-3" src="{{ asset('CategoryImages/' . $db_category->image) }}"
                                    width="70px" height="70px">
                            @endif
                        </div>

                        <div class="mb-3">

                            <label class="form-label" for="description">Description</label>
                            <textarea id="description" rows="5" name="description" class="form-control" placeholder="">{{ old('description', isset($db_category) ? $db_category->description : '') }}</textarea>
                            @error('description')
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
