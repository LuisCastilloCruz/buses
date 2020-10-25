@extends('tenant.layouts.auth')

@section('content')
    <section class="body-sign">
        <div class="center-sign">
            <div class="card">
                <div class="card card-header card-primary" style="background:#6EB23F">
                    <p class="card-title text-center">AQPFACT NUBE</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Correo</label>
                            <div class="input-group">
                                <span class="input-group-append">
                                    <span class="input-group-text" style="border-radius: 10px 0px 0px 10px;color: #fff;background: #6eb23f;border: 0;">
                                        <i class="fas fa-user"></i>
                                    </span>
                                </span>
                                <input id="email" type="email" name="email" class="form-control form-control-lg" value="{{ old('email') }}">
                            </div>
                            @if ($errors->has('email'))
                                <label class="error">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </label>
                            @endif
                        </div>
                        <div class="form-group mb-3 {{ $errors->has('password') ? ' error' : '' }}">
                            <label>Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-append">
                                    <span class="input-group-text" style="border-radius: 10px 0px 0px 10px;color: #fff;background: #6eb23f;border: 0;">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                </span>
                                <input name="password" type="password" class="form-control form-control-lg">
                            </div>
                            @if ($errors->has('password'))
                                <label class="error">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </label>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-sm-8 text-left">
                                <button type="submit" class="btn btn-primary mt-2" style="background-color: #6EB23F;border-color: #6EB23F #6EB23F #68b335; color: #FFF;">Iniciar sesión</button>
                            </div>
                            <div class="col-sm-4">
                                <div class="checkbox-custom checkbox-default">
                                    <input name="remember" id="RememberMe" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
                                    <label for="RememberMe">Recordarme</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            {{-- <p class="text-center text-muted mt-3 mb-3">&copy; Copyright {{ date('Y') }}. Todos los derechos reservados</p> --}}
        </div>
    </section>
@endsection
