@extends('admin.layouts.common')
@section('content')
    @push('css')
        <style>
            input.form-control:read-only {
                background-color: #D3D3D3;
            }
        </style>
    @endpush
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Email Template/</span>
        @if (isset($email_template))
            Edit
        @else
            Create
        @endif
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Email Template </h5>
                </div>
                <div class="card-body">
                    <form
                        action="{{ isset($email_template) ? route('email-templates.update', $email_template->id) : route('email-templates.store') }}"
                        method="post">
                        @csrf
                        @if (isset($email_template))
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label" for="name">Name</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', isset($email_template) ? $email_template->name : '') }}"
                                id="name" placeholder="Enter Name" @if (isset($email_template)) readonly @endif />
                            @error('name')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        @if (isset($email_template) && isset($vars[$email_template->slug]))
                            <div class="col-md-12 mb-3">
                                <div class="form-group border border-primary p-3">
                                    <pre>{{ $vars[$email_template->slug] }}</pre>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="subject">Subject</label>
                            <input type="text" name="subject" class="form-control"
                                value="{{ old('subject', isset($email_template) ? $email_template->subject : '') }}"
                                id="subject" placeholder="Enter Subject" />
                            @error('subject')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="body">Body</label>
                            <textarea class="form-control email_body ckeditor" name="body" id="body" rows="5">{{ old('body', isset($email_template) ? $email_template->body : '') }}</textarea>
                            @error('body')
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
        <script src="{{ asset('admin/tinymce/tinymce.min.js') }}"></script>
        <script src="{{ asset('admin/js/email_template.js') }}"></script>
    @endpush
@endsection
