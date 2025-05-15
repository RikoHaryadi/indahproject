@extends('layout.mainlayout')
@section('title', 'Penjualan')
@section('content')
<div style="margin-left: 0px; padding: 5px;">
    <h1>Form Penjualan</h1>

    <!-- Error Message -->
    <div id="error-message" style="color: red; display: none;">
        Stok barang tidak mencukupi untuk beberapa item. Silakan periksa jumlahnya.
    </div>

    <!-- Form Penjualan -->
    <form id="penjualan-form" method="POST" action="{{ route('penjualan.store') }}">
        @csrf
        <div class="row mb-3" style="font-size: 12px;">
            <label for="po_id" class="col-sm-2 col-form-label col-form-label-sm">Nomor PO:</label>
            <div class="col-sm-3">
                <input type="text" name="po_id" id="po_id" class="form-control" style="font-size: 12px;" placeholder="Masukkan Nomor SO">
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-primary btn-sm" onclick="fetchPOData()">Ambil Data PO</button>
            </div>
        </div>
        <!-- Data Pelanggan -->
        <div class="row mb-3" style="font-size: 12px;">
            <label for="Kode_pelanggan" class="col-sm-2 col-form-label col-form-label-sm">Kode Pelanggan:</label>
            <div class="col-sm-3">
            <input type="text" id="Kode_pelanggan" name="kode_pelanggan" class="form-control" readonly>
            </div>
            <label for="created_at colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Tanggal:</label>
            <div class="col-sm-3">
                <input type="date" id="created_at" name="created_at" class="form-control" value="{{ date('Y-m-d') }}" style="font-size: 12px;" required>
            </div>
        </div>
        <div class="row mb-3" style="font-size: 12px;">
            <label for="Nama_pelanggan colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Nama Pelanggan:</label>
            <div class="col-sm-3">
                <input type="text" id="Nama_pelanggan" name="nama_pelanggan" class="form-control" readonly>
            </div>
            <label for="alamat colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Alamat:</label>
            <div class="col-sm-3">
                <input type="text" id="alamat" name="alamat" class="form-control" readonly>
            </div>
        </div>
        <div class="row mb-3">
            <label for="telepon colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Telepon:</label>
            <div class="col-sm-3">
                <input type="text" id="telepon" name="telepon" class="form-control" readonly>
            </div>
        </div>

        <!-- Tabel Items -->
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
                        <th style="width: 6%;">Disc 1</th>
                        <th style="width: 6%;">Disc 2</th>
                        <th style="width: 6%;">Disc 3</th>
                        <th style="width: 6%;">Disc 4</th>
                        <th style="width: 10%;">Jumlah</th>            
                        <th style="width: 10%;">Aksi</th>
                        <th style="width: 6%;">Notif</th>
                    </tr>
                </thead>
                <tbody id="items-body"></tbody>
            </table>
            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addNewItem()">Tambah Item</button>
            <div>
                <button type="button" class="btn btn-primary btn-sm mt-2" id="hitung-discount">Hitung Discount</button>
            </div>
        </div>

        <!-- Total Diskon dan Total Keseluruhan -->
        <div>
            <strong>Total Diskon:</strong> <span id="total_discount">0.00</span>
        </div>
        <div>
            <strong>Total Keseluruhan:</strong> <span id="total-amount">0.00</span>
        </div>
        <input type="hidden" name="total_discount" id="total_discount" value="0">

        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-success mt-3" name="action" value="save" id="saveButton">Simpan</button>
        <button type="submit" class="btn btn-secondary mt-3" name="action" value="save_and_print" id="saveButton">Simpan & Cetak</button>
        @if($errors->any())
            <ul class="text-danger">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif    
        @if(session('success'))
            <p style="color: green;">{{ session('success') }}</p>
        @endif
    </form>
</div>

