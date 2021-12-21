<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ConfigBundle\Entity\Pais;
use ConfigBundle\Entity\Provincia;
use ConfigBundle\Entity\Localidad;
use ConfigBundle\Form\RegionType;

/**
 * @Route("/region")
 */
class RegionController extends Controller
{
    
    /**
     * @Route("/{table}", name="region")
     * @Method("GET")
     * @Template()
     */        
    public function indexAction($table)
    {
        UtilsController::haveAccess($this->getUser(),'region');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:'.$table)->findAll();
        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm( $table, $entity->getId())->createView();
        }
        $html = $this->renderView('ConfigBundle:Region:list.html.twig', 
                array('entities' => $entities,'table'=>$table, 'deleteForms' => $deleteForms )
        );
        return $this->render('ConfigBundle:Region:index.html.twig', array(
            'title' => $table, 'html' => json_encode($html)
        ));
    }
    
     /**
     * @Route("/new/{table}", name="region_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($table)
    {
        UtilsController::haveAccess($this->getUser(),'region_new');
        $entity = $this->getNewObject($table);
        $urlCreate = $this->generateUrl('region_create', array('table' => $table));    
        $form = $this->createForm(new RegionType(), $entity, array(
                        'action' => $urlCreate, 'method' => 'POST',
                    ));            

        $html = $this->renderView('ConfigBundle:Region:edit.html.twig', 
                array('entity' => $entity, 'form' => $form->createView(), 'table' => $table  )
        );
        return $this->render('ConfigBundle:Region:index.html.twig', array(
            'title' => $table, 'html' => json_encode($html)
        ));
    }
    
    /**
     * @Route("/create/{table}", name="region_create")
     * @Method("POST")
     * @Template()
     */    
    public function createAction(Request $request, $table){
        UtilsController::haveAccess($this->getUser(),'region_new');
        $entity = $this->getNewObject($table);
        $urlCreate = $this->generateUrl('region_create', array('table' => $table));    
        $form = $this->createForm(new RegionType(), $entity, array(
                        'action' => $urlCreate, 'method' => 'POST',
                    )); 
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try{
                $em->persist($entity);
                $em->flush();
                $this->addFlash('success','Creado con éxito!' );
                 return $this->redirectToRoute('region',array('table'=>$table));
            }catch (\Exception $ex) {  
                $this->addFlash('danger',$ex->getTraceAsString());    
            }
        }
        $html = $this->renderView('ConfigBundle:Region:edit.html.twig', 
                array('entity' => $entity, 'form' => $form->createView(), 'table' => $table  )
        );
        return $this->render('ConfigBundle:Region:index.html.twig', array(
            'title' => $table, 'html' => json_encode($html)
        ));
    }
    
    /**
     * @Route("/{table}/{id}/edit", name="region_edit")
     * @Method("GET")
     * @Template()
     */    
    public function editAction($table,$id){
        UtilsController::haveAccess($this->getUser(),'region_edit');
        $em = $this->getDoctrine()->getManager();   
        $entity = $em->getRepository('ConfigBundle:'.$table)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe');
        }
        $urlUpdate = $this->generateUrl('region_update', 
                array('table' => $table,'id' => $entity->getId()) );

        $form = $this->createForm(new RegionType(), $entity, array(
            'action' => $urlUpdate,
            'method' => 'POST',
        ));
        $html = $this->renderView('ConfigBundle:Region:edit.html.twig', 
                array('entity' => $entity, 'form' => $form->createView(), 'table' => $table  )
        );
        return $this->render('ConfigBundle:Region:index.html.twig', array(
            'title' => $table, 'html' => json_encode($html)
        ));
    }
    
    /**
     * @Route("/update/{table}/{id}", name="region_update")
     * @Method("PUT")
     * @Template()
     */    
    public function updateAction(Request $request, $table, $id) {
        UtilsController::haveAccess($this->getUser(),'region_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:' . $table)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe.');
        }
        $urlUpdate = $this->generateUrl('region_update', array('table' => $table, 'id' => $entity->getId()));

        $form = $this->createForm(new RegionType(), $entity, array(
            'action' => $urlUpdate,
            'method' => 'PUT',
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            try{
                $em->persist($entity);
                $em->flush();
                $this->addFlash('success','Modificado con éxito!' ); 
                return $this->redirectToRoute('region',array('table'=>$table));
                
            }catch (\Exception $ex) {  
                $this->addFlash('danger',$ex->getTraceAsString());    
            }
        }
        $html = $this->renderView('ConfigBundle:Region:edit.html.twig', 
                array('entity' => $entity, 'form' => $form->createView(), 'table' => $table  )
        );
        return $this->render('ConfigBundle:Region:index.html.twig', array(
            'title' => $table, 'html' => json_encode($html)
        ));
    }
    
    /**
     * @Route("/delete/{table}/{id}", name="region_delete")
     * @Method("DELETE")
     */
    public function deleteAction($table, $id)
    {
        UtilsController::haveAccess($this->getUser(),'region_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:'.$table)->find($id);
        try {
            $em->remove($entity);
            $em->flush();
            $this->addFlash('success', 'Se ha eliminado con éxito!');
        } catch (\Doctrine\DBAL\DBALException $e) {
            /*if ($this->getUser()->getRol()->getAdmin())
                $this->addFlash('danger', $e->getMessage());
              else*/
                $this->addFlash('danger', 'Este dato no puede ser eliminado porque está siendo utilizado en el sistema.');
        }
        return $this->redirectToRoute('region',array('table'=>$table));
    }
    
    
    private function createDeleteForm($table,$id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('region_delete', array('table'=>$table , 'id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    
    
    /*
     * Functions
     */
    private function getNewObject($obj) {
        switch ($obj) {
            case 'Localidad':
                $entity = new Localidad();
                break;
            case 'Provincia':
                $entity = new Provincia();
                break;
            case 'Pais':
                $entity = new Pais();
                break;
        }
        return $entity;
    }
       
    
    /**
     * @Route("/selectProvincias", name="select_provincias")
     * @Method("POST")
     */    
    public function provinciasAction(Request $request)
    {
        $pais_id = $request->request->get('pais_id');
        $em = $this->getDoctrine()->getManager();
        $provincias = $em->getRepository('ConfigBundle:Provincia')->findByPaisId($pais_id);
        return new JsonResponse($provincias);
    }    
    
    /**
     * @Route("/selectLocalidades", name="select_localidades")
     * @Method("POST")
     */        
    public function localidadesAction(Request $request)
    {
        $provincia_id = $request->request->get('provincia_id');
        $em = $this->getDoctrine()->getManager();
        $localidades = $em->getRepository('ConfigBundle:Localidad')->findByProvinciaId($provincia_id);
        return new JsonResponse($localidades);
    }    
    /**
     * @Route("/getCodPostal", name="get_codpostal")
     * @Method("POST")
     */            
    public function codPostalAction(Request $request)
    {
        $localidad_id = $request->request->get('localidad_id');
        $em = $this->getDoctrine()->getManager();
        $localidad = $em->getRepository('ConfigBundle:Localidad')->find($localidad_id);
        return new JsonResponse($localidad->getCodPostal());
    }      
    
    
    
    
}