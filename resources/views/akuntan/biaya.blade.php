@extends('layout.mainlayout')
@section('title', 'Input Biaya')
@section('content')
<div class="container">
    <h1>Form Penjualan</h1>
    <form id="penjualan-form" method="POST" action="{{ route('biaya.store') }}">
    @csrf
   
    <div class="row mb-3">
        <label for="kode_transaksi colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Kode Transaksi</label>
        <div class="col-sm-10">
        <input type="text" id="kode_transaksi" name="kode_transaksi" class="form-control" value="{{ $kode_transaksi }}" readonly>
        </div>
    </div>
    <div class="row mb-3">
        <label for="created_at colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Tanggal Transaksi</label>
        <div class="col-sm-10">
        <input type="date" id="created_at" name="created_at" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
    </div>
    
    <div id="items-container">
        <h3>Items</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="items-body"></tbody>
        </table>
        <button type="button" id="add-item" class="btn btn-primary">Tambah Item</button>
    </div>
    <div class="mt-3">
        <label>Total Keseluruhan</label>
        <input type="text" id="total" name="total" class="form-control" readonly>
    </div>
    <button type="submit" class="btn btn-success mt-3" name="action" value="save" id="saveButton">Simpan</button>
    <button type="submit" class="btn btn-success mt-3" name="action" value="save_and_print" id="saveButton">Simpan & Cetak</button>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
</form>

    
</div>
<script>

    let itemsCount = 0;

// Tambah item baru
document.getElementById('add-item').addEventListener('click', function () {
    const existingCodes = Array.from(document.querySelectorAll('select.kode_akun'))
        .map(select => select.value);

    if (existingCodes.includes('')) {
        alert('Harap pilih barang pada baris sebelumnya sebelum menambahkan baris baru.');
        return;
    }

    const row = `<tr>
        <td>
            <select class="kode_akun form-control" name="items[${itemsCount}][kode_akun]" required onchange="updateBarangDetails(this)">
                <option value="" disabled selected>Pilih Akun</option>
                @foreach($kodeakunList as $kodeakun)
                    <option value="{{ $kodeakun->kode_akun }}"
                        data-nama="{{ $kodeakun->nama_akun }}">
                        {{ $kodeakun->kode_akun }} - {{ $kodeakun->nama_akun }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="items[${itemsCount}][nama_akun]" class="form-control" readonly></td>
        <td><input type="number" name="items[${itemsCount}][jumlah]" class="form-control jumlah-input" required></td>
        <td><input type="text" name="items[${itemsCount}][keterangan]" class="form-control" required></td>
        <td><button type="button" class="btn btn-danger delete-item">Hapus</button></td>
    </tr>`;
    document.getElementById('items-body').insertAdjacentHTML('beforeend', row);

    // Tambahkan event listener ke input jumlah
    const newJumlahInput = document.querySelector(`input[name="items[${itemsCount}][jumlah]"]`);
    newJumlahInput.addEventListener('input', calculateTotal);

    itemsCount++;
});

    // Panggil checkItems setiap kali ada perubahan di form
    document.addEventListener('input', checkItems);

document.getElementById('items-body').addEventListener('click', function (event) {
    if (event.target.matches('.delete-item')) {
        event.target.closest('tr').remove();
        calculateTotal(); // Hitung ulang total setelah menghapus baris
    }
});

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('input[name$="[jumlah]"]').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('total').value = total.toFixed(2);
}


    // Hapus item
document.getElementById('items-body').addEventListener('click', function (event) {
    if (event.target.matches('.delete-item')) {
        event.target.closest('tr').remove();
        calculateTotal(); // Recalculate total after deletion
    }
});
    function updateBarangDetails(selectElement) {
    const selectedValue = selectElement.value;

    // Cek apakah kode barang sudah digunakan
    const existingCodes = Array.from(document.querySelectorAll('select.kode_akun'))
        .filter(select => select !== selectElement) // Kecualikan dropdown yang sedang dipilih
        .map(select => select.value);



    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const row = selectElement.closest('tr');

    // Update detail barang di baris
    row.querySelector('input[name$="[nama_akun]"]').value = selectedOption.dataset.nama || '';
    row.querySelector('input[name$="[jumlah]"]').value = ''; // Reset jumlah
    row.querySelector('input[name$="[keterangan]"]').value = ''; // Reset jumlah
    calculateTotal(); // Recalculate total

}
function calculateTotal() {
    let total = 0;
    document.querySelectorAll('input[name$="[jumlah]"]').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('total').value = total.toFixed(2);
}

document.getElementById('saveButton').addEventListener('click', function(event) {
        // Tunggu form disubmit lalu kosongkan semua input
        event.preventDefault(); // Mencegah form refresh secara default
        const form = document.getElementById('penjualan-form');

        // Lakukan submit form secara manual
        form.submit();

        // Setelah submit, kosongkan semua input dalam form
        form.reset();
    });
</script>
@endsection