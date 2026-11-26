<?php
abstract class Controller {
    /** Renders a view inside the layout. Internal names use a __ prefix and extract() uses EXTR_SKIP,
     *  so view data (e.g. "v", "c", "u") can never overwrite the loader's own variables. */
    protected function view(string $__view, array $__data = []): void {
        $__layout = APP_ROOT . '/app/views/layout/' . ($__data['layout'] ?? (user() ? 'app' : 'auth')) . '.php';
        $__file = APP_ROOT . "/app/views/$__view.php";
        $u = user(); $title = $__data['title'] ?? 'Water Bill';
        extract($__data, EXTR_SKIP);
        $content = $__file;
        require $__layout;
    }
    protected function redirect(string $r, array $q = []) { header('Location: ' . url($r, $q)); exit; }
    /** Server-side role check; guests go to login, wrong roles get 403. */
    protected function need(string ...$roles): array {
        $u = user(); if (!$u) $this->redirect('auth/login');
        if (!in_array($u['role'], $roles, true)) { http_response_code(403); $this->view('errors/denied', ['title' => 'Access denied']); exit; }
        return $u;
    }
    protected function post(): void { if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); } csrf_check(); }
    protected function json(int $code, array $b) { http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode($b); exit; }
}
