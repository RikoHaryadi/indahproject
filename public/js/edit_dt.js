$(document).ready(function () {
    const detailItems = window.detailItems || [];

    detailItems.forEach(function (item, index) {
        const row = `
        <tr class="item-row">
            <td><input type="text" name="items[${index}][id_faktur]" class="form-control" value="${item.id_faktur}" readonly></td>
            <td><input type="text" name="items[${index}][kode_pelanggan]" class="form-control" value="${item.kode_pelanggan}" readonly></td>
            <td><input type="text" name="items[${index}][nama_pelanggan]" class="form-control" value="${item.nama_pelanggan}" readonly></td>
            <td><input type="number" name="items[${index}][top]" class="form-control" value="${item.top}"></td>
            <td><input type="number" name="items[${index}][total]" class="form-control total-field" value="${item.total}" readonly></td>
            <td><input type="number" name="items[${index}][bayar]" class="form-control bayar-field" value="${item.bayar}" oninput="updateSisa(this)"></td>
            <td><input type="number" name="items[${index}][sisapiutang]" class="form-control sisa-field" value="${item.sisapiutang}" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-item">Hapus</button></td>
        </tr>`;
        $('#items-body').append(row);
    });

    hitungTotalBayar();

    // Hapus baris item
    $('#items-body').on('click', '.remove-item', function () {
        $(this).closest('tr').remove();
        hitungTotalBayar();
    });

    // Tambah item
    let itemsCount = detailItems.length;

    $('#add-item').on('click', function () {
        const rowHtml = `
        <tr>
            <td>
                <select name="items[${itemsCount}][id_faktur]" class="form-control kode-barang-select id_faktur select-faktur">
                    <option value="">Pilih Faktur</option>
                    ${window.fakturOptionsHtml || ''}
                </select>
            </td>
            <td><input type="text" name="items[${itemsCount}][kode_pelanggan]" class="form-control kode_pelanggan" readonly></td>
            <td><input type="text" name="items[${itemsCount}][nama_pelanggan]" class="form-control nama_pelanggan" readonly></td>
            <td><input type="number" name="items[${itemsCount}][top]" class="form-control" value="0"></td>
            <td><input type="number" name="items[${itemsCount}][total]" class="form-control total_faktur" readonly></td>
            <td><input type="number" name="items[${itemsCount}][bayar]" class="form-control pembayaran" value="0"></td>
            <td><input type="number" name="items[${itemsCount}][sisapiutang]" class="form-control sisapiutang" readonly></td>
            <td><button type="button" class="btn btn-danger delete-item">Hapus</button></td>
        </tr>`;
        $('#items-body').append(rowHtml);
        initializeSelect2($('#items-body tr:last'));
        itemsCount++;
    });

    // Inisialisasi select2
    function initializeSelect2(row) {
        row.find('.select-faktur').select2();

        row.find('.select-faktur').on('select2:select', function () {
            const selectedOption = $(this).find('option:selected');
            const idFaktur = selectedOption.val();
            const selectEl = $(this);

            // Cek duplikat
            let duplicate = false;
            $('.select-faktur').not(this).each(function () {
                if ($(this).val() === idFaktur) {
                    duplicate = true;
                    return false;
                }
            });
            if (duplicate) {
                alert('ID Faktur sudah dipilih di baris lain!');
                selectEl.val(null).trigger('change');
                return;
            }

            // Cek piutang nol
            const sisapiutang = parseFloat(selectedOption.data('sisapiutang')) || 0;
            if (sisapiutang === 0) {
                alert("❌ Piutang sudah lunas!");
                selectEl.val(null).trigger("change");
                return;
            }

            // Cek di server apakah faktur sudah pernah ditagih
            $.get('/check-faktur-exists', { id_faktur: idFaktur }, function (response) {
                if (response.exists) {
                    alert('❌ Faktur sudah ada di daftar tagihan sebelumnya dan belum diproses!');
                    selectEl.val(null).trigger('change');
                    return;
                }

                const row = selectEl.closest('tr');
                row.find('.kode_pelanggan').val(selectedOption.data('kode_pelanggan'));
                row.find('.nama_pelanggan').val(selectedOption.data('nama_pelanggan'));
                row.find('.total_faktur').val(selectedOption.data('total'));
                row.find('.sisapiutang').val(sisapiutang);
                row.find('.pembayaran').val(0);

                updateTotalKeseluruhan();
            });
        });
    }

    $(document).on('click', '.delete-item', function () {
        $(this).closest('tr').remove();
        updateTotalKeseluruhan();
    });

    // Hitung total bayar
    function hitungTotalBayar() {
        let total = 0;
        $('.bayar-field').each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        $('#totaldt').val(total.toFixed(2));
    }

    // Hitung total faktur
    function updateTotalKeseluruhan() {
        let total = 0;
        $('.total_faktur').each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        $('#totaldt').val(total.toFixed(2));
    }

    // Input collector otomatis
    $('#id_faktur').on('change', function () {
        const nama = $(this).find(':selected').data('nama');
        $('#nama_salesman').val(nama || '');
    });
});

// Fungsi global untuk hitung sisa per baris
function updateSisa(input) {
    const row = $(input).closest('tr');
    const total = parseFloat(row.find('.total-field').val()) || 0;
    const bayar = parseFloat($(input).val()) || 0;
    const sisa = total - bayar;
    row.find('.sisa-field').val(sisa);

    let totalBayar = 0;
    $('.bayar-field').each(function () {
        totalBayar += parseFloat($(this).val()) || 0;
    });
    $('#totaldt').val(totalBayar.toFixed(2));
}