<!-- SCRIPT: FETCH DATA, PERHITUNGAN, DISCOUNT, & PENAMBAHAN ITEM -->
<script>
// --- 1. Fungsi Fetch Data PO ---
function fetchPOData() {
    const poId = document.getElementById('po_id').value;
    if (!poId) {
        alert('Masukkan Nomor PO terlebih dahulu.');
        return;
    }

    fetch(`/po/${poId}`)
        .then(response => response.json())
        .then(data => {
            // Populate data pelanggan
            document.getElementById('Kode_pelanggan').value = data.kode_pelanggan;
            document.getElementById('Nama_pelanggan').value = data.nama_pelanggan;
            document.getElementById('alamat').value = data.alamat;
            document.getElementById('telepon').value = data.telepon;
            
            // Populate items dari PO
            const itemsBody = document.getElementById('items-body');
            itemsBody.innerHTML = '';
            data.po_details.forEach((item, index) => {
                const row = `
  <tr>
    <td>
      <!-- Misal untuk kode barang, jika perlu multiline, gunakan textarea juga -->
      <textarea name="items[${index}][kode_barang]" class="form-control" style="font-size: 12px; resize: vertical; overflow: hidden;" readonly>${item.kode_barang}</textarea>
    </td>
    <td>
      <textarea name="items[${index}][nama_barang]" class="form-control" style="font-size: 12px; resize: vertical; overflow: hidden;" readonly>${item.nama_barang}</textarea>
    </td>
    <td>
      <input type="number" name="items[${index}][harga]" value="${item.harga}" class="form-control" style="font-size: 12px;" readonly>
    </td>
    <td>
      <input type="number" name="items[${index}][dus]" value="${item.dus}" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})">
    </td>
    <td>
      <input type="number" name="items[${index}][lsn]" value="${item.lusin}" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})">
    </td>
    <td>
      <input type="number" name="items[${index}][pcs]" value="${item.pcs}" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})">
    </td>
    <td>
      <input type="number" name="items[${index}][isidus]" value="${item.isidus}" class="form-control" style="font-size: 12px;" readonly>
    </td>
    <!-- Quantity akan dihitung otomatis -->
    <td>
      <input type="number" name="items[${index}][quantity]" class="form-control" style="font-size: 12px;" readonly>
    </td>
    <td>
      <input type="number" name="items[${index}][stok]" value="${item.stok}" class="form-control" style="font-size: 12px;" readonly>
    </td>
    <td>
      <input type="number" name="items[${index}][disc1]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})" readonly>
    </td>
    <td>
      <input type="number" name="items[${index}][disc2]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})" readonly>
    </td>
    <td>
      <input type="number" name="items[${index}][disc3]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})" readonly>
    </td>
    <td>
      <input type="number" name="items[${index}][disc4]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})">
    </td>
    <!-- Jumlah dihitung: harga * quantity -->
    <td>
      <input type="number" name="items[${index}][jumlah]" value="${item.jumlah || 0}" class="form-control" style="font-size: 12px;" readonly>
    </td>
    <td>
      <button type="button" class="btn btn-danger" style="font-size: 12px;" onclick="deleteRow(this)">Hapus</button>
    </td>
    <td>
      <span class="warning-text text-danger" style="font-size: 12px;"></span>
    </td>
  </tr>`;
itemsBody.insertAdjacentHTML('beforeend', row);
updateTotal(index);
            });
        })
        .catch(error => console.error('Error fetching PO data:', error));
}

// --- 2. Fungsi Update Total per Baris ---
// Menghitung quantity dan jumlah dengan formula:
// quantity = (dus × isidus) + (lsn × 12) + pcs  
// jumlah = harga × quantity
function updateTotal(index) {
    const row = document.querySelector(`#items-body tr:nth-child(${index + 1})`);
    if (!row) return;

    // Ambil nilai-nilai dari kolom yang diperlukan
    const harga = parseFloat(row.querySelector(`input[name="items[${index}][harga]"]`).value) || 0;
    const dus = parseFloat(row.querySelector(`input[name="items[${index}][dus]"]`).value) || 0;
    const lsn = parseFloat(row.querySelector(`input[name="items[${index}][lsn]"]`).value) || 0;
    const pcs = parseFloat(row.querySelector(`input[name="items[${index}][pcs]"]`).value) || 0;
    const isidus = parseFloat(row.querySelector(`input[name="items[${index}][isidus]"]`).value) || 0;
    const stok = parseFloat(row.querySelector(`input[name="items[${index}][stok]"]`).value) || 0;
    const warningText = row.querySelector('.warning-text');

    // Hitung quantity dengan formula:
    const quantity = (dus * isidus) + (lsn * 12) + pcs;
    row.querySelector(`input[name="items[${index}][quantity]"]`).value = quantity;

    // Periksa apakah stok lebih kecil dari quantity
    if (stok < quantity) {
        row.style.backgroundColor = '#f8d7da'; // Warna merah muda
        warningText.textContent = 'Stok tidak mencukupi!';
    } else {
        row.style.backgroundColor = ''; // Kembalikan ke warna normal
        warningText.textContent = '';
    }
    // Hitung jumlah (total per baris)
    const jumlah = harga * quantity;
    row.querySelector(`input[name="items[${index}][jumlah]"]`).value = jumlah;

    // Update total keseluruhan
    culateTotals();
}

