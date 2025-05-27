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
      {{-- Pilih Sales --}}
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">Kode Sales:</label>
      <div class="col-sm-3">
        <select id="kode_sales" name="kode_sales" class="form-control" @if($userLevel==1) disabled @endif>
          <option value="" disabled {{ $userLevel!=1?'selected':'' }}>Pilih Sales</option>
          @foreach($salesmanList as $s)
            <option 
              value="{{ $s->kode_sales }}" 
              data-nama="{{ $s->nama_salesman }}"
              {{ $userLevel==1 && $s->kode_sales==$userSales ? 'selected' : '' }}>
              {{ $s->kode_sales }} – {{ $s->nama_salesman }}
            </option>
          @endforeach
        </select>
         @if($userLevel==1)
      {{-- backup field supaya tetap dikirim --}}
      <input type="hidden" name="kode_sales" value="{{ $userSales }}">
    @endif
      </div>
      <label class="col-sm-2 col-form-label">Nama Salesman:</label>
      <div class="col-sm-3">
        <input type="text" id="nama_salesman" name="nama_salesman" class="form-control" readonly>
      </div>
    </div>

 
    <div class="row mb-3">
        <label for="created_at colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Tanggal Transaksi</label>
        <div class="col-sm-3">
        <input type="date" id="created_at" name="created_at" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
    </div>
{{-- Pilih Pelanggan --}}
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">Pelanggan:</label>
      <div class="col-sm-3">
        <select id="kode_pelanggan" name="kode_pelanggan" class="form-control">
          <option value="" disabled selected>Pilih Pelanggan</option>
        </select>
      </div>
      <label class="col-sm-2 col-form-label">Nama Pelanggan:</label>
      <div class="col-sm-3">
        <input type="text" id="nama_pelanggan" name="nama_pelanggan" class="form-control" readonly>
      </div>
    </div>

    {{-- Alamat & Telepon --}}
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">Alamat:</label>
      <div class="col-sm-3">
        <input type="text" id="alamat" class="form-control" readonly>
      </div>
      <label class="col-sm-2 col-form-label">Telepon:</label>
      <div class="col-sm-3">
        <input type="text" id="telepon" class="form-control" readonly>
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
<!-- Modal Pilih Barang -->
<div class="modal fade" id="barangModal" tabindex="-1" aria-labelledby="barangModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pilih Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="search-barang" class="form-control mb-3" placeholder="Cari nama atau kode barang...">
        <table class="table table-sm table-hover" id="table-barang">
          <thead>
            <tr style="font-size: 12px;">
              <th>Kode</th>
              <th>Nama</th>
              <th>Harga</th>
              <th>Isi Dus</th>
              <th>Pilih</th>
            </tr>
          </thead>
          <tbody id="barang-list">
            {{-- Daftar barang akan diisi via JS --}}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>
{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  let currentIndexForBarang = null;

function openBarangModal(index) {
  currentIndexForBarang = index;
  $('#barangModal').modal('show');
  loadBarang('');
}

// Pencarian saat ketik
$('#search-barang').on('keyup', function() {
  const keyword = $(this).val();
  loadBarang(keyword);
});

// Load data barang dari server
function loadBarang(keyword) {
  $.getJSON('/barang/search', { q: keyword }, function(data) {
    let rows = '';
    data.forEach(b => {
      rows += `
        <tr style="font-size: 12px;">
          <td>${b.kode_barang}</td>
          <td>${b.nama_barang}</td>
          <td>${b.nilairp}</td>
          <td>${b.isidus}</td>
          <td>
            <button class="btn btn-sm btn-primary" onclick="pilihBarang('${b.kode_barang}', '${b.nama_barang}', '${b.nilairp}', '${b.isidus}')">Pilih</button>
          </td>
        </tr>`;
    });
    $('#barang-list').html(rows);
  });
}

// Setelah barang dipilih
function pilihBarang(kode, nama, harga, isidus) {
  // Cek apakah kode barang sudah ada di baris lain
  let duplikat = false;

  $('input[name^="items["][name$="[kode_barang]"]').each(function() {
    const val = $(this).val();
    const thisIndex = $(this).attr('id').split('_')[2]; // kode_barang_0 → ambil 0
    if (val === kode && parseInt(thisIndex) !== currentIndexForBarang) {
      duplikat = true;
      return false; // break loop
    }
  });

  if (duplikat) {
    alert('Kode barang sudah dipilih di baris lain!');
    return;
  }

  // Jika tidak duplikat, isi field seperti biasa
  const idx = currentIndexForBarang;
  $(`#kode_barang_${idx}`).val(kode);
  $(`input[name="items[${idx}][nama_barang]"]`).val(nama);
  $(`input[name="items[${idx}][harga]"]`).val(harga);
  $(`input[name="items[${idx}][isidus]"]`).val(isidus);

  // Hapus warning jika ada sebelumnya
  $(`#kode_barang_${idx}`).removeClass('is-invalid');

  $('#barangModal').modal('hide');
}

$(function(){
  // 1) Fungsi isi nama_salesman
  function fillSalesman(){
    const nama = $('#kode_sales option:selected').data('nama') || '';
    $('#nama_salesman').val(nama);
  }
  fillSalesman();

  // 2) On change kode_sales → AJAX load pelanggan
  $('#kode_sales').on('change', function(){
    fillSalesman();

    const kode = $(this).val();
    $('#kode_pelanggan').html('<option disabled>Loading…</option>');
    $('#nama_pelanggan, #alamat, #telepon').val('');

    $.getJSON('{{ route("pelanggan.search") }}', { salesman: kode }, function(data){
      let opts = '<option value="" disabled selected>Pilih Pelanggan</option>';
      data.forEach(p => {
        opts += `<option 
          value="${p.Kode_pelanggan}"
          data-nama="${p.Nama_pelanggan}"
          data-alamat="${p.alamat}"
          data-telepon="${p.telepon}">
          ${p.Kode_pelanggan} – ${p.Nama_pelanggan}
        </option>`;
      });
      $('#kode_pelanggan').html(opts);
    });
  });

  // 3) Jika user sales, trigger sekali agar dropdown langsung terisi
  @if($userLevel == 1)
    $('#kode_sales').trigger('change');
  @endif

  // 4) On change pelanggan → isi nama, alamat, telepon
  $('#kode_pelanggan').on('change', function(){
    const $o = $(this).find('option:selected');
    $('#nama_pelanggan').val($o.data('nama')    || '');
    $('#alamat').val($o.data('alamat')          || '');
    $('#telepon').val($o.data('telepon')        || '');
  });
});

function initializeSelect2() {
    $('.kode-barang-select').select2({
      placeholder: 'Cari kode barang…',
      ajax: {
        url: '/masterbarang/search',
        dataType: 'json',
        delay: 250,
        data: params => ({ q: params.term }),
        processResults: data => ({
          results: data.map(item => ({
            id: item.kode_barang,
            text: `${item.kode_barang} – ${item.nama_barang}`,
            nama_barang: item.nama_barang,
            harga: item.hargapcsjual,
            isidus: item.isidus
          }))
        }),
        cache: true
      }
    }).off('select2:select')
      .on('select2:select', function(e) {
        const d = e.params.data, idx = $(this).data('index');
        $(`input[name="items[${idx}][nama_barang]"]`).val(d.nama_barang);
        $(`input[name="items[${idx}][harga]"]`).val(d.harga);
        $(`input[name="items[${idx}][isidus]"]`).val(d.isidus);
      });
  }
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
    <div class="input-group">
        <input type="text" class="form-control" name="items[${index}][kode_barang]" id="kode_barang_${index}" readonly>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openBarangModal(${index})">Cari</button>
    </div>
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
$('#items-body').append(row);


    // Inisialisasi Select2 untuk dropdown barang yang baru ditambahkan
    initializeSelect2();
}
 $(document).ready(() => {
    initializeSelect2();
  });

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
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(item => ({
          id: item.kode_barang,
          text: `${item.kode_barang} – ${item.nama_barang}`,
          nama_barang: item.nama_barang,
          harga: item.nilairp,
          isidus: item.isidus
        }))
      }),
      cache: true
    }
  })
  .off('select2:select')         // Hapus listener lama, kalau ada
  .on('select2:select', function(e) {
    const $this = $(this);
    const kode  = e.params.data.id;

    // Cek duplikat
    let dup = false;
    $('.kode-barang-select').not($this).each(function() {
      if ($(this).val() === kode) {
        dup = true;
        return false;
      }
    });

    if (dup) {
      alert('Kode barang ini sudah ada di baris sebelumnya! Silakan pilih kode barang lain.');
      // Reset hanya Select2-nya
      $this.val(null).trigger('change.select2');
      return;
    }

    // Tidak duplikat: isi otomatis nama, harga, isidus
    const idx = $this.data('index');
    $(`input[name="items[${idx}][nama_barang]"]`).val(e.params.data.nama_barang);
    $(`input[name="items[${idx}][harga]"]`).val(e.params.data.harga);
    $(`input[name="items[${idx}][isidus]"]`).val(e.params.data.isidus);
  });
}


