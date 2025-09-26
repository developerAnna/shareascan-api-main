@extends('Front.layouts.comman')
@section('content')
    <section class="pageheader-section px-lg-5">
        <div class="container-fluid">
            <div class="header-caption contact">
                <h2>Verify Email</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Verify Email</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>
    <section class="login-section pt-80 pb-80 px-lg-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6">
                    <div class="login-images">
                        <img src="{{ asset('front/img/home/login-image.png') }}" alt="" />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Verify Your Email Address</div>

                        <div class="card-body">
                            @if (session('resent'))
                                <div class="alert alert-success" role="alert">
                                    A fresh verification link has been sent to your email address.
                                </div>
                            @endif

                            Before proceeding, please check your email for a verification link.
                            If you did not receive the email,
                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 m-0 align-baseline">click here to request
                                    another</button>.
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
