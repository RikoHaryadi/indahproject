@extends('layout.mainlayout')
@section('title', 'po')
@section('content')
<div class="container">
@if(session('success'))
    <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px; border: 1px solid #c3e6cb; border-radius: 5px;">
        {{ session('success') }}
    </div>
@endif

    <h1>Form Penjualan</h1>
    <form id="penjualan-form" method="POST" action="{{ route('po.store') }}">
    @csrf
    <div class="row mb-3">
        <label for="created_at colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Tanggal Transaksi</label>
        <div class="col-sm-3">
        <input type="date" id="created_at" name="created_at" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
    </div>
    <div class="row mb-3">
    <label for="Kode_pelanggan" class="col-sm-2 col-form-label col-form-label-sm">Pilih Pelanggan:</label>
    <div class="col-sm-3">
    <div>
        <select class="form-control kode-pelanggan-select">
                    <option value="">Pilih Pelanggan</option>
        </select>
     </div>
     <input type="hidden" name="kode_pelanggan" id="Kode_pelanggan">  {{-- Tambahkan ini --}}
    </div>
</div>


<div class="row mb-3">
    <label for="Nama_pelanggan" class="col-sm-2 col-form-label col-form-label-sm">Nama Pelanggan</label>
    <div class="col-sm-10">
        <input type="text" name="nama_pelanggan" id="Nama_pelanggan" class="form-control" readonly>
    </div>    
</div>
<div class="row mb-3">
    <label for="alamat" class="col-sm-2 col-form-label col-form-label-sm">Alamat</label>
    <div class="col-sm-10">
        <input type="text" name="alamat" id="alamat" class="form-control" readonly>
    </div>  
</div>
<div class="row mb-3">
    <label for="telepon" class="col-sm-2 col-form-label col-form-label-sm">Telepon</label>
    <div class="col-sm-10">
        <input type="text" name="telepon" id="telepon" class="form-control" readonly>
    </div>    
</div>

    <div id="items-container">
        <h3>Items</h3>
        <table class="table">
            <thead>
            
            <tr style="font-size: 10px;">
            <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Harga</th>
                    <th>Qty Dus</th>
                    <th>Qty Lsn</th>
                    <th>Qty Pcs</th>
                    <th>Isi Dus</th>
                    
                    <th>Quantity</th>
                    <th>Jumlah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="items-body"></tbody>
        </table>
        <button type="button" id="add-item" class="btn btn-primary" onclick="addNewItem()">Tambah Item</button>
    </div>
    <div>
            <strong>Total Keseluruhan:</strong> <span id="total-amount">0.00</span>
        </div>
    <button type="submit" class="btn btn-success mt-3" name="action" value="save">Simpan</button>
    <button type="submit" class="btn btn-secondary mt-3" name="action" value="save_and_print" target="_blank">Simpan & Cetak</button>
    @if($errors->any())
    <ul class="text-danger">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    @endif    
   
</form>
</div>
<!-- Skrip Custom -->
<!--  -->
<script>
 

    // Update detail pelanggan saat dipilih
    $('#Kode_pelanggan').on('change', function() {
        let selectedOption = $(this).find('option:selected');
        $('#Nama_pelanggan').val(selectedOption.data('nama') || '');
        $('#alamat').val(selectedOption.data('alamat') || '');
        $('#telepon').val(selectedOption.data('telepon') || '');
    });


</script>

<script>


// Tambah item baru
function addNewItem() {
    let lastRow = $('#items-body tr:last-child');

    // Cek apakah pengguna sudah memilih kode barang di baris terakhir sebelum menambahkan item baru
    if (lastRow.length > 0 && lastRow.find('.kode-barang-select').val() === '') {
        alert('Harap lengkapi input barang sebelumnya sebelum menambahkan yang baru.');
        return;
    }

   

    const index = document.querySelectorAll('#items-body tr').length;
    
    const row = `
    <tr>
        <td class="kode-barang-column">
            <select name="items[${index}][kode_barang]" class="form-control kode-barang-select" data-index="${index}" style="width: 100%; font-size: 10px;">
                <option value="">Pilih Barang</option>
            </select>
        </td>    
        <td><input type="text" name="items[${index}][nama_barang]" class="form-control" style="max-width: 150px;" readonly></td>
        <td><input type="number" name="items[${index}][harga]" class="form-control" style="font-size: 10px;" readonly></td>
        <td><input type="number" name="items[${index}][dus]" class="form-control" oninput="updateTotal(${index})" value = 0 required style="font-size: 10px;"></td>
        <td><input type="number" name="items[${index}][lsn]" class="form-control" oninput="updateTotal(${index})" value = 0 required style="font-size: 10px;"></td>
        <td><input type="number" name="items[${index}][pcs]" class="form-control" oninput="updateTotal(${index})" value = 0 required style="font-size: 10px;"></td>
        <td><input type="number" name="items[${index}][isidus]" class="form-control" readonly style="font-size: 10px;"></td>
        
        <td><input type="number" name="items[${index}][quantity]" class="form-control" readonly style="font-size: 10px;"></td>
        <td><input type="number" name="items[${index}][jumlah]" class="form-control" readonly style="font-size: 10px;"></td>
        <td><button type="button" class="btn btn-danger" onclick="deleteRow(this)">Hapus</button></td>
    </tr>`;

    document.getElementById('items-body').insertAdjacentHTML('beforeend', row);

    // Inisialisasi Select2 untuk dropdown barang yang baru ditambahkan
    initializeSelect2();
}


