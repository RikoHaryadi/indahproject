@extends('layout.mainlayout')
@section('title', 'Grn')
@section('content')
<div class="container">
    <h1>Form Penjualan</h1>
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    <form id="penjualan-form" method="POST" action="{{ route('grn.store') }}">
        @csrf
        <div class="row mb-3">
            <label for="kode_suplier" class="col-sm-2 col-form-label col-form-label-sm">Kode Pelanggan:</label>
            <div class="col-sm-3">
                <select id="kode_suplier" class="kode_suplier form-control" name="kode_suplier" required onchange="updatePelangganDetails(this)">
                    <option value="" disabled selected>Pilih Supplier</option>
                    @foreach($supplierList as $supplier)
                        <option value="{{ $supplier->Kode_suplier }}"
                            data-nama="{{ $supplier->Nama_suplier }}"
                            data-alamat="{{ $supplier->alamat }}"
                            data-telepon="{{ $supplier->telepon }}">
                            {{ $supplier->Kode_suplier }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label for="nama_suplier" class="col-sm-2 col-form-label col-form-label-sm">Nama Supplier</label>
            <div class="col-sm-10">
                <input type="text" name="nama_suplier" id="nama_suplier" class="form-control" required>
            </div>    
        </div>
        <div class="row mb-3">
            <label for="alamat" class="col-sm-2 col-form-label col-form-label-sm">Alamat</label>
            <div class="col-sm-10">
                <input type="text" name="alamat" id="alamat" class="form-control" required>
            </div>  
        </div>
        <div class="row mb-3">
            <label for="telepon" class="col-sm-2 col-form-label col-form-label-sm">Telepon</label>
            <div class="col-sm-10">
                <input type="text" name="telepon" id="telepon" class="form-control" required>
            </div>    
        </div>

        <div id="items-container">
            <h3>Items</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th>Qty Dus</th>
                        <th>Qty Lsn</th>
                        <th>Qty Pcs</th>
                        <th>Quantity</th>
                        <th>Jumlah</th>
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
        <button type="submit" class="btn btn-success mt-3" name="action" value="save">Simpan</button>
        <button type="submit" class="btn btn-secondary mt-3" name="action" value="save_and_print" target="_blank">Simpan & Cetak</button>
        @if($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif    
    </form>
</div>

<script>
    // Deklarasi variabel hanya sekali
    let itemsCount = 0;

    // Fungsi auto-isi supplier
    function updatePelangganDetails(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        if (selectedOption) {
            document.getElementById('nama_suplier').value = selectedOption.dataset.nama || '';
            document.getElementById('alamat').value = selectedOption.dataset.alamat || '';
            document.getElementById('telepon').value = selectedOption.dataset.telepon || '';
        }
    }

    // Fungsi update detail barang ketika produk dipilih (pada select)
    function updateBarangDetails(selectElement) {
        const selectedValue = selectElement.value;
        // Cek duplikasi jika diperlukan (opsional)
        const existingCodes = Array.from(document.querySelectorAll('select.kode-barang-select'))
            .filter(select => select !== selectElement)
            .map(select => select.value);
        if (existingCodes.includes(selectedValue)) {
            alert('Kode barang ini sudah ditambahkan. Pilih kode barang lain.');
            selectElement.value = '';
            return;
        }
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const row = selectElement.closest('tr');
        // Karena kita ingin tampilan tidak melebar, kita akan menampilkan kode barang dalam select yang lebarnya sudah dibatasi
        // dan nama barang akan ditampilkan di textarea
        row.querySelector('textarea[name$="[nama_barang]"]').value = selectedOption.dataset.nama || '';
        row.querySelector('input[name$="[harga]"]').value = selectedOption.dataset.harga || '';
        // Trigger perhitungan ulang (misal jika Qty Dus sudah ada)
        const qtyDusEl = row.querySelector('input[name$="[qtydus]"]');
        if(qtyDusEl) {
            qtyDusEl.dispatchEvent(new Event('input'));
        }
        calculateTotal();
    }

    // Event listener untuk tombol "Tambah Item"
    document.getElementById('add-item').addEventListener('click', function () {
    // Periksa apakah sudah ada baris di dalam #items-body
    let lastRow = document.querySelector("#items-body tr:last-child");
    if(lastRow) {
        // Ambil nilai kode barang dari select di baris terakhir
        let kodeBarang = lastRow.querySelector("select.kode-barang-select").value;
        // Ambil nilai quantity dari input di baris terakhir (dengan name berakhiran [quantity])
        let quantityInput = lastRow.querySelector("input[name$='[quantity]']");
        let quantity = quantityInput ? parseFloat(quantityInput.value) : 0;
        
        // Jika kode barang belum dipilih atau quantity masih nol, tampilkan pesan dan hentikan proses penambahan
        if(!kodeBarang || quantity === 0) {
            alert("Harap pilih kode barang dan isi quantity sebelum menambahkan baris baru.");
            return;
        }
    }
    
    // Jika pengecekan sudah terpenuhi, lanjutkan menambahkan baris baru
    const row = `<tr>
        <td class="kode-barang-column">
            <select name="items[${itemsCount}][kode_barang]" class="form-control kode-barang-select" data-index="${itemsCount}" style="font-size: 10px;" onchange="updateBarangDetails(this)">
                <option value="">Pilih Barang</option>
                @foreach($masterbarangList as $masterbarang)
                    <option value="{{ $masterbarang->kode_barang }}"
                        data-nama="{{ $masterbarang->nama_barang }}"
                        data-harga="{{ $masterbarang->hargapcs }}"
                        data-isi="{{ $masterbarang->isidus }}"
                        data-stok="{{ $masterbarang->stok }}">
                        {{ $masterbarang->kode_barang }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <textarea name="items[${itemsCount}][nama_barang]" class="form-control" readonly style="font-size: 12px; resize: vertical; overflow: hidden; white-space: pre-wrap; width: 200px;"></textarea>
        </td>
        <td><input type="number" name="items[${itemsCount}][harga]" class="form-control" readonly></td>
        <td><input type="number" name="items[${itemsCount}][qtydus]" class="form-control" value="0" required></td>
        <td><input type="number" name="items[${itemsCount}][qtylsn]" class="form-control" value="0" required></td>
        <td><input type="number" name="items[${itemsCount}][qtypcs]" class="form-control" value="0" required></td>
        <td><input type="number" name="items[${itemsCount}][quantity]" class="form-control" readonly required></td>
        <td><input type="text" name="items[${itemsCount}][jumlah]" class="form-control" readonly></td>
        <td><button type="button" class="btn btn-danger delete-item">Hapus</button></td>
    </tr>`;

    document.getElementById('items-body').insertAdjacentHTML('beforeend', row);

    // Inisialisasi select2 untuk select yang baru ditambahkan (pastikan jQuery & select2 sudah ter-load)
    $(`select[name="items[${itemsCount}][kode_barang]"]`).select2({
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
                    }))
                };
            },
            cache: true
        }
    }).on('select2:select', function (e) {
        const data = e.params.data;
        const index = $(this).data('index');
        // Update nama_barang di textarea dan harga
        $(`textarea[name="items[${index}][nama_barang]"]`).val(data.nama_barang);
        $(`input[name="items[${index}][harga]"]`).val(data.harga);
        // Trigger perhitungan dengan memicu event input pada Qty Dus
        $(`input[name="items[${index}][qtydus]"]`).trigger('input');
    });

    itemsCount++;
});


    // Event delegation untuk perhitungan otomatis saat mengubah Qty Dus, Qty Lsn, atau Qty Pcs
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
            
            // Ambil nilai isidus dari option produk (jika belum dipilih, default 1)
            const select = row.querySelector('select.kode-barang-select');
            const selectedOption = select ? select.options[select.selectedIndex] : null;
            const isidus = (selectedOption && selectedOption.dataset.isi) ? parseInt(selectedOption.dataset.isi, 10) || 1 : 1;
    
            // Hitung total quantity (dus * isidus + lusin * 12 + pcs)
            const totalQuantity = qtydus * isidus + qtylsn * 12 + qtypcs;
            row.querySelector('input[name$="[quantity]"]').value = totalQuantity;
    
            // Hitung subtotal (jumlah = harga * quantity)
            const harga = parseFloat(row.querySelector('input[name$="[harga]"]').value) || 0;
            const jumlah = harga * totalQuantity;
            row.querySelector('input[name$="[jumlah]"]').value = jumlah.toFixed(2);
    
            calculateTotal();
        }
    });

    // Event delegation untuk input langsung pada field quantity (jika user mengubahnya secara manual)
    document.getElementById('items-body').addEventListener('input', function (event) {
        if (event.target.matches('input[name$="[quantity]"]')) {
            const row = event.target.closest('tr');
            const harga = parseFloat(row.querySelector('input[name$="[harga]"]').value) || 0;
            const quantity = parseInt(event.target.value, 10) || 0;
            const jumlah = harga * quantity;
            row.querySelector('input[name$="[jumlah]"]').value = jumlah.toFixed(2);
            calculateTotal();
        }
    });

    // Event delegation untuk menghapus baris item
    document.getElementById('items-body').addEventListener('click', function (event) {
        if (event.target.matches('.delete-item')) {
            event.target.closest('tr').remove();
            calculateTotal();
        }
    });

    // Fungsi untuk menghitung total keseluruhan dari seluruh baris
    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('input[name$="[jumlah]"]').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('total').value = total.toFixed(2);
    }
</script>
<style>
    /* Batasi lebar select kode barang */
    .kode-barang-select {
        width: 150px !important;
    }
    /* Untuk Select2 agar opsi teks dapat membungkus */
    .select2-container--default .select2-results__option {
        white-space: normal;
    }
</style>
@endsection
