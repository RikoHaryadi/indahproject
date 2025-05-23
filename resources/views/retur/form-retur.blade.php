@extends('layout.mainlayout')

@section('title', 'Retur Penjualan')

@section('content')
<div class="container py-4">
    <h1>Form Retur Penjualan</h1>
    {{-- pesan sukses / error --}}
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

    {{-- Pilih No. Faktur --}}
    <div class="row mb-3">
        <label for="no_faktur" class="col-sm-2 col-form-label">No. Faktur:</label>
        <div class="col-sm-6">
            <select name="no_faktur" id="no_faktur" class="form-control kode-barang-select" style="width:100%;">
                <option value="">-- Cari No. Faktur --</option>
            </select>
        </div>
    </div>

    {{-- Header dan detail penjualan akan muncul setelah faktur dipilih --}}
    <div id="header-penjualan" style="display: none;">
      <!-- isi header: kode_pelanggan, nama_pelanggan, alamat ... -->
    </div>

    <form action="{{ route('retur.submit') }}" method="POST" id="form-retur">
      @csrf
      <input type="hidden" name="penjualan_id" id="penjualan_id">
      <div id="detail-penjualan" style="display: none;">
        {{-- Tabel detail item akan di‐append --}}
        <table class="table table-bordered table-sm">
          <thead class="table-light"><tr>
              <th>#</th>
              <th>Kode Barang</th>
              <th>Nama Barang</th>
              <th>Harga</th>
              <th>Dus</th>
              <th>Retur Dus</th>
              <th>Lusin</th>
              <th>Retur Lusin</th>
              <th>Pcs</th>
              <th>Retur Pcs</th>
              <th>Isi Dus</th>
              <th>Qty (Total PCS)</th>
          </tr></thead>
          <tbody id="items-body-retur"></tbody>
        </table>
        <button type="submit" class="btn btn-success mt-3">Proses Retur</button>
      </div>
    </form>
</div>



<!-- jQuery (WAJIB lebih dulu) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS + JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!-- Script kustom -->
<script>
  $(document).ready(function() {
    console.log("Form retur siap!");
     $('#no_faktur').select2({
    placeholder: 'Cari kode barang atau nama...',
    ajax: {
      url: '/penjualan/search-faktur',
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(item => ({
          id: item.id,
          text: `${item.id}`,
            id_faktur: item.id_faktur, // Tambahkan id_faktur sebagai properti terpisah
        nama_pelanggan: item.nama_pelanggan,
        total: item.total
        }))
      }),
      cache: true
    }
  })
  .on('select2:select', function(e) {
   const data = e.params.data;

   axios.get(`/retur-penjualan/details/${data.id}`)
   .then(response => {
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
   })
   .catch(error => {
       console.error("Gagal ambil detail faktur: ", error);
       alert("Gagal mengambil detail penjualan. Coba lagi.");
   });

   $('#id_faktur_display').val(data.id_faktur);
   $('#nama_pelanggan_display').val(data.nama_pelanggan);
});


    function populateDetailItems(details) {
      var $tbody = $('#items-body-retur');
      $tbody.empty();
      details.forEach(function(det, idx) {
        var row = `<tr>
          <td>${idx + 1}</td>
          <td><input type="hidden" name="items[${idx}][detail_id]" value="${det.id}">
              <input type="text" name="items[${idx}][kode_barang]" class="form-control form-control-sm" value="${det.kode_barang}" readonly></td>
          <td><input type="text" name="items[${idx}][nama_barang]" class="form-control form-control-sm" value="${det.nama_barang}" readonly></td>
          <td><input type="number" name="items[${idx}][harga]" class="form-control form-control-sm" value="${det.harga}" readonly></td>
          <td><input type="number" name="items[${idx}][dus]" class="form-control form-control-sm" value="${det.dus}" readonly></td>
          <td><input type="number" name="items[${idx}][retur_dus]" class="form-control form-control-sm" min="0" max="${det.dus}" value="0"></td>
          <td><input type="number" name="items[${idx}][lusin]" class="form-control form-control-sm" value="${det.lusin}" readonly></td>
          <td><input type="number" name="items[${idx}][retur_lusin]" class="form-control form-control-sm" min="0" max="${det.lusin}" value="0"></td>
          <td><input type="number" name="items[${idx}][pcs]" class="form-control form-control-sm" value="${det.pcs}" readonly></td>
          <td><input type="number" name="items[${idx}][retur_pcs]" class="form-control form-control-sm" min="0" max="${det.pcs}" value="0"></td>
          <td><input type="number" name="items[${idx}][isidus]" class="form-control form-control-sm" value="${det.isidus}" readonly></td>
          <td><input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm" value="${det.quantity}" readonly></td>
        </tr>`;
        $tbody.append(row);
      });
    }
  });
</script>
@endsection