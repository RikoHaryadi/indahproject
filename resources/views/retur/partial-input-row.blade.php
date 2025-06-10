<td><input type="text" name="items[0][kode_barang]" class="form-control" required></td>
<td><input type="text" name="items[0][nama_barang]" class="form-control" required></td>
<td><input type="number" name="items[0][harga]" class="form-control harga" step="0.01" required></td>
<td><input type="number" name="items[0][dus]" class="form-control qty" min="0" value="0"></td>
<td><input type="number" name="items[0][lusin]" class="form-control qty" min="0" value="0"></td>
<td><input type="number" name="items[0][pcs]" class="form-control qty" min="0" value="0"></td>
<td><input type="number" name="items[0][disc1]" class="form-control" step="0.01" value="0"></td>
<td><input type="number" name="items[0][disc2]" class="form-control" step="0.01" value="0"></td>
<td><input type="number" name="items[0][disc3]" class="form-control" step="0.01" value="0"></td>
<td><input type="number" name="items[0][disc4]" class="form-control" step="0.01" value="0"></td>
<td><input type="text" class="form-control jumlah" readonly></td>
<td><button type="button" class="btn btn-danger btn-sm hapus-baris">Hapus</button></td>

<script>
document.addEventListener('input', function (e) {
    if (e.target.closest('tr')) {
        hitungJumlah(e.target.closest('tr'));
    }
});

function hitungJumlah(row) {
    const harga = parseFloat(row.querySelector('[name$="[harga]"]').value) || 0;
    const dus = parseInt(row.querySelector('[name$="[dus]"]').value) || 0;
    const lusin = parseInt(row.querySelector('[name$="[lusin]"]').value) || 0;
    const pcs = parseInt(row.querySelector('[name$="[pcs]"]').value) || 0;

    const totalQty = dus * 12 + lusin * 12 + pcs;
    let jumlah = harga * totalQty;

    const disc1 = parseFloat(row.querySelector('[name$="[disc1]"]').value) || 0;
    const disc2 = parseFloat(row.querySelector('[name$="[disc2]"]').value) || 0;
    const disc3 = parseFloat(row.querySelector('[name$="[disc3]"]').value) || 0;
    const disc4 = parseFloat(row.querySelector('[name$="[disc4]"]').value) || 0;

    jumlah *= (1 - disc1 / 100);
    jumlah *= (1 - disc2 / 100);
    jumlah *= (1 - disc3 / 100);
    jumlah *= (1 - disc4 / 100);

    row.querySelector('.jumlah').value = jumlah.toFixed(2);
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('hapus-baris')) {
        e.target.closest('tr').remove();
    }
});
</script>