// --- 3. Fungsi Hapus Baris ---
function deleteRow(button) {
    const row = button.closest('tr');
    row.parentNode.removeChild(row);
    culateTotals();
    
}

// --- 4. Fungsi Menghitung Total Keseluruhan ---
function culateTotals() {
    let total = 0;
    document.querySelectorAll('#items-body tr').forEach((row, index) => {
        const jumlah = parseFloat(row.querySelector(`input[name="items[${index}][jumlah]"]`).value) || 0;
        total += jumlah;
    });
    document.getElementById('total-amount').innerText = total.toFixed(2);
}

// --- 5. Fungsi Hitung Discount ---
// Fungsi ini akan mengiterasi semua baris dan menerapkan diskon sesuai syarat:
// a. Jika totalAmount (penjumlahan nilai jumlah dari semua baris) >= 1.000.000 maka disc1 = 2%,
//    jika > 500.000 maka disc1 = 1%, selain itu 0%.
// b. Jika ada item dengan kode_barang "21132689" dan total dus (nilai dus) > 3 maka disc2 = 10%.
// Fungsi Hitung Discount (berlaku untuk semua baris, termasuk yang baru ditambahkan)
// Fungsi Hitung Discount (berlaku untuk semua baris, termasuk item baru)

function recalculateDiscount() {
    let totalAmountKotor = 0; // total kotor sebelum diskon
    let totalDus21132689 = 0;

    // Iterasi semua baris untuk menghitung total kotor dan total dus untuk kode_barang "21132689"
    document.querySelectorAll('#items-body tr').forEach((row) => {
        // Ambil nilai jumlah (kotor) dari setiap baris
        const jumlahEl = row.querySelector('[name*="[jumlah]"]');
        let kotor = parseFloat(jumlahEl ? jumlahEl.value : 0) || 0;
        totalAmountKotor += kotor;

        // Untuk kode barang, ambil elemen apapun (input/select)
        const kodeBarangEl = row.querySelector('[name*="[kode_barang]"]');
        const kodeBarang = kodeBarangEl ? kodeBarangEl.value : "";
        const dusEl = row.querySelector('input[name*="[dus]"]');
        const dus = dusEl ? (parseFloat(dusEl.value) || 0) : 0;
        if (kodeBarang === "21132689") {
            totalDus21132689 += dus;
        }
    });

    // Terapkan discount untuk setiap baris
    document.querySelectorAll('#items-body tr').forEach((row) => {
        // Diskon 1: Berdasarkan totalAmount kotor
        const disc1Field = row.querySelector('input[name*="[disc1]"]');
        if (disc1Field) {
            if (totalAmountKotor >= 1000000) {
                disc1Field.value = 2;
            } else if (totalAmountKotor > 500000) {
                disc1Field.value = 1;
            } else {
                disc1Field.value = 0;
            }
        }
        
        // Diskon 2: Untuk item dengan kode_barang "21132689" jika total dus > 3
        // Gunakan selector yang menangkap input atau select
        const disc2Field = row.querySelector('input[name*="[disc2]"]');
        const kodeBarangField = row.querySelector('[name*="[kode_barang]"]');
        if (disc2Field && kodeBarangField) {
            if (kodeBarangField.value === "21132689" && totalDus21132689 > 3) {
                disc2Field.value = 10;
            } else {
                disc2Field.value = 0;
            }
        }
    });

    // Hitung total discount dan hitung ulang nilai netto tiap baris
    let totalDiscount = 0;
    let netTotal = 0;
    document.querySelectorAll('#items-body tr').forEach((row) => {
        const harga = parseFloat(row.querySelector('[name*="[harga]"]').value) || 0;
        const quantity = parseFloat(row.querySelector('[name*="[quantity]"]').value) || 0;
        const disc1 = parseFloat(row.querySelector('[name*="[disc1]"]').value) || 0;
        const disc2 = parseFloat(row.querySelector('[name*="[disc2]"]').value) || 0;
        const disc3 = parseFloat(row.querySelector('[name*="[disc3]"]').value) || 0;
        const disc4 = parseFloat(row.querySelector('[name*="[disc4]"]').value) || 0;
        const totalKotor = harga * quantity;
        const totalDiscPersen = disc1 + disc2 + disc3 + disc4;
        const discountValue = totalKotor * (totalDiscPersen / 100);
        totalDiscount += discountValue;

        // Nilai netto per baris: kotor dikurangi discount
        const netValue = totalKotor - discountValue;
        netTotal += netValue;

        // Update kolom jumlah dengan nilai netto per baris
        row.querySelector('[name*="[jumlah]"]').value = netValue;
    });

    // Perbarui tampilan total discount dan total keseluruhan (net total)
    document.getElementById('total_discount').innerText = totalDiscount.toFixed(2);
    document.getElementById('total-amount').innerText = netTotal.toFixed(2);
   
}

