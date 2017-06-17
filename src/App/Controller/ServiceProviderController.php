<?php

namespace App\Controller;

use App\Application;
use App\Entity\ServiceProvider;
use App\Form\ServiceProviderType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServiceProviderController {
    public function index(Application $app) {
        $providers = $app['orm.em']
            ->getRepository(ServiceProvider::class)
            ->findAll();

        return $app->render('service_providers/index.html.twig', [
            'providers' => $providers
        ]);
    }

    public function certificate(Request $request, Application $app, $id) {
        /** @var EntityManager $em */
        $em = $app['orm.em'];

        /** @var ServiceProvider $provider */
        $provider = $em->getRepository(ServiceProvider::class)
            ->findOneById($id);

        if($provider === null) {
            throw new NotFoundHttpException();
        }

        $cert = openssl_x509_read($provider->getCertificate());
        $certificateInfo = openssl_x509_parse($cert);
        openssl_x509_free($cert);

        return $app->render('service_providers/certificate.html.twig', [
            'sp' => $provider,
            'certificate' => $certificateInfo
        ]);
    }

    public function add(Request $request, Application $app) {
        /** @var EntityManager $em */
        $em = $app['orm.em'];

        $provider = new ServiceProvider();

        $form = $app->form($provider, [ ], ServiceProviderType::class)
            ->getForm();
        $form->handleRequest($request);

        if($form->isValid()) {
            $em->persist($provider);
            $em->flush();

            return $app->redirectTo('service_providers');
        }

        return $app->render('service_providers/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function edit(Request $request, Application $app, $id) {
        /** @var EntityManager $em */
        $em = $app['orm.em'];

        /** @var ServiceProvider $provider */
        $provider = $em->getRepository(ServiceProvider::class)
            ->findOneById($id);

        if($provider === null) {
            throw new NotFoundHttpException();
        }

        $form = $app->form($provider, [ ], ServiceProviderType::class)
            ->getForm();
        $form->handleRequest($request);

        if($form->isValid()) {
            $em->persist($provider);
            $em->flush();

            return $app->redirectTo('service_providers');
        }

        return $app->render('service_providers/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}