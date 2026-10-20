<!-- Modal Tambah/Edit -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form
            method="POST"
            id="itemForm"
            enctype="multipart/form-data"
            class="modal-content"
            autocomplete="off"
            data-store="{{ route('kelola-katalog.store', $kategori) }}"
            data-update="{{ route('kelola-katalog.update', ['kategori' => $kategori, 'id' => '__ID__']) }}"
            data-query="{{ json_encode($modalQuery) }}">

            @csrf

            <input
                type="hidden"
                name="_method"
                id="itemMethod"
                value="PUT"
                disabled>

            <div class="modal-header">
                <h5
                    class="modal-title fw-bold"
                    id="itemModalTitle"
                    style="font-size:1rem;">
                    Tambah {{ $label }}
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Tutup">
                </button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-3">
                    @foreach($fields as $f)
                        <div class="col-12 {{ in_array($f['name'], ['stok', 'id_satuan', 'harga', 'id_ruangan']) ? 'col-md-6' : '' }}">
                            <label
                                class="form-label"
                                for="f_{{ $f['name'] }}">
                                {{ $f['label'] }}@if($f['required'] ?? true)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            @if(in_array($f['type'], ['select', 'satuan']))
                                <select
                                    id="f_{{ $f['name'] }}"
                                    name="{{ $f['name'] }}"
                                    class="form-select @error($f['name']) is-invalid @enderror">

                                    <option value="">
                                        {{ $f['placeholder'] }}
                                    </option>

                                    @foreach($f['options'] as $val => $opt)
                                        <option
                                            value="{{ $val }}"
                                            @selected((string) old($f['name']) === (string) $val)>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>

                            @elseif($f['type'] === 'textarea')
                                <textarea
                                    id="f_{{ $f['name'] }}"
                                    name="{{ $f['name'] }}"
                                    rows="3"
                                    class="form-control @error($f['name']) is-invalid @enderror"
                                    placeholder="{{ $f['placeholder'] }}">{{ old($f['name']) }}</textarea>

                            @else
                                <input
                                    type="{{ $f['type'] }}"
                                    id="f_{{ $f['name'] }}"
                                    name="{{ $f['name'] }}"
                                    value="{{ old($f['name']) }}"
                                    @if($f['type'] === 'number')
                                        min="{{ $f['name'] === 'harga' ? '0.01' : '0' }}"
                                        step="{{ $f['name'] === 'harga' ? '0.01' : '1' }}"
                                    @endif
                                    class="form-control @error($f['name']) is-invalid @enderror"
                                    placeholder="{{ $f['placeholder'] }}">
                            @endif

                            @error($f['name'])
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    @endforeach

                        <div class="col-12">
                            <label
                                class="form-label"
                                for="f_gambar">
                                Gambar @unless($isRoom)<span class="text-danger">*</span>@endunless
                            </label>

                            <div class="d-flex align-items-center gap-3">
                                <img
                                    id="gambarPreview"
                                    src=""
                                    alt=""
                                    class="thumb"
                                    style="display:none;"
                                    width="56" height="56" tabindex="0" role="button" aria-label="Perbesar pratinjau gambar {{ $label }}"
                                    onclick="previewImage(this.src, 'Gambar {{ $label }}')"
                                    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">

                                <input
                                    type="file"
                                    id="f_gambar"
                                    name="gambar"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    class="form-control @error('gambar') is-invalid @enderror">
                            </div>

                            <div class="form-text" id="gambarHint">
                                Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                            </div>

                            @error('gambar')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-silab"
                    data-bs-dismiss="modal">
                    Batal
                </button>

                <button
                    type="submit"
                    class="btn btn-primary">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
