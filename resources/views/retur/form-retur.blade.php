@extends('layout.mainlayout')
@section('title', 'Retur Penjualan')

@section('content')
<div class="container py-4">
    <h1>Form Retur Penjualan</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul></div>
    @endif

    <div class="row mb-3">
        <label for="no_faktur" class="col-sm-2 col-form-label">No. Faktur:</label>
        <div class="col-sm-6">
            <select name="no_faktur" id="no_faktur" class="form-control kode-barang-select" style="width:100%;">
                <option value="">-- Cari No. Faktur --</option>
            </select>
        </div>
    </div>

    <div id="header-penjualan" style="display: none;"></div>
  @if(session('retur_error'))
    <div class="alert alert-danger">
        <strong>Kesalahan:</strong> {{ session('retur_error') }}
    </div>
@endif
    <form action="{{ route('retur.submit') }}" method="POST" id="form-retur">
        @csrf
        <input type="hidden" name="penjualan_id" id="penjualan_id" value="{{ old('penjualan_id') }}">
        <div id="detail-penjualan" style="display: none;">
            <table class="table table-bordered table-sm">
               <thead class="table-light">
    <tr style="font-size: 10px">
        <th>#</th>
        <th>Kode Barang</th>
        <th>Nama Barang</th>
        <th>Harga</th>
        <th>Disc1 (%)</th>
        <th>Disc2 (%)</th>
        <th>Disc3 (%)</th>
        <th>Disc4 (%)</th>
        <th>Dus</th>
        <th>Lusin</th>
        <th>Pcs</th>
        <th>Retur Dus</th>
        <th>Retur Lusin</th>
        <th>Retur Pcs</th>
        <th>Isi Dus</th>
        <th>Qty (Total PCS)</th>
    </tr>
</thead>

                <tbody id="items-body-retur"></tbody>
            </table>
            <div class="mt-3">
    <h5>Rangkuman Retur:</h5>
    <p><strong>Total Diskon:</strong> <span id="total-diskon">Rp 0</span></p>
    <p><strong>Total Retur Setelah Diskon:</strong> <span id="total-retur">Rp 0</span></p>
    <button type="button" id="btn-retur-full" class="btn btn-warning mt-2">Retur Full</button>

</div>

            <button type="submit" class="btn btn-success mt-3">Proses Retur</button>
        </div>
    </form>
</div>

