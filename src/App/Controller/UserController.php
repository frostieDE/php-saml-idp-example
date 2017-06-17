<?php

namespace App\Controller;

use App\Application;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController {
    public function index(Application $app) {
        $users = $app['orm.em']
            ->getRepository(User::class)
            ->findAll();

        return $app->render('users/index.html.twig', [
            'users' => $users
        ]);
    }

    public function add(Request $request, Application $app) {
        /** @var EntityManager $em */
        $em = $app['orm.em'];

        $user = new User();

        $form = $app->form($user, [ ], UserType::class)
            ->getForm();
        $form->handleRequest($request);

        if($form->isValid()) {
            $user->setPassword($app->encodePassword($user, $password));

            $em->persist($user);
            $em->flush();

            return $app->redirectTo('users');
        }

        return $app->render('users/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function edit(Request $request, Application $app, $id) {
        /** @var EntityManager $em */
        $em = $app['orm.em'];

        /** @var User $user */
        $user = $em->getRepository(User::class)
            ->findOneById($id);

        if($user === null) {
            throw new NotFoundHttpException();
        }

        $form = $app->form($user, [ ], UserType::class)
            ->getForm();
        $form->handleRequest($request);

        if($form->isValid()) {
            $password = $form->get('password')->getData();

            if(!empty($password)) {
                $user->setPassword($app->encodePassword($user, $password));
            }

            $em->persist($user);
            $em->flush();

            return $app->redirectTo('users');
        }

        return $app->render('users/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

}