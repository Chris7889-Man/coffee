<!DOCTYPE html>
<html>

<head>
    <title>Tambah Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>Tambah Staff</h2>

    <form action="proses_tambah_staff.php" method="POST">
        <div class="mb-3">
            <label>Nama Staff</label>
            <input type="text" name="nama_staff" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Jabatan</label>
            <select name="jabatan" class="form-control" required>
                <option value="" disabled selected>Pilih Jabatan</option>
                <option value="Admin">Admin</option>
                <option value="Super Admin">Super Admin</option>
                <option value="Staff">Staff</option>
     
            </select>
        </div>

        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirm" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
            <label>No. HP</label>
            <input type="text" name="no_hp" class="form-control">
        </div>
        <div class="mb-3">
            <label>Alamat Tempat Tinggal Sekarang</label>
            <textarea name="alamat" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Kode Gerobak</label>
            <select name="kode_gerobak" class="form-control" required>
                <option value="" disabled selected>Pilih Kode Gerobak</option>
                <option value="GRB 1">GRB 1</option>
                <option value="GRB 2">GRB 2</option>
                <option value="GRB 3">GRB 3</option>
                <option value="GRB 4">GRB 4</option>
                <option value="GRB 5">GRB 5</option>
                <option value="GRB 6">GRB 6</option>
                <option value="GRB 7">GRB 7</option>
                <option value="GRB 8">GRB 8</option>
                <option value="GRB 9">GRB 9</option>
                <option value="GRB 10">GRB 10</option>
                <option value="GRB 11">GRB 11</option>
                <option value="GRB 12">GRB 12</option>
                <option value="GRB 13">GRB 13</option>
                <option value="GRB 14">GRB 14</option>
                <option value="GRB 15">GRB 15</option>
                <option value="GRB 16">GRB 16</option>
                <option value="GRB 17">GRB 17</option>
                <option value="GRB 18">GRB 18</option>
                <option value="GRB 19">GRB 19</option>
                <option value="GRB 20">GRB 20</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Lokasi Jualan</label>
            <select name="lokasi_jualan" class="form-control" required>
                <option value="" disabled selected>Pilih Lokasi Jualan</option>
                <option value="Jl. Sultan Hasanuddin">Jl. Sultan Hasanuddin</option>
                <option value="Jl. Urip Sumoharjo">Jl. Urip Sumoharjo</option>
                <option value="Jl. Jenderal Sudirman">Jl. Jenderal Sudirman</option>
                <option value="Jl. Dr. Ratulangi">Jl. Dr. Ratulangi</option>
                <option value="Jl. A. P. Pettarani">Jl. A. P. Pettarani</option>
                <option value="Jl. Andi Pangerang Pettarani">Jl. Andi Pangerang Pettarani</option>
                <option value="Jl. Hertasning">Jl. Hertasning</option>
                <option value="Jl. Metro Tanjung Bunga">Jl. Metro Tanjung Bunga</option>
                <option value="Jl. Veteran">Jl. Veteran</option>
                <option value="Jl. Perintis Kemerdekaan">Jl. Perintis Kemerdekaan</option>
                <option value="Jl. Somba Opu">Jl. Somba Opu</option>
                <option value="Jl. Boulevard">Jl. Boulevard</option>
                <option value="Jl. Taman Cendana">Jl. Taman Cendana</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="data_staff.php" class="btn btn-secondary">Kembali</a>
    </form>
</body>

</html>