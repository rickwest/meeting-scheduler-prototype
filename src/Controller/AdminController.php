<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @Route("/admin/", name="admin_index")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/admin/reset", name="admin_reset", methods={"DELETE"})
     */
    public function resetDemo(Request $request, KernelInterface $kernel)
    {
        if ($this->isCsrfTokenValid('reset', $request->request->get('_token'))) {
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(['command' => 'demo:reset']);
            $output = new BufferedOutput();

            $application->run($input, $output);

            $content = $output->fetch();

            $this->addFlash('success', 'System reset successfully');
        }

        return $this->redirectToRoute('admin_index');
    }

    public function impersonateUsers(UserRepository $users)
    {
        return $this->render('admin/partials/impersonateUsers.html.twig', [
            'users' => $users->findAllDemoUsers(),
        ]);
    }
}
