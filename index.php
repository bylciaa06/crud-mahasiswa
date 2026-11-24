<?php
class Koneksi {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db   = "db_mahasiswa";
    public $conn;

    public function __construct() {
        $this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
        if (!$this->conn) {
            die("Koneksi gagal");
        }
    }
}

class Kampus {
    protected $koneksi;

    public function __construct($db) {
        $this->koneksi = $db;
    }

    public static function namaWeb() {
        return "Data Mahasiswa";
    }
}

class Mahasiswa extends Kampus {
    public function tampil() {
        $sql = "SELECT mahasiswa.*, jurusan.nama_jurusan 
                FROM mahasiswa 
                JOIN jurusan ON mahasiswa.id_jurusan = jurusan.id_jurusan 
                ORDER BY mahasiswa.id DESC";
        return mysqli_query($this->koneksi, $sql);
    }

    public function jurusan() {
        return mysqli_query($this->koneksi, "SELECT * FROM jurusan");
    }

    public function tambah($nim, $nama, $id_jur) {
        $nim = mysqli_real_escape_string($this->koneksi, $nim);
        $nama = mysqli_real_escape_string($this->koneksi, $nama);
        $id_jur = (int)$id_jur;
        return mysqli_query($this->koneksi, "INSERT INTO mahasiswa (nim, nama, id_jurusan) VALUES ('$nim', '$nama', '$id_jur')");
    }

    public function edit($id, $nim, $nama, $id_jur) {
        $id = (int)$id;
        $nim = mysqli_real_escape_string($this->koneksi, $nim);
        $nama = mysqli_real_escape_string($this->koneksi, $nama);
        $id_jur = (int)$id_jur;
        return mysqli_query($this->koneksi, "UPDATE mahasiswa SET nim='$nim', nama='$nama', id_jurusan='$id_jur' WHERE id=$id");
    }

    public function hapus($id) {
        $id = (int)$id;
        return mysqli_query($this->koneksi, "DELETE FROM mahasiswa WHERE id=$id");
    }

    public function satuData($id) {
        $id = (int)$id;
        $q = mysqli_query($this->koneksi, "SELECT * FROM mahasiswa WHERE id=$id");
        return mysqli_fetch_assoc($q);
    }
}

$db = new Koneksi();
$mhs = new Mahasiswa($db->conn);

if (isset($_POST['simpan'])) {
    $mhs->tambah($_POST['nim'], $_POST['nama'], $_POST['id_jurusan']);
    header("Location: index.php");
}

if (isset($_POST['update'])) {
    $mhs->edit($_POST['id'], $_POST['nim'], $_POST['nama'], $_POST['id_jurusan']);
    header("Location: index.php");
}

if (isset($_GET['hapus'])) {
    $mhs->hapus($_GET['hapus']);
    header("Location: index.php");
}

$ubah = isset($_GET['edit']) ? $mhs->satuData($_GET['edit']) : null;
$data = $mhs->tampil();
$listJurusan = $mhs->jurusan();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo Kampus::namaWeb(); ?></title>
</head>
<body>

<h3><?php echo Kampus::namaWeb(); ?></h3>

<form method="post">
    <?php if ($ubah) { ?>
        <input type="hidden" name="id" value="<?php echo $ubah['id']; ?>">
    <?php } ?>

    NIM: <br>
    <input type="text" name="nim" value="<?php echo $ubah['nim'] ?? ''; ?>" required><br><br>

    Nama: <br>
    <input type="text" name="nama" value="<?php echo $ubah['nama'] ?? ''; ?>" required><br><br>

    Jurusan: <br>
    <select name="id_jurusan" required>
        <option value="">-- Pilih --</option>
        <?php while ($j = mysqli_fetch_assoc($listJurusan)) { ?>
            <option value="<?php echo $j['id_jurusan']; ?>" <?php if ($ubah && $ubah['id_jurusan'] == $j['id_jurusan']) echo "selected"; ?>>
                <?php echo $j['nama_jurusan']; ?>
            </option>
        <?php } ?>
    </select><br><br>

    <?php if ($ubah) { ?>
        <button type="submit" name="update">Update</button>
        <a href="index.php">Batal</a>
    <?php } else { ?>
        <button type="submit" name="simpan">Simpan</button>
    <?php } ?>
</form>

<br>

<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>No</th>
        <th>NIM</th>
        <th>Nama</th>
        <th>Jurusan</th>
        <th>Aksi</th>
    </tr>
    <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
    <tr>
        <td><?php echo $no++; ?></td>
        <td><?php echo $row['nim']; ?></td>
        <td><?php echo $row['nama']; ?></td>
        <td><?php echo $row['nama_jurusan']; ?></td>
        <td>
            <a href="?edit=<?php echo $row['id']; ?>">Edit</a> | 
            <a href="?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>