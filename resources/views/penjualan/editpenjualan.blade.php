@extends('layout.mainlayout')
@section('title', 'Edit Penjualan')
@section('content')
<div class="container py-3">
    <h1>Form Edit Penjualan (ID Faktur: {{ $penj->id_faktur }})</h1>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Form Update: Gunakan method PUT -->
    <form id="penjualan-form" method="POST" action="{{ route('penjualan.update', $penj->id) }}">
        @csrf
        @method('PUT')

        <!-- 1. Header Penjualan -->
        <div class="row mb-3" style="font-size: 12px;">
            <label for="kode_sales" class="col-sm-2 col-form-label">Kode Sales:</label>
            <div class="col-sm-3">
                <input type="text" name="kode_sales" id="kode_sales"
                       class="form-control" style="font-size: 12px;"
                       value="{{ old('kode_sales', $penj->kode_sales) }}" required>
            </div>
            <label for="nama_sales" class="col-sm-2 col-form-label">Nama Sales:</label>
            <div class="col-sm-3">
                <input type="text" name="nama_sales" id="nama_sales"
                       class="form-control" style="font-size: 12px;"
                       value="{{ old('nama_sales', $penj->nama_sales) }}" required>
            </div>
        </div>

        <div class="row mb-3" style="font-size: 12px;">
            <label for="kode_pelanggan" class="col-sm-2 col-form-label">Kode Pelanggan:</label>
            <div class="col-sm-3">
                <input type="text" id="kode_pelanggan" name="kode_pelanggan"
                       class="form-control" readonly
                       value="{{ old('kode_pelanggan', $penj->kode_pelanggan) }}">
            </div>

            <label for="created_at" class="col-sm-2 col-form-label">Tanggal:</label>
            <div class="col-sm-3">
                <input type="date" id="created_at" name="created_at"
                       class="form-control" style="font-size:12px;"
                       value="{{ old('created_at', \Carbon\Carbon::parse($penj->created_at)->format('Y-m-d')) }}"
                       required>
            </div>
        </div>

        <div class="row mb-3" style="font-size: 12px;">
            <label for="nama_pelanggan" class="col-sm-2 col-form-label">Nama Pelanggan:</label>
            <div class="col-sm-3">
                <input type="text" id="nama_pelanggan" name="nama_pelanggan"
                       class="form-control" readonly
                       value="{{ old('nama_pelanggan', $penj->nama_pelanggan) }}">
            </div>

            <label for="alamat" class="col-sm-2 col-form-label">Alamat:</label>
            <div class="col-sm-3">
                <input type="text" id="alamat" name="alamat"
                       class="form-control" readonly
                       value="{{ old('alamat', optional($pelanggan)->alamat) }}">
            </div>
        </div>

        <div class="row mb-3" style="font-size: 12px;">
            <label for="telepon" class="col-sm-2 col-form-label">Telepon:</label>
            <div class="col-sm-3">
                <input type="text" id="telepon" name="telepon"
                       class="form-control" readonly
                       value="{{ old('telepon', optional($pelanggan)->telepon) }}">
            </div>
        </div>

        <!-- 2. Tabel Items (Detail) -->
        <div id="items-container">
            <h3>Items</h3>
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr style="font-size: 10px;">
                        <th style="width: 8%;">Kode Barang</th>
                        <th style="width: 15%;">Nama Barang</th>
                        <th style="width: 10%;">Harga</th>
                        <th style="width: 6%;">Dus</th>
                        <th style="width: 6%;">Lsn</th>
                        <th style="width: 6%;">Pcs</th>
                        <th style="width: 6%;">Isi Dus</th>
                        <th style="width: 8%;">Qty</th>
                        <th style="width: 6%;">Stok</th>
                        <th style="width: 6%;">Disc 1 (%)</th>
                        <th style="width: 6%;">Disc 2 (%)</th>
                        <th style="width: 6%;">Disc 3 (%)</th>
                        <th style="width: 6%;">Disc 4 (%)</th>
                        <th style="width: 10%;">Jumlah</th>
                        <th style="width: 10%;">Aksi</th>
                        <th style="width: 6%;">Notif</th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    {{-- Loop detail lama --}}
                    @foreach($penj->details as $idx => $det)
                        <tr>
                            <td>
                                <textarea name="items[{{ $idx }}][kode_barang]"
                                          class="form-control" style="font-size:12px; resize: vertical; overflow: hidden;" readonly>{{ $det->kode_barang }}</textarea>
                            </td>
                            <td>
                                <textarea name="items[{{ $idx }}][nama_barang]"
                                          class="form-control" style="font-size:12px; resize: vertical; overflow: hidden;" readonly>{{ $det->nama_barang }}</textarea>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][harga]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->harga }}" readonly>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][dus]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->dus }}" oninput="updateTotal({{ $idx }})">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][lsn]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->lusin }}" oninput="updateTotal({{ $idx }})">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][pcs]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->pcs }}" oninput="updateTotal({{ $idx }})">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][isidus]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->dus > 0 ? intval($det->quantity / $det->dus) : 1 }}"
                                       readonly>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][quantity]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->quantity }}" readonly>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][stok]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ \App\Models\Barang::where('kode_barang', $det->kode_barang)->first()->stok ?? 0 }}"
                                       readonly>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][disc1]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->disc1 }}" oninput="updateTotal({{ $idx }})">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][disc2]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->disc2 }}" oninput="updateTotal({{ $idx }})">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][disc3]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->disc3 }}" oninput="updateTotal({{ $idx }})">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][disc4]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->disc4 }}" oninput="updateTotal({{ $idx }})">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $idx }}][jumlah]"
                                       class="form-control" style="font-size:12px;"
                                       value="{{ $det->jumlah }}" readonly>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger"
                                        style="font-size:12px;"
                                        onclick="deleteRow(this)">Hapus</button>
                            </td>
                            <td>
                                <span class="warning-text text-danger" style="font-size:12px;"></span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="button" class="btn btn-primary btn-sm" onclick="addNewItem()">Tambah Item</button>
            <button type="button" class="btn btn-secondary btn-sm" id="hitung-discount">Hitung Discount</button>
        </div>

        <!-- 3. Total Discount & Total Keseluruhan -->
        <div class="mt-3">
            <strong>Total Diskon:</strong> <span id="total_discount">0.00</span>
        </div>
        <div>
            <strong>Total Keseluruhan:</strong> <span id="total-amount">0.00</span>
        </div>

        <!-- 4. Tombol Submit -->
        <button type="submit" class="btn btn-success mt-3" id="saveButton">
            Simpan Perubahan
        </button>
        @if($errors->any())
            <ul class="text-danger mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif    
    </form>
