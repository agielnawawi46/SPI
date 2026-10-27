@extends('layouts.app')

@section('title', 'Edit User')
@section('page_title', 'Edit User')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('admin.users.index') }}" class="breadcrumb-link">Manajemen User</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Edit User</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header border-b border-surface-200">
        <h2 class="card-title">Form Edit User: {{ $user->name }}</h2>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nama --}}
                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-input @error('name') border-red-400 @enderror" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label for="email" class="form-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-input @error('email') border-red-400 @enderror" required>
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                
                {{-- NIP --}}
                <div class="form-group">
                    <label for="nip" class="form-label">NIP</label>
                    <input type="text" name="nip" id="nip" value="{{ old('nip', $user->nip) }}" class="form-input @error('nip') border-red-400 @enderror">
                    @error('nip') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Jabatan --}}
                <div class="form-group">
                    <label for="position" class="form-label">Jabatan</label>
                    <input type="text" name="position" id="position" value="{{ old('position', $user->position) }}" class="form-input @error('position') border-red-400 @enderror">
                    @error('position') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Password (Opsional) --}}
                <div class="form-group">
                    <label for="password" class="form-label">Password Baru <span class="text-xs font-normal text-charcoal-muted">(Kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" name="password" id="password" class="form-input @error('password') border-red-400 @enderror">
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input">
                </div>

                {{-- Role --}}
                <div class="form-group">
                    <label for="role" class="form-label">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="role" class="form-input @error('role') border-red-400 @enderror" required>
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Bidang / Department --}}
                @php $lainDeptId = \App\Models\Department::where('code', 'LAIN')->first()?->id; @endphp
                <div class="form-group" x-data="{ selectedDept: '{{ old('department_id', $user->department_id) }}' }">
                    <label for="department_id" class="form-label">Bidang (Department)</label>
                    <select name="department_id" id="department_id" x-model="selectedDept" class="form-input @error('department_id') border-red-400 @enderror">
                        <option value="">-- Pilih Bidang (Opsional) --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">
                                {{ $dept->code }} - {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>

                    <template x-if="selectedDept == '{{ $lainDeptId }}'">
                        <div class="mt-2">
                            <input type="text" name="custom_department" placeholder="Masukkan nama bidang/department baru..." class="form-input @error('custom_department') border-red-400 @enderror" value="{{ old('custom_department') }}" required>
                        </div>
                    </template>

                    @error('department_id') <p class="form-error">{{ $message }}</p> @enderror
                    @error('custom_department') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Status Aktif --}}
                <div class="form-group md:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-charcoal">Akun Aktif</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-surface-200">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection
