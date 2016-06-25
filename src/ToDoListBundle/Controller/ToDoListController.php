<?php

namespace ToDoListBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use ToDoListBundle\Entity\TaskList;
use ToDoListBundle\Form\TaskListType;
use ToDoListBundle\Entity\Task;
use ToDoListBundle\Form\TaskType;

class ToDoListController extends Controller
{
    /**
     * Index Action to render the first template
     *
     * @return Response
     */
    public function indexAction()
    {
        $repository = $this->getDoctrine()->getRepository('ToDoListBundle:TaskList');
        $tasksList = $repository->findAll();
        return $this->render('ToDoListBundle:TaskViews:index.html.twig', array('tasksLists' => $tasksList));
    }

    /**
     * Action to add a taskList
     *
     * @param Request $request
     *
     * @return RedirectResponse | Response
     */
    public function addTaskListAction(Request $request)
    {
        $taskList = new TaskList();
        $form = $this->get('form.factory')->create(TaskListType::class, $taskList);
        if ($form->handleRequest($request)->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($taskList);
            $entityManager->flush();
            $request->getSession()->getFlashBag()->add('notice', 'TaskList saved.');
            return $this->redirect($this->generateUrl('todo_list_add_task_list'));
        }
        return $this->render('ToDoListBundle:TaskViews:addTaskList.html.twig', array('form' => $form->createView(),));
    }

    /**
     * Action to print the details of a TaskList, the tasks
     *
     * @param $idList
     * @param Request $request
     *
     * @return RedirectResponse | Response
     */
    public function detailTasksAction($idList, Request $request)
    {
        $taskList = $this->getDoctrine()->getRepository('ToDoListBundle:TaskList')->find($idList);
        $repository = $this->getDoctrine()->getRepository('ToDoListBundle:Task');
        $tasks = $repository->findByTaskListID($idList);

        $task = new Task();
        $task->setTaskListID($idList);
        $form = $this->get('form.factory')->create(TaskType::class, $task);

        if ($form->handleRequest($request)->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Task saved.');
            return $this->redirect($this->generateUrl('todo_list_detail_tasks', array('idList' => $idList)));
        }

        return $this->render('ToDoListBundle:TaskViews:detailTasks.html.twig', array('tasks' => $tasks, 'taskList' => $taskList, 'form' => $form->createView(),));

    }

    /**
     *  Render the taskLists
     *
     * @return Response
     */
    public function detailTaskListAction()
    {
        $repository = $this->getDoctrine()->getRepository('ToDoListBundle:TaskList');
        $tasklists = $repository->findAll();

        if (!$tasklists) {
            throw $this->createNotFoundException(
                'No tasklist found.'
            );
        }
        return $this->render('ToDoListBundle:TaskViews:index.html.twig', array('tasklists' => $tasklists));
    }

    /**
     * Method to delete a taskList
     *
     * @param $idList
     *
     * @return RedirectResponse
     */
    public function deleteTaskListAction($idList)
    {
        $em = $this->getDoctrine()->getManager();
        $TaskList = $em->getRepository('ToDoListBundle:TaskList')->find($idList);
        $em->remove($TaskList);
        $em->flush();
        return $this->redirect($this->generateUrl('todo_list_detail_task_list'));
    }

    /**
     * Method to update a taskList with his ID
     *
     * @param $idList
     * @param Request $request
     *
     * @return RedirectResponse | Response
     */
    public function updateTaskListAction($idList, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $taskList = $entityManager->getRepository('ToDoListBundle:TaskList')->find($idList);

        if (!$taskList) {
            throw $this->createNotFoundException(
                'No product found for id ' . $idList
            );
        }

        $form = $this->get('form.factory')->create(TaskListType::class, $taskList);

        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData();
            $taskList->setName($data->getName());
            $entityManager->flush();

            return $this->redirect($this->generateUrl("detail"));
        }

        return $this->render('ToDoListBundle:TaskViews:updateTaskList.html.twig', array('form' => $form->createView(),));
    }

}