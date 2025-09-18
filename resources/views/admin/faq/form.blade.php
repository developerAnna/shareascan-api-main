@extends('admin.layouts.common')
@section('content')
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">FAQ/</span>
        @if (isset($faq))
            Edit
        @else
            Create
        @endif
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">FAQ </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($faq) ? route('faq.update', $faq->id) : route('faq.store') }}" method="post">
                        @csrf
                        @if (isset($faq))
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label" for="question">Question</label>
                            <input type="text" name="question" class="form-control"
                                value="{{ old('question', isset($faq) ? $faq->question : '') }}" id="question"
                                placeholder="Enter Question" />
                            @error('question')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="answer">Answer</label>
                            <textarea id="answer" rows="5" name="answer" class="form-control" placeholder="Enter Answer">{{ old('answer', isset($faq) ? $faq->answer : '') }}</textarea>
                            @error('answer')
                                <div class="error-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status" aria-label="Default select example">
                                <option value="" @if (old('status', isset($faq) ? $faq->status : '') == '') selected @endif>Select Status
                                </option>
                                <option value="1" @if (old('status', isset($faq) ? $faq->status : '') == 1) selected @endif> Active</option>
                                <option value="0" @if (old('status', isset($faq) ? $faq->status : '') == 0) selected @endif> InActive</option>
                            </select>
                            @error('status')
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