</div>

{{-- =========================== SCRIPT & LOGIKA JS =========================== --}}
<script>
    // 1) Update Total per Baris (sinkron dengan form input yang ada)
    function updateTotal(index) {
        const row = document.querySelector(`#items-body tr:nth-child(${index + 1})`);
        if (!row) return;

        const harga    = parseFloat(row.querySelector(`input[name="items[${index}][harga]"]`).value) || 0;
        const dus      = parseFloat(row.querySelector(`input[name="items[${index}][dus]"]`).value) || 0;
        const lsn      = parseFloat(row.querySelector(`input[name="items[${index}][lsn]"]`).value) || 0;
        const pcs      = parseFloat(row.querySelector(`input[name="items[${index}][pcs]"]`).value) || 0;
        const isidus   = parseFloat(row.querySelector(`input[name="items[${index}][isidus]"]`).value) || 1;
        const stok     = parseFloat(row.querySelector(`input[name="items[${index}][stok]"]`).value) || 0;
        const disc1    = parseFloat(row.querySelector(`input[name="items[${index}][disc1]"]`).value) || 0;
        const disc2    = parseFloat(row.querySelector(`input[name="items[${index}][disc2]"]`).value) || 0;
        const disc3    = parseFloat(row.querySelector(`input[name="items[${index}][disc3]"]`).value) || 0;
        const disc4    = parseFloat(row.querySelector(`input[name="items[${index}][disc4]"]`).value) || 0;
        const notifEl  = row.querySelector('.warning-text');

        // Hitung quantity
        const quantity = (dus * isidus) + (lsn * 12) + pcs;
        row.querySelector(`input[name="items[${index}][quantity]"]`).value = quantity;

        // Jika stok tidak cukup → tandai baris
        if (stok < quantity) {
            row.style.backgroundColor = '#f8d7da';
            notifEl.textContent = 'Stok kurang!';
        } else {
            row.style.backgroundColor = '';
            notifEl.textContent = '';
        }

        // Hitung diskon persentase total baris
        const totalKotor     = harga * quantity;
        const totalDiscPct   = disc1 + disc2 + disc3 + disc4;
        const discValueLine  = totalKotor * (totalDiscPct / 100);
        const nettoLine      = totalKotor - discValueLine;

        // Tampilkan jumlah netto di kolom jumlah (jika ingin override)
        row.querySelector(`input[name="items[${index}][jumlah]"]`).value = nettoLine;

        // Update total keseluruhan
        calculateTotals();
    }

    // 2) Delete Row
    function deleteRow(button) {
        const row = button.closest('tr');
        row.parentNode.removeChild(row);
        calculateTotals();
    }

    // 3) Hitung Total Keseluruhan (netto)
    function calculateTotals() {
        let total = 0;
        document.querySelectorAll('#items-body tr').forEach((row, idx) => {
            const val = parseFloat(row.querySelector(`input[name="items[${idx}][jumlah]"]`).value) || 0;
            total += val;
        });
        document.getElementById('total-amount').innerText = total.toFixed(2);
    }

    // 4) Fungsi Menghitung Discount untuk Semua Baris
    // function recalculateDiscount() {
    //     let totalKotor = 0;
    //     // Hitung total kotor dan total dus untuk kode_barang tertentu
    //     let totalDus21132689 = 0;
    //     document.querySelectorAll('#items-body tr').forEach((row, idx) => {
    //         const harga    = parseFloat(row.querySelector(`input[name="items[${idx}][harga]"]`).value) || 0;
    //         const dus      = parseFloat(row.querySelector(`input[name="items[${idx}][dus]"]`).value) || 0;
    //         const lsn      = parseFloat(row.querySelector(`input[name="items[${idx}][lsn]"]`).value) || 0;
    //         const pcs      = parseFloat(row.querySelector(`input[name="items[${idx}][pcs]"]`).value) || 0;
    //         const isidus   = parseFloat(row.querySelector(`input[name="items[${idx}][isidus]"]`).value) || 1;
    //         const kodeBar  = row.querySelector(`textarea[name="items[${idx}][kode_barang]"]`).value;

    //         const qtyLine  = (dus * isidus) + (lsn * 12) + pcs;
    //         const kotor    = harga * qtyLine;
    //         totalKotor    += kotor;

    //         if (kodeBar === "21132689") {
    //             totalDus21132689 += dus;
    //         }
    //     });

    //     // Setelah dapat total kotor dan total dus khusus:
    //     document.querySelectorAll('#items-body tr').forEach((row, idx) => {
    //         const harga    = parseFloat(row.querySelector(`input[name="items[${idx}][harga]"]`).value) || 0;
    //         const dus      = parseFloat(row.querySelector(`input[name="items[${idx}][dus]"]`).value) || 0;
    //         const lsn      = parseFloat(row.querySelector(`input[name="items[${idx}][lsn]"]`).value) || 0;
    //         const pcs      = parseFloat(row.querySelector(`input[name="items[${idx}][pcs]"]`).value) || 0;
    //         const isidus   = parseFloat(row.querySelector(`input[name="items[${idx}][isidus]"]`).value) || 1;
    //         const kodeBar  = row.querySelector(`textarea[name="items[${idx}][kode_barang]"]`).value;

    //         // Diskon 1: totalKotor ≥ 1.000.000 → 2%, > 500.000 → 1%, else 0%
    //         let disc1 = 0;
    //         if (totalKotor >= 1000000) {
    //             disc1 = 2;
    //         } else if (totalKotor > 500000) {
    //             disc1 = 1;
    //         }

    //         // Diskon 2: kode_barang "21132689" & totalDus21132689 > 3 → 10%
    //         let disc2 = (kodeBar === "21132689" && totalDus21132689 > 3) ? 10 : 0;

    //         // Jaga agar disc3 dan disc4 tetap apa adanya (value input)
    //         let disc3 = parseFloat(row.querySelector(`input[name="items[${idx}][disc3]"]`).value) || 0;
    //         let disc4 = parseFloat(row.querySelector(`input[name="items[${idx}][disc4]"]`).value) || 0;

    //         // Tulis kembali diskon (disc1, disc2)
    //         row.querySelector(`input[name="items[${idx}][disc1]"]`).value = disc1;
    //         row.querySelector(`input[name="items[${idx}][disc2]"]`).value = disc2;

    //         // Setelah urus diskon, panggil updateTotal untuk mereset jumlah baris
    //         updateTotal(idx);
    //     });

    //     // Setelah semua baris ter‐update, panggil calculateTotals
    //     calculateTotals();
    // }

    // Pasang event listener untuk tombol “Hitung Discount”
    document.getElementById('hitung-discount')
        .addEventListener('click', recalculateDiscount);

    // Pastikan ketika tombol “Simpan” dipanggil, diskon sudah ter‐hitung
    document.getElementById('saveButton')
        .addEventListener('click', recalculateDiscount);

    // 5) Fungsi Tambah Item Baru
    function addNewItem() {
        const index = document.querySelectorAll('#items-body tr').length;
        const row = `
            <tr>
                <td class="kode-barang-column">
                    <select name="items[${index}][kode_barang]" class="form-control kode-barang-select" data-index="${index}" style="width: 100%;">
                        <option value="">Pilih Barang</option>
                    </select>
                </td>
                <td>
                    <textarea name="items[${index}][nama_barang]" class="form-control" style="font-size:12px; resize: vertical; overflow: hidden;" readonly></textarea>
                </td>
                <td>
                    <input type="number" name="items[${index}][harga]" value="0" class="form-control" style="font-size:12px;" readonly>
                </td>
                <td>
                    <input type="number" name="items[${index}][dus]" value="0" class="form-control" style="font-size:12px;" oninput="updateTotal(${index})">
                </td>
                <td>
                    <input type="number" name="items[${index}][lsn]" value="0" class="form-control" style="font-size:12px;" oninput="updateTotal(${index})">
                </td>
                <td>
                    <input type="number" name="items[${index}][pcs]" value="0" class="form-control" style="font-size:12px;" oninput="updateTotal(${index})">
                </td>
                <td>
                    <input type="number" name="items[${index}][isidus]" value="0" class="form-control" style="font-size:12px;" readonly>
                </td>
                <td>
                    <input type="number" name="items[${index}][quantity]" value="0" class="form-control" style="font-size:12px;" readonly>
                </td>
                <td>
                    <input type="number" name="items[${index}][stok]" value="0" class="form-control" style="font-size:12px;" readonly>
                </td>
                <td>
                    <input type="number" name="items[${index}][disc1]" value="0" class="form-control" style="font-size:12px;" oninput="updateTotal(${index})" readonly>
                </td>
                <td>
                    <input type="number" name="items[${index}][disc2]" value="0" class="form-control" style="font-size:12px;" oninput="updateTotal(${index})" readonly>
                </td>
                <td>
                    <input type="number" name="items[${index}][disc3]" value="0" class="form-control" style="font-size:12px;" oninput="updateTotal(${index})" readonly>
                </td>
                <td>
                    <input type="number" name="items[${index}][disc4]" value="0" class="form-control" style="font-size:12px;" oninput="updateTotal(${index})">
                </td>
                <td>
                    <input type="number" name="items[${index}][jumlah]" value="0" class="form-control" style="font-size:12px;" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-danger" style="font-size:12px;" onclick="deleteRow(this)">Hapus</button>
                </td>
                <td>
                    <span class="warning-text text-danger" style="font-size:12px;"></span>
                </td>
            </tr>
        `;
        document.getElementById('items-body').insertAdjacentHTML('beforeend', row);
        initializeSelect2(index);
        calculateTotals();
    }

    // 6) Inisialisasi Select2 (fetch barang via AJAX)
    function initializeSelect2(index = null) {
        let selector = index !== null
            ? `.kode-barang-select[data-index="${index}"]`
            : '.kode-barang-select';

        $(selector).select2({
            placeholder: 'Cari kode barang atau nama...',
            ajax: {
                url: '/barang/search',   // Pastikan endpoint ini ada, mengembalikan JSON list barang
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map(item => ({
                            id: item.kode_barang,
                            text: `${item.kode_barang} - ${item.nama_barang}`,
                            nama_barang: item.nama_barang,
                            harga: item.harga,
                            isidus: item.isidus,
                            stok: item.stok
                        }))
                    };
                },
                cache: true
            }
        }).on('select2:select', function (e) {
            const data = e.params.data;
            const idx  = $(this).data('index');

            // Isi otomatis kolom di baris tersebut
            document.querySelector(`textarea[name="items[${idx}][nama_barang]"]`).value = data.nama_barang || '';
            document.querySelector(`input[name="items[${idx}][harga]"]`).value = data.harga || 0;
            document.querySelector(`input[name="items[${idx}][isidus]"]`).value = data.isidus || 1;
            document.querySelector(`input[name="items[${idx}][stok]"]`).value = data.stok || 0;
            updateTotal(idx);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        initializeSelect2();
        // Setelah halaman load, hitung ulang total berdasarkan data yang ada
        document.querySelectorAll('#items-body tr').forEach((_, idx) => updateTotal(idx));
    });
</script>

{{-- 7) Style CSS Tambahan --}}
<style>
    .kode-barang-column {
        max-width: 120px;
        word-wrap: break-word;
        white-space: normal;
    }
    .kode-barang-select {
        width: 100% !important;
        min-width: 100px;
    }
    .form-control {
        font-size: 12px;
        padding: 5px;
    }
</style>
@endsection