// Pasang event listener pada tombol "Hitung Discount"
document.getElementById('hitung-discount').addEventListener('click', recalculateDiscount);
document.getElementById('saveButton').addEventListener('click', recalculateDiscount);

// --- 6. Fungsi Menambahkan Item Baru ---
// Saat menambah item baru, baris baru ditambahkan, select2 diinisialisasi, dan total keseluruhan langsung di-update.
function addNewItem() {
    // Hitung jumlah baris yang sudah ada untuk mendapatkan index baru
    const index = document.querySelectorAll('#items-body tr').length;
    
    // Buat template row baru dengan nilai default (kosong atau 0)
    const row = `
        <tr>
            <td class="kode-barang-column">
                <select name="items[${index}][kode_barang]" class="form-control kode-barang-select" data-index="${index}" style="width: 100%;">
                    <option value="">Pilih Barang</option>
                </select>
            </td>
            <td>
                <!-- Menggunakan textarea untuk mendukung multiline -->
                <textarea name="items[${index}][nama_barang]" class="form-control" style="font-size: 12px; resize: vertical; overflow: hidden; white-space: pre-wrap;" readonly></textarea>
            </td>
            <td>
                <input type="number" name="items[${index}][harga]" value="0" class="form-control" style="font-size: 12px;" readonly>
            </td>
            <td>
                <input type="number" name="items[${index}][dus]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})">
            </td>
            <td>
                <input type="number" name="items[${index}][lsn]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})">
            </td>
            <td>
                <input type="number" name="items[${index}][pcs]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})">
            </td>
            <td>
                <input type="number" name="items[${index}][isidus]" value="0" class="form-control" style="font-size: 12px;" readonly>
            </td>
            <!-- Quantity dihitung otomatis -->
            <td>
                <input type="number" name="items[${index}][quantity]" value="0" class="form-control" style="font-size: 12px;" readonly>
            </td>
            <td>
                <input type="number" name="items[${index}][stok]" value="0" class="form-control" style="font-size: 12px;" readonly>
            </td>
            <td>
                <input type="number" name="items[${index}][disc1]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})" readonly>
            </td>
            <td>
                <input type="number" name="items[${index}][disc2]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})" readonly>
            </td>
            <td>
                <input type="number" name="items[${index}][disc3]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})" readonly>
            </td>
            <td>
                <input type="number" name="items[${index}][disc4]" value="0" class="form-control" style="font-size: 12px;" oninput="updateTotal(${index})">
            </td>
            <!-- Jumlah dihitung otomatis -->
            <td>
                <input type="number" name="items[${index}][jumlah]" value="0" class="form-control" style="font-size: 12px;" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-danger" style="font-size: 12px;" onclick="deleteRow(this)">Hapus</button>
            </td>
            <td>
                <span class="warning-text text-danger" style="font-size: 12px;"></span>
            </td>
        </tr>
    `;
    
    // Insert baris baru ke dalam table body dengan id "items-body"
    document.getElementById('items-body').insertAdjacentHTML('beforeend', row);
    
    // Inisialisasi Select2 untuk elemen select yang baru saja ditambahkan
    initializeSelect2(index);
    
    // Panggil fungsi untuk menghitung total (sesuaikan dengan nama fungsi Anda, misal "recalculateTotals")
    culateTotals();
}

// --- 7. Fungsi Inisialisasi Select2 ---
function initializeSelect2(index = null) {
    let selector = index !== null ? `.kode-barang-select[data-index="${index}"]` : '.kode-barang-select';

    $(selector).select2({
        placeholder: 'Cari kode barang atau nama...',
        ajax: {
            url: '/barang/search',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
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
        const idx = $(this).data('index');

        // Isi nilai otomatis untuk baris yang terpilih
        document.querySelector(`textarea[name="items[${idx}][nama_barang]"]`).value = data.nama_barang || '';
        document.querySelector(`input[name="items[${idx}][harga]"]`).value = data.harga || 0;
        document.querySelector(`input[name="items[${idx}][isidus]"]`).value = data.isidus || 0;
        document.querySelector(`input[name="items[${idx}][stok]"]`).value = data.stok || 0;
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initializeSelect2();
});
</script>

<!-- STYLE: ATUR LEBAR KOLOM DAN TAMPILAN INPUT -->
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
