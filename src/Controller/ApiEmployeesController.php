<?php

namespace App\Controller;

use App\Entity\Employee;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/amazing-employees", name="api_employees_")
 */
class ApiEmployeesController extends AbstractController
{
    /**
     * @Route(
     *      "",
     *      name="cget",
     *      methods={"GET"}
     * )
     */
    public function index(Request $request, EmployeeRepository $employeeRepository): Response
    {
        if($request->query->has('term')) {
            $people = $employeeRepository->findByTerm($request->query->get('term'));

            return $this->json($people);
        }

        return $this->json($employeeRepository->findAll());
    }


    /**
     * @Route(
     *      "/{id}",
     *      name="get",
     *      methods={"GET"},
     *      requirements={
     *          "id": "\d+"
     *      }
     * )
     */
    public function show(Employee $employee): Response
    {
        return $this->json($employee);
    }

    /**
     * @Route(
     *      "",
     *      name="post",
     *      methods={"POST"}
     * )
     */
    public function add(
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
 
        $data = $request->request; // nos treameos los datos
        
        $employee = new Employee();//creamos un nuevo Employee

        $employee->setName($data->get('name'));
        $employee->setEmail($data->get('email'));
        $employee->setAge($data->get('age'));
        $employee->setCity($data->get('city'));
        $employee->setPhone($data->get('phone'));

        $entityManager->persist($employee); // persiste en memoria hasta enviarlo a la base de datos como un git commit
        $entityManager->flush();//envia los datos de $entityManager en la base de datos como un git push

        dump($employee);

        return $this->json(
            $employee,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl(
                    'api_employees_get',
                    [
                        'id' => $employee->getId()
                    ]
                )
            ]
        );
    }

    /**
     * @Route(
     * "/{id}",
     *  name="update",  
     *  methods={"PUT"},
     *   requirements={
     *          "id": "\d+" 
     * })
     */
    public function update(
        Employee $employee,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {

        $data = $request->request; //recupero los datos de la solicitud

        $employee->setName($data->get('name'));
        $employee->setEmail($data->get('email'));
        $employee->setAge($data->get('age'));
        $employee->setCity($data->get('city'));
        $employee->setPhone($data->get('phone'));

        $entityManager->persist($employee);
        $entityManager->flush();

        return  $this->json([
            
        ]);
    }

        /**
     * @Route(
     * "/{id}",
     *  name="delete",
     *  methods={"DELETE"},
     *   requirements={
     *          "id": "\d+" 
     * })
     */
    public function DeleteEmpleado(
        Employee $employee,
        EntityManagerInterface $entityManager
    ): Response {

        dump($employee);

        $entityManager->remove($employee);
        $entityManager->flush();
        return  $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
