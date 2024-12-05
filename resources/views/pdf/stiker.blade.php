<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            width: 100%;
            text-align: center;
            padding: 20px;
        }

        .badge {
            position: relative;
            width: 400px;
            height: 450px;
            margin: auto;
            border: 5px solid gold;
            padding: 20px;
        }

        .badge h1 {
            color: purple;
        }

        .badge h3 {
            color: rgb(0, 0, 0);
        }

        .badge .details {
            text-align: left;
            margin-top: 30px;
            color: black;
            font-size: 18px;
        }

        .badge .expiry {
            margin-top: 50px;
            font-size: 24px;
            color: gold;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="badge">
            <h1>KIE PASS MASUK KENDARAAN</h1>
            <h3>No: {{ $nomor_stiker }}</h3>
            <div class="details">
                <p>Perusahaan: {{ $name_customer }}</p>
                <p>No. Lambung: {{ $no_lambung }}</p>
                <p>No. Polisi: {{ $plate_number }}</p>
                <p>Area: {{ $area }}</p>
            </div>
            <div class="expiry">
                {{-- BERLAKU S/D: {{ $expired_at }} --}}
            </div>
        </div>
    </div>

</body>

</html>
