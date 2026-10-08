@extends('layouts.app')

@section('title', 'Pengaturan Operasional')
@section('page-title', 'Pengaturan Operasional')
@section('page-subtitle', 'Jam operasional dan hari operasional laboratorium.')

@section('content')
@php
    $aktif = old('hari', $setting->hariAktif());
    $buka = old('jam_buka', substr($setting->jam_buka, 0, 5));
    $tutup = old('jam_tutup', substr($setting->jam_tutup, 0, 5));
@endphp

<form method="POST" action="{{ route('pengaturan-operasional.update') }}" class="card-modern p-4">
    @csrf @method('PUT')
    <h5 class="fw-bold mb-3" style="font-size:1rem;">Jam &amp; Hari Operasional</h5>
    <div class="row g-3">
        <div class="col-6">
            <label class="form-label" for="jam_buka">Jam Buka <span class="text-danger">*</span></label>
            <input type="time" id="jam_buka" name="jam_buka" value="{{ $buka }}" class="form-control @error('jam_buka') is-invalid @enderror">
            @error('jam_buka')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-6">
            <label class="form-label" for="jam_tutup">Jam Tutup <span class="text-danger">*</span></label>
            <input type="time" id="jam_tutup" name="jam_tutup" value="{{ $tutup }}" class="form-control @error('jam_tutup') is-invalid @enderror">
            @error('jam_tutup')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label">Hari Operasional <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3">
                @foreach($labelHari as $key => $text)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="hari[]" value="{{ $key }}" id="hari_{{ $key }}" @checked(in_array($key, $aktif, true))>
                        <label class="form-check-label" for="hari_{{ $key }}">{{ $text }}</label>
                    </div>
                @endforeach
            </div>
            @error('hari')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="mt-4"><button type="submit" class="btn btn-primary">Simpan</button></div>
</form>
@endsection
