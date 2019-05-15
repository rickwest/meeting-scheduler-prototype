<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/", name="admin_index")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig');
    }

    public function impersonateUsers(UserRepository $users)
    {

        return $this->render('admin/partials/impersonateUsers.html.twig', [
            'users' => $users->findAllDemoUsers(),
        ]);
    }
}
