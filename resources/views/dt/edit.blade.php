@extends('layout.mainlayout')
@section('title', 'Edit Daftar Tagihan')

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
    {{-- <pre>{{ print_r($dt->toArray(), true) }}</pre> --}}
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

    <form id="penjualan-form" method="POST" action="{{ route('dt.update', $dt->id) }}">
        @csrf
          @method('PUT')
        <div class="row mb-3">
            <label for="kode_sales" class="col-sm-2 col-form-label col-form-label-sm">Pilih Collector:</label>
            <div class="col-sm-3">
               <select id="id_colector" class="form-control" name="id_colector" required onchange="updateColectorDetails(this)">
                    <option value="" disabled selected>Pilih Collector</option>
                    @foreach($salesmanList as $salesman)
                        <option value="{{ $salesman->kode_sales }}" data-nama="{{ $salesman->nama_salesman }}"
                            @if($salesman->kode_sales == $dt->id_colector) selected @endif>
                            {{ $salesman->nama_salesman }} - {{ $salesman->kode_sales }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label for="nama_salesman" class="col-sm-2 col-form-label col-form-label-sm">Nama Collector</label>
            <div class="col-sm-10">
              
                <input type="text" name="colector" id="nama_salesman" class="form-control" required readonly value="{{ old('colector', $dt->colector ?? '') }}">

            </div>
        </div>

       
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

        {{-- Total Keseluruhan --}}
        <div class="mt-3">
            <label>Total Keseluruhan</label>
            <input type="text" id="totaldt" name="totaldt" class="form-control" readonly>
        </div>
        <button type="submit" class="btn btn-success mt-3" name="action" value="save">Simpan</button>
    </div>
@endsection

@push('scripts')
 
<script>
$(document).ready(function () {
    

    const detailItems = @json($dt->ddt);
    const selectedCollector = $('#id_colector option:selected');
    $('#nama_salesman').val(selectedCollector.data('nama') || '');

    // 🔧 DEKLARASI di luar, bukan di dalam loop
    let itemsCount = 0;

    // 🧾 Tampilkan item lama ke tabel
    detailItems.forEach(function (item) {
        const row = `
        <tr>
            <td>
                ${item.id_faktur}
                <input type="hidden" name="items[${itemsCount}][id_faktur]" value="${item.id_faktur}">
            </td>
            <td>
                ${item.kode_pelanggan}
                <input type="hidden" name="items[${itemsCount}][kode_pelanggan]" value="${item.kode_pelanggan}">
            </td>
            <td>
                ${item.nama_pelanggan}
                <input type="hidden" name="items[${itemsCount}][nama_pelanggan]" value="${item.nama_pelanggan}">
            </td>
            <td>
                <input type="number" name="items[${itemsCount}][top]" class="form-control" value="${item.top}" required>
            </td>
            <td>
                <input type="number" name="items[${itemsCount}][total]" class="form-control total-field" value="${item.total}" readonly required>
            </td>
            <td>
                <input type="number" name="items[${itemsCount}][bayar]" class="form-control bayar-field" value="${item.bayar}" oninput="updateSisa(this)" required>
            </td>
            <td>
                <input type="number" name="items[${itemsCount}][sisapiutang]" class="form-control sisa-field" value="${item.sisapiutang}" readonly required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm delete-item">Hapus</button>
            </td>
        </tr>`;
        $('#items-body').append(row);
        itemsCount++;
    });

    hitungTotalBayar();

    // Fungsi perhitungan sisa piutang per baris
    window.updateSisa = function(input) {
        const row = $(input).closest('tr');
        const total = parseFloat(row.find('.total-field').val()) || 0;
        const bayar = parseFloat($(input).val()) || 0;
        const sisa = total - bayar;
        row.find('.sisa-field').val(sisa);
        hitungTotalBayar();
    }

    function hitungTotalBayar() {
        let total = 0;
        $('.total-field').each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        $('.total_faktur').each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        $('#totaldt').val(total.toFixed(2));
    }

    // Tambah item baru
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
        </tr>`;
        $('#items-body').append(rowHtml);
        initializeSelect2($('#items-body tr:last'));
        itemsCount++;
    });

    function initializeSelect2(row) {
        row.find('.select-faktur').select2();

        row.find('.select-faktur').on('select2:select', function (e) {
            const selectedOption = $(this).find('option:selected');
            const idFaktur = selectedOption.val();
            const selectEl = $(this);

            // Validasi faktur dobel
            $.get('/check-faktur-exists', { id_faktur: idFaktur }, function (response) {
                if (response.exists) {
                    alert('❌ Faktur sudah ada dan belum diproses.');
                    selectEl.val(null).trigger('change');
                    const parentRow = selectEl.closest('tr');
                    parentRow.find('input').val('');
                    return;
                }

                const row = selectEl.closest('tr');
                row.find('.kode_pelanggan').val(selectedOption.data('kode_pelanggan'));
                row.find('.nama_pelanggan').val(selectedOption.data('nama_pelanggan'));
                row.find('.total_faktur').val(selectedOption.data('total'));
                row.find('.pembayaran').val(0);
                row.find('.sisapiutang').val(selectedOption.data('sisapiutang'));
                hitungTotalBayar();
            });
        });
    }

    // Hapus baris item
    $(document).on('click', '.delete-item', function () {
        $(this).closest('tr').remove();
        hitungTotalBayar();
    });
});
</script>
@endpush
