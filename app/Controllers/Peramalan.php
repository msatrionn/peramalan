<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\M_peramalan;
use DateTime;

class Peramalan extends Controller
{
    public function __construct()
    {
        $this->session = session();
        $this->model = new M_peramalan;
    }
    public function index()
    {
        if (!$this->session->has('isLogin')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'judul' => 'Peramalan',
            'peramalan' => $this->model->getAllData()
        ];
        echo view('templates/v_header', $data);
        echo view('templates/v_sidebar');
        echo view('templates/v_topbar');
        echo view('peramalan/index', $data);
        echo view('templates/v_footer');
    }
    public function tambah()
    {
        if (!$this->session->has('isLogin')) {
            return redirect()->to('/auth/login');
        }
        $daris = $this->request->getPost('dari');
        $sampais = $this->request->getPost('sampai');
        $val = DateTime::createFromFormat('!m', $daris);
        $dari = $val->format('F');
        $val = DateTime::createFromFormat('!m', $sampais);
        $sampai = $val->format('F');
        $tahun = $this->request->getPost('tahun');
        $bulan_peramalan = $dari . "-" . $sampai . " " . $tahun;
        $data = [
            'nama_barang' => $this->request->getPost('nama_barang'),
            'alpha' => $this->request->getPost('alpha'),
            'beta' => $this->request->getPost('beta'),
            'bulan_peramalan' => $bulan_peramalan,
            'level' => $this->request->getPost('level'),
            'trend' => $this->request->getPost('trend'),
            'nilai_ramal' => $this->request->getPost('ramal'),
            'mad' => $this->request->getPost('mad'),
            'mape' => $this->request->getPost('mape'),
        ];
        // dd($data);
        //insert data
        $success = $this->model->tambah($data);
        if ($success) {
            session()->setFlashdata('message', 'Ditambahkan');
            return redirect()->to(base_url('peramalan'));
        }
    }
    public function hapus()
    {
        $id = $this->request->getPost('id_peramalan');
        $success = $this->model->hapus($id);
        if ($success) {
            session()->setFlashdata('message', 'Dihapus');
            return redirect()->to(base_url('peramalan'));
        }
    }
    public function ubah()
    {
        if (!$this->session->has('isLogin')) {
            return redirect()->to('/auth/login');
        }
        $id = $this->request->getPost('id_peramalan');

        $data = [
            'id_barang' => $this->request->getPost('id_barang'),
            'id_rekap' => $this->request->getPost('id_rekap'),
            'tanggal_peramalan' => $this->request->getPost('tanggal_peramalan'),
            'jumlah_barang' => $this->request->getPost('jumlah_barang')
        ];
        //insert data
        $success = $this->model->ubah($data, $id);
        if ($success) {
            session()->setFlashdata('message', 'Diubah');
            return redirect()->to(base_url('peramalan'));
        }
    }
    public function view_ramal()
    {
        if (!$this->session->has('isLogin')) {
            return redirect()->to('/auth/login');
        }
        $data = [
            'judul' => 'peramalan',
            'pemesanan' => $this->model->pemesanan(),
            'barang' => $this->model->barang()
        ];
        echo view('templates/v_header', $data);
        echo view('templates/v_sidebar');
        echo view('templates/v_topbar');
        echo view('peramalan/ramal', $data);
        echo view('templates/v_footer');
    }