document.addEventListener("DOMContentLoaded", function () {
    initializeSelect2();
});

$(document).ready(function() {
//   $(document).on('change', '.kode-barang-select', function() {
//     let selectedKodeBarang = $(this).val();
//     let isDuplicate = false;

//     $('.kode-barang-select').each(function() {
//         if ($(this).val() === selectedKodeBarang && this !== event.target) {
//             isDuplicate = true;
//             return false;
//         }
//     });

//     if (isDuplicate) {
//         alert('Kode barang ini sudah ada di baris sebelumnya! Silakan pilih kode barang lain.');
//         // Reset tanpa memicu handler ulang:
//         $(this).val(null).trigger('change.select2');
//         return;  // hentikan eksekusi handler
    
//         } else {
//             // Isi otomatis kolom terkait jika tidak duplikat
//             const data = $(this).select2('data')[0];
//             let currentRow = $(this).closest('tr');

//             currentRow.find('input[name$="[nama_barang]"]').val(data.nama_barang);
//             currentRow.find('input[name$="[harga]"]').val(data.harga);
//             currentRow.find('input[name$="[isidus]"]').val(data.isidus);
//         }
//     });
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

        function updateSalesmanDetails(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        if (selectedOption) {
            document.getElementById('nama_salesman').value = selectedOption.dataset.nama1 || '';
        }
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
     @media (max-width: 576px) {
    label.form-label {
      font-size: 13px;
    }
      .kode-barang-column {
        max-width: 100px; /* Ubah sesuai kebutuhan */
        word-wrap: break-word; /* Membungkus teks */
        white-space: normal; /* Izinkan teks turun ke bawah */
    }
     .kode-barang-select {
        width: 100% !important;
        min-width: 100px; /* Sesuaikan dengan kebutuhan */
    }

    input, select, button {
      font-size: 10px;
    }
     .form-control {
        font-size: 10px; /* Ukuran font kecil agar lebih pas */
        padding: 5px; /* Padding agar lebih rapi */
    }

    .btn {
      padding: 8px 12px;
    }
  }
</style>
@endsection
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" 
      rel="stylesheet" />
@endpush