// Fungsi untuk inisialisasi select2 setelah elemen baru ditambahkan
function initializeSelect2() {
    // Inisialisasi Select2 untuk kode pelanggan
    $('.kode-pelanggan-select').select2({
        placeholder: 'Cari kode pelanggan atau nama...',
        ajax: {
            url: '/pelanggan/search', 
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: data.map(item => ({
                        id: item.Kode_pelanggan,
                        text: `${item.Kode_pelanggan} - ${item.Nama_pelanggan}`,
                        Nama_pelanggan: item.Nama_pelanggan,
                        alamat: item.alamat,
                        telepon: item.telepon
                    }))
                };
            },
            cache: true
        }
    }).on('select2:select', function (e) {
        const data = e.params.data;
        $('#Kode_pelanggan').val(data.id); // Simpan kode pelanggan di input hidden
        $('#Nama_pelanggan').val(data.Nama_pelanggan || '');
        $('#alamat').val(data.alamat || '');
        $('#telepon').val(data.telepon || '');
    });

    // Inisialisasi Select2 untuk kode barang
    $('.kode-barang-select').select2({
        placeholder: 'Cari kode barang atau nama...',
        ajax: {
            url: '/barang/search', 
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
                        isidus: item.isidus
                        // stok: item.stok
                    }))
                };
            },
            cache: true
        }
    }).on('select2:select', function (e) {
        const data = e.params.data;
        const index = $(this).data('index');

        // Isi otomatis kolom terkait
        $(`input[name="items[${index}][nama_barang]"]`).val(data.nama_barang);
        $(`input[name="items[${index}][harga]"]`).val(data.harga);
        $(`input[name="items[${index}][isidus]"]`).val(data.isidus);
        $(`input[name="items[${index}][stok]"]`).val(data.stok);
    });
}


document.addEventListener("DOMContentLoaded", function () {
    initializeSelect2();
});

$(document).ready(function() {
    $(document).on('change', '.kode-barang-select', function() {
        let selectedKodeBarang = $(this).val();
        let isDuplicate = false;

        // Cek apakah kode barang sudah dipilih di baris sebelumnya
        $('.kode-barang-select').each(function() {
            if ($(this).val() === selectedKodeBarang && $(this)[0] !== event.target) {
                isDuplicate = true;
                return false; // Hentikan loop jika ditemukan duplikat
            }
        });

        if (isDuplicate) {
            alert('Kode barang ini sudah ada di baris sebelumnya! Silakan pilih kode barang lain.');
            $(this).val('').trigger('change'); // Reset pilihan jika duplikat
        } else {
            // Isi otomatis kolom terkait jika tidak duplikat
            const data = $(this).select2('data')[0];
            let currentRow = $(this).closest('tr');

            currentRow.find('input[name$="[nama_barang]"]').val(data.nama_barang);
            currentRow.find('input[name$="[harga]"]').val(data.harga);
            currentRow.find('input[name$="[isidus]"]').val(data.isidus);
        }
    });
});



// Hitung jumlah dan total keseluruhan
document.getElementById('items-body').addEventListener('input', function (event) {
    if (event.target.matches('input[name$="[quantity]"]')) {
        const row = event.target.closest('tr');
        const harga = parseFloat(row.querySelector('input[name$="[harga]"]').value) || 0;
        const quantity = parseInt(event.target.value, 10) || 0;
        const stok = parseInt(row.querySelector('select.kode_barang option:checked').dataset.stok, 10);

        if (quantity > stok) {
            alert('Jumlah barang melebihi stok yang tersedia.');
            event.target.value = stok; // Set ke stok maksimum
        }

        // Hitung jumlah per baris
        const jumlah = harga * quantity;
        row.querySelector('input[name$="[jumlah]"]').value = jumlah.toFixed(2);

        // Hitung total keseluruhan
        calculateTotal();
    }
});

    // Hapus item