{{-- Script --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#no_faktur').select2({
        placeholder: 'Cari berdasarkan ID, No Faktur, atau Nama Pelanggan...',
        ajax: {
            url: '/penjualan/search-faktur',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.map(item => ({
                    id: item.id,
                    text: `${item.id_faktur} - ${item.nama_pelanggan}`
                }))
            })
        }
    }).on('select2:select', function(e) {
        const data = e.params.data;

        axios.get(`/retur-penjualan/details/${data.id}`).then(response => {
            const header = response.data.penjualan;
            const details = response.data.details;

            $('#header-penjualan').html(`
                <div class="card p-3 mb-3">
                    <strong>Kode Pelanggan:</strong> ${header.kode_pelanggan}<br>
                    <strong>Nama Pelanggan:</strong> ${header.nama_pelanggan}<br>
                    <strong>Alamat:</strong> ${header.alamat}
                </div>
            `).show();

            $('#penjualan_id').val(header.id);
            $('#detail-penjualan').show();
            populateDetailItems(details);
            calculateTotals();
            
        });
    });

    $('#form-retur').on('submit', function(e) {
        let totalRetur = 0;
        $('#items-body-retur tr').each(function() {
            const returDus = parseInt($(this).find('[name$="[retur_dus]"]').val()) || 0;
            const returLusin = parseInt($(this).find('[name$="[retur_lusin]"]').val()) || 0;
            const returPcs = parseInt($(this).find('[name$="[retur_pcs]"]').val()) || 0;
            totalRetur += returDus + returLusin + returPcs;
        });

        if (totalRetur === 0) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Input Retur Kosong!', text: 'Silakan isi minimal satu kolom retur.' });
        }
    });

    function populateDetailItems(details) {
        var $tbody = $('#items-body-retur');
        $tbody.empty();

        details.forEach(function(det, idx) {
            const totalQty = (det.dus * det.isidus) + (det.lusin * 12) + det.pcs;
            const isError = det.kode_barang === "{{ session('fokus_kode') }}";

            var row = `<tr data-kode="${det.kode_barang}" class="${isError ? 'row-error' : ''}">
                <td style="font-size: 10px">${idx + 1}</td>
                <td><input type="hidden" name="items[${idx}][detail_id]" value="${det.id}">
                    <input type="text" name="items[${idx}][kode_barang]" class="form-control form-control-sm" value="${det.kode_barang}" readonly></td>
                <td><input type="text" name="items[${idx}][nama_barang]" class="form-control form-control-sm" value="${det.nama_barang}" readonly></td>
                <td><input type="number" name="items[${idx}][harga]" class="form-control form-control-sm" value="${det.harga}" readonly></td>
                 <td><input type="number" name="items[${idx}][disc1]" class="form-control form-control-sm" value="${det.disc1 ?? 0}" readonly></td>
                <td><input type="number" name="items[${idx}][disc2]" class="form-control form-control-sm" value="${det.disc2 ?? 0}" readonly></td>
                <td><input type="number" name="items[${idx}][disc3]" class="form-control form-control-sm" value="${det.disc3 ?? 0}" readonly></td>
                <td><input type="number" name="items[${idx}][disc4]" class="form-control form-control-sm" value="${det.disc4 ?? 0}" readonly></td>
                <td><input type="number" name="items[${idx}][dus]" class="form-control form-control-sm" value="${det.dus}" readonly></td>
                <td><input type="number" name="items[${idx}][lusin]" class="form-control form-control-sm" value="${det.lusin}" readonly></td>
                <td><input type="number" name="items[${idx}][pcs]" class="form-control form-control-sm" value="${det.pcs}" readonly></td>
                <td>
                    <input type="number" name="items[${idx}][retur_dus]" class="form-control form-control-sm ${isError ? 'is-invalid' : ''}" min="0" value="${det.retur_dus ?? 0}">
                    ${isError ? `<div class="invalid-feedback d-block">Retur melebihi jumlah</div>` : ''}
                </td>
                <td><input type="number" name="items[${idx}][retur_lusin]" class="form-control form-control-sm ${isError ? 'is-invalid' : ''}" min="0" value="${det.retur_lusin ?? 0}"></td>
                <td><input type="number" name="items[${idx}][retur_pcs]" class="form-control form-control-sm ${isError ? 'is-invalid' : ''}" min="0" value="${det.retur_pcs ?? 0}"></td>
                <td><input type="number" name="items[${idx}][isidus]" class="form-control form-control-sm" value="${det.isidus}" readonly></td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm" value="${totalQty}" readonly title="Max Retur: ${totalQty} pcs"></td>
            </tr>`;
            $tbody.append(row);
        });
        calculateTotals();
    }
    function calculateTotals() {
    let totalDiskon = 0;
    let totalReturSetelahDiskon = 0;

    $('#items-body-retur tr').each(function() {
        const harga = parseFloat($(this).find('[name$="[harga]"]').val()) || 0;
        const isidus = parseInt($(this).find('[name$="[isidus]"]').val()) || 1;

        const returDus = parseInt($(this).find('[name$="[retur_dus]"]').val()) || 0;
        const returLusin = parseInt($(this).find('[name$="[retur_lusin]"]').val()) || 0;
        const returPcs = parseInt($(this).find('[name$="[retur_pcs]"]').val()) || 0;

        const qtyRetur = (returDus * isidus) + (returLusin * 12) + returPcs;

        const disc1 = parseFloat($(this).find('[name$="[disc1]"]').val()) || 0;
        const disc2 = parseFloat($(this).find('[name$="[disc2]"]').val()) || 0;
        const disc3 = parseFloat($(this).find('[name$="[disc3]"]').val()) || 0;
        const disc4 = parseFloat($(this).find('[name$="[disc4]"]').val()) || 0;

        let subtotal = harga * qtyRetur;
        let afterDisc1 = subtotal - (subtotal * disc1 / 100);
        let afterDisc2 = afterDisc1 - (afterDisc1 * disc2 / 100);
        let afterDisc3 = afterDisc2 - (afterDisc2 * disc3 / 100);
        let afterDisc4 = afterDisc3 - (afterDisc3 * disc4 / 100);

        let nilaiDiskon = subtotal - afterDisc4;

        totalDiskon += nilaiDiskon;
        totalReturSetelahDiskon += afterDisc4;
    });

    $('#total-diskon').text(`Rp ${totalDiskon.toLocaleString('id-ID')}`);
    $('#total-retur').text(`Rp ${totalReturSetelahDiskon.toLocaleString('id-ID')}`);
}
 // Jika ada perubahan jumlah retur, hitung ulang
    $(document).on('input', '[name$="[retur_dus]"], [name$="[retur_lusin]"], [name$="[retur_pcs]"]', function() {
        calculateTotals();
    });
    

    // Jika sebelumnya gagal retur → reload detail otomatis
    @if (old('penjualan_id') && old('items'))
        axios.get(`/retur-penjualan/details/{{ old('penjualan_id') }}`).then(response => {
            const header = response.data.penjualan;
            const details = response.data.details;
            const oldItems = @json(old('items'));

            const merged = details.map((d, i) => ({
                ...d,
                retur_dus: oldItems[i]?.retur_dus ?? 0,
                retur_lusin: oldItems[i]?.retur_lusin ?? 0,
                retur_pcs: oldItems[i]?.retur_pcs ?? 0
            }));

            $('#header-penjualan').html(`
                <div class="card p-3 mb-3">
                    <strong>Kode Pelanggan:</strong> ${header.kode_pelanggan}<br>
                    <strong>Nama Pelanggan:</strong> ${header.nama_pelanggan}<br>
                    <strong>Alamat:</strong> ${header.alamat}
                </div>
            `).show();

            $('#penjualan_id').val(header.id);
            $('#detail-penjualan').show();
            populateDetailItems(merged);
        });
    @endif

    @if(session('retur_error') && session('fokus_kode'))
        setTimeout(() => {
            const fokusKode = "{{ session('fokus_kode') }}";
            const row = document.querySelector(`tr[data-kode="${fokusKode}"]`);
            if (row) {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Retur',
                text: "{{ session('retur_error') }}"
            });
        }, 1000);
    @endif
});

