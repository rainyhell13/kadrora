<?php

class DocumentController extends Controller
{
    private Document $docModel;
    private User     $userModel;

    // Разрешённые расширения документов
    private const ALLOWED_EXT = [
        'pdf','doc','docx','xls','xlsx','ppt','pptx','txt','rtf','odt','ods',
        'csv','zip','rar','7z','jpg','jpeg','png','gif','webp','mp3','json','xml',
    ];

    public function __construct()
    {
        $this->docModel  = new Document();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $this->requireAuth();
        $uid    = $this->currentUserId();
        $docs   = $this->docModel->getUserDocs($uid);

        $this->view('documents/index', [
            'docs'  => $docs,
            'me'    => $this->userModel->findById($uid),
            'csrf'  => $this->csrf(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function upload(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid   = $this->currentUserId();
        $title = trim($_POST['title'] ?? '');

        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Выберите файл для загрузки');
            $this->redirect('/documents');
            return;
        }

        $file     = $_FILES['document'];
        $origName = $file['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            $this->flash('error', 'Недопустимый тип файла: .' . $ext);
            $this->redirect('/documents');
            return;
        }
        if ($file['size'] > MAX_DOC_SIZE) {
            $this->flash('error', 'Файл слишком большой (максимум ' . round(MAX_DOC_SIZE / 1048576) . ' МБ)');
            $this->redirect('/documents');
            return;
        }

        if (!is_dir(DOC_UPLOAD_PATH)) {
            @mkdir(DOC_UPLOAD_PATH, 0775, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], DOC_UPLOAD_PATH . '/' . $filename)) {
            $this->flash('error', 'Ошибка сохранения файла');
            $this->redirect('/documents');
            return;
        }

        $this->docModel->add(
            $uid,
            $title !== '' ? $title : pathinfo($origName, PATHINFO_FILENAME),
            $filename,
            $origName,
            $ext,
            (int)$file['size']
        );
        $this->flash('success', 'Документ загружен');
        $this->redirect('/documents');
    }

    public function download($id): void
    {
        $this->requireAuth();
        $id  = (int)$id;
        $doc = $this->docModel->findById($id);
        if (!$doc) {
            http_response_code(404);
            $this->view('errors/404', [], 'main');
            return;
        }

        $path = DOC_UPLOAD_PATH . '/' . $doc['filename'];
        if (!is_file($path)) {
            http_response_code(404);
            $this->view('errors/404', [], 'main');
            return;
        }

        // Отдаём файл с оригинальным именем
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . rawurlencode($doc['original_name']) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache');
        readfile($path);
        exit;
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = $this->currentUserId();
        $id  = (int)($_POST['doc_id'] ?? 0);

        $filename = $this->docModel->delete($id, $uid);
        if ($filename) {
            @unlink(DOC_UPLOAD_PATH . '/' . $filename);
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Нет прав'], 403);
        }
    }
}
