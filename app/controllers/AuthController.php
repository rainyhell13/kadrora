<?php

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function loginPage(): void
    {
        if (isset($_SESSION['user_id'])) $this->redirect('/feed');
        $this->view('auth/login', ['csrf' => $this->csrf()], 'auth');
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $this->view('auth/login', ['error' => 'Заполните все поля', 'csrf' => $this->csrf()], 'auth');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->view('auth/login', ['error' => 'Неверный email или пароль', 'csrf' => $this->csrf()], 'auth');
            return;
        }

        if ($user['is_banned']) {
            $this->view('auth/login', ['error' => 'Ваш аккаунт заблокирован', 'csrf' => $this->csrf()], 'auth');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->userModel->setOnline($user['id'], true);
        $this->redirect('/feed');
    }

    public function registerPage(): void
    {
        if (isset($_SESSION['user_id'])) $this->redirect('/feed');
        $this->view('auth/register', ['csrf' => $this->csrf()], 'auth');
    }

    public function register(): void
    {
        $this->verifyCsrf();

        $data = [
            'username'   => trim($_POST['username'] ?? ''),
            'email'      => trim($_POST['email'] ?? ''),
            'password'   => $_POST['password'] ?? '',
            'password2'  => $_POST['password2'] ?? '',
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            'gender'     => ($_POST['gender'] ?? '') ?: null,
            'birth_date' => ($_POST['birth_date'] ?? '') ?: null,
            'city'       => trim($_POST['city'] ?? '') ?: null,
        ];

        $errors = $this->validateRegister($data);

        if ($errors) {
            $this->view('auth/register', ['errors' => $errors, 'old' => $data, 'csrf' => $this->csrf()], 'auth');
            return;
        }

        if ($this->userModel->findByEmail($data['email'])) {
            $this->view('auth/register', ['errors' => ['email' => 'Email уже занят'], 'old' => $data, 'csrf' => $this->csrf()], 'auth');
            return;
        }

        if ($this->userModel->findByUsername($data['username'])) {
            $this->view('auth/register', ['errors' => ['username' => 'Имя пользователя занято'], 'old' => $data, 'csrf' => $this->csrf()], 'auth');
            return;
        }

        $id = $this->userModel->create($data);

        session_regenerate_id(true);
        $_SESSION['user_id']    = $id;
        $_SESSION['username']   = $data['username'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->userModel->setOnline($id, true);
        $this->flash('success', 'Добро пожаловать в Kadrora!');
        $this->redirect('/feed');
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->userModel->setOnline($_SESSION['user_id'], false);
        }
        session_destroy();
        $this->redirect('/login');
    }

    private function validateRegister(array $data): array
    {
        $errors = [];

        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $data['username'])) {
            $errors['username'] = 'Имя пользователя: 3–50 символов, только буквы, цифры и _';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный email';
        }
        if (strlen($data['password']) < 6) {
            $errors['password'] = 'Пароль не менее 6 символов';
        }
        if ($data['password'] !== $data['password2']) {
            $errors['password2'] = 'Пароли не совпадают';
        }
        if (mb_strlen($data['first_name']) < 2) {
            $errors['first_name'] = 'Введите имя';
        }
        if (mb_strlen($data['last_name']) < 2) {
            $errors['last_name'] = 'Введите фамилию';
        }

        return $errors;
    }
}