// Tombol Retur Full
$(document).on('click', '#btn-retur-full', function() {
    $('#items-body-retur tr').each(function() {
        const dus = parseInt($(this).find('[name$="[dus]"]').val()) || 0;
        const lusin = parseInt($(this).find('[name$="[lusin]"]').val()) || 0;
        const pcs = parseInt($(this).find('[name$="[pcs]"]').val()) || 0;

        $(this).find('[name$="[retur_dus]"]').val(dus);
        $(this).find('[name$="[retur_lusin]"]').val(lusin);
        $(this).find('[name$="[retur_pcs]"]').val(pcs);
    });

    calculateTotals(); // update total retur
});
$('#btn-retur-full').on('click', function() {
    Swal.fire({
        title: 'Retur Semua Barang?',
        text: "Semua barang akan diretur sesuai jumlah di faktur.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Retur Semua',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#items-body-retur tr').each(function() {
                const dus = parseInt($(this).find('[name$="[dus]"]').val()) || 0;
                const lusin = parseInt($(this).find('[name$="[lusin]"]').val()) || 0;
                const pcs = parseInt($(this).find('[name$="[pcs]"]').val()) || 0;

                $(this).find('[name$="[retur_dus]"]').val(dus);
                $(this).find('[name$="[retur_lusin]"]').val(lusin);
                $(this).find('[name$="[retur_pcs]"]').val(pcs);
            });

            // 🚀 Tambahkan ini agar total langsung dihitung ulang
            calculateTotals();
        }
    });
});


</script>

<style>
    .row-error {
        background-color: #ffe0e0;
        border: 2px solid red;
    }
    input.is-invalid {
        border-color: red;
        background-color: #fff5f5;
    }
    .form-control {
        font-size: 10px;
    }
</style>
@endsection
