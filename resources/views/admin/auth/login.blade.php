<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login | {{ config('admin.brand.name') }}</title>
    <link rel="icon" href="{{ asset('fastkart-admin/images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('fastkart-admin/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-admin/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('fastkart-admin/css/bawaskar-fastkart.css').'?v='.filemtime(public_path('fastkart-admin/css/bawaskar-fastkart.css')) }}">
</head>

<body>
    <section class="log-in-section section-b-space"><a href="{{ route('store.home') }}" class="logo-login"><span
                class="bawaskar-brand"></a>
        <div class="container w-100">
            <div class="row">
                <div class="col-xl-5 col-lg-6 me-auto">
                    <div class="log-in-box">
                        <div class="log-in-title">
                            <h3>Welcome to Bawaskar ERP</h3>
                            <h4>Administrator Login</h4>
                        </div>
                        @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>@endif @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                        <div class="input-box">
                            <form class="row g-4" method="POST" action="{{ route('admin.login.store') }}">@csrf<div
                                    class="col-12">
                                    <div class="form-floating theme-form-floating log-in-form"><input type="email"
                                            class="form-control @error('email') is-invalid @enderror" id="email"
                                            name="email" value="{{ old('email') }}" placeholder="Email Address" required
                                            autofocus><label for="email">Email Address</label></div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating theme-form-floating log-in-form"><input type="password"
                                            class="form-control @error('password') is-invalid @enderror" id="password"
                                            name="password" placeholder="Password" required><label
                                            for="password">Password</label></div>
                                </div>
                                <div class="col-12">
                                    <div class="forgot-box">
                                        <div class="form-check ps-0 m-0 remember-box"><input
                                                class="checkbox_animated check-box" type="checkbox" name="remember"
                                                value="1" id="remember"><label class="form-check-label"
                                                for="remember">Remember me</label></div>
                                    </div>
                                </div>
                                <div class="col-12"><button type="submit"
                                        class="btn btn-animation w-100 justify-content-center">Log In</button></div>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>