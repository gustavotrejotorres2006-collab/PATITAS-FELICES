@extends('layouts.public')

@section('title', 'Iniciar sesión')
@section('meta-description', 'Accede al portal privado de gestión de Patitas Felices.')

@section('header-actions')
    <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-outline-variant px-4 py-2.5 text-sm font-semibold text-primary transition-colors hover:bg-surface-container-low focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
        Volver al sitio
    </a>
@endsection

@section('content')
    <section class="grid min-h-[65vh] place-items-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md rounded-3xl border border-outline-variant/50 bg-surface-container-lowest p-6 shadow-sm sm:p-8">
            <div class="mb-6 flex flex-col gap-2">
                <p class="text-sm font-bold uppercase tracking-widest text-secondary">Portal privado</p>
                <h1 class="text-3xl font-bold tracking-tight text-primary">Iniciar sesión</h1>
                <p class="leading-7 text-on-surface-variant">Ingresa con la cuenta del personal de la clínica.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
                @csrf

                <div class="flex flex-col gap-2">
                    <label for="email" class="text-sm font-semibold text-primary">Correo electrónico</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                        autofocus
                        aria-describedby="@error('email') email-error @enderror"
                        @error('email') aria-invalid="true" @enderror
                        class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none transition focus:border-secondary focus:ring-2 focus:ring-secondary/30"
                    >
                    @error('email')
                        <p id="email-error" class="text-sm font-medium text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="password" class="text-sm font-semibold text-primary">Contraseña</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        aria-describedby="@error('password') password-error @enderror"
                        @error('password') aria-invalid="true" @enderror
                        class="min-h-12 rounded-lg border border-outline-variant bg-surface px-4 py-3 text-on-surface outline-none transition focus:border-secondary focus:ring-2 focus:ring-secondary/30"
                    >
                    @error('password')
                        <p id="password-error" class="text-sm font-medium text-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-primary-container px-5 py-3 font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                    Entrar al portal
                    <span class="material-symbols-outlined" aria-hidden="true">login</span>
                </button>
            </form>
        </div>
    </section>
@endsection
