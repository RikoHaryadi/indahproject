<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
   <!-- Tambahkan di dalam bagian <head> atau sebelum tag </body> -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">


    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Laravel-9 | @yield('title')</title>
</head>
<body class="bg-primary-subtle">
  @php
    $level = session('user_level');
@endphp
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
        <a class="navbar-brand" href="/">SNDPro PT. INDAH SEJATI</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/">Home</a>
        </li>
    @if($level == 1)

     
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Sales Order
          </a>
          <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="/so">Input SO</a></li>
          <li><a class="dropdown-item" href="/penjualan/daftarso">Daftar SO</a></li>
          <!-- <li><a class="dropdown-item" href="/so/selection">Proses SO-MultiSO</a></li> -->
          </ul>
        </li>
      
        <li class="nav-item">
          <a class="nav-link" href="/barang">Stok</a>
        </li>
       
    @else
     <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Sales Order
          </a>
          <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="/po">Input SO</a></li>
          <li><a class="dropdown-item" href="/penjualan/daftarso">Daftar SO</a></li>
          <li><a class="dropdown-item" href="/so/selection">Proses SO-MultiSO</a></li>
          <li><a class="dropdown-item" href="/penjualan/import">Bridging</a></li>
          </ul>
        </li>
      
        <li class="nav-item">
          <a class="nav-link" href="/barang">Stok</a>
        </li>
           <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Master
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/masterbarang">Master_Barang</a></li>
            <li><a class="dropdown-item" href="/kategori">Kategori</a></li>
            <li><a class="dropdown-item" href="/pelanggan">Pelanggan</a></li>
            <li><a class="dropdown-item" href="/supplier">Supplier</a></li>
            <li><a class="dropdown-item" href="/salesman">Salesman</a></li>
          </ul>
        </li>
          <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Penjualan
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/penjualan">Proses SO</a></li>
            <li><a class="dropdown-item" href="/penjualan/daftar">Daftar Transaksi</a></li>
            <li><a class="dropdown-item" href="/rekap/pilih-faktur">Buat Rekap Faktur</a></li>
            <li><a class="dropdown-item" href="/retur-penjualan">Retur</a></li>
            <li><a class="dropdown-item" href="/retur-bebas">Retur Bebas</a></li>
            <li><a class="dropdown-item" href="/retur/daftar">Daftar Retur</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            GRN
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/grn">Input GRN</a></li>
            <li><a class="dropdown-item" href="/grn/daftar">Daftar Transaksi</a></li>
            <li><a class="dropdown-item" href="#">Retur GRN</a></li>
            <li><a class="dropdown-item" href="#">Daftar Retur</a></li>
            <li><a class="dropdown-item" href="/grn/import">Import CSV</a></li>
          </ul>
        </li>
         <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            AR
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/datapiutang">Data Piutang</a></li>
          </ul>
        </li>
       <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Akuntansi
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/inputbiaya">Input Biaya</a></li>
            <li><a class="dropdown-item" href="/bukubesar">Buku Besar</a></li>
            <li><a class="dropdown-item" href="/kodeakun">Buat Akun</a></li>
            <li><a class="dropdown-item" href="#">Daftar Retur</a></li>
          </ul>
        </li>
      @endif
    </div>
            @php
            $user = session('username');
        @endphp
        @if($user)
          <span class="navbar-text text-light me-3">
            Hi, {{ $user }}
          </span>
        @endif
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-outline-light nav-link me-auto" 
                    style="border: none; background: none;">
              Logout
            </button>
          </form>
  </div>

</nav>
<div class="container">
    @yield('content')
     <!-- jQuery (pastikan sudah ada) -->
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</div>
</body>
</html>