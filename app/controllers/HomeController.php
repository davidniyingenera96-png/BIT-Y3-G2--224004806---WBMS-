<?php // Public pages: home (login screen), about, features, contact (+ contact form handler)
class HomeController extends Controller {
    function index() { $this->view('home/index', ['title' => 'Home', 'layout' => 'site']); }
    function about() {
        $bills = (int)Model::scalar("SELECT COUNT(*) FROM bills WHERE status<>'Cancelled'"); $paid = (int)Model::scalar("SELECT COUNT(*) FROM bills WHERE status='Paid'");
        $this->view('home/about', ['title' => 'About Us', 'layout' => 'site', 'customers' => (int)Model::scalar('SELECT COUNT(*) FROM customers WHERE is_active=1'), 'bills' => $bills, 'paid' => $paid, 'rate' => $bills ? round($paid / $bills * 100) : 0]);
    }
    function features() { $this->view('home/features', ['title' => 'Features', 'layout' => 'site']); }
    function contact() {
        $old = $_SESSION['old'] ?? []; unset($_SESSION['old']);
        $this->view('home/contact', ['title' => 'Contact Us', 'layout' => 'site', 'site' => Setting::all(), 'old' => $old]);
    }
    /** POST handler for the contact form: CSRF, honeypot, throttle, server validation, saved to contact_messages. */
    function send() {
        $this->post();
        $back = function (string $m, string $t) { flash($m, $t); header('Location: ' . url('home/contact')); exit; };
        if (trim((string)($_POST['website'] ?? '')) !== '') $back('Thank you! Your message has been sent.', 'success');
        if (time() - ($_SESSION['c_t'] ?? 0) >= 600) { $_SESSION['c_n'] = 0; $_SESSION['c_t'] = time(); }
        if (($_SESSION['c_n'] ?? 0) >= 3) $back('Too many messages sent. Please try again in a few minutes.', 'error');
        $d = ['name' => trim($_POST['name'] ?? ''), 'email' => trim($_POST['email'] ?? ''), 'subject' => trim($_POST['subject'] ?? ''), 'message' => trim($_POST['message'] ?? '')]; $e = [];
        if (mb_strlen($d['name']) < 2 || mb_strlen($d['name']) > 80) $e[] = 'Name must be 2-80 characters.';
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 120) $e[] = 'Enter a valid email address.';
        if (mb_strlen($d['subject']) < 3 || mb_strlen($d['subject']) > 120) $e[] = 'Subject must be 3-120 characters.';
        if (mb_strlen($d['message']) < 10 || mb_strlen($d['message']) > 2000) $e[] = 'Message must be 10-2000 characters.';
        if ($e) { $_SESSION['old'] = $d; $back(implode(' ', $e), 'error'); }
        try { Message::create($d); } catch (PDOException $x) { error_log($x->getMessage()); $_SESSION['old'] = $d; $back('Your message could not be sent right now. Please try again later.', 'error'); }
        $_SESSION['c_n']++; $back('Thank you! Your message has been sent. We will get back to you soon.', 'success');
    }
}
