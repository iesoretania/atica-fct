<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Person;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityController extends Controller
{
    /**
     * @Route("/entrar", name="login", methods={"GET"})
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // obtener el error de entrada, si existe alguno
        $error = $authenticationUtils->getLastAuthenticationError();

        // último nombre de usuario introducido
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            array(
                'last_username' => $lastUsername,
                'login_error' => $error,
            )
        );
    }

    /**
     * @Route("/restablecer", name="login_password_reset", methods={"GET", "POST"})
     */
    public function passwordResetRequestAction(Request $request)
    {

        $data = [
            'email' => ''
        ];

        $form = $this->createForm('AppBundle\Form\Type\PasswordResetType', $data);

        $form->handleRequest($request);

        $data = $form->getData();
        $email = $data['email'];
        $error = '';

        // ¿se ha enviado una dirección?
        if ($form->isSubmitted() && $form->isValid()) {

            // comprobar que está asociada a un usuario
            $person = $this->getDoctrine()->getManager()->getRepository('AppBundle:Person')->findOneBy(['email' => $email]);

            $user = (null !== $person) ? $person->getUser() : null;

            if (null === $user) {
                $error = $this->get('translator')->trans('form.reset.notfound', [], 'security');
            } else {
                // almacenar como último correo electrónico el indicado
                $this->get('session')->set('_security.last_username', $email);

                // obtener tiempo de expiración del token
                $expire = (int) $this->getParameter('password_reset.expire');

                // comprobar que no se ha generado un token hace poco
                if ($user->getToken() && $user->getTokenValidity() > new \DateTime()) {
                    $error = $this->get('translator')->trans('form.reset.wait', ['%expiry%' => $expire], 'security');
                } else {
                    // generar un nuevo token
                    $token = bin2hex(random_bytes(16));
                    $user->setToken($token);

                    // calcular fecha de expiración del token
                    $validity = new \DateTime();
                    $validity->add(new \DateInterval('PT' . $expire . 'M'));
                    $user->setTokenValidity($validity);

                    // guardar token
                    $this->get('doctrine')->getManager()->flush();

                    // enviar correo
                    if (0 === $this->get('app.mailer')->sendEmail([$user],
                            ['id' => 'form.reset.email.subject', 'parameters' => []],
                            [
                                'id' => 'form.reset.email.body',
                                'parameters' => [
                                    '%name%' => $user->getPerson()->getFirstName(),
                                    '%link%' => $this->generateUrl('login_password_reset_do',
                                        ['userId' => $user->getId(), 'token' => $token],
                                        UrlGeneratorInterface::ABSOLUTE_URL),
                                    '%expiry%' => $expire
                                ]
                            ], 'security')
                    ) {
                        $this->addFlash('error', $this->get('translator')->trans('form.reset.error', [], 'security'));
                    } else {
                        $this->addFlash('success',
                            $this->get('translator')->trans('form.reset.sent', ['%email%' => $email], 'security'));
                        return $this->redirectToRoute('login');
                    }
                }
            }
        }

        return $this->render(
            ':security:login_password_reset.html.twig', [
                'last_username' => $this->get('session')->get('_security.last_username', ''),
                'form' => $form->createView(),
                'error' => $error
            ]
        );
    }

    /**
     * @Route("/restablecer/{userId}/{token}", name="login_password_reset_do", methods={"GET", "POST"})
     */
    public function passwordResetAction(Request $request, $userId, $token)
    {
        /**
         * @var User|null
         */
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy([
            'id' => $userId,
            'token' => $token
        ]);

        if (null === $user || ($user->getTokenValidity() < new \DateTime())) {
            $this->addFlash('error', $this->get('translator')->trans('form.reset.notvalid', [], 'security'));
            return $this->redirectToRoute('login');
        }

        $data = [
            'password' => '',
            'repeat' => ''
        ];

        $form = $this->createForm('AppBundle\Form\Type\NewPasswordType', $data);

        $form->handleRequest($request);

        $error = '';
        if ($form->isSubmitted() && $form->isValid()) {

            //codificar la nueva contraseña y asignarla al usuario
            $password = $this->container->get('security.password_encoder')
                ->encodePassword($user, $form->get('newPassword')->get('first')->getData());
            $user->setPassword($password)->setToken(null)->setTokenValidity(null);
            $this->getDoctrine()->getManager()->flush();

            // indicar que los cambios se han realizado con éxito y volver a la página de inicio
            $message = $this->get('translator')->trans('form.reset.message', [], 'security');
            $this->addFlash('success', $message);
            return new RedirectResponse(
                $this->generateUrl('frontpage')
            );
        }

        return $this->render(
            ':security:login_password_new.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
                'error' => $error
            ]
        );
    }

    /**
     * @Route("/comprobar", name="login_check", methods={"POST"})
     * @Route("/salir", name="logout", methods={"GET"})
     */
    public function logInOutCheckAction()
    {
    }
}
