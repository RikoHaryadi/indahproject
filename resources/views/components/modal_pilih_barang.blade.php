<!-- Tombol untuk membuka modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPilihBarang">
    Pilih Barang
</button>


<!-- Modal -->
<div class="modal fade" id="modalPilihBarang" tabindex="-1" aria-labelledby="modalPilihBarangLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPilihBarangLabel">Pilih Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="searchBarang" class="form-control" placeholder="Cari Barang...">
                <br>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBarang">
                        @foreach($barang as $item)
                        <tr>
                            <td>{{ $item->kode_barang }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ number_format($item->harga, 0, ',', '.') }}</td>
                            <td>{{ $item->stok }}</td>
                            <td>
                                <button class="btn btn-primary btnPilihBarang"
                                    data-kode_barang="{{ $item->kode_barang }}"
                                    data-nama_barang="{{ $item->nama_barang }}"
                                    data-harga="{{ $item->harga }}">
                                    Pilih
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchBarang').addEventListener('input', function () {
        let searchValue = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableBarang tr');
        rows.forEach(row => {
            let namaBarang = row.querySelector('td:nth-child(2)').innerText.toLowerCase();
            row.style.display = namaBarang.includes(searchValue) ? '' : 'none';
        });
    });

    function loadBarang() {
        fetch('/api/barang') // Pastikan endpoint ini mengembalikan data barang dalam format JSON
            .then(response => response.json())
            .then(data => {
                let tbody = document.getElementById('barangTableBody');
                tbody.innerHTML = '';
                data.forEach(barang => {
                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${barang.kode_barang}</td>
                        <td>${barang.nama_barang}</td>
                        <td>${barang.harga}</td>
                        <td>${barang.stok}</td>
                        <td><button class="btn btn-success" onclick="pilihBarang('${barang.kode_barang}', '${barang.nama_barang}', ${barang.harga})">Pilih</button></td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    

    document.addEventListener('DOMContentLoaded', loadBarang);

    $(document).ready(function () {
    // Filter pencarian barang
    $("#searchBarang").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tableBarang tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Pilih barang dari modal menggunakan event delegation
    $(document).on("click", ".btnPilihBarang", function () {
    var kode = $(this).data("kode_barang");
    var nama = $(this).data("nama_barang");
    var harga = $(this).data("harga");

    // Pastikan elemen ini ada di dalam form
    $("#kode_barang").val(kode);
    $("#nama_barang").val(nama);
    $("#harga").val(harga);

    // Tutup modal
    $("#modalPilihBarang").modal("hide");
});

});

$(document).on('click', '.pilih-barang', function () {
    let kode_barang = $(this).data('kode_barang');
    let nama_barang = $(this).data('nama_barang');
    let harga = $(this).data('harga');
    let stok = $(this).data('stok');
    let isidus = $(this).data('isidus');

    // Pastikan ada baris untuk menampung barang, jika tidak tambahkan
    let lastRow = $('#items-body tr:last-child');

    if (lastRow.length === 0 || lastRow.find('.kode-barang-select').val() !== '') {
        addNewItem();
        lastRow = $('#items-body tr:last-child');
    }

    // Isi data barang ke baris terakhir
    let index = lastRow.index();
    lastRow.find('.kode-barang-select').val(kode_barang).trigger('change');
    lastRow.find(`input[name="items[${index}][nama_barang]"]`).val(nama_barang);
    lastRow.find(`input[name="items[${index}][harga]"]`).val(harga);
    lastRow.find(`input[name="items[${index}][stok]"]`).val(stok);
    lastRow.find(`input[name="items[${index}][isidus]"]`).val(isidus);

    // Tutup modal
    $('#modalPilihBarang').modal('hide');
});

</script>
