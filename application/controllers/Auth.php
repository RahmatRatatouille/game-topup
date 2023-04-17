<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }

    public function index()
    {
        if ($this->session->userdata('email')) {
            redirect('member');
        }
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Halaman Login';
            $this->load->view('template/auth_header', $data);
            $this->load->view('Auth/login');
            $this->load->view('template/auth_footer');
        } else {
            // validasi berhasil
            $this->_login();
            // redirect('login');
        }
    }

    private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();
        // var_dump($user);
        // die;

        // jika usernya ada
        if ($user) {
            // 1 itu Member
            // 2 itu Admin
            if ($user['is_active'] == 1) {
                // cek password
                if (password_verify($password, $user['password'])) {
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    if ($user['role_id'] == 1) {
                        redirect('admin');
                    } else {
                        redirect('member');
                    }
                } else {
                    $this->session->set_flashdata('pesan', '<div class="alert alert-danger" role="alert">
                    Password salah!!</div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('pesan', '<div class="alert alert-danger" role="alert">
                Email belum diaktivasi!!</div>');
                redirect('auth');
            }
        } else {
            // bila usernya tidak ada
            $this->session->set_flashdata('pesan', '<div class="alert alert-danger" role="alert">
            Email tidak terdaftar!!</div>');
            redirect('auth');
        }
    }


    public function registration()
    {
        if ($this->session->userdata('email')) {
            redirect('member');
        }
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
            'is_unique' => 'Email sudah digunakan!!'
        ]);
        $this->form_validation->set_rules('password1', 'password', 'required|trim|min_length[8]|matches[password2]', [
            'matches' => 'Password tidak cocok!',
            'min_length' => 'Password minimal 8 karakter!!'
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim');
        // | matches[password1]


        if ($this->form_validation->run() == false) {
            $data['title'] = 'Halaman Registration';
            $this->load->view('template/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('template/auth_footer');
        } else {
            $data = [
                'nama' => $this->input->post('nama'),
                'email' => $this->input->post('email'),
                'foto' => 'default.jpg',
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 1,
                'tanggal_pembuatan' => time()
            ];

            $this->db->insert('user', $data);
            $this->session->set_flashdata('pesan', '<div class="alert alert-success" role="alert">
            Selamat akun anda telah terdaftar. Cobalah untuk masuk</div>');
            redirect('auth');
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');

        $this->session->set_flashdata('pesan', '<div class="alert alert-success" role="alert">
            Anda berhasil logout</div>');
        redirect('auth');
    }

    public function blocked()
    {
        $this->load->view('auth/blocked');
    }

    // public function edit($id)
    // {
    //     $where  = array('id' => $id);
    //     $data['user_sub_menu'] = $this->menu_model->edit_data($where, 'user_sub_menu')->result();
    //     $this->load->view('template/auth_header');
    //     $this->load->view('template/auth_sidebar');
    //     $this->load->view('edit_data', $data);
    //     $this->load->view('template/auth_footer');
    // }

    // public function udpate()
    // {
    //     $id = $this->input->post('id');
    //     $title = $this->input->post('title');
    //     $url = $this->input->post('url');
    //     $icon = $this->input->post('icon');
    //     $is_active = $this->input->post('is_active');

    //     $data = array(
    //         'title' => $title,
    //         'url' => $url,
    //         'icon' => $icon,
    //         'is_active' => $is_active,
    //     );
    //     $where = array(
    //         'id' => $id
    //     );
    //     $this->menu_model->update_data($where, $data, 'user_sub_menu');
    //     redirect('auth/index');
    // }
}
