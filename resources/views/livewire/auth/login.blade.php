<x-layouts.auth>
    <div class="flex flex-col gap-6">
        {{-- <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" /> --}}
        {{-- <x-auth-header :title="__('Masuk ke Akun SIKAP FT UNSOED')" :description="__('Masukkan email dan kata sandi Anda di bawah ini untuk masuk')" /> --}}

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input name="email" :label="__('Email')" type="email" required autofocus autocomplete="email"
                placeholder="Fulan@mhs.unsoed.ac.id" />

            <!-- Password -->
            <div class="relative">
                <flux:input name="password" :label="__('Password')" type="password" required
                    autocomplete="current-password" :placeholder="__('Password')" viewable />

                {{-- @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif --}}

                {{-- Ganti link "Lupa password" dengan tooltip --}}
                {{-- <div class="flex justify-end">
                    <flux:heading class="flex items-center gap-2 text-xs text-zinc-500">
                        Lupa password?
                        <flux:tooltip toggleable>
                            <flux:button icon="information-circle" size="sm" variant="ghost" />
                            <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                <p>Reset kata sandi mandiri dinonaktifkan.</p>
                                <p>Silakan hubungi Bapendik untuk bantuan reset.</p>
                            </flux:tooltip.content>
                        </flux:tooltip>
                    </flux:heading>
                </div> --}}
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Ingat Saya')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button"
                    icon="arrow-right">
                    {{ __('Masuk') }}
                </flux:button>
            </div>
        </form>

        {{-- @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
            </div>
        @endif --}}

        {{-- Satu tooltip gabungan di bawah --}}
        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            <div class="inline-flex items-center gap-2">
                <span>Butuh akses akun atau lupa kata sandi?</span>
                <flux:tooltip toggleable>
                    <flux:button icon="information-circle" size="sm" variant="ghost" />
                    <flux:tooltip.content class="max-w-[22rem] space-y-2">
                        <p>Registrasi mandiri <strong>dan</strong> reset kata sandi via aplikasi
                            <strong>dinonaktifkan</strong>.
                        </p>
                        <p>Akun dibuat/diaktifkan oleh Bapendik. Untuk permintaan akun baru, aktivasi, atau reset kata
                            sandi, silakan hubungi Bapendik melalui kanal resmi.</p>
                    </flux:tooltip.content>
                </flux:tooltip>
            </div>
        </div>
    </div>
</x-layouts.auth>
