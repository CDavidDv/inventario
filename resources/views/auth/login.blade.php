@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Real de plateros logo" class="rounded-full w-32 h-32">
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mt-4">
                <input id="email" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" 
                       type="text" name="email" value="{{ old('email') }}" 
                       required autofocus autocomplete="username" placeholder="Username" />
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <input id="password" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
                       type="password" name="password" required 
                       autocomplete="current-password" placeholder="Password" />
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="w-full h-10 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Login
                </button>
            </div>

            <div class="mt-4">
                <img src="{{ asset('images/logo-transparente.png') }}" alt="logo" class="w-1/2 mx-auto">
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-900">
                    Forgot your password? <span>Reset your password</span>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection