<?php

namespace App\Http\Controllers;

use App\Middleware\AuthMiddleware;

class HomeController extends Controller
{
    public function __construct(AuthMiddleware $authMiddleware)
    {
        parent::__construct($authMiddleware);
    }

    public function index(): void
    {
        $this->redirect('');
    }

    public function alias(): void
    {
        $this->redirect('');
    }
}
