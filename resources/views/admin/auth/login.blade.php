@extends('admin.layouts.app')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h1>
            <div class="login-logo">
                <a href="#">
                    <img src="{{asset('share/img/logo.png')}}" alt="">
                </a>
            </div>
        </h1>
        <div class="card-group">
            <div class="card p-4">
                <div class="card-body">
                    @if(!$errors->isEmpty())
                        <div class="alert alert-info">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.login') }}">
                        {{ csrf_field() }}
                        <p class="text-muted">{{ trans('global.login') }}</p>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-user"></i></span>
                            </div>
                            <input name="email" type="text" class="form-control" placeholder="{{ trans('global.login_email') }}" value="{{old('email')}}">
                        </div>
                        <div class="input-group mb-4">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                            </div>
                            <input name="password" type="password" class="form-control" placeholder="{{ trans('global.login_password') }}" value="{{old('password')}}">
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <input type="submit" class="btn btn-primary px-4" value='{{ trans('global.login') }}'>
                                <label class="ml-2">
                                    <input name="remember" type="checkbox" /> {{ trans('global.remember_me') }}
                                </label>
                            </div>
{{--                            <div class="col-6 text-right">--}}
{{--                                <a class="btn btn-link px-0" href="{{ route('password.request') }}">--}}
{{--                                    {{ trans('global.forgot_password') }}--}}
{{--                                </a>--}}
{{--                            </div>--}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
