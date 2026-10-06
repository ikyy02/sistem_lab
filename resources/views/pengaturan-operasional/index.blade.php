@extends('layouts.app')

@section('title', 'Pengaturan Operasional')
@section('page-title', 'Pengaturan Operasional')
@section('page-subtitle', 'Jam operasional, hari operasional, dan hari libur laboratorium.')

@section('content')
@php
    $aktif = old('hari', $setting->hariAktif());
    $buka = old('jam_buka', substr($setting->jam_buka, 0, 5));
    $tutup = old('jam_tutup', substr($setting->jam_tutup, 0, 5));
@endphp

<div class="row g-3">
    <div class="col-12 col-lg-5">
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
    </div>

    <div class="col-12 col-lg-7">
        <div class="card-modern overflow-hidden">
            <div class="p-4 pb-3">
                <h5 class="fw-bold mb-3" style="font-size:1rem;">Hari / Tanggal Libur</h5>
                <form method="POST" action="{{ route('pengaturan-operasional.libur.store') }}" class="row g-2 align-items-start">
                    @csrf
                    <div class="col-12 col-md-4">
                        <input type="date" name="tanggal" value="{{ old('tanggal') }}" aria-label="Tanggal" class="form-control @error('tanggal') is-invalid @enderror">
                        @error('tanggal')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-5">
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}" maxlength="100" placeholder="Keterangan libur" aria-label="Keterangan" class="form-control @error('keterangan') is-invalid @enderror">
                        @error('keterangan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah</button></div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-silab mb-0">
                    <thead><tr><th class="ps-4" style="width:56px;">No</th><th>Tanggal</th><th>Keterangan</th><th class="text-end pe-4">Aksi</th></tr></thead>
                    <tbody>
                    @forelse($libur as $item)
                        <tr>
                            <td class="ps-4 text-muted">{{ $libur->firstItem() + $loop->index }}</td>
                            <td class="fw-semibold">{{ $item->tanggal->translatedFormat('l, d F Y') }}</td>
                            <td>{{ $item->keterangan }}</td>
                            <td class="text-end pe-4">
                                <form action="{{ route('pengaturan-operasional.libur.destroy', $item->id_libur) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus hari libur ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-del d-inline-flex align-items-center gap-1"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-5" style="color:var(--text-muted);"><i class="bi bi-calendar-x fs-1 d-block mb-2" style="color:#c9bdb4;"></i>Belum ada hari libur.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($libur->hasPages())<div class="table-footer"><div>{{ $libur->total() }} hari libur</div>{{ $libur->links('pagination.silab') }}</div>@endif
        </div>
    </div>
</div>
@endsection
