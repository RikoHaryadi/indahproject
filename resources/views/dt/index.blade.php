@extends('layout.mainlayout')
@section('title', 'Daftar Tagihan')

@section('styles')
<!-- Pastikan hanya memuat satu kali Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .kode-barang-select { width: 150px !important; }
    .select2-container { width: 100% !important; }
    .select2-container--default .select2-results__option { white-space: normal; }
</style>
@endsection

@section('content')
<div class="container">
    <h1>Daftar Tagihan</h1>
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

    <form id="penjualan-form" method="POST" action="{{ route('dt.store') }}">
        @csrf
        <div class="row mb-3">
            <label for="kode_sales" class="col-sm-2 col-form-label col-form-label-sm">Pilih Collector:</label>
            <div class="col-sm-3">
                <select id="id_faktur" class="form-control" name="id_colector" required onchange="updateColectorDetails(this)">
                    <option value="" disabled selected>Pilih Collector</option>
                    @foreach($salesmanList as $salesman)
                        <option value="{{ $salesman->kode_sales }}" data-nama="{{ $salesman->nama_salesman }}">
                            {{ $salesman->nama_salesman }} - {{ $salesman->kode_sales }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label for="nama_salesman" class="col-sm-2 col-form-label col-form-label-sm">Nama Collector</label>
            <div class="col-sm-10">
                <input type="text" name="colector" id="nama_salesman" class="form-control" required readonly>
            </div>
        </div>

        <div id="items-container">
            <h3>Items</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID_Faktur</th>
                        <th>Kode Pelanggan</th>
                        <th>Nama Pelanggan</th>
                        <th>Top</th>
                        <th>Total Faktur</th>
                        <th>Bayar</th>
                        <th>Sisa Piutang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="items-body"></tbody>
            </table>
            <button type="button" id="add-item" class="btn btn-primary">Tambah Item</button>
        </div>

        <div class="mt-3">
            <label>Total Keseluruhan</label>
            <input type="text" id="totaldt" name="totaldt" class="form-control" readonly>
        </div>
        <button type="submit" class="btn btn-success mt-3" name="action" value="save">Simpan</button>
        <button type="submit" class="btn btn-secondary mt-3" name="action" value="save_and_print">Simpan & Cetak</button>
    
</div>
@endsection

<!-- Muat jQuery terlebih dahulu -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Lalu muat Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<script>
    // Fungsi untuk update detail Collector
    function updateColectorDetails(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    console.log('Option dipilih:', selectedOption);
    console.log('data-nama:', selectedOption.dataset.nama);
    if (selectedOption) {
        document.getElementById('nama_salesman').value = selectedOption.dataset.nama || '';
    }
}


    $(document).ready(function () {
        // Fungsi untuk inisialisasi Select2 pada elemen dengan kelas .select-faktur
        function initializeSelect2(context) {
            context = context || document;
            $(context).find('.select-faktur').select2({
                placeholder: "Cari ID Faktur...",
                allowClear: true,
                width: '100%'
            });
        }
        
        // Inisialisasi awal (jika sudah ada elemen Select2)
        initializeSelect2();

        // Fungsi untuk menghitung total keseluruhan
        function updateTotalKeseluruhan() {
            let totalKeseluruhan = 0;
            $('.total_faktur').each(function () {
                let value = parseFloat($(this).val()) || 0;
                totalKeseluruhan += value;
            });
            $('#totaldt').val(totalKeseluruhan.toFixed(2));
        }

        // Event delegation: ketika ada perubahan pada dropdown Select2
        $(document).on('change', '.select-faktur', function () {
            var selectedValue = $(this).val();

            // Cek apakah selectedValue sudah ada di baris lain
            var duplicate = false;
            $('.select-faktur').not(this).each(function () {
                if ($(this).val() === selectedValue && selectedValue !== "") {
                    duplicate = true;
                    return false; // break out of loop
                }
            });

            if (duplicate) {
                alert('ID Faktur ini sudah dipilih di baris lain!');
                // Reset nilai pada dropdown saat ini dan trigger perubahan Select2
                $(this).val("").trigger("change");
                return; // hentikan eksekusi event handler lebih lanjut
            }

            var selectedOption = $(this).find(':selected');
            var sisapiutang = selectedOption.data('sisapiutang');

            // Cek jika sisapiutang adalah 0
            if (Number(sisapiutang) === 0) {
                alert("piutang sudah lunas");
                $(this).val("").trigger("change");
                return;
            }

            var row = $(this).closest('tr');
            row.find('.kode_pelanggan').val(selectedOption.data('kode_pelanggan'));
            row.find('.nama_pelanggan').val(selectedOption.data('nama_pelanggan'));
            row.find('.total_faktur').val(selectedOption.data('total'));
            row.find('.sisapiutang').val(selectedOption.data('sisapiutang'));

            updateTotalKeseluruhan();
        });

        // Menambahkan item baru saat tombol "Tambah Item" diklik
        let itemsCount = 0;
        $('#add-item').on('click', function () {
            const rowHtml = `
            <tr>
                <td>
                    <select name="items[${itemsCount}][id_faktur]" class="form-control kode-barang-select id_faktur select-faktur">
                        <option value="">Pilih Faktur</option>
                        @foreach($piutangList as $piutang)
                            <option value="{{ $piutang->id_faktur }}"
                                data-kode_pelanggan="{{ $piutang->kode_pelanggan }}"
                                data-nama_pelanggan="{{ $piutang->nama_pelanggan }}"
                                data-total="{{ $piutang->total }}"
                                data-sisapiutang="{{ $piutang->sisapiutang }}">
                                {{ $piutang->id_faktur }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="items[${itemsCount}][kode_pelanggan]" class="form-control kode_pelanggan" readonly></td>
                <td><input type="text" name="items[${itemsCount}][nama_pelanggan]" class="form-control nama_pelanggan" readonly></td>
                <td><input type="number" name="items[${itemsCount}][top]" class="form-control" value="0"></td>
                <td><input type="number" name="items[${itemsCount}][total]" class="form-control total_faktur" readonly required></td>
                <td><input type="number" name="items[${itemsCount}][bayar]" class="form-control pembayaran" value="0" required></td>
                <td><input type="number" name="items[${itemsCount}][sisapiutang]" class="form-control sisapiutang" readonly required></td>
                <td><button type="button" class="btn btn-danger delete-item">Hapus</button></td>
            </tr>
            `;
            $('#items-body').append(rowHtml);
            itemsCount++;

            // Inisialisasi Select2 pada dropdown yang baru saja ditambahkan
            initializeSelect2($('#items-body tr:last'));
        });

        // Menghapus baris item dan mengupdate total
        $(document).on('click', '.delete-item', function () {
            $(this).closest('tr').remove();
            updateTotalKeseluruhan();
        });
    });
</script>
</form>
