<?php
/*
    @ Library Simple Upload
    @ Dibuat dengan penuh cinta oleh Muhammad Randika Rosyid :)
    @ Dibuat untuk mempermudah para developer untuk mengembangkan aplikasinya, terlebih untuk para developer yang masih stay sama native :D
    @ Library ini bebas untuk digunakan oleh siapapun, tetapi dengan tanpa mengurangi rasa hormat kepada pembuat library ini. :)
    @ Seluruh hak cipta dilindungi oleh undang-undang ( UU Nomor 28 tahun 2014 )
	@ Kontak saya: randika.rosyid2@gmail.com (email)
*/

class Simple_Upload
{
    protected $config;
    protected $data;
    protected $error;

    /*
        # Constructor, fungsi yg pertama kali dipanggil dalam class.
        # Yang akan mengambil seluruh konfigurasi yang anda kirimkan.
    */
    function __construct($config)
    {
        $this->data = (object) [];
        $this->error = NULL;

        $this->config = (object) [
            'file_ext_tolower' => FALSE,
            'overwrite' => FALSE,
            'max_size' => 0,
            'max_filename' => 0,
            'max_filename_overwrite' => 100,
            'encrypt_name' => FALSE,
            'remove_spaces' => TRUE
        ];

        foreach($config as $key => $value)
            $this->config->$key = $value;
    }

    private function _set_error($message)
    {
        $this->data = (object) [];
        $this->error = $message;
        return FALSE;
    }

    private function _encrypt_name()
    {
        $range = array_merge(range(0,9), range('a','z'), range('A','Z'));
        $max_range = count($range);
        $result = '';
    
        for($i=0; $i < 15; $i++) {
            $rand = mt_rand(0, ($max_range - 1));
            $result .= $range[$rand];
        }
    
        return $result;
    }

    private function _raw_name($file_name)
    {
        return preg_replace('/(\.\w+)$/m', '', $file_name);
    }

    private function _file_ext($file_name)
    {
        $array_name = explode('.', $file_name);
        return end($array_name);
    }

    private function _validation()
    {
        if(isset($this->config->allowed_ext)) {
            $array_allowed_ext = explode('|', $this->config->allowed_ext);
            if(!in_array($this->data->file_ext, $array_allowed_ext))
                return $this->_set_error("File dengan ekstensi {$this->data->file_ext} tidak diizinkan.");
        }

        if($this->config->max_size !== 0) {
            $kilobyte = $this->data->file_size / 1000;
            if($kilobyte > $this->config->max_size)
                return $this->_set_error("Maksimal ukuran file ialah {$this->config->max_size}");
        }

        if($this->config->max_filename !== 0) {
            $file_raw = $this->_raw_name($this->data->file_name);
            if(strlen($file_raw) > $this->config->max_filename)
                return $this->_set_error("Maksimal panjang nama file ialah {$this->config->max_filename}");
        }
    }

    private function _count_overwrite($file_name, $file_raw, $file_ext)
    {
        $result = 0;
        $dir = dir(__DIR__ . "/{$this->config->upload_path}/");

        while($value_dir = $dir->read()) {
            if(is_file($value_dir)) {
                $value_dir_raw = $this->_raw_name($value_dir);
                $value_dir_ext = $this->_file_ext($value_dir);

                if($value_dir_ext == $file_ext && strpos($value_dir_raw, $file_raw) !== FALSE)
                    $result += 1;
            }
        }

        return $result;
    }

    private function _fetch_overwrite($file_name)
    {
        if($this->config->overwrite === TRUE)
            return $file_name;

        $file_raw = $this->_raw_name($file_name);
        $file_ext = $this->_file_ext($file_name);

        $count_overwrite = $this->_count_overwrite($file_name, $file_raw, $file_ext);
        if($count_overwrite >= 1) {
            return ($count_overwrite > $this->config->max_filename_overwrite)
                ? "{$file_raw} (" . ($count_overwrite - 1) . ").{$file_ext}"
                : "{$file_raw} ({$count_overwrite}).{$file_ext}";

        } else {
            return $file_name;
        }
    }

    private function _fetch_data()
    {
        if($this->config->encrypt_name === TRUE) {
            $file_ext = $this->_file_ext($this->data->file_name);
            $new_file_name = $this->_encrypt_name() . ".{$file_ext}";

            rename($this->data->full_path, $this->data->file_path . $new_file_name);
            $this->data->file_name = $new_file_name;
            $this->data->full_path = $this->data->file_path . $new_file_name;

        } else if(isset($this->config->file_name)) {
            rename($this->data->full_path, $this->data->file_path . $this->config->file_name);
            $this->data->file_name = $this->config->file_name;
            $this->data->full_path = $this->data->file_path . $this->config->file_name;
        }

        if($this->config->file_ext_tolower === TRUE) {
            $file_raw = $this->_raw_name($this->data->file_name);
            $file_ext = $this->_file_ext($this->data->file_name);

            $new_file_name = $file_raw .'.'. strtolower($file_ext);
            rename($this->data->full_path, $this->data->file_path . $new_file_name);
            $this->data->file_name = $new_file_name;
            $this->data->full_path = $this->data->file_path . $new_file_name;
        }

        if($this->config->remove_spaces === TRUE) {
            $file_raw = $this->_raw_name($this->data->file_name);
            $file_ext = $this->_file_ext($this->data->file_name);

            $new_file_name = preg_replace('/\s/', '', $file_raw) .'.'. $file_ext;
            rename($this->data->full_path, $this->data->file_path . $new_file_name);
            $this->data->file_name = $new_file_name;
            $this->data->full_path = $this->data->file_path . $new_file_name;
        }
    }

    function get_data($index)
    {
        return (isset($this->data->$index)) ? $this->data->$index : NULL;
    }

    function get_error()
    {
        return $this->error;
    }

    function upload($name)
    {
        if(isset($_FILES[$name]) && $_FILES[$name]['error'] == 0) {
            $this->data = (object) [
                'file_name' => $this->_fetch_overwrite($_FILES[$name]['name']),
                'file_type' => $_FILES[$name]['type'],
                'file_path' => __DIR__ . $this->config->upload_path,
                'full_path' => __DIR__ . $this->config->upload_path . $this->_fetch_overwrite($_FILES[$name]['name']),
                'raw_name' => $this->_raw_name($_FILES[$name]['name']),
                'orig_name' => $_FILES[$name]['name'],
                'file_ext' => $this->_file_ext($_FILES[$name]['name']),
                'file_size' => $_FILES[$name]['size'] / 1000
            ];

            $validation = $this->_validation();
            if($validation !== FALSE) {
                $move = move_uploaded_file($_FILES[$name]['tmp_name'], $this->data->full_path);
                if($move === TRUE) {
                    $this->_fetch_data();
                    return TRUE;

                } else {
                    return $this->_set_error('File gagal di upload');
                }

            } else {
                return FALSE;
            }

        } else {
            return $this->_set_error('File tidak dapat ditemukan atau terjadi error pada file.');
        }
    }
}