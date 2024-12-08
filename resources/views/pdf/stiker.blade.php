<!DOCTYPE html>
<html>
<style>
</style>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

    .badge {
        position: relative;
        width: 600px;
        height: 800px;
        margin: 0 auto;
        border: 1px solid #ddd;
        transform: scaleX(-1); /* Membalik secara horizontal */
        -webkit-transform: scaleX(-1); /* Dukungan untuk browser lama */
    }


        .badge::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('{{ base_path('resources/img/stiker.jpg') }}') no-repeat center;
            background-size: contain;
            z-index: 1;
        }

        /* Label dan Isian */
        .label-container {
            position: absolute;
            top: 340px;
            left: 90px;
            display: flex;
            flex-direction: column;
            gap: 25px;
            /* Jarak antar label */
            z-index: 2;
        }

        .label {
            display: flex;
            align-items: center;
            /* Teks rata vertikal */
            gap: 10px;
            /* Jarak antara label dan isi */
            font-size: 18px;
            font-family: 'Times New Roman', Times, serif;
            font-weight: bold;
            font-size: 25px;
            color: black;
        }

        .label span {
            font-family: Arial, Helvetica, sans-serif;
            font-style: italic;
            color: white;
        }

        .label-no {
            display: flex;
            align-items: center;
            font-family: 'Times New Roman', Times, serif;
            font-weight: bold;
            font-size: 23px;
            color: black;
            font-size: 27px;
        }

        .label-no span {
            color: white;
        }

        .container {
            position: absolute;
            top: 205px;
            left: 260px;
            display: flex;
            flex-direction: column;
            gap: 25px;
            /* Jarak antar label */
            z-index: 2;
        }

        .expiry {
            position: absolute;
            white-space: nowrap;
            overflow-x: auto;
            bottom: 180px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            text-transform: uppercase;
            font-size: 40px;
            font-weight: bold;
            color: rgb(0, 0, 0);
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body>
    <div class="badge">
        <!-- Semua label ada di dalam container -->
        <div class="container">

            <div class="label-no">No : <span><b>{{ $nomor_stiker }}</b></span></div>
        </div>

        <div class="label-container">
            <div class="label">Perusahaan : <span><b><i>{{ $name_customer }}</i></b></span></div>
            <div class="label">No Lambung : <span><b><i>{{ $no_lambung }}</i></b></span></div>
            <div class="label">Plat Nomor : <span><b><i>{{ $plate_number }}</i></b></span></div>
            <div class="label">Lokasi : <span><b><i>{{ $area }}</i></b></span></div>
        </div>

        <!-- Label BERLAKU S/D di posisi terpisah -->
        <div class="expiry"><b>{{ \Carbon\Carbon::parse($expired_at)->translatedFormat('d F Y') }}</b></div>
    </div>
</body>

</html>
