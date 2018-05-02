<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Follow;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class FollowController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function followAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();

        if (!$isAjax) {
            return $this->redirect("people");
        }

        $user = $this->getUser();
        $followed_id = $request->get('followed'); // id de usuario que se va a seguir

        $em = $this->getDoctrine()->getManager();
        $user_repo = $em->getRepository('BackendBundle:User');
        $followed = $user_repo->find($followed_id);

        $follow = new Follow();
        $follow->setUser($user);
        $follow->setFollowed($followed);

        $em->persist($follow);
        $flush = $em->flush();

        if ($flush == null){
            $notification = $this->get('app.notification_service');
            $notification->set($followed, 'follow', $user->getId());
            $status = "Vous suivez maintenant cet utilisateur";
        }else{
            $status = "Cet utilisateur n'a pas pu être suivi";
        }

        return new Response($status);

    }

    /**
     * @param Request $request
     * @return Response
     */
    public function unfollowAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();

        if (!$isAjax) {
            return $this->redirect("people");
        }

        $user = $this->getUser();
        $followed_id = $request->get('followed'); // id de usuario que se va a seguir

        $em = $this->getDoctrine()->getManager();
        $follow_repo = $em->getRepository('BackendBundle:Follow');
        $followed = $follow_repo->findOneBy(array(
            'user' => $user,
            'followed' => $followed_id
        ));

        $em->remove($followed);
        $flush = $em->flush();

        if ($flush == null){
            $status = "Vous avez arrêté de suivre cet utilisateur";
        }else{
            $status = "Il n'a pas été possible d'arrêter de suivre cet utilisateur";
        }

        return new Response($status);

    }

    /**
     * @param Request $request
     * @param null $nick
     * @param null $type
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function followsListAction(Request $request, $nick = null, $type = null)
    {
        $em = $this->getDoctrine()->getManager();

        if($nick != null) {
            $user_repo = $em->getRepository('BackendBundle:User');
            $user = $user_repo->findOneBy(array(
                'nick' => $nick
            ));
        } else {
            $user = $this->getUser();
        }

        if(empty($user) || !is_object($user)) {
            return $this->redirect($this->generateUrl('home_publications'));
        }

        $user_id = $user->getId();

        if($type == 'following') {
            $dql = "SELECT f FROM BackendBundle:Follow f WHERE f.user = $user_id ORDER BY f.id DESC";
        } elseif($type == 'followers') {
            $dql = "SELECT f FROM BackendBundle:Follow f WHERE f.followed = $user_id ORDER BY f.id DESC";
        } else {
            return $this->redirect($this->generateUrl('home_publications'));
        }

        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('AppBundle:Follow:followslist.html.twig', array(
            'type' => $type,
            'profile_user' => $user,
            'followslist' => $pagination
        ));

    }
}
