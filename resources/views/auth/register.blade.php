@extends('Front.layouts.comman')
@section('content')
    <!-- page header section start -->
    <section class="pageheader-section px-lg-5">
        <div class="container-fluid">
            <div class="header-caption contact">
                <h2>Register</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Register</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>
    <!-- page header section end -->

    <!-- Login section start -->
    <section class="login-section pt-80 pb-80 px-lg-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6">
                    <div class="login-images">
                        <img src="{{ asset('front/img/home/login-image.png') }}" alt="" />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="login-form">
                        <div class="sub-title">
                            <h4>Register</h4>
                        </div>
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group mb-4">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" placeholder="" name="name" value="{{ old('name') }}"
                                            class="form-control @error('name') is-invalid @enderror" autocomplete="name"
                                            autofocus required>
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group mb-4">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" placeholder=""
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email') }}" required autocomplete="email">

                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group mb-4">
                                        <label class="form-label">Password</label>
                                        <input type="password" placeholder=""
                                            class="form-control @error('password') is-invalid @enderror" name="password"
                                            required autocomplete="new-password">
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group mb-4">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" placeholder="" class="form-control"
                                            name="password_confirmation" required autocomplete="new-password">

                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group mb-4">
                                        <div class="remember">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="flexCheckDefault"
                                                    name="terms_and_conditions">
                                                <label class="form-check-label " for="flexCheckDefault">
                                                    I agree with term and conditions
                                                </label>
                                                @error('terms_and_conditions')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <button type="submit" value="" class="btn btn-theme rounded-pill w-100">Sign
                                            Up</button>
                                    </div>
                                    <div class="bottom-bar">
                                        <p>Already have an account? <a href="{{ route('login') }}"> Log in</a></p>
                                        <span>Or</span>
                                        <a href="#" class="google">
                                            <img src="{{ asset('front/img/home/google.png') }}" alt="">
                                            Sign Up with Google
                                        </a>
                                        <a href="#" class="google">
                                            <img src="{{ asset('front/img/home/facebook.png') }}" alt="">
                                            Login with Facebook
                                        </a>
                                        <a href="#" class="google">
                                            <img src="{{ asset('front/img/home/apple.png') }}" alt="">
                                            Login with Apple
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Login section end -->
@endsection
