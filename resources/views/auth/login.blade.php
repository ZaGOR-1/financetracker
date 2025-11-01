@extends('layouts.guest')

@section('title', 'Вхід')

@section('content')
<div class="flex items-center justify-center min-h-[calc(100vh-8rem)] px-4">
    <div class="w-full max-w-md">
        <div class="card">
            <h1 class="text-2xl font-bold text-center text-white mb-6">
                Вхід до системи
            </h1>

            @if (session('status'))
                <div class="mb-4 p-4 text-sm rounded-lg bg-gray-800 text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block mb-2 text-sm font-medium text-white">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400"
                        placeholder="name@example.com">
                    @error('email')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block mb-2 text-sm font-medium text-white">Пароль</label>
                    <input type="password" name="password" id="password" required
                        class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                            class="w-4 h-4 text-primary-600 bg-gray-700 border-gray-600 rounded focus:ring-primary-600 ring-offset-gray-800">
                        <label for="remember" class="ms-2 text-sm font-medium text-gray-300">Запам'ятати мене</label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full btn-primary">
                    Увійти
                </button>

                <p class="text-sm font-light text-gray-400 mt-4 text-center">
                    Ще не маєте облікового запису? <a href="{{ route('register') }}" class="font-medium text-primary-500 hover:underline">Зареєструватися</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
