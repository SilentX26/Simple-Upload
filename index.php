<?php
require __DIR__ . '/Simple_Upload.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $simple_upload = new Simple_Upload([
        'upload_path' => '/', // Isi dengan direktori upload file yang anda inginkan
        'allowed_ext' => '', // (optional) Isi dengan ekstensi file yang diizinkan, pisahkan dengan tanda "|" untuk lebih dari 1 ekstensi
        'file_name' => 'example.jpg', // (optional) Isi dengan kustom nama file yang anda inginkan (harus beserta ekstensi)
        'file_ext_tolower' => FALSE, // (optional) Isi TRUE jika anda ingin ekstensi file diubah ke lowercase (huruf kecil semua)
        'overwrite' => FALSE, // (optional) Isi TRUE jika anda ingin me-overwrite jika terdapat file dengan nama yang sama
        'max_size' => 0, // (optional) Isi dengan besaran maksimal ukuran file yang diinginkan (dalam satuan kilobyte), isi dengan 0 jika tidak ingin digunakan
        'max_filename' => 0, // (optional) Isi dengan besaran maksimal panjang nama file yang diinginkan, isi dengan 0 jika tidak ingin digunakan
        'max_filename_overwrite' => 0, // (optional) Isi dengan maksimal file yang tidak ingin di overwrite
        'encrypt_name' => FALSE, // (optional) Isi TRUE jika anda ingin nama file menggunakan random string
        'remove_spaces' => TRUE // (optional) Isi TRUE jika anda ingin seluruh whitespace/spasi pada nama file dihapus
    ]);

    /*
        # Gunakan metode ini untuk meng-eksekusi proses upload file
        # Parameter diisi dengan name pada input file
    */
    $simple_upload->upload('data');

    /*
        # Gunakan metode ini untuk mendapatkan data file yang di upload
        # Parameter diisi dengan name data, terdapat:
            - [file_name] => Nama file
            - [file_type] => Mime type file
            - [file_path] => Path upload file
            - [full_path] => Path upload file lengkap, beserta dengan nama file nya
            - [raw_name] => Nama file tanpa ekstensi
            - [orig_name] => Nama file original dari input (sebelum di rename dengan menggunakan encrypt_name, dll)
            - [file_ext] => Ekstensi file
            - [file_size] => Ukuran file (dalam satuan kilobyte)
    */
    $simple_upload->get_data('file_name');

    /*
        # Gunakan metode ini untuk mendapatkan pesan error (jika terjadi kesalahan)
    */
    $simple_upload->get_error();

} else {
?>

<html>
    <head>
        <title>Demo Simple Upload</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    </head>

    <body>
        <div class="container">
            <div class="row justify-content-center mt-5">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="file" name="data" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-success btn-block">Submit!</button>
                </form>
            </div>
        </div>
    </body>
</html>

<?php } ?>