<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use BackendBundle\Entity\Publication;
use BackendBundle\Entity\Like;

class LikeController extends Controller
{

    /**
     * @param null $id
     * @return Response
     */
    public function likeAction($id = null)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $publication_repo = $em->getRepository('BackendBundle:Publication');
        $publication = $publication_repo->find($id);

        //check if the user already likes the publication or not!
        $like = $em->getRepository('BackendBundle:Like')->findBy(
            array('user' => $user, 'publication' => $publication));
        if(!$like)
        {
            $like = new Like();
            $like->setUser($user);
            $like->setPublication($publication);
            $em->persist($like);
            $flush = $em->flush();
            if($flush == null) {
                $notification = $this->get('app.notification_service');
                $notification->set($publication->getUser(), 'like', $user->getId(), $publication->getId());
                $status = 'Vous aimez cette publication!';
            } else {
                $status = 'Impossible de sauvegarder le Like';
            }
        }
        else{
            $status = 'vous l\'aimez déjà!';
        }

        return new Response($status);
    }

    /**
     * @param null $id
     * @return Response
     */
    public function dislikeAction($id = null)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $like_repo = $em->getRepository('BackendBundle:Like');
        $like = $like_repo->findOneBy(array(
            'user' => $user,
            'publication' => $id
        ));

        //remove notification when user don't like anymore
        $notification = $em->getRepository('BackendBundle:Notification')->findOneBy(array(
                'user' => $user,
                'extra' => $id
            ));
        if($notification){
            $em->remove($notification);
        }
        
        $em->remove($like);
        $flush = $em->flush();

        if($flush == null) {
            $status = 'Vous n\'aimez plus cette publication!';
        } else {
            $status = 'Impossible de supprimer le Like';
        }

        return new Response($status);
    }

    /**
     *
     * @param Request $request
     * @param null $nick
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function likesListAction(Request $request, $nick = null)
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

        $dql = "SELECT l FROM BackendBundle:Like l WHERE l.user = $user_id ORDER BY l.id DESC";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('AppBundle:Like:likeslist.html.twig', array(
            'profile_user' => $user,
            'likeslist' => $pagination
        ));

    }

}
