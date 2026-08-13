<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('ข้อมูลโปรไฟล์') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('แก้ไขชื่อผู้ใช้และชื่อ-นามสกุลของบัญชีคุณ') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="username" :value="__('ชื่อผู้ใช้ (Username)')" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)" required autofocus autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="fullname" :value="__('ชื่อ-นามสกุล')" />
            <x-text-input id="fullname" name="fullname" type="text" class="mt-1 block w-full" :value="old('fullname', $user->fullname)" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('fullname')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('บันทึก') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('บันทึกแล้ว') }}</p>
            @endif
        </div>
    </form>
</section>
