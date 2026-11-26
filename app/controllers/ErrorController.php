<?php
class ErrorController extends Controller {
    function notfound() { $this->view('errors/page', ['title' => 'Not found', 'msg' => 'The page you requested does not exist.']); }
    function failure() { $this->view('errors/page', ['title' => 'Error', 'msg' => 'Something went wrong. Please try again later.']); }
}
