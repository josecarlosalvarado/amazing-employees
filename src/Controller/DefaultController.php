<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use App\Service\EmployeeNormalize;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// AbstractController es un controlador de Symfony
// que pone a disposición nuestra multitud de características.
class DefaultController extends AbstractController
{
    
    /**
     * @Route("/default", name="default_index")
     * 
     * La clase ruta debe estar precedida en los comentario por una arroba.
     * El primer parámetro de Route es la URL a la que queremos asociar la acción.
     * El segundo parámetro de Route es el nombre que queremos dar a la ruta.
     */
    public function index( Request $request, EmployeeRepository $employeeRepository): Response
    {

        if($request->query->has('term')) {
            $people = $employeeRepository->findByTerm($request->query->get('term'));

            return $this->render('default/index.html.twig', [
                'people' => $people
            ]);
        }
        
        // Una acción siempre debe devolver una respesta.
        // Por defecto deberá ser un objeto de la clase,
        // Symfony\Component\HttpFoundation\Response

        // render() es un método hereado de AbstractController
        // que devuelve el contenido declarado en una plantillas de Twig.
        // https://twig.symfony.com/doc/3.x/templates.html


        // $orm = $this->getDoctrine();
        // $repo = $orm->getRepository(Employee::class); 
        // $repo->findAll();

        // $people = $this->getDoctrine()->getRepository(Employee::class)->findAll();

        $order = [];

        if($request->query->has('orderBy')) {
            $order[$request->query->get('orderBy')] = $request->query->get('orderDir', 'ASC');
        }
        $people = $employeeRepository->findBy([], $order); // Employee::class = App\Entity\Employee
        return $this->render('default/index.html.twig', [
            'people' => $people
        ]);
    }
    /**
     * @Route("hola", name="default_hola")
     */
    public function hola(): Response {
        return new response('<html><body>hola</body></html>');
    }

    /**
     * @Route(
     *      "/default.{_format}",
     *      name="default_index_json",
     *      requirements = {
     *          "_format": "json"
     *      }
     * )
     * 
     * El comando:
     * symfony console router:match /default.json
     * buscará la acción coincidente con la ruta indicada
     * y mostrará la información asociada.
     */
    public function indexJson(
        Request $request,
        EmployeeRepository $employeeRepository,
        EmployeeNormalize $employeeNormalize

    ): JsonResponse {

        // $data = $request->query->has('id') ? [] : [];
        $result = $request->query->has('id') ?
        $employeeRepository->find($request->query->get('id')) :
        $employeeRepository->findAll();

        $data = [];

        if ($result instanceof Employee) {
            array_push($data,$employeeNormalize->employeeNormalize($result));
        }

        foreach ($result as $employee) {
            array_push($data, $employeeNormalize->employeeNormalize($employee));
        }

        return $this->json($data);
    }


    /**
     * @Route(
     * "/default/{id}",
     *  name="default_show")
     * requirements= {
     *      "id": "[1-4]"
     *      * }
     */
    //la tecnica ParamConverte inyecta directamente,
    //un onjeto del tipo indicado como parametro
    //intentando hacer un mach del parametro de la ruta
    //con alguna de las propiedades del objeto requerido.
    public function show(Employee $employee): Response {
        return $this->render('default/show.html.twig', [
            'person' => $employee
        ]);
    }

   
    /**
     * @Route("/redirect-to-home", name="default_redirect_to_home")
     */
    public function redirectToHome(): Response {
        //redirigir a la URL /
        return $this->redirect('/');

        //redirigir a ruta urilizando su nombre
        // return $this->redirectToHome('default_show', ['id' => 1]);
    }

    /**
     * Normalize an employee.
     * 
     * @param Employee $employee
     * 
     * @return array|null
     */
    private function normalizeEmployee (Employee $employee): ?array {
        $projects = [];

        foreach($employee->getProjects() as $project) {
            array_push($projects, [
                'id' => $project->getId(),    
                'name' => $project->getName(),    
            ]);
        }

        return [
            'name' => $employee->getName(),
            'email' => $employee->getEmail(),
            'city' => $employee->getCity(),
            'department' => [
                'id' => $employee->getDepartment()->getId(),
                'name' => $employee->getDepartment()->getName(),
            ],
            'projects' => $projects
        ];
    }


}
