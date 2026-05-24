<x-guest-layout>
    <style>
        body {
            background: linear-gradient(135deg, #83a4b3, #d7f2ed);
        }

        .register-card {
            width: 400px;
            margin: 25px auto;
            background: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            border-radius: 4px;
            overflow: hidden;
        }

        .register-header {
            background: #2f4b5a;
            color: white;
            text-align: center;
            padding: 18px;
            font-size: 28px;
            font-weight: bold;
            position: relative;
        }

        .register-header::after {
            content: "";
            position: absolute;
            bottom: -16px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 18px solid transparent;
            border-right: 18px solid transparent;
            border-top: 18px solid #2f4b5a;
        }

        .register-body {
            padding: 38px 30px 25px;
        }

        .input-group-custom {
            display: flex;
            margin-bottom: 16px;
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

        .btn-register {
            width: 100%;
            background: #45c777;
            color: white;
            border: none;
            padding: 12px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 4px;
            margin-top: 10px;
        }

        .btn-register:hover {
            background: #35b866;
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #333;
            text-decoration: none;
        }

        .register-footer {
            background: #2f4b5a;
            color: white;
            text-align: center;
            padding: 20px;
            font-weight: bold;
        }
    </style>

    <div class="register-card">
        <div class="register-header">
            Crear cuenta
        </div>

        <div class="register-body">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div>
                    <div class="input-group-custom">
                        <div class="input-icon">👤</div>
                        <x-text-input
                            id="name"
                            class="input-field"
                            type="text"
                            name="name"
                            :value="old('name')"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Name"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <div class="input-group-custom">
                        <div class="input-icon">✉️</div>
                        <x-text-input
                            id="email"
                            class="input-field"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autocomplete="username"
                            placeholder="Email"
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
                            autocomplete="new-password"
                            placeholder="Password"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <div class="input-group-custom">
                        <div class="input-icon">🔐</div>
                        <x-text-input
                            id="password_confirmation"
                            class="input-field"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm Password"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button type="submit" class="btn-register">
                    Register
                </button>

                <a class="login-link" href="{{ route('login') }}">
                    Already registered?
                </a>
            </form>
        </div>

        <div class="register-footer">
            Welcome! Create your account now
        </div>
    </div>
</x-guest-layout>