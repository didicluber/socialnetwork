<?php

namespace BackendBundle\Controller;

use BackendBundle\Entity\Jobs;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Job controller.
 *
 */
class JobsController extends Controller
{

    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * Lists all job entities.
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $job = new Jobs();
        $form = $this->createForm('BackendBundle\Form\JobsType', $job);
        $form->handleRequest($request);
        $jobs = $em->getRepository('BackendBundle:Jobs')->findAll();
        $users = $em->getRepository('BackendBundle:User')->findAll();

        return $this->render('jobs/index.html.twig', array(
            'jobs' => $jobs,
            'users' => $users,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new job entity.
     *
     */
    public function newAction(Request $request)
    {
        $job = new Jobs();
        $user = $this->getUser();
        $form = $this->createForm('BackendBundle\Form\JobsType', $job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush($job);

            $user_media_route = 'uploads/media/'.$user->getId().'_usermedia';

            // upload image
            $file = $form['image']->getData();
            if (!empty($file) && $file != null) {
                $ext = $file->guessExtension();

                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
                    $file_name = $user->getId().'_imgcompany_'.time().'.'.$ext;
                    $file->move($user_media_route.'/company/images', $file_name);

                    $job->setImage($file_name);
                } else {
                    $job->setImage(null);
                }
            } else {
                $job->setImage(null);
            }


            $job->setUser($user);
            $job->setCreatedAt(new \DateTime("now"));

            $em->persist($job);
            $flush = $em->flush();

            if ($flush == null) {
                $status = 'La publication a été crée correctement !!';
            } else {
                $status = 'Erreur lors de l\'ajout de la publication !!';
            }

        } else {
            $status = 'Le message n\'a pas été créé, car le formulaire n\'est pas valide';
        }

        $this->session->getFlashBag()->add("status", $status);




        return $this->render('jobs/new.html.twig', array(
            'job' => $job,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a job entity.
     *
     */
    public function showAction(Jobs $job)
    {
        $deleteForm = $this->createDeleteForm($job);

        return $this->render('jobs/show.html.twig', array(
            'job' => $job,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing job entity.
     *
     */
    public function editAction(Request $request, Jobs $job)
    {
        $deleteForm = $this->createDeleteForm($job);
        $editForm = $this->createForm('BackendBundle\Form\JobsType', $job);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('jobs_edit', array('id' => $job->getId()));
        }

        return $this->render('jobs/edit.html.twig', array(
            'job' => $job,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a job entity.
     *
     */
    public function deleteAction(Request $request, Jobs $job)
    {
        $form = $this->createDeleteForm($job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($job);
            $em->flush($job);
        }

        return $this->redirectToRoute('jobs_index');
    }

    /**
     * Creates a form to delete a job entity.
     *
     * @param Jobs $job The job entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Jobs $job)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('jobs_delete', array('id' => $job->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
