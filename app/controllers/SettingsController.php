<?php
require_once __DIR__ . '/../core/Controller.php';

class SettingsController extends Controller {
    public function index() {
        $this->requireAuth();
        $data = [
            'title' => 'Pengaturan',
        ];
        $this->render('settings/index', $data);
    }
}
