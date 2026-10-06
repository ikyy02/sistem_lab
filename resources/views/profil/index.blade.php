@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Lihat dan ubah data profil serta password Anda.')

@section('content')
<div class="row g-3">
    <div class="col-12 col-lg-7">
        <form method="POST" action="{{ route('profil.update') }}" class="card-modern p-4" autocomplete="off">
            @csrf @method('PUT')
            <h5 class="fw-bold mb-3" style="font-size:1rem;">Data Profil</h5>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">{{ $idLabel }}</label>
                    <input type="text" class="form-control" value="{{ $profil->getKey() }}" readonly>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Peran</label>
                    <input type="text" class="form-control" value="{{ $roleLabel }}" readonly>
                </div>
                @if($prodi)
                <div class="col-12">
                    <label class="form-label">Prodi</label>
                    <input type="text" class="form-control" value="{{ $prodi }}" readonly>
                </div>
                @endif
                <div class="col-12">
                    <label class="form-label" for="nama">Nama <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $profil->nama) }}" maxlength="100" class="form-control @error('nama') is-invalid @enderror">
                    @error('nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $profil->email) }}" maxlength="50" class="form-control @error('email') is-invalid @enderror">
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="no_whatsapp">Nomor WhatsApp</label>
                    <input type="text" id="no_whatsapp" name="no_whatsapp" value="{{ old('no_whatsapp', $profil->no_whatsapp) }}" maxlength="20" placeholder="08xxxxxxxxxx" class="form-control @error('no_whatsapp') is-invalid @enderror">
                    @error('no_whatsapp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mt-4"><button type="submit" class="btn btn-primary">Simpan Profil</button></div>
        </form>
    </div>

    <div class="col-12 col-lg-5">
        <form method="POST" action="{{ route('profil.password') }}" class="card-modern p-4" autocomplete="off">
            @csrf @method('PUT')
            <h5 class="fw-bold mb-3" style="font-size:1rem;">Ubah Password</h5>
            <div class="row g-3">
                @foreach([['password_lama', 'Password Lama'], ['password_baru', 'Password Baru'], ['password_baru_confirmation', 'Konfirmasi Password Baru']] as [$name, $text])
                <div class="col-12">
                    <label class="form-label" for="{{ $name }}">{{ $text }} <span class="text-danger">*</span></label>
                    <input type="password" id="{{ $name }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror">
                    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                @endforeach
            </div>
            <div class="mt-4"><button type="submit" class="btn btn-primary">Ubah Password</button></div>
        </form>
    </div>
</div>
@endsection
