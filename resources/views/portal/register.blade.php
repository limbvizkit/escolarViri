@extends('layouts.portal')

@section('title', 'Registro')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title mb-0">Crear cuenta del portal</h5>
                </div>
                <div class="ip-card-body">
                    <form method="POST" action="{{ route('portal.register.attempt') }}" class="row g-3">
                        @csrf

                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre completo <span class="ip-required">*</span></label>
                            <input type="text" id="name" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required autocomplete="name">
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo electrónico <span class="ip-required">*</span></label>
                            <input type="email" id="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="tel" id="phone" name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}" autocomplete="tel">
                            @error('phone')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="alumno_nombre" class="form-label">Nombre del alumno <span class="ip-required">*</span></label>
                            <input type="text" id="alumno_nombre" name="alumno_nombre"
                                class="form-control @error('alumno_nombre') is-invalid @enderror"
                                value="{{ old('alumno_nombre') }}" required>
                            @error('alumno_nombre')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="grado_escolar_id" class="form-label">Grado escolar <span class="ip-required">*</span></label>
                            <select id="grado_escolar_id" name="grado_escolar_id"
                                class="form-select @error('grado_escolar_id') is-invalid @enderror" required>
                                <option value="" disabled {{ old('grado_escolar_id') ? '' : 'selected' }}>Selecciona un grado</option>
                                @foreach ($grados as $id => $nombre)
                                    <option value="{{ $id }}" {{ old('grado_escolar_id') == $id ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grado_escolar_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 ip-form-actions">
                            <a href="{{ route('portal.login') }}" class="btn ip-btn-outline">Ya tengo cuenta</a>
                            <button type="submit" class="btn ip-btn-success">Registrarme</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
