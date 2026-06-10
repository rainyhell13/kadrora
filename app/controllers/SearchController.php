<?php

class SearchController extends Controller
{
    private User  $userModel;
    private Group $groupModel;
    private Post  $postModel;

    public function __construct()
    {
        $this->userModel  = new User();
        $this->groupModel = new Group();
        $this->postModel  = new Post();
    }

    public function index(): void
    {
        $this->requireAuth();
        $uid   = $this->currentUserId();
        $query = trim($_GET['q'] ?? '');
        $tab   = $_GET['tab'] ?? 'people';
        if (!in_array($tab, ['people', 'groups', 'posts'], true)) {
            $tab = 'people';
        }

        $users  = [];
        $groups = [];
        $posts  = [];

        if ($query !== '') {
            // Считаем количество в каждой вкладке, а грузим только активную
            $users  = $this->userModel->search($query, $uid, USERS_PER_PAGE, 0);
            $groups = $this->groupModel->search($query, $uid, USERS_PER_PAGE);
            $posts  = $this->postModel->searchPublic($query, $uid, USERS_PER_PAGE);
        }

        $this->view('search/index', [
            'users'  => $users,
            'groups' => $groups,
            'posts'  => $posts,
            'query'  => $query,
            'tab'    => $tab,
            'me'     => $this->userModel->findById($uid),
            'csrf'   => $this->csrf(),
        ]);
    }
}
