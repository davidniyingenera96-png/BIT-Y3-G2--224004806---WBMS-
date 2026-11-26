<?php // Admin inbox for homepage contact messages
class MessageController extends Controller {
    function delete() { $this->need('admin'); $this->post(); Message::delete((int)($_POST['id'] ?? 0)); flash('Message deleted.'); $this->redirect('messages/index'); }
    function index() {
        $this->need('admin'); $rows = Message::recent(); $this->view('messages/index', ['title' => 'Messages', 'rows' => $rows]); Message::markAllRead();
    }
}
