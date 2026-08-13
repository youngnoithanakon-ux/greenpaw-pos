<x-app-layout>
    <x-slot name="title">จัดการพนักงาน — GreenPaw</x-slot>
    @php $editUser = $editUser ?? null; @endphp
    <style>
        .container { padding: 28px; max-width: 960px; margin: auto; }
        .card {
            background: white; padding: 24px; border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,.07); margin-bottom: 26px;
            border-top: 4px solid {{ $editUser ? '#f39c12' : '#3498db' }};
        }
        .card h3 { margin: 0 0 18px; font-size: 1rem; color: #2c3e50; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group label { display: block; font-size: .82rem; color: #666; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 7px;
            font-size: .9rem; outline: none; transition: .2s; box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #3498db; box-shadow: 0 0 0 3px rgba(52,152,219,.15);
        }
        .btn-submit {
            width: 100%; margin-top: 16px; padding: 12px; border: none; border-radius: 8px;
            font-weight: bold; font-size: .95rem; cursor: pointer; color: white;
            background: {{ $editUser ? '#f39c12' : '#27ae60' }}; transition: filter .2s;
        }
        .btn-submit:hover { filter: brightness(1.08); }
        .btn-cancel { display: block; text-align: center; margin-top: 10px; color: #666; text-decoration: none; font-size: .85rem; }
        .error-msg { color: #e74c3c; font-size: .8rem; margin-top: 3px; }

        .table-wrap { background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.06); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 540px; }
        th { background: #34495e; color: white; padding: 13px 14px; text-align: left; font-weight: 500; font-size: .85rem; }
        td { padding: 12px 14px; border-bottom: 1px solid #eee; font-size: .9rem; }
        tr:hover td { background: #f9fafb; }

        .badge-admin { background: #fee2e2; color: #b91c1c; padding: 3px 9px; border-radius: 4px; font-size: .75rem; font-weight: bold; }
        .badge-staff { background: #dbeafe; color: #1e40af; padding: 3px 9px; border-radius: 4px; font-size: .75rem; font-weight: bold; }
        .btn-edit {
            color: #f39c12; font-weight: bold; text-decoration: none; font-size: .82rem;
            padding: 5px 10px; border-radius: 5px; border: 1px solid #fde68a; background: #fffbeb; transition: .15s;
        }
        .btn-edit:hover { background: #f39c12; color: white; }
        .btn-del {
            color: #e74c3c; font-size: .82rem; padding: 5px 10px; border-radius: 5px;
            border: 1px solid #fecaca; background: #fff5f5; margin-left: 6px;
            cursor: pointer; font-weight: bold; transition: .15s;
        }
        .btn-del:hover { background: #e74c3c; color: white; }
        .you-tag { font-size: .72rem; background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 4px; margin-left: 6px; }
    </style>

    <div class="container">
        {{-- ฟอร์มเพิ่ม / แก้ไข --}}
        <div class="card">
            <h3>{{ $editUser ? '📝 แก้ไขข้อมูลพนักงาน' : '➕ เพิ่มพนักงานใหม่' }}</h3>

            <form method="POST"
                  action="{{ $editUser ? route('admin.users.update', $editUser) : route('admin.users.store') }}">
                @csrf
                @if($editUser) @method('PUT') @endif

                <div class="form-grid">
                    <div class="form-group">
                        <label>Username</label>
                        @if($editUser)
                            <input type="text" value="{{ $editUser->username }}" readonly style="background:#f3f4f6;">
                        @else
                            <input type="text" name="username" value="{{ old('username') }}" required maxlength="50" placeholder="ชื่อผู้ใช้">
                            @error('username')<p class="error-msg">{{ $message }}</p>@enderror
                        @endif
                    </div>
                    <div class="form-group">
                        <label>ชื่อ-นามสกุล</label>
                        <input type="text" name="fullname" value="{{ old('fullname', $editUser->fullname ?? '') }}" required maxlength="100" placeholder="ชื่อจริง นามสกุล">
                        @error('fullname')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label>รหัสผ่าน{{ $editUser ? ' (เว้นว่างถ้าไม่เปลี่ยน)' : '' }}</label>
                        <input type="password" name="password" {{ $editUser ? '' : 'required' }} placeholder="รหัสผ่าน">
                        @error('password')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label>สิทธิ์การใช้งาน</label>
                        <select name="role">
                            <option value="staff" {{ old('role', $editUser->role ?? 'staff') === 'staff' ? 'selected' : '' }}>Staff — ขายได้อย่างเดียว</option>
                            <option value="admin" {{ old('role', $editUser->role ?? '') === 'admin' ? 'selected' : '' }}>Admin — จัดการระบบได้</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    {{ $editUser ? '💾 บันทึกการเปลี่ยนแปลง' : '✅ สร้างบัญชีผู้ใช้' }}
                </button>
                @if($editUser)
                    <a href="{{ route('admin.users.index') }}" class="btn-cancel">ยกเลิก</a>
                @endif
            </form>
        </div>

        {{-- ตารางรายชื่อ --}}
        <h3 style="margin-bottom:14px;">👥 รายชื่อผู้ใช้งานทั้งหมด</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>สิทธิ์</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td>
                            <strong>{{ $u->username }}</strong>
                            @if($u->id === auth()->id()) <span class="you-tag">คุณ</span> @endif
                        </td>
                        <td>{{ $u->fullname }}</td>
                        <td>
                            <span class="badge-{{ $u->role }}">{{ strtoupper($u->role) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn-edit">แก้ไข</a>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                  style="display:inline;"
                                  onsubmit="return confirm('ยืนยันการลบพนักงาน \"{{ $u->fullname }}\"?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del">ลบ</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:40px;color:#aaa;">ยังไม่มีผู้ใช้งาน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
