{{--
    Satu baris item transaksi: pilihan menu, jumlah porsi, harga jual, subtotal, tombol hapus.
    $i     = nomor baris (untuk nama isian item[$i][...])
    $baris = isi baris, contoh ['menu_id' => 1, 'jumlah' => 2]
--}}
<tr class="baris-item">
    <td>
        <select name="item[{{ $i }}][menu_id]"
                class="form-select pilih-menu @error("item.$i.menu_id") is-invalid @enderror">
            <option value="">-- Pilih menu --</option>
            @foreach ($daftarMenu as $menu)
                {{-- data-harga dibaca JavaScript untuk menghitung subtotal --}}
                <option value="{{ $menu->id }}" data-harga="{{ $menu->harga_jual }}"
                        @selected(($baris['menu_id'] ?? null) == $menu->id)>
                    {{ $menu->nama }}
                </option>
            @endforeach
        </select>
        @error("item.$i.menu_id")
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </td>
    <td>
        <input type="number" name="item[{{ $i }}][jumlah]" value="{{ $baris['jumlah'] ?? '' }}"
               step="1" min="1" class="form-control jumlah-porsi @error("item.$i.jumlah") is-invalid @enderror">
        @error("item.$i.jumlah")
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </td>
    <td class="text-end harga-jual">-</td>
    <td class="text-end subtotal">-</td>
    <td class="text-end">
        <button type="button" class="btn btn-outline-danger btn-sm tombol-hapus-baris">Hapus</button>
    </td>
</tr>