document.getElementById('items-body').addEventListener('click', function (event) {
    if (event.target.matches('.delete-item')) {
        event.target.closest('tr').remove();
        calculateTotal(); // Recalculate total after deletion
    }
});
function updatePelangganDetails() {
            const selectedOption = document.querySelector('#Kode_pelanggan option:checked');
            document.getElementById('Nama_pelanggan').value = selectedOption.dataset.nama || '';
            document.getElementById('alamat').value = selectedOption.dataset.alamat || '';
            document.getElementById('telepon').value = selectedOption.dataset.telepon || '';
    }
    // Update detail barang saat kode barang dipilih
    function updateBarangDetails(selectElement) {
    const selectedValue = selectElement.value;

    // Cek apakah kode barang sudah digunakan
    const existingCodes = Array.from(document.querySelectorAll('select.kode_barang'))
        .filter(select => select !== selectElement) // Kecualikan dropdown yang sedang dipilih
        .map(select => select.value);

    if (existingCodes.includes(selectedValue)) {
        alert('Kode barang ini sudah ditambahkan. Pilih kode barang lain.');
        selectElement.value = ''; // Reset pilihan
        return;
    }

    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const row = selectElement.closest('tr');

    // Update detail barang di baris
    row.querySelector('input[name$="[nama_barang]"]').value = selectedOption.dataset.nama || '';
    row.querySelector('input[name$="[harga]"]').value = selectedOption.dataset.harga || '';
    row.querySelector('input[name$="[quantity]"]').value = ''; // Reset quantity
    row.querySelector('input[name$="[jumlah]"]').value = ''; // Reset jumlah
    calculateTotal(); // Recalculate total
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('input[name$="[jumlah]"]').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    // Update span dengan innerText
    document.getElementById('total-amount').innerText = total.toFixed(2);
}


// Hitung quantity berdasarkan qtydus, qtylusin, qtypcs, dan isidus
document.getElementById('items-body').addEventListener('input', function (event) {
    if (
        event.target.matches('input[name$="[qtydus]"]') ||
        event.target.matches('input[name$="[qtylsn]"]') ||
        event.target.matches('input[name$="[qtypcs]"]')
    ) {
        const row = event.target.closest('tr');
        const qtydus = parseInt(row.querySelector('input[name$="[qtydus]"]').value, 10) || 0;
        const qtylsn = parseInt(row.querySelector('input[name$="[qtylsn]"]').value, 10) || 0;
        const qtypcs = parseInt(row.querySelector('input[name$="[qtypcs]"]').value, 10) || 0;

        // Ambil nilai isidus dari dropdown kode barang
        const isidus = parseInt(row.querySelector('select.kode_barang option:checked').dataset.isi, 10) || 1;

        // Hitung total quantity dalam pcs
        const totalQuantity = qtydus * isidus + qtylsn * 12 + qtypcs;

        // Update kolom quantity
        row.querySelector('input[name$="[quantity]"]').value = totalQuantity;

        // Update jumlah
        const harga = parseFloat(row.querySelector('input[name$="[harga]"]').value) || 0;
        const jumlah = harga * totalQuantity;
        row.querySelector('input[name$="[jumlah]"]').value = jumlah.toFixed(2);

        // Hitung total keseluruhan
        calculateTotal();
    }
});

function updateTotal(index) {
    const row = document.querySelectorAll('#items-body tr')[index];
    const dus = parseFloat(row.querySelector('input[name="items[' + index + '][dus]"]').value) || 0;
    const lusin = parseFloat(row.querySelector('input[name="items[' + index + '][lsn]"]').value) || 0;
    const pcs = parseFloat(row.querySelector('input[name="items[' + index + '][pcs]"]').value) || 0;
    const harga = parseFloat(row.querySelector('input[name="items[' + index + '][harga]"]').value) || 0;
    const isidus = parseFloat(row.querySelector('input[name="items[' + index + '][isidus]"]').value) || 0;

    // Asumsikan 1 dus = 12 lusin dan 1 lusin = 12 pcs
    const quantity = dus * isidus + lusin * 12 + pcs;
    row.querySelector('input[name="items[' + index + '][quantity]"]').value = quantity;


    const totalPrice = harga * quantity;
    const jumlah = totalPrice;

    row.querySelector('input[name="items[' + index + '][jumlah]"]').value = jumlah.toFixed(2);

    recalculateTotals();
}


function recalculateTotals() {
    let totalDiscount = 0;
    let total = 0;

    document.querySelectorAll('#items-body tr').forEach((row, index) => {
        const jumlah = parseFloat(row.querySelector('input[name="items[' + index + '][jumlah]"]').value) || 0;
        total += jumlah; // Tambahkan jumlah ke total
    });

    // Misalnya, jika Anda memiliki elemen untuk diskon, update sesuai kebutuhan.
    // document.getElementById('total_discount').innerText = totalDiscount.toFixed(2);

    document.getElementById('total-amount').innerText = total.toFixed(2);
}


function deleteRow(button) {
    button.closest('tr').remove();
    recalculateTotals();
}
</script>

<style>
    /* Membatasi lebar kolom Kode Barang */
    .kode-barang-column {
        max-width: 150px; /* Ubah sesuai kebutuhan */
        word-wrap: break-word; /* Membungkus teks */
        white-space: normal; /* Izinkan teks turun ke bawah */
    }

    /* Agar select tidak terlalu lebar */
    .kode-barang-select {
        width: 100% !important;
        min-width: 100px; /* Sesuaikan dengan kebutuhan */
    }

    /* Menyesuaikan lebar input di dalam tabel */
    .form-control {
        font-size: 12px; /* Ukuran font kecil agar lebih pas */
        padding: 5px; /* Padding agar lebih rapi */
    }
</style>
@endsection