<x-app-layout>
    <x-slot name="title">ตั้งค่าโปรไฟล์ — GreenPaw</x-slot>
    <style>
        .container { padding: 28px; max-width: 680px; margin: auto; }
        .card {
            background: white; padding: 28px; border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,.07); margin-bottom: 24px;
        }
        .card h2 { margin: 0 0 6px; font-size: 1.05rem; color: #1a202c; }
        .card p  { margin: 0 0 22px; font-size: .88rem; color: #718096; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: .82rem; font-weight: 600; color: #4a5568; margin-bottom: 6px; }
        .form-group input {
            width: 100%; padding: 10px 13px; border: 1px solid #e2e8f0;
            border-radius: 7px; font-size: .92rem; outline: none; transition: .2s; box-sizing: border-box;
        }
        .form-group input:focus { border-color: #27ae60; box-shadow: 0 0 0 3px rgba(39,174,96,.15); }
        .error-msg { color: #e53e3e; font-size: .8rem; margin-top: 4px; }
        .btn { padding: 10px 22px; border: none; border-radius: 7px; font-weight: bold; cursor: pointer; font-size: .9rem; }
        .btn-green  { background: #27ae60; color: white; }
        .btn-green:hover { background: #219150; }
        .btn-red    { background: #e53e3e; color: white; }
        .btn-red:hover { background: #c53030; }
        .saved-msg  { font-size: .82rem; color: #27ae60; margin-left: 12px; }
        .danger-zone { border-top: 4px solid #e53e3e; }
    </style>

    <div class="container">
        {{-- ===== แก้ไขข้อมูลผู้ใช้ ===== --}}
        <div class="card">
            <h2>⚙️ ข้อมูลโปรไฟล์</h2>
            <p>แก้ไขชื่อผู้ใช้และชื่อ-นามสกุลของบัญชีคุณ</p>

            <form method="post" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="form-group">
                    <label>ชื่อผู้ใช้ (Username)</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" required autofocus autocomplete="username">
                    @error('username')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="fullname" value="{{ old('fullname', $user->fullname) }}" required autocomplete="name">
                    @error('fullname')<p class="error-msg">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-green">💾 บันทึกการเปลี่ยนแปลง</button>
                @if (session('status') === 'profile-updated')
                    <span class="saved-msg">✅ บันทึกแล้ว</span>
                @endif
            </form>
        </div>

        {{-- ===== เปลี่ยนรหัสผ่าน ===== --}}
        <div class="card">
            <h2>🔑 เปลี่ยนรหัสผ่าน</h2>
            <p>ใช้รหัสผ่านที่ยาวและคาดเดาได้ยากเพื่อความปลอดภัย</p>

            <form method="post" action="{{ route('password.update') }}">
                @csrf
                @method('put')

                <div class="form-group">
                    <label>รหัสผ่านปัจจุบัน</label>
                    <input type="password" name="current_password" autocomplete="current-password">
                    @error('current_password', 'updatePassword')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>รหัสผ่านใหม่</label>
                    <input type="password" name="password" autocomplete="new-password">
                    @error('password', 'updatePassword')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password">
                    @error('password_confirmation', 'updatePassword')<p class="error-msg">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-green">🔄 เปลี่ยนรหัสผ่าน</button>
                @if (session('status') === 'password-updated')
                    <span class="saved-msg">✅ เปลี่ยนรหัสผ่านแล้ว</span>
                @endif
            </form>
        </div>

        {{-- ===== ลบบัญชี ===== --}}
        <div class="card danger-zone">
            <h2 style="color:#c53030;">🗑️ ลบบัญชีผู้ใช้</h2>
            <p>เมื่อลบแล้วจะไม่สามารถกู้คืนได้ กรุณายืนยันรหัสผ่านก่อนดำเนินการ</p>

            <form method="post" action="{{ route('profile.destroy') }}"
                  onsubmit="return confirm('⚠️ ยืนยันการลบบัญชีนี้?\nการกระทำนี้ไม่สามารถย้อนกลับได้')">
                @csrf
                @method('delete')

                <div class="form-group">
                    <label>รหัสผ่านปัจจุบัน (เพื่อยืนยัน)</label>
                    <input type="password" name="password" autocomplete="current-password">
                    @error('password', 'userDeletion')<p class="error-msg">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-red">🗑️ ลบบัญชีถาวร</button>
            </form>
        </div>
    </div>
</x-app-layout>
