<x-guest-layout>
    <style>
        body {
            background: linear-gradient(135deg, #83a4b3, #d7f2ed);
        }

        .login-card {
            width: 380px;
            margin: 40px auto;
            background: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            border-radius: 4px;
            overflow: hidden;
        }

        .login-header {
            background: #2f4b5a;
            color: white;
            text-align: center;
            padding: 18px;
            font-size: 28px;
            font-weight: bold;
            position: relative;
        }

        .login-header::after {
            content: "";
            position: absolute;
            bottom: -16px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 18px solid transparent;
            border-right: 18px solid transparent;
            border-top: 18px solid #2f4b5a;
        }

        .login-body {
            padding: 38px 30px 25px;
        }

        .input-group-custom {
            display: flex;
            margin-bottom: 18px;
            border: 2px solid #2f4b5a;
            border-radius: 3px;
            overflow: hidden;
        }

        .input-icon {
            width: 48px;
            background: #2f4b5a;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .input-field {
            flex: 1;
            border: none !important;
            box-shadow: none !important;
            padding: 12px;
        }

        .input-field:focus {
            outline: none;
            box-shadow: none !important;
            border: none !important;
        }

        .btn-login {
            width: 100%;
            background: #45c777;
            color: white;
            border: none;
            padding: 12px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 4px;
            margin-top: 12px;
        }

        .btn-login:hover {
            background: #35b866;
        }

        .forgot-link {
            display: block;
            text-align: center;
            margin-top: 14px;
            color: #333;
            text-decoration: none;
        }

        .login-footer {
            background: #2f4b5a;
            color: white;
            text-align: center;
            padding: 22px;
            font-weight: bold;
        }

        .remember-box {
            font-size: 14px;
            margin-top: 5px;
        }
    </style>

    <div class="login-card">
        <div class="login-header">
            ExampleService
        </div>

        <div class="login-body">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <div class="input-group-custom">
                        <div class="input-icon">👤</div>
                        <x-text-input 
                            id="email" 
                            class="input-field" 
                            type="email" 
                            name="email" 
                            :value="old('email')" 
                            required 
                            autofocus 
                            autocomplete="username" 
                            placeholder="Username"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <div class="input-group-custom">
                        <div class="input-icon">🔒</div>
                        <x-text-input 
                            id="password" 
                            class="input-field"
                            type="password"
                            name="password"
                            required 
                            autocomplete="current-password"
                            placeholder="Password"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="remember-box">
                    <label for="remember_me">
                        <input id="remember_me" type="checkbox" name="remember">
                        {{ __('Remember me') }}
                    </label>
                </div>

                <button type="submit" class="btn-login">
                    Login
                </button>

                @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">
                        I forgot my password
                    </a>
                @endif
            </form>
        </div>

        <div class="login-footer">
            No account? Create one now!
        </div>
    </div>
</x-guest-layout>