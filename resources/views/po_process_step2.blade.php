@extends('layout.mainlayout')
@section('title', 'Proses PO - Step 2')
@section('content')
<div class="container">
    <h1>Proses SO - Step 2</h1>
    <form id="so-processing-form" method="POST" action="{{ route('so.process.final') }}">
        @csrf
        <!-- Kirim ID PO yang dipilih -->
        <input type="hidden" name="selected_po_ids" value="{{ implode(',', $selectedPoIds) }}">
        <table class="table table-bordered table-success" style="font-size:12px;">
            <thead class=" table table-danger">
                <tr>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Total Qty Dus</th>
                    <th>Total Qty Lsn</th>
                    <th>Total Qty Pcs</th>
                    <th>Stok Tersedia</th>
                    <th>Isi Dus</th>
                </tr>
            </thead>
            <tbody id="aggregated-items-body">
    @foreach($aggregatedItems as $index => $item)
    <tr>
        <td>{{ $item['kode_barang'] }}</td>
        <td>{{ $item['nama_barang'] }}</td>
        <td>{{ $item['total_dus'] }}</td>
        <td>{{ $item['total_lsn'] }}</td>
        <td>{{ $item['total_pcs'] }}</td>
        <td>{{ $item['stok'] }}</td>
        <td>{{ $item['isi'] ?? 10 }}</td>
        <td style="display: none;">
            <input type="hidden" name="items[{{ $loop->index }}][kode_barang]" value="{{ $item['kode_barang'] }}">
            <input type="hidden" name="items[{{ $loop->index }}][nama_barang]" value="{{ $item['nama_barang'] }}">
            <input type="hidden" name="items[{{ $loop->index }}][total_dus]" value="{{ $item['total_dus'] }}">
            <input type="hidden" name="items[{{ $loop->index }}][total_lsn]" value="{{ $item['total_lsn'] }}">
            <input type="hidden" name="items[{{ $loop->index }}][total_pcs]" value="{{ $item['total_pcs'] }}">
            <input type="hidden" name="items[{{ $loop->index }}][stok]" value="{{ $item['stok'] ?? 0 }}">
            <input type="hidden" name="items[{{ $loop->index }}][isi]" value="{{ $item['isi'] ?? 10 }}">
        </td>
    </tr>
    @endforeach
</tbody>
        </table>
        <button type="button" class="btn btn-primary" onclick="processStock()">Auto Gin</button>
        <div id="final-items" style="margin-top:20px;"></div>
        <button type="button" class="btn btn-success mt-2" onclick="statusChange()">Status Change & Simpan</button>
    </form>
</div>

<script>
    // Fungsi simulasi pemenuhan stok dari PO secara berurutan
    function processStock() {
    let items = [];
    // Gunakan ID pada tbody agar pencarian lebih spesifik
    document.querySelectorAll('#aggregated-items-body tr').forEach((row, index) => {
        // Ambil hidden input berdasarkan index sekuensial
        const kodeEl = row.querySelector('input[name="items['+index+'][kode_barang]"]');
        const totalDusEl = row.querySelector('input[name="items['+index+'][total_dus]"]');
        const totalLsnEl = row.querySelector('input[name="items['+index+'][total_lsn]"]');
        const totalPcsEl = row.querySelector('input[name="items['+index+'][total_pcs]"]');
        const stokEl = row.querySelector('input[name="items['+index+'][stok]"]');
        const isiEl = row.querySelector('input[name="items['+index+'][isi]"]');

        if (!kodeEl || !totalDusEl || !totalLsnEl || !totalPcsEl || !stokEl || !isiEl) {
            console.error("Data input tidak lengkap pada baris " + index);
            return;
        }
        
        let kode_barang = kodeEl.value;
        let totalDus = parseFloat(totalDusEl.value) || 0;
        let totalLsn = parseFloat(totalLsnEl.value) || 0;
        let totalPcs = parseFloat(totalPcsEl.value) || 0;
        let stok = parseFloat(stokEl.value) || 0;
        let isi = parseFloat(isiEl.value) || 0;
        
        // Jika isi bernilai 0, tetapkan default 10
        if (isi === 0) {
            isi = 10;
        }
        
        // Hitung total quantity dalam satuan pcs
        let totalQty = (totalDus * isi) + (totalLsn * 12) + totalPcs;
        
        // Jika stok tidak mencukupi, gunakan stok sebagai totalQty
        if (stok < totalQty) {
            totalQty = stok;
        }
        
        items.push({
            kode_barang: kode_barang,
            totalQty: totalQty,
            stok: stok
        });
    });
    
    // Tampilkan final items yang sesuai dengan stok
    let html = '<h3>Final Items yang Dapat Dipenuhi</h3>';
    html += '<table class="table table-bordered table-sm table-success" style="font-size:12px;">';
    html += '<thead class=" table table-danger"><tr><th>Kode Barang</th><th>Total Qty Dipenuhi</th><th>Stok</th></tr></thead><tbody>';
    items.forEach(item => {
        html += `<tr>
                    <td>${item.kode_barang}</td>
                    <td>${item.totalQty}</td>
                    <td>${item.stok}</td>
                 </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('final-items').innerHTML = html;
    
    console.log("Proses stok selesai:", items);
}

 // Fungsi untuk menghapus baris dengan stok kosong
 function removeEmptyStockRows() {
        document.querySelectorAll('#aggregated-items-body tr').forEach((row) => {
            const stokEl = row.querySelector('input[name*="[stok]"]');
            if (stokEl && parseFloat(stokEl.value) === 0) {
                console.log("Menghapus baris dengan stok kosong pada kode barang:", row.querySelector('input[name*="[kode_barang]"]').value);
                row.remove();
            }
        });
    }

     // Fungsi statusChange untuk menghitung discount dan total net per PO, lalu simpan ke database
     function statusChange() {
        // Pertama, hapus baris yang stoknya kosong
        removeEmptyStockRows();
        
        // Kemudian, ambil FormData dari form
        const formData = new FormData(document.getElementById('so-processing-form'));
        
        // Debug: Cetak semua pasangan key-value di FormData
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Kirim data melalui AJAX ke controller untuk diproses lebih lanjut (calculate discount, net total, simpan)
        fetch("{{ route('so.process.final') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            console.log("Response status:", response.status);
            return response.json();
        })
        .then(data => {
            console.log("Response data:", data);
            if(data.success){
                alert('Proses PO berhasil dan data tersimpan!');
                window.location.href = "{{ route('so.list') }}"; // Redirect ke halaman lain
            } else {
                alert('Terjadi kesalahan saat memproses PO.');
            }
        })
        .catch(error => console.error('Error:', error));
    }


</script>
@endsection