    public function hitung_ramal()
    {
        if (!$this->session->has('isLogin')) {
            return redirect()->to('/auth/login');
        }
        $data = [
            'dari' => $this->request->getPost('dari'),
            'sampai' => $this->request->getPost('sampai'),
            'id_barang' => $this->request->getPost('id_barang'),
            'tahun' => $this->request->getPost('tahun')
        ];
        $success =
            [
                $this->model->hitung_ramal($data),
                $this->model->bulan($data),
                $this->model->ramal($data),
                $this->model->detail_barang($data),
                $this->model->hitung_barang($data),
            ];
        if ($success) {

            $alpha = 0.2;
            $beta = 0.2;
            $ambil = $this->model->hitung_ramal($data);
            $cek_barang = $this->model->barang();
            $detail_barang = $this->model->detail_barang($data);
            $cek_bulan = $this->model->bulan($data);
            $nama_awal_bulan = $cek_bulan[0];
            $nama_akhir_bulan = $cek_bulan[1];
            $brg = $this->model->hitung_barang($data);
            $barangs = $brg;
            $total = count($ambil->getResult());
            // dd($barang->total_amount);
            foreach ($ambil->getResultArray() as $key => $value) {
                $barang[] = $value['total_amount'];
            }

            // print_r($barang[2]);
            // print_r($cek);
            switch ($total) {
                case '0':

                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        'level' => 'Bulan ini kekurangan data',
                        'mad' => 'Bulan ini kekurangan data',
                        'trend' => 'Bulan ini kekurangan data',
                        'pemesanan' => $this->model->hitung_ramal($data),
                        'forecasting' => 'tidak ada hasil',
                        'mape' => 'tidak ada hasil'

                    ];

                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '1':
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        'level' => 'Bulan ini kekurangan data',
                        'trend' => 'Bulan ini kekurangan data',
                        'mad' => 'Bulan ini kekurangan data',
                        'forecasting' => 'tidak ada hasil',
                        'pemesanan' => $this->model->hitung_ramal($data),
                        'mape' => 'tidak ada hasil'
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '2':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $forecasting2 = $level2 + $trend2;
                    $mape2 = 100 * ($barang[1] - $forecasting2) / $barang[1];
                    $mad = ($barang[1] - $forecasting2) / $total;
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level2,
                        'trend' => $trend2,
                        'forecasting' => $forecasting2,
                        'mape' => $mape2,
                        'mad' => $mad,
                        'pemesanan' => $this->model->hitung_ramal($data)
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '3':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $level3 = ($alpha * $barang[2]) + ((1 - $alpha) * ($level2 + $trend2));
                    $trend3 = $beta * ($level3 - $level2) + (1 - $beta) * $trend2;
                    $forecasting3 = $level3 + $trend3;
                    $mad = ($barang[2] - $forecasting3) / $total;
                    $mape3 = 100 * ($barang[2] - $forecasting3) / $barang[2];
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level3,
                        'trend' => $trend3,
                        'mape' => $mape3,
                        'mad' => $mad,
                        'pemesanan' => $this->model->hitung_ramal($data),
                        'forecasting' => $forecasting3
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '4':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $level3 = ($alpha * $barang[2]) + ((1 - $alpha) * ($level2 + $trend2));
                    $trend3 = $beta * ($level3 - $level2) + (1 - $beta) * $trend2;
                    $level4 = ($alpha * $barang[3]) + ((1 - $alpha) * ($level3 + $trend3));
                    $trend4 = $beta * ($level4 - $level3) + (1 - $beta) * $trend3;
                    $forecasting4 = $level4 + $trend4;
                    $mad = ($barang[3] - $forecasting4) / $total;
                    $mape4 = 100 * ($barang[3] - $forecasting4) / $barang[3];

                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level4,
                        'trend' => $trend4,
                        'forecasting' => $forecasting4,
                        'mape' => $mape4,
                        'mad' => $mad,
                        'pemesanan' => $this->model->hitung_ramal($data)
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '5':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $level3 = ($alpha * $barang[2]) + ((1 - $alpha) * ($level2 + $trend2));
                    $trend3 = $beta * ($level3 - $level2) + (1 - $beta) * $trend2;
                    $level4 = ($alpha * $barang[3]) + ((1 - $alpha) * ($level3 + $trend3));
                    $trend4 = $beta * ($level4 - $level3) + (1 - $beta) * $trend3;
                    $level5 = ($alpha * $barang[4]) + ((1 - $alpha) * ($level4 + $trend4));
                    $trend5 = $beta * ($level5 - $level4) + (1 - $beta) * $trend4;
                    $forecasting5 = $level5 + $trend5;
                    $mad = ($barang[4] - $forecasting5) / $total;
                    $mape5 = 100 * ($barang[4] - $forecasting5) / $barang[4];
                    // dd($barang[3]);
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level5,
                        'trend' => $trend5,
                        'forecasting' => $forecasting5,
                        'mape' => $mape5,
                        'mad' => $mad,
                        'pemesanan' => $this->model->hitung_ramal($data)
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '6':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $level3 = ($alpha * $barang[2]) + ((1 - $alpha) * ($level2 + $trend2));
                    $trend3 = $beta * ($level3 - $level2) + (1 - $beta) * $trend2;
                    $level4 = ($alpha * $barang[3]) + ((1 - $alpha) * ($level3 + $trend3));
                    $trend4 = $beta * ($level4 - $level3) + (1 - $beta) * $trend3;
                    $level5 = ($alpha * $barang[4]) + ((1 - $alpha) * ($level4 + $trend4));
                    $trend5 = $beta * ($level5 - $level4) + (1 - $beta) * $trend4;
                    $level6 = ($alpha * $barang[5]) + ((1 - $alpha) * ($level5 + $trend5));
                    $trend6 = $beta * ($level6 - $level5) + (1 - $beta) * $trend5;
                    $forecasting6 = $level6 + $trend6;
                    $mad = ($barang[5] - $forecasting6) / $total;
                    $mape6 = 100 * ($barang[5] - $forecasting6) / $barang[5];
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level6,
                        'trend' => $trend6,
                        'forecasting' => $forecasting6,
                        'mape' => $mape6,
                        'mad' => $mad,
                        'pemesanan' => $this->model->hitung_ramal($data)
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '7':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $level3 = ($alpha * $barang[2]) + ((1 - $alpha) * ($level2 + $trend2));
                    $trend3 = $beta * ($level3 - $level2) + (1 - $beta) * $trend2;
                    $level4 = ($alpha * $barang[3]) + ((1 - $alpha) * ($level3 + $trend3));
                    $trend4 = $beta * ($level4 - $level3) + (1 - $beta) * $trend3;
                    $level5 = ($alpha * $barang[4]) + ((1 - $alpha) * ($level4 + $trend4));
                    $trend5 = $beta * ($level5 - $level4) + (1 - $beta) * $trend4;
                    $level6 = ($alpha * $barang[5]) + ((1 - $alpha) * ($level5 + $trend5));
                    $trend6 = $beta * ($level6 - $level5) + (1 - $beta) * $trend5;
                    $level7 = ($alpha * $barang[6]) + ((1 - $alpha) * ($level6 + $trend6));
                    $trend7 = $beta * ($level7 - $level6) + (1 - $beta) * $trend6;
                    $forecasting7 = $level7 + $trend7;
                    $mad = ($barang[6] - $forecasting7) / $total;
                    $mape7 = 100 * ($barang[6] - $forecasting7) / $barang[6];
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level7,
                        'trend' => $trend7,
                        'pemesanan' => $this->model->hitung_ramal($data),
                        'forecasting' => $forecasting7,
                        'mape' => $mape7,
                        'mad' => $mad,
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '8':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $level3 = ($alpha * $barang[2]) + ((1 - $alpha) * ($level2 + $trend2));
                    $trend3 = $beta * ($level3 - $level2) + (1 - $beta) * $trend2;
                    $level4 = ($alpha * $barang[3]) + ((1 - $alpha) * ($level3 + $trend3));
                    $trend4 = $beta * ($level4 - $level3) + (1 - $beta) * $trend3;
                    $level5 = ($alpha * $barang[4]) + ((1 - $alpha) * ($level4 + $trend4));
                    $trend5 = $beta * ($level5 - $level4) + (1 - $beta) * $trend4;
                    $level6 = ($alpha * $barang[5]) + ((1 - $alpha) * ($level5 + $trend5));
                    $trend6 = $beta * ($level6 - $level5) + (1 - $beta) * $trend5;
                    $level7 = ($alpha * $barang[6]) + ((1 - $alpha) * ($level6 + $trend6));
                    $trend7 = $beta * ($level7 - $level6) + (1 - $beta) * $trend6;
                    $level8 = ($alpha * $barang[7]) + ((1 - $alpha) * ($level7 + $trend7));
                    $trend8 = $beta * ($level8 - $level7) + (1 - $beta) * $trend7;
                    $forecasting8 = $level8 + $trend8;
                    $mad = ($barang[7] - $forecasting8) / $total;
                    $mape8 =  ($barang[7] - $forecasting8) / $barang[7];
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level8,
                        'trend' => $trend8,
                        'pemesanan' => $this->model->hitung_ramal($data),
                        'forecasting' => $forecasting8,
                        'mape' => $mape8,
                        'mad' => $mad,
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '9':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $level3 = ($alpha * $barang[2]) + ((1 - $alpha) * ($level2 + $trend2));
                    $trend3 = $beta * ($level3 - $level2) + (1 - $beta) * $trend2;
                    $level4 = ($alpha * $barang[3]) + ((1 - $alpha) * ($level3 + $trend3));
                    $trend4 = $beta * ($level4 - $level3) + (1 - $beta) * $trend3;
                    $level5 = ($alpha * $barang[4]) + ((1 - $alpha) * ($level4 + $trend4));
                    $trend5 = $beta * ($level5 - $level4) + (1 - $beta) * $trend4;
                    $level6 = ($alpha * $barang[5]) + ((1 - $alpha) * ($level5 + $trend5));
                    $trend6 = $beta * ($level6 - $level5) + (1 - $beta) * $trend5;
                    $level7 = ($alpha * $barang[6]) + ((1 - $alpha) * ($level6 + $trend6));
                    $trend7 = $beta * ($level7 - $level6) + (1 - $beta) * $trend6;
                    $level8 = ($alpha * $barang[7]) + ((1 - $alpha) * ($level7 + $trend7));
                    $trend8 = $beta * ($level8 - $level7) + (1 - $beta) * $trend7;
                    $level9 = ($alpha * $barang[8]) + ((1 - $alpha) * ($level8 + $trend8));
                    $trend9 = $beta * ($level9 - $level8) + (1 - $beta) * $trend8;
                    $forecasting9 = $level9 + $trend9;
                    $mad = ($barang[8] - $forecasting9) / $total;
                    $mape9 = 100 * ($barang[8] - $forecasting9) / $barang[8];
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level9,
                        'trend' => $trend9,
                        'pemesanan' => $this->model->hitung_ramal($data),
                        'forecasting' => $forecasting9,
                        'mape' => $mape9,
                        'mad' => $mad,
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '10':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $level3 = ($alpha * $barang[2]) + ((1 - $alpha) * ($level2 + $trend2));
                    $trend3 = $beta * ($level3 - $level2) + (1 - $beta) * $trend2;
                    $level4 = ($alpha * $barang[3]) + ((1 - $alpha) * ($level3 + $trend3));
                    $trend4 = $beta * ($level4 - $level3) + (1 - $beta) * $trend3;
                    $level5 = ($alpha * $barang[4]) + ((1 - $alpha) * ($level4 + $trend4));
                    $trend5 = $beta * ($level5 - $level4) + (1 - $beta) * $trend4;
                    $level6 = ($alpha * $barang[5]) + ((1 - $alpha) * ($level5 + $trend5));
                    $trend6 = $beta * ($level6 - $level5) + (1 - $beta) * $trend5;
                    $level7 = ($alpha * $barang[6]) + ((1 - $alpha) * ($level6 + $trend6));
                    $trend7 = $beta * ($level7 - $level6) + (1 - $beta) * $trend6;
                    $level8 = ($alpha * $barang[7]) + ((1 - $alpha) * ($level7 + $trend7));
                    $trend8 = $beta * ($level8 - $level7) + (1 - $beta) * $trend7;
                    $level9 = ($alpha * $barang[8]) + ((1 - $alpha) * ($level8 + $trend8));
                    $trend9 = $beta * ($level9 - $level8) + (1 - $beta) * $trend8;
                    $level10 = ($alpha * $barang[9]) + ((1 - $alpha) * ($level9 + $trend9));
                    $trend10 = $beta * ($level10 - $level9) + (1 - $beta) * $trend9;
                    $forecasting10 = $level10 + $trend10;
                    $mad = ($barang[9] - $forecasting10) / $total;
                    $mape10 = 100 * ($barang[9] - $forecasting10) / $barang[9];
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level10,
                        'trend' => $trend10,
                        'pemesanan' => $this->model->hitung_ramal($data),
                        'forecasting' => $forecasting10,
                        'mape' => $mape10,
                        'mad' => $mad,
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '11':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $level3 = ($alpha * $barang[2]) + ((1 - $alpha) * ($level2 + $trend2));
                    $trend3 = $beta * ($level3 - $level2) + (1 - $beta) * $trend2;
                    $level4 = ($alpha * $barang[3]) + ((1 - $alpha) * ($level3 + $trend3));
                    $trend4 = $beta * ($level4 - $level3) + (1 - $beta) * $trend3;
                    $level5 = ($alpha * $barang[4]) + ((1 - $alpha) * ($level4 + $trend4));
                    $trend5 = $beta * ($level5 - $level4) + (1 - $beta) * $trend4;
                    $level6 = ($alpha * $barang[5]) + ((1 - $alpha) * ($level5 + $trend5));
                    $trend6 = $beta * ($level6 - $level5) + (1 - $beta) * $trend5;
                    $level7 = ($alpha * $barang[6]) + ((1 - $alpha) * ($level6 + $trend6));
                    $trend7 = $beta * ($level7 - $level6) + (1 - $beta) * $trend6;
                    $level8 = ($alpha * $barang[7]) + ((1 - $alpha) * ($level7 + $trend7));
                    $trend8 = $beta * ($level8 - $level7) + (1 - $beta) * $trend7;
                    $level9 = ($alpha * $barang[8]) + ((1 - $alpha) * ($level8 + $trend8));
                    $trend9 = $beta * ($level9 - $level8) + (1 - $beta) * $trend8;
                    $level10 = ($alpha * $barang[9]) + ((1 - $alpha) * ($level9 + $trend9));
                    $trend10 = $beta * ($level10 - $level9) + (1 - $beta) * $trend9;
                    $level11 = ($alpha * $barang[10]) + ((1 - $alpha) * ($level10 + $trend10));
                    $trend11 = $beta * ($level11 - $level10) + (1 - $beta) * $trend10;
                    $forecasting11 = $level11 + $trend11;
                    $mad = ($barang[10] - $forecasting11) / $total;
                    $mape11 = 100 * ($barang[10] - $forecasting11) / $barang[10];
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level11,
                        'trend' => $trend11,
                        'pemesanan' => $this->model->hitung_ramal($data),
                        'forecasting' => $forecasting11,
                        'mape' => $mape11,
                        'mad' => $mad,
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;
                case '12':
                    $level2 = ($alpha * $barang[1]) + ((1 - $alpha) * ($barang[0] + ($barang[1] - $barang[0])));
                    $trend2 = $beta * ($level2 - $barang[0]) + (1 - $beta) * ($barang[1] - $barang[0]);
                    $level3 = ($alpha * $barang[2]) + ((1 - $alpha) * ($level2 + $trend2));
                    $trend3 = $beta * ($level3 - $level2) + (1 - $beta) * $trend2;
                    $level4 = ($alpha * $barang[3]) + ((1 - $alpha) * ($level3 + $trend3));
                    $trend4 = $beta * ($level4 - $level3) + (1 - $beta) * $trend3;
                    $level5 = ($alpha * $barang[4]) + ((1 - $alpha) * ($level4 + $trend4));
                    $trend5 = $beta * ($level5 - $level4) + (1 - $beta) * $trend4;
                    $level6 = ($alpha * $barang[5]) + ((1 - $alpha) * ($level5 + $trend5));
                    $trend6 = $beta * ($level6 - $level5) + (1 - $beta) * $trend5;
                    $level7 = ($alpha * $barang[6]) + ((1 - $alpha) * ($level6 + $trend6));
                    $trend7 = $beta * ($level7 - $level6) + (1 - $beta) * $trend6;
                    $level8 = ($alpha * $barang[7]) + ((1 - $alpha) * ($level7 + $trend7));
                    $trend8 = $beta * ($level8 - $level7) + (1 - $beta) * $trend7;
                    $level9 = ($alpha * $barang[8]) + ((1 - $alpha) * ($level8 + $trend8));
                    $trend9 = $beta * ($level9 - $level8) + (1 - $beta) * $trend8;
                    $level10 = ($alpha * $barang[9]) + ((1 - $alpha) * ($level9 + $trend9));
                    $trend10 = $beta * ($level10 - $level9) + (1 - $beta) * $trend9;
                    $level11 = ($alpha * $barang[10]) + ((1 - $alpha) * ($level10 + $trend10));
                    $trend11 = $beta * ($level11 - $level10) + (1 - $beta) * $trend10;
                    $level12 = ($alpha * $barang[11]) + ((1 - $alpha) * ($level11 + $trend11));
                    $trend12 = $beta * ($level12 - $level11) + (1 - $beta) * $trend11;
                    $forecasting12 = $level12 + $trend12;
                    $mad = ($barang[11] - $forecasting12) / $total;
                    $mape12 = 100 * ($barang[11] - $forecasting12) / $barang[11];
                    $data = [
                        'cek_barang' => $cek_barang,
                        'judul' => 'peramalan',
                        'detail_barang' => $detail_barang,
                        'nama_awal_bulan' => $nama_awal_bulan,
                        'nama_akhir_bulan' => $nama_akhir_bulan,
                        'dari' => $this->request->getPost('dari'),
                        'sampai' => $this->request->getPost('sampai'),
                        'id_barang' => $this->request->getPost('id_barang'),
                        'tahun' => $this->request->getPost('tahun'),
                        'alpha' => $alpha = $this->request->getPost('alpha'),
                        'beta' => $beta = $this->request->getPost('beta'),
                        // 'judul' => 'Barang',
                        'level' => $level12,
                        'trend' => $trend12,
                        'pemesanan' => $this->model->hitung_ramal($data),
                        'forecasting' => $forecasting12,
                        'mape' => $mape12,
                        'mad' => $mad,
                    ];
                    echo view('templates/v_header', $data);
                    echo view('templates/v_sidebar');
                    echo view('templates/v_topbar');
                    echo view('peramalan/hitung_peramalan', $data);
                    echo view('templates/v_footer');
                    break;

                default:
                    echo 'Batas peramalan 12 bulan';
                    break;
            }
        }
    }
}
