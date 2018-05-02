<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{

    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function notificationsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $user_id = $user->getId();

        $dql = "SELECT n FROM BackendBundle:Notification n WHERE n.user = $user_id ORDER BY n.id DESC";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        // marca notificaciones como leidas
        $notification = $this->get('app.notification_service');
        $notification->read($user);

        return $this->render('AppBundle:Notification:notifications.html.twig', array(
            'profile_user' => $user,
            'notifications' => $pagination
        ));

    }

    /**
     * @param Request $request
     * @return Response
     */
    public function countNotificationsAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();

        if (!$isAjax) {

            return $this->redirect("../notifications");
        }

        $em = $this->getDoctrine()->getManager();
        $notification_repo = $em->getRepository("BackendBundle:Notification");

        $notifications = $notification_repo->findBy(array(
            'user' => $this->getUser(),
            'readed' => 0
        ));

        return new Response(count($notifications));
    }
}
