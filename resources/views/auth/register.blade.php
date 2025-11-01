@extends('layouts.guest')

@section('title', 'Реєстрація')

@section('content')
<div class="flex items-center justify-center min-h-[calc(100vh-8rem)] px-4 py-8">
    <div class="w-full max-w-md">
        <div class="card">
            <h1 class="text-2xl font-bold text-center text-white mb-6">
                Створити обліковий запис
            </h1>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block mb-2 text-sm font-medium text-white">Ім'я</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                        class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400"
                        placeholder="Іван Іваненко">
                    @error('name')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block mb-2 text-sm font-medium text-white">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
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

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block mb-2 text-sm font-medium text-white">Підтвердження паролю</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400"
                        placeholder="••••••••">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full btn-primary">
                    Зареєструватися
                </button>

                <p class="text-sm font-light text-gray-400 mt-4 text-center">
                    Вже маєте обліковий запис? <a href="{{ route('login') }}" class="font-medium text-primary-500 hover:underline">Увійти</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
