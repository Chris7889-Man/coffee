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
            <input type="text" name="jabatan" class="form-control" required>
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
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
            <label>No. HP</label>
            <input type="text" name="no_hp" class="form-control">
        </div>
        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="data_staff.php" class="btn btn-secondary">Kembali</a>
    </form>
</body>
</html>
