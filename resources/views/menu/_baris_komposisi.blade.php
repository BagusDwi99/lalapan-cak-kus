{{--
    Satu baris komposisi: pilihan bahan baku, jumlah per porsi, satuan, tombol hapus.
    $i     = nomor urut baris (untuk nama isian komposisi[$i][...])
    $baris = isi baris, contoh ['bahan_baku_id' => 1, 'jumlah' => 1]
--}}
<tr class="baris-komposisi">
    <td>
        <select name="komposisi[{{ $i }}][bahan_baku_id]"
                class="form-select pilih-bahan @error("komposisi.$i.bahan_baku_id") is-invalid @enderror">
            <option value="">-- Pilih bahan baku --</option>
            @foreach ($daftarBahan as $bahan)
                {{-- data-satuan dibaca JavaScript untuk menampilkan satuan saat bahan dipilih --}}
                <option value="{{ $bahan->id }}" data-satuan="{{ $bahan->satuan }}"
                        @selected(($baris['bahan_baku_id'] ?? null) == $bahan->id)>
                    {{ $bahan->nama }}
                </option>
            @endforeach
        </select>
        @error("komposisi.$i.bahan_baku_id")
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </td>
    <td>
        <input type="number" name="komposisi[{{ $i }}][jumlah]" value="{{ $baris['jumlah'] ?? '' }}"
               step="0.001" min="0" class="form-control @error("komposisi.$i.jumlah") is-invalid @enderror">
        @error("komposisi.$i.jumlah")
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </td>
    <td class="satuan-bahan text-muted">{{ $satuanBahan[$baris['bahan_baku_id'] ?? ''] ?? '-' }}</td>
    <td class="text-end">
        <button type="button" class="btn btn-outline-danger btn-sm tombol-hapus-baris">Hapus</button>
    </td>
</tr>
