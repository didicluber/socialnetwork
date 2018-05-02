<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use BackendBundle\Entity\PrivateMessage;
use AppBundle\Form\PrivateMessageType;


class PrivateMessageController extends Controller
{

    private $session;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function indexPrivateMessageAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $private_message = new PrivateMessage();
        $form = $this->createForm(PrivateMessageType::class, $private_message, array(
            'empty_data' => $user
        ));

        // data binding form
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {

                $user_media_route = 'uploads/media/'.$user->getId().'_usermedia';

                // upload image
                $file = $form['image']->getData();
                if (!empty($file) && $file != null) {
                    $ext = $file->guessExtension(); // obtencion de extension

                    if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
                        $file_name = $user->getId().'_imgpmessage_'.time().'.'.$ext;
                        $file->move($user_media_route.'/pmessages/images', $file_name);

                        $private_message->setImage($file_name);
                    } else {
                        $private_message->setImage(null);
                    }
                } else {
                    $private_message->setImage(null);
                }

                // upload file
                $doc = $form['file']->getData();
                if (!empty($doc) && $doc != null) {
                    $ext = $doc->guessExtension();

                    if ($ext == 'pdf') {
                        $file_name = $user->getId().'_docpmessage_'.time().'.'.$ext;
                        $doc->move($user_media_route.'/pmessages/documents', $file_name);

                        $private_message->setFile($file_name);
                    } else {
                        $private_message->setFile(null);
                    }
                } else {
                    $private_message->setFile(null);
                }

                $private_message->setEmitter($user);
                $private_message->setCreatedAt(new \DateTime("now"));
                $private_message->setReaded(0);

                $em->persist($private_message);
                $flush = $em->flush();

                if ($flush == null) {
                    $status = 'Le message privé a été envoyé correctement';
                } else {
                    $status = 'Erreur d\'envoi de message privé';
                }

            } else {
                $status = 'Le message privé n\'a pas été envoyé';
            }

            $this->session->getFlashBag()->add("status", $status);
            return $this->redirectToRoute('private_message_index');
        }

        $private_messages = $this->getPrivateMessages($request);
        $this->setReadedPrivateMessages($em, $user); // marca mensajes como leidos

        return $this->render('AppBundle:PrivateMessage:index_private_message.html.twig', array(
            'form' => $form->createView(),
            'private_messages' => $private_messages,
            'type' => 'received'
        ));
    }

    /**
     *
     * @param Request $request
     * @return Response
     */
    public function sendedAction(Request $request)
    {
        $private_messages = $this->getPrivateMessages($request, "sended");

        return $this->render('AppBundle:PrivateMessage:sended.html.twig', array(
            'private_messages' => $private_messages,
            'type' => 'sended'
        ));

    }

    /**
     * @param $request
     * @param null $type si es "sended" devuelve mensajes enviados
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function getPrivateMessages($request, $type = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $user_id = $user->getId();

        if($type == "sended") {
            $dql = "SELECT p FROM BackendBundle:PrivateMessage p WHERE"
                . " p.emitter = $user_id ORDER BY p.id DESC";
        } else {
            $dql = "SELECT p FROM BackendBundle:PrivateMessage p WHERE"
                . " p.receiver = $user_id ORDER BY p.id DESC";
        }

        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // parametro request de paginacion y en que num de pagina empieza
            5 //numero de registros por paginas
        );

        return $pagination;
    }

    /**
     *
     * @return Response
     */
    public function notReadedAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();

        if (!$isAjax) {
            return $this->redirect("../../private-message");
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $pm_repo = $em->getRepository('BackendBundle:PrivateMessage');
        $num_not_readed_pm = count($pm_repo->findBy(array(
            'receiver' => $user,
            'readed' => 0
        )));

        return new Response($num_not_readed_pm);
    }

    /**
     *
     * @param $em
     * @param $user
     * @return bool
     */
    private function setReadedPrivateMessages($em, $user)
    {
        $pm_repo = $em->getRepository('BackendBundle:PrivateMessage');
        $private_messages = $pm_repo->findBy(array(
            'receiver' => $user,
            'readed' => 0
        ));

        foreach($private_messages as $msg) {
            $msg->setReaded(1);
            $em->persist($msg);
        }

        $flush = $em->flush();

        if ($flush == null) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

}